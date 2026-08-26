<?php

namespace Database\Seeders;

use App\Models\Block;
use App\Models\Flat;
use App\Models\FlatType;
use App\Models\Maintenance;
use App\Models\MaintenanceBill;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class CleanSocietySeeder extends Seeder
{
    /**
     * Run the clean society seeder.
     * Creates exactly 7 users (Admin, Secretary, 5 Residents), all with password 123456.
     * Creates 1 block (Block A).
     * Creates 4 unit categories with a single property unit per category.
     * Assigns each property unit to exactly 1 resident user.
     */
    public function run(): void
    {
        $this->command->info("=== Starting Clean Society Database Setup ===");

        // 1. Clean / Truncate tables cleanly
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        MaintenanceBill::truncate();
        Maintenance::truncate();
        Resident::truncate();
        Flat::truncate();
        FlatType::truncate();
        Block::truncate();
        User::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 2. Ensure Roles & Permissions and Settings exist
        $this->call([
            SettingSeeder::class,
            RolesAndPermissionsSeeder::class,
        ]);

        $password = Hash::make('123456');

        // 3. Create exactly 7 Users
        $this->command->info("Creating exactly 7 Users (password: 123456)...");

        $usersData = [
            [
                'email' => 'admin@gmail.com',
                'name' => 'Administrator',
                'phone' => '9876543210',
                'role' => 'Admin',
                'spatie_role' => 'Admin',
            ],
            [
                'email' => 'secretary@gmail.com',
                'name' => 'Society Secretary',
                'phone' => '9876543211',
                'role' => 'Secretary',
                'spatie_role' => 'Secretary',
            ],
            [
                'email' => 'arpit@gmail.com',
                'name' => 'Arpit Vadhiyari',
                'phone' => '9876543212',
                'role' => 'Owner',
                'spatie_role' => 'Owner',
            ],
            [
                'email' => 'jeelpatel@gmail.com',
                'name' => 'Jeel Patel',
                'phone' => '9876543213',
                'role' => 'Owner',
                'spatie_role' => 'Owner',
            ],
            [
                'email' => 'devpatel@gmail.com',
                'name' => 'Dev Patel',
                'phone' => '9876543214',
                'role' => 'Rental',
                'spatie_role' => 'Rental',
            ],
            [
                'email' => 'premparate@gmail.com',
                'name' => 'Prem Parate',
                'phone' => '9876543215',
                'role' => 'Owner',
                'spatie_role' => 'Owner',
            ],
            [
                'email' => 'mahendraverma@gmail.com',
                'name' => 'Mahendra Verma',
                'phone' => '9876543216',
                'role' => 'Owner',
                'spatie_role' => 'Owner',
            ],
        ];

        $createdUsers = [];
        $aadharIndex = 1;
        foreach ($usersData as $uData) {
            $user = User::create([
                'email' => $uData['email'],
                'name' => $uData['name'],
                'phone' => $uData['phone'],
                'role' => $uData['role'],
                'password' => $password,
                'status' => 'active',
                'aadhar_id' => sprintf("1000000000%02d", $aadharIndex++),
            ]);

            if (class_exists(Role::class) && Role::where('name', $uData['spatie_role'])->exists()) {
                $user->syncRoles([$uData['spatie_role']]);
            }

            $createdUsers[$uData['email']] = $user;
        }

        // 4. Create 1 Block
        $this->command->info("Creating 1 Block (Block A)...");
        $blockA = Block::create([
            'block_name' => 'A',
            'total_floor' => 5,
            'total_flats' => 5,
        ]);

        // 5. Create 9 Unit Categories (FlatTypes)
        $this->command->info("Creating 9 Unit Categories & 1 Property Unit per Category...");

        $categoriesData = [
            [
                'flat_type_name' => '1 BHK',
                'owner_fee' => 1500.00,
                'rental_fee' => 2000.00,
                'desc' => 'Standard 1 BHK Apartment',
                'unit_type' => 'flat',
                'flat_no' => '101',
                'floor_no' => 1,
                'area_sqft' => 650,
                'resident_email' => 'arpit@gmail.com',
                'resident_type' => 'owner',
                'business_name' => null,
            ],
            [
                'flat_type_name' => '2 BHK',
                'owner_fee' => 2500.00,
                'rental_fee' => 3200.00,
                'desc' => 'Standard 2 BHK Apartment',
                'unit_type' => 'flat',
                'flat_no' => '102',
                'floor_no' => 1,
                'area_sqft' => 1100,
                'resident_email' => 'jeelpatel@gmail.com',
                'resident_type' => 'owner',
                'business_name' => null,
            ],
            [
                'flat_type_name' => '3 BHK',
                'owner_fee' => 3500.00,
                'rental_fee' => 4500.00,
                'desc' => 'Standard 3 BHK Apartment',
                'unit_type' => 'flat',
                'flat_no' => '103',
                'floor_no' => 1,
                'area_sqft' => 1500,
                'resident_email' => 'arpit@gmail.com',
                'resident_type' => 'owner',
                'business_name' => null,
            ],
            [
                'flat_type_name' => '4 BHK',
                'owner_fee' => 4500.00,
                'rental_fee' => 5800.00,
                'desc' => 'Spacious 4 BHK Apartment',
                'unit_type' => 'flat',
                'flat_no' => '104',
                'floor_no' => 1,
                'area_sqft' => 2100,
                'resident_email' => 'jeelpatel@gmail.com',
                'resident_type' => 'owner',
                'business_name' => null,
            ],
            [
                'flat_type_name' => '3 BHK Villa',
                'owner_fee' => 3500.00,
                'rental_fee' => 4500.00,
                'desc' => 'Luxury 3 BHK Villa',
                'unit_type' => 'villa',
                'flat_no' => '201',
                'floor_no' => 2,
                'area_sqft' => 1800,
                'resident_email' => 'devpatel@gmail.com',
                'resident_type' => 'rental',
                'business_name' => null,
            ],

            [
                'flat_type_name' => 'Sky Penthouse',
                'owner_fee' => 6000.00,
                'rental_fee' => 7500.00,
                'desc' => 'Top Floor Luxury Penthouse',
                'unit_type' => 'penthouse',
                'flat_no' => '501',
                'floor_no' => 5,
                'area_sqft' => 3200,
                'resident_email' => 'arpit@gmail.com',
                'resident_type' => 'owner',
                'business_name' => null,
            ],
            [
                'flat_type_name' => 'Duplex Apartment',
                'owner_fee' => 4800.00,
                'rental_fee' => 6000.00,
                'desc' => 'Double Storey Duplex',
                'unit_type' => 'duplex',
                'flat_no' => '401',
                'floor_no' => 4,
                'area_sqft' => 2400,
                'resident_email' => 'jeelpatel@gmail.com',
                'resident_type' => 'owner',
                'business_name' => null,
            ],
            [
                'flat_type_name' => 'Garden Row House',
                'owner_fee' => 4000.00,
                'rental_fee' => 5200.00,
                'desc' => 'Row House with Private Garden',
                'unit_type' => 'rowhouse',
                'flat_no' => '301',
                'floor_no' => 3,
                'area_sqft' => 1950,
                'resident_email' => 'devpatel@gmail.com',
                'resident_type' => 'rental',
                'business_name' => null,
            ],
            [
                'flat_type_name' => 'Classic Tenement',
                'owner_fee' => 3000.00,
                'rental_fee' => 3800.00,
                'desc' => 'Independent Tenement House',
                'unit_type' => 'tenement',
                'flat_no' => '302',
                'floor_no' => 3,
                'area_sqft' => 1600,
                'resident_email' => 'premparate@gmail.com',
                'resident_type' => 'owner',
                'business_name' => null,
            ],

        ];

        foreach ($categoriesData as $cat) {
            // Create Category (FlatType)
            $flatType = FlatType::create([
                'name' => $cat['flat_type_name'],
                'category_type' => $cat['unit_type'],
                'owner_maintenance_fee' => $cat['owner_fee'],
                'rental_maintenance_fee' => $cat['rental_fee'],
                'status' => 'active',
            ]);

            // Create single Property Unit for this category
            $flat = Flat::create([
                'block_id' => $blockA->id,
                'flat_type_id' => $flatType->id,
                'flat_no' => $cat['flat_no'],
                'floor_no' => $cat['floor_no'],
                'area_sqft' => $cat['area_sqft'],
                'unit_type' => $cat['unit_type'],
                'status' => 'occupied',
            ]);

            // Assign single Resident to this property unit
            $residentUser = $createdUsers[$cat['resident_email']] ?? null;
            if ($residentUser) {
                Resident::create([
                    'user_id' => $residentUser->id,
                    'flat_id' => $flat->id,
                    'block_id' => $blockA->id,
                    'type' => $cat['resident_type'],
                    'move_in_date' => now()->subMonths(6)->format('Y-m-d'),
                    'move_out_date' => null,
                    'business_name' => $cat['business_name'],
                    'contact_person' => $residentUser->name,
                ]);
            }
        }

        $this->command->info("=== Clean Society Database Setup Completed Successfully! ===");
        $this->command->info("Summary: 7 Users created (Admin, Secretary, 5 Residents), 1 Block created, 9 Unit Categories created with 1 Unit & 1 Resident each.");
    }
}
