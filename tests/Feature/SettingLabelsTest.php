<?php

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingLabelsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Setting::clearCache();
    }

    public function test_default_flat_residential_labels()
    {
        Setting::updateOrCreate(['key' => 'society_property_type'], ['value' => 'flat_residential']);
        Setting::clearCache();

        $this->assertEquals('Flat', Setting::label('unit'));
        $this->assertEquals('Flats', Setting::label('unit_plural'));
        $this->assertEquals('Flat Type', Setting::label('unit_type'));
        $this->assertEquals('Flat Types', Setting::label('unit_types'));
        $this->assertEquals('Flat No', Setting::label('unit_no'));
        $this->assertEquals('Block/Wing', Setting::label('block'));
        $this->assertEquals('Resident', Setting::label('resident'));
        $this->assertEquals('Residents', Setting::label('resident_plural'));
    }

    public function test_preset_commercial_complex_labels()
    {
        Setting::updateOrCreate(['key' => 'society_property_type'], ['value' => 'commercial_complex']);
        Setting::clearCache();

        $this->assertEquals('Shop / Office', Setting::label('unit'));
        $this->assertEquals('Shops & Offices', Setting::label('unit_plural'));
        $this->assertEquals('Shop & Office Category', Setting::label('unit_type'));
        $this->assertEquals('Shop & Office Categories', Setting::label('unit_types'));
        $this->assertEquals('Shop / Office No', Setting::label('unit_no'));
        $this->assertEquals('Wing', Setting::label('block'));
        $this->assertEquals('Occupant / Corporate', Setting::label('resident'));
        $this->assertEquals('Occupants / Corporates', Setting::label('resident_plural'));
    }

    public function test_preset_rowhouse_villa_labels()
    {
        Setting::updateOrCreate(['key' => 'society_property_type'], ['value' => 'rowhouse_villa']);
        Setting::clearCache();

        $this->assertEquals('Villa', Setting::label('unit'));
        $this->assertEquals('Villas', Setting::label('unit_plural'));
        $this->assertEquals('Villa Category', Setting::label('unit_type'));
        $this->assertEquals('Villa Categories', Setting::label('unit_types'));
        $this->assertEquals('Villa No', Setting::label('unit_no'));
        $this->assertEquals('Phase', Setting::label('block'));
        $this->assertEquals('Villa Owner / Occupant', Setting::label('resident'));
        $this->assertEquals('Villa Owners / Occupants', Setting::label('resident_plural'));
    }

    public function test_preset_mixed_use_labels()
    {
        Setting::updateOrCreate(['key' => 'society_property_type'], ['value' => 'mixed_use']);
        Setting::clearCache();

        $this->assertEquals('Property Unit', Setting::label('unit'));
        $this->assertEquals('Property Units', Setting::label('unit_plural'));
        $this->assertEquals('Property Unit Category', Setting::label('unit_type'));
        $this->assertEquals('Property Unit Categories', Setting::label('unit_types'));
        $this->assertEquals('Property Unit No', Setting::label('unit_no'));
        $this->assertEquals('Wing / Phase', Setting::label('block'));
        $this->assertEquals('Occupant / Resident', Setting::label('resident'));
        $this->assertEquals('Occupants / Residents', Setting::label('resident_plural'));
    }

    public function test_custom_explicit_label_overrides()
    {
        Setting::updateOrCreate(['key' => 'society_property_type'], ['value' => 'flat_residential']);
        Setting::updateOrCreate(['key' => 'ui_label_unit'], ['value' => 'Apartment']);
        Setting::updateOrCreate(['key' => 'ui_label_block'], ['value' => 'Tower']);
        Setting::updateOrCreate(['key' => 'ui_label_resident'], ['value' => 'Member']);
        Setting::clearCache();

        $this->assertEquals('Apartment', Setting::label('unit'));
        $this->assertEquals('Tower', Setting::label('block'));
        $this->assertEquals('Member', Setting::label('resident'));
        // Test derived plural & type when base unit is overridden
        $this->assertEquals('Apartment Type', Setting::label('unit_type'));
        $this->assertEquals('Apartment Types', Setting::label('unit_types'));
        $this->assertEquals('Apartments', Setting::label('unit_plural'));
        $this->assertEquals('Members', Setting::label('resident_plural'));
    }

    public function test_derived_labels_for_preset_base_units_without_explicit_types()
    {
        Setting::updateOrCreate(['key' => 'society_property_type'], ['value' => 'flat_residential']);
        Setting::updateOrCreate(['key' => 'ui_label_unit'], ['value' => 'Villa']);
        Setting::clearCache();

        $this->assertEquals('Villa', Setting::label('unit'));
        $this->assertEquals('Villa Category', Setting::label('unit_type'));
        $this->assertEquals('Villa Categories', Setting::label('unit_types'));
        $this->assertEquals('Villas', Setting::label('unit_plural'));
        $this->assertEquals('Villa No', Setting::label('unit_no'));
    }
}
