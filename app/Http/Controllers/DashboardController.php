<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Flat;
use App\Models\Complain;
use App\Models\MaintenanceBill;
use App\Models\Expense;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalResidents = User::count();
        $totalFlats = Flat::count();
        $totalComplaints = Complain::where('status', '!=', 'resolved')->count();
        
        $totalRevenue = MaintenanceBill::where('status', 'paid')->sum('total_amount');
        $totalExpenses = Expense::sum('total_amount');

        // Revenue Chart Data (Current Year)
        $months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        
        $monthlyRevenueDB = MaintenanceBill::where('maintenance_bills.status', 'paid')
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
        
        foreach ($months as $m) {
            $chartDataRevenue[] = $monthlyRevenueDB[$m] ?? 0;
            $chartDataExpenses[] = $monthlyExpensesDB[$m] ?? 0;
        }

        // Financial Upgrade: Maintenance Collected vs Pending (Based on Latest Maintenance)
        $latestMaintenance = \App\Models\Maintenance::orderBy('year', 'desc')->orderBy('id', 'desc')->first();
        $maintenanceId = $latestMaintenance ? $latestMaintenance->id : null;

        $billStatusData = [
            'paid' => 0,
            'pending' => 0,
        ];
        if ($maintenanceId) {
            $billStatusData['paid'] = (float) MaintenanceBill::where('maintenance_id', $maintenanceId)->where('status', 'paid')->sum('total_amount');
            $billStatusData['pending'] = (float) MaintenanceBill::where('maintenance_id', $maintenanceId)->where('status', 'pending')->sum('total_amount');
        }

        // Occupancy Rates (Empty vs Occupied Flats)
        $occupiedFlatsCount = Flat::whereHas('residents', function ($query) {
            $query->whereNull('move_out_date')->orWhere('move_out_date', '>=', now()->startOfDay());
        })->count();
        $emptyFlatsCount = max(0, $totalFlats - $occupiedFlatsCount);
        
        $occupancyData = [
            'occupied' => $occupiedFlatsCount,
            'empty' => $emptyFlatsCount,
        ];

        // Expense Breakdown (Pie Chart) - Current Year
        $expenseCategories = \App\Models\ExpenseCategory::all()->keyBy('id');
        $expensesByCategory = \App\Models\Expense::whereYear('created_at', date('Y'))
            ->selectRaw('category_id, sum(total_amount) as total')
            ->groupBy('category_id')
            ->get();
            
        $expenseBreakdownLabels = [];
        $expenseBreakdownData = [];
        foreach ($expensesByCategory as $expense) {
            $catName = isset($expenseCategories[$expense->category_id]) ? $expenseCategories[$expense->category_id]->title : 'Uncategorized';
            $expenseBreakdownLabels[] = $catName;
            $expenseBreakdownData[] = (float) $expense->total;
        }

        // Fetch Recent Activities
        $recentPayments = MaintenanceBill::with('user')
            ->where('status', 'paid')
            ->select('batch_id', 'user_id', DB::raw('MAX(status) as status'), DB::raw('MAX(updated_at) as updated_at'), DB::raw('SUM(total_amount) as total_amount'))
            ->groupBy('batch_id', 'user_id')
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($payment) {
                // Ensure updated_at is a Carbon instance since MAX() might return a string
                $timestamp = \Carbon\Carbon::parse($payment->updated_at);
                return (object)[
                    'icon' => 'fa-solid fa-money-bill-wave text-success',
                    'title' => 'Payment Received',
                    'description' => '₹' . number_format($payment->total_amount, 2) . ' from ' . ($payment->user->name ?? 'Unknown'),
                    'time' => $timestamp->diffForHumans(),
                    'timestamp' => $timestamp
                ];
            });

        $recentComplaints = Complain::with('user')
            ->where('status', '!=', 'resolved')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($complain) {
                return (object)[
                    'icon' => 'fa-solid fa-triangle-exclamation text-warning',
                    'title' => 'New Complaint',
                    'description' => $complain->title . ' by ' . ($complain->user->name ?? 'Unknown'),
                    'time' => $complain->created_at->diffForHumans(),
                    'timestamp' => $complain->created_at
                ];
            });

        $recentUsers = User::latest()
            ->take(5)
            ->get()
            ->map(function ($user) {
                return (object)[
                    'icon' => 'fa-solid fa-user-plus text-info',
                    'title' => 'New Resident',
                    'description' => $user->name . ' joined the system',
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
    }
}
