<?php

namespace App\Http\Controllers;

use App\Helpers\CurrencyHelper;
use App\Helpers\ModuleHelper;
use App\Models\Block;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Flat;
use App\Models\Complain;
use App\Models\MaintenanceBill;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Maintenance;
use App\Models\NameTransferBill;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class DashboardController extends Controller
{
    public function index()
    {
        abort_if(! \Auth::user()->can('dashboard_view'), 403);
        try {
            $totalFlats = Flat::count();
            $totalResidents = Flat::whereHas('residents', function ($query) {
                $query->whereNull('move_out_date')->orWhere('move_out_date', '>=', now()->startOfDay());
            })->count();
            $totalComplaints = Complain::where('status', '!=', config('status.complaints.resolved'))->count();

            $totalOccupiedFlats = Flat::where('status', config('status.flats.occupied'))->count();
            $blocks = Block::withCount([
                'flats',
                'flats as occupied_flats_count' => function ($query) {
                    $query->where('status', config('status.flats.occupied'));
                },
            ])->get();

            $isFinanceActive = ModuleHelper::isFinanceActive()
                && ModuleHelper::hasModel(MaintenanceBill::class, 'maintenance_bills');

            $months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

            if ($isFinanceActive) {
                $totalRevenue = MaintenanceBill::where('status', config('status.maintenance_bills.paid', 'paid'))->sum('total_amount')
                    + (ModuleHelper::hasModel(NameTransferBill::class, 'name_transfer_bills') ? NameTransferBill::where('status', config('status.name_transfer_bills.paid', 'paid'))->sum('amount') : 0);
                $totalExpenses = ModuleHelper::hasModel(Expense::class, 'expenses') ? Expense::sum('total_amount') : 0;
                $totalAvailableFund = $totalRevenue - $totalExpenses;

                // Revenue Chart Data (Current Year)
                $monthlyRevenueDB = MaintenanceBill::where('maintenance_bills.status', config('status.maintenance_bills.paid', 'paid'))
                    ->join('maintenances', 'maintenance_bills.maintenance_id', '=', 'maintenances.id')
                    ->where('maintenances.year', date('Y'))
                    ->selectRaw('maintenances.month, sum(maintenance_bills.total_amount) as total')
                    ->groupBy('maintenances.month')
                    ->pluck('total', 'month')
                    ->toArray();

                $transferRevenueDB = [];
                if (ModuleHelper::hasModel(NameTransferBill::class, 'name_transfer_bills')) {
                    $transferRevenueDB = NameTransferBill::where('status', config('status.name_transfer_bills.paid', 'paid'))
                        ->whereYear(DB::raw('COALESCE(updated_at, created_at)'), date('Y'))
                        ->selectRaw('MONTHNAME(COALESCE(updated_at, created_at)) as month, sum(amount) as total')
                        ->groupBy('month')
                        ->pluck('total', 'month')
                        ->toArray();
                }

                // Expense Chart Data (Current Year)
                $monthlyExpensesDB = [];
                if (ModuleHelper::hasModel(Expense::class, 'expenses')) {
                    $monthlyExpensesDB = Expense::whereYear(DB::raw('COALESCE(expense_date, created_at)'), date('Y'))
                        ->selectRaw('MONTHNAME(COALESCE(expense_date, created_at)) as month, sum(total_amount) as total')
                        ->groupBy('month')
                        ->pluck('total', 'month')
                        ->toArray();
                }

                $chartDataRevenue = [];
                $chartDataExpenses = [];

                foreach ($months as $month) {
                    $chartDataRevenue[] = ($monthlyRevenueDB[$month] ?? 0) + ($transferRevenueDB[$month] ?? 0);
                    $chartDataExpenses[] = $monthlyExpensesDB[$month] ?? 0;
                }

                // Bill Status Chart Data
                $paidBills = MaintenanceBill::where('status', config('status.maintenance_bills.paid', 'paid'))->count();
                $pendingBills = MaintenanceBill::where('status', config('status.maintenance_bills.pending', 'pending'))->count();
                $overdueBills = MaintenanceBill::where('status', config('status.maintenance_bills.overdue', 'overdue'))->count();
                $billStatusData = [
                    'paid' => $paidBills,
                    'pending' => $pendingBills,
                    'overdue' => $overdueBills,
                ];

                // Expense Breakdown Chart Data
                $expensesByCategory = collect();
                if (ModuleHelper::hasModel(Expense::class, 'expenses') && ModuleHelper::hasModel(ExpenseCategory::class, 'expense_categories')) {
                    $expensesByCategory = Expense::join('expense_categories', 'expenses.category_id', '=', 'expense_categories.id')
                        ->selectRaw('expense_categories.title, sum(expenses.total_amount) as total')
                        ->groupBy('expense_categories.title')
                        ->pluck('total', 'title');
                }

                $expenseBreakdownLabels = $expensesByCategory->keys()->toArray();
                $expenseBreakdownData = $expensesByCategory->values()->toArray();

                // Recent Payment Activities
                $recentPayments = MaintenanceBill::with('user', 'flat', 'block')
                    ->where('status', config('status.maintenance_bills.paid', 'paid'))
                    ->latest('updated_at')
                    ->take(30)
                    ->get()
                    ->groupBy(function ($bill) {
                        $timeKey = $bill->paid_at ? $bill->paid_at->format('Y-m-d_H:i') : $bill->updated_at->format('Y-m-d_H:i');
                        return 'user_' . $bill->user_id . '_flat_' . $bill->flat_id . '_' . $timeKey;
                    })
                    ->take(4)
                    ->map(function ($billsGroup) {
                        $bill = $billsGroup->first();
                        $totalAmount = $billsGroup->sum('total_amount');
                        $monthsCount = $billsGroup->count();
                        $residentName = $bill->user?->name ?? 'Unknown Resident';
                        $flatNo = ($bill->block ? $bill->block->block_name . '-' : '') . ($bill->flat?->flat_no ?? 'N/A');
                        $durationText = $monthsCount > 1 ? " ({$monthsCount} months)" : "";

                        return (object) [
                            'type' => 'payment',
                            'icon' => 'fa-solid fa-money-bill-wave text-success',
                            'title' => 'Payment Received',
                            'description' => "{$residentName} (Flat #{$flatNo}) paid " . CurrencyHelper::formatCurrency($totalAmount) . $durationText,
                            'time' => Carbon::parse($bill->updated_at ?? $bill->created_at ?? now())->diffForHumans(),
                            'timestamp' => Carbon::parse($bill->updated_at ?? $bill->created_at ?? now())
                        ];
                    })
                    ->values();

                $unapprovedTransferUserIds = [];
                $recentTransfers = collect();
                if (ModuleHelper::hasModel(NameTransferBill::class, 'name_transfer_bills')) {
                    $unapprovedTransferUserIds = NameTransferBill::where(function ($q) {
                        $q->where('is_approved', false)->orWhereNull('is_approved');
                    })->pluck('new_owner_id')->filter()->toArray();

                    $recentTransfers = NameTransferBill::with('flat.block', 'oldOwner', 'newOwner')
                        ->where('is_approved', true)
                        ->latest('updated_at')
                        ->take(4)
                        ->get()
                        ->map(function ($transfer) {
                            $oldName = $transfer->oldOwner?->name ?? 'Previous Owner';
                            $newName = $transfer->newOwner?->name ?? 'New Owner';
                            $flatNo = ($transfer->flat?->block ? $transfer->flat->block->block_name . '-' : '') . ($transfer->flat?->flat_no ?? 'N/A');

                            return (object) [
                                'type' => 'transfer',
                                'icon' => 'fa-solid fa-right-left text-warning',
                                'title' => 'Ownership Transferred',
                                'description' => "Flat #{$flatNo} transferred from {$oldName} to {$newName}",
                                'time' => Carbon::parse($transfer->updated_at ?? $transfer->created_at ?? now())->diffForHumans(),
                                'timestamp' => Carbon::parse($transfer->updated_at ?? $transfer->created_at ?? now())
                            ];
                        });
                }

                // Live Cashflow Metrics
                $thisMonthRevenue = MaintenanceBill::where('status', config('status.maintenance_bills.paid', 'paid'))
                    ->whereMonth('updated_at', now()->month)
                    ->whereYear('updated_at', now()->year)
                    ->sum('total_amount')
                    + (ModuleHelper::hasModel(NameTransferBill::class, 'name_transfer_bills') ? NameTransferBill::where('status', config('status.name_transfer_bills.paid', 'paid'))
                        ->where('is_approved', true)
                        ->whereMonth('updated_at', now()->month)
                        ->whereYear('updated_at', now()->year)
                        ->sum('amount') : 0);

                $thisMonthPenalty = MaintenanceBill::where('status', config('status.maintenance_bills.paid', 'paid'))
                    ->whereMonth('updated_at', now()->month)
                    ->whereYear('updated_at', now()->year)
                    ->sum('penalty_amount');

                $totalPenaltyRevenue = MaintenanceBill::where('status', config('status.maintenance_bills.paid', 'paid'))->sum('penalty_amount');

                $thisMonthTransfer = ModuleHelper::hasModel(NameTransferBill::class, 'name_transfer_bills') ? NameTransferBill::where('status', config('status.name_transfer_bills.paid', 'paid'))
                    ->where('is_approved', true)
                    ->whereMonth('updated_at', now()->month)
                    ->whereYear('updated_at', now()->year)
                    ->sum('amount') : 0;

                $totalTransferRevenue = ModuleHelper::hasModel(NameTransferBill::class, 'name_transfer_bills') ? NameTransferBill::where('status', config('status.name_transfer_bills.paid', 'paid'))
                    ->where('is_approved', true)
                    ->sum('amount') : 0;

                $thisMonthExpense = ModuleHelper::hasModel(Expense::class, 'expenses') ? Expense::whereMonth(DB::raw('COALESCE(expense_date, created_at)'), now()->month)
                    ->whereYear(DB::raw('COALESCE(expense_date, created_at)'), now()->year)
                    ->sum('total_amount') : 0;

                $thisMonthNet = $thisMonthRevenue - $thisMonthExpense;
                $cashflowStatus = $thisMonthNet >= 0 ? 'Surplus (+)' : 'Deficit (-)';
                $cashflowColor = $thisMonthNet >= 0 ? 'success' : 'danger';

                $recentIncomeBills = MaintenanceBill::with('user', 'flat', 'block')
                    ->where('status', config('status.maintenance_bills.paid', 'paid'))
                    ->latest('updated_at')
                    ->take(30)
                    ->get()
                    ->groupBy(function ($bill) {
                        return ($bill->user_id ?? '0') . '_' . ($bill->flat_id ?? '0') . '_' . $bill->updated_at->format('Y-m-d');
                    })
                    ->flatMap(function ($group) {
                        $firstBill = $group->first();
                        $flatNo = ($firstBill->block ? $firstBill->block->block_name . '-' : '') . ($firstBill->flat?->flat_no ?? 'N/A');
                        $userName = $firstBill->user?->name ?? 'Resident';

                        $totalBaseAmount = $group->sum(function ($b) {
                            return (float) $b->total_amount - (float) $b->penalty_amount;
                        });
                        $totalPenaltyAmount = $group->sum('penalty_amount');
                        $totalAmount = $group->sum('total_amount');

                        $latestTimestamp = $group->max('updated_at');

                        $records = [];

                        if ($totalBaseAmount > 0) {
                            $records[] = (object) [
                                'type' => 'income',
                                'category' => 'Maintenance Fee',
                                'title' => $userName . " (Flat #{$flatNo})",
                                'amount' => $totalBaseAmount,
                                'timestamp' => Carbon::parse($latestTimestamp ?? now()),
                                'time' => Carbon::parse($latestTimestamp ?? now())->diffForHumans()
                            ];
                        }

                        if ($totalPenaltyAmount > 0) {
                            $records[] = (object) [
                                'type' => 'income',
                                'category' => 'Penalty Fee',
                                'title' => "Late Penalty - " . $userName . " (Flat #{$flatNo})",
                                'amount' => $totalPenaltyAmount,
                                'timestamp' => Carbon::parse($latestTimestamp ?? now()),
                                'time' => Carbon::parse($latestTimestamp ?? now())->diffForHumans()
                            ];
                        }

                        if (empty($records)) {
                            $records[] = (object) [
                                'type' => 'income',
                                'category' => 'Maintenance Fee',
                                'title' => $userName . " (Flat #{$flatNo})",
                                'amount' => $totalAmount,
                                'timestamp' => Carbon::parse($latestTimestamp ?? now()),
                                'time' => Carbon::parse($latestTimestamp ?? now())->diffForHumans()
                            ];
                        }

                        return $records;
                    });

                $recentTransferIncome = collect();
                if (ModuleHelper::hasModel(NameTransferBill::class, 'name_transfer_bills')) {
                    $recentTransferIncome = NameTransferBill::with('flat.block', 'newOwner')
                        ->where('status', config('status.name_transfer_bills.paid', 'paid'))
                        ->where('is_approved', true)
                        ->latest('updated_at')
                        ->take(4)
                        ->get()
                        ->map(function ($bill) {
                            $flatNo = ($bill->flat?->block ? $bill->flat->block->block_name . '-' : '') . ($bill->flat?->flat_no ?? 'N/A');
                            return (object) [
                                'type' => 'income',
                                'category' => 'Transfer Fee',
                                'title' => "Ownership Transfer (Flat #{$flatNo})",
                                'amount' => $bill->amount,
                                'timestamp' => Carbon::parse($bill->updated_at ?? $bill->created_at ?? now()),
                                'time' => Carbon::parse($bill->updated_at ?? $bill->created_at ?? now())->diffForHumans()
                            ];
                        });
                }

                $recentExpenseItems = collect();
                if (ModuleHelper::hasModel(Expense::class, 'expenses')) {
                    $recentExpenseItems = Expense::with('category')
                        ->latest('created_at')
                        ->take(8)
                        ->get()
                        ->map(function ($exp) {
                            return (object) [
                                'type' => 'expense',
                                'category' => $exp->category?->title ?? 'General Expense',
                                'title' => $exp->title ?? 'Society Expenditure',
                                'amount' => $exp->total_amount,
                                'timestamp' => Carbon::parse($exp->created_at ?? $exp->updated_at ?? now()),
                                'time' => Carbon::parse($exp->created_at ?? $exp->updated_at ?? now())->diffForHumans()
                            ];
                        });
                }

                $ledgerTransactions = $recentIncomeBills->concat($recentTransferIncome)->concat($recentExpenseItems)
                    ->sortByDesc('timestamp')
                    ->take(10)
                    ->values();
            } else {
                $totalRevenue = 0;
                $totalExpenses = 0;
                $totalAvailableFund = 0;
                $chartDataRevenue = array_fill(0, 12, 0);
                $chartDataExpenses = array_fill(0, 12, 0);
                $billStatusData = ['paid' => 0, 'pending' => 0, 'overdue' => 0];
                $expenseBreakdownLabels = [];
                $expenseBreakdownData = [];
                $recentPayments = collect();
                $recentTransfers = collect();
                $unapprovedTransferUserIds = [];
                $thisMonthRevenue = 0;
                $thisMonthPenalty = 0;
                $totalPenaltyRevenue = 0;
                $thisMonthTransfer = 0;
                $totalTransferRevenue = 0;
                $thisMonthExpense = 0;
                $thisMonthNet = 0;
                $cashflowStatus = 'N/A';
                $cashflowColor = 'secondary';
                $ledgerTransactions = collect();
            }

            // Flat Occupancy Chart Data
            $occupancyData = Flat::syncAllOccupancyStatus();

            // Fetch recent complaints
            $recentComplaints = Complain::with('user')
                ->latest('created_at')
                ->take(4)
                ->get()
                ->map(function ($complain) {
                    $userName = $complain->user?->name ?? 'Resident';
                    return (object) [
                        'type' => 'complain',
                        'icon' => 'fa-solid fa-exclamation-circle text-danger',
                        'title' => 'New Complaint Logged',
                        'description' => "{$userName}: \"{$complain->subject}\"",
                        'time' => Carbon::parse($complain->created_at ?? $complain->updated_at ?? now())->diffForHumans(),
                        'timestamp' => Carbon::parse($complain->created_at ?? $complain->updated_at ?? now())
                    ];
                });

            // Fetch recent users
            $recentUsers = User::whereNotIn('id', $unapprovedTransferUserIds)
                ->latest('updated_at')
                ->take(3)
                ->get()
                ->map(function ($user) {
                    $roleLabel = ucfirst($user->role);
                    return (object) [
                        'type' => 'user',
                        'icon' => 'fa-solid fa-user-check text-primary',
                        'title' => 'New Resident Registered',
                        'description' => "{$user->name} joined as {$roleLabel}",
                        'time' => Carbon::parse($user->updated_at ?? $user->created_at ?? now())->diffForHumans(),
                        'timestamp' => Carbon::parse($user->updated_at ?? $user->created_at ?? now())
                    ];
                });

            $activities = $recentPayments->concat($recentComplaints)->concat($recentUsers)->concat($recentTransfers)
                ->sortByDesc('timestamp')
                ->take(8)
                ->values();

            return view('dashboard', compact(
                'isFinanceActive',
                'totalResidents',
                'totalFlats',
                'totalComplaints',
                'totalRevenue',
                'totalExpenses',
                'totalAvailableFund',
                'months',
                'chartDataRevenue',
                'chartDataExpenses',
                'billStatusData',
                'occupancyData',
                'expenseBreakdownLabels',
                'expenseBreakdownData',
                'activities',
                'thisMonthRevenue',
                'thisMonthExpense',
                'thisMonthNet',
                'cashflowStatus',
                'cashflowColor',
                'ledgerTransactions',
                'thisMonthPenalty',
                'totalPenaltyRevenue',
                'thisMonthTransfer',
                'totalTransferRevenue',
                'blocks',
                'totalOccupiedFlats'
            ));
        } catch (\Exception $e) {
            if ($e instanceof ValidationException || $e instanceof HttpExceptionInterface) {
                throw $e;
            }
            Log::error('Error in DashboardController@index: ' . $e->getMessage());

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
            }

            return redirect()->back()->with('error', 'An error occurred loading the dashboard: ' . $e->getMessage());
        }
    }
}