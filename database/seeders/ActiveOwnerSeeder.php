<?php

namespace Database\Seeders;

use App\Models\Block;
use App\Models\Flat;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ActiveOwnerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all flats
        $flats = Flat::all();

        foreach ($flats as $flat) {
            // Check if this flat has an active owner
            $hasActiveOwner = Resident::where('flat_id', $flat->id)
                ->where('type', 'owner')
                ->where(function ($q) {
                    $q->whereNull('move_out_date')->orWhere('move_out_date', '>=', now()->startOfDay());
                })
                ->exists();

            if (!$hasActiveOwner) {
                $blockName = $flat->block ? $flat->block->block_name : 'Unknown';
                
                // Create a dummy user for the owner
                $ownerUser = User::firstOrCreate(
                    ['email' => "owner_{$blockName}_{$flat->flat_no}@system.local"],
                    [
                        'name' => "Dummy Owner {$blockName}-{$flat->flat_no}",
                        'phone' => '0000000000',
                        'aadhar_id' => '000000000000',
                        'password' => Hash::make('password'),
                        'role' => 'owner',
                        'status' => 'active',
                    ]
                );

                // Create the resident record
                Resident::create([
                    'user_id' => $ownerUser->id,
                    'flat_id' => $flat->id,
                    'block_id' => $flat->block_id,
                    'type' => 'owner',
                    'move_in_date' => now()->subYear()->format('Y-m-d'),
                ]);

                // Update flat status to occupied
                if ($flat->status !== 'occupied') {
                    $flat->update(['status' => 'occupied']);
                }
            }
        }
    }
}
