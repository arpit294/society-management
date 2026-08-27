<?php

namespace Modules\Finance\Services;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Modules\Finance\Models\Account;
use Modules\Finance\Models\FinancialAuditLog;
use Modules\Finance\Models\FiscalYear;
use Modules\Finance\Models\JournalEntry;
use Modules\Finance\Models\JournalItem;

class AccountingEngineService
{
    /**
     * Post a balanced double-entry journal record.
     *
     * @param string $description Summary of the transaction
     * @param array $items Array of [ 'account_id' => int, 'debit' => float, 'credit' => float, 'description' => ?string ]
     * @param Model|null $reference Linked Eloquent model (Invoice, Payment, Voucher, etc.)
     * @param string|null $entryDate Optional specific date (defaults to today)
     * @return JournalEntry
     * @throws Exception
     */
    public function postTransaction(
        string $description,
        array $items,
        ?Model $reference = null,
        ?string $entryDate = null
    ): JournalEntry {
        if (empty($items)) {
            throw new Exception("Cannot create an empty journal transaction.");
        }

        $totalDebit = 0.00;
        $totalCredit = 0.00;

        foreach ($items as $item) {
            $totalDebit += (float) ($item['debit'] ?? 0);
            $totalCredit += (float) ($item['credit'] ?? 0);
        }

        // Validate Double-Entry Balancing (within 0.01 tolerance for precision)
        if (abs(round($totalDebit, 2) - round($totalCredit, 2)) > 0.01) {
            throw new Exception("Double-entry accounting error: Total Debit (₹{$totalDebit}) must equal Total Credit (₹{$totalCredit}).");
        }

        $fiscalYear = FiscalYear::current();
        $date = $entryDate ? date('Y-m-d', strtotime($entryDate)) : date('Y-m-d');

        return DB::transaction(function () use ($description, $items, $reference, $date, $totalDebit, $totalCredit, $fiscalYear) {
            $yearStr = date('Y', strtotime($date));
            $count = JournalEntry::whereYear('entry_date', $yearStr)->count() + 1;
            $entryNumber = sprintf("JRN-%s-%05d", $yearStr, $count);

            $entry = JournalEntry::create([
                'entry_number' => $entryNumber,
                'fiscal_year_id' => $fiscalYear?->id,
                'entry_date' => $date,
                'reference_type' => $reference ? get_class($reference) : null,
                'reference_id' => $reference ? $reference->getKey() : null,
                'description' => $description,
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
                'status' => 'posted',
                'created_by' => auth()->id() ?? 1,
                'posted_at' => now(),
            ]);

            foreach ($items as $line) {
                $debit = (float) ($line['debit'] ?? 0);
                $credit = (float) ($line['credit'] ?? 0);

                JournalItem::create([
                    'journal_entry_id' => $entry->id,
                    'account_id' => $line['account_id'],
                    'debit' => $debit,
                    'credit' => $credit,
                    'description' => $line['description'] ?? $description,
                ]);

                // Update real-time balance of the account
                $this->updateAccountBalance($line['account_id'], $debit, $credit);
            }

            FinancialAuditLog::log('journal_entry.posted', $entry, null, [
                'entry_number' => $entryNumber,
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
            ]);

            return $entry;
        });
    }

    /**
     * Update account's current balance based on normal balance rule.
     */
    protected function updateAccountBalance(int $accountId, float $debit, float $credit): void
    {
        $account = Account::find($accountId);
        if (!$account) {
            return;
        }

        if ($account->isNormalDebit()) {
            // Assets & Expenses: increase with debit, decrease with credit
            $account->current_balance += ($debit - $credit);
        } else {
            // Liabilities, Equity, Income: increase with credit, decrease with debit
            $account->current_balance += ($credit - $debit);
        }

        $account->save();
    }
}
