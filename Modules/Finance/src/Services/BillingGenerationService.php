<?php

namespace Modules\Finance\Services;

use App\Models\Flat;
use App\Models\Resident;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Modules\Finance\Models\Account;
use Modules\Finance\Models\Invoice;
use Modules\Finance\Models\InvoiceItem;
use Modules\Finance\Models\Payment;

class BillingGenerationService
{
    public function __construct(
        protected AccountingEngineService $accountingEngine
    ) {}

    /**
     * Generate monthly maintenance invoices in batch for all occupied flats.
     *
     * @param string $month e.g., 'August'
     * @param int $year e.g., 2026
     * @param string|null $dueDate e.g., '2026-08-20'
     * @param int|null $blockId Optional filter by Block
     * @return array Summary of generated bills
     */
    public function generateBatchMonthlyBills(string $month, int $year, ?string $dueDate = null, ?int $blockId = null): array
    {
        $due = $dueDate ? Carbon::parse($dueDate) : Carbon::createFromDate($year, Carbon::parse($month)->month, 1)->addDays(config('finance.billing.default_due_days', 15));
        
        $flatsQuery = Flat::with(['block', 'flatType', 'residents.user'])
            ->where('status', 'occupied');

        if ($blockId) {
            $flatsQuery->where('block_id', $blockId);
        }

        $flats = $flatsQuery->get();
        $generatedCount = 0;
        $skippedCount = 0;
        $totalAmount = 0.00;

        $arAccount = Account::where('code', config('finance.default_accounts.accounts_receivable', '1040'))->first();
        $incomeAccount = Account::where('code', config('finance.default_accounts.maintenance_income', '4010'))->first();

        if (!$arAccount || !$incomeAccount) {
            throw new Exception("Core accounting accounts (AR 1040 / Income 4010) are missing from Chart of Accounts.");
        }

        foreach ($flats as $flat) {
            // Find active primary resident
            $activeResident = $flat->residents()
                ->where(function ($q) {
                    $q->whereNull('move_out_date')->orWhere('move_out_date', '>', now());
                })
                ->latest()
                ->first();

            if (!$activeResident || !$activeResident->user) {
                $skippedCount++;
                continue;
            }

            // Check if bill already exists for this flat, month and year
            $exists = Invoice::where('flat_id', $flat->id)
                ->where('bill_month', $month)
                ->where('bill_year', $year)
                ->where('invoice_type', 'maintenance')
                ->exists();

            if ($exists) {
                $skippedCount++;
                continue;
            }

            // Calculate maintenance fee
            $fee = 0.00;
            $flatType = $flat->flatType;
            if ($flatType) {
                if (strtolower($activeResident->type) === 'rental') {
                    $fee = (float) ($flatType->rental_maintenance_fee > 0 ? $flatType->rental_maintenance_fee : $flatType->owner_maintenance_fee);
                } else {
                    $fee = (float) $flatType->owner_maintenance_fee;
                }
            }

            if ($fee <= 0) {
                $fee = 1000.00; // default fallback
            }

            DB::transaction(function () use ($flat, $activeResident, $month, $year, $due, $fee, $arAccount, $incomeAccount, &$generatedCount, &$totalAmount) {
                $yearStr = date('Y');
                $count = Invoice::whereYear('created_at', $yearStr)->count() + 1;
                $invoiceNumber = sprintf("%s%s-%05d", config('finance.billing.invoice_prefix', 'INV-'), $yearStr, $count);

                $invoice = Invoice::create([
                    'invoice_number' => $invoiceNumber,
                    'user_id' => $activeResident->user_id,
                    'flat_id' => $flat->id,
                    'invoice_date' => now()->toDateString(),
                    'due_date' => $due->toDateString(),
                    'bill_month' => $month,
                    'bill_year' => $year,
                    'invoice_type' => 'maintenance',
                    'subtotal' => $fee,
                    'late_fee' => 0.00,
                    'discount' => 0.00,
                    'total_amount' => $fee,
                    'paid_amount' => 0.00,
                    'balance_due' => $fee,
                    'status' => 'unpaid',
                    'notes' => "Monthly Maintenance for {$month} {$year} ({$flat->flat_no})",
                    'created_by' => auth()->id() ?? 1,
                ]);

                // Create line item
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'account_id' => $incomeAccount->id,
                    'item_name' => "Maintenance Charges ({$month} {$year})",
                    'description' => "Flat {$flat->flat_no} - " . ($flat->block ? 'Block ' . $flat->block->block_name : ''),
                    'amount' => $fee,
                ]);

                // Post Double Entry: Debit AR (1040), Credit Maintenance Income (4010)
                $journalEntry = $this->accountingEngine->postTransaction(
                    "Invoice {$invoiceNumber} for Flat {$flat->flat_no} ({$month} {$year})",
                    [
                        [
                            'account_id' => $arAccount->id,
                            'debit' => $fee,
                            'credit' => 0,
                            'description' => "Receivable from {$activeResident->user->name} for Flat {$flat->flat_no}",
                        ],
                        [
                            'account_id' => $incomeAccount->id,
                            'debit' => 0,
                            'credit' => $fee,
                            'description' => "Maintenance Income from {$flat->flat_no} ({$month} {$year})",
                        ],
                    ],
                    $invoice,
                    $invoice->invoice_date
                );

                $invoice->update(['journal_entry_id' => $journalEntry->id]);

                $generatedCount++;
                $totalAmount += $fee;
            });
        }

        return [
            'generated_count' => $generatedCount,
            'skipped_count' => $skippedCount,
            'total_amount' => $totalAmount,
            'month' => $month,
            'year' => $year,
        ];
    }

    /**
     * Create a one-off custom / auxiliary invoice (e.g. Name Transfer, NOC, Amenity).
     */
    public function createAuxiliaryInvoice(array $data): Invoice
    {
        $arAccount = Account::where('code', config('finance.default_accounts.accounts_receivable', '1040'))->firstOrFail();
        $incomeAccount = Account::findOrFail($data['income_account_id']);

        return DB::transaction(function () use ($data, $arAccount, $incomeAccount) {
            $yearStr = date('Y');
            $count = Invoice::whereYear('created_at', $yearStr)->count() + 1;
            $invoiceNumber = sprintf("%s%s-%05d", config('finance.billing.invoice_prefix', 'INV-'), $yearStr, $count);
            $amount = (float) $data['amount'];

            $invoice = Invoice::create([
                'invoice_number' => $invoiceNumber,
                'user_id' => $data['user_id'],
                'flat_id' => $data['flat_id'],
                'invoice_date' => $data['invoice_date'] ?? now()->toDateString(),
                'due_date' => $data['due_date'] ?? now()->addDays(7)->toDateString(),
                'bill_month' => Carbon::parse($data['invoice_date'] ?? now())->format('F'),
                'bill_year' => Carbon::parse($data['invoice_date'] ?? now())->year,
                'invoice_type' => $data['invoice_type'] ?? 'custom',
                'subtotal' => $amount,
                'late_fee' => 0.00,
                'discount' => 0.00,
                'total_amount' => $amount,
                'paid_amount' => 0.00,
                'balance_due' => $amount,
                'status' => 'unpaid',
                'notes' => $data['notes'] ?? null,
                'created_by' => auth()->id() ?? 1,
            ]);

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'account_id' => $incomeAccount->id,
                'item_name' => $data['item_name'] ?? ucfirst($invoice->invoice_type) . ' Charge',
                'description' => $data['description'] ?? null,
                'amount' => $amount,
            ]);

            // Post double entry: Debit AR, Credit specific income account
            $journalEntry = $this->accountingEngine->postTransaction(
                "Auxiliary Invoice {$invoiceNumber} ({$invoice->invoice_type})",
                [
                    [
                        'account_id' => $arAccount->id,
                        'debit' => $amount,
                        'credit' => 0,
                        'description' => "Receivable for {$invoice->invoice_type}",
                    ],
                    [
                        'account_id' => $incomeAccount->id,
                        'debit' => 0,
                        'credit' => $amount,
                        'description' => $incomeAccount->name,
                    ],
                ],
                $invoice,
                $invoice->invoice_date
            );

            $invoice->update(['journal_entry_id' => $journalEntry->id]);

            return $invoice;
        });
    }

    /**
     * Record payment received for an invoice with automatic double-entry posting.
     */
    public function recordPayment(Invoice $invoice, array $data): Payment
    {
        return DB::transaction(function () use ($invoice, $data) {
            $amount = (float) $data['amount'];
            if ($amount <= 0 || $amount > $invoice->balance_due) {
                throw new Exception("Invalid payment amount ₹{$amount}. Balance due is ₹{$invoice->balance_due}.");
            }

            $yearStr = date('Y');
            $count = Payment::whereYear('created_at', $yearStr)->count() + 1;
            $receiptNumber = sprintf("%s%s-%05d", config('finance.billing.receipt_prefix', 'REC-'), $yearStr, $count);

            $bankAccount = \Modules\Finance\Models\BankAccount::findOrFail($data['bank_account_id']);
            $arAccount = Account::where('code', config('finance.default_accounts.accounts_receivable', '1040'))->firstOrFail();

            $payment = Payment::create([
                'receipt_number' => $receiptNumber,
                'invoice_id' => $invoice->id,
                'user_id' => $invoice->user_id,
                'flat_id' => $invoice->flat_id,
                'bank_account_id' => $bankAccount->id,
                'payment_date' => $data['payment_date'] ?? now()->toDateString(),
                'amount' => $amount,
                'payment_mode' => $data['payment_mode'] ?? 'bank_transfer',
                'transaction_reference' => $data['transaction_reference'] ?? null,
                'status' => 'completed',
                'remarks' => $data['remarks'] ?? "Payment for Invoice {$invoice->invoice_number}",
                'received_by' => auth()->id() ?? 1,
            ]);

            // Update invoice balances
            $invoice->paid_amount += $amount;
            $invoice->balance_due = max(0, $invoice->total_amount - $invoice->paid_amount);
            $invoice->status = $invoice->balance_due <= 0 ? 'paid' : 'partially_paid';
            $invoice->save();

            // Update Bank Account balance
            $bankAccount->current_balance += $amount;
            $bankAccount->save();

            // Record Bank Transaction
            \Modules\Finance\Models\BankTransaction::create([
                'bank_account_id' => $bankAccount->id,
                'transaction_date' => $payment->payment_date,
                'type' => 'deposit',
                'amount' => $amount,
                'reference_number' => $payment->transaction_reference ?? $receiptNumber,
                'description' => "Receipt {$receiptNumber} from {$invoice->user->name} for Flat {$invoice->flat->flat_no}",
                'is_reconciled' => true,
                'reconciled_at' => now(),
            ]);

            // Post Double Entry: Debit Bank/Cash Account (linked to bank_account->account_id), Credit AR (1040)
            $journalEntry = $this->accountingEngine->postTransaction(
                "Receipt {$receiptNumber} for Invoice {$invoice->invoice_number}",
                [
                    [
                        'account_id' => $bankAccount->account_id,
                        'debit' => $amount,
                        'credit' => 0,
                        'description' => "Payment deposit into {$bankAccount->bank_name}",
                    ],
                    [
                        'account_id' => $arAccount->id,
                        'debit' => 0,
                        'credit' => $amount,
                        'description' => "Clear receivable from {$invoice->user->name}",
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
