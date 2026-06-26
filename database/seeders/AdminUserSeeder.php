<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@gmail.com')],
            [
                'name' => 'Administrator',
                'password' => Hash::make(env('ADMIN_PASSWORD', '123456')),
                'role' => 'Admin',
                'phone' => null,
                'aadhar_id' => '',
                'status' => 'active',
            ]
        );
    }
}
