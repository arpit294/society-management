<?php

namespace App\Http\Controllers;

use App\Models\Complain;
use App\Models\Expense;
use App\Models\Flat;
use App\Models\MaintenanceBill;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $totalResidents = User::count();
        $totalFlats = Flat::count();
        $totalComplaints = Complain::count();

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

        // Bill Status Doughnut Chart Data
        $billStatusData = [
            'paid' => MaintenanceBill::where('status', 'paid')->count(),
            'pending' => MaintenanceBill::where('status', 'pending')->count(),
            'due' => MaintenanceBill::where('status', 'due')->count(),
        ];

        return view('dashboard', compact(
            'totalResidents',
            'totalFlats',
            'totalComplaints',
            'totalRevenue',
            'totalExpenses',
            'months',
            'chartDataRevenue',
            'chartDataExpenses',
            'billStatusData'
        ));
    }
}
