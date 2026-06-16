<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\User::firstOrCreate(
            ['email' => 'admin@society.com'],
            [
                'name' => 'Society Secretary',
                'password' => \Illuminate\Support\Facades\Hash::make('password123'),
                'role' => 'secretary',
                'status' => 'active',
                'phone' => '9999999999',
                'aadhar_id' => '000000000000',
            ]
        );
    }
}
