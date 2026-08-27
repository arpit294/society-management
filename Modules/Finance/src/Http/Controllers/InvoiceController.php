<?php

namespace Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Block;
use App\Models\Flat;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Http\Request;
use Modules\Finance\DataTables\InvoicesDataTable;
use Modules\Finance\Models\Account;
use Modules\Finance\Models\BankAccount;
use Modules\Finance\Models\Invoice;
use Modules\Finance\Services\BillingGenerationService;

class InvoiceController extends Controller
{
    public function __construct(
        protected BillingGenerationService $billingService
    ) {}

    public function index(InvoicesDataTable $dataTable)
    {
        $blocks = Block::orderBy('block_name')->get();
        $bankAccounts = BankAccount::where('status', 'active')->get();
        $incomeAccounts = Account::where('type', 'income')->where('status', 'active')->get();
        $flats = Flat::with(['block', 'residents.user'])->where('status', 'occupied')->get();

        return $dataTable->render('finance::billing.index', compact('blocks', 'bankAccounts', 'incomeAccounts', 'flats'));
    }

    public function generateBatch(Request $request)
    {
        $request->validate([
            'month' => 'required|string',
            'year' => 'required|integer',
            'due_date' => 'nullable|date',
            'block_id' => 'nullable|integer|exists:blocks,id',
        ]);

        try {
            $result = $this->billingService->generateBatchMonthlyBills(
                $request->month,
                (int) $request->year,
                $request->due_date,
                $request->block_id ? (int) $request->block_id : null
            );

            return response()->json([
                'success' => true,
                'message' => "Successfully generated {$result['generated_count']} maintenance invoices totaling ₹" . number_format($result['total_amount'], 2) . " ({$result['skipped_count']} skipped).",
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'flat_id' => 'required|exists:flats,id',
            'income_account_id' => 'required|exists:finance_chart_of_accounts,id',
            'invoice_type' => 'required|string',
            'amount' => 'required|numeric|min:1',
            'item_name' => 'required|string',
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        try {
            $invoice = $this->billingService->createAuxiliaryInvoice($request->all());

            return response()->json([
                'success' => true,
                'message' => "Invoice {$invoice->invoice_number} created successfully.",
                'invoice' => $invoice,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['user', 'flat.block', 'items.account', 'payments.bankAccount', 'journalEntry.items.account']);
        $bankAccounts = BankAccount::where('status', 'active')->get();

        return view('finance::billing.show', compact('invoice', 'bankAccounts'));
    }

    public function downloadPdf(Invoice $invoice)
    {
        $invoice->load(['user', 'flat.block', 'items.account', 'payments']);
        $pdf = Pdf::loadView('finance::pdf.invoice', compact('invoice'));
        return $pdf->stream("Invoice-{$invoice->invoice_number}.pdf");
    }
}
