<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FlatType;
use App\Models\Flat;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class FixDatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->command->info("=== Starting Database Inspection and Fix ===");

        // 1. Ensure all Flat Types exist and have valid maintenance rates (> 0)
        $this->command->info("1. Checking Flat Types...");
        $defaultTypes = [
            [
                'name' => '1 BHK',
                'owner_maintenance_fee' => 1500.00,
                'rental_maintenance_fee' => 2000.00,
                'description' => '1 Bedroom, Hall, Kitchen',
                'status' => 'active',
            ],
            [
                'name' => '2 BHK',
                'owner_maintenance_fee' => 2500.00,
                'rental_maintenance_fee' => 3200.00,
                'description' => '2 Bedroom, Hall, Kitchen',
                'status' => 'active',
            ],
            [
                'name' => '3 BHK',
                'owner_maintenance_fee' => 3500.00,
                'rental_maintenance_fee' => 4500.00,
                'description' => '3 Bedroom, Hall, Kitchen',
                'status' => 'active',
            ],
            [
                'name' => '4 BHK',
                'owner_maintenance_fee' => 5000.00,
                'rental_maintenance_fee' => 6500.00,
                'description' => '4 Bedroom, Hall, Kitchen',
                'status' => 'active',
            ],
            [
                'name' => '5 BHK',
                'owner_maintenance_fee' => 7500.00,
                'rental_maintenance_fee' => 9000.00,
                'description' => '5 Bedroom, Hall, Kitchen',
                'status' => 'active',
            ],
        ];

        foreach ($defaultTypes as $typeData) {
            $ft = FlatType::where('name', $typeData['name'])->first();
            if (!$ft) {
                FlatType::create($typeData);
                $this->command->info("Created missing Flat Type: {$typeData['name']}");
            } else {
                // If fee is 0 or null, fix it
                if ($ft->owner_maintenance_fee <= 0 || $ft->rental_maintenance_fee <= 0) {
                    $ft->update([
                        'owner_maintenance_fee' => $ft->owner_maintenance_fee <= 0 ? $typeData['owner_maintenance_fee'] : $ft->owner_maintenance_fee,
                        'rental_maintenance_fee' => $ft->rental_maintenance_fee <= 0 ? $typeData['rental_maintenance_fee'] : $ft->rental_maintenance_fee,
                        'status' => 'active'
                    ]);
                    $this->command->info("Updated 0.00 fee for Flat Type: {$ft->name} (Owner: {$ft->owner_maintenance_fee}, Rental: {$ft->rental_maintenance_fee})");
                }
            }
        }

        $allFlatTypeIds = FlatType::where('status', 'active')->pluck('id')->toArray();
        if (empty($allFlatTypeIds)) {
            $allFlatTypeIds = FlatType::pluck('id')->toArray();
        }

        // 2. Check all Flats
        $this->command->info("2. Checking Flats...");
        $flats = Flat::all();
        $flatsFixed = 0;
        foreach ($flats as $flat) {
            $needsUpdate = false;
            if (!$flat->flat_type_id || !in_array($flat->flat_type_id, $allFlatTypeIds)) {
                $flat->flat_type_id = $allFlatTypeIds[array_rand($allFlatTypeIds)];
                $needsUpdate = true;
            }
            if (empty($flat->status)) {
                $flat->status = 'occupied';
                $needsUpdate = true;
            }
            if ($needsUpdate) {
                $flat->save();
                $flatsFixed++;
            }
        }
        $this->command->info("Checked {$flats->count()} flats. Fixed {$flatsFixed} flats with missing/invalid Flat Type.");

        // 3. Check all Residents
        $this->command->info("3. Checking Residents...");
        $residents = Resident::with(['user', 'flat.flatType'])->get();
        $residentsFixed = 0;
        foreach ($residents as $resident) {
            $needsUpdate = false;
            
            // Normalize resident type
            $type = strtolower(trim($resident->type));
            if ($type === 'tenant' || $type === 'rent') {
                $type = 'rental';
            }
            if ($type !== 'owner' && $type !== 'rental') {
                $type = 'owner';
            }
            if ($resident->type !== $type) {
                $resident->type = $type;
                $needsUpdate = true;
            }

            // Ensure flat_id is valid
            if (!$resident->flat) {
                // assign to a random flat
                $randomFlat = Flat::first();
                if ($randomFlat) {
                    $resident->flat_id = $randomFlat->id;
                    $needsUpdate = true;
                }
            }

            if ($needsUpdate) {
                $resident->save();
                $residentsFixed++;
            }
        }
        $this->command->info("Checked {$residents->count()} residents. Fixed {$residentsFixed} residents.");

        // 4. Check Nicolas Thompson specifically
        $nicolas = Resident::with(['user', 'flat.flatType'])->whereHas('user', function($q) {
            $q->where('name', 'like', '%Nicolas%');
        })->first();

        if ($nicolas) {
            $name = $nicolas->user->name ?? 'Unknown';
            $flatNo = $nicolas->flat->flat_no ?? 'None';
            $typeName = $nicolas->flat->flatType->name ?? 'None';
            $ownerFee = $nicolas->flat->flatType->owner_maintenance_fee ?? 0;
            $rentalFee = $nicolas->flat->flatType->rental_maintenance_fee ?? 0;
            $resType = $nicolas->type;
            $fee = ($resType === 'owner') ? $ownerFee : $rentalFee;
            
            $this->command->info("=== Nicolas Thompson Record ===");
            $this->command->info("Name: {$name}, Flat: {$flatNo}, Type: {$resType}, Flat Type: {$typeName}");
            $this->command->info("Calculated Monthly Fee: {$fee} (Owner Rate: {$ownerFee}, Rental Rate: {$rentalFee})");
        } else {
            $this->command->info("Nicolas Thompson resident record not found.");
        }

        $this->command->info("=== Database Check and Fix Completed Successfully! ===");
    }
}
