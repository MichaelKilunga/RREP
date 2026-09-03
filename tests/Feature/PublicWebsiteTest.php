<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\RealEstateProject;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class PublicWebsiteTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        SystemSetting::where('group', 'landing')->delete();
        SystemSetting::setVal('feature_online_reservations_enabled', '1');
        SystemSetting::setVal('feature_online_bookings_enabled', '1');
        SystemSetting::setVal('feature_property_owner_submissions_enabled', '1');
        SystemSetting::setVal('sms_enabled', '1');

        if (! Property::where('is_published', true)->exists()) {
            $type = PropertyType::first() ?: PropertyType::create([
                'name' => 'Apartment',
                'code' => 'APT',
                'icon' => 'building',
                'slug' => 'apartment',
            ]);
            Property::create([
                'title' => 'Victoria Prime Luxury Apartment',
                'slug' => 'victoria-prime-luxury-apartment',
                'property_code' => 'PROP-2026-001',
                'property_type_id' => $type->id,
                'listing_type' => 'Sale',
                'status' => 'Available',
                'price' => 450000000,
                'city' => 'Dar es Salaam',
                'address' => 'Victoria Tower, New Bagamoyo Rd',
                'is_published' => true,
                'is_featured' => true,
            ]);
        }
    }

    public function test_homepage_loads_successfully_with_all_strategic_sections(): void
    {
        $response = $this->get(route('public.home'));
        $response->assertStatus(200);
        $response->assertSee('hero-title', false);
        $response->assertSee('Marketplace Categories');
        $response->assertSee('Have a Property to Sell or Rent?');
    }

    public function test_homepage_listings_display_maximum_one_row_each(): void
    {
        $response = $this->get(route('public.home'));
        $response->assertStatus(200);

        // Verify each listing collection is constrained to at most one row
        $response->assertViewHas('featuredProperties', fn ($properties) => $properties->count() <= 3);
        $response->assertViewHas('latestProperties', fn ($properties) => $properties->count() <= 3);
        $response->assertViewHas('featuredProjects', fn ($projects) => $projects->count() <= 3);
        $response->assertViewHas('landOpportunities', fn ($land) => $land->count() <= 4);
        $response->assertViewHas('propertyTypes', fn ($types) => $types->count() <= 4);
        $response->assertViewHas('locations', fn ($locations) => count($locations) <= 3);

        // Verify CTA view all links are present
        $response->assertSee(route('public.properties'));
        $response->assertSee(route('public.projects'));
        $response->assertSee(route('public.land'));
        $response->assertSee(route('public.locations'));
    }

    public function test_properties_discovery_and_search_filters(): void
    {
        // 1. All properties
        $response = $this->get(route('public.properties'));
        $response->assertStatus(200);
        $response->assertSee('Explore Verified Real Estate Properties');

        // 2. City filter (Morogoro)
        $morogoroResponse = $this->get(route('public.properties', ['city' => 'Morogoro']));
        $morogoroResponse->assertStatus(200);
        $morogoroResponse->assertSee('Morogoro');

        // 3. Purpose filter (Sale)
        $saleResponse = $this->get(route('public.buy'));
        $saleResponse->assertStatus(200);
        $saleResponse->assertSee('Properties for Sale');

        // 4. Purpose filter (Rent)
        $rentResponse = $this->get(route('public.rent'));
        $rentResponse->assertStatus(200);
        $rentResponse->assertSee('Properties for Rent');
    }

    public function test_single_property_detail_page_loads_with_cadastral_data(): void
    {
        $property = Property::where('is_published', true)->firstOrFail();

        $response = $this->get(route('public.properties.show', $property));
        $response->assertStatus(200);
        $response->assertSee($property->title);
        $response->assertSee($property->property_code);
        $response->assertSee('Verified Listing');
        $response->assertSee('Property Description');
        $response->assertSee('Chat on WhatsApp');
        $response->assertSee('Book Viewing');
    }

    public function test_land_marketplace_loads(): void
    {
        $response = $this->get(route('public.land'));
        $response->assertStatus(200);
        $response->assertSee('Buy Surveyed Land & Cadastral Plots in Tanzania', false);
        $response->assertSee('GPS Beacon Relocation');
    }

    public function test_projects_index_and_show(): void
    {
        $response = $this->get(route('public.projects'));
        $response->assertStatus(200);
        $response->assertSee('Discover New Developments & Projects', false);

        $project = RealEstateProject::where('is_published', true)->first();
        if (! $project) {
            $project = RealEstateProject::create([
                'title' => 'Victoria Prime Residences',
                'slug' => 'victoria-prime-residences',
                'developer_name' => 'Avenix Developments',
                'city' => 'Dar es Salaam',
                'location_name' => 'Masaki',
                'is_published' => true,
                'is_featured' => true,
                'unit_types_json' => [
                    ['name' => '2 Bedroom Suite', 'size' => '120 sqm', 'price' => '250,000,000 TZS', 'status' => 'Available'],
                ],
            ]);
        }
        $detailResponse = $this->get(route('public.projects.show', $project->slug));
        $detailResponse->assertStatus(200);
        $detailResponse->assertSee($project->title);
        $detailResponse->assertSee($project->developer_name);
        $detailResponse->assertSee('Unit Types & Master Options', false);
    }

    public function test_locations_directory_and_location_landing_page(): void
    {
        $response = $this->get(route('public.locations'));
        $response->assertStatus(200);
        $response->assertSee('Explore Real Estate by Location');

        $morogoroResponse = $this->get(route('public.locations.show', 'morogoro'));
        $morogoroResponse->assertStatus(200);
        $morogoroResponse->assertSee('Properties in Morogoro');
    }

    public function test_land_survey_portal_loads(): void
    {
        $response = $this->get(route('public.services.land_survey'));
        $response->assertStatus(200);
        $response->assertSee('Professional Land Survey & GIS Mapping Services', false);
        $response->assertSee('Boundary Survey & Beaconing', false);
        $response->assertSee('Request a Land Survey or Cadastral Quote');
    }

    public function test_services_index_and_detail(): void
    {
        $response = $this->get(route('public.services'));
        $response->assertStatus(200);
        $response->assertSee('Real Estate & Land Survey Services', false);

        $salesResponse = $this->get(route('public.services.detail', 'property-sales'));
        $salesResponse->assertStatus(200);
        $salesResponse->assertSee('Property Sales');
    }

    public function test_blog_index_and_article_detail(): void
    {
        $response = $this->get(route('public.blog'));
        $response->assertStatus(200);
        $response->assertSee('Real Estate Insights & Buyer Guides', false);

        $article = Article::where('is_published', true)->firstOrFail();
        $articleResponse = $this->get(route('public.blog.show', $article->slug));
        $articleResponse->assertStatus(200);
        $articleResponse->assertSee($article->title);
        $articleResponse->assertSee($article->author_name);
    }

    public function test_about_contact_and_faq_pages_load(): void
    {
        $aboutResponse = $this->get(route('public.about'));
        $aboutResponse->assertStatus(200);
        $aboutResponse->assertSee('About REMS Real Estate Platform', false);
        $contactResponse = $this->get(route('public.contact'));
        $contactResponse->assertStatus(200);
        $contactResponse->assertSee('Contact REMS Platform', false);

        $faqResponse = $this->get(route('public.faq'));
        $faqResponse->assertStatus(200);
        $faqResponse->assertSee('Frequently Asked Questions');

        $favResponse = $this->get(route('public.favorites'));
        $favResponse->assertStatus(200);

        $compareResponse = $this->get(route('public.compare'));
        $compareResponse->assertStatus(200);
    }

    public function test_property_inquiry_submission_creates_customer_and_lead(): void
    {
        $property = Property::where('is_published', true)->firstOrFail();

        $response = $this->post(route('public.inquire'), [
            'property_id' => $property->id,
            'name' => 'Juma Athumani',
            'phone' => '+255 712 345 678',
            'email' => 'juma@testdomain.tz',
            'message' => 'I would like to inquire about purchasing this property.',
            'preferred_contact_method' => 'WhatsApp',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('customers', [
            'phone' => '+255 712 345 678',
            'first_name' => 'Juma',
        ]);

        $this->assertDatabaseHas('leads', [
            'property_interest_id' => $property->id,
            'source' => 'Website Marketplace',
        ]);
    }

    public function test_viewing_booking_creates_appointment_and_lead(): void
    {
        $property = Property::where('is_published', true)->firstOrFail();

        $response = $this->post(route('public.viewing.book'), [
            'property_id' => $property->id,
            'name' => 'Amina Said',
            'phone' => '+255 755 987 654',
            'email' => 'amina@testdomain.tz',
            'preferred_date' => date('Y-m-d', strtotime('+2 days')),
            'preferred_time' => '10:00 AM',
            'message' => 'Attending viewing with architect.',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('appointments', [
            'property_id' => $property->id,
            'meeting_type' => 'Site Viewing',
        ]);

        $this->assertDatabaseHas('leads', [
            'property_interest_id' => $property->id,
            'stage' => 'Viewing',
        ]);
    }

    public function test_request_land_survey_creates_lead(): void
    {
        $response = $this->post(route('public.survey.request'), [
            'name' => 'Baraka Mtenga',
            'phone' => '+255 768 112 233',
            'email' => 'baraka@testdomain.tz',
            'location' => 'Kihonda, Morogoro',
            'survey_type' => 'Boundary Beacon Relocation',
            'approx_land_size' => '2 Acres',
            'preferred_date' => date('Y-m-d', strtotime('+3 days')),
            'description' => 'Need missing corner beacons replaced and verified.',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('customers', [
            'phone' => '+255 768 112 233',
            'first_name' => 'Baraka',
        ]);

        $this->assertDatabaseHas('leads', [
            'source' => 'Land Survey Portal',
        ]);
    }

    public function test_newsletter_subscription(): void
    {
        $response = $this->post(route('public.newsletter.subscribe'), [
            'email' => 'investor@testproperty.co.tz',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('newsletter_subscribers', [
            'email' => 'investor@testproperty.co.tz',
        ]);
    }
}
