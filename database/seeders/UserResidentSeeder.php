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

        foreach ($flats as $index => $flat) {
            // Determine scenario based on index
            $scenario = $index % 4; 

            if ($scenario == 0) {
                // Scenario 0: Past Owner + Current Owner
                $pastOwnerUser = User::create([
                    'name' => $faker->name,
                    'email' => strtolower($flat->block->block_name . $flat->flat_no . '_past_owner@example.com'),
                    'phone' => $faker->numerify('##########'),
                    'role' => 'owner',
                    'password' => $password,
                    'aadhar_id' => $faker->numerify('############'),
                    'status' => 'active',
                ]);
                Resident::create([
                    'block_id' => $flat->block_id,
                    'flat_id' => $flat->id,
                    'user_id' => $pastOwnerUser->id,
                    'type' => 'owner',
                    'move_in_date' => now()->subYears(5),
                    'move_out_date' => now()->subYears(2),
                ]);

                $currentOwnerUser = User::create([
                    'name' => $faker->name,
                    'email' => strtolower($flat->block->block_name . $flat->flat_no . '@example.com'),
                    'phone' => $faker->numerify('##########'),
                    'role' => 'owner',
                    'password' => $password,
                    'aadhar_id' => $faker->numerify('############'),
                    'status' => 'active',
                ]);
                Resident::create([
                    'block_id' => $flat->block_id,
                    'flat_id' => $flat->id,
                    'user_id' => $currentOwnerUser->id,
                    'type' => 'owner',
                    'move_in_date' => now()->subYears(2),
                ]);
                $flat->update(['status' => 'occupied']);

            } elseif ($scenario == 1) {
                // Scenario 1: Owner + Past Tenant + Current Tenant
                $ownerUser = User::create([
                    'name' => $faker->name,
                    'email' => strtolower($flat->block->block_name . $flat->flat_no . '@example.com'),
                    'phone' => $faker->numerify('##########'),
                    'role' => 'owner',
                    'password' => $password,
                    'aadhar_id' => $faker->numerify('############'),
                    'status' => 'active',
                ]);
                Resident::create([
                    'block_id' => $flat->block_id,
                    'flat_id' => $flat->id,
                    'user_id' => $ownerUser->id,
                    'type' => 'owner',
                    'move_in_date' => now()->subYears(4),
                ]);

                $pastTenant = User::create([
                    'name' => $faker->name,
                    'email' => strtolower($flat->block->block_name . $flat->flat_no . '_past_tenant@example.com'),
                    'phone' => $faker->numerify('##########'),
                    'role' => 'rental',
                    'password' => $password,
                    'aadhar_id' => $faker->numerify('############'),
                    'status' => 'active',
                ]);
                Resident::create([
                    'block_id' => $flat->block_id,
                    'flat_id' => $flat->id,
                    'user_id' => $pastTenant->id,
                    'type' => 'rental',
                    'move_in_date' => now()->subYears(3),
                    'move_out_date' => now()->subMonths(6),
                ]);

                $currentTenant = User::create([
                    'name' => $faker->name,
                    'email' => strtolower($flat->block->block_name . $flat->flat_no . '_tenant@example.com'),
                    'phone' => $faker->numerify('##########'),
                    'role' => 'rental',
                    'password' => $password,
                    'aadhar_id' => $faker->numerify('############'),
                    'status' => 'active',
                ]);
                Resident::create([
                    'block_id' => $flat->block_id,
                    'flat_id' => $flat->id,
                    'user_id' => $currentTenant->id,
                    'type' => 'rental',
                    'move_in_date' => now()->subMonths(5),
                ]);
                $flat->update(['status' => 'occupied']);

            } elseif ($scenario == 2) {
                // Scenario 2: Owner + Current Tenant
                $ownerUser = User::create([
                    'name' => $faker->name,
                    'email' => strtolower($flat->block->block_name . $flat->flat_no . '@example.com'),
                    'phone' => $faker->numerify('##########'),
                    'role' => 'owner',
                    'password' => $password,
                    'aadhar_id' => $faker->numerify('############'),
                    'status' => 'active',
                ]);
                Resident::create([
                    'block_id' => $flat->block_id,
                    'flat_id' => $flat->id,
                    'user_id' => $ownerUser->id,
                    'type' => 'owner',
                    'move_in_date' => now()->subYears(2),
                ]);

                $currentTenant = User::create([
                    'name' => $faker->name,
                    'email' => strtolower($flat->block->block_name . $flat->flat_no . '_tenant@example.com'),
                    'phone' => $faker->numerify('##########'),
                    'role' => 'rental',
                    'password' => $password,
                    'aadhar_id' => $faker->numerify('############'),
                    'status' => 'active',
                ]);
                Resident::create([
                    'block_id' => $flat->block_id,
                    'flat_id' => $flat->id,
                    'user_id' => $currentTenant->id,
                    'type' => 'rental',
                    'move_in_date' => now()->subMonths(2),
                ]);
                $flat->update(['status' => 'occupied']);

            } else {
                // Scenario 3: Just an Owner
                $user = User::create([
                    'name' => $faker->name,
                    'email' => strtolower($flat->block->block_name . $flat->flat_no . '@example.com'),
                    'phone' => $faker->numerify('##########'),
                    'role' => 'owner',
                    'password' => $password,
                    'aadhar_id' => $faker->numerify('############'),
                    'status' => 'active',
                ]);

                Resident::create([
                    'block_id' => $flat->block_id,
                    'flat_id' => $flat->id,
                    'user_id' => $user->id,
                    'type' => 'owner',
                    'move_in_date' => now()->subMonths(rand(1, 24)),
                ]);
                $flat->update(['status' => 'occupied']);
            }
        }
    }
}
