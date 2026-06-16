<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MaintenanceBill;
use App\Models\Maintenance;
use App\Models\Resident;
use App\DataTables\MaintenanceBillsDataTable;
use App\Models\Block;
use App\Models\Flat;
use App\Models\PrepaidMaintenance;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use App\Http\Requests\StoreMaintenanceBillRequest;
use App\Http\Requests\UpdateMaintenanceBillStatusRequest;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Mcp\Response;

class MaintenanceBillController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  MaintenanceBillsDataTable  $dataTable
     * @return Response
     */
    public function index(MaintenanceBillsDataTable $dataTable)
    {
        $totalCollected = MaintenanceBill::where('status', 'paid')->sum('total_amount');
        $cashCollected = MaintenanceBill::where('status', 'paid')->where('payment_method', 'CASH')->sum('total_amount');
        $upiCollected = MaintenanceBill::where('status', 'paid')->where('payment_method', 'UPI')->sum('total_amount');

        $months = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];

        $monthlyRevenueDB = MaintenanceBill::query()
            ->where('maintenance_bills.status', 'paid')
            ->join('maintenances', 'maintenance_bills.maintenance_id', '=', 'maintenances.id')
            ->where('maintenances.year', Carbon::now()->year)
            ->selectRaw('maintenances.month, SUM(maintenance_bills.total_amount) as total')
            ->groupBy('maintenances.month')
            ->pluck('total', 'month')
            ->toArray();

        $chartDataRevenue = array_map(function ($month) use ($monthlyRevenueDB) {
            return $monthlyRevenueDB[$month] ?? 0;
        }, $months);

        $blocks = Block::orderBy('block_name')->get();
        $residents = Resident::with(['user', 'flat.block'])->get()->sortBy(function($resident) {
            return $resident->user->name ?? '';
        });
        $dbYears = Maintenance::select('year')->distinct()->pluck('year')->toArray();
        $currentYear =  Carbon::now()->year;
        $rangeYears = range(2024, $currentYear + 1);
        $years = collect(array_merge($dbYears, $rangeYears))->unique()->sortDesc()->values();

        return $dataTable->render('maintenance_bills.index', compact(
            'totalCollected',
            'cashCollected',
            'upiCollected',
            'months',
            'chartDataRevenue',
            'blocks',
            'residents',
            'years'
        ));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    // This method prepares the data needed for the create form, including fetching active residents and their associated fees, as well as discount and penalty settings.
    public function create()
    {
        $residents = Resident::with(['user', 'flat.flatType', 'block'])
            ->where(function (Builder $query) {
                $query->whereNull('move_out_date')
                    ->orWhere('move_out_date', '>=', Carbon::now()->startOfDay());
            })
            ->get();

        $residentFees = $residents->mapWithKeys(function ($resident) {
            $fee = 0;
            if ($resident->flat && $resident->flat->flatType) {
                $fee = ($resident->type === 'owner')
                    ? $resident->flat->flatType->owner_maintenance_fee
                    : $resident->flat->flatType->rental_maintenance_fee;
            }
            return [$resident->id => $fee];
        });

        $discountSettings = $this->getSettingValues('discount');
        $penaltySettings = $this->getSettingValues('penalty');

        return view('maintenance_bills.create', compact('residents', 'residentFees', 'discountSettings', 'penaltySettings'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreMaintenanceBillRequest  $request
     * @return Response
     */
    public function store(StoreMaintenanceBillRequest $request)
    {
        DB::beginTransaction();

        try {
            $resident = Resident::with(['user', 'flat.flatType'])->findOrFail($request->resident_id);

            if (!$resident->flat || !$resident->flat->flatType) {
                throw new \Exception('Resident does not have a flat assigned with a valid flat type.');
            }

            $monthlyFee = ($resident->type === 'owner')
                ? $resident->flat->flatType->owner_maintenance_fee
                : $resident->flat->flatType->rental_maintenance_fee;
            $numberOfMonths = $request->months;

            $paymentSlipPath = null;
            if ($request->hasFile('payment_slip')) {
                $paymentSlipPath = $request->file('payment_slip')->store('payment_slips', 'public');
            }

            $currentDate = Carbon::createFromDate($request->start_year, Carbon::parse($request->start_month)->month, 1);

            list($totalPenaltyAmount, $totalDiscountAmount) = $this->calculatePenaltyAndDiscount(
                $request, $monthlyFee, $numberOfMonths, $currentDate
            );

            $amountPerMonth = $monthlyFee + ($totalPenaltyAmount / $numberOfMonths) - ($totalDiscountAmount / $numberOfMonths);
            $amountPerMonth = max(0, $amountPerMonth); // Ensure amount is not negative

            $batchId = uniqid('pay_');

            for ($i = 0; $i < $numberOfMonths; $i++) {
                $loopDate = $currentDate->copy()->addMonths($i);
                $monthStr = $loopDate->format('F');
                $yearInt = $loopDate->year;

                $maintenance = Maintenance::firstOrCreate(
                    ['month' => $monthStr, 'year' => $yearInt],
                    [
                        'billing_cycle' => 'monthly',
                        'due_date' => $loopDate->copy()->endOfMonth()->format('Y-m-d'),
                        'total_additional_cost' => 0,
                        'status' => 'published'
                    ]
                );

                MaintenanceBill::updateOrCreate(
                    [
                        'maintenance_id' => $maintenance->id,
                        'flat_id' => $resident->flat_id,
                    ],
                    [
                        'batch_id' => $batchId,
                        'user_id' => $resident->user_id,
                        'block_id' => $resident->block_id,
                        'amount' => $monthlyFee,
                        'discount_amount' => $totalDiscountAmount / $numberOfMonths,
                        'penalty_amount' => $totalPenaltyAmount / $numberOfMonths,
                        'total_amount' => $amountPerMonth,
                        'generated_date' => now(),
                        'paid_at' => now(),
                        'payment_method' => $request->payment_method,
                        'transaction_id' => $request->transaction_id,
                        'payment_slip' => $paymentSlipPath,
                        'status' => 'paid',
                    ]
                );
            }

            DB::commit();

            $message = 'Payment recorded successfully for ' . $numberOfMonths . ' months.';
            return $request->ajax()
                ? response()->json(['success' => true, 'message' => $message])
                : redirect()->route('maintenance-bills.index')->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            $message = 'Error recording payment: ' . $e->getMessage();
            return $request->ajax()
                ? response()->json(['success' => false, 'message' => $message], 500)
                : redirect()->back()->with('error', $message);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $id  Can be batch_id or individual bill id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $bills = MaintenanceBill::where('batch_id', $id)->get();

        if ($bills->isEmpty()) {
            $bills = MaintenanceBill::where('id', $id)->get();
        }

        if ($bills->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Bill(s) not found.'], 404);
        }

        foreach ($bills as $bill) {
            $bill->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Payment deleted successfully.',
        ]);
    }

    /**
     * Additional method to delete individual bill (not by batch), useful for correcting mistakes without deleting entire batch
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroyIndividual($id)
    {
        $bill = MaintenanceBill::findOrFail($id);
        $bill->delete();

        return response()->json([
            'success' => true,
            'message' => 'Maintenance bill deleted successfully.',
        ]);
    }

    /**
     * Method to update payment status, with logic to lock in penalty and total amounts when marking as paid
     *
     * @param  \App\Http\Requests\UpdateMaintenanceBillStatusRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateStatus(UpdateMaintenanceBillStatusRequest $request, $id)
    {
        $maintenanceBill = MaintenanceBill::findOrFail($id);

        if ($request->status === 'paid' && $maintenanceBill->status !== 'paid') {
            // Recalculate penalty and discount based on current date for accurate locking
            $monthlyFee = ($maintenanceBill->resident->type === 'owner')
                ? $maintenanceBill->flat->flatType->owner_maintenance_fee
                : $maintenanceBill->flat->flatType->rental_maintenance_fee;

            $currentDate = Carbon::createFromDate(
                $maintenanceBill->maintenance->year,
                Carbon::parse($maintenanceBill->maintenance->month)->month,
                1
            );

            list($totalPenaltyAmount, $totalDiscountAmount) = $this->calculatePenaltyAndDiscount(
                $request, $monthlyFee, 1, $currentDate, true // Calculate for single month, force recalculation
            );

            $maintenanceBill->status = 'paid';
            $maintenanceBill->paid_at = now();
            $maintenanceBill->payment_method = $request->payment_method;
            $maintenanceBill->transaction_id = $request->transaction_id;

            if ($request->hasFile('payment_slip')) {
                $maintenanceBill->payment_slip = $request->file('payment_slip')->store('payment_slips', 'public');
            }

            // Lock in the dynamically calculated amounts
            $maintenanceBill->penalty_amount = $totalPenaltyAmount;
            $maintenanceBill->discount_amount = $totalDiscountAmount;
            $maintenanceBill->total_amount = $monthlyFee + $totalPenaltyAmount - $totalDiscountAmount;

        } elseif ($request->status !== 'paid') {
            $maintenanceBill->status = $request->status;
            $maintenanceBill->paid_at = null;
            $maintenanceBill->payment_method = null;
            $maintenanceBill->transaction_id = null;
            $maintenanceBill->payment_slip = null;
            // When status is not paid, reset penalty/discount to 0 or original calculated if any
            // This part might need more specific business logic based on how you want to handle unpaid bills
            // For now, we'll just clear payment-related fields.
            $maintenanceBill->penalty_amount = 0; // Or recalculate based on current date if bill becomes due again
            $maintenanceBill->discount_amount = 0;
            // total_amount would then be $monthlyFee + 0 - 0 = $monthlyFee
            // This needs careful consideration based on desired behavior for non-paid bills.
        }

        $maintenanceBill->save();

        if ($request->ajax() || $request->expectsJson()) {
            $maintenance = Maintenance::with('maintenanceBills')->findOrFail($maintenanceBill->maintenance_id);
            $paidCount = $maintenance->maintenanceBills->where('status', 'paid')->count();
            $totalCount = $maintenance->maintenanceBills->count();
            $totalAmountExpected = $maintenance->maintenanceBills->sum('total_amount');

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully.',
                'paidCount' => $paidCount,
                'totalCount' => $totalCount,
                'totalAmountExpected' => number_format($totalAmountExpected, 2)
            ]);
        }

        return redirect()->back()->with('success', 'Status updated successfully.');
    }

    /**
     * Display the specified resource details.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function details($id)
    {
        $bill = MaintenanceBill::with(['user', 'flat.block', 'flat.flatType', 'maintenance'])->findOrFail($id);

        return view('maintenance_bills.details', compact('bill'));
    }

    /**
     * Method to download invoice as PDF
     *
     * @param  string  $id  Can be batch_id or individual bill id
     * @return \Illuminate\Http\Response
     */
    public function downloadInvoice($id)
    {
        $bills = MaintenanceBill::with(['user', 'flat.block', 'flat.flatType', 'maintenance'])
            ->where('batch_id', $id)
            ->orderBy('id', 'asc')
            ->get();

        if ($bills->isEmpty()) {
            $bills = MaintenanceBill::with(['user', 'flat.block', 'flat.flatType', 'maintenance'])
                ->where('id', $id)
                ->get();
            if ($bills->isEmpty()) {
                abort(404);
            }
        }

        $bill = $bills->first();
        $pdf = Pdf::loadView('maintenance_bills.invoice_pdf', compact('bills', 'bill'));

        $fileName = 'invoice_' . ($bill->flat->block->block_name ?? '') . '-' . ($bill->flat->flat_no ?? '') . '_' . now()->format('Ymd_His') . '.pdf';

        return $pdf->download($fileName);
    }

    /**
     * API endpoint to fetch resident info based on user ID, used for dynamic form updates when creating/editing bills
     *
     * @param  int  $userId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getResidentInfo($userId)
    {
        $resident = Resident::with('flat.flatType')
            ->where('user_id', $userId)
            ->where(function (Builder $query) {
                $query->whereNull('move_out_date')
                    ->orWhere('move_out_date', '>=', Carbon::now()->startOfDay());
            })->first();

        if ($resident && $resident->flat && $resident->flat->flatType) {
            $amount = ($resident->type === 'owner')
                ? $resident->flat->flatType->owner_maintenance_fee
                : $resident->flat->flatType->rental_maintenance_fee;

            return response()->json([
                'success' => true,
                'block_id' => $resident->block_id,
                'flat_id' => $resident->flat_id,
                'amount' => $amount
            ]);
        }
        return response()->json(['success' => false, 'message' => 'Resident not found or flat/flat type missing.']);
    }

    /**
     * Helper to get setting values for discount or penalty.
     *
     * @param  string  $type  'discount' or 'penalty'
     * @return array
     */
    private function getSettingValues(string $type): array
    {
        return [
            "apply_{$type}" => setting("apply_{$type}", '1'),
            'type' => setting("{$type}_type", 'percentage'),
            'yearly_value' => (float)setting("{$type}_yearly_value", setting("{$type}_yearly_percent", ($type === 'penalty' ? 15 : 10))),
            'half_yearly_value' => (float)setting("{$type}_half_yearly_value", setting("{$type}_half_yearly_percent", ($type === 'penalty' ? 10 : 0))),
            'quarterly_value' => (float)setting("{$type}_quarterly_value", setting("{$type}_quarterly_percent", 5)),
            'monthly_value' => (float)setting("{$type}_monthly_value", setting("{$type}_monthly_percent", 2)),

            'yearly_enabled' => setting("{$type}_yearly_enabled", '1') == '1',
            'half_yearly_enabled' => setting("{$type}_half_yearly_enabled", '1') == '1',
            'quarterly_enabled' => setting("{$type}_quarterly_enabled", '1') == '1',
            'monthly_enabled' => setting("{$type}_monthly_enabled", '1') == '1',
        ];
    }

    /**
     * Helper to calculate penalty and discount amounts.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  float  $monthlyFee
     * @param  int  $numberOfMonths
     * @param  \Carbon\Carbon  $startDate
     * @param  bool  $forceRecalculation  If true, ignores request values and recalculates based on settings.
     * @return array  [totalPenaltyAmount, totalDiscountAmount]
     */
    private function calculatePenaltyAndDiscount(
        Request $request, float $monthlyFee, int $numberOfMonths, Carbon $startDate, bool $forceRecalculation = false
    ): array
    {
        $now = Carbon::now()->startOfMonth();

        // Calculate past and future months
        $pastMonthsCount = 0;
        $futureMonthsCount = $numberOfMonths;

        if ($startDate->lt($now)) {
            $pastMonthsCount = $now->diffInMonths($startDate);
            if ($pastMonthsCount > $numberOfMonths) {
                $pastMonthsCount = $numberOfMonths;
            }
            $futureMonthsCount = $numberOfMonths - $pastMonthsCount;
        }

        $arrearsAmount = $pastMonthsCount * $monthlyFee;
        $advanceAmount = $futureMonthsCount * $monthlyFee;

        $totalPenaltyAmount = 0;
        if ($forceRecalculation || ($request->has('penalty_amount') && $request->filled('penalty_amount'))) {
            $totalPenaltyAmount = (float)$request->penalty_amount;
        } else {
            $penaltySettings = $this->getSettingValues('penalty');
            if ($penaltySettings['apply_penalty'] === '1' && $pastMonthsCount > 0) {
                $penaltyValue = 0;
                if ($pastMonthsCount >= 12 && $penaltySettings['yearly_enabled']) {
                    $penaltyValue = $penaltySettings['yearly_value'];
                } elseif ($pastMonthsCount >= 6 && $penaltySettings['half_yearly_enabled']) {
                    $penaltyValue = $penaltySettings['half_yearly_value'];
                } elseif ($pastMonthsCount >= 3 && $penaltySettings['quarterly_enabled']) {
                    $penaltyValue = $penaltySettings['quarterly_value'];
                } elseif ($pastMonthsCount >= 1 && $penaltySettings['monthly_enabled']) {
                    $penaltyValue = $penaltySettings['monthly_value'];
                }

                if ($penaltyValue > 0) {
                    if ($penaltySettings['type'] === 'fixed') {
                        $totalPenaltyAmount = $penaltyValue;
                    } else {
                        $totalPenaltyAmount = $arrearsAmount * ($penaltyValue / 100);
                    }
                }
            }
        }

        // Discount is only applied to future months, so we use advanceAmount for percentage calculations   
        $totalDiscountAmount = 0;
        if ($forceRecalculation || ($request->has('discount_amount') && $request->filled('discount_amount'))) {
            $totalDiscountAmount = (float)$request->discount_amount;
        } else {
            $discountSettings = $this->getSettingValues('discount');
            $applyDiscount = $discountSettings['apply_discount'];

            if (($applyDiscount === '1' || $applyDiscount === 'true' || $applyDiscount === 'on') && $futureMonthsCount > 0) {
                $discountValue = 0;
                if ($futureMonthsCount >= 12 && $discountSettings['yearly_enabled']) {
                    $discountValue = $discountSettings['yearly_value'];
                } elseif ($futureMonthsCount >= 6 && $discountSettings['half_yearly_enabled']) {
                    $discountValue = $discountSettings['half_yearly_value'];
                } elseif ($futureMonthsCount >= 3 && $discountSettings['quarterly_enabled']) {
                    $discountValue = $discountSettings['quarterly_value'];
                } elseif ($futureMonthsCount >= 1 && $discountSettings['monthly_enabled']) {
                    $discountValue = $discountSettings['monthly_value'];
                }

                if ($discountValue > 0) {
                    if ($discountSettings['type'] === 'fixed') {
                        $totalDiscountAmount = $discountValue;
                    } else {
                        $totalDiscountAmount = $advanceAmount * ($discountValue / 100);
                    }
                }
            }
        }

        return [$totalPenaltyAmount, $totalDiscountAmount];
    }
}
