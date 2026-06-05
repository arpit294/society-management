<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\User;
use Faker\Factory as Faker;

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
