<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\BrandingConfig;
use App\Models\Currency;
use App\Models\Organization;
use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class OrganizationSeeder extends Seeder
{
    public function run(): void
    {
        $org = Organization::firstOrCreate(['code' => 'REHO-HQ'], [
            'name' => 'RehoSpace Real Estate',
            'email' => 'info@rehospace.com',
            'phone' => '+255 754 000 111',
            'website' => 'https://rehospace.com',
            'tax_number' => 'TIN-100-992-881',
            'address' => 'Ali Hassan Mwinyi Road, Victoria Area',
            'city' => 'Dar es Salaam',
            'country' => 'Tanzania',
            'currency' => 'TZS',
            'currency_symbol' => 'TSh',
            'timezone' => 'Africa/Dar_es_Salaam',
            'status' => 'Active',
        ]);

        Branch::firstOrCreate(['code' => 'DAR-01'], [
            'organization_id' => $org->id,
            'name' => 'Dar es Salaam HQ',
            'email' => 'dar@rehospace.com',
            'phone' => '+255 754 000 111',
            'address' => 'Victoria Plaza, 4th Floor',
            'city' => 'Dar es Salaam',
            'is_main' => true,
            'status' => 'Active',
        ]);

        Branch::firstOrCreate(['code' => 'ARU-02'], [
            'organization_id' => $org->id,
            'name' => 'Arusha Northern Branch',
            'email' => 'arusha@rehospace.com',
            'phone' => '+255 754 000 222',
            'address' => 'Clock Tower Commercial Center',
            'city' => 'Arusha',
            'is_main' => false,
            'status' => 'Active',
        ]);

        Branch::firstOrCreate(['code' => 'DOM-03'], [
            'organization_id' => $org->id,
            'name' => 'Dodoma Capital Branch',
            'email' => 'dodoma@rehospace.com',
            'phone' => '+255 754 000 333',
            'address' => 'Makole Business Center',
            'city' => 'Dodoma',
            'is_main' => false,
            'status' => 'Active',
        ]);

        BrandingConfig::firstOrCreate(['organization_id' => $org->id], [
            'primary_color' => '#0f52ba',
            'secondary_color' => '#495057',
            'accent_color' => '#00a86b',
            'dark_color' => '#1a2238',
            'light_color' => '#f8f9fa',
            'sidebar_theme' => 'dark',
            'company_tagline' => 'Transforming Real Estate Ecosystems with Intelligence',
        ]);

        Currency::firstOrCreate(['code' => 'TZS'], ['name' => 'Tanzanian Shilling', 'symbol' => 'TSh', 'exchange_rate' => 1.0000, 'is_default' => true]);
        Currency::firstOrCreate(['code' => 'USD'], ['name' => 'US Dollar', 'symbol' => '$', 'exchange_rate' => 2600.0000, 'is_default' => false]);
        Currency::firstOrCreate(['code' => 'KES'], ['name' => 'Kenyan Shilling', 'symbol' => 'KSh', 'exchange_rate' => 20.0000, 'is_default' => false]);

        SystemSetting::setVal('company_name', 'RehoSpace Real Estate', 'general', $org->id);
        SystemSetting::setVal('default_currency', 'TZS', 'general', $org->id);
        SystemSetting::setVal('tax_rate_percentage', '18.0', 'payment', $org->id);
        SystemSetting::setVal('commission_default_rate', '5.0', 'sales', $org->id);
    }
}
