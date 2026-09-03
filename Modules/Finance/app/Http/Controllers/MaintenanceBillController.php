<?php

namespace Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Block;
use App\Models\Flat;
use App\Models\Resident;
use App\Models\Setting;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use GuzzleHttp\Psr7\Response as Psr7Response;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Modules\Finance\DataTables\MaintenanceBillsDataTable;
use Modules\Finance\Http\Requests\StoreMaintenanceBillRequest;
use Modules\Finance\Http\Requests\UpdateMaintenanceBillStatusRequest;
use Modules\Finance\Models\Maintenance;
use Modules\Finance\Models\MaintenanceBill;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

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
        abort_if(! \Auth::user()->can('maintenance_bill_view'), 403);
        try {
            // 1. Calculate overall collection statistics for the top cards
            $totalCollected = MaintenanceBill::where('status', config('status.maintenance_bills.paid', 'paid'))->sum('total_amount');
            $cashCollected = MaintenanceBill::where('status', config('status.maintenance_bills.paid', 'paid'))->where('payment_method', 'CASH')->sum('total_amount');
            $upiCollected = MaintenanceBill::where('status', config('status.maintenance_bills.paid', 'paid'))->where('payment_method', 'UPI')->sum('total_amount');
            $penaltyCollected = MaintenanceBill::where('status', config('status.maintenance_bills.paid', 'paid'))->sum('penalty_amount');

            // 2. Prepare data for the monthly revenue chart (current year)
            $months = [
                'January', 'February', 'March', 'April', 'May', 'June',
                'July', 'August', 'September', 'October', 'November', 'December',
            ];

            $monthlyRevenueDB = MaintenanceBill::query()
                ->where('maintenance_bills.status', config('status.maintenance_bills.paid', 'paid'))
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
                'penaltyCollected',
                'months',
                'chartDataRevenue',
                'blocks',
                'residents',
                'years'
            ));
        } catch (\Exception $e) {
            if ($e instanceof ValidationException || $e instanceof HttpExceptionInterface) {
                throw $e;
            }
            Log::error('Error in MaintenanceBillController@index: ' . $e->getMessage());

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
            }

            return redirect()->back()->with('error', 'An error occurred loading maintenance bills: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new maintenance bill.
     * Prepares data like active residents, their fees, and dynamic penalty/discount settings.
     *
     * @return Psr7Response
     */
    public function create()
    {
        abort_if(! \Auth::user()->can('maintenance_bill_create'), 403);
        try {
            // Get all active residents (filtering out old owners if a tenant lives there)
            $residents = $this->getUniqueActiveResidents();

            // Pre-calculate the base monthly fee for each resident based on flat type & area
            $residentFees = $residents->mapWithKeys(function ($resident) {
                $fee = 0;
                if ($resident->flat) {
                    $fee = $resident->flat->calculateMaintenanceFee($resident->type);
                }

                return [$resident->id => $fee];
            });

            $residentDetails = $residents->mapWithKeys(function ($resident) {
                $details = 'Basic Maintenance Fee';
                if ($resident->flat) {
                    $isCommercial = in_array(strtolower($resident->flat->unit_type ?? ''), ['shop', 'office', 'showroom', 'warehouse']);
                    $flatType = $resident->flat->flatType;
                    
                    $sqftRate = $resident->flat->maintenanceSqftRate();

                    $categoryLabel = $flatType ? $flatType->name : ucfirst($resident->flat->unit_type ?? 'Standard');
                    if ($isCommercial) {
                        $area = (float) $resident->flat->area_sqft;
                        $details = 'Category: ' . $categoryLabel . ' — Commercial Sq.Ft. Rate (' . number_format($area, 2) . ' Sq.Ft. @ ₹' . number_format($sqftRate, 2) . ')';
                    } elseif ($flatType) {
                        $details = 'Category: ' . $flatType->name . ' — Fixed Residential Rate';
                    } else {
                        $details = 'Category: ' . $categoryLabel . ' — Fixed Residential Rate';
                    }
                }
                return [$resident->id => $details];
            });

            // Load the global penalty and discount settings to pass to the frontend JavaScript
            $discountSettings = $this->getSettingValues('discount');
            $penaltySettings = $this->getSettingValues('penalty');

            // Calculate the next billed month for each resident
            $flatLastPaid = [];
            $flatEarliestUnpaid = [];

            // 1. Get the latest PAID bill for each flat
            $latestPaidBills = DB::table('maintenance_bills')
                ->join('maintenances', 'maintenance_bills.maintenance_id', '=', 'maintenances.id')
                ->where('maintenance_bills.status', 'paid')
                ->select('maintenance_bills.flat_id', 'maintenances.month', 'maintenances.year')
                ->get();
            
            foreach ($latestPaidBills as $bill) {
                try {
                    $date = Carbon::parse("1 {$bill->month} {$bill->year}");
                    if (!isset($flatLastPaid[$bill->flat_id]) || $date->gt($flatLastPaid[$bill->flat_id])) {
                        $flatLastPaid[$bill->flat_id] = $date;
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }

            $nextBilledDates = [];
            $defaultStartMonth = Setting::get('billing_start_month', Carbon::now()->format('Y-m'));

            foreach ($residents as $res) {
                if (isset($flatLastPaid[$res->flat_id])) {
                    $nextBilledDates[$res->id] = $flatLastPaid[$res->flat_id]->copy()->addMonth()->format('Y-m');
                } else {
                    $nextBilledDates[$res->id] = $defaultStartMonth;
                }
            }

            return view('maintenance_bills.create', compact('residents', 'residentFees', 'residentDetails', 'discountSettings', 'penaltySettings', 'nextBilledDates'));
        } catch (\Exception $e) {
            if ($e instanceof ValidationException || $e instanceof HttpExceptionInterface) {
                throw $e;
            }
            Log::error('Error in MaintenanceBillController@create: ' . $e->getMessage());

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
            }

            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created payment/bill in the database.
     * Handles the logic of calculating totals, splitting across multiple months, and saving.
     *
     * @return Response
     */
    public function store(StoreMaintenanceBillRequest $request)
    {
        abort_if(! \Auth::user()->can('maintenance_bill_create'), 403);
        DB::beginTransaction();

        try {
            $resident = Resident::with(['user', 'flat.flatType'])->findOrFail($request->resident_id);

            if (! $resident->flat) {
                throw new \Exception('Resident does not have a property unit (flat/shop) assigned.');
            }

            $monthlyFee = $resident->flat->calculateMaintenanceFee($resident->type);

            if ($monthlyFee <= 0 && ! $resident->flat->flatType) {
                throw new \Exception('This property unit does not have a maintenance rate configured or a valid property type assigned.');
            }

            $numberOfMonths = (int) $request->months;

            $paymentSlipPath = null;
            if ($request->hasFile('payment_slip')) {
                $paymentSlipPath = $request->file('payment_slip')->store('payment_slips', 'public');
            }

            $currentDate = Carbon::createFromDate($request->start_year, Carbon::parse($request->start_month)->month, 1);

            [$totalPenaltyAmount, $totalDiscountAmount] = $this->calculatePenaltyAndDiscount(
                $request, $monthlyFee, $numberOfMonths, $currentDate
            );

            $amountPerMonth = $monthlyFee + ($totalPenaltyAmount / $numberOfMonths) - ($totalDiscountAmount / $numberOfMonths);
            $amountPerMonth = max(0, $amountPerMonth);

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
                        'status' => 'published',
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
                        'received_by' => \Auth::id(),
                        'status' => 'paid',
                    ]
                );
            }

            DB::commit();

            $message = 'Payment recorded successfully for '.$numberOfMonths.' months.';

            return $request->ajax()
                ? response()->json(['success' => true, 'message' => $message])
                : redirect()->route('maintenance-bills.index')->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            if ($e instanceof ValidationException || $e instanceof HttpExceptionInterface) {
                throw $e;
            }
            Log::error('Error in MaintenanceBillController@store: ' . $e->getMessage());
            $message = 'Error recording payment: '.$e->getMessage();

            return $request->ajax()
                ? response()->json(['success' => false, 'message' => $message], 500)
                : redirect()->back()->with('error', $message);
        }
    }

    public function destroy($id)
    {
        abort_if(! \Auth::user()->can('maintenance_bill_delete'), 403);
        try {
            $bills = MaintenanceBill::where(function ($query) use ($id) {
                $query->where('batch_id', $id)
                    ->orWhere('id', $id);
            })->get();

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
        } catch (\Exception $e) {
            if ($e instanceof ValidationException || $e instanceof HttpExceptionInterface) {
                throw $e;
            }
            Log::error('Error in MaintenanceBillController@destroy: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    public function destroyIndividual($id)
    {
        abort_if(! \Auth::user()->can('maintenance_bill_delete'), 403);
        try {
            $bill = MaintenanceBill::findOrFail($id);
            $bill->delete();

            return response()->json([
                'success' => true,
                'message' => 'Maintenance bill deleted successfully.',
            ]);
        } catch (\Exception $e) {
            if ($e instanceof ValidationException || $e instanceof HttpExceptionInterface) {
                throw $e;
            }
            Log::error('Error in MaintenanceBillController@destroyIndividual: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    public function updateStatus(UpdateMaintenanceBillStatusRequest $request, $id)
    {
        abort_if(! \Auth::user()->can('maintenance_bill_create'), 403);
        try {
            $maintenanceBill = MaintenanceBill::findOrFail($id);

            if ($request->status === config('status.maintenance_bills.paid', 'paid') && $maintenanceBill->status !== config('status.maintenance_bills.paid', 'paid')) {

                $resType = 'owner';
                if ($maintenanceBill->resident && isset($maintenanceBill->resident->role)) {
                    $resType = $maintenanceBill->resident->role === 'rental' ? 'rental' : 'owner';
                }
                $residentRecord = Resident::where('flat_id', $maintenanceBill->flat_id)->where('user_id', $maintenanceBill->user_id)->first();
                if ($residentRecord && $residentRecord->type) {
                    $resType = $residentRecord->type;
                }
                $monthlyFee = $maintenanceBill->flat ? $maintenanceBill->flat->calculateMaintenanceFee($resType) : $maintenanceBill->amount;

                $currentDate = Carbon::createFromDate(
                    $maintenanceBill->maintenance->year,
                    Carbon::parse($maintenanceBill->maintenance->month)->month,
                    1
                );

                [$totalPenaltyAmount, $totalDiscountAmount] = $this->calculatePenaltyAndDiscount(
                    $request, $monthlyFee, 1, $currentDate, true
                );

                $maintenanceBill->status = config('status.maintenance_bills.paid', 'paid');
                $maintenanceBill->paid_at = now();
                $maintenanceBill->payment_method = $request->payment_method;
                $maintenanceBill->transaction_id = $request->transaction_id;

                if ($request->hasFile('payment_slip')) {
                    $maintenanceBill->payment_slip = $request->file('payment_slip')->store('payment_slips', 'public');
                }

                $maintenanceBill->received_by = \Auth::id();

                $maintenanceBill->penalty_amount = $totalPenaltyAmount;
                $maintenanceBill->discount_amount = $totalDiscountAmount;
                $maintenanceBill->total_amount = max(0, $monthlyFee + $totalPenaltyAmount - $totalDiscountAmount);

            } elseif ($request->status !== config('status.maintenance_bills.paid', 'paid')) {
                $maintenanceBill->status = $request->status;
                $maintenanceBill->paid_at = null;
                $maintenanceBill->payment_method = null;
                $maintenanceBill->transaction_id = null;
                $maintenanceBill->payment_slip = null;
                $maintenanceBill->received_by = null;

                $maintenanceBill->penalty_amount = 0;
                $maintenanceBill->discount_amount = 0;
                $maintenanceBill->total_amount = ($maintenanceBill->amount ?? 0);
            }

            $maintenanceBill->save();

            if ($request->ajax() || $request->expectsJson()) {
                $maintenance = Maintenance::with('maintenanceBills')->findOrFail($maintenanceBill->maintenance_id);
                $paidCount = $maintenance->maintenanceBills->where('status', config('status.maintenance_bills.paid', 'paid'))->count();
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
        } catch (\Exception $e) {
            if ($e instanceof ValidationException || $e instanceof HttpExceptionInterface) {
                throw $e;
            }
            Log::error('Error in MaintenanceBillController@updateStatus: ' . $e->getMessage());

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
            }

            return redirect()->back()->with('error', 'An error occurred updating status: ' . $e->getMessage());
        }
    }

    public function details($id)
    {
        abort_if(! \Auth::user()->can('maintenance_bill_view'), 403);
        try {
            $bill = MaintenanceBill::with(['user', 'flat.block', 'flat.flatType', 'maintenance', 'receivedBy'])->findOrFail($id);

            return view('maintenance_bills.details', compact('bill'));
        } catch (\Exception $e) {
            if ($e instanceof ValidationException || $e instanceof HttpExceptionInterface) {
                throw $e;
            }
            Log::error('Error in MaintenanceBillController@details: ' . $e->getMessage());

            return redirect()->back()->with('error', 'An error occurred loading bill details: ' . $e->getMessage());
        }
    }

    public function downloadInvoice($id)
    {
        abort_if(! \Auth::user()->can('maintenance_bill_view'), 403);
        try {
            $bills = MaintenanceBill::with(['user', 'flat.block', 'flat.flatType', 'maintenance', 'receivedBy'])
                ->where(function ($query) use ($id) {
                    $query->where('batch_id', $id)
                        ->orWhere('id', $id);
                })
                ->orderBy('id', 'asc')
                ->get();

            if ($bills->isEmpty()) {
                abort(404);
            }

            $bill = $bills->first();
            $pdf = Pdf::loadView('maintenance_bills.invoice_pdf', compact('bills', 'bill'));
            $fileName = 'invoice_'.($bill->flat->block->block_name ?? '').'-'.($bill->flat->flat_no ?? '').'_'.now()->format('Ymd_His').'.pdf';

            return $pdf->download($fileName);
        } catch (\Exception $e) {
            if ($e instanceof ValidationException || $e instanceof HttpExceptionInterface) {
                throw $e;
            }
            Log::error('Error in MaintenanceBillController@downloadInvoice: ' . $e->getMessage());

            return redirect()->back()->with('error', 'An error occurred downloading invoice: ' . $e->getMessage());
        }
    }

    public function getResidentInfo($userId)
    {
        abort_if(! \Auth::user()->can('maintenance_bill_view'), 403);
        try {
            $resident = Resident::with('flat.flatType')
                ->where('user_id', $userId)
                ->where(function (Builder $query) {
                    $query->whereNull('move_out_date')
                        ->orWhere('move_out_date', '>=', Carbon::now()->startOfDay());
                })->first();

            if ($resident && $resident->flat) {
                $amount = $resident->flat->calculateMaintenanceFee($resident->type);
                $details = 'Basic Maintenance Fee';
                $isCommercial = in_array(strtolower($resident->flat->unit_type ?? ''), ['shop', 'office', 'showroom', 'warehouse']);
                $flatType = $resident->flat->flatType;
                
                $sqftRate = $resident->flat->maintenanceSqftRate();

                $categoryLabel = $flatType ? $flatType->name : ucfirst($resident->flat->unit_type ?? 'Standard');
                if ($isCommercial) {
                    $area = (float) $resident->flat->area_sqft;
                    $details = 'Category: ' . $categoryLabel . ' — Commercial Sq.Ft. Rate (' . number_format($area, 2) . ' Sq.Ft. @ ₹' . number_format($sqftRate, 2) . ')';
                } elseif ($flatType) {
                    $details = 'Category: ' . $flatType->name . ' — Fixed Residential Rate';
                } else {
                    $details = 'Category: ' . $categoryLabel . ' — Fixed Residential Rate';
                }

                return response()->json([
                    'success' => true,
                    'block_id' => $resident->block_id,
                    'flat_id' => $resident->flat_id,
                    'amount' => $amount,
                    'details' => $details,
                ]);
            }

            return response()->json(['success' => false, 'message' => 'Resident not found or flat/flat type missing.']);
        } catch (\Exception $e) {
            if ($e instanceof ValidationException || $e instanceof HttpExceptionInterface) {
                throw $e;
            }
            Log::error('Error in MaintenanceBillController@getResidentInfo: ' . $e->getMessage());

            return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    private function getSettingValues(string $type): array
    {
        $defaults = Setting::defaults();

        return [
            "apply_{$type}" => Setting::get("apply_{$type}", '1'),
            'type' => Setting::get("{$type}_type", 'percentage'),

            'yearly_value' => (float) Setting::get("{$type}_yearly_value", $defaults["{$type}_yearly_value"] ?? 0),
            'half_yearly_value' => (float) Setting::get("{$type}_half_yearly_value", $defaults["{$type}_half_yearly_value"] ?? 0),
            'quarterly_value' => (float) Setting::get("{$type}_quarterly_value", $defaults["{$type}_quarterly_value"] ?? 0),
            'monthly_value' => $type === 'discount' ? 0 : (float) Setting::get("{$type}_monthly_value", $defaults["{$type}_monthly_value"] ?? 0),

            'yearly_enabled' => Setting::get("{$type}_yearly_enabled", '1') == '1',
            'half_yearly_enabled' => Setting::get("{$type}_half_yearly_enabled", '1') == '1',
            'quarterly_enabled' => Setting::get("{$type}_quarterly_enabled", '1') == '1',
            'monthly_enabled' => $type === 'discount' ? false : (Setting::get("{$type}_monthly_enabled", '1') == '1'),
        ];
    }

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
            $sortedResidents = $flatResidents->sortByDesc(function ($res) {
                return [
                    is_null($res->move_out_date) ? 1 : 0,
                    $res->move_in_date,
                ];
            });

            $tenant = $sortedResidents->where('type', 'rental')->first();
            $uniqueResidents->push($tenant ?: $sortedResidents->first());
        }

        return $uniqueResidents->sortBy(function ($resident) {
            return $resident->user->name ?? '';
        })->values();
    }

    private function calculatePenaltyAndDiscount(
        Request $request, float $monthlyFee, int $numberOfMonths, Carbon $startDate, bool $forceRecalculation = false
    ): array {
        $now = Carbon::now()->startOfMonth();

        $pastMonthsCount = 0;
        $currentMonthCount = 0;
        $futureMonthsCount = 0;

        $tempDate = $startDate->copy()->startOfMonth();
        for ($i = 0; $i < $numberOfMonths; $i++) {
            if ($tempDate->lt($now)) {
                $pastMonthsCount++;
            } elseif ($tempDate->equalTo($now)) {
                $currentMonthCount++;
            } else {
                $futureMonthsCount++;
            }
            $tempDate->addMonth();
        }

        $totalPenaltyAmount = 0;

        if (! $forceRecalculation && $request->has('penalty_amount') && $request->filled('penalty_amount')) {
            $totalPenaltyAmount = (float) $request->penalty_amount;
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
                        $totalPenaltyAmount = (float) $penaltyValue;
                    } else {
                        $totalArrearsAmount = $pastMonthsCount * $monthlyFee;
                        $totalPenaltyAmount = $totalArrearsAmount * ($penaltyValue / 100);
                    }
                }
            }
        }

        $totalDiscountAmount = 0;

        if (! $forceRecalculation && $request->has('discount_amount') && $request->filled('discount_amount')) {
            $totalDiscountAmount = (float) $request->discount_amount;
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
                }

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
