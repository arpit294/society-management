<?php

namespace Modules\Finance\Services;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\MaintenanceBill;
use App\Models\NameTransferBill;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Finance\Models\Account;
use Modules\Finance\Models\BankAccount;
use Modules\Finance\Models\Invoice;
use Modules\Finance\Models\InvoiceItem;
use Modules\Finance\Models\Payment;
use Modules\Finance\Models\Vendor;
use Modules\Finance\Models\VendorBill;

class LegacyDataMigrationService
{
    public function __construct(
        protected AccountingEngineService $accountingEngine
    ) {}

    /**
     * Migrate all legacy tables into the Finance Module.
     */
    public function migrateAll(): array
    {
        $expensesMigrated = $this->migrateExpenses();
        $maintenanceMigrated = $this->migrateMaintenanceBills();
        $transfersMigrated = $this->migrateNameTransferBills();

        return [
            'expenses_migrated' => $expensesMigrated,
            'maintenance_bills_migrated' => $maintenanceMigrated,
            'transfers_migrated' => $transfersMigrated,
        ];
    }

    /**
     * Convert legacy expenses into Vendor Bills.
     */
    public function migrateExpenses(): int
    {
        $count = 0;
        $legacyExpenses = Expense::with('expenseCategory')->get();
        $apAccount = Account::where('code', config('finance.default_accounts.accounts_payable', '2010'))->first();
        $bankAccount = BankAccount::first();

        // Default or generic vendor
        $defaultVendor = Vendor::firstOrCreate(
            ['name' => 'General Society Vendor'],
            [
                'service_type' => 'Maintenance & Supplies',
                'status' => 'active',
            ]
        );

        foreach ($legacyExpenses as $exp) {
            $catName = $exp->expenseCategory ? $exp->expenseCategory->name : 'Miscellaneous';
            
            // Map or create Account Head
            $accountHead = Account::where('name', 'like', "%{$catName}%")->where('type', 'expense')->first();
            if (!$accountHead) {
                $accountHead = Account::create([
                    'code' => '509' . (Account::where('type', 'expense')->count() + 1),
                    'name' => $catName . ' (Migrated)',
                    'type' => 'expense',
                    'parent_id' => Account::where('code', '5000')->value('id'),
                    'is_system' => false,
                    'status' => 'active',
                ]);
            }

            $date = $exp->expense_date ?? $exp->created_at->toDateString();
            $billNo = 'LEGACY-EXP-' . $exp->id;

            if (!VendorBill::where('bill_number', $billNo)->exists()) {
                $amount = (float) $exp->amount;
                $bill = VendorBill::create([
                    'bill_number' => $billNo,
                    'vendor_id' => $defaultVendor->id,
                    'expense_account_id' => $accountHead->id,
                    'bill_date' => $date,
                    'due_date' => $date,
                    'subtotal' => $amount,
                    'tax_amount' => 0.00,
                    'total_amount' => $amount,
                    'paid_amount' => $amount,
                    'balance_due' => 0.00,
                    'status' => 'paid',
                    'notes' => $exp->description ?? 'Migrated legacy expense record',
                    'created_by' => 1,
                ]);

                // Post historical ledger entry
                $this->accountingEngine->postTransaction(
                    "Historical Expense #{$exp->id} - {$catName}",
                    [
                        [
                            'account_id' => $accountHead->id,
                            'debit' => $amount,
                            'credit' => 0,
                            'description' => $exp->description ?? "Expense: {$catName}",
                        ],
                        [
                            'account_id' => $bankAccount->account_id,
                            'debit' => 0,
                            'credit' => $amount,
                            'description' => "Paid from {$bankAccount->bank_name}",
                        ],
                    ],
                    $bill,
                    $date
                );

                $count++;
            }
        }

        return $count;
    }

    /**
     * Convert legacy maintenance bills into Invoices and Payments.
     */
    public function migrateMaintenanceBills(): int
    {
        $count = 0;
        $legacyBills = MaintenanceBill::with(['resident.user', 'flat.block'])->get();
        $arAccount = Account::where('code', config('finance.default_accounts.accounts_receivable', '1040'))->first();
        $incomeAccount = Account::where('code', config('finance.default_accounts.maintenance_income', '4010'))->first();
        $bankAccount = BankAccount::first();

        foreach ($legacyBills as $mb) {
            $user = $mb->resident?->user;
            $flat = $mb->flat;

            if (!$user || !$flat) continue;

            $invNumber = 'LEGACY-INV-' . $mb->id;
            if (Invoice::where('invoice_number', $invNumber)->exists()) continue;

            $amount = (float) $mb->amount;
            $isPaid = ($mb->status === 'paid');
            $date = $mb->created_at ? $mb->created_at->toDateString() : now()->toDateString();
            $dueDate = $mb->due_date ? Carbon::parse($mb->due_date)->toDateString() : $date;

            $invoice = Invoice::create([
                'invoice_number' => $invNumber,
                'user_id' => $user->id,
                'flat_id' => $flat->id,
                'invoice_date' => $date,
                'due_date' => $dueDate,
                'bill_month' => $mb->month ?? Carbon::parse($date)->format('F'),
                'bill_year' => $mb->year ?? Carbon::parse($date)->year,
                'invoice_type' => 'maintenance',
                'subtotal' => $amount,
                'late_fee' => 0.00,
                'discount' => 0.00,
                'total_amount' => $amount,
                'paid_amount' => $isPaid ? $amount : 0.00,
                'balance_due' => $isPaid ? 0.00 : $amount,
                'status' => $isPaid ? 'paid' : 'unpaid',
                'notes' => 'Migrated maintenance bill #' . $mb->id,
                'created_by' => 1,
            ]);

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'account_id' => $incomeAccount->id,
                'item_name' => "Maintenance Fee ({$invoice->bill_month} {$invoice->bill_year})",
                'description' => "Flat {$flat->flat_no}",
                'amount' => $amount,
            ]);

            // Post Invoice Journal: Debit AR, Credit Income
            $this->accountingEngine->postTransaction(
                "Historical Invoice {$invNumber} for Flat {$flat->flat_no}",
                [
                    [
                        'account_id' => $arAccount->id,
                        'debit' => $amount,
                        'credit' => 0,
                        'description' => "Receivable from {$user->name}",
                    ],
                    [
                        'account_id' => $incomeAccount->id,
                        'debit' => 0,
                        'credit' => $amount,
                        'description' => "Maintenance Revenue",
                    ],
                ],
                $invoice,
                $date
            );

            // If bill was paid, record Payment Receipt
            if ($isPaid) {
                $payDate = $mb->payment_date ? Carbon::parse($mb->payment_date)->toDateString() : $date;
                $recNumber = 'LEGACY-REC-' . $mb->id;

                $payment = Payment::create([
                    'receipt_number' => $recNumber,
                    'invoice_id' => $invoice->id,
                    'user_id' => $user->id,
                    'flat_id' => $flat->id,
                    'bank_account_id' => $bankAccount->id,
                    'payment_date' => $payDate,
                    'amount' => $amount,
                    'payment_mode' => $mb->payment_mode ?? 'bank_transfer',
                    'transaction_reference' => $mb->transaction_reference ?? 'LEGACY-PAID',
                    'status' => 'completed',
                    'remarks' => "Migrated payment for {$invNumber}",
                    'received_by' => 1,
                ]);

                // Post Payment Journal: Debit Bank, Credit AR
                $this->accountingEngine->postTransaction(
                    "Historical Receipt {$recNumber} for Flat {$flat->flat_no}",
                    [
                        [
                            'account_id' => $bankAccount->account_id,
                            'debit' => $amount,
                            'credit' => 0,
                            'description' => "Receipt deposited in {$bankAccount->bank_name}",
                        ],
                        [
                            'account_id' => $arAccount->id,
                            'debit' => 0,
                            'credit' => $amount,
                            'description' => "Clear AR from {$user->name}",
                        ],
                    ],
                    $payment,
                    $payDate
                );
            }

            $count++;
        }

        return $count;
    }

    /**
     * Convert legacy Name Transfer Bills.
     */
    public function migrateNameTransferBills(): int
    {
        $count = 0;
        $legacyBills = NameTransferBill::with(['transferor', 'transferee', 'flat.block'])->get();
        $incomeAccount = Account::where('code', config('finance.default_accounts.name_transfer_income', '4040'))->first();
        $bankAccount = BankAccount::first();

        foreach ($legacyBills as $tb) {
            $user = $tb->transferee ?? $tb->transferor;
            $flat = $tb->flat;
            if (!$user || !$flat) continue;

            $invNumber = 'LEGACY-TRF-' . $tb->id;
            if (Invoice::where('invoice_number', $invNumber)->exists()) continue;

            $amount = (float) $tb->transfer_fee;
            $date = $tb->created_at ? $tb->created_at->toDateString() : now()->toDateString();

            $invoice = Invoice::create([
                'invoice_number' => $invNumber,
                'user_id' => $user->id,
                'flat_id' => $flat->id,
                'invoice_date' => $date,
                'due_date' => $date,
                'bill_month' => Carbon::parse($date)->format('F'),
                'bill_year' => Carbon::parse($date)->year,
                'invoice_type' => 'name_transfer',
                'subtotal' => $amount,
                'late_fee' => 0.00,
                'discount' => 0.00,
                'total_amount' => $amount,
                'paid_amount' => $amount,
                'balance_due' => 0.00,
                'status' => 'paid',
                'notes' => "Name Transfer Bill #{$tb->id} (Flat {$flat->flat_no})",
                'created_by' => 1,
            ]);

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'account_id' => $incomeAccount->id,
                'item_name' => "Name Transfer Fee",
                'description' => "Flat {$flat->flat_no}",
                'amount' => $amount,
            ]);

            $count++;
        }

        return $count;
    }
}
