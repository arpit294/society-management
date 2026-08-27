<?php

namespace Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Http\Request;
use Modules\Finance\DataTables\PaymentsDataTable;
use Modules\Finance\Models\BankAccount;
use Modules\Finance\Models\Invoice;
use Modules\Finance\Models\Payment;
use Modules\Finance\Services\BillingGenerationService;

class PaymentController extends Controller
{
    public function __construct(
        protected BillingGenerationService $billingService
    ) {}

    public function index(PaymentsDataTable $dataTable)
    {
        return $dataTable->render('finance::payments.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'invoice_id' => 'required|exists:finance_invoices,id',
            'bank_account_id' => 'required|exists:finance_bank_accounts,id',
            'amount' => 'required|numeric|min:1',
            'payment_date' => 'required|date',
            'payment_mode' => 'required|string',
            'transaction_reference' => 'nullable|string',
            'remarks' => 'nullable|string',
        ]);

        try {
            $invoice = Invoice::findOrFail($request->invoice_id);
            $payment = $this->billingService->recordPayment($invoice, $request->all());

            return response()->json([
                'success' => true,
                'message' => "Payment of ₹" . number_format($payment->amount, 2) . " recorded successfully. Receipt #{$payment->receipt_number}",
                'payment' => $payment,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function receiptPdf(Payment $payment)
    {
        $payment->load(['user', 'flat.block', 'invoice.items', 'bankAccount']);
        $pdf = Pdf::loadView('finance::pdf.receipt', compact('payment'));
        return $pdf->stream("Receipt-{$payment->receipt_number}.pdf");
    }
}
