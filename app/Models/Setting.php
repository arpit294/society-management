<?php

namespace App\Models;

use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function defaults(): array
    {
        return [
            'society_name' => '',
            'society_address' => '',
            'contact_email' => '',
            'contact_phone' => '',
            'currency' => 'INR',
            'currency_symbol' => "\u{20B9}",
            'financial_year_start' => '04',
            'name_transfer_fee' => '0',
            'apply_penalty' => '0',
            'penalty_type' => 'percentage',
            'penalty_due_days' => '0',
            'penalty_monthly_enabled' => '0',
            'penalty_monthly_value' => '0',
            'penalty_quarterly_enabled' => '0',
            'penalty_quarterly_value' => '0',
            'penalty_half_yearly_enabled' => '0',
            'penalty_half_yearly_value' => '0',
            'penalty_yearly_enabled' => '0',
            'penalty_yearly_value' => '0',
            'apply_discount' => '0',
            'discount_type' => 'percentage',
            'discount_monthly_enabled' => '0',
            'discount_monthly_value' => '0',
            'discount_quarterly_enabled' => '0',
            'discount_quarterly_value' => '0',
            'discount_half_yearly_enabled' => '0',
            'discount_half_yearly_value' => '0',
            'discount_yearly_enabled' => '0',
            'discount_yearly_value' => '0',
            'society_latitude' => '',
            'society_longitude' => '',
            'society_map_address' => '',
            'req_doc_owner_passport_photo' => '0',
            'req_doc_owner_adhar_card' => '0',
            'req_doc_owner_pan_card' => '0',
            'req_doc_owner_index_copy' => '0',
            'req_doc_owner_possession_letter' => '0',
            'req_doc_owner_tax_bill' => '0',
            'req_doc_owner_contact_no' => '0',
            'req_doc_owner_email' => '0',
            'req_doc_rental_passport_photo' => '0',
            'req_doc_rental_adhar_card' => '0',
            'req_doc_rental_pan_card' => '0',
            'req_doc_rental_rent_agreement' => '0',
            'req_doc_rental_police_verification' => '0',
            'req_doc_rental_permanent_address_proof' => '0',
            'req_doc_rental_contact_no' => '0',
            'req_doc_rental_email' => '0',
            'enable_debugger' => '0',
            // SMP 2.0 Multi-Structure & Property Settings
            'society_property_type' => 'flat_residential', // flat_residential, commercial_complex, rowhouse_villa, mixed_use
            'maintenance_billing_method' => 'fixed', // fixed, per_sqft
            'maintenance_rate_per_sqft' => '0',
            'ui_label_block' => 'Block/Wing',
            'ui_label_unit' => 'Flat',
            'ui_label_unit_plural' => 'Flats',
            'ui_label_resident' => 'Resident',
            'enable_area_based_billing' => '0',
            'enable_commercial_gst' => '0',
            'commercial_gst_percentage' => '18',
        ];
    }

    public static function label(string $key, string $default = ''): string
    {
        $value = self::get("ui_label_{$key}");
        $propType = self::get('society_property_type', 'flat_residential');

        // Check if value equals generic default or is empty, so we can apply dynamic property type vocabulary
        $genericDefaults = ['Block/Wing', 'Block', 'Flat', 'Flats', 'Resident', 'Residents', ''];
        if (in_array($value, $genericDefaults)) {
            if ($propType === 'commercial_complex') {
                $vocabulary = [
                    'block' => 'Commercial Wing',
                    'unit' => 'Shop / Office',
                    'unit_plural' => 'Shops & Offices',
                    'resident' => 'Occupant / Corporate',
                ];
                if (isset($vocabulary[$key])) return $vocabulary[$key];
            } elseif ($propType === 'rowhouse_villa') {
                $vocabulary = [
                    'block' => 'Phase / Sector',
                    'unit' => 'Villa / Row House',
                    'unit_plural' => 'Villas / Row Houses',
                    'resident' => 'Villa Owner / Occupant',
                ];
                if (isset($vocabulary[$key])) return $vocabulary[$key];
            } elseif ($propType === 'mixed_use') {
                $vocabulary = [
                    'block' => 'Wing / Phase',
                    'unit' => 'Property Unit',
                    'unit_plural' => 'Property Units',
                    'resident' => 'Occupant / Resident',
                ];
                if (isset($vocabulary[$key])) return $vocabulary[$key];
            }
        }

        return !empty($value) ? $value : ($default ?: (self::defaults()["ui_label_{$key}"] ?? $key));
    }

    public static function unitIconClass(): string
    {
        $propType = self::get('society_property_type', 'flat_residential');
        return match ($propType) {
            'commercial_complex' => 'fa-store',
            'rowhouse_villa' => 'fa-house-chimney',
            'mixed_use' => 'fa-city',
            default => 'fa-building',
        };
    }

    public static function blockIconClass(): string
    {
        $propType = self::get('society_property_type', 'flat_residential');
        return match ($propType) {
            'commercial_complex' => 'fa-building-columns',
            'rowhouse_villa' => 'fa-map-location-dot',
            'mixed_use' => 'fa-sitemap',
            default => 'fa-building',
        };
    }

    protected static $cachedSettings = null;

    public static function getAll(): array
    {
        if (self::$cachedSettings !== null) {
            return self::$cachedSettings;
        }

        self::$cachedSettings = Cache::rememberForever('global_settings', function () {
            return self::all()->pluck('value', 'key')->toArray();
        });

        return self::$cachedSettings;
    }

    public static function clearCache(): void
    {
        self::$cachedSettings = null;
        Cache::forget('global_settings');
    }

    public static function get($key, $default = null)
    {
        $settings = self::getAll();

        return $settings[$key] ?? (self::defaults()[$key] ?? $default);
    }

    public static function allPermissions(): array
    {
        $permissions = [];
        foreach (config('permissions.modules', []) as $modulePermissions) {
            $permissions = array_merge($permissions, $modulePermissions);
        }
        return $permissions;
    }
}
