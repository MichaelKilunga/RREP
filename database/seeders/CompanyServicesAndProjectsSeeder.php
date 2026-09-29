<?php

namespace Database\Seeders;

use App\Models\CompanyService;
use App\Models\LandParcel;
use App\Models\RealEstateProject;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CompanyServicesAndProjectsSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed the 8 Core Client Services
        $services = [
            [
                'title' => 'Land Sales',
                'slug' => 'land-sales',
                'category' => 'Land',
                'short_description' => 'Prime surveyed land plots for residential, commercial, and industrial development across Tanzania.',
                'full_description' => 'We offer verified land parcels with clean title deeds and official survey plans. All plots are free from encumbrances.',
                'icon' => 'bi-geo-alt-fill',
                'display_order' => 1,
                'sub_services' => ['Residential Plots', 'Commercial Land', 'Industrial Parks', 'Farm Land'],
            ],
            [
                'title' => 'TP Drawing Preparation',
                'slug' => 'tp-drawing-preparation',
                'category' => 'Planning',
                'short_description' => 'Town Planning (TP) drawing preparation, sub-division schemes, and master plan drafting.',
                'full_description' => 'Professional Town Planning drawings prepared in strict compliance with the Ministry of Lands and local planning authorities.',
                'icon' => 'bi-rulers',
                'display_order' => 2,
                'sub_services' => ['Master Planning', 'Sub-division Schemes', 'Urban Renewal Plans', 'Zoning Permits'],
            ],
            [
                'title' => 'Surveying Services',
                'slug' => 'surveying',
                'category' => 'Surveying',
                'short_description' => 'Cadastral, Engineering, Topographical, Mining, and Hydrographical land surveys.',
                'full_description' => 'Certified survey solutions using state-of-the-art RTK GPS and total station instruments.',
                'icon' => 'bi-compass-fill',
                'display_order' => 3,
                'sub_services' => [
                    'Cadastral Survey',
                    'Engineering Survey',
                    'Topographical Survey',
                    'Mining Survey',
                    'Hydrographical Survey',
                ],
            ],
            [
                'title' => 'Comprehensive Land & Property Consultations',
                'slug' => 'land-property-consultations',
                'category' => 'Consultancy',
                'short_description' => 'Expert advisory on land tenure, property acquisition, due diligence, and dispute resolution.',
                'full_description' => 'Protect your real estate investment with comprehensive legal, technical, and commercial land consulting.',
                'icon' => 'bi-chat-left-text-fill',
                'display_order' => 4,
                'sub_services' => ['Due Diligence', 'Land Tenure Advisory', 'Property Valuation', 'Dispute Resolution'],
            ],
            [
                'title' => 'Title Deeds Facilitation Service',
                'slug' => 'title-deeds-facilitation',
                'category' => 'Legal',
                'short_description' => 'End-to-end processing and issuance of Certificate of Occupancy (Title Deeds).',
                'full_description' => 'Fast-track your Title Deed issuance with our experienced land officers handling Ministry of Lands workflows.',
                'icon' => 'bi-file-earmark-check-fill',
                'display_order' => 5,
                'sub_services' => ['Certificate of Occupancy', 'Title Transfer', 'Deed Search', 'Land Registration'],
            ],
            [
                'title' => 'Full Landscaping & Outdoor Development',
                'slug' => 'landscaping-outdoor-development',
                'category' => 'Development',
                'short_description' => 'Professional landscape architectural design, site grading, perimeter fencing, and greening.',
                'full_description' => 'Transform raw land into beautiful, functional spaces with master landscaping, paving, and drainage systems.',
                'icon' => 'bi-tree-fill',
                'display_order' => 6,
                'sub_services' => ['Perimeter Fencing', 'Site Grading & Paving', 'Greening & Lawn Care', 'Retaining Walls'],
            ],
            [
                'title' => 'Property Sales',
                'slug' => 'property-sales',
                'category' => 'Property',
                'short_description' => 'Direct sales of verified residential houses, commercial buildings, and industrial spaces.',
                'full_description' => 'Explore premium residential villas, modern office blocks, and commercial complexes with guaranteed ownership.',
                'icon' => 'bi-building-fill',
                'display_order' => 7,
                'sub_services' => ['Residential Houses', 'Commercial Buildings', 'Warehouses', 'Apartments'],
            ],
            [
                'title' => 'Property Construction Management',
                'slug' => 'property-construction-management',
                'category' => 'Development',
                'short_description' => 'End-to-end site supervision, project management, and quality control for building construction.',
                'full_description' => 'On-time, on-budget building construction oversight led by registered civil engineers and architects.',
                'icon' => 'bi-tools',
                'display_order' => 8,
                'sub_services' => ['Site Supervision', 'Cost Management', 'Quality Assurance', 'Contractor Coordination'],
            ],
        ];

        foreach ($services as $srv) {
            CompanyService::updateOrCreate(
                ['slug' => $srv['slug']],
                array_merge($srv, ['uuid' => Str::uuid()])
            );
        }

        // 2. Seed TAUSI-Inspired Land Projects with Interactive Shapefile / GeoJSON Plots
        $proj1 = RealEstateProject::updateOrCreate(
            ['slug' => 'maisaka-katani-project'],
            [
                'uuid' => Str::uuid(),
                'title' => 'MAISAKA KATANI PROJECT',
                'developer_name' => 'Babati Urban Development Authority',
                'project_type' => 'Master-Planned Land',
                'project_status' => 'Selling',
                'starting_price' => 15000000.00,
                'currency' => 'TZS',
                'location_name' => 'Maisaka Katani, Babati',
                'city' => 'Babati',
                'state' => 'Manyara',
                'country' => 'Tanzania',
                'region' => 'Manyara',
                'district' => 'Babati',
                'council' => 'Halmashauri ya Mji Babati',
                'latitude' => -4.2185,
                'longitude' => 35.7485,
                'total_units' => 54,
                'available_units' => 28,
                'sold_plots' => 26,
                'grace_period_days' => 30,
                'initial_payment_percent' => 50.00,
                'hero_image' => 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?w=800&auto=format&fit=crop&q=80',
                'description' => 'Mradi upo Kata ya Maisaka Katani wa Mji wa Babati. Upo Shule Mpya, umbali wa Kilomita tatu (3) kutoka Shule ya Zamani. Mradi unapatikana na Barabara ya Arusha. Mradi unahuduma za maji, umeme na Barabara za mtaa.',
                'is_featured' => true,
                'is_published' => true,
            ]
        );

        // Seed Plots for MAISAKA KATANI
        $centerLat = -4.2185;
        $centerLng = 35.7485;

        for ($i = 1; $i <= 12; $i++) {
            $latOffset = ($i % 4) * 0.0015;
            $lngOffset = floor($i / 4) * 0.0018;

            $pLat = $centerLat + $latOffset;
            $pLng = $centerLng + $lngOffset;

            // Generate polygon box around point
            $coords = [
                [$pLng, $pLat],
                [$pLng + 0.0012, $pLat],
                [$pLng + 0.0012, $pLat + 0.0010],
                [$pLng, $pLat + 0.0010],
                [$pLng, $pLat],
            ];

            $geoJsonGeometry = [
                'type' => 'Polygon',
                'coordinates' => [$coords],
            ];

            $status = $i <= 6 ? 'Available' : ($i <= 9 ? 'Sold' : 'Reserved');
            $landUse = $i % 3 === 0 ? 'Viwanda / Industrial' : ($i % 2 === 0 ? 'Biashara / Commercial' : 'Makazi / Residential');
            $sqm = 650 + ($i * 120);
            $price = 15000000 + ($i * 2500000);

            LandParcel::updateOrCreate(
                [
                    'real_estate_project_id' => $proj1->id,
                    'parcel_number' => (string) $i,
                    'block_number' => 'Block NN',
                ],
                [
                    'uuid' => Str::uuid(),
                    'property_id' => null,
                    'title_deed_type' => 'Right of Occupancy (Title Deed)',
                    'acreage' => round($sqm / 4046.86, 4),
                    'area_sqm' => $sqm,
                    'price' => $price,
                    'status' => $status,
                    'land_use' => $landUse,
                    'zoning' => $landUse,
                    'region' => 'Manyara',
                    'district' => 'Babati',
                    'council' => 'Halmashauri ya Mji Babati',
                    'lease_details' => '99-Year Leasehold',
                    'boundary_coordinates_json' => $coords,
                    'geojson_geometry' => json_encode($geoJsonGeometry),
                ]
            );
        }

        // Project 2: KIBAHA INDUSTRIAL & RESIDENTIAL ESTATE
        $proj2 = RealEstateProject::updateOrCreate(
            ['slug' => 'kibaha-viwanda-estate'],
            [
                'uuid' => Str::uuid(),
                'title' => 'KIBAHA INDUSTRIAL & RESIDENTIAL ESTATE',
                'developer_name' => 'Coast Regional Land Authority',
                'project_type' => 'Master-Planned Land',
                'project_status' => 'Selling',
                'starting_price' => 22000000.00,
                'currency' => 'TZS',
                'location_name' => 'Picha ya Ndege, Kibaha',
                'city' => 'Kibaha',
                'state' => 'Pwani',
                'country' => 'Tanzania',
                'region' => 'Pwani',
                'district' => 'Kibaha',
                'council' => 'Halmashauri ya Mji Kibaha',
                'latitude' => -6.7725,
                'longitude' => 38.9215,
                'total_units' => 40,
                'available_units' => 25,
                'sold_plots' => 15,
                'grace_period_days' => 30,
                'initial_payment_percent' => 40.00,
                'hero_image' => 'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?w=800&auto=format&fit=crop&q=80',
                'description' => 'Mradi wa Viwanda na Makazi Kibaha Picha ya Ndege. Upo karibu na barabara kuu ya Morogoro na reli ya SGR.',
                'is_featured' => true,
                'is_published' => true,
            ]
        );

        $centerLat2 = -6.7725;
        $centerLng2 = 38.9215;

        for ($i = 1; $i <= 10; $i++) {
            $latOffset = ($i % 3) * 0.0016;
            $lngOffset = floor($i / 3) * 0.0020;

            $pLat = $centerLat2 + $latOffset;
            $pLng = $centerLng2 + $lngOffset;

            $coords = [
                [$pLng, $pLat],
                [$pLng + 0.0014, $pLat],
                [$pLng + 0.0014, $pLat + 0.0011],
                [$pLng, $pLat + 0.0011],
                [$pLng, $pLat],
            ];

            $geoJsonGeometry = [
                'type' => 'Polygon',
                'coordinates' => [$coords],
            ];

            $status = $i <= 7 ? 'Available' : 'Sold';
            $landUse = $i % 2 === 0 ? 'Viwanda / Industrial' : 'Makazi / Residential';
            $sqm = 1000 + ($i * 200);
            $price = 22000000 + ($i * 3000000);

            LandParcel::updateOrCreate(
                [
                    'real_estate_project_id' => $proj2->id,
                    'parcel_number' => 'Plot '.(100 + $i),
                    'block_number' => 'Block A',
                ],
                [
                    'uuid' => Str::uuid(),
                    'title_deed_type' => 'Right of Occupancy (Title Deed)',
                    'acreage' => round($sqm / 4046.86, 4),
                    'area_sqm' => $sqm,
                    'price' => $price,
                    'status' => $status,
                    'land_use' => $landUse,
                    'region' => 'Pwani',
                    'district' => 'Kibaha',
                    'council' => 'Halmashauri ya Mji Kibaha',
                    'lease_details' => '99-Year Leasehold',
                    'boundary_coordinates_json' => $coords,
                    'geojson_geometry' => json_encode($geoJsonGeometry),
                ]
            );
        }
    }
}
