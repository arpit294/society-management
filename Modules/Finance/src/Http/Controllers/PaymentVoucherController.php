<?php

namespace Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Finance\DataTables\VouchersDataTable;
use Modules\Finance\Models\Account;
use Modules\Finance\Models\BankAccount;
use Modules\Finance\Models\BankTransaction;
use Modules\Finance\Models\PaymentVoucher;
use Modules\Finance\Models\VendorBill;
use Modules\Finance\Services\AccountingEngineService;

class PaymentVoucherController extends Controller
{
    public function __construct(
        protected AccountingEngineService $accountingEngine
    ) {}

    public function index(VouchersDataTable $dataTable)
    {
        $bankAccounts = BankAccount::where('status', 'active')->get();
        return $dataTable->render('finance::payables.vouchers', compact('bankAccounts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'vendor_bill_id' => 'required|exists:finance_vendor_bills,id',
            'bank_account_id' => 'required|exists:finance_bank_accounts,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_mode' => 'required|string',
            'reference_no' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        try {
            $bill = VendorBill::findOrFail($request->vendor_bill_id);
            $amount = (float) $request->amount;

            if ($amount > $bill->balance_due) {
                throw new Exception("Voucher amount (₹{$amount}) cannot exceed remaining bill balance due (₹{$bill->balance_due}).");
            }

            $yearStr = date('Y');
            $count = PaymentVoucher::whereYear('created_at', $yearStr)->count() + 1;
            $voucherNumber = sprintf("%s%s-%05d", config('finance.billing.voucher_prefix', 'VCH-'), $yearStr, $count);

            $voucher = PaymentVoucher::create([
                'voucher_number' => $voucherNumber,
                'vendor_bill_id' => $bill->id,
                'vendor_id' => $bill->vendor_id,
                'bank_account_id' => $request->bank_account_id,
                'voucher_date' => now()->toDateString(),
                'amount' => $amount,
                'payment_mode' => $request->payment_mode,
                'reference_no' => $request->reference_no,
                'description' => $request->description ?? "Payment for Bill {$bill->bill_number}",
                'approval_status' => 'draft',
                'created_by' => auth()->id() ?? 1,
            ]);

            return response()->json([
                'success' => true,
                'message' => "Payment Voucher {$voucherNumber} created as Draft. Ready for approval.",
                'voucher' => $voucher,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function approve(PaymentVoucher $voucher)
    {
        $voucher->update([
            'approval_status' => 'approved',
            'approved_by' => auth()->id() ?? 1,
            'approved_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Voucher {$voucher->voucher_number} approved successfully.",
        ]);
    }

    public function disburse(PaymentVoucher $voucher)
    {
        if ($voucher->approval_status !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Only approved vouchers can be disbursed.',
            ], 422);
        }

        try {
            DB::transaction(function () use ($voucher) {
                $bankAccount = $voucher->bankAccount;
                if (!$bankAccount) {
                    throw new Exception("Disbursing bank account not specified.");
                }

                if ($bankAccount->current_balance < $voucher->amount) {
                    throw new Exception("Insufficient balance in {$bankAccount->bank_name}. Available: ₹{$bankAccount->current_balance}, Required: ₹{$voucher->amount}");
                }

                $apAccount = Account::where('code', config('finance.default_accounts.accounts_payable', '2010'))->firstOrFail();

                // 1. Deduct bank balance
                $bankAccount->current_balance -= $voucher->amount;
                $bankAccount->save();

                // 2. Record bank transaction
                BankTransaction::create([
                    'bank_account_id' => $bankAccount->id,
                    'transaction_date' => now()->toDateString(),
                    'type' => 'withdrawal',
                    'amount' => $voucher->amount,
                    'reference_number' => $voucher->reference_no ?? $voucher->voucher_number,
                    'description' => "Voucher {$voucher->voucher_number} paid to {$voucher->vendor?->name}",
                    'is_reconciled' => true,
                    'reconciled_at' => now(),
                ]);

                // 3. Update Vendor Bill if linked
                if ($voucher->bill) {
                    $bill = $voucher->bill;
                    $bill->paid_amount += $voucher->amount;
                    $bill->balance_due = max(0, $bill->total_amount - $bill->paid_amount);
                    $bill->status = $bill->balance_due <= 0 ? 'paid' : 'partially_paid';
                    $bill->save();
                }

                // 4. Post Double Entry: Debit Accounts Payable (2010), Credit Bank/Cash Account
                $journalEntry = $this->accountingEngine->postTransaction(
                    "Voucher Disbursed {$voucher->voucher_number} to {$voucher->vendor?->name}",
                    [
                        [
                            'account_id' => $apAccount->id,
                            'debit' => $voucher->amount,
                            'credit' => 0,
                            'description' => "Clear AP liability for {$voucher->vendor?->name}",
                        ],
                        [
                            'account_id' => $bankAccount->account_id,
                            'debit' => 0,
                            'credit' => $voucher->amount,
                            'description' => "Payout from {$bankAccount->bank_name}",
                        ],
                    ],
                    $voucher,
                    now()->toDateString()
                );

                // 5. Update voucher status
                $voucher->update([
                    'approval_status' => 'paid',
                    'paid_at' => now(),
                    'journal_entry_id' => $journalEntry->id,
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => "Voucher {$voucher->voucher_number} successfully disbursed and posted to General Ledger.",
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function voucherPdf(PaymentVoucher $voucher)
    {
        $voucher->load(['vendor', 'bankAccount', 'bill', 'creator', 'approver']);
        $pdf = Pdf::loadView('finance::pdf.voucher', compact('voucher'));
        return $pdf->stream("Voucher-{$voucher->voucher_number}.pdf");
    }
}
