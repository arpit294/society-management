<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class SettingSeeder extends Seeder
{
    /**
     * Seed the application's global settings table.
     */
    public function run(): void
    {
        $defaultSettings = [
            'society_name' => 'Royal Palms Society',
            'society_address' => '123 Palm Avenue, Green Valley',
            'contact_email' => 'admin@royalpalms.com',
            'contact_phone' => '+91 9876543210',
            'financial_year_start' => '04',
            'name_transfer_fee' => '5000',
            
            // Penalty Settings
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
            
            // Discount Settings
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
            
            // Map Coordinates
            'society_latitude' => '19.0760',
            'society_longitude' => '72.8777',
            'society_map_address' => 'Mumbai, Maharashtra, India',

            // Owner Required Documents
            'req_doc_owner_passport_photo' => '1',
            'req_doc_owner_adhar_card' => '1',
            'req_doc_owner_pan_card' => '1',
            'req_doc_owner_index_copy' => '1',
            'req_doc_owner_possession_letter' => '1',
            'req_doc_owner_tax_bill' => '1',
            'req_doc_owner_contact_no' => '1',
            'req_doc_owner_email' => '1',

            // Rental Required Documents
            'req_doc_rental_passport_photo' => '1',
            'req_doc_rental_adhar_card' => '1',
            'req_doc_rental_pan_card' => '1',
            'req_doc_rental_rent_agreement' => '1',
            'req_doc_rental_police_verification' => '1',
            'req_doc_rental_permanent_address_proof' => '1',
            'req_doc_rental_contact_no' => '1',
            'req_doc_rental_email' => '1',
        ];

        foreach ($defaultSettings as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => (string) $value]
            );
        }

        Cache::forget('global_settings');
    }
}
