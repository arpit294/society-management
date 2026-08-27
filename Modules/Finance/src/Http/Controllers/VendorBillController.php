<?php

namespace Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Modules\Finance\DataTables\VendorBillsDataTable;
use Modules\Finance\Models\Account;
use Modules\Finance\Models\BankAccount;
use Modules\Finance\Models\Vendor;
use Modules\Finance\Models\VendorBill;
use Modules\Finance\Services\AccountingEngineService;

class VendorBillController extends Controller
{
    public function __construct(
        protected AccountingEngineService $accountingEngine
    ) {}

    public function index(VendorBillsDataTable $dataTable)
    {
        $vendors = Vendor::where('status', 'active')->orderBy('name')->get();
        $expenseAccounts = Account::where('type', 'expense')->where('status', 'active')->get();
        $bankAccounts = BankAccount::where('status', 'active')->get();

        return $dataTable->render('finance::payables.bills', compact('vendors', 'expenseAccounts', 'bankAccounts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'vendor_id' => 'required|exists:finance_vendors,id',
            'expense_account_id' => 'required|exists:finance_chart_of_accounts,id',
            'bill_number' => 'required|string|max:50',
            'bill_date' => 'required|date',
            'due_date' => 'required|date',
            'subtotal' => 'required|numeric|min:0.01',
            'tax_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        try {
            $subtotal = (float) $request->subtotal;
            $tax = (float) ($request->tax_amount ?? 0);
            $total = $subtotal + $tax;

            $attachmentPath = null;
            if ($request->hasFile('attachment')) {
                $attachmentPath = $request->file('attachment')->store('finance/bills', 'public');
            }

            $apAccount = Account::where('code', config('finance.default_accounts.accounts_payable', '2010'))->firstOrFail();
            $expenseAccount = Account::findOrFail($request->expense_account_id);

            $bill = VendorBill::create([
                'vendor_id' => $request->vendor_id,
                'expense_account_id' => $expenseAccount->id,
                'bill_number' => $request->bill_number,
                'bill_date' => $request->bill_date,
                'due_date' => $request->due_date,
                'subtotal' => $subtotal,
                'tax_amount' => $tax,
                'total_amount' => $total,
                'paid_amount' => 0.00,
                'balance_due' => $total,
                'status' => 'unpaid',
                'notes' => $request->notes,
                'attachment_path' => $attachmentPath,
                'created_by' => auth()->id() ?? 1,
            ]);

            // Post double-entry: Debit Expense Account (50xx), Credit Accounts Payable (2010)
            $this->accountingEngine->postTransaction(
                "Vendor Bill {$bill->bill_number} from {$bill->vendor->name}",
                [
                    [
                        'account_id' => $expenseAccount->id,
                        'debit' => $total,
                        'credit' => 0,
                        'description' => "Expense: {$expenseAccount->name} (Bill #{$bill->bill_number})",
                    ],
                    [
                        'account_id' => $apAccount->id,
                        'debit' => 0,
                        'credit' => $total,
                        'description' => "Payable to {$bill->vendor->name}",
                    ],
                ],
                $bill,
                $bill->bill_date
            );

            return response()->json([
                'success' => true,
                'message' => "Vendor Bill {$bill->bill_number} recorded successfully.",
                'bill' => $bill,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function destroy(VendorBill $vendorBill)
    {
        if ($vendorBill->paid_amount > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete bill that has already been paid or partially paid.',
            ], 422);
        }

        $vendorBill->delete();

        return response()->json([
            'success' => true,
            'message' => 'Bill deleted successfully.',
        ]);
    }
}
