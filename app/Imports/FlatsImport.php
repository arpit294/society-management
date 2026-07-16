<?php

namespace App\Imports;

use App\Models\Block;
use App\Models\Flat;
use App\Models\FlatType;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Log;

class FlatsImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Map horizontal structure headers like Villa No / Shop No directly into flat_no
            $flatNo = $row['flat_no'] ?? $row['villa_no'] ?? $row['shop_no'] ?? $row['office_no'] ?? $row['row_house_no'] ?? $row['unit_no'] ?? null;
            if (empty($flatNo)) {
                continue;
            }

            // Map block header
            $blockName = $row['block'] ?? $row['block_name'] ?? $row['commercial_wing'] ?? $row['phase'] ?? $row['sector'] ?? null;
            if (empty($blockName)) {
                Log::warning("Block name missing for unit '{$flatNo}'. Skipping.");
                continue;
            }

            $block = Block::where('block_name', $blockName)->orWhere('name', $blockName)->first();
            if (!$block) {
                Log::warning("Block '{$blockName}' not found for unit '{$flatNo}'. Skipping.");
                continue;
            }

            // Default floor_no to 0 during spreadsheet parsing (especially for horizontal structures like villas/shops)
            $floorNo = isset($row['floor_no']) && $row['floor_no'] !== '' ? (int) $row['floor_no'] : 0;

            // Resolve flat type
            $flatTypeId = null;
            $typeName = $row['flat_type'] ?? $row['flat_type_name'] ?? $row['category'] ?? null;
            if (!empty($typeName)) {
                $flatType = FlatType::where('name', $typeName)->first();
                if ($flatType) {
                    $flatTypeId = $flatType->id;
                }
            }

            $status = in_array(strtolower($row['status'] ?? ''), ['occupied', 'vacant', 'maintenance']) ? strtolower($row['status']) : 'vacant';

            Flat::updateOrCreate(
                [
                    'block_id' => $block->id,
                    'flat_no' => (string) $flatNo,
                ],
                [
                    'floor_no' => $floorNo,
                    'flat_type_id' => $flatTypeId,
                    'status' => $status,
                ]
            );
        }
    }
}
