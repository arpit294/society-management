<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Flat;
use App\Models\Resident;
use App\Models\MaintenanceBill;
use App\Models\Maintenance;
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
        User::where('id', '>', 1)->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Run seeders
        $this->call([
            FlatSeeder::class,
            UserResidentSeeder::class,
        ]);
    }
}
