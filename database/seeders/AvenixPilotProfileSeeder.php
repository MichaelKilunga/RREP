<?php

namespace Database\Seeders;

use App\Models\BrandingConfig;
use App\Models\Organization;
use App\Models\SystemSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class AvenixPilotProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $org = Organization::first();
        if ($org) {
            $org->update([
                'name' => 'AVENIX COMPANY LIMITED',
                'email' => 'info@avenix.co.tz',
                'website' => 'https://avenix.co.tz',
                'phone' => '+255 784 100 200',
                'address' => 'Dar es Salaam & Dodoma Urban Growth Corridors',
                'city' => 'Dar es Salaam',
                'country' => 'Tanzania',
                'status' => 'Active',
            ]);
        }

        $orgId = $org?->id;

        // 1. Branding Config (Royal Blue, Slate, Accent)
        $branding = BrandingConfig::firstOrNew(['organization_id' => $orgId]);
        $branding->primary_color = '#0b3c99'; // Deep / Royal Blue per Profile.pdf
        $branding->secondary_color = '#1e293b'; // Charcoal Slate
        $branding->accent_color = '#0284c7'; // Sky / Cadastral Cyan
        $branding->sidebar_theme = 'dark';
        $branding->company_tagline = 'Your Land, Our Expertise.';
        $branding->custom_css = "/* AVENIX Corporate Branding */\n.brand-font { font-family: 'Plus Jakarta Sans', sans-serif; letter-spacing: -0.01em; }\n.btn-primary { box-shadow: 0 4px 14px rgba(11, 60, 153, 0.25); }\n.hero-banner { border-bottom: 3px solid #0b3c99; }";
        $branding->save();

        // 2. System Settings - General Identity
        $settings = [
            'company_name' => 'AVENIX COMPANY LIMITED',
            'company_subtitle' => 'Real Estate • Land • Property Solutions',
            'company_tagline' => 'Your Land, Our Expertise.',
            'brand_monogram' => 'A',
            'contact_phone' => '+255 784 100 200',
            'contact_whatsapp' => '255784100200',
            'contact_email' => 'info@avenix.co.tz',
            'contact_address' => 'Dar es Salaam & Dodoma, Tanzania',
            'footer_copyright' => 'All Rights Reserved. Platform Architecture by RehoSpace Company Limited.',
            'facebook_url' => 'https://facebook.com/avenixltd',
            'instagram_url' => 'https://instagram.com/avenix_tz',
            'linkedin_url' => 'https://linkedin.com/company/avenix-ltd',

            // Landing Page Master Section Switches
            'landing_topbar_enabled' => '1',
            'landing_search_enabled' => '1',
            'landing_categories_enabled' => '1',
            'landing_featured_enabled' => '1',
            'landing_locations_enabled' => '1',
            'landing_latest_enabled' => '1',
            'landing_projects_enabled' => '1',
            'landing_land_enabled' => '1',
            'landing_services_enabled' => '1',
            'landing_trust_enabled' => '1',
            'landing_testimonials_enabled' => '1',
            'landing_blog_enabled' => '1',
            'landing_owner_cta_enabled' => '1',
            'landing_whatsapp_enabled' => '1',

            // Topbar & Utility Bar
            'landing_topbar_ticker_label' => 'AVENIX BULLETIN',
            'landing_topbar_ticker_text' => 'Verified surveyed plots now available in Dodoma, Dar es Salaam, Arusha & Mwanza growth corridors.',
            'landing_topbar_hotline' => '+255 784 100 200',
            'landing_topbar_support_email' => 'info@avenix.co.tz',
            'landing_topbar_survey_text' => 'Book Survey Team',
            'landing_topbar_survey_icon' => 'bi-geo-alt-fill',
            'landing_topbar_survey_url' => '/survey/request',
            'landing_topbar_staff_text' => 'Client & Staff Portal',
            'landing_topbar_staff_icon' => 'bi-person-badge',

            // Hero Section
            'landing_hero_title' => "Your Land, Our Expertise.\nIntegrated Land & Real Estate Solutions.",
            'landing_hero_subtitle' => 'Connecting real estate marketing, cadastral surveying, land planning, and GIS spatial intelligence to empower individuals, investors, and developers across Tanzania.',
            'landing_hero_badge_icon' => 'bi-patch-check-fill',
            'landing_hero_badge_text' => "Tanzania's Integrated Land & Survey Platform",

            // Hero Search Intent Tabs
            'landing_search_tab_buy' => 'Buy Residential',
            'landing_search_tab_rent' => 'Commercial',
            'landing_search_tab_land' => 'Surveyed Land & Plots',
            'landing_search_tab_projects' => 'Investment Zones',
            'landing_search_button_text' => 'Search Verified Plots',

            // Services Section (3-Card Suite per Profile.pdf Page 4)
            'landing_services_tag' => 'OUR SERVICE PORTFOLIO',
            'landing_services_title' => 'Integrated Land, Property & Planning Solutions',
            'landing_services_subtitle' => 'We coordinate real estate, surveying, and planning capabilities within one disciplined service model.',
            'landing_service_1_icon' => 'bi-shield-check',
            'landing_service_1_title' => 'Land Acquisition & Due Diligence',
            'landing_service_1_desc' => 'Land search support, official ownership documentation checks, preliminary review, and structured transaction coordination.',
            'landing_service_1_btn_text' => 'Inquire for Land Search',
            'landing_service_1_btn_url' => '/services/property-sales',
            'landing_service_2_icon' => 'bi-compass',
            'landing_service_2_title' => 'Land Surveying & Cadastral Mapping',
            'landing_service_2_desc' => 'Cadastral boundary coordination, topographical surveys, GNSS/GPS spatial data capture, beacon relocation, and subdivision support.',
            'landing_service_2_btn_text' => 'Book Survey Team',
            'landing_service_2_btn_url' => '/survey/request',
            'landing_service_3_icon' => 'bi-buildings',
            'landing_service_3_title' => 'Town & Land Use Planning',
            'landing_service_3_desc' => 'Site layout planning, zoning advisory, development schemes, and seamless coordination with relevant land authorities.',
            'landing_service_3_btn_text' => 'Consult Planning Experts',
            'landing_service_3_btn_url' => '/services/valuation-consulting',

            // Trust Protocol & Statistics (Why AVENIX per Profile.pdf Page 5)
            'landing_trust_badge' => 'THE AVENIX ADVANTAGE',
            'landing_trust_title' => 'Why Partner with AVENIX COMPANY LIMITED',
            'landing_trust_body' => 'Good land decisions begin with reliable information, proper planning, appropriate technical support, and transparent communication. We connect you with the right expertise at each stage of your property journey.',
            'landing_pillar_1_icon' => 'bi-diagram-3-fill',
            'landing_pillar_1_title' => 'Integrated Expertise',
            'landing_pillar_1_desc' => 'Land, property, planning, surveying, and GIS capabilities coordinated within one streamlined service model.',
            'landing_pillar_2_icon' => 'bi-clipboard2-data-fill',
            'landing_pillar_2_title' => 'Information-Led Decisions',
            'landing_pillar_2_desc' => 'We emphasize reliable property records and spatial verification to support sound investment decisions.',
            'landing_pillar_3_icon' => 'bi-geo-alt-fill',
            'landing_pillar_3_title' => 'Technology & Mapping',
            'landing_pillar_3_desc' => 'Modern GNSS/GPS spatial technology and digital mapping for absolute clarity on boundaries and terrain context.',
            'landing_stats_verified_override' => '350',
            'landing_stats_sales_override' => '120',
            'landing_stats_customers_override' => '450',
            'landing_stats_districts_override' => '4',

            // Strategic Locations (Profile.pdf Page 5 Target Locations)
            'landing_locations_tag' => 'STRATEGIC GROWTH CORRIDORS',
            'landing_locations_title' => 'Prime Land & Property Markets Across Tanzania',
            'landing_locations_subtitle' => 'Targeting high-potential opportunities in urban growth corridors, commercial hubs, and infrastructure-led development zones.',
            'landing_location_1_name' => 'Dodoma Capital',
            'landing_location_1_desc' => 'Government capital, Mtumba administrative city & expanding investment corridors',
            'landing_location_2_name' => 'Dar es Salaam',
            'landing_location_2_desc' => 'Commercial economic hub, prime residential plots, beach fronts & business districts',
            'landing_location_3_name' => 'Arusha City',
            'landing_location_3_desc' => 'Northern safari gateway, scenic highland estates & agricultural investment land',
            'landing_location_4_name' => 'Mwanza Hub',
            'landing_location_4_desc' => 'Lake Victoria economic zone, commercial centers & prime lakeside plots',
            'landing_location_5_name' => 'Strategic Towns',
            'landing_location_5_desc' => 'Emerging urban growth corridors experiencing infrastructure-led development',

            // Property Owner CTA Banner
            'landing_owner_cta_title' => 'Have Land or Property to Sell in Tanzania?',
            'landing_owner_cta_subtitle' => 'Partner with Avenix Company Limited for professional cadastral mapping, document verification, and targeted marketplace positioning to qualified buyers.',
            'landing_owner_cta_btn_icon' => 'bi-cloud-arrow-up',
            'landing_owner_cta_btn_text' => 'List Your Plot with Avenix',
            'landing_owner_cta_btn_url' => '/login',
            'landing_owner_cta_sec_btn_icon' => 'bi-compass',
            'landing_owner_cta_sec_btn_text' => 'Book a Field Survey Team',
            'landing_owner_cta_sec_btn_url' => '/survey/request',

            // Footer & Floating WhatsApp Widget
            'landing_footer_bio' => 'AVENIX COMPANY LIMITED is a Tanzanian real estate and land solutions company providing practical, professional, and technology-informed services across the property and land value chain.',
            'landing_whatsapp_greeting' => 'Hello Avenix Company Limited! I am inquiring about your verified land listings and survey services.',
        ];

        foreach ($settings as $key => $val) {
            SystemSetting::setVal($key, $val, 'landing', $orgId);
        }

        Cache::flush();
    }
}
