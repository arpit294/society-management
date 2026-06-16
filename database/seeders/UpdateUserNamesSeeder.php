<?php

namespace Database\Seeders;

use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class UpdateUserNamesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $faker = Faker::create();
        $users = User::where('name', 'like', 'Resident %')->get();

        foreach ($users as $user) {
            $user->name = $faker->name();
            $user->save();
        }
    }
}
