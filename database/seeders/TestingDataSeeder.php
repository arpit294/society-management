<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TestingDataSeeder extends Seeder
{
    /**
     * Seed the dummy/testing data (blocks, flats, residents) for local development and testing.
     */
    public function run(): void
    {
        $this->call([
            BlockSeeder::class,
            FlatTypeSeeder::class,
            FlatSeeder::class,
            UserResidentSeeder::class,
        ]);
    }
}
