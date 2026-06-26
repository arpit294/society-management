<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SystemDataSeeder extends Seeder
{
    /**
     * Seed the core system data required for the application to function.
     * Includes Roles, Permissions, Default Settings, and Admin User.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            AdminUserSeeder::class,
            SettingSeeder::class,
        ]);
    }
}
