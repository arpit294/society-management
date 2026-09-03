<?php

namespace Modules\Finance\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\Finance\Models\ExpenseCategory;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class FinanceDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Seed Finance Permissions
        $permissions = [
            'maintenance_bill_view',
            'maintenance_bill_create',
            'maintenance_bill_delete',
            'expense_category_view',
            'expense_category_create',
            'expense_category_edit',
            'expense_category_delete',
            'expense_view',
            'expense_create',
            'expense_edit',
            'expense_delete',
            'name_transfer_bill_view',
            'name_transfer_bill_delete',
        ];

        if (class_exists(Permission::class)) {
            foreach ($permissions as $perm) {
                Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
            }

            // Assign permissions to Admin role if present
            if (class_exists(Role::class)) {
                $adminRole = Role::where('name', 'Admin')->orWhere('name', 'admin')->first();
                if ($adminRole) {
                    $adminRole->givePermissionTo($permissions);
                }
            }
        }

        // 2. Seed Default Expense Categories
        $defaultCategories = [
            'Electricity & Water',
            'Security & Guard',
            'Plumbing & Repairs',
            'Cleaning & Housekeeping',
            'Elevator / Lift Maintenance',
            'Garden & Landscape',
            'Administrative / Office',
            'Miscellaneous',
        ];

        foreach ($defaultCategories as $categoryTitle) {
            ExpenseCategory::firstOrCreate(
                ['title' => $categoryTitle],
                [
                    'slug' => Str::slug($categoryTitle),
                    'status' => 'active',
                ]
            );
        }
    }
}
