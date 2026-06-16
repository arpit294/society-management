<?php

namespace App\Http\Controllers;

use App\Models\Complain;
use App\Models\Expense;
use App\Models\Flat;
use App\Models\Maintenance;
use App\Models\MaintenanceBill;
use App\Models\Resident;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Class DashboardController
 *
 * Handles the logic for the main administrative dashboard.
 * Provides statistics, chart data, and recent activities overview.
 */
class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     *
     * Compiles data for key metrics (total residents, revenue, expenses),
     * prepares data for revenue/expense charts, and fetches recent activities
     * to provide a high-level overview of the society's status.
     *
     * @return View
     */
    public function index()
    {
        // 1. Calculate top-level statistics for dashboard widgets
        $totalResidents = User::count();
        $totalFlats = Flat::count();
        $totalComplaints = Complain::where('status', '!=', 'resolved')->count();

        $totalRevenue = MaintenanceBill::where('status', 'paid')->sum('total_amount');
        $totalExpenses = Expense::sum('total_amount');

        // 2. Prepare Revenue Chart Data (Current Year)
        $months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

        $monthlyRevenueDB = MaintenanceBill::where('maintenance_bills.status', 'paid')
            ->join('maintenances', 'maintenance_bills.maintenance_id', '=', 'maintenances.id')
            ->where('maintenances.year', date('Y'))
            ->selectRaw('maintenances.month, sum(maintenance_bills.total_amount) as total')
            ->groupBy('maintenances.month')
            ->pluck('total', 'month')
            ->toArray();

        // 3. Prepare Expense Chart Data (Current Year)
        $monthlyExpensesDB = Expense::whereYear('created_at', date('Y'))
            ->selectRaw('MONTHNAME(created_at) as month, sum(total_amount) as total')
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        // 4. Map DB results to the 12-month array format required by charts
        $chartDataRevenue = [];
        $chartDataExpenses = [];

        foreach ($months as $m) {
            $chartDataRevenue[] = $monthlyRevenueDB[$m] ?? 0;
            $chartDataExpenses[] = $monthlyExpensesDB[$m] ?? 0;
        }

        // 5. Calculate Bill Status Doughnut Chart Data (Based on Latest Maintenance)
        $latestMaintenance = Maintenance::orderBy('year', 'desc')->orderBy('id', 'desc')->first();
        $maintenanceId = $latestMaintenance ? $latestMaintenance->id : null;

        $activeResidentsCount = Resident::where(function ($query) {
            $query->whereNull('move_out_date')
                ->orWhere('move_out_date', '>=', now()->startOfDay());
        })->count();

        $paidCount = 0;
        if ($maintenanceId) {
            $paidCount = MaintenanceBill::where('maintenance_id', $maintenanceId)
                ->where('status', 'paid')
                ->count();
        }

        $pendingCount = max(0, $activeResidentsCount - $paidCount);

        $billStatusData = [
            'paid' => $paidCount,
            'pending' => $pendingCount,
            'due' => 0,
        ];

        // 6. Fetch Recent Activities (Payments, Complaints, Users)
        $recentPayments = MaintenanceBill::with('user')
            ->where('status', 'paid')
            ->select('batch_id', 'user_id', DB::raw('MAX(status) as status'), DB::raw('MAX(updated_at) as updated_at'), DB::raw('SUM(total_amount) as total_amount'))
            ->groupBy('batch_id', 'user_id')
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($payment) {
                // Ensure updated_at is a Carbon instance since MAX() might return a string
                $timestamp = Carbon::parse($payment->updated_at);

                return (object) [
                    'icon' => 'fa-solid fa-money-bill-wave text-success',
                    'title' => 'Payment Received',
                    'description' => '₹'.number_format($payment->total_amount, 2).' from '.($payment->user->name ?? 'Unknown'),
                    'time' => $timestamp->diffForHumans(),
                    'timestamp' => $timestamp,
                ];
            });

        $recentComplaints = Complain::with('user')
            ->where('status', '!=', 'resolved')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($complain) {
                return (object) [
                    'icon' => 'fa-solid fa-triangle-exclamation text-warning',
                    'title' => 'New Complaint',
                    'description' => $complain->title.' by '.($complain->user->name ?? 'Unknown'),
                    'time' => $complain->created_at->diffForHumans(),
                    'timestamp' => $complain->created_at,
                ];
            });

        $recentUsers = User::latest()
            ->take(5)
            ->get()
            ->map(function ($user) {
                return (object) [
                    'icon' => 'fa-solid fa-user-plus text-info',
                    'title' => 'New Resident',
                    'description' => $user->name.' joined the system',
                    'time' => $user->created_at->diffForHumans(),
                    'timestamp' => $user->created_at,
                ];
            });

        // 7. Merge and sort all activities chronologically
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
            'activities'
        ));
    }
}
