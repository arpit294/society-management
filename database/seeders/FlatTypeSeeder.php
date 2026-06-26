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
                'owner_maintenance_fee' => 1000,
                'rental_maintenance_fee' => 1500,
                'description' => '1 Bedroom, Hall, Kitchen',
                'status' => 'active',
            ],
            [
                'name' => '2 BHK',
                'owner_maintenance_fee' => 2000,
                'rental_maintenance_fee' => 2500,
                'description' => '2 Bedroom, Hall, Kitchen',
                'status' => 'active',
            ],
            [
                'name' => '3 BHK',
                'owner_maintenance_fee' => 3000,
                'rental_maintenance_fee' => 3500,
                'description' => '3 Bedroom, Hall, Kitchen',
                'status' => 'active',
            ],
            [
                'name' => '4 BHK',
                'owner_maintenance_fee' => 4000,
                'rental_maintenance_fee' => 4500,
                'description' => '4 Bedroom, Hall, Kitchen',
                'status' => 'active',
            ],
        ];

        foreach ($types as $type) {
            FlatType::updateOrCreate(['name' => $type['name']], $type);
        }
    }
}
