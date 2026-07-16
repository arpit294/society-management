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
            'ui_label_unit_type' => 'Flat Type',
            'ui_label_unit_types' => 'Flat Types',
            'ui_label_unit_no' => 'Flat No',
            'ui_label_resident' => 'Resident',
            'enable_area_based_billing' => '0',
            'enable_commercial_gst' => '0',
            'commercial_gst_percentage' => '18',
            // Toaster & Notification Settings
            'toastr_enabled' => '1',
            'toastr_position' => 'toast-top-right',
            'toastr_timeout' => '3000',
            'toastr_close_button' => '1',
            'toastr_progress_bar' => '1',
        ];
    }

    public static function label(string $key, string $default = ''): string
    {
        $allSettings = self::getAll();
        $propType = self::get('society_property_type', 'flat_residential');

        $genericDefaults = [
            'Block/Wing', 'Block', 'Flat', 'Flats', 'Resident', 'Residents', 
            'Flat Type', 'Flat Types', 'Flat No', '', 
            'Villa & Rowhouse Category', 'Villa & Rowhouse Categories', 
            'Shop & Office Category', 'Shop & Office Categories',
            'Property & Unit Category', 'Property & Unit Categories'
        ];

        $hasExplicitSetting = array_key_exists("ui_label_{$key}", $allSettings) 
            && !empty($allSettings["ui_label_{$key}"]) 
            && !in_array($allSettings["ui_label_{$key}"], $genericDefaults);

        if (!$hasExplicitSetting && $propType !== 'flat_residential') {
            if ($propType === 'commercial_complex') {
                $vocabulary = [
                    'block' => 'Wing',
                    'unit' => 'Shop / Office',
                    'unit_plural' => 'Shops & Offices',
                    'unit_type' => 'Shop & Office Category',
                    'unit_types' => 'Shop & Office Categories',
                    'unit_no' => 'Shop / Office No',
                    'resident' => 'Occupant / Corporate',
                    'resident_plural' => 'Occupants / Corporates',
                ];
                if (isset($vocabulary[$key])) return $vocabulary[$key];
            } elseif ($propType === 'rowhouse_villa') {
                $vocabulary = [
                    'block' => 'Phase',
                    'unit' => 'Villa',
                    'unit_plural' => 'Villas',
                    'unit_type' => 'Villa Category',
                    'unit_types' => 'Villa Categories',
                    'unit_no' => 'Villa No',
                    'resident' => 'Villa Owner / Occupant',
                    'resident_plural' => 'Villa Owners / Occupants',
                ];
                if (isset($vocabulary[$key])) return $vocabulary[$key];
            } elseif ($propType === 'mixed_use') {
                $vocabulary = [
                    'block' => 'Wing / Phase',
                    'unit' => 'Property Unit',
                    'unit_plural' => 'Property Units',
                    'unit_type' => 'Property Unit Category',
                    'unit_types' => 'Property Unit Categories',
                    'unit_no' => 'Property Unit No',
                    'resident' => 'Occupant / Resident',
                    'resident_plural' => 'Occupants / Residents',
                ];
                if (isset($vocabulary[$key])) return $vocabulary[$key];
            }
        }

        // If asking for unit derived fields (types, plural, no) and no explicit setting exists for them
        if (in_array($key, ['unit_type', 'unit_types', 'unit_plural', 'unit_no']) && !$hasExplicitSetting) {
            $baseUnit = self::label('unit', 'Flat');
            
            if ($baseUnit === 'Villa') {
                if ($key === 'unit_type') return 'Villa Category';
                if ($key === 'unit_types') return 'Villa Categories';
                if ($key === 'unit_plural') return 'Villas';
                if ($key === 'unit_no') return 'Villa No';
            } elseif ($baseUnit === 'Villa / Row House') {
                if ($key === 'unit_type') return 'Villa & Rowhouse Category';
                if ($key === 'unit_types') return 'Villa & Rowhouse Categories';
                if ($key === 'unit_plural') return 'Villas / Row Houses';
                if ($key === 'unit_no') return 'Villa / Row House No';
            } elseif ($baseUnit === 'Shop') {
                if ($key === 'unit_type') return 'Shop Category';
                if ($key === 'unit_types') return 'Shop Categories';
                if ($key === 'unit_plural') return 'Shops';
                if ($key === 'unit_no') return 'Shop No';
            } elseif ($baseUnit === 'Shop / Office') {
                if ($key === 'unit_type') return 'Shop & Office Category';
                if ($key === 'unit_types') return 'Shop & Office Categories';
                if ($key === 'unit_plural') return 'Shops & Offices';
                if ($key === 'unit_no') return 'Shop / Office No';
            } elseif ($baseUnit === 'Property Unit') {
                if ($key === 'unit_type') return 'Property Unit Category';
                if ($key === 'unit_types') return 'Property Unit Categories';
                if ($key === 'unit_plural') return 'Property Units';
                if ($key === 'unit_no') return 'Property Unit No';
            } else {
                if ($key === 'unit_type') return $propType !== 'flat_residential' ? "{$baseUnit} Category" : "{$baseUnit} Type";
                if ($key === 'unit_types') return $propType !== 'flat_residential' ? "{$baseUnit} Categories" : "{$baseUnit} Types";
                if ($key === 'unit_plural') return str_ends_with($baseUnit, 's') ? $baseUnit : "{$baseUnit}s";
                if ($key === 'unit_no') return "{$baseUnit} No";
            }
        }

        // If asking for resident_plural derived field
        if ($key === 'resident_plural' && !$hasExplicitSetting) {
            $baseResident = self::label('resident', 'Resident');
            if ($baseResident === 'Villa Owner / Occupant') return 'Villa Owner / Occupants';
            if ($baseResident === 'Occupant / Corporate') return 'Occupants / Corporates';
            if ($baseResident === 'Occupant / Resident') return 'Occupants / Residents';
            if ($baseResident === 'Tenant / Owner') return 'Tenant / Owners';
            if ($baseResident === 'Villa Owner') return 'Villa Owners';
            return str_ends_with($baseResident, 's') ? $baseResident : "{$baseResident}s";
        }

        $value = $hasExplicitSetting ? $allSettings["ui_label_{$key}"] : self::get("ui_label_{$key}");
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
