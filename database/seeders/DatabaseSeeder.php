<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with clean, exact society data.
     */
    public function run(): void
    {
        $this->call([
            CleanSocietySeeder::class,
        ]);
    }
}
