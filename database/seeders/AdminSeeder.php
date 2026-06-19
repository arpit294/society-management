<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@society.com'],
            [
                'name' => 'Society Secretary',
                'password' => Hash::make('password123'),
                'role' => 'secretary',
                'status' => 'active',
                'phone' => '9999999999',
                'aadhar_id' => '000000000000',
            ]
        );

        $this->call(RolesAndPermissionsSeeder::class);
    }
}
