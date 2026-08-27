<?php

namespace Modules\Finance\Services;

use App\Models\Flat;
use App\Models\Resident;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Finance\Models\Account;
use Modules\Finance\Models\BankAccount;
use Modules\Finance\Models\Invoice;
use Modules\Finance\Models\JournalItem;
use Modules\Finance\Models\Payment;

class FinancialReportService
{
    /**
     * Generate Trial Balance.
     */
    public function getTrialBalance(?string $asOfDate = null): array
    {
        $date = $asOfDate ?? now()->toDateString();

        $accounts = Account::with(['journalItems' => function ($q) use ($date) {
            $q->whereHas('entry', function ($sub) use ($date) {
                $sub->whereDate('entry_date', '<=', $date)->where('status', 'posted');
            });
        }])->get();

        $rows = [];
        $totalDebit = 0.00;
        $totalCredit = 0.00;

        foreach ($accounts as $acc) {
            $debitSum = $acc->journalItems->sum('debit') + ($acc->isNormalDebit() ? $acc->opening_balance : 0);
            $creditSum = $acc->journalItems->sum('credit') + (!$acc->isNormalDebit() ? $acc->opening_balance : 0);

            $netBalance = $debitSum - $creditSum;

            if ($netBalance != 0 || $debitSum != 0 || $creditSum != 0) {
                $debit = 0.00;
                $credit = 0.00;

                if ($acc->isNormalDebit()) {
                    if ($netBalance >= 0) {
                        $debit = $netBalance;
                    } else {
                        $credit = abs($netBalance);
                    }
                } else {
                    $netCredit = $creditSum - $debitSum;
                    if ($netCredit >= 0) {
                        $credit = $netCredit;
                    } else {
                        $debit = abs($netCredit);
                    }
                }

                $rows[] = [
                    'code' => $acc->code,
                    'name' => $acc->name,
                    'type' => ucfirst($acc->type),
                    'debit' => $debit,
                    'credit' => $credit,
                ];

                $totalDebit += $debit;
                $totalCredit += $credit;
            }
        }

        return [
            'as_of_date' => $date,
            'rows' => $rows,
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'is_balanced' => abs($totalDebit - $totalCredit) < 0.01,
        ];
    }

    /**
     * Generate Income & Expenditure (Profit & Loss) Statement.
     */
    public function getIncomeExpenditure(string $startDate, string $endDate): array
    {
        $incomes = Account::where('type', 'income')->with(['journalItems' => function ($q) use ($startDate, $endDate) {
            $q->whereHas('entry', function ($sub) use ($startDate, $endDate) {
                $sub->whereBetween('entry_date', [$startDate, $endDate])->where('status', 'posted');
            });
        }])->get();

        $expenses = Account::where('type', 'expense')->with(['journalItems' => function ($q) use ($startDate, $endDate) {
            $q->whereHas('entry', function ($sub) use ($startDate, $endDate) {
                $sub->whereBetween('entry_date', [$startDate, $endDate])->where('status', 'posted');
            });
        }])->get();

        $incomeRows = [];
        $totalIncome = 0.00;
        foreach ($incomes as $inc) {
            $amount = $inc->journalItems->sum('credit') - $inc->journalItems->sum('debit');
            if ($amount != 0) {
                $incomeRows[] = ['code' => $inc->code, 'name' => $inc->name, 'amount' => $amount];
                $totalIncome += $amount;
            }
        }

        $expenseRows = [];
        $totalExpense = 0.00;
        foreach ($expenses as $exp) {
            $amount = $exp->journalItems->sum('debit') - $exp->journalItems->sum('credit');
            if ($amount != 0) {
                $expenseRows[] = ['code' => $exp->code, 'name' => $exp->name, 'amount' => $amount];
                $totalExpense += $amount;
            }
        }

        $netSurplusDeficit = $totalIncome - $totalExpense;

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'incomes' => $incomeRows,
            'total_income' => $totalIncome,
            'expenses' => $expenseRows,
            'total_expense' => $totalExpense,
            'net_surplus_deficit' => $netSurplusDeficit,
        ];
    }

    /**
     * Generate Balance Sheet (Assets vs Liabilities & Equity).
     */
    public function getBalanceSheet(?string $asOfDate = null): array
    {
        $date = $asOfDate ?? now()->toDateString();

        $assets = Account::where('type', 'asset')->where('status', 'active')->get();
        $liabilities = Account::where('type', 'liability')->where('status', 'active')->get();
        $equity = Account::where('type', 'equity')->where('status', 'active')->get();

        $totalAssets = $assets->sum('current_balance');
        $totalLiabilities = $liabilities->sum('current_balance');
        $totalEquity = $equity->sum('current_balance');

        return [
            'as_of_date' => $date,
            'assets' => $assets,
            'total_assets' => $totalAssets,
            'liabilities' => $liabilities,
            'total_liabilities' => $totalLiabilities,
            'equity' => $equity,
            'total_equity' => $totalEquity,
            'total_liabilities_and_equity' => $totalLiabilities + $totalEquity,
        ];
    }

    /**
     * Generate Dues Aging Report (0-30, 31-60, 61-90, 90+ days overdue) and Defaulters.
     */
    public function getDuesAgingReport(): array
    {
        $unpaidInvoices = Invoice::with(['user', 'flat.block'])
            ->whereIn('status', ['unpaid', 'partially_paid', 'overdue'])
            ->where('balance_due', '>', 0)
            ->get();

        $now = now();
        $defaulters = [];
        $agingBuckets = [
            '0_30' => 0.00,
            '31_60' => 0.00,
            '61_90' => 0.00,
            '90_plus' => 0.00,
            'total' => 0.00,
        ];

        foreach ($unpaidInvoices as $inv) {
            $daysOverdue = max(0, Carbon::parse($inv->due_date)->diffInDays($now, false));
            $balance = (float) $inv->balance_due;

            $aging = '0-30 days';
            if ($daysOverdue > 90) {
                $aging = '90+ days';
                $agingBuckets['90_plus'] += $balance;
            } elseif ($daysOverdue > 60) {
                $aging = '61-90 days';
                $agingBuckets['61_90'] += $balance;
            } elseif ($daysOverdue > 30) {
                $aging = '31-60 days';
                $agingBuckets['31_60'] += $balance;
            } else {
                $agingBuckets['0_30'] += $balance;
            }

            $agingBuckets['total'] += $balance;

            $flatName = ($inv->flat?->block ? 'Block ' . $inv->flat->block->block_name . ' - ' : '') . ($inv->flat?->flat_no ?? 'N/A');

            $defaulters[] = [
                'invoice_id' => $inv->id,
                'invoice_number' => $inv->invoice_number,
                'resident_name' => $inv->user?->name ?? 'N/A',
                'resident_phone' => $inv->user?->phone ?? 'N/A',
                'flat_no' => $flatName,
                'due_date' => $inv->due_date->format('d M Y'),
                'days_overdue' => (int) $daysOverdue,
                'aging_bucket' => $aging,
                'balance_due' => $balance,
            ];
        }

        return [
            'summary' => $agingBuckets,
            'defaulters' => $defaulters,
        ];
    }

    /**
     * Generate Member Statement of Account (Resident Ledger Passbook).
     */
    public function getMemberPassbook(int $flatId, ?string $startDate = null, ?string $endDate = null): array
    {
        $flat = Flat::with(['block', 'residents.user'])->findOrFail($flatId);
        
        $invoicesQuery = Invoice::where('flat_id', $flatId);
        $paymentsQuery = Payment::where('flat_id', $flatId);

        if ($startDate && $endDate) {
            $invoicesQuery->whereBetween('invoice_date', [$startDate, $endDate]);
            $paymentsQuery->whereBetween('payment_date', [$startDate, $endDate]);
        }

        $invoices = $invoicesQuery->get();
        $payments = $paymentsQuery->get();

        $ledgerEntries = collect();

        foreach ($invoices as $inv) {
            $ledgerEntries->push([
                'date' => $inv->invoice_date->format('Y-m-d'),
                'type' => 'Invoice',
                'reference' => $inv->invoice_number,
                'description' => $inv->notes ?? "Maintenance for {$inv->bill_month} {$inv->bill_year}",
                'debit' => (float) $inv->total_amount,
                'credit' => 0.00,
            ]);
        }

        foreach ($payments as $pay) {
            $ledgerEntries->push([
                'date' => $pay->payment_date->format('Y-m-d'),
                'type' => 'Receipt',
                'reference' => $pay->receipt_number,
                'description' => "Payment via " . strtoupper($pay->payment_mode) . ($pay->transaction_reference ? " (Ref: {$pay->transaction_reference})" : ''),
                'debit' => 0.00,
                'credit' => (float) $pay->amount,
            ]);
        }

        $sortedEntries = $ledgerEntries->sortBy('date')->values();
        $runningBalance = 0.00;
        $ledgerWithBalance = [];

        foreach ($sortedEntries as $entry) {
            $runningBalance += ($entry['debit'] - $entry['credit']);
            $entry['running_balance'] = $runningBalance;
            $ledgerWithBalance[] = $entry;
        }

        return [
            'flat' => $flat,
            'entries' => $ledgerWithBalance,
            'closing_balance' => $runningBalance,
        ];
    }
}
