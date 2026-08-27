<?php

namespace Modules\Finance\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class FinancePermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'finance_dashboard_view',
            'finance_invoice_view',
            'finance_invoice_create',
            'finance_invoice_cancel',
            'finance_payment_receive',
            'finance_receipt_print',
            'finance_vendor_manage',
            'finance_bill_create',
            'finance_voucher_create',
            'finance_voucher_approve',
            'finance_voucher_pay',
            'finance_petty_cash_manage',
            'finance_bank_manage',
            'finance_bank_reconcile',
            'finance_journal_create',
            'finance_coa_manage',
            'finance_reports_view',
            'finance_reports_export',
            'finance_settings_manage',
        ];

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);
        }

        // Assign all permissions to Admin
        $adminRole = Role::where('name', 'Admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo($permissions);
        }

        // Assign Secretary permissions
        $secretaryRole = Role::where('name', 'Secretary')->first();
        if ($secretaryRole) {
            $secretaryRole->givePermissionTo([
                'finance_dashboard_view',
                'finance_invoice_view',
                'finance_invoice_create',
                'finance_payment_receive',
                'finance_receipt_print',
                'finance_vendor_manage',
                'finance_bill_create',
                'finance_voucher_create',
                'finance_petty_cash_manage',
                'finance_reports_view',
                'finance_reports_export',
            ]);
        }

        // Assign Member permissions (Owners / Rentals view their invoices & receipts)
        $memberRoles = Role::whereIn('name', ['Owner', 'Rental'])->get();
        foreach ($memberRoles as $role) {
            $role->givePermissionTo([
                'finance_invoice_view',
                'finance_receipt_print',
            ]);
        }
    }
}
