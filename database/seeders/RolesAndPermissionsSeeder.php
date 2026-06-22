<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Permission;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Define all permissions grouped by their module name
        $permissionsByModule = [
            'Dashboard' => [
                'dashboard_view',
            ],
            'User Management' => [
                'user_view',
                'user_create',
                'user_edit',
                'user_delete',
            ],
            'Flat Management' => [
                'flat_view',
                'flat_create',
                'flat_edit',
                'flat_delete',
            ],
            'Flat Types' => [
                'flat_type_view',
                'flat_type_create',
                'flat_type_edit',
                'flat_type_delete',
            ],
            'Flat Documents' => [
                'flat_document_view',
                'flat_document_create',
                'flat_document_delete',
            ],
            'Block Management' => [
                'block_view',
                'block_create',
                'block_edit',
                'block_delete',
            ],
            'Residents' => [
                'resident_view',
                'resident_create',
                'resident_edit',
                'resident_delete',
            ],
            'Complaints' => [
                'complain_view',
                'complain_create',
                'complain_edit',
                'complain_delete',
            ],
            'Maintenance Bills' => [
                'maintenance_bill_view',
                'maintenance_bill_create',
                'maintenance_bill_delete',
            ],
            'Expense Categories' => [
                'expense_category_view',
                'expense_category_create',
                'expense_category_edit',
                'expense_category_delete',
            ],
            'Expenses' => [
                'expense_view',
                'expense_create',
                'expense_edit',
                'expense_delete',
            ],
            'Name Transfer Bills' => [
                'name_transfer_bill_view',
                'name_transfer_bill_delete',
            ],
            'Settings vc' => [
                'setting_view',
                'setting_edit',
            ],
        ];

        // 2. Create permissions in the database
        foreach ($permissionsByModule as $moduleName => $permissions) {
            foreach ($permissions as $permissionName) {
                Permission::updateOrCreate(
                    ['name' => $permissionName, 'guard_name' => 'web'],
                    ['module_name' => $moduleName]
                );
            }
        }

        // 3. Map config modules to our database module names
        $moduleMap = [
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
        ];

        // 4. Create roles and assign their fine-grained permissions
        foreach (config('roles.access') as $roleName => $moduleNames) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);

            if ($moduleNames === '*') {
                // If '*' is defined (like Secretary), sync all permissions in the system
                $role->syncPermissions(Permission::pluck('name')->toArray());
            } else {
                $rolePermissions = [];

                foreach ($moduleNames as $moduleKey) {
                    $dbModuleName = $moduleMap[$moduleKey] ?? null;
                    if ($dbModuleName) {
                        // Retrieve all fine-grained permissions for this module
                        $perms = Permission::where('module_name', $dbModuleName)->pluck('name')->toArray();
                        $rolePermissions = array_merge($rolePermissions, $perms);
                    }
                }

                $role->syncPermissions($rolePermissions);
            }
        }

        // 5. Re-sync all users with their roles based on their 'role' column
        User::query()
            ->whereNotNull('role')
            ->each(fn (User $user) => $user->syncRoles([$user->role]));
    }
}
