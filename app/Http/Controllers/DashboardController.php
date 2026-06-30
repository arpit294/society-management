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

            // Revenue Chart Data (Current Year)
            $months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

            $monthlyRevenueDB = MaintenanceBill::where('maintenance_bills.status', config('status.maintenance_bills.paid'))
                ->join('maintenances', 'maintenance_bills.maintenance_id', '=', 'maintenances.id')
                ->where('maintenances.year', date('Y'))
                ->selectRaw('maintenances.month, sum(maintenance_bills.total_amount) as total')
                ->groupBy('maintenances.month')
                ->pluck('total', 'month')
                ->toArray();

            // Expense Chart Data (Current Year)
            $monthlyExpensesDB = Expense::whereYear('created_at', date('Y'))
                ->selectRaw('MONTHNAME(created_at) as month, sum(total_amount) as total')
                ->groupBy('month')
                ->pluck('total', 'month')
                ->toArray();

            $chartDataRevenue = [];
            $chartDataExpenses = [];

            foreach ($months as $month) {
                $chartDataRevenue[] = $monthlyRevenueDB[$month] ?? 0;
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

            // Flat Occupancy Chart Data
            $occupiedFlats = Flat::where('status', config('status.flats.occupied'))->count();
            $emptyFlats = Flat::where('status', config('status.flats.empty'))->count();
            $occupancyData = [
                'occupied' => $occupiedFlats,
                'empty' => $emptyFlats,
            ];

            // Expense Breakdown Chart Data
            $expensesByCategory = Expense::join('expense_categories', 'expenses.category_id', '=', 'expense_categories.id')
                ->selectRaw('expense_categories.title, sum(expenses.total_amount) as total')
                ->groupBy('expense_categories.title')
                ->pluck('total', 'title');

            $expenseBreakdownLabels = $expensesByCategory->keys()->toArray();
            $expenseBreakdownData = $expensesByCategory->values()->toArray();

            // Recent Activity Feed
            $recentPayments = MaintenanceBill::with('user', 'flat')
                ->where('status', config('status.maintenance_bills.paid'))
                ->latest('updated_at')
                ->take(4)
                ->get()
                ->map(function ($bill) {
                    $residentName = $bill->user?->name ?? 'Unknown Resident';
                    $flatNo = $bill->flat?->flat_no ?? 'N/A';
                    return (object) [
                        'type' => 'payment',
                        'icon' => 'fa-solid fa-money-bill-wave text-success',
                        'title' => 'Payment Received',
                        'description' => "{$residentName} (Flat #{$flatNo}) paid " . CurrencyHelper::formatCurrency($bill->total_amount),
                        'time' => $bill->updated_at->diffForHumans(),
                        'timestamp' => $bill->updated_at
                    ];
                });

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
                        'time' => $complain->created_at->diffForHumans(),
                        'timestamp' => $complain->created_at
                    ];
                });

            $recentUsers = User::latest('created_at')
                ->take(3)
                ->get()
                ->map(function ($user) {
                    $roleLabel = ucfirst($user->role);
                    return (object) [
                        'type' => 'user',
                        'icon' => 'fa-solid fa-user-check text-primary',
                        'title' => 'New Resident Registered',
                        'description' => "{$user->name} joined as {$roleLabel}",
                        'time' => $user->created_at->diffForHumans(),
                        'timestamp' => $user->created_at
                    ];
                });

            $activities = $recentPayments->concat($recentComplaints)->concat($recentUsers)
                ->sortByDesc('timestamp')
                ->take(6)
                ->values();

            return view('dashboard', compact(
                'totalResidents',
                'totalFlats',
                'totalComplaints',
                'totalRevenue',
                'totalExpenses',
                'months',
                'chartDataRevenue',
                'chartDataExpenses',
                'billStatusData',
                'occupancyData',
                'expenseBreakdownLabels',
                'expenseBreakdownData',
                'activities'
            ));
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Validation\ValidationException || $e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
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
