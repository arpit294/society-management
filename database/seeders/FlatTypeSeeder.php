<?php

namespace Database\Seeders;

use App\Models\FlatType;
use Illuminate\Database\Seeder;

class FlatTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            [
                'name' => '1 BHK',
                'maintenance_fee' => 1000,
                'penalty_per_day' => 10,
                'description' => '1 Bedroom, Hall, Kitchen',
                'status' => 'active',
            ],
            [
                'name' => '2 BHK',
                'maintenance_fee' => 2000,
                'penalty_per_day' => 20,
                'description' => '2 Bedroom, Hall, Kitchen',
                'status' => 'active',
            ],
            [
                'name' => '3 BHK',
                'maintenance_fee' => 3000,
                'penalty_per_day' => 30,
                'description' => '3 Bedroom, Hall, Kitchen',
                'status' => 'active',
            ],
            [
                'name' => '4 BHK',
                'maintenance_fee' => 4000,
                'penalty_per_day' => 40,
                'description' => '4 Bedroom, Hall, Kitchen',
                'status' => 'active',
            ],
        ];

        foreach ($types as $type) {
            FlatType::updateOrCreate(['name' => $type['name']], $type);
        }
    }
}
