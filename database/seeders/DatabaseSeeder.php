<?php

namespace Database\Seeders;

use App\Models\Flat;
use App\Models\Maintenance;
use App\Models\MaintenanceBill;
use App\Models\Resident;
use App\Models\Setting;
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
        Setting::truncate();
        MaintenanceBill::truncate();
        Maintenance::truncate();
        Resident::truncate();
        Flat::truncate();

        // Delete dummy resident users but preserve the primary admin/test accounts
        User::whereNotIn('email', ['arpitvadhiyari11@gmail.com', env('ADMIN_EMAIL', 'admin@gmail.com')])->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Run seeders
        $this->call([
            SystemDataSeeder::class,
            TestingDataSeeder::class,
        ]);
    }
}
