<?php

namespace Modules\Finance\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Finance\Models\Account;
use Modules\Finance\Models\BankAccount;
use Modules\Finance\Models\FiscalYear;
use Modules\Finance\Models\Fund;

class FinanceDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ChartOfAccountsSeeder::class,
            FinancePermissionsSeeder::class,
        ]);

        // 1. Create Default Active Fiscal Year (April 1 to March 31 or current year)
        $currentYear = date('Y');
        $nextYear = $currentYear + 1;
        $fiscalYear = FiscalYear::firstOrCreate(
            ['title' => "FY {$currentYear}-{$nextYear}"],
            [
                'start_date' => "{$currentYear}-04-01",
                'end_date' => "{$nextYear}-03-31",
                'is_active' => true,
                'is_closed' => false,
            ]
        );

        // 2. Create Default Society Bank Account linked to 1020
        $bankAccountHead = Account::where('code', '1020')->first();
        if ($bankAccountHead && !BankAccount::where('account_number', '987654321001')->exists()) {
            BankAccount::create([
                'account_id' => $bankAccountHead->id,
                'bank_name' => 'State Bank of India',
                'account_name' => 'Universal Society Maintenance A/c',
                'account_number' => '987654321001',
                'ifsc_code' => 'SBIN0001234',
                'branch' => 'Main City Branch',
                'account_type' => 'current',
                'opening_balance' => 250000.00,
                'current_balance' => 250000.00,
                'status' => 'active',
            ]);
            $bankAccountHead->update(['current_balance' => 250000.00]);
        }

        // 3. Create Default Petty Cash Account linked to 1050
        $pettyCashHead = Account::where('code', '1050')->first();
        if ($pettyCashHead && !BankAccount::where('account_type', 'cash')->exists()) {
            BankAccount::create([
                'account_id' => $pettyCashHead->id,
                'bank_name' => 'Cash Register',
                'account_name' => 'Society Office Petty Cash',
                'account_number' => 'CASH-01',
                'ifsc_code' => null,
                'branch' => 'Society Clubhouse Office',
                'account_type' => 'cash',
                'opening_balance' => 10000.00,
                'current_balance' => 10000.00,
                'status' => 'active',
            ]);
            $pettyCashHead->update(['current_balance' => 10000.00]);
        }

        // 4. Create Sinking Fund record linked to 3010
        $sinkingFundHead = Account::where('code', '3010')->first();
        if ($sinkingFundHead && !Fund::where('name', 'General Sinking Fund')->exists()) {
            Fund::create([
                'name' => 'General Sinking Fund',
                'type' => 'sinking_fund',
                'account_id' => $sinkingFundHead->id,
                'principal_amount' => 500000.00,
                'current_balance' => 500000.00,
                'interest_rate' => 6.50,
                'start_date' => "{$currentYear}-04-01",
                'status' => 'active',
                'notes' => 'Mandatory sinking fund for structural repairs and painting',
            ]);
            $sinkingFundHead->update(['current_balance' => 500000.00]);
        }
    }
}
