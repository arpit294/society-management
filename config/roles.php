<?php

/**
 * Roles & access control — single source of truth.
 *
 * Each "module" is one permission (e.g. "users", "flats").
 * If a role has a module, it can view/create/edit/delete within that module.
 *
 * To give a role access to a new page, add the module here and re-run the seeder.
 */
return [

    'all' => ['owner', 'rental', 'security', 'committee_member', 'secretary'],

    'self_register' => ['owner', 'rental'],

    'staff' => ['security', 'committee_member', 'secretary'],

    'labels' => [
        'owner' => 'Owner',
        'rental' => 'Rental',
        'security' => 'Security',
        'committee_member' => 'Committee Member',
        'secretary' => 'Secretary',
    ],

    /*
    |--------------------------------------------------------------------------
    | Modules (one permission per feature area)
    |--------------------------------------------------------------------------
    */
    'modules' => [
        'dashboard' => 'Dashboard',
        'users' => 'User Management',
        'flats' => 'Flat Management',
        'flat-types' => 'Flat Types',
        'flat-documents' => 'Flat Documents',
        'blocks' => 'Block Management',
        'residents' => 'Residents',
        'complains' => 'Complaints',
        'maintenance-bills' => 'Maintenance Bills',
        'expense-categories' => 'Expense Categories',
        'expenses' => 'Expenses',
        'name-transfer-bills' => 'Name Transfer Bills',
        'settings' => 'Settings vc',
    ],

    /*
    |--------------------------------------------------------------------------
    | Role → module access
    |--------------------------------------------------------------------------
    | Use '*' for full access (secretary).
    */
    'access' => [
        'secretary' => '*',

        'committee_member' => [
            'dashboard',
            'flats',
            'flat-types',
            'flat-documents',
            'blocks',
            'residents',
            'complains',
            'maintenance-bills',
            'expense-categories',
            'expenses',
            'name-transfer-bills',
        ],

        'security' => [
            'dashboard',
            'flats',
            'residents',
            'complains',
        ],

        'owner' => [
            'dashboard',
            'complains',
            'maintenance-bills',
        ],

        'rental' => [
            'dashboard',
            'complains',
            'maintenance-bills',
        ],
    ],

];
