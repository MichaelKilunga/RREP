<?php

namespace Database\Seeders;

use App\Models\Amenity;
use App\Models\Branch;
use App\Models\LandParcel;
use App\Models\Organization;
use App\Models\Property;
use App\Models\PropertyOwner;
use App\Models\PropertyType;
use App\Models\PropertyUnit;
use App\Models\User;
use Illuminate\Database\Seeder;

class PropertySeeder extends Seeder
{
    public function run(): void
    {
        $org = Organization::first();
        $darBranch = Branch::where('code', 'DAR-01')->first();
        $aruBranch = Branch::where('code', 'ARU-02')->first();
        $domBranch = Branch::where('code', 'DOM-03')->first();
        $admin = User::first();

        // 1. Property Types
        $types = [
            ['name' => 'Residential Apartment', 'slug' => 'residential-apartment', 'category' => 'Residential', 'icon' => 'building'],
            ['name' => 'Luxury Villa & Mansion', 'slug' => 'luxury-villa', 'category' => 'Residential', 'icon' => 'home'],
            ['name' => 'Commercial Office Space', 'slug' => 'commercial-office', 'category' => 'Commercial', 'icon' => 'briefcase'],
            ['name' => 'Shopping Center & Retail', 'slug' => 'retail-shop', 'category' => 'Commercial', 'icon' => 'shopping-bag'],
            ['name' => 'Land / Cadastral Plot', 'slug' => 'land-plot', 'category' => 'Land', 'icon' => 'map'],
            ['name' => 'Agricultural Farm Estate', 'slug' => 'agricultural-farm', 'category' => 'Agricultural', 'icon' => 'sun'],
            ['name' => 'Industrial Warehouse', 'slug' => 'industrial-warehouse', 'category' => 'Industrial', 'icon' => 'truck'],
        ];
        $typeModels = [];
        foreach ($types as $t) {
            $typeModels[$t['slug']] = PropertyType::firstOrCreate(['slug' => $t['slug']], $t);
        }

        // 2. Amenities
        $amenities = [
            'Swimming Pool', '24/7 Security CCTV', 'Backup Generator', 'High-Speed Elevator',
            'Borehole Water Supply', 'Tarmac Access Road', 'Title Deed Ready', 'Perimeter Wall & Electric Fence',
            'Ample Parking', 'Air Conditioning', 'Ocean View', 'Children Play Area',
        ];
        $amenityModels = [];
        foreach ($amenities as $a) {
            $amenityModels[] = Amenity::firstOrCreate(['name' => $a], ['category' => 'General', 'icon' => 'check-circle']);
        }

        // 3. Property Owners
        $owner1 = PropertyOwner::firstOrCreate(['email' => 'haji@kilimanjaroholdings.co.tz'], [
            'organization_id' => $org->id,
            'first_name' => 'Haji',
            'last_name' => 'Manara',
            'company_name' => 'Kilimanjaro Holdings Ltd',
            'phone' => '+255 784 100 200',
            'national_id' => '19800512-11101-00001-20',
            'tax_pin' => 'TIN-990-221-334',
            'address' => 'Masaki Peninsula, Dar es Salaam',
            'bank_name' => 'CRDB Bank Plc',
            'bank_account_number' => '0150299887700',
            'kyc_status' => 'Verified',
        ]);

        $owner2 = PropertyOwner::firstOrCreate(['email' => 'neema@meruvalleys.tz'], [
            'organization_id' => $org->id,
            'first_name' => 'Neema',
            'last_name' => 'Massawe',
            'company_name' => 'Meru Valley Estates',
            'phone' => '+255 786 333 999',
            'national_id' => '19851104-22202-00002-15',
            'tax_pin' => 'TIN-881-445-112',
            'address' => 'Njiro Hill, Arusha',
            'bank_name' => 'NMB Bank Plc',
            'bank_account_number' => '2010887766554',
            'kyc_status' => 'Verified',
        ]);

        // 4. Sample Properties
        // Property 1: Masaki Oceanview Executive Villa
        $p1 = Property::firstOrCreate(['property_code' => 'RREP-DAR-001'], [
            'organization_id' => $org->id,
            'branch_id' => $darBranch->id,
            'property_type_id' => $typeModels['luxury-villa']->id,
            'property_owner_id' => $owner1->id,
            'title' => 'Masaki Oceanview 5-Bedroom Executive Villa',
            'slug' => 'masaki-oceanview-5-bedroom-executive-villa',
            'listing_type' => 'Sale',
            'status' => 'Available',
            'price' => 1250000000.00, // 1.25 Billion TZS
            'currency' => 'TZS',
            'address' => 'Toure Drive, Masaki Peninsula',
            'city' => 'Dar es Salaam',
            'state' => 'Kinondoni',
            'country' => 'Tanzania',
            'latitude' => -6.74950000,
            'longitude' => 39.28180000,
            'area_size' => 1200.00,
            'area_unit' => 'Sqm',
            'bedrooms' => 5,
            'bathrooms' => 6,
            'floors' => 2,
            'parking_spaces' => 4,
            'year_built' => 2024,
            'is_featured' => true,
            'is_published' => true,
            'views_count' => 342,
            'description' => 'A mastercrafted contemporary oceanview villa located in the prestigious Masaki neighborhood. Features high ceiling glass windows, private infinity pool, Italian marble flooring, smart home automation, and 24/7 guarded security perimeter.',
            'features_json' => ['Smart Home Automation', 'Infinity Pool', 'Private Gym', 'Solar Water Heating', 'Staff Quarters'],
            'created_by' => $admin->id,
        ]);
        $p1->amenities()->sync([$amenityModels[0]->id, $amenityModels[1]->id, $amenityModels[2]->id, $amenityModels[7]->id, $amenityModels[10]->id]);

        // Property 2: Victoria Commercial Plaza Offices
        $p2 = Property::firstOrCreate(['property_code' => 'RREP-DAR-002'], [
            'organization_id' => $org->id,
            'branch_id' => $darBranch->id,
            'property_type_id' => $typeModels['commercial-office']->id,
            'property_owner_id' => $owner1->id,
            'title' => 'Victoria Business Tower - Grade A Office Suites',
            'slug' => 'victoria-business-tower-grade-a-offices',
            'listing_type' => 'Rent',
            'status' => 'Available',
            'price' => 0.00,
            'rent_price' => 3500000.00, // 3.5M TZS per month
            'rent_period' => 'Monthly',
            'deposit_amount' => 10500000.00, // 3 months deposit
            'currency' => 'TZS',
            'address' => 'New Bagamoyo Road, Victoria',
            'city' => 'Dar es Salaam',
            'state' => 'Kinondoni',
            'country' => 'Tanzania',
            'latitude' => -6.77250000,
            'longitude' => 39.24860000,
            'area_size' => 250.00,
            'area_unit' => 'Sqm',
            'floors' => 8,
            'parking_spaces' => 20,
            'year_built' => 2023,
            'is_featured' => true,
            'is_published' => true,
            'views_count' => 512,
            'description' => 'Modern Grade-A corporate office space located along the high-traffic Bagamoyo corridor. High-speed dual fiber internet, double elevator systems, central air conditioning, and basement secure parking.',
            'features_json' => ['24/7 Access', 'Fiber Internet Ready', 'CCTV & Biometric Access', 'Fire Suppression System'],
            'created_by' => $admin->id,
        ]);
        $p2->amenities()->sync([$amenityModels[1]->id, $amenityModels[2]->id, $amenityModels[3]->id, $amenityModels[8]->id, $amenityModels[9]->id]);

        // Add units to commercial plaza
        PropertyUnit::firstOrCreate(['property_id' => $p2->id, 'unit_number' => 'Suite 301'], ['floor_number' => '3', 'unit_type' => 'Executive Office', 'size' => 120.00, 'rent_amount' => 3500000.00, 'status' => 'Available']);
        PropertyUnit::firstOrCreate(['property_id' => $p2->id, 'unit_number' => 'Suite 302'], ['floor_number' => '3', 'unit_type' => 'Corporate Open Floor', 'size' => 130.00, 'rent_amount' => 3800000.00, 'status' => 'Available']);

        // Property 3: Arusha Mount Meru Panoramic Land Subdivision (GIS Ready)
        $p3 = Property::firstOrCreate(['property_code' => 'RREP-ARU-003'], [
            'organization_id' => $org->id,
            'branch_id' => $aruBranch->id,
            'property_type_id' => $typeModels['land-plot']->id,
            'property_owner_id' => $owner2->id,
            'title' => 'Mount Meru View 10-Acre Master-Planned Land Estate',
            'slug' => 'mount-meru-view-10-acre-land-estate',
            'listing_type' => 'Sale',
            'status' => 'Available',
            'price' => 450000000.00, // 450M TZS
            'currency' => 'TZS',
            'address' => 'Oldonyo Sambu Road, Ngaramtoni',
            'city' => 'Arusha',
            'state' => 'Arumeru',
            'country' => 'Tanzania',
            'latitude' => -3.32840000,
            'longitude' => 36.65430000,
            'area_size' => 10.00,
            'area_unit' => 'Acres',
            'is_featured' => true,
            'is_published' => true,
            'views_count' => 780,
            'description' => 'Spectacular surveyed land parcel offering unhindered vistas of Mount Meru and lush greenery. Perfectly suited for eco-lodge tourism development, residential gated community, or horticultural farming.',
            'features_json' => ['Beaconed Boundary', 'Title Deed Clean', 'Spring Water Supply', 'Tarmac Proximity'],
            'created_by' => $admin->id,
        ]);
        $p3->amenities()->sync([$amenityModels[4]->id, $amenityModels[5]->id, $amenityModels[6]->id]);

        // Land Parcel record with coordinates
        LandParcel::firstOrCreate(['property_id' => $p3->id], [
            'parcel_number' => 'PLOT-ARU-2026/89',
            'deed_number' => 'TITLE-ARU-09882',
            'survey_plan_number' => 'TP-ARU-5541',
            'title_deed_type' => 'Right of Occupancy (99 Yrs)',
            'acreage' => 10.0000,
            'tenure_years_remaining' => 95,
            'zoning' => 'Mixed Use / Residential',
            'topography' => 'Gentle Slope with Mountain View',
            'soil_type' => 'Rich Volcanic Soil',
            'boundary_coordinates_json' => [
                ['beacon' => 'B1', 'lat' => -3.3280, 'lng' => 36.6540],
                ['beacon' => 'B2', 'lat' => -3.3280, 'lng' => 36.6580],
                ['beacon' => 'B3', 'lat' => -3.3320, 'lng' => 36.6580],
                ['beacon' => 'B4', 'lat' => -3.3320, 'lng' => 36.6540],
            ],
        ]);

        // Property 4: Dodoma Capital City Modern Apartments
        $p4 = Property::firstOrCreate(['property_code' => 'RREP-DOM-004'], [
            'organization_id' => $org->id,
            'branch_id' => $domBranch->id,
            'property_type_id' => $typeModels['residential-apartment']->id,
            'property_owner_id' => $owner2->id,
            'title' => 'Mtumba Diplomatic Zone 3-Bedroom Luxury Apartments',
            'slug' => 'mtumba-diplomatic-zone-luxury-apartments',
            'listing_type' => 'Sale',
            'status' => 'Available',
            'price' => 180000000.00, // 180M TZS
            'currency' => 'TZS',
            'address' => 'Government City Boulevard, Mtumba',
            'city' => 'Dodoma',
            'state' => 'Dodoma Urban',
            'country' => 'Tanzania',
            'latitude' => -6.18200000,
            'longitude' => 35.84500000,
            'area_size' => 165.00,
            'area_unit' => 'Sqm',
            'bedrooms' => 3,
            'bathrooms' => 3,
            'floors' => 4,
            'parking_spaces' => 2,
            'year_built' => 2025,
            'is_featured' => true,
            'is_published' => true,
            'views_count' => 420,
            'description' => 'Brand new premium apartments located minutes from the National Government City in Mtumba, Dodoma. Built to international standards with fitted kitchens, solar backup, and landscaped gardens.',
            'features_json' => ['Fitted Modern Kitchen', 'Solar Water Heating', 'Dedicated Parking', 'Security Patrol'],
            'created_by' => $admin->id,
        ]);
        $p4->amenities()->sync([$amenityModels[1]->id, $amenityModels[2]->id, $amenityModels[7]->id, $amenityModels[8]->id]);
    }
}
