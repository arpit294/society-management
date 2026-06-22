<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin will hold all permissions now (no separate Super Admin role)
        Role::firstOrCreate(
            ['name' => 'Admin'],
            ['permissions' => all_permissions()]
        );

        Role::firstOrCreate(['name' => 'User']);
    }
}
