<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('123456'),
                'role' => 'Admin',
                'phone' => 7896541230,
                'aadhar_id' => '',
                'status' => 'active',
            ]
        );

        if (class_exists(Role::class) && Role::where('name', 'Admin')->exists()) {
            $user->syncRoles(['Admin']);
        }
    }
}
