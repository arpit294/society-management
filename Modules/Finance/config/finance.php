<?php

return [
    'name' => 'Finance',
    'version' => '1.0.0',
    'prefix' => 'finance',
    'middleware' => ['web', 'auth'],

    // Currency settings
    'currency' => [
        'code' => 'INR',
        'symbol' => '₹',
        'precision' => 2,
    ],

    // Default System Account Codes (from Chart of Accounts)
    'default_accounts' => [
        'cash_in_hand' => '1010',
        'society_bank' => '1020',
        'fixed_deposits' => '1030',
        'accounts_receivable' => '1040',
        'petty_cash' => '1050',
        'accounts_payable' => '2010',
        'advance_maintenance' => '2020',
        'security_deposits' => '2030',
        'sinking_fund_reserve' => '3010',
        'general_corpus_fund' => '3020',
        'repair_reserve_fund' => '3030',
        'maintenance_income' => '4010',
        'sinking_fund_income' => '4020',
        'late_fee_income' => '4030',
        'name_transfer_income' => '4040',
        'amenity_booking_income' => '4050',
        'interest_income' => '4060',
        'misc_income' => '4070',
        'security_expense' => '5010',
        'housekeeping_expense' => '5020',
        'lift_amc_expense' => '5030',
        'electricity_water_expense' => '5040',
        'repair_maintenance_expense' => '5050',
        'admin_office_expense' => '5060',
        'bank_charges_expense' => '5070',
    ],

    // Billing configuration
    'billing' => [
        'invoice_prefix' => 'INV-',
        'receipt_prefix' => 'REC-',
        'voucher_prefix' => 'VCH-',
        'default_due_days' => 15,
        'late_fine_percentage' => 2.0, // 2% per month overdue
        'late_fine_fixed' => 0.0,
    ],

    // Petty Cash configuration
    'petty_cash' => [
        'imprest_limit' => 10000.00,
        'single_expense_limit' => 2000.00,
        'low_balance_threshold' => 2000.00,
    ],
];
