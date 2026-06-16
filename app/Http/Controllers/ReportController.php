<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Maintenance;
use App\Models\MaintenanceBill;
use Carbon\Carbon;
use OpenSpout\Writer\XLSX\Writer;
use OpenSpout\Common\Entity\Row;

class ReportController extends Controller
{
    public function maintenanceReport(Request $request)
    {
        $reportType = $request->input('report_type', 'monthly');

        // Get available months and years from Maintenance table
        $availableDates = Maintenance::select('month', 'year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->get();

        $latestMaintenance = Maintenance::orderBy('year', 'desc')->orderBy('id', 'desc')->first();
        
        $selectedMonth = $request->input('month', $latestMaintenance ? $latestMaintenance->month : Carbon::now()->format('F'));
        $selectedYear = $request->input('year', $latestMaintenance ? $latestMaintenance->year : Carbon::now()->format('Y'));

        // Fetch all active residents once
        $activeResidents = \App\Models\Resident::with(['user', 'flat.block', 'flat.flatType'])
            ->where(function($query) {
                $query->whereNull('move_out_date')
                      ->orWhere('move_out_date', '>=', now()->startOfDay());
            })->get();

        if ($reportType === 'yearly') {
            $yearlyExpected = 0;
            $yearlyPaid = 0;
            $yearlyPending = 0;
            $monthlyBreakdown = [];

            $months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

            foreach ($months as $month) {
                $stats = $this->calculateMonthlyStats($month, $selectedYear, $activeResidents);
                $monthlyBreakdown[] = (object)[
                    'month' => $month,
                    'expected' => $stats['totalExpected'],
                    'paid' => $stats['totalPaid'],
                    'pending' => $stats['totalPending'],
                ];
                $yearlyExpected += $stats['totalExpected'];
                $yearlyPaid += $stats['totalPaid'];
                $yearlyPending += $stats['totalPending'];
            }

            return view('reports.maintenance', compact(
                'reportType',
                'selectedYear',
                'availableDates',
                'yearlyExpected',
                'yearlyPaid',
                'yearlyPending',
                'monthlyBreakdown'
            ));
        }

        // Monthly Logic
        $stats = $this->calculateMonthlyStats($selectedMonth, $selectedYear, $activeResidents);

        return view('reports.maintenance', array_merge([
            'reportType' => $reportType,
            'selectedMonth' => $selectedMonth,
            'selectedYear' => $selectedYear,
            'availableDates' => $availableDates,
        ], $stats));
    }

    public function exportReport(Request $request)
    {
        $reportType = $request->input('report_type', 'monthly');

        $latestMaintenance = Maintenance::orderBy('year', 'desc')->orderBy('id', 'desc')->first();
        $selectedMonth = $request->input('month', $latestMaintenance ? $latestMaintenance->month : Carbon::now()->format('F'));
        $selectedYear = $request->input('year', $latestMaintenance ? $latestMaintenance->year : Carbon::now()->format('Y'));

        $activeResidents = \App\Models\Resident::with(['user', 'flat.block', 'flat.flatType'])
            ->where(function($query) {
                $query->whereNull('move_out_date')
                      ->orWhere('move_out_date', '>=', now()->startOfDay());
            })->get();

        $headers = [
            "Content-type"        => "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
            "Content-Disposition" => "attachment; filename=maintenance_report_{$reportType}_{$selectedYear}.xlsx",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($reportType, $selectedMonth, $selectedYear, $activeResidents) {
            $writer = new Writer();
            $writer->openToFile('php://output');
            
            if ($reportType === 'yearly') {
                $writer->addRow(Row::fromValues(['Month', 'Expected Amount', 'Paid Amount', 'Pending Amount']));
                
                $months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                $yearlyExpected = $yearlyPaid = $yearlyPending = 0;

                foreach ($months as $month) {
                    $stats = $this->calculateMonthlyStats($month, $selectedYear, $activeResidents);
                    $writer->addRow(Row::fromValues([
                        $month,
                        round($stats['totalExpected'], 2),
                        round($stats['totalPaid'], 2),
                        round($stats['totalPending'], 2)
                    ]));
                    
                    $yearlyExpected += $stats['totalExpected'];
                    $yearlyPaid += $stats['totalPaid'];
                    $yearlyPending += $stats['totalPending'];
                }
                
                $writer->addRow(Row::fromValues([
                    'Total',
                    round($yearlyExpected, 2),
                    round($yearlyPaid, 2),
                    round($yearlyPending, 2)
                ]));
            } else {
                $stats = $this->calculateMonthlyStats($selectedMonth, $selectedYear, $activeResidents);
                
                $writer->addRow(Row::fromValues(["Paid Residents - $selectedMonth $selectedYear"]));
                $writer->addRow(Row::fromValues(['Resident', 'Block - Flat', 'Paid Amount', 'Payment Method', 'Paid Date']));
                
                foreach ($stats['paidBills'] as $bill) {
                    $writer->addRow(Row::fromValues([
                        $bill->user->name ?? 'N/A',
                        ($bill->block->block_name ?? 'N/A') . ' - ' . ($bill->flat->flat_no ?? 'N/A'),
                        round($bill->total_amount, 2),
                        ucfirst($bill->payment_method),
                        $bill->paid_at ? $bill->paid_at->format('d M Y') : 'N/A'
                    ]));
                }
                
                $writer->addRow(Row::fromValues([]));
                
                $writer->addRow(Row::fromValues(["Pending Maintenance - $selectedMonth $selectedYear"]));
                $writer->addRow(Row::fromValues(['Resident', 'Block - Flat', 'Base Amount', 'Penalty Amount', 'Total Due', 'Status']));
                
                foreach ($stats['pendingBills'] as $bill) {
                    $writer->addRow(Row::fromValues([
                        $bill->user->name ?? 'N/A',
                        ($bill->block->block_name ?? 'N/A') . ' - ' . ($bill->flat->flat_no ?? 'N/A'),
                        round($bill->amount, 2),
                        round($bill->penalty_amount, 2),
                        round($bill->total_amount, 2),
                        ucfirst($bill->status)
                    ]));
                }
            }
            
            $writer->close();
        };

        return response()->stream($callback, 200, $headers);
    }

    private function calculateMonthlyStats($month, $year, $activeResidents)
    {
        $maintenance = Maintenance::where('month', $month)
                                  ->where('year', $year)
                                  ->first();

        $paidBills = collect();
        $pendingBills = collect();

        $totalExpected = 0;
        $totalPaid = 0;
        $totalPending = 0;

        // Sort active residents to prioritize rentals, so if a flat has both, tenant is billed
        $activeResidents = $activeResidents->sortByDesc(function ($resident) {
            return $resident->type === 'rental' ? 1 : 0;
        });

        $processedFlats = [];

        foreach ($activeResidents as $resident) {
            if (!$resident->flat_id || in_array($resident->flat_id, $processedFlats)) {
                continue;
            }
            $processedFlats[] = $resident->flat_id;

            $baseAmount = 0;
            if ($resident->flat && $resident->flat->flatType) {
                $baseAmount = $resident->type === 'owner' 
                    ? (float)$resident->flat->flatType->owner_maintenance_fee 
                    : (float)$resident->flat->flatType->rental_maintenance_fee;
            }

            $totalExpected += $baseAmount;

            $bill = null;
            if ($maintenance) {
                $bill = MaintenanceBill::where('maintenance_id', $maintenance->id)
                                       ->where('flat_id', $resident->flat_id)
                                       ->first();
            }

            if ($bill && $bill->status === 'paid') {
                $paidBills->push((object)[
                    'user' => $resident->user,
                    'block' => $resident->flat->block ?? null,
                    'flat' => $resident->flat,
                    'total_amount' => $bill->total_amount,
                    'payment_method' => $bill->payment_method ?? 'N/A',
                    'paid_at' => $bill->paid_at,
                ]);
                $totalPaid += $bill->total_amount;
            } else {
                $penalty = $bill ? $bill->penalty_amount : 0;
                $totalDue = $bill ? $bill->total_amount : $baseAmount;
                $status = $bill ? $bill->status : 'pending';

                $pendingBills->push((object)[
                    'user' => $resident->user,
                    'block' => $resident->flat->block ?? null,
                    'flat' => $resident->flat,
                    'amount' => $baseAmount,
                    'penalty_amount' => $penalty,
                    'total_amount' => $totalDue,
                    'status' => $status,
                ]);
                $totalPending += $totalDue;
            }
        }

        return [
            'paidBills' => $paidBills,
            'pendingBills' => $pendingBills,
            'totalExpected' => $totalExpected,
            'totalPaid' => $totalPaid,
            'totalPending' => $totalPending,
        ];
    }
}
