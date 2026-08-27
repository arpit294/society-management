<?php

namespace Modules\Finance\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Finance\Models\Account;

class ChartOfAccountsSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            // ASSETS (1000s)
            ['code' => '1000', 'name' => 'Current Assets', 'type' => 'asset', 'parent_id' => null, 'is_system' => true],
            ['code' => '1010', 'name' => 'Cash in Hand', 'type' => 'asset', 'parent_code' => '1000', 'is_system' => true],
            ['code' => '1020', 'name' => 'Society Bank Account', 'type' => 'asset', 'parent_code' => '1000', 'is_system' => true],
            ['code' => '1030', 'name' => 'Fixed Deposits (FD)', 'type' => 'asset', 'parent_code' => '1000', 'is_system' => true],
            ['code' => '1040', 'name' => 'Accounts Receivable (Member Dues)', 'type' => 'asset', 'parent_code' => '1000', 'is_system' => true],
            ['code' => '1050', 'name' => 'Petty Cash Register', 'type' => 'asset', 'parent_code' => '1000', 'is_system' => true],

            // LIABILITIES (2000s)
            ['code' => '2000', 'name' => 'Current Liabilities', 'type' => 'liability', 'parent_id' => null, 'is_system' => true],
            ['code' => '2010', 'name' => 'Accounts Payable (Vendors)', 'type' => 'liability', 'parent_code' => '2000', 'is_system' => true],
            ['code' => '2020', 'name' => 'Advance Maintenance Received', 'type' => 'liability', 'parent_code' => '2000', 'is_system' => true],
            ['code' => '2030', 'name' => 'Member Security Deposits', 'type' => 'liability', 'parent_code' => '2000', 'is_system' => true],

            // EQUITY / FUNDS (3000s)
            ['code' => '3000', 'name' => 'Society Capital & Reserves', 'type' => 'equity', 'parent_id' => null, 'is_system' => true],
            ['code' => '3010', 'name' => 'Sinking Fund Reserve', 'type' => 'equity', 'parent_code' => '3000', 'is_system' => true],
            ['code' => '3020', 'name' => 'General Corpus Fund', 'type' => 'equity', 'parent_code' => '3000', 'is_system' => true],
            ['code' => '3030', 'name' => 'Major Repair & Renovation Fund', 'type' => 'equity', 'parent_code' => '3000', 'is_system' => true],

            // INCOMES (4000s)
            ['code' => '4000', 'name' => 'Society Operating Revenue', 'type' => 'income', 'parent_id' => null, 'is_system' => true],
            ['code' => '4010', 'name' => 'Monthly Maintenance Charges', 'type' => 'income', 'parent_code' => '4000', 'is_system' => true],
            ['code' => '4020', 'name' => 'Sinking Fund Collections', 'type' => 'income', 'parent_code' => '4000', 'is_system' => true],
            ['code' => '4030', 'name' => 'Late Payment Fines & Interest', 'type' => 'income', 'parent_code' => '4000', 'is_system' => true],
            ['code' => '4040', 'name' => 'Name Transfer / NOC Fees', 'type' => 'income', 'parent_code' => '4000', 'is_system' => true],
            ['code' => '4050', 'name' => 'Clubhouse & Amenity Booking', 'type' => 'income', 'parent_code' => '4000', 'is_system' => true],
            ['code' => '4060', 'name' => 'Bank FD Interest Income', 'type' => 'income', 'parent_code' => '4000', 'is_system' => true],
            ['code' => '4070', 'name' => 'Miscellaneous Income', 'type' => 'income', 'parent_code' => '4000', 'is_system' => true],

            // EXPENSES (5000s)
            ['code' => '5000', 'name' => 'Society Operating Expenses', 'type' => 'expense', 'parent_id' => null, 'is_system' => true],
            ['code' => '5010', 'name' => 'Security Agency Services', 'type' => 'expense', 'parent_code' => '5000', 'is_system' => true],
            ['code' => '5020', 'name' => 'Housekeeping & Cleaning', 'type' => 'expense', 'parent_code' => '5000', 'is_system' => true],
            ['code' => '5030', 'name' => 'Elevator / Lift AMC', 'type' => 'expense', 'parent_code' => '5000', 'is_system' => true],
            ['code' => '5040', 'name' => 'Common Area Electricity & Water', 'type' => 'expense', 'parent_code' => '5000', 'is_system' => true],
            ['code' => '5050', 'name' => 'Repairs & Maintenance Work', 'type' => 'expense', 'parent_code' => '5000', 'is_system' => true],
            ['code' => '5060', 'name' => 'Administrative & Office Expenses', 'type' => 'expense', 'parent_code' => '5000', 'is_system' => true],
            ['code' => '5070', 'name' => 'Bank Charges & Fees', 'type' => 'expense', 'parent_code' => '5000', 'is_system' => true],
        ];

        // 1. Create top-level parent accounts
        $parentMap = [];
        foreach ($accounts as $acc) {
            if (empty($acc['parent_code'])) {
                $created = Account::updateOrCreate(
                    ['code' => $acc['code']],
                    [
                        'name' => $acc['name'],
                        'type' => $acc['type'],
                        'parent_id' => null,
                        'is_system' => $acc['is_system'],
                        'status' => 'active',
                    ]
                );
                $parentMap[$acc['code']] = $created->id;
            }
        }

        // 2. Create child accounts
        foreach ($accounts as $acc) {
            if (!empty($acc['parent_code'])) {
                Account::updateOrCreate(
                    ['code' => $acc['code']],
                    [
                        'name' => $acc['name'],
                        'type' => $acc['type'],
                        'parent_id' => $parentMap[$acc['parent_code']] ?? null,
                        'is_system' => $acc['is_system'],
                        'status' => 'active',
                    ]
                );
            }
        }
    }
}
