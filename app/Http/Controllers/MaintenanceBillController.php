<?php

namespace App\Http\Controllers;

use App\DataTables\MaintenanceBillsDataTable;
use App\Http\Requests\StoreMaintenanceBillRequest;
use App\Http\Requests\UpdateMaintenanceBillStatusRequest;
use App\Models\Block;
use App\Models\Flat;
use App\Models\Maintenance;
use App\Models\MaintenanceBill;
use App\Models\Resident;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use GuzzleHttp\Psr7\Response as Psr7Response;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Laravel\Mcp\Response;

class MaintenanceBillController extends Controller
{
    /**
     * Display a listing of the resource.
     * This loads the main dashboard for maintenance bills, including the top statistics,
     * the revenue chart, and the data table.
     *
     * @return Response
     */
    public function index(MaintenanceBillsDataTable $dataTable)
    {
        abort_if(\Gate::denies('maintenance_bill_view'), 403);
        // 1. Calculate overall collection statistics for the top cards
        $totalCollected = MaintenanceBill::where('status', 'paid')->sum('total_amount');
        $cashCollected = MaintenanceBill::where('status', 'paid')->where('payment_method', 'CASH')->sum('total_amount');
        $upiCollected = MaintenanceBill::where('status', 'paid')->where('payment_method', 'UPI')->sum('total_amount');

        // 2. Prepare data for the monthly revenue chart (current year)
        $months = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December',
        ];

        $monthlyRevenueDB = MaintenanceBill::query()
            ->where('maintenance_bills.status', 'paid')
            ->join('maintenances', 'maintenance_bills.maintenance_id', '=', 'maintenances.id')
            ->where('maintenances.year', Carbon::now()->year)
            ->selectRaw('maintenances.month, SUM(maintenance_bills.total_amount) as total')
            ->groupBy('maintenances.month')
            ->pluck('total', 'month')
            ->toArray();

        // Map database results to the 12-month array format required by the chart
        $chartDataRevenue = array_map(function ($month) use ($monthlyRevenueDB) {
            return $monthlyRevenueDB[$month] ?? 0;
        }, $months);

        // 3. Fetch data for dropdown filters (Blocks, Residents, Years)
        $blocks = Block::orderBy('block_name')->get();
        $residents = $this->getUniqueActiveResidents();

        $dbYears = Maintenance::select('year')->distinct()->pluck('year')->toArray();
        $currentYear = Carbon::now()->year;
        $rangeYears = range(2024, $currentYear + 1);
        $years = collect(array_merge($dbYears, $rangeYears))->unique()->sortDesc()->values();

        // Render the page with all necessary data
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
     * Show the form for creating a new maintenance bill.
     * Prepares data like active residents, their fees, and dynamic penalty/discount settings.
     *
     * @return Psr7Response
     */
    public function create()
    {
        abort_if(\Gate::denies('maintenance_bill_create'), 403);
        // Get all active residents (filtering out old owners if a tenant lives there)
        $residents = $this->getUniqueActiveResidents();

        // Pre-calculate the base monthly fee for each resident based on flat type (Owner vs Tenant rate)
        $residentFees = $residents->mapWithKeys(function ($resident) {
            $fee = 0;
            if ($resident->flat && $resident->flat->flatType) {
                $fee = ($resident->type === 'owner')
                    ? $resident->flat->flatType->owner_maintenance_fee
                    : $resident->flat->flatType->rental_maintenance_fee;
            }

            return [$resident->id => $fee];
        });

        // Load the global penalty and discount settings to pass to the frontend JavaScript
        $discountSettings = $this->getSettingValues('discount');
        $penaltySettings = $this->getSettingValues('penalty');

        return view('maintenance_bills.create', compact('residents', 'residentFees', 'discountSettings', 'penaltySettings'));
    }

    /**
     * Store a newly created payment/bill in the database.
     * Handles the logic of calculating totals, splitting across multiple months, and saving.
     *
     * @return Response
     */
    public function store(StoreMaintenanceBillRequest $request)
    {
        abort_if(\Gate::denies('maintenance_bill_create'), 403);
        // Wrap everything in a database transaction. If anything fails, it will roll back all changes.
        DB::beginTransaction();

        try {
            $resident = Resident::with(['user', 'flat.flatType'])->findOrFail($request->resident_id);

            if (! $resident->flat || ! $resident->flat->flatType) {
                throw new \Exception('Resident does not have a flat assigned with a valid flat type.');
            }

            // Determine the base fee (Owner vs Rental rate)
            $monthlyFee = ($resident->type === 'owner')
                ? $resident->flat->flatType->owner_maintenance_fee
                : $resident->flat->flatType->rental_maintenance_fee;

            $numberOfMonths = (int) $request->months;

            // Handle file upload for payment slips
            $paymentSlipPath = null;
            if ($request->hasFile('payment_slip')) {
                $paymentSlipPath = $request->file('payment_slip')->store('payment_slips', 'public');
            }

            // Determine the exact start date selected by the user
            $currentDate = Carbon::createFromDate($request->start_year, Carbon::parse($request->start_month)->month, 1);

            // Calculate the total penalty and discount amounts across all selected months
            [$totalPenaltyAmount, $totalDiscountAmount] = $this->calculatePenaltyAndDiscount(
                $request, $monthlyFee, $numberOfMonths, $currentDate
            );

            // Split the total amount evenly across the selected number of months
            $amountPerMonth = $monthlyFee + ($totalPenaltyAmount / $numberOfMonths) - ($totalDiscountAmount / $numberOfMonths);
            $amountPerMonth = max(0, $amountPerMonth); // Prevent negative bills

            // Generate a unique batch ID to group these multi-month payments together
            $batchId = uniqid('pay_');

            // Loop through each month and create a separate database entry
            for ($i = 0; $i < $numberOfMonths; $i++) {
                $loopDate = $currentDate->copy()->addMonths($i);
                $monthStr = $loopDate->format('F');
                $yearInt = $loopDate->year;

                // 1. Ensure the core "Maintenance" (the master billing period) exists
                $maintenance = Maintenance::firstOrCreate(
                    ['month' => $monthStr, 'year' => $yearInt],
                    [
                        'billing_cycle' => 'monthly',
                        'due_date' => $loopDate->copy()->endOfMonth()->format('Y-m-d'),
                        'total_additional_cost' => 0,
                        'status' => 'published',
                    ]
                );

                // 2. Create or update the specific bill for this flat in this month
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

            // Commit all operations to the database
            DB::commit();

            $message = 'Payment recorded successfully for '.$numberOfMonths.' months.';

            return $request->ajax()
                ? response()->json(['success' => true, 'message' => $message])
                : redirect()->route('maintenance-bills.index')->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            $message = 'Error recording payment: '.$e->getMessage();

            return $request->ajax()
                ? response()->json(['success' => false, 'message' => $message], 500)
                : redirect()->back()->with('error', $message);
        }
    }

    /**
     * Remove the specified bill from storage (either by batch ID or individual ID).
     *
     * @param  string  $id  Can be batch_id or individual bill id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        abort_if(\Gate::denies('maintenance_bill_delete'), 403);
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
     * Additional method to delete an individual bill without deleting the entire batch.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroyIndividual($id)
    {
        abort_if(\Gate::denies('maintenance_bill_delete'), 403);
        $bill = MaintenanceBill::findOrFail($id);
        $bill->delete();

        return response()->json([
            'success' => true,
            'message' => 'Maintenance bill deleted successfully.',
        ]);
    }

    /**
     * Method to update payment status.
     * When marking a bill as 'paid', it dynamically recalculates and locks in the final penalty/discount amounts.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateStatus(UpdateMaintenanceBillStatusRequest $request, $id)
    {
        abort_if(\Gate::denies('maintenance_bill_create'), 403);
        $maintenanceBill = MaintenanceBill::findOrFail($id);

        if ($request->status === 'paid' && $maintenanceBill->status !== 'paid') {

            // Re-fetch the correct monthly fee based on user type
            $monthlyFee = ($maintenanceBill->resident->type === 'owner')
                ? $maintenanceBill->flat->flatType->owner_maintenance_fee
                : $maintenanceBill->flat->flatType->rental_maintenance_fee;

            $currentDate = Carbon::createFromDate(
                $maintenanceBill->maintenance->year,
                Carbon::parse($maintenanceBill->maintenance->month)->month,
                1
            );

            // Force recalculation of penalty and discount based on the ACTUAL date it was marked 'paid'
            [$totalPenaltyAmount, $totalDiscountAmount] = $this->calculatePenaltyAndDiscount(
                $request, $monthlyFee, 1, $currentDate, true
            );

            $maintenanceBill->status = 'paid';
            $maintenanceBill->paid_at = now();
            $maintenanceBill->payment_method = $request->payment_method;
            $maintenanceBill->transaction_id = $request->transaction_id;

            if ($request->hasFile('payment_slip')) {
                $maintenanceBill->payment_slip = $request->file('payment_slip')->store('payment_slips', 'public');
            }

            // Lock in the dynamically calculated amounts so they never change again
            $maintenanceBill->penalty_amount = $totalPenaltyAmount;
            $maintenanceBill->discount_amount = $totalDiscountAmount;
            $maintenanceBill->total_amount = $monthlyFee + $totalPenaltyAmount - $totalDiscountAmount;

        } elseif ($request->status !== 'paid') {
            // Revert back to unpaid state
            $maintenanceBill->status = $request->status;
            $maintenanceBill->paid_at = null;
            $maintenanceBill->payment_method = null;
            $maintenanceBill->transaction_id = null;
            $maintenanceBill->payment_slip = null;

            // Reset modifiers
            $maintenanceBill->penalty_amount = 0;
            $maintenanceBill->discount_amount = 0;
            // The total_amount should revert back to just the base fee (adjusted as needed by business rules)
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
                'totalAmountExpected' => number_format($totalAmountExpected, 2),
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
        abort_if(\Gate::denies('maintenance_bill_view'), 403);
        $bill = MaintenanceBill::with(['user', 'flat.block', 'flat.flatType', 'maintenance'])->findOrFail($id);

        return view('maintenance_bills.details', compact('bill'));
    }

    /**
     * Download the invoice as a PDF file.
     *
     * @param  string  $id  Can be batch_id or individual bill id
     * @return \Illuminate\Http\Response
     */
    public function downloadInvoice($id)
    {
        abort_if(\Gate::denies('maintenance_bill_view'), 403);
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
        $fileName = 'invoice_'.($bill->flat->block->block_name ?? '').'-'.($bill->flat->flat_no ?? '').'_'.now()->format('Ymd_His').'.pdf';

        return $pdf->download($fileName);
    }

    /**
     * API endpoint to fetch resident info based on user ID.
     * Used for dynamic form updates via AJAX.
     *
     * @param  int  $userId
     * @return JsonResponse
     */
    public function getResidentInfo($userId)
    {
        abort_if(\Gate::denies('maintenance_bill_view'), 403);
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
                'amount' => $amount,
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Resident not found or flat/flat type missing.']);
    }

    // =========================================================================
    // PRIVATE HELPER METHODS
    // =========================================================================

    /**
     * Helper to get setting values for discount or penalty.
     * Fetches the global configuration from the database.
     *
     * @param  string  $type  'discount' or 'penalty'
     */
    private function getSettingValues(string $type): array
    {
        return [
            "apply_{$type}" => \App\Models\Setting::get("apply_{$type}", '1'),
            'type' => \App\Models\Setting::get("{$type}_type", 'percentage'),

            // Values (percentages or fixed amounts)
            'yearly_value' => (float) \App\Models\Setting::get("{$type}_yearly_value", \App\Models\Setting::get("{$type}_yearly_percent", ($type === 'penalty' ? 15 : 10))),
            'half_yearly_value' => (float) \App\Models\Setting::get("{$type}_half_yearly_value", \App\Models\Setting::get("{$type}_half_yearly_percent", ($type === 'penalty' ? 10 : 0))),
            'quarterly_value' => (float) \App\Models\Setting::get("{$type}_quarterly_value", \App\Models\Setting::get("{$type}_quarterly_percent", 5)),
            'monthly_value' => (float) \App\Models\Setting::get("{$type}_monthly_value", \App\Models\Setting::get("{$type}_monthly_percent", 2)),

            // Toggle Switches
            'yearly_enabled' => \App\Models\Setting::get("{$type}_yearly_enabled", '1') == '1',
            'half_yearly_enabled' => \App\Models\Setting::get("{$type}_half_yearly_enabled", '1') == '1',
            'quarterly_enabled' => \App\Models\Setting::get("{$type}_quarterly_enabled", '1') == '1',
            'monthly_enabled' => \App\Models\Setting::get("{$type}_monthly_enabled", '1') == '1',
        ];
    }

    /**
     * Helper to fetch unique active residents.
     * If an owner has rented out their flat, the tenant will take precedence in this list.
     *
     * @return Collection
     */
    private function getUniqueActiveResidents()
    {
        $activeResidents = Resident::with(['user', 'flat.block', 'flat.flatType'])
            ->where(function ($query) {
                $query->whereNull('move_out_date')
                    ->orWhere('move_out_date', '>=', now()->startOfDay());
            })
            ->get();

        $uniqueResidents = collect();
        foreach ($activeResidents->groupBy('flat_id') as $flatId => $flatResidents) {
            // Sort residents: null move_out_date first, then newest move_in_date
            $sortedResidents = $flatResidents->sortByDesc(function ($res) {
                return [
                    is_null($res->move_out_date) ? 1 : 0,
                    $res->move_in_date,
                ];
            });

            // Prioritize the tenant if one exists, otherwise default to the owner
            $tenant = $sortedResidents->where('type', 'rental')->first();
            $uniqueResidents->push($tenant ?: $sortedResidents->first());
        }

        return $uniqueResidents->sortBy(function ($resident) {
            return $resident->user->name ?? '';
        })->values();
    }

    /**
     * Core logic engine to calculate the correct penalty and discount amounts.
     * This iterates month-by-month to apply the exact tier rate to each individual month based on lateness.
     *
     * @param  bool  $forceRecalculation  If true, completely ignores frontend request values and relies ONLY on settings.
     * @return array [totalPenaltyAmount, totalDiscountAmount]
     */
    private function calculatePenaltyAndDiscount(
        Request $request, float $monthlyFee, int $numberOfMonths, Carbon $startDate, bool $forceRecalculation = false
    ): array {
        $now = Carbon::now()->startOfMonth();

        // 1. Determine how many months are past due (arrears) and how many are in advance (future)
        $pastMonthsCount = 0;
        $futureMonthsCount = $numberOfMonths;

        if ($startDate->lt($now)) {
            $pastMonthsCount = $now->diffInMonths($startDate);
            // Cap past months so it doesn't exceed the total selected duration
            if ($pastMonthsCount > $numberOfMonths) {
                $pastMonthsCount = $numberOfMonths;
            }
            $futureMonthsCount = $numberOfMonths - $pastMonthsCount;
        }

        // 2. Penalty Calculation (Only applies to Past Months)
        $totalPenaltyAmount = 0;

        // If frontend provided a manually overridden amount, use it (unless we force a strict recalculation)
        if (! $forceRecalculation && $request->has('penalty_amount') && $request->filled('penalty_amount')) {
            $totalPenaltyAmount = (float) $request->penalty_amount;
        } else {
            $penaltySettings = $this->getSettingValues('penalty');

            // If penalty feature is enabled, apply penalty based on total past months
            if ($penaltySettings['apply_penalty'] === '1' && $pastMonthsCount > 0) {
                $penaltyValue = 0;

                // Determine which penalty tier the total duration falls into
                if ($pastMonthsCount >= 12 && $penaltySettings['yearly_enabled']) {
                    $penaltyValue = $penaltySettings['yearly_value'];
                } elseif ($pastMonthsCount >= 6 && $penaltySettings['half_yearly_enabled']) {
                    $penaltyValue = $penaltySettings['half_yearly_value'];
                } elseif ($pastMonthsCount >= 3 && $penaltySettings['quarterly_enabled']) {
                    $penaltyValue = $penaltySettings['quarterly_value'];
                } elseif ($pastMonthsCount >= 1 && $penaltySettings['monthly_enabled']) {
                    $penaltyValue = $penaltySettings['monthly_value'];
                }

                // Apply the penalty rate to the total arrears amount
                if ($penaltyValue > 0) {
                    if ($penaltySettings['type'] === 'fixed') {
                        $totalPenaltyAmount = (float) $penaltyValue;
                    } else {
                        $totalArrearsAmount = $pastMonthsCount * $monthlyFee;
                        $totalPenaltyAmount = $totalArrearsAmount * ($penaltyValue / 100);
                    }
                }
            }
        }

        // 3. Discount Calculation (Only applies to Future/Advance Months)
        $totalDiscountAmount = 0;

        if (! $forceRecalculation && $request->has('discount_amount') && $request->filled('discount_amount')) {
            $totalDiscountAmount = (float) $request->discount_amount;
        } else {
            $discountSettings = $this->getSettingValues('discount');
            $applyDiscount = $discountSettings['apply_discount'];

            // If discount feature is enabled, apply discount based on total future months
            if (($applyDiscount === '1' || $applyDiscount === 'true' || $applyDiscount === 'on') && $futureMonthsCount > 0) {
                $discountValue = 0;

                // Determine which discount tier the total duration falls into
                if ($futureMonthsCount >= 12 && $discountSettings['yearly_enabled']) {
                    $discountValue = $discountSettings['yearly_value'];
                } elseif ($futureMonthsCount >= 6 && $discountSettings['half_yearly_enabled']) {
                    $discountValue = $discountSettings['half_yearly_value'];
                } elseif ($futureMonthsCount >= 3 && $discountSettings['quarterly_enabled']) {
                    $discountValue = $discountSettings['quarterly_value'];
                } elseif ($futureMonthsCount >= 1 && $discountSettings['monthly_enabled']) {
                    $discountValue = $discountSettings['monthly_value'];
                }

                // Apply the discount rate to the total advance amount
                if ($discountValue > 0) {
                    if ($discountSettings['type'] === 'fixed') {
                        $totalDiscountAmount = (float) $discountValue;
                    } else {
                        $totalAdvanceAmount = $futureMonthsCount * $monthlyFee;
                        $totalDiscountAmount = $totalAdvanceAmount * ($discountValue / 100);
                    }
                }
            }
        }

        return [$totalPenaltyAmount, $totalDiscountAmount];
    }
}
