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

    public function test_per_sqft_calculation_for_villa_or_apartment()
    {
        $flatType = FlatType::create([
            'name' => 'Luxury Villa Rate',
            'category_type' => 'residential',
            'calculation_method' => 'per_sqft',
            'owner_maintenance_fee' => 0,
            'rental_maintenance_fee' => 0,
            'rate_per_sqft' => 2.50,
            'commercial_surcharge_percentage' => 0,
        ]);

        $block = Block::create([
            'block_name' => 'Sector 1',
            'block_type' => 'rowhouse_sector',
            'label_type' => 'Sector',
            'total_floor' => 0,
            'total_flats' => 20,
        ]);

        $flat = Flat::create([
            'block_id' => $block->id,
            'flat_type_id' => $flatType->id,
            'flat_no' => 'V-12',
            'floor_no' => 0,
            'unit_type' => 'villa',
            'area_sqft' => 2000,
            'status' => 'Occupied',
        ]);

        // 2000 sqft * 2.50 = 5000
        $this->assertEquals(5000, $flat->calculateMaintenanceFee('owner'));
    }

    public function test_hybrid_calculation_method()
    {
        $flatType = FlatType::create([
            'name' => 'Hybrid Penthouse Rate',
            'category_type' => 'residential',
            'calculation_method' => 'hybrid',
            'owner_maintenance_fee' => 1000,
            'rental_maintenance_fee' => 1200,
            'rate_per_sqft' => 1.50,
            'commercial_surcharge_percentage' => 0,
        ]);

        $block = Block::create([
            'block_name' => 'Tower B',
            'block_type' => 'residential_tower',
            'label_type' => 'Tower',
            'total_floor' => 15,
            'total_flats' => 30,
        ]);

        $flat = Flat::create([
            'block_id' => $block->id,
            'flat_type_id' => $flatType->id,
            'flat_no' => '1501',
            'floor_no' => 15,
            'unit_type' => 'flat',
            'area_sqft' => 1500,
            'status' => 'Occupied',
        ]);

        // owner: 1000 base + (1500 sqft * 1.50 = 2250) = 3250
        $this->assertEquals(3250, $flat->calculateMaintenanceFee('owner'));
        // rental: 1200 base + 2250 = 3450
        $this->assertEquals(3450, $flat->calculateMaintenanceFee('rental'));
    }

    public function test_commercial_surcharge_applied_automatically()
    {
        $flatType = FlatType::create([
            'name' => 'Commercial Arcade Rate',
            'category_type' => 'commercial',
            'calculation_method' => 'fixed',
            'owner_maintenance_fee' => 3000,
            'rental_maintenance_fee' => 3500,
            'rate_per_sqft' => 0,
            'commercial_surcharge_percentage' => 10, // 10% surcharge
        ]);

        $block = Block::create([
            'block_name' => 'Arcade Wing',
            'block_type' => 'commercial_wing',
            'label_type' => 'Wing',
            'total_floor' => 3,
            'total_flats' => 15,
        ]);

        $flat = Flat::create([
            'block_id' => $block->id,
            'flat_type_id' => $flatType->id,
            'flat_no' => 'S-01',
            'floor_no' => 0,
            'unit_type' => 'shop',
            'area_sqft' => 450,
            'has_commercial_license' => true,
            'status' => 'Occupied',
        ]);

        // owner: 3000 + 10% surcharge (300) = 3300
        $this->assertEquals(3300, $flat->calculateMaintenanceFee('owner'));
        // rental: 3500 + 10% surcharge (350) = 3850
        $this->assertEquals(3850, $flat->calculateMaintenanceFee('rental'));
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
