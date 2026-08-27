<?php

namespace Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Modules\Finance\Models\BankAccount;
use Modules\Finance\Models\Invoice;
use Modules\Finance\Models\Payment;
use Modules\Finance\Models\PaymentVoucher;
use Modules\Finance\Models\VendorBill;
use Modules\Finance\Services\FinancialReportService;

class FinanceDashboardController extends Controller
{
    public function __construct(
        protected FinancialReportService $reportService
    ) {}

    public function index()
    {
        $startOfMonth = now()->startOfMonth()->toDateString();
        $endOfMonth = now()->endOfMonth()->toDateString();

        // 1. Collections this month
        $collectionsThisMonth = Payment::whereBetween('payment_date', [$startOfMonth, $endOfMonth])
            ->where('status', 'completed')
            ->sum('amount');

        // 2. Outstanding / Overdue Dues
        $totalOutstandingDues = Invoice::whereIn('status', ['unpaid', 'partially_paid', 'overdue'])
            ->sum('balance_due');

        // 3. Expenses disbursed this month
        $expensesThisMonth = PaymentVoucher::whereBetween('voucher_date', [$startOfMonth, $endOfMonth])
            ->where('approval_status', 'paid')
            ->sum('amount');

        // 4. Total Liquid Bank & Cash Balances
        $totalLiquidCash = BankAccount::where('status', 'active')->sum('current_balance');

        // 5. Recent Invoices
        $recentInvoices = Invoice::with(['user', 'flat.block'])->latest('id')->limit(5)->get();

        // 6. Recent Payments
        $recentPayments = Payment::with(['user', 'flat.block', 'bankAccount'])->latest('id')->limit(5)->get();

        // 7. Vouchers requiring approval
        $pendingVouchers = PaymentVoucher::with(['vendor', 'bankAccount'])
            ->whereIn('approval_status', ['draft', 'submitted'])
            ->latest('id')
            ->limit(5)
            ->get();

        // 8. Dues Aging Summary
        $agingData = $this->reportService->getDuesAgingReport();

        return view('finance::dashboard.index', compact(
            'collectionsThisMonth',
            'totalOutstandingDues',
            'expensesThisMonth',
            'totalLiquidCash',
            'recentInvoices',
            'recentPayments',
            'pendingVouchers',
            'agingData'
        ));
    }
}
