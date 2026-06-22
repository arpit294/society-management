<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Role;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all permissions from config
        $allPermissions = [];
        $modules = config('permissions.modules', []);
        foreach ($modules as $module => $perms) {
            $allPermissions = array_merge($allPermissions, $perms);
        }

        // Create roles and assign created permissions
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin'], ['permissions' => $allPermissions]);
        
        $admin = Role::firstOrCreate(['name' => 'Admin'], ['permissions' => [
            'user_view', 'user_create', 'user_edit'
        ]]);

        $user = Role::firstOrCreate(['name' => 'User']);
    }
}
