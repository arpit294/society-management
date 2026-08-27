<?php

namespace Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Flat;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Modules\Finance\Services\FinancialReportService;

class FinancialReportController extends Controller
{
    public function __construct(
        protected FinancialReportService $reportService
    ) {}

    public function index()
    {
        $flats = Flat::with(['block', 'residents.user'])->where('status', 'occupied')->get();
        return view('finance::reports.index', compact('flats'));
    }

    public function trialBalance(Request $request)
    {
        $asOfDate = $request->input('as_of_date', now()->toDateString());
        $data = $this->reportService->getTrialBalance($asOfDate);

        if ($request->input('export') === 'pdf') {
            $pdf = Pdf::loadView('finance::reports.trial_balance_pdf', compact('data'));
            return $pdf->stream("Trial-Balance-{$asOfDate}.pdf");
        }

        return view('finance::reports.trial_balance', compact('data', 'asOfDate'));
    }

    public function incomeExpenditure(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfYear()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());

        $data = $this->reportService->getIncomeExpenditure($startDate, $endDate);

        if ($request->input('export') === 'pdf') {
            $pdf = Pdf::loadView('finance::reports.income_expenditure_pdf', compact('data'));
            return $pdf->stream("Income-Expenditure-{$startDate}-to-{$endDate}.pdf");
        }

        return view('finance::reports.income_expenditure', compact('data', 'startDate', 'endDate'));
    }

    public function balanceSheet(Request $request)
    {
        $asOfDate = $request->input('as_of_date', now()->toDateString());
        $data = $this->reportService->getBalanceSheet($asOfDate);

        if ($request->input('export') === 'pdf') {
            $pdf = Pdf::loadView('finance::reports.balance_sheet_pdf', compact('data'));
            return $pdf->stream("Balance-Sheet-{$asOfDate}.pdf");
        }

        return view('finance::reports.balance_sheet', compact('data', 'asOfDate'));
    }

    public function duesAging(Request $request)
    {
        $data = $this->reportService->getDuesAgingReport();

        if ($request->input('export') === 'pdf') {
            $pdf = Pdf::loadView('finance::reports.dues_aging_pdf', compact('data'));
            return $pdf->stream("Dues-Aging-Defaulters-" . date('Y-m-d') . ".pdf");
        }

        return view('finance::reports.dues_aging', compact('data'));
    }

    public function memberPassbook(Request $request)
    {
        $flatId = (int) $request->input('flat_id');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $flats = Flat::with(['block', 'residents.user'])->where('status', 'occupied')->get();
        $passbook = null;

        if ($flatId) {
            $passbook = $this->reportService->getMemberPassbook($flatId, $startDate, $endDate);

            if ($request->input('export') === 'pdf') {
                $pdf = Pdf::loadView('finance::reports.member_passbook_pdf', compact('passbook'));
                return $pdf->stream("Statement-Flat-{$passbook['flat']->flat_no}.pdf");
            }
        }

        return view('finance::reports.member_passbook', compact('flats', 'passbook', 'flatId', 'startDate', 'endDate'));
    }
}
