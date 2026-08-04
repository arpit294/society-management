<?php

namespace Tests\Feature;

use App\Models\Block;
use App\Models\Flat;
use App\Models\FlatType;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PropertyUnitCalculationTest extends TestCase
{
    use RefreshDatabase;

    public function test_fixed_rate_calculation_for_standard_flat()
    {
        $flatType = FlatType::create([
            'name' => '2BHK Standard',
            'category_type' => 'residential',
            'calculation_method' => 'fixed',
            'owner_maintenance_fee' => 1500,
            'rental_maintenance_fee' => 2000,
            'rate_per_sqft' => 0,
            'commercial_surcharge_percentage' => 0,
        ]);

        $block = Block::create([
            'block_name' => 'Block A',
            'block_type' => 'residential_tower',
            'label_type' => 'Wing',
            'total_floor' => 10,
            'total_flats' => 40,
        ]);

        $flat = Flat::create([
            'block_id' => $block->id,
            'flat_type_id' => $flatType->id,
            'flat_no' => '101',
            'floor_no' => 1,
            'unit_type' => 'flat',
            'area_sqft' => 1000,
            'status' => 'Empty',
        ]);

        $this->assertEquals(1500, $flat->calculateMaintenanceFee('owner'));
        $this->assertEquals(2000, $flat->calculateMaintenanceFee('rental'));
    }

    public function test_dynamic_setting_labels()
    {
        Setting::clearCache();

        // Check defaults when not configured explicitly
        $this->assertEquals('Block/Wing', Setting::label('block', 'Block'));
        $this->assertEquals('Flat', Setting::label('unit', 'Flat'));

        // Update settings dynamically
        Setting::updateOrCreate(['key' => 'ui_label_block'], ['value' => 'Sector/Phase']);
        Setting::updateOrCreate(['key' => 'ui_label_unit'], ['value' => 'Row House']);
        Setting::clearCache();

        $this->assertEquals('Sector/Phase', Setting::label('block', 'Block'));
        $this->assertEquals('Row House', Setting::label('unit', 'Flat'));
    }
}
