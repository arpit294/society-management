<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Resident;
use App\Models\Flat;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;

class UserResidentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $flats = Flat::with('block')->get();
        $faker = Faker::create();
        $password = Hash::make('123456');

        foreach ($flats as $flat) {
            // Create user
            $user = User::create([
                'name' => 'Resident ' . $flat->block->block_name . '-' . $flat->flat_no,
                'email' => strtolower($flat->block->block_name . $flat->flat_no . '@example.com'),
                'phone' => $faker->numerify('##########'),
                'role' => 'owner',
                'password' => $password,
                'aadhar_id' => $faker->numerify('############'),
                'status' => 'active',
            ]);

            // Create resident record
            Resident::create([
                'block_id' => $flat->block_id,
                'flat_id' => $flat->id,
                'user_id' => $user->id,
                'type' => 'owner',
                'move_in_date' => now(),
            ]);

            // Update flat status
            $flat->update(['status' => 'occupied']);
        }
    }
}
