<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Branch;
use App\Models\Faq;
use App\Models\LandParcel;
use App\Models\Organization;
use App\Models\Property;
use App\Models\PropertyOwner;
use App\Models\PropertyType;
use App\Models\RealEstateProject;
use App\Models\SystemSetting;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PublicWebsiteSeeder extends Seeder
{
    public function run(): void
    {
        $org = Organization::first();
        $admin = User::first();

        // 1. Ensure Branches Exist
        $branches = [
            ['name' => 'Dar es Salaam HQ', 'code' => 'DAR-01', 'city' => 'Dar es Salaam', 'address' => 'Ali Hassan Mwinyi Rd, Victoria'],
            ['name' => 'Morogoro Regional Branch', 'code' => 'MOR-01', 'city' => 'Morogoro', 'address' => 'Boma Road, Morogoro Urban'],
            ['name' => 'Arusha Northern Hub', 'code' => 'ARU-02', 'city' => 'Arusha', 'address' => 'Njiro Hill Complex, Arusha'],
            ['name' => 'Dodoma Capital Office', 'code' => 'DOM-03', 'city' => 'Dodoma', 'address' => 'Mtumba Government Boulevard'],
            ['name' => 'Mwanza Lake Hub', 'code' => 'MWZ-01', 'city' => 'Mwanza', 'address' => 'Capri Point, Mwanza'],
            ['name' => 'Zanzibar Island Office', 'code' => 'ZNZ-01', 'city' => 'Zanzibar', 'address' => 'Fumba Town Development'],
        ];

        $branchModels = [];
        foreach ($branches as $b) {
            $branchModels[$b['city']] = Branch::firstOrCreate(
                ['code' => $b['code']],
                array_merge($b, ['organization_id' => $org?->id, 'phone' => '+255 22 211 5500', 'email' => strtolower(str_replace(' ', '', $b['city'])).'@rehospace.co.tz'])
            );
        }

        // 2. System Settings for Public Website
        $settings = [
            ['key' => 'company_name', 'value' => 'RehoSpace Real Estate Platform', 'group' => 'general', 'is_public' => true],
            ['key' => 'company_tagline', 'value' => 'Verified Properties, Land Survey & Digital Real Estate Marketplace', 'group' => 'general', 'is_public' => true],
            ['key' => 'contact_phone', 'value' => '+255 784 100 200', 'group' => 'general', 'is_public' => true],
            ['key' => 'contact_whatsapp', 'value' => '255784100200', 'group' => 'general', 'is_public' => true],
            ['key' => 'contact_email', 'value' => 'marketplace@rehospace.co.tz', 'group' => 'general', 'is_public' => true],
            ['key' => 'contact_address', 'value' => 'Plot 42, Victoria Business Tower, New Bagamoyo Road, Dar es Salaam, Tanzania', 'group' => 'general', 'is_public' => true],
            ['key' => 'facebook_url', 'value' => 'https://facebook.com/rehospace', 'group' => 'social', 'is_public' => true],
            ['key' => 'instagram_url', 'value' => 'https://instagram.com/rehospace_tz', 'group' => 'social', 'is_public' => true],
            ['key' => 'x_url', 'value' => 'https://x.com/rehospace', 'group' => 'social', 'is_public' => true],
            ['key' => 'linkedin_url', 'value' => 'https://linkedin.com/company/rehospace', 'group' => 'social', 'is_public' => true],
            ['key' => 'youtube_url', 'value' => 'https://youtube.com/@rehospace_tz', 'group' => 'social', 'is_public' => true],
        ];

        foreach ($settings as $s) {
            SystemSetting::updateOrCreate(
                ['organization_id' => $org?->id, 'key' => $s['key']],
                $s
            );
        }

        // 3. Property Owners
        $ownerMorogoro = PropertyOwner::firstOrCreate(
            ['phone' => '+255 754 888 111'],
            [
                'organization_id' => $org?->id,
                'first_name' => 'Rashid',
                'last_name' => 'Mwinyi',
                'company_name' => 'Uluguru Land Holdings Ltd',
                'email' => 'rashid@uluguruholdings.co.tz',
                'address' => 'Kihonda Industrial Area, Morogoro',
                'kyc_status' => 'Verified',
            ]
        );

        $ownerDar = PropertyOwner::firstOrCreate(
            ['phone' => '+255 784 100 200'],
            [
                'organization_id' => $org?->id,
                'first_name' => 'Haji',
                'last_name' => 'Manara',
                'company_name' => 'Kilimanjaro Holdings Ltd',
                'email' => 'haji@kilimanjaroholdings.co.tz',
                'address' => 'Masaki Peninsula, Dar es Salaam',
                'kyc_status' => 'Verified',
            ]
        );

        // 4. Property Types
        $residential = PropertyType::where('slug', 'residential-apartment')->first() ?? PropertyType::first();
        $villa = PropertyType::where('slug', 'luxury-villa')->first() ?? PropertyType::first();
        $commercial = PropertyType::where('slug', 'commercial-office')->first() ?? PropertyType::first();
        $landType = PropertyType::where('slug', 'land-plot')->first() ?? PropertyType::first();
        $farmType = PropertyType::where('slug', 'agricultural-farm')->first() ?? PropertyType::first();

        // 5. Additional Realistic Tanzanian Properties
        $propertiesData = [
            // Morogoro Properties
            [
                'title' => 'Modern 4-Bedroom Family House in Kihonda',
                'slug' => 'modern-4-bedroom-family-house-kihonda-morogoro',
                'property_code' => 'RREP-MOR-001',
                'property_type_id' => $villa->id,
                'property_owner_id' => $ownerMorogoro->id,
                'listing_type' => 'Sale',
                'status' => 'Available',
                'price' => 185000000.00,
                'currency' => 'TZS',
                'address' => 'Kihonda Magorofani, Near St. Joseph',
                'city' => 'Morogoro',
                'state' => 'Morogoro Urban',
                'latitude' => -6.79250000,
                'longitude' => 37.64860000,
                'area_size' => 420.00,
                'area_unit' => 'Sqm',
                'bedrooms' => 4,
                'bathrooms' => 3,
                'floors' => 1,
                'parking_spaces' => 3,
                'year_built' => 2024,
                'is_featured' => true,
                'is_published' => true,
                'views_count' => 840,
                'description' => 'Beautiful contemporary 4-bedroom bungalow situated in prime Kihonda Magorofani. Master bedroom en-suite with walk-in closet, modern fitted kitchen with granite countertops, paved driveway, landscaped garden, and high perimeter wall with electric fence. Clean title deed ready.',
                'features_json' => ['Electric Fence', 'Paved Compound', 'Borehole Water + DAWASA', 'Servant Quarter', 'Title Deed Clean'],
                'branch_id' => $branchModels['Morogoro']->id,
            ],
            [
                'title' => 'Surveyed 5-Acre Agricultural & Farm Plot in Mazimbu',
                'slug' => 'surveyed-5-acre-farm-plot-mazimbu-morogoro',
                'property_code' => 'RREP-MOR-002',
                'property_type_id' => $landType->id,
                'property_owner_id' => $ownerMorogoro->id,
                'listing_type' => 'Sale',
                'status' => 'Available',
                'price' => 75000000.00,
                'currency' => 'TZS',
                'address' => 'Mazimbu Extension, Near SUA Border',
                'city' => 'Morogoro',
                'state' => 'Morogoro Rural',
                'latitude' => -6.81500000,
                'longitude' => 37.62000000,
                'area_size' => 5.00,
                'area_unit' => 'Acres',
                'is_featured' => true,
                'is_published' => true,
                'views_count' => 620,
                'description' => 'Fertile 5-acre agricultural land with permanent stream water access, suitable for horticulture, poultry farming, or future residential subdivision. Full cadastral survey completed with beacons intact.',
                'features_json' => ['Survey Beacons Intact', 'River/Stream Water', 'All-Weather Road Access', 'Loam Soil', 'Customary Right of Occupancy'],
                'branch_id' => $branchModels['Morogoro']->id,
            ],
            // Dar es Salaam Properties
            [
                'title' => 'Mikocheni B Prime 3-Bedroom Fully Furnished Apartment',
                'slug' => 'mikocheni-b-prime-3-bedroom-furnished-apartment',
                'property_code' => 'RREP-DAR-005',
                'property_type_id' => $residential->id,
                'property_owner_id' => $ownerDar->id,
                'listing_type' => 'Rent',
                'status' => 'Available',
                'price' => 0.00,
                'rent_price' => 2200000.00,
                'rent_period' => 'Monthly',
                'deposit_amount' => 6600000.00,
                'currency' => 'TZS',
                'address' => 'Mwai Kibaki Road, Mikocheni B',
                'city' => 'Dar es Salaam',
                'state' => 'Kinondoni',
                'latitude' => -6.76200000,
                'longitude' => 39.24500000,
                'area_size' => 180.00,
                'area_unit' => 'Sqm',
                'bedrooms' => 3,
                'bathrooms' => 3,
                'floors' => 5,
                'parking_spaces' => 2,
                'year_built' => 2023,
                'is_featured' => true,
                'is_published' => true,
                'views_count' => 950,
                'description' => 'Luxurious fully-furnished 3-bedroom apartment on the 4th floor with sea breeze and elevator access. Includes standby generator, gym, swimming pool, 24/7 security guard, and private balcony.',
                'features_json' => ['Fully Furnished', 'Swimming Pool', 'Gym Access', 'Standby Generator', 'High-Speed Lift'],
                'branch_id' => $branchModels['Dar es Salaam']->id,
            ],
            [
                'title' => 'Kigamboni Beachfront 2-Acre Commercial Development Plot',
                'slug' => 'kigamboni-beachfront-2-acre-commercial-plot',
                'property_code' => 'RREP-DAR-006',
                'property_type_id' => $landType->id,
                'property_owner_id' => $ownerDar->id,
                'listing_type' => 'Sale',
                'status' => 'Available',
                'price' => 650000000.00,
                'currency' => 'TZS',
                'address' => 'Gezaulole Coastal Corridor, Kigamboni',
                'city' => 'Dar es Salaam',
                'state' => 'Kigamboni',
                'latitude' => -6.89200000,
                'longitude' => 39.38500000,
                'area_size' => 2.00,
                'area_unit' => 'Acres',
                'is_featured' => true,
                'is_published' => true,
                'views_count' => 1100,
                'description' => 'Unmatched beachfront land parcel ideal for luxury boutique resort, beach club, or gated villa cluster. Surveyed with 99-year Right of Occupancy title deed.',
                'features_json' => ['Direct Beach Access', 'Survey Title Deed 99 Yrs', 'Electricity on Site', 'Tarmac Road Connection'],
                'branch_id' => $branchModels['Dar es Salaam']->id,
            ],
            // Zanzibar Property
            [
                'title' => 'Fumba Town 2-Bedroom Coastal Villa with Garden',
                'slug' => 'fumba-town-2-bedroom-coastal-villa-zanzibar',
                'property_code' => 'RREP-ZNZ-001',
                'property_type_id' => $villa->id,
                'property_owner_id' => $ownerDar->id,
                'listing_type' => 'Sale',
                'status' => 'Available',
                'price' => 320000000.00,
                'currency' => 'TZS',
                'address' => 'Fumba Eco-Township, South District',
                'city' => 'Zanzibar',
                'state' => 'Urban West',
                'latitude' => -6.31500000,
                'longitude' => 39.27800000,
                'area_size' => 145.00,
                'area_unit' => 'Sqm',
                'bedrooms' => 2,
                'bathrooms' => 2,
                'floors' => 1,
                'parking_spaces' => 2,
                'year_built' => 2024,
                'is_featured' => true,
                'is_published' => true,
                'views_count' => 1350,
                'description' => 'Sustainable modern villa situated in the world-renowned Fumba Town master development. Foreigners eligible for Zanzibar residency permit upon purchase. Excellent rental yield on Airbnb and long-term expat leases.',
                'features_json' => ['Residency Permit Eligible', 'Eco-Friendly Solar', 'Gated Community', 'Beach Walking Distance'],
                'branch_id' => $branchModels['Zanzibar']->id,
            ],
        ];

        foreach ($propertiesData as $pData) {
            $prop = Property::firstOrCreate(
                ['slug' => $pData['slug']],
                array_merge($pData, ['organization_id' => $org?->id, 'created_by' => $admin?->id])
            );

            // Add LandParcel record for land type
            if ($pData['property_type_id'] === $landType->id && ! $prop->landParcel) {
                LandParcel::create([
                    'property_id' => $prop->id,
                    'parcel_number' => 'PLOT-'.strtoupper(Str::random(6)),
                    'deed_number' => 'TITLE-'.rand(10000, 99999),
                    'survey_plan_number' => 'TP-'.rand(1000, 9999),
                    'title_deed_type' => 'Right of Occupancy (99 Yrs)',
                    'acreage' => $pData['area_size'],
                    'tenure_years_remaining' => 98,
                    'zoning' => 'Mixed Use',
                    'topography' => 'Flat',
                    'soil_type' => 'Loam / Sand',
                    'boundary_coordinates_json' => [
                        ['beacon' => 'B1', 'lat' => $pData['latitude'] + 0.001, 'lng' => $pData['longitude'] + 0.001],
                        ['beacon' => 'B2', 'lat' => $pData['latitude'] + 0.001, 'lng' => $pData['longitude'] - 0.001],
                        ['beacon' => 'B3', 'lat' => $pData['latitude'] - 0.001, 'lng' => $pData['longitude'] - 0.001],
                        ['beacon' => 'B4', 'lat' => $pData['latitude'] - 0.001, 'lng' => $pData['longitude'] + 0.001],
                    ],
                ]);
            }
        }

        // 6. Real Estate Projects / Developments
        $projects = [
            [
                'title' => 'Morogoro Uluguru Green Acres Satellite City',
                'slug' => 'morogoro-uluguru-green-acres-satellite-city',
                'developer_name' => 'Uluguru Property Developments Ltd',
                'project_type' => 'Master-Planned Land',
                'project_status' => 'Selling',
                'starting_price' => 18000000.00,
                'currency' => 'TZS',
                'location_name' => 'Kihonda - Mazimbu Growth Corridor',
                'city' => 'Morogoro',
                'state' => 'Morogoro Urban',
                'latitude' => -6.78500000,
                'longitude' => 37.63500000,
                'total_units' => 250,
                'available_units' => 84,
                'launch_date' => '2024-01-15',
                'expected_completion_date' => '2026-12-31',
                'description' => 'A premier 120-acre master-planned residential and commercial land development at the foot of the scenic Uluguru Mountains. Fully surveyed cadastral plots with title deeds, tarmac arterial roads, street lighting, DAWASCO water connections, and dedicated school/commercial reserves.',
                'amenities_json' => ['Title Deeds Ready', 'Tarmac Roads & Drainage', 'Electricity & Streetlights', 'Commercial Center Zone', 'Green Parks & Playgrounds'],
                'unit_types_json' => [
                    ['name' => 'Standard Residential Plot (600 Sqm)', 'price' => 18000000, 'size' => '600 Sqm', 'available' => 45],
                    ['name' => 'Executive Villa Plot (1,000 Sqm)', 'price' => 28000000, 'size' => '1,000 Sqm', 'available' => 25],
                    ['name' => 'Commercial High-Street Plot (1,500 Sqm)', 'price' => 45000000, 'size' => '1,500 Sqm', 'available' => 14],
                ],
                'is_featured' => true,
                'is_published' => true,
                'branch_id' => $branchModels['Morogoro']->id,
            ],
            [
                'title' => 'Kigamboni Coastal Marina Residences',
                'slug' => 'kigamboni-coastal-marina-residences',
                'developer_name' => 'Amani Seafront Developers',
                'project_type' => 'Residential Estate',
                'project_status' => 'Under Construction',
                'starting_price' => 240000000.00,
                'currency' => 'TZS',
                'location_name' => 'South Coast Road, Gezaulole',
                'city' => 'Dar es Salaam',
                'state' => 'Kigamboni',
                'latitude' => -6.89500000,
                'longitude' => 39.38000000,
                'total_units' => 60,
                'available_units' => 19,
                'launch_date' => '2024-06-01',
                'expected_completion_date' => '2025-11-30',
                'description' => 'A gated waterfront sanctuary comprising 3 and 4-bedroom contemporary coastal villas overlooking the Indian Ocean. Features private beach access, clubhouse, infinity swimming pool, and boat mooring dock.',
                'amenities_json' => ['Private Beach Access', 'Clubhouse & Infinity Pool', '24/7 Biometric Security', 'Backup Solar Generator', 'Tennis & Padel Courts'],
                'unit_types_json' => [
                    ['name' => '3-Bedroom Marina Villa', 'price' => 240000000, 'size' => '220 Sqm', 'available' => 11],
                    ['name' => '4-Bedroom Executive Waterfront Villa', 'price' => 380000000, 'size' => '350 Sqm', 'available' => 8],
                ],
                'is_featured' => true,
                'is_published' => true,
                'branch_id' => $branchModels['Dar es Salaam']->id,
            ],
            [
                'title' => 'Victoria Financial Plaza & Luxury Suites',
                'slug' => 'victoria-financial-plaza-luxury-suites',
                'developer_name' => 'Victoria Capital Real Estate',
                'project_type' => 'Commercial Plaza',
                'project_status' => 'Selling',
                'starting_price' => 165000000.00,
                'currency' => 'TZS',
                'location_name' => 'New Bagamoyo Road, Victoria',
                'city' => 'Dar es Salaam',
                'state' => 'Kinondoni',
                'latitude' => -6.77200000,
                'longitude' => 39.24800000,
                'total_units' => 80,
                'available_units' => 28,
                'launch_date' => '2023-11-01',
                'expected_completion_date' => '2025-08-31',
                'description' => 'A 14-storey landmark commercial tower integrating Grade-A executive office suites with rooftop sky lounge, conference centers, and high-street banking halls.',
                'amenities_json' => ['Dual High-Speed Lifts', 'Fiber Optic Backbone', 'Basement 3-Level Parking', 'Central VRF Air Conditioning', 'Rooftop Helipad & Lounge'],
                'unit_types_json' => [
                    ['name' => 'Corporate Office Suite (85 Sqm)', 'price' => 165000000, 'size' => '85 Sqm', 'available' => 18],
                    ['name' => 'Full Floor Plate (450 Sqm)', 'price' => 850000000, 'size' => '450 Sqm', 'available' => 4],
                ],
                'is_featured' => true,
                'is_published' => true,
                'branch_id' => $branchModels['Dar es Salaam']->id,
            ],
            [
                'title' => 'Meru Panoramic Eco-Villas Estate',
                'slug' => 'meru-panoramic-eco-villas-estate',
                'developer_name' => 'Northern Safari Living Ltd',
                'project_type' => 'Residential Estate',
                'project_status' => 'Pre-Launch',
                'starting_price' => 290000000.00,
                'currency' => 'TZS',
                'location_name' => 'Oldonyo Sambu Foothills, Ngaramtoni',
                'city' => 'Arusha',
                'state' => 'Arumeru',
                'latitude' => -3.32800000,
                'longitude' => 36.65400000,
                'total_units' => 32,
                'available_units' => 24,
                'launch_date' => '2024-09-01',
                'expected_completion_date' => '2026-06-30',
                'description' => 'Exclusive low-density eco-friendly residences nestled in the foothills of Mount Meru with unobstructed wildlife and mountain vistas. Exceptional passive solar design, rain harvesting, and organic coffee farm integration.',
                'amenities_json' => ['Mountain Views', 'Solar Off-Grid Power', 'Coffee Estate Trails', 'Helipad Access', '24/7 Security Patrol'],
                'unit_types_json' => [
                    ['name' => '3-Bedroom Meru Stone Villa', 'price' => 290000000, 'size' => '260 Sqm', 'available' => 16],
                    ['name' => '4-Bedroom Grand Safari Chalet', 'price' => 420000000, 'size' => '380 Sqm', 'available' => 8],
                ],
                'is_featured' => true,
                'is_published' => true,
                'branch_id' => $branchModels['Arusha']->id,
            ],
        ];

        foreach ($projects as $proj) {
            RealEstateProject::firstOrCreate(
                ['slug' => $proj['slug']],
                array_merge($proj, ['organization_id' => $org?->id])
            );
        }

        // 7. Articles / Real Estate Insights & Guides
        $articles = [
            [
                'title' => 'Complete Guide to Buying Titled Land in Morogoro & Dar es Salaam',
                'slug' => 'guide-to-buying-titled-land-morogoro-dar-es-salaam',
                'category' => 'Land Ownership',
                'excerpt' => 'Understand the legal process of land acquisition in Tanzania, from cadastral survey verification, title deed search at the Ministry of Lands, to final deed transfer.',
                'content' => "Land acquisition in Tanzania requires diligent due diligence. Whether you are purchasing residential plots in Kihonda (Morogoro) or beachfront land in Kigamboni (Dar es Salaam), verifying the survey plan number, beacon coordinates, and Right of Occupancy deed is vital.\n\n### Step 1: Official Cadastral Search\nAlways request the official Survey Plan (Town Planning / TP Drawing) and cross-check beacon coordinates using licensed land surveyors.\n\n### Step 2: Ministry of Lands Title Search\nConduct an official land registry search to ensure there are no encumbrances, bank caveats, or unresolved boundary disputes.\n\n### Step 3: Formal Sales Agreement\nExecute a legally binding sales agreement witnessed by registered Advocates and the local Ward Executive Officer (Mwenyekiti wa Serikali ya Mtaa).",
                'author_name' => 'Eng. Josephat Mwakyusa',
                'author_role' => 'Principal Land Surveyor & GIS Consultant',
                'reading_time_minutes' => 6,
                'tags_json' => ['Land Survey', 'Title Deeds', 'Tanzania Law', 'Morogoro', 'Property Buying'],
                'is_featured' => true,
                'is_published' => true,
                'published_at' => now()->subDays(5),
            ],
            [
                'title' => 'Why Morogoro is Becoming Tanzania’s Next Real Estate Investment Frontier',
                'slug' => 'why-morogoro-is-becoming-real-estate-investment-frontier',
                'category' => 'Real Estate Investment',
                'excerpt' => 'Strategic position along the Standard Gauge Railway (SGR), expanding universities, agricultural processing hubs, and affordable land make Morogoro prime for high capital appreciation.',
                'content' => "Morogoro is rapidly evolving from a quiet regional town into a major industrial, educational, and logistics hub. The operational Standard Gauge Railway (SGR) connects Morogoro to Dar es Salaam in just 90 minutes, sparking unprecedented demand for residential housing and logistics warehouses in Kihonda, Mazimbu, and Kingolwira.\n\nProperty investors are seeing land values in prime corridors appreciate by 20-35% annually. Emerging developments such as master-planned subdivisions and satellite residential estates are providing structured opportunities for forward-looking buyers.",
                'author_name' => 'Salma Khalfan',
                'author_role' => 'Senior Property Investment Analyst',
                'reading_time_minutes' => 5,
                'tags_json' => ['Morogoro', 'SGR', 'Land Appreciation', 'Investment', 'Real Estate Trends'],
                'is_featured' => true,
                'is_published' => true,
                'published_at' => now()->subDays(12),
            ],
            [
                'title' => 'The Critical Role of Cadastral & Boundary Survey Before Buying Property',
                'slug' => 'critical-role-cadastral-boundary-survey-before-buying',
                'category' => 'Land Surveying',
                'excerpt' => 'Avoid costly land disputes. Discover how professional beacon replacement, boundary verification, and GIS topographical mapping protect your real estate investments.',
                'content' => "Boundary conflicts remain one of the leading causes of property litigation in East Africa. Many buyers mistakenly assume that an existing fence or informal hedge represents the exact legal boundary of a parcel.\n\n### What a Cadastral Survey Accomplishes:\n1. **Beacon Relocation & Fixation**: Using high-precision GNSS/RTK GPS systems to locate original survey beacons or replace missing markers.\n2. **Area Verification**: Ensuring the land size stated in the contract exactly matches ground reality.\n3. **Road Encroachment Checks**: Verifying that no part of the parcel infringes on gazetted public road reserves or railway corridors.",
                'author_name' => 'Surveyor Daudi Ndunguru',
                'author_role' => 'Licensed Geomatics Engineer',
                'reading_time_minutes' => 7,
                'tags_json' => ['Land Survey', 'GIS Mapping', 'Beacons', 'Boundary Verification'],
                'is_featured' => true,
                'is_published' => true,
                'published_at' => now()->subDays(18),
            ],
            [
                'title' => 'Maximizing Rental Yields in Dar es Salaam: Apartments vs Standalone Houses',
                'slug' => 'maximizing-rental-yields-dar-es-salaam-apartments-vs-houses',
                'category' => 'Market Insights',
                'excerpt' => 'A data-driven comparative analysis of rental income, occupancy rates, and capital returns in Masaki, Mikocheni, Victoria, and Kigamboni.',
                'content' => "For buy-to-let property investors in Dar es Salaam, choosing between multi-unit residential apartments and standalone villas requires analyzing gross rental yields versus maintenance overheads.\n\nModern 2 and 3-bedroom furnished apartments in areas like Mikocheni and Victoria deliver average rental yields between 8.5% and 11% driven by young professionals and corporate expat tenants, whereas standalone houses provide stronger long-term capital land appreciation.",
                'author_name' => 'Hassan Mkama',
                'author_role' => 'Commercial Property Consultant',
                'reading_time_minutes' => 4,
                'tags_json' => ['Rental Yields', 'Dar es Salaam', 'Apartments', 'Property Management'],
                'is_featured' => false,
                'is_published' => true,
                'published_at' => now()->subDays(25),
            ],
        ];

        foreach ($articles as $art) {
            Article::firstOrCreate(
                ['slug' => $art['slug']],
                array_merge($art, ['organization_id' => $org?->id])
            );
        }

        // 8. Testimonials
        $testimonials = [
            [
                'customer_name' => 'Dr. Peter Mlay',
                'customer_role' => 'Property Buyer',
                'company' => 'Kihonda Family Home Buyer',
                'location' => 'Morogoro, Tanzania',
                'rating' => 5,
                'feedback' => 'Finding our dream 4-bedroom house in Morogoro was seamless with REMS. Every title document was verified in advance, and the site viewing was scheduled within hours through WhatsApp.',
                'is_featured' => true,
                'is_active' => true,
                'display_order' => 1,
            ],
            [
                'customer_name' => 'Fatma Al-Jabri',
                'customer_role' => 'Land Survey Client',
                'company' => 'Kilimo Bora Agribusiness',
                'location' => 'Mazimbu, Morogoro',
                'rating' => 5,
                'feedback' => 'The land survey team dispatched licensed surveyors with RTK GPS equipment who beaconed our 10-acre farm plot in Mazimbu within two days and provided certified cadastral drawings.',
                'is_featured' => true,
                'is_active' => true,
                'display_order' => 2,
            ],
            [
                'customer_name' => 'Edward Lowassa Jr.',
                'customer_role' => 'Commercial Real Estate Investor',
                'company' => 'Savannah Capital Investments',
                'location' => 'Dar es Salaam & Arusha',
                'rating' => 5,
                'feedback' => 'REMS platform offers unmatched transparency in East African real estate. The financial modeling, deed verification, and digital booking system set a new industry benchmark.',
                'is_featured' => true,
                'is_active' => true,
                'display_order' => 3,
            ],
            [
                'customer_name' => 'Gladys Mbowe',
                'customer_role' => 'Tenant',
                'company' => 'Victoria Tower Executive Tenant',
                'location' => 'Dar es Salaam',
                'rating' => 5,
                'feedback' => 'Secured our Grade-A office space in Victoria Plaza directly through the marketplace. Smooth lease signing, automated digital invoicing, and professional property management.',
                'is_featured' => true,
                'is_active' => true,
                'display_order' => 4,
            ],
        ];

        foreach ($testimonials as $t) {
            Testimonial::firstOrCreate(
                ['customer_name' => $t['customer_name']],
                array_merge($t, ['organization_id' => $org?->id])
            );
        }

        // 9. Frequently Asked Questions (FAQs)
        $faqs = [
            // Buying
            [
                'category' => 'Buying',
                'question' => 'How does REMS verify property listings and title deeds?',
                'answer' => 'Every property submitted to the REMS marketplace undergoes a multi-point verification protocol: cadastral survey plan verification at the Ministry of Lands, physical beacon inspection by certified geomatics surveyors, and legal ownership KYC confirmation before receiving the "Verified Listing" badge.',
                'display_order' => 1,
            ],
            [
                'category' => 'Buying',
                'question' => 'Can diaspora and international buyers purchase properties in Tanzania?',
                'answer' => 'Yes. Tanzanian citizens abroad can purchase any titled property. Foreign investors can acquire land through TIC (Tanzania Investment Centre) derivative rights, long-term commercial leases, or designated special economic zones such as Fumba Town in Zanzibar which grants residency eligibility.',
                'display_order' => 2,
            ],
            [
                'category' => 'Buying',
                'question' => 'What additional fees apply when buying property in Tanzania?',
                'answer' => 'Standard statutory costs include Stamp Duty (typically 1% of property value), Land Transfer Fees (0.5%), Title Registration Fees, and Advocate Legal Conveyancing fees (usually 1-3%).',
                'display_order' => 3,
            ],
            // Land & Survey
            [
                'category' => 'Land & Survey',
                'question' => 'What land survey services can I request through the platform?',
                'answer' => 'You can request boundary beacon relocation, full cadastral plot surveys, topographical GIS contour mapping, agricultural estate subdivision, construction engineering setting-out, and official town planning deed verification.',
                'display_order' => 4,
            ],
            [
                'category' => 'Land & Survey',
                'question' => 'How long does a boundary cadastral survey take?',
                'answer' => 'Fieldwork for standard residential and agricultural plots is typically completed within 24 to 48 hours. Processed computation sheets, beacon coordinates, and official survey plans are delivered within 3 to 5 business days.',
                'display_order' => 5,
            ],
            // Renting
            [
                'category' => 'Renting',
                'question' => 'How do I book an in-person property viewing?',
                'answer' => 'Simply click "Book Viewing" on any property page, select your preferred date and time, and submit your contact details. You can also click "Chat on WhatsApp" for instant real-time coordination with our assigned property specialist.',
                'display_order' => 6,
            ],
            // Listing
            [
                'category' => 'Listing',
                'question' => 'How can property owners and developers advertise on REMS?',
                'answer' => 'Click "List Property" on the top navigation bar. You can register an owner account, upload high-resolution photos, describe property features, attach survey coordinates, and submit for verification and publication.',
                'display_order' => 7,
            ],
        ];

        foreach ($faqs as $faq) {
            Faq::firstOrCreate(
                ['question' => $faq['question']],
                array_merge($faq, ['organization_id' => $org?->id, 'is_active' => true])
            );
        }
    }
}
