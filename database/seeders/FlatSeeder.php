<?php

namespace Database\Seeders;

use App\Models\Block;
use App\Models\Flat;
use App\Models\FlatType;
use Illuminate\Database\Seeder;

class FlatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $blocks = Block::all();
        $flatTypes = FlatType::pluck('id')->toArray();

        if (empty($flatTypes)) {
            $this->command->info('No flat types found. Please create some flat types first.');

            return;
        }

        $statuses = ['empty', 'occupied'];

        foreach ($blocks as $block) {
            $totalFloors = $block->total_floor;
            $flatsPerFloor = $block->total_flats / $totalFloors;

            for ($floor = 1; $floor <= $totalFloors; $floor++) {
                for ($i = 1; $i <= $flatsPerFloor; $i++) {
                    $flatNo = sprintf('%d%02d', $floor, $i);

                    Flat::updateOrCreate([
                        'block_id' => $block->id,
                        'flat_no' => $flatNo,
                    ], [
                        'floor_no' => $floor,
                        'flat_type_id' => $flatTypes[array_rand($flatTypes)],
                        'status' => $statuses[array_rand($statuses)],
                    ]);
                }
            }
        }
    }
}
