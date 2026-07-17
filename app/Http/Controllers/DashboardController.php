<?php

namespace App\Http\Controllers;

use App\Helpers\CurrencyHelper;
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

            $totalRevenue = MaintenanceBill::where('status', config('status.maintenance_bills.paid'))->sum('total_amount')
                + NameTransferBill::where('status', config('status.name_transfer_bills.paid'))->sum('amount');
            $totalExpenses = Expense::sum('total_amount');
            $totalAvailableFund = $totalRevenue - $totalExpenses;

            // Revenue Chart Data (Current Year)
            $months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

            $monthlyRevenueDB = MaintenanceBill::where('maintenance_bills.status', config('status.maintenance_bills.paid'))
                ->join('maintenances', 'maintenance_bills.maintenance_id', '=', 'maintenances.id')
                ->where('maintenances.year', date('Y'))
                ->selectRaw('maintenances.month, sum(maintenance_bills.total_amount) as total')
                ->groupBy('maintenances.month')
                ->pluck('total', 'month')
                ->toArray();

            $transferRevenueDB = NameTransferBill::where('status', config('status.name_transfer_bills.paid'))
                ->whereYear(DB::raw('COALESCE(updated_at, created_at)'), date('Y'))
                ->selectRaw('MONTHNAME(COALESCE(updated_at, created_at)) as month, sum(amount) as total')
                ->groupBy('month')
                ->pluck('total', 'month')
                ->toArray();

            // Expense Chart Data (Current Year)
            $monthlyExpensesDB = Expense::whereYear(DB::raw('COALESCE(expense_date, created_at)'), date('Y'))
                ->selectRaw('MONTHNAME(COALESCE(expense_date, created_at)) as month, sum(total_amount) as total')
                ->groupBy('month')
                ->pluck('total', 'month')
                ->toArray();

            $chartDataRevenue = [];
            $chartDataExpenses = [];

            foreach ($months as $month) {
                $chartDataRevenue[] = ($monthlyRevenueDB[$month] ?? 0) + ($transferRevenueDB[$month] ?? 0);
                $chartDataExpenses[] = $monthlyExpensesDB[$month] ?? 0;
            }

            // Bill Status Chart Data
            $paidBills = MaintenanceBill::where('status', config('status.maintenance_bills.paid'))->count();
            $pendingBills = MaintenanceBill::where('status', config('status.maintenance_bills.pending'))->count();
            $overdueBills = MaintenanceBill::where('status', config('status.maintenance_bills.overdue'))->count();
            $billStatusData = [
                'paid' => $paidBills,
                'pending' => $pendingBills,
                'overdue' => $overdueBills,
            ];

            // Flat Occupancy Chart Data (Auto-syncs and calculates occupied vs empty accurately based on residents)
            $occupancyData = Flat::syncAllOccupancyStatus();

            // Expense Breakdown Chart Data
            $expensesByCategory = Expense::join('expense_categories', 'expenses.category_id', '=', 'expense_categories.id')
                ->selectRaw('expense_categories.title, sum(expenses.total_amount) as total')
                ->groupBy('expense_categories.title')
                ->pluck('total', 'title');

            $expenseBreakdownLabels = $expensesByCategory->keys()->toArray();
            $expenseBreakdownData = $expensesByCategory->values()->toArray();

            // Recent Activity Feed
            $recentPayments = MaintenanceBill::with('user', 'flat', 'block')
                ->where('status', config('status.maintenance_bills.paid'))
                ->latest('updated_at')
                ->take(30)
                ->get()
                ->groupBy(function ($bill) {
                    // Group bills paid by the same resident for the same flat within the same minute into a single activity entry
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

            // Fetch recent complaints and map them to activity feed format
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

            $unapprovedTransferUserIds = NameTransferBill::where(function ($q) {
                $q->where('is_approved', false)->orWhereNull('is_approved');
            })->pluck('new_owner_id')->filter()->toArray();

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

            $activities = $recentPayments->concat($recentComplaints)->concat($recentUsers)->concat($recentTransfers)
                ->sortByDesc('timestamp')
                ->take(8)
                ->values();

            // Live Cashflow & Net-Worth Ledger Metrics
            $thisMonthRevenue = MaintenanceBill::where('status', config('status.maintenance_bills.paid'))
                ->whereMonth('updated_at', now()->month)
                ->whereYear('updated_at', now()->year)
                ->sum('total_amount')
                + NameTransferBill::where('status', config('status.name_transfer_bills.paid'))
                ->where('is_approved', true)
                ->whereMonth('updated_at', now()->month)
                ->whereYear('updated_at', now()->year)
                ->sum('amount');

            $thisMonthPenalty = MaintenanceBill::where('status', config('status.maintenance_bills.paid'))
                ->whereMonth('updated_at', now()->month)
                ->whereYear('updated_at', now()->year)
                ->sum('penalty_amount');

            $totalPenaltyRevenue = MaintenanceBill::where('status', config('status.maintenance_bills.paid'))->sum('penalty_amount');

            $thisMonthTransfer = NameTransferBill::where('status', config('status.name_transfer_bills.paid'))
                ->where('is_approved', true)
                ->whereMonth('updated_at', now()->month)
                ->whereYear('updated_at', now()->year)
                ->sum('amount');

            $totalTransferRevenue = NameTransferBill::where('status', config('status.name_transfer_bills.paid'))
                ->where('is_approved', true)
                ->sum('amount');

            $thisMonthExpense = Expense::whereMonth(DB::raw('COALESCE(expense_date, created_at)'), now()->month)
                ->whereYear(DB::raw('COALESCE(expense_date, created_at)'), now()->year)
                ->sum('total_amount');

            $thisMonthNet = $thisMonthRevenue - $thisMonthExpense;
            $cashflowStatus = $thisMonthNet >= 0 ? 'Surplus (+)' : 'Deficit (-)';
            $cashflowColor = $thisMonthNet >= 0 ? 'success' : 'danger';

            $recentIncomeBills = MaintenanceBill::with('user', 'flat', 'block')
                ->where('status', config('status.maintenance_bills.paid'))
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

            $recentTransferIncome = NameTransferBill::with('flat.block', 'newOwner')
                ->where('status', config('status.name_transfer_bills.paid'))
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

            $ledgerTransactions = $recentIncomeBills->concat($recentTransferIncome)->concat($recentExpenseItems)
                ->sortByDesc('timestamp')
                ->take(10)
                ->values();

            return view('dashboard', compact(
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
                'totalTransferRevenue'
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
