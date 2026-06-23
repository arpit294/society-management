<?php

namespace Database\Seeders;

use App\Models\Block;
use Illuminate\Database\Seeder;

class BlockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $blocks = [
            [
                'block_name' => 'A',
                'total_floor' => 10,
                'total_flats' => 40,
                'created_at' => now(),
            ],
            [
                'block_name' => 'B',
                'total_floor' => 10,
                'total_flats' => 40,
                'created_at' => now(),
            ],
            [
                'block_name' => 'C',
                'total_floor' => 12,
                'total_flats' => 48,
                'created_at' => now(),
            ],
        ];

        foreach ($blocks as $block) {
            Block::updateOrCreate(['block_name' => $block['block_name']], $block);
        }
    }
}
