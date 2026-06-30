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
            'society_name' => 'Royal Palms Society',
            'society_address' => '123 Palm Avenue, Green Valley',
            'contact_email' => 'admin@royalpalms.com',
            'contact_phone' => '+91 9876543210',
            'currency' => 'INR',
            'currency_symbol' => "\u{20B9}",
            'financial_year_start' => '04',
            'name_transfer_fee' => '5000',
            'apply_penalty' => '1',
            'penalty_type' => 'percentage',
            'penalty_due_days' => '10',
            'penalty_monthly_enabled' => '1',
            'penalty_monthly_value' => '5',
            'penalty_quarterly_enabled' => '1',
            'penalty_quarterly_value' => '10',
            'penalty_half_yearly_enabled' => '1',
            'penalty_half_yearly_value' => '15',
            'penalty_yearly_enabled' => '1',
            'penalty_yearly_value' => '20',
            'apply_discount' => '1',
            'discount_type' => 'percentage',
            'discount_monthly_enabled' => '0',
            'discount_monthly_value' => '0',
            'discount_quarterly_enabled' => '1',
            'discount_quarterly_value' => '2',
            'discount_half_yearly_enabled' => '1',
            'discount_half_yearly_value' => '5',
            'discount_yearly_enabled' => '1',
            'discount_yearly_value' => '10',
            'society_latitude' => '19.0760',
            'society_longitude' => '72.8777',
            'society_map_address' => 'Mumbai, Maharashtra, India',
            'req_doc_owner_passport_photo' => '1',
            'req_doc_owner_adhar_card' => '1',
            'req_doc_owner_pan_card' => '1',
            'req_doc_owner_index_copy' => '1',
            'req_doc_owner_possession_letter' => '1',
            'req_doc_owner_tax_bill' => '1',
            'req_doc_owner_contact_no' => '1',
            'req_doc_owner_email' => '1',
            'req_doc_rental_passport_photo' => '1',
            'req_doc_rental_adhar_card' => '1',
            'req_doc_rental_pan_card' => '1',
            'req_doc_rental_rent_agreement' => '1',
            'req_doc_rental_police_verification' => '1',
            'req_doc_rental_permanent_address_proof' => '1',
            'req_doc_rental_contact_no' => '1',
            'req_doc_rental_email' => '1',
        ];
    }

    public static function get($key, $default = null)
    {
        $settings = Cache::rememberForever('global_settings', function () {
            return self::all()->pluck('value', 'key')->toArray();
        });

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
