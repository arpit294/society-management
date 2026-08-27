<?php

namespace Modules\Finance\Services;

use App\Models\Flat;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Modules\Finance\Models\Account;
use Modules\Finance\Models\BankAccount;
use Modules\Finance\Models\Invoice;
use Modules\Finance\Models\Payment;

class AdvancePaymentService
{
    public function __construct(
        protected AccountingEngineService $accountingEngine
    ) {}

    /**
     * Record an Advance / Prepaid deposit from a resident into Account 2020 (Advance Maintenance).
     */
    public function depositAdvance(User $user, Flat $flat, BankAccount $bankAccount, float $amount, string $mode = 'bank_transfer', ?string $ref = null): Payment
    {
        return DB::transaction(function () use ($user, $flat, $bankAccount, $amount, $mode, $ref) {
            $yearStr = date('Y');
            $count = Payment::whereYear('created_at', $yearStr)->count() + 1;
            $receiptNumber = sprintf("%s%s-%05d", config('finance.billing.receipt_prefix', 'REC-'), $yearStr, $count);

            $advanceAccount = Account::where('code', config('finance.default_accounts.advance_maintenance', '2020'))->firstOrFail();

            $payment = Payment::create([
                'receipt_number' => $receiptNumber,
                'invoice_id' => null,
                'user_id' => $user->id,
                'flat_id' => $flat->id,
                'bank_account_id' => $bankAccount->id,
                'payment_date' => now()->toDateString(),
                'amount' => $amount,
                'payment_mode' => $mode,
                'transaction_reference' => $ref,
                'status' => 'completed',
                'remarks' => "Advance maintenance deposit for Flat {$flat->flat_no}",
                'received_by' => auth()->id() ?? 1,
            ]);

            // Update bank balance
            $bankAccount->current_balance += $amount;
            $bankAccount->save();

            // Post double-entry: Debit Bank Account (Asset), Credit Advance Maintenance (Liability 2020)
            $journalEntry = $this->accountingEngine->postTransaction(
                "Advance Deposit {$receiptNumber} from {$user->name} (Flat {$flat->flat_no})",
                [
                    [
                        'account_id' => $bankAccount->account_id,
                        'debit' => $amount,
                        'credit' => 0,
                        'description' => "Deposit in {$bankAccount->bank_name}",
                    ],
                    [
                        'account_id' => $advanceAccount->id,
                        'debit' => 0,
                        'credit' => $amount,
                        'description' => "Advance maintenance liability from {$user->name}",
                    ],
                ],
                $payment,
                $payment->payment_date
            );

            $payment->update(['journal_entry_id' => $journalEntry->id]);

            return $payment;
        });
    }

    /**
     * Apply advance credit against an unpaid invoice.
     */
    public function applyAdvanceToInvoice(Invoice $invoice, float $amountToApply): Payment
    {
        return DB::transaction(function () use ($invoice, $amountToApply) {
            $advanceAccount = Account::where('code', config('finance.default_accounts.advance_maintenance', '2020'))->firstOrFail();
            $arAccount = Account::where('code', config('finance.default_accounts.accounts_receivable', '1040'))->firstOrFail();

            // Default cash bank account for record linking
            $defaultBank = BankAccount::first();

            $yearStr = date('Y');
            $count = Payment::whereYear('created_at', $yearStr)->count() + 1;
            $receiptNumber = sprintf("%s%s-%05d", config('finance.billing.receipt_prefix', 'REC-'), $yearStr, $count);

            $payment = Payment::create([
                'receipt_number' => $receiptNumber,
                'invoice_id' => $invoice->id,
                'user_id' => $invoice->user_id,
                'flat_id' => $invoice->flat_id,
                'bank_account_id' => $defaultBank->id,
                'payment_date' => now()->toDateString(),
                'amount' => $amountToApply,
                'payment_mode' => 'advance_adjustment',
                'transaction_reference' => "ADV-ADJUST-{$invoice->invoice_number}",
                'status' => 'completed',
                'remarks' => "Advance maintenance wallet adjustment for Invoice {$invoice->invoice_number}",
                'received_by' => auth()->id() ?? 1,
            ]);

            $invoice->paid_amount += $amountToApply;
            $invoice->balance_due = max(0, $invoice->total_amount - $invoice->paid_amount);
            $invoice->status = $invoice->balance_due <= 0 ? 'paid' : 'partially_paid';
            $invoice->save();

            // Post double-entry: Debit Advance Maintenance (Liability 2020), Credit Accounts Receivable (Asset 1040)
            $journalEntry = $this->accountingEngine->postTransaction(
                "Advance Adjustment for Invoice {$invoice->invoice_number}",
                [
                    [
                        'account_id' => $advanceAccount->id,
                        'debit' => $amountToApply,
                        'credit' => 0,
                        'description' => "Utilize advance maintenance liability for {$invoice->user->name}",
                    ],
                    [
                        'account_id' => $arAccount->id,
                        'debit' => 0,
                        'credit' => $amountToApply,
                        'description' => "Clear invoice receivable {$invoice->invoice_number}",
                    ],
                ],
                $payment,
                $payment->payment_date
            );

            $payment->update(['journal_entry_id' => $journalEntry->id]);

            return $payment;
        });
    }
}
