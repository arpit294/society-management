<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Maintenance;
use App\Models\MaintenanceBill;
use App\Models\Resident;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Nette\Schema\ValidationException;
use OpenSpout\Writer\XLSX\Writer;
use OpenSpout\Common\Entity\Row;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ReportController extends Controller
{
    public function maintenanceReport(Request $request)
    {
        abort_if(! auth()->user()->can('setting_view'), 403);
        try {
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
            $activeResidents = Resident::with(['user', 'flat.block', 'flat.flatType'])
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
        } catch (\Exception $e) {
            if ($e instanceof ValidationException || $e instanceof HttpExceptionInterface) {
                throw $e;
            }
            Log::error('Error in ReportController@maintenanceReport: ' . $e->getMessage());

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
            }

            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    public function exportReport(Request $request)
    {
        abort_if(! auth()->user()->can('setting_view'), 403);
        try {
            $reportType = $request->input('report_type', 'monthly');

            $latestMaintenance = Maintenance::orderBy('year', 'desc')->orderBy('id', 'desc')->first();
            $selectedMonth = $request->input('month', $latestMaintenance ? $latestMaintenance->month : Carbon::now()->format('F'));
            $selectedYear = $request->input('year', $latestMaintenance ? $latestMaintenance->year : Carbon::now()->format('Y'));

            // Fetch all active residents once
            $activeResidents = Resident::with(['user', 'flat.block', 'flat.flatType'])
                ->where(function($query) {
                    $query->whereNull('move_out_date')
                          ->orWhere('move_out_date', '>=', now()->startOfDay());
                })->get();

            $filename = $reportType === 'monthly'
                ? "maintenance_report_{$selectedMonth}_{$selectedYear}.xlsx"
                : "maintenance_report_yearly_{$selectedYear}.xlsx";

            $headers = [
                "Content-type"        => "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
                "Content-Disposition" => "attachment; filename={$filename}",
                "Pragma"              => "no-cache",
                "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
                "Expires"             => "0"
            ];

            // Create a callback to stream the Excel file
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
        } catch (\Exception $e) {
            if ($e instanceof ValidationException || $e instanceof HttpExceptionInterface) {
                throw $e;
            }
            Log::error('Error in ReportController@exportReport: ' . $e->getMessage());

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
            }

            return redirect()->back()->with('error', 'An error occurred generating report export: ' . $e->getMessage());
        }
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
        $processedFlatIds = [];

        if ($maintenance) {
            $allBills = MaintenanceBill::with(['user', 'block', 'flat'])
                                       ->where('maintenance_id', $maintenance->id)
                                       ->get();

            foreach ($allBills as $bill) {
                $processedFlatIds[] = $bill->flat_id;

                if ($bill->status === 'paid') {
                    $paidBills->push((object)[
                        'user' => $bill->user,
                        'block' => $bill->block,
                        'flat' => $bill->flat,
                        'total_amount' => $bill->total_amount,
                        'payment_method' => $bill->payment_method ?? 'N/A',
                        'paid_at' => $bill->paid_at,
                    ]);
                    $totalPaid += $bill->total_amount;
                    $totalExpected += $bill->amount ?? $bill->total_amount;
                } else {
                    $pendingBills->push((object)[
                        'user' => $bill->user,
                        'block' => $bill->block,
                        'flat' => $bill->flat,
                        'amount' => $bill->amount,
                        'penalty_amount' => $bill->penalty_amount,
                        'total_amount' => $bill->total_amount,
                        'status' => $bill->status,
                    ]);
                    $totalPending += $bill->total_amount;
                    $totalExpected += $bill->amount;
                }
            }
        }

        foreach ($activeResidents as $resident) {
            if (in_array($resident->flat_id, $processedFlatIds)) {
                continue;
            }

            $baseAmount = 0;
            if ($resident->flat && $resident->flat->flatType) {
                $baseAmount = $resident->type === 'owner'
                    ? (float)$resident->flat->flatType->owner_maintenance_fee
                    : (float)$resident->flat->flatType->rental_maintenance_fee;
            }

            $pendingBills->push((object)[
                'user' => $resident->user,
                'block' => $resident->flat->block ?? null,
                'flat' => $resident->flat,
                'amount' => $baseAmount,
                'penalty_amount' => 0,
                'total_amount' => $baseAmount,
                'status' => 'pending',
            ]);

            $totalExpected += $baseAmount;
            $totalPending += $baseAmount;
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
