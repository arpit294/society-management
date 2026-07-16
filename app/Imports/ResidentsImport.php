<?php

namespace App\Imports;

use App\Models\Block;
use App\Models\Flat;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class ResidentsImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Skip empty rows
            $flatNo = $row['flat'] ?? $row['flat_no'] ?? $row['villa_no'] ?? $row['shop_no'] ?? $row['office_no'] ?? $row['row_house_no'] ?? $row['unit_no'] ?? null;
            $blockName = $row['block'] ?? $row['block_name'] ?? $row['commercial_wing'] ?? $row['phase'] ?? $row['sector'] ?? null;

            if (!isset($row['email']) || empty($flatNo) || empty($blockName)) {
                continue;
            }

            // 1. Find or create the user
            $user = User::firstOrCreate(
                ['email' => $row['email']],
                [
                    'name' => $row['name'],
                    'phone' => $row['phone'] ?? null,
                    'password' => Hash::make('password'),
                    'role' => in_array(strtolower($row['type']), ['owner', 'rental']) ? strtolower($row['type']) : 'owner',
                    'status' => 'active',
                ]
            );

            // Update user role if it's currently a default or different role, but typically we just set it
            if (!in_array($user->role, ['Admin', 'secretary', 'committee_member'])) {
                $user->update(['role' => in_array(strtolower($row['type']), ['owner', 'rental']) ? strtolower($row['type']) : 'owner']);
            }

            // 2. Find the block and flat
            $block = Block::where('name', $blockName)->orWhere('block_name', $blockName)->first();
            if (!$block) {
                Log::warning("Block '{$blockName}' not found for resident {$row['name']}. Skipping.");
                continue;
            }

            $flat = Flat::where('block_id', $block->id)->where('flat_no', $flatNo)->first();
            if (!$flat) {
                Log::warning("Flat '{$flatNo}' not found in block '{$blockName}' for resident {$row['name']}. Skipping.");
                continue;
            }

            // 3. Check if flat is already occupied by an active resident
            $isOccupied = Resident::where('flat_id', $flat->id)
                ->whereNull('move_out_date')
                ->exists();

            if ($isOccupied) {
                Log::warning("Flat '{$flatNo}' in block '{$blockName}' is already occupied. Skipping resident {$row['name']}.");
                continue; // Skip or handle it based on requirements
            }

            // 4. Create the resident
            $type = in_array(strtolower($row['type']), ['owner', 'rental']) ? strtolower($row['type']) : 'owner';
            
            // Convert Excel date to standard date if needed
            $moveInDate = null;
            if (isset($row['move_in_date'])) {
                if (is_numeric($row['move_in_date'])) {
                    $moveInDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['move_in_date'])->format('Y-m-d');
                } else {
                    $moveInDate = date('Y-m-d', strtotime($row['move_in_date']));
                }
            } else {
                $moveInDate = now()->format('Y-m-d');
            }

            Resident::create([
                'block_id' => $block->id,
                'flat_id' => $flat->id,
                'user_id' => $user->id,
                'type' => $type,
                'move_in_date' => $moveInDate,
            ]);
        }
    }
}
