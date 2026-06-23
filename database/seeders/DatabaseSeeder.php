<?php

namespace Database\Seeders;

use App\Models\Flat;
use App\Models\Maintenance;
use App\Models\MaintenanceBill;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Truncate tables to ensure a clean slate
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        MaintenanceBill::truncate();
        Maintenance::truncate();
        Resident::truncate();
        Flat::truncate();

        // Delete dummy resident users but preserve the primary admin/test accounts
        User::whereNotIn('email', ['arpitvadhiyari11@gmail.com', 'admin@gmail.com'])->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Run seeders
        $this->call([
            RoleAndPermissionSeeder::class,
            AdminUserSeeder::class,
            BlockSeeder::class,
            FlatTypeSeeder::class,
            FlatSeeder::class,
            UserResidentSeeder::class,
            RolesAndPermissionsSeeder::class,
        ]);
    }
}
