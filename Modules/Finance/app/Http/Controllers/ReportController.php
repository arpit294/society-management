<?php

namespace Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Block;
use App\Models\Resident;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Modules\Finance\Models\Expense;
use Modules\Finance\Models\Maintenance;
use Modules\Finance\Models\MaintenanceBill;
use Modules\Finance\Models\NameTransferBill;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Yajra\DataTables\Facades\DataTables;

class ReportController extends Controller
{
    public function maintenanceReport(Request $request)
    {
        abort_if(! auth()->user()->can('setting_view'), 403);
        try {
            $reportType = $request->input('report_type', 'yearly');

            $availableDates = Maintenance::select('month', 'year')
                ->distinct()
                ->orderBy('year', 'desc')
                ->get();

            $latestMaintenance = Maintenance::orderBy('year', 'desc')->orderBy('id', 'desc')->first();

            $selectedMonth = $request->input('month', $latestMaintenance ? $latestMaintenance->month : Carbon::now()->format('F'));
            $selectedYear = $request->input('year', $latestMaintenance ? $latestMaintenance->year : Carbon::now()->format('Y'));

            $filterResidentId = $request->input('resident_id', null);
            $filterUserId = null;
            $filterFlatId = null;
            if ($filterResidentId) {
                $selectedResident = Resident::find($filterResidentId);
                if ($selectedResident) {
                    $filterUserId = $selectedResident->user_id;
                    $filterFlatId = $selectedResident->flat_id;
                }
            }
            $filterBlockId = $request->input('block_id', null);

            $residentBaseQuery = Resident::with(['user', 'flat.block', 'flat.flatType'])
                ->where(function ($query) use ($reportType, $selectedYear) {
                    $query->whereNull('move_out_date')
                        ->orWhere('move_out_date', '>', now());
                    if ($reportType === 'yearly') {
                        $query->orWhereYear('move_out_date', '>=', $selectedYear);
                    }
                });

            if ($filterBlockId && $filterUserId) {
                $userInBlock = (clone $residentBaseQuery)
                    ->where('user_id', $filterUserId)
                    ->whereHas('flat', function ($q) use ($filterBlockId) {
                        $q->where('block_id', $filterBlockId);
                    })->exists();
                if (!$userInBlock) {
                    $filterUserId = null;
                    $filterFlatId = null;
                    $filterResidentId = null;
                }
            }

            $activeResidentsQuery = clone $residentBaseQuery;
            if ($filterUserId) {
                $activeResidentsQuery->where('user_id', $filterUserId);
            }
            if ($filterFlatId) {
                $activeResidentsQuery->where('flat_id', $filterFlatId);
            }
            if ($filterBlockId) {
                $activeResidentsQuery->whereHas('flat', function ($q) use ($filterBlockId) {
                    $q->where('block_id', $filterBlockId);
                });
            }
            $activeResidents = $activeResidentsQuery->get()->groupBy(function($r) {
                return $r->user_id . '_' . $r->flat_id;
            })->map(function($group) {
                return $group->sortByDesc(function ($r) {
                    return is_null($r->move_out_date) ? PHP_INT_MAX : Carbon::parse($r->move_out_date)->timestamp;
                })->first();
            })->values();

            $usersYearly = collect();
            if ($reportType === 'yearly') {
                $months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                $residentsByUser = $activeResidents->groupBy('user_id')->map(function ($grp) {
                    return $grp->first();
                });
                foreach ($residentsByUser as $userId => $resident) {
                    if (! $resident || ! $resident->user) continue;
                    $userTotals = ['totalExpected' => 0, 'totalPaid' => 0, 'totalPending' => 0];
                    foreach ($months as $month) {
                        $stats = $this->calculateMonthlyStats($month, $selectedYear, $activeResidents, $userId, $filterBlockId);
                        $userTotals['totalExpected'] += $stats['totalExpected'];
                        $userTotals['totalPaid'] += $stats['totalPaid'];
                        $userTotals['totalPending'] += $stats['totalPending'];
                    }
                    $userTransferFees = NameTransferBill::where('status', 'paid')
                        ->where(function ($q) use ($selectedYear) {
                            $q->whereYear('transfer_date', $selectedYear)
                                ->orWhereYear('paid_at', $selectedYear)
                                ->orWhereYear('created_at', $selectedYear);
                        })
                        ->where('new_owner_id', $userId)
                        ->sum('amount');

                    if (!$filterUserId && $userTotals['totalExpected'] == 0 && $userTotals['totalPaid'] == 0 && $userTotals['totalPending'] == 0 && $userTransferFees == 0) {
                        if (!is_null($resident->move_out_date) && Carbon::parse($resident->move_out_date)->lte(now())) {
                            continue;
                        }
                    }

                    $usersYearly->push((object)[
                        'user' => $resident->user,
                        'resident' => $resident,
                        'totalExpected' => $userTotals['totalExpected'],
                        'totalPaid' => $userTotals['totalPaid'],
                        'transferFees' => $userTransferFees,
                        'totalPending' => $userTotals['totalPending'],
                    ]);
                }
            }

            $residentsQuery = clone $residentBaseQuery;
            if ($filterBlockId) {
                $residentsQuery->whereHas('flat', function ($q) use ($filterBlockId) {
                    $q->where('block_id', $filterBlockId);
                });
            }
            $residents = $residentsQuery->get();
            $blocks = Block::orderBy('block_name')->get();

            if ($reportType === 'yearly') {
                $yearlyExpected = 0;
                $yearlyPaid = 0;
                $yearlyPending = 0;
                $monthlyBreakdown = [];

                $yearlyTransferFeesQuery = NameTransferBill::where('status', 'paid')
                    ->where(function ($q) use ($selectedYear) {
                        $q->whereYear('transfer_date', $selectedYear)
                            ->orWhereYear('paid_at', $selectedYear)
                            ->orWhereYear('created_at', $selectedYear);
                    });
                if ($filterBlockId) {
                    $yearlyTransferFeesQuery->whereHas('flat', function ($q) use ($filterBlockId) {
                        $q->where('block_id', $filterBlockId);
                    });
                }
                if ($filterUserId) {
                    $yearlyTransferFeesQuery->where('new_owner_id', $filterUserId);
                }
                if ($filterFlatId) {
                    $yearlyTransferFeesQuery->where('flat_id', $filterFlatId);
                }
                $yearlyTransferFeesList = $yearlyTransferFeesQuery->get();
                $yearlyTransferFees = $yearlyTransferFeesList->sum('amount');
                $monthlyTransferFeesMap = $yearlyTransferFeesList->groupBy(function ($fee) {
                    $date = $fee->transfer_date ?: ($fee->paid_at ?: $fee->created_at);
                    return Carbon::parse($date)->format('F');
                })->map->sum('amount');

                $yearlyExpensesQuery = Expense::with(['category', 'user'])
                    ->whereYear(DB::raw('COALESCE(expense_date, created_at)'), $selectedYear)
                    ->latest(DB::raw('COALESCE(expense_date, created_at)'))
                    ->get();

                $totalExpense = $yearlyExpensesQuery->sum('total_amount');
                $monthlyExpenseMap = $yearlyExpensesQuery->groupBy(function ($exp) {
                    $date = $exp->expense_date ? Carbon::parse($exp->expense_date) : $exp->created_at;
                    return $date->format('F');
                })->map->sum('total_amount');

                $expenseCategories = $yearlyExpensesQuery->groupBy(function ($exp) {
                    return $exp->category ? $exp->category->title : 'Uncategorized';
                })->map->sum('total_amount');

                $expensesList = $yearlyExpensesQuery;

                $months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

                foreach ($months as $month) {
                    $stats = $this->calculateMonthlyStats($month, $selectedYear, $activeResidents, $filterUserId, $filterBlockId, $filterFlatId);
                    $monthlyBreakdown[] = (object)[
                        'month' => $month,
                        'expected' => $stats['totalExpected'],
                        'paid' => $stats['totalPaid'],
                        'transfer_fees' => $monthlyTransferFeesMap[$month] ?? 0,
                        'pending' => $stats['totalPending'],
                        'expense' => $monthlyExpenseMap[$month] ?? 0,
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
                    'yearlyTransferFees',
                    'yearlyPending',
                    'monthlyBreakdown',
                    'totalExpense',
                    'expenseCategories',
                    'expensesList'
                ))->with([
                    'residents' => $residents,
                    'blocks' => $blocks,
                    'filterResidentId' => $filterResidentId,
                    'filterUserId' => $filterUserId,
                    'filterBlockId' => $filterBlockId,
                    'usersYearly' => $usersYearly,
                ]);
            }

            // Monthly Logic
            $stats = $this->calculateMonthlyStats($selectedMonth, $selectedYear, $activeResidents, $filterUserId, $filterBlockId, $filterFlatId);

            $monthlyTransferFeesQuery = NameTransferBill::where('status', 'paid')
                ->where(function ($q) use ($selectedYear, $selectedMonth) {
                    $q->where(function ($sub) use ($selectedYear, $selectedMonth) {
                        $sub->whereYear('transfer_date', $selectedYear)
                            ->whereRaw('MONTHNAME(transfer_date) = ?', [$selectedMonth]);
                    })->orWhere(function ($sub) use ($selectedYear, $selectedMonth) {
                        $sub->whereYear('paid_at', $selectedYear)
                            ->whereRaw('MONTHNAME(paid_at) = ?', [$selectedMonth]);
                    })->orWhere(function ($sub) use ($selectedYear, $selectedMonth) {
                        $sub->whereYear('created_at', $selectedYear)
                            ->whereRaw('MONTHNAME(created_at) = ?', [$selectedMonth]);
                    });
                });
            if ($filterBlockId) {
                $monthlyTransferFeesQuery->whereHas('flat', function ($q) use ($filterBlockId) {
                    $q->where('block_id', $filterBlockId);
                });
            }
            if ($filterUserId) {
                $monthlyTransferFeesQuery->where('new_owner_id', $filterUserId);
            }
            if ($filterFlatId) {
                $monthlyTransferFeesQuery->where('flat_id', $filterFlatId);
            }
            $totalTransferFees = $monthlyTransferFeesQuery->sum('amount');

            $monthlyExpensesQuery = Expense::with(['category', 'user'])
                ->whereYear(DB::raw('COALESCE(expense_date, created_at)'), $selectedYear)
                ->whereRaw('MONTHNAME(COALESCE(expense_date, created_at)) = ?', [$selectedMonth])
                ->latest(DB::raw('COALESCE(expense_date, created_at)'))
                ->get();

            $totalExpense = $monthlyExpensesQuery->sum('total_amount');
            $expenseCategories = $monthlyExpensesQuery->groupBy(function ($exp) {
                return $exp->category ? $exp->category->title : 'Uncategorized';
            })->map->sum('total_amount');
            $expensesList = $monthlyExpensesQuery;

            return view('reports.maintenance', array_merge([
                'reportType' => $reportType,
                'selectedMonth' => $selectedMonth,
                'selectedYear' => $selectedYear,
                'availableDates' => $availableDates,
                'totalTransferFees' => $totalTransferFees,
                'totalExpense' => $totalExpense,
                'expenseCategories' => $expenseCategories,
                'expensesList' => $expensesList,
            ], $stats))->with([
                'residents' => $residents,
                'blocks' => $blocks,
                'filterResidentId' => $filterResidentId,
                'filterUserId' => $filterUserId,
                'filterBlockId' => $filterBlockId,
            ]);
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

    public function exportExpenseReport(Request $request)
    {
        $request->merge(['active_tab' => '#main-expense']);
        return $this->exportReport($request);
    }

    public function exportSummaryReport(Request $request)
    {
        $request->merge(['active_tab' => '#main-summary']);
        return $this->exportReport($request);
    }

    public function exportReport(Request $request)
    {
        abort_if(! auth()->user()->can('setting_view'), 403);
        try {
            $reportType = $request->input('report_type', 'monthly');

            $latestMaintenance = Maintenance::orderBy('year', 'desc')->orderBy('id', 'desc')->first();
            $selectedMonth = $request->input('month', $latestMaintenance ? $latestMaintenance->month : Carbon::now()->format('F'));
            $selectedYear = $request->input('year', $latestMaintenance ? $latestMaintenance->year : Carbon::now()->format('Y'));

            $filterResidentId = $request->input('resident_id', null);
            $filterUserId = null;
            $filterFlatId = null;
            if ($filterResidentId) {
                $selectedResident = Resident::find($filterResidentId);
                if ($selectedResident) {
                    $filterUserId = $selectedResident->user_id;
                    $filterFlatId = $selectedResident->flat_id;
                }
            }
            $filterBlockId = $request->input('block_id', null);

            $residentBaseQuery = Resident::with(['user', 'flat.block', 'flat.flatType'])
                ->where(function ($query) use ($reportType, $selectedYear) {
                    $query->whereNull('move_out_date')
                        ->orWhere('move_out_date', '>', now());
                    if ($reportType === 'yearly') {
                        $query->orWhereYear('move_out_date', '>=', $selectedYear);
                    }
                });

            if ($filterBlockId && $filterUserId) {
                $userInBlock = (clone $residentBaseQuery)
                    ->where('user_id', $filterUserId)
                    ->whereHas('flat', function ($q) use ($filterBlockId) {
                        $q->where('block_id', $filterBlockId);
                    })->exists();
                if (!$userInBlock) {
                    $filterUserId = null;
                    $filterFlatId = null;
                    $filterResidentId = null;
                }
            }

            $activeResidentsQuery = clone $residentBaseQuery;
            if ($filterUserId) {
                $activeResidentsQuery->where('user_id', $filterUserId);
            }
            if ($filterBlockId) {
                $activeResidentsQuery->whereHas('flat', function ($q) use ($filterBlockId) {
                    $q->where('block_id', $filterBlockId);
                });
            }
            $activeResidents = $activeResidentsQuery->get()->groupBy(function($r) {
                return $r->user_id . '_' . $r->flat_id;
            })->map(function($group) {
                return $group->sortByDesc(function ($r) {
                    return is_null($r->move_out_date) ? PHP_INT_MAX : Carbon::parse($r->move_out_date)->timestamp;
                })->first();
            })->values();

            $usersYearly = collect();
            if ($reportType === 'yearly') {
                $months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                foreach ($activeResidents as $resident) {
                    if (! $resident || ! $resident->user) continue;
                    
                    $userId = $resident->user_id;
                    $flatId = $resident->flat_id;
                    
                    $userTotals = ['totalExpected' => 0, 'totalPaid' => 0, 'totalPending' => 0];
                    foreach ($months as $month) {
                        $stats = $this->calculateMonthlyStats($month, $selectedYear, $activeResidents, $userId, $filterBlockId, $flatId);
                        $userTotals['totalExpected'] += $stats['totalExpected'];
                        $userTotals['totalPaid'] += $stats['totalPaid'];
                        $userTotals['totalPending'] += $stats['totalPending'];
                    }
                    $userTransferFees = NameTransferBill::where('status', 'paid')
                        ->where(function ($q) use ($selectedYear) {
                            $q->whereYear('transfer_date', $selectedYear)
                                ->orWhereYear('paid_at', $selectedYear)
                                ->orWhereYear('created_at', $selectedYear);
                        })
                        ->where('flat_id', $flatId)
                        ->where('new_owner_id', $userId)
                        ->sum('amount');

                    if (!$filterUserId && $userTotals['totalExpected'] == 0 && $userTotals['totalPaid'] == 0 && $userTotals['totalPending'] == 0 && $userTransferFees == 0) {
                        if (!is_null($resident->move_out_date) && Carbon::parse($resident->move_out_date)->lte(now())) {
                            continue;
                        }
                    }

                    $usersYearly->push((object)[
                        'user' => $resident->user,
                        'resident' => $resident,
                        'totalExpected' => $userTotals['totalExpected'],
                        'totalPaid' => $userTotals['totalPaid'],
                        'transferFees' => $userTransferFees,
                        'totalPending' => $userTotals['totalPending'],
                    ]);
                }
            }

            $activeTab = $request->input('active_tab', '#main-maintenance');
            $prefix = 'maintenance_collection_report';
            if ($activeTab === '#main-expense') {
                $prefix = 'society_expense_report';
            } elseif ($activeTab === '#main-summary') {
                $prefix = 'financial_summary_report';
            }

            $blockSuffix = '';
            if ($filterBlockId) {
                $blockModel = Block::find($filterBlockId);
                if ($blockModel) {
                    $blockSuffix = '_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', str_replace(' ', '_', strtolower($blockModel->block_name)));
                }
            }
            $userSuffix = '';
            if ($filterUserId) {
                $userModel = User::find($filterUserId);
                if ($userModel) {
                    $userSuffix = '_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', str_replace(' ', '_', strtolower($userModel->name)));
                }
            }

            $filename = $reportType === 'monthly'
                ? "{$prefix}_{$selectedMonth}_{$selectedYear}{$blockSuffix}{$userSuffix}.xlsx"
                : "{$prefix}_yearly_{$selectedYear}{$blockSuffix}{$userSuffix}.xlsx";

            $headers = [
                "Content-type"        => "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
                "Content-Disposition" => "attachment; filename={$filename}",
                "Pragma"              => "no-cache",
                "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
                "Expires"             => "0"
            ];

            $callback = function () use ($reportType, $selectedMonth, $selectedYear, $activeResidents, $activeTab, $filterUserId, $filterBlockId, $usersYearly, $filterFlatId) {
                $writer = new Writer();
                $writer->openToFile('php://output');

                if ($activeTab === '#main-expense') {
                    if ($reportType === 'yearly') {
                        $writer->addRow(Row::fromValues(["Yearly Society Expense Report - $selectedYear"]));
                        $writer->addRow(Row::fromValues(['#', 'Expense Title', 'Category', 'Logged By', 'Expense Date', 'Amount']));

                        $yearlyExpenses = Expense::with(['category', 'user'])
                            ->whereYear(DB::raw('COALESCE(expense_date, created_at)'), $selectedYear)
                            ->latest(DB::raw('COALESCE(expense_date, created_at)'))
                            ->get();

                        $totalYearlyExpense = 0;
                        foreach ($yearlyExpenses as $index => $exp) {
                            $writer->addRow(Row::fromValues([
                                $index + 1,
                                $exp->title,
                                $exp->category ? $exp->category->title : 'Uncategorized',
                                $exp->user ? $exp->user->name : 'N/A',
                                $exp->expense_date ? Carbon::parse($exp->expense_date)->format('d M Y') : $exp->created_at->format('d M Y'),
                                round($exp->total_amount, 2)
                            ]));
                            $totalYearlyExpense += $exp->total_amount;
                        }
                        $writer->addRow(Row::fromValues(['TOTAL EXPENSES', '', '', '', '', round($totalYearlyExpense, 2)]));
                    } else {
                        $writer->addRow(Row::fromValues(["Monthly Society Expense Report - $selectedMonth $selectedYear"]));
                        $writer->addRow(Row::fromValues(['#', 'Expense Title', 'Category', 'Logged By', 'Expense Date', 'Amount']));

                        $monthlyExpenses = Expense::with(['category', 'user'])
                            ->whereYear(DB::raw('COALESCE(expense_date, created_at)'), $selectedYear)
                            ->whereRaw('MONTHNAME(COALESCE(expense_date, created_at)) = ?', [$selectedMonth])
                            ->latest(DB::raw('COALESCE(expense_date, created_at)'))
                            ->get();

                        $totalMonthlyExpense = 0;
                        foreach ($monthlyExpenses as $index => $exp) {
                            $writer->addRow(Row::fromValues([
                                $index + 1,
                                $exp->title,
                                $exp->category ? $exp->category->title : 'Uncategorized',
                                $exp->user ? $exp->user->name : 'N/A',
                                $exp->expense_date ? Carbon::parse($exp->expense_date)->format('d M Y') : $exp->created_at->format('d M Y'),
                                round($exp->total_amount, 2)
                            ]));
                            $totalMonthlyExpense += $exp->total_amount;
                        }
                        $writer->addRow(Row::fromValues(['TOTAL EXPENSES', '', '', '', '', round($totalMonthlyExpense, 2)]));
                    }
                    $writer->close();
                    return;
                }

                if ($activeTab === '#main-summary') {
                    if ($reportType === 'yearly') {
                        $writer->addRow(Row::fromValues(["Financial Revenue vs Expense Summary - Yearly ($selectedYear)"]));
                        $writer->addRow(Row::fromValues(['Month', 'Maintenance Revenue', 'Transfer Fees Revenue', 'Total Income', 'Total Expenses', 'Remaining Fund Balance']));

                        $months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                        $yearlyExpensesQuery = Expense::whereYear(DB::raw('COALESCE(expense_date, created_at)'), $selectedYear)->get();
                        $monthlyExpenseMap = $yearlyExpensesQuery->groupBy(function ($exp) {
                            return Carbon::parse($exp->expense_date ?: $exp->created_at)->format('F');
                        })->map->sum('total_amount');

                        $totMaint = $totTrans = $totInc = $totExp = 0;
                        foreach ($months as $month) {
                            $stats = $this->calculateMonthlyStats($month, $selectedYear, $activeResidents, $filterUserId, $filterBlockId, $filterFlatId);
                            $transFee = NameTransferBill::where('status', 'paid')
                                ->where(function ($q) use ($selectedYear, $month) {
                                    $q->where(function ($sub) use ($selectedYear, $month) {
                                        $sub->whereYear('transfer_date', $selectedYear)->whereRaw('MONTHNAME(transfer_date) = ?', [$month]);
                                    })->orWhere(function ($sub) use ($selectedYear, $month) {
                                        $sub->whereYear('paid_at', $selectedYear)->whereRaw('MONTHNAME(paid_at) = ?', [$month]);
                                    })->orWhere(function ($sub) use ($selectedYear, $month) {
                                        $sub->whereYear('created_at', $selectedYear)->whereRaw('MONTHNAME(created_at) = ?', [$month]);
                                    });
                                });
                            if ($filterBlockId) {
                                $transFee->whereHas('flat', function ($q) use ($filterBlockId) {
                                    $q->where('block_id', $filterBlockId);
                                });
                            }
                            if ($filterUserId) {
                                $transFee->where('new_owner_id', $filterUserId);
                            }
                            if ($filterFlatId) {
                                $transFee->where('flat_id', $filterFlatId);
                            }
                            $trans = round($transFee->sum('amount'), 2);
                            $rev = round($stats['totalPaid'], 2);
                            $inc = round($rev + $trans, 2);
                            $exp = round($monthlyExpenseMap[$month] ?? 0, 2);
                            $net = round($inc - $exp, 2);
                            $writer->addRow(Row::fromValues([$month, $rev, $trans, $inc, $exp, $net]));
                            $totMaint += $rev;
                            $totTrans += $trans;
                            $totInc += $inc;
                            $totExp += $exp;
                        }
                        $writer->addRow(Row::fromValues(['TOTAL', round($totMaint, 2), round($totTrans, 2), round($totInc, 2), round($totExp, 2), round($totInc - $totExp, 2)]));
                    } else {
                        $stats = $this->calculateMonthlyStats($selectedMonth, $selectedYear, $activeResidents, $filterUserId, $filterBlockId, $filterFlatId);
                        $monthlyExpenses = Expense::whereYear(DB::raw('COALESCE(expense_date, created_at)'), $selectedYear)
                            ->whereRaw('MONTHNAME(COALESCE(expense_date, created_at)) = ?', [$selectedMonth])
                            ->sum('total_amount');

                        $transFee = NameTransferBill::where('status', 'paid')
                            ->where(function ($q) use ($selectedYear, $selectedMonth) {
                                $q->where(function ($sub) use ($selectedYear, $selectedMonth) {
                                    $sub->whereYear('transfer_date', $selectedYear)->whereRaw('MONTHNAME(transfer_date) = ?', [$selectedMonth]);
                                })->orWhere(function ($sub) use ($selectedYear, $selectedMonth) {
                                    $sub->whereYear('paid_at', $selectedYear)->whereRaw('MONTHNAME(paid_at) = ?', [$selectedMonth]);
                                })->orWhere(function ($sub) use ($selectedYear, $selectedMonth) {
                                    $sub->whereYear('created_at', $selectedYear)->whereRaw('MONTHNAME(created_at) = ?', [$selectedMonth]);
                                });
                            });
                        if ($filterBlockId) {
                            $transFee->whereHas('flat', function ($q) use ($filterBlockId) {
                                $q->where('block_id', $filterBlockId);
                            });
                        }
                        if ($filterUserId) {
                            $transFee->where('new_owner_id', $filterUserId);
                        }
                        if ($filterFlatId) {
                            $transFee->where('flat_id', $filterFlatId);
                        }
                        $trans = round($transFee->sum('amount'), 2);
                        $rev = round($stats['totalPaid'], 2);
                        $inc = round($rev + $trans, 2);
                        $exp = round($monthlyExpenses, 2);
                        $net = round($inc - $exp, 2);

                        $writer->addRow(Row::fromValues(["Financial Revenue vs Expense Summary - $selectedMonth $selectedYear"]));
                        $writer->addRow(Row::fromValues(['Category', 'Amount']));
                        $writer->addRow(Row::fromValues(['Collected Maintenance Revenue', $rev]));
                        $writer->addRow(Row::fromValues(['Collected Transfer Fees Revenue', $trans]));
                        $writer->addRow(Row::fromValues(['Total Society Income', $inc]));
                        $writer->addRow(Row::fromValues(['Total Society Expenses', $exp]));
                        $writer->addRow(Row::fromValues(['Remaining Fund Balance', $net]));
                    }
                    $writer->close();
                    return;
                }

                if ($reportType === 'yearly') {
                    $writer->addRow(Row::fromValues(['Month', 'Expected Amount', 'Paid Amount', 'Pending Amount']));

                    $months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                    $yearlyExpected = $yearlyPaid = $yearlyPending = 0;

                    foreach ($months as $month) {
                        $stats = $this->calculateMonthlyStats($month, $selectedYear, $activeResidents, $filterUserId, $filterBlockId, $filterFlatId);
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

                    if (!empty($usersYearly) && $usersYearly->isNotEmpty()) {
                        $writer->addRow(Row::fromValues([]));
                        $writer->addRow(Row::fromValues(["Resident Yearly Summary - $selectedYear"]));
                        $writer->addRow(Row::fromValues(['Resident Name', 'Block', 'Flat', 'Expected', 'Paid', 'Transfer Fees', 'Pending']));
                        foreach ($usersYearly as $u) {
                            $writer->addRow(Row::fromValues([
                                $u->user ? $u->user->name : 'N/A',
                                $u->resident?->flat?->block?->block_name ?? 'N/A',
                                $u->resident?->flat?->flat_no ?? 'N/A',
                                round($u->totalExpected, 2),
                                round($u->totalPaid, 2),
                                round($u->transferFees ?? 0, 2),
                                round($u->totalPending, 2),
                            ]));
                        }
                        $writer->addRow(Row::fromValues([
                            'TOTAL',
                            '',
                            '',
                            round($usersYearly->sum('totalExpected'), 2),
                            round($usersYearly->sum('totalPaid'), 2),
                            round($usersYearly->sum('transferFees'), 2),
                            round($usersYearly->sum('totalPending'), 2),
                        ]));
                    }

                    $writer->addRow(Row::fromValues([]));
                    $writer->addRow(Row::fromValues([]));
                    $writer->addRow(Row::fromValues(["Yearly Society Expenses - $selectedYear"]));
                    $writer->addRow(Row::fromValues(['Expense Title', 'Category', 'Logged By', 'Date', 'Amount']));

                    $yearlyExpenses = Expense::with(['category', 'user'])
                        ->whereYear(DB::raw('COALESCE(expense_date, created_at)'), $selectedYear)
                        ->latest(DB::raw('COALESCE(expense_date, created_at)'))
                        ->get();

                    foreach ($yearlyExpenses as $exp) {
                        $writer->addRow(Row::fromValues([
                            $exp->title,
                            $exp->category ? $exp->category->title : 'Uncategorized',
                            $exp->user ? $exp->user->name : 'N/A',
                            $exp->expense_date ? Carbon::parse($exp->expense_date)->format('d M Y') : $exp->created_at->format('d M Y'),
                            round($exp->total_amount, 2)
                        ]));
                    }
                    $writer->addRow(Row::fromValues([
                        'TOTAL EXPENSES',
                        '',
                        '',
                        '',
                        round($yearlyExpenses->sum('total_amount'), 2)
                    ]));
                } else {
                    $stats = $this->calculateMonthlyStats($selectedMonth, $selectedYear, $activeResidents, $filterUserId, $filterBlockId, $filterFlatId);

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
                    $writer->addRow(Row::fromValues([
                        'TOTAL PAID',
                        '',
                        round($stats['paidBills']->sum('total_amount'), 2),
                        '',
                        ''
                    ]));

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
                    $writer->addRow(Row::fromValues([
                        'TOTAL PENDING',
                        '',
                        round($stats['pendingBills']->sum('amount'), 2),
                        round($stats['pendingBills']->sum('penalty_amount'), 2),
                        round($stats['pendingBills']->sum('total_amount'), 2),
                        ''
                    ]));

                    $writer->addRow(Row::fromValues([]));
                    $writer->addRow(Row::fromValues([]));
                    $writer->addRow(Row::fromValues(["Society Expenses - $selectedMonth $selectedYear"]));
                    $writer->addRow(Row::fromValues(['Expense Title', 'Category', 'Logged By', 'Date', 'Amount']));

                    $monthlyExpenses = Expense::with(['category', 'user'])
                        ->whereYear(DB::raw('COALESCE(expense_date, created_at)'), $selectedYear)
                        ->whereRaw('MONTHNAME(COALESCE(expense_date, created_at)) = ?', [$selectedMonth])
                        ->latest(DB::raw('COALESCE(expense_date, created_at)'))
                        ->get();

                    foreach ($monthlyExpenses as $exp) {
                        $writer->addRow(Row::fromValues([
                            $exp->title,
                            $exp->category ? $exp->category->title : 'Uncategorized',
                            $exp->user ? $exp->user->name : 'N/A',
                            $exp->expense_date ? Carbon::parse($exp->expense_date)->format('d M Y') : $exp->created_at->format('d M Y'),
                            round($exp->total_amount, 2)
                        ]));
                    }
                    $writer->addRow(Row::fromValues([
                        'TOTAL EXPENSES',
                        '',
                        '',
                        '',
                        round($monthlyExpenses->sum('total_amount'), 2)
                    ]));
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

    public function usersYearlyData(Request $request)
    {
        abort_if(! auth()->user()->can('setting_view'), 403);

        $selectedYear = $request->input('year', date('Y'));
        $filterResidentId = $request->input('resident_id', null);
        $filterUserId = null;
        $filterFlatId = null;
        if ($filterResidentId) {
            $selectedResident = Resident::find($filterResidentId);
            if ($selectedResident) {
                $filterUserId = $selectedResident->user_id;
                $filterFlatId = $selectedResident->flat_id;
            }
        }
        $filterBlockId = $request->input('block_id', null);

        $residentBaseQuery = Resident::with(['user', 'flat.block', 'flat.flatType'])
            ->where(function ($query) use ($selectedYear) {
                $query->whereNull('move_out_date')
                    ->orWhere('move_out_date', '>', now())
                    ->orWhereYear('move_out_date', '>=', $selectedYear);
            });

        if ($filterBlockId && $filterUserId) {
            $userInBlock = (clone $residentBaseQuery)
                ->where('user_id', $filterUserId)
                ->whereHas('flat', function ($q) use ($filterBlockId) {
                    $q->where('block_id', $filterBlockId);
                })->exists();
            if (!$userInBlock) {
                $filterUserId = null;
                $filterFlatId = null;
                $filterResidentId = null;
            }
        }

        $activeResidentsQuery = clone $residentBaseQuery;
        if ($filterUserId) {
            $activeResidentsQuery->where('user_id', $filterUserId);
        }
        if ($filterFlatId) {
            $activeResidentsQuery->where('flat_id', $filterFlatId);
        }
        if ($filterBlockId) {
            $activeResidentsQuery->whereHas('flat', function ($q) use ($filterBlockId) {
                $q->where('block_id', $filterBlockId);
            });
        }
        $activeResidents = $activeResidentsQuery->get()->groupBy(function($r) {
            return $r->user_id . '_' . $r->flat_id;
        })->map(function($group) {
            return $group->sortByDesc(function ($r) {
                return is_null($r->move_out_date) ? PHP_INT_MAX : Carbon::parse($r->move_out_date)->timestamp;
            })->first();
        })->values();

        $months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

        $usersYearly = collect();
        foreach ($activeResidents as $resident) {
            if (! $resident || ! $resident->user) continue;
            
            $userId = $resident->user_id;
            $flatId = $resident->flat_id;
            
            $userTotals = ['totalExpected' => 0, 'totalPaid' => 0, 'totalPending' => 0];
            foreach ($months as $month) {
                $stats = $this->calculateMonthlyStats($month, $selectedYear, $activeResidents, $userId, $filterBlockId, $flatId);
                $userTotals['totalExpected'] += $stats['totalExpected'];
                $userTotals['totalPaid'] += $stats['totalPaid'];
                $userTotals['totalPending'] += $stats['totalPending'];
            }
            $userTransferFees = NameTransferBill::where('status', 'paid')
                ->where(function ($q) use ($selectedYear) {
                    $q->whereYear('transfer_date', $selectedYear)
                        ->orWhereYear('paid_at', $selectedYear)
                        ->orWhereYear('created_at', $selectedYear);
                })
                ->where('flat_id', $flatId)
                ->where('new_owner_id', $userId)
                ->sum('amount');

            if (!$filterUserId && $userTotals['totalExpected'] == 0 && $userTotals['totalPaid'] == 0 && $userTotals['totalPending'] == 0 && $userTransferFees == 0) {
                if (!is_null($resident->move_out_date) && Carbon::parse($resident->move_out_date)->lte(now())) {
                    continue;
                }
            }

            $usersYearly->push((object)[
                'user_id' => $resident->user_id,
                'user_name' => $resident->user->name,
                'block' => $resident->flat?->block?->block_name ?? '',
                'flat' => $resident->flat ? ($resident->flat->flat_no ?? '') : '',
                'totalExpected' => $userTotals['totalExpected'],
                'totalPaid' => $userTotals['totalPaid'],
                'transferFees' => $userTransferFees,
                'totalPending' => $userTotals['totalPending'],
            ]);
        }

        return DataTables::of($usersYearly)
            ->addIndexColumn()
            ->editColumn('totalExpected', fn($row) => number_format($row->totalExpected, 2))
            ->editColumn('totalPaid', fn($row) => number_format($row->totalPaid, 2))
            ->editColumn('transferFees', fn($row) => number_format($row->transferFees, 2))
            ->editColumn('totalPending', fn($row) => number_format($row->totalPending, 2))
            ->make(true);
    }

    private function calculateMonthlyStats($month, $year, $activeResidents, $filterUserId = null, $filterBlockId = null, $filterFlatId = null)
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

        $billedFlatIds = [];
        if ($maintenance) {
            $billedFlatIds = MaintenanceBill::where('maintenance_id', $maintenance->id)
                ->pluck('flat_id')
                ->toArray();
        }

        $latestResidentByFlat = $activeResidents->groupBy('flat_id')->map(function ($group) {
            return $group->sortByDesc(function ($r) {
                return is_null($r->move_out_date) ? PHP_INT_MAX : Carbon::parse($r->move_out_date)->timestamp;
            })->first();
        });

        if ($maintenance) {
            $allBills = MaintenanceBill::with(['user', 'block', 'flat'])
                ->where('maintenance_id', $maintenance->id)
                ->when($filterUserId, function ($q) use ($filterUserId) {
                    $q->where('user_id', $filterUserId);
                })
                ->when($filterFlatId, function ($q) use ($filterFlatId) {
                    $q->where('flat_id', $filterFlatId);
                })
                ->when($filterBlockId, function ($q) use ($filterBlockId) {
                    $q->where(function ($sub) use ($filterBlockId) {
                        $sub->where('block_id', $filterBlockId)
                            ->orWhereHas('flat', function ($fq) use ($filterBlockId) {
                                $fq->where('block_id', $filterBlockId);
                            });
                    });
                })
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
            if (in_array($resident->flat_id, $processedFlatIds) || in_array($resident->flat_id, $billedFlatIds)) {
                continue;
            }

            $latestRes = $latestResidentByFlat->get($resident->flat_id);
            if ($latestRes && $latestRes->id !== $resident->id) {
                continue;
            }

            if ($filterUserId && $resident->user_id != $filterUserId) {
                continue;
            }
            if ($filterFlatId && $resident->flat_id != $filterFlatId) {
                continue;
            }

            $baseAmount = 0;
            if ($resident->flat) {
                $baseAmount = $resident->flat->calculateMaintenanceFee($resident->type);
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
