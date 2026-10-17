<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\PropertyType;
use Tests\TestCase;

class PublicMarketplaceUiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! Property::where('is_published', true)->exists()) {
            $type = PropertyType::first() ?: PropertyType::create([
                'name' => 'Apartment',
                'category' => 'Residential',
                'code' => 'APT',
                'icon' => 'building',
                'slug' => 'apartment',
            ]);

            Property::create([
                'title' => 'Masaki Seaview Apartment',
                'slug' => 'masaki-seaview-apartment',
                'property_code' => 'PROP-2026-UI1',
                'property_type_id' => $type->id,
                'listing_type' => 'Sale',
                'status' => 'Available',
                'price' => 450000000,
                'city' => 'Dar es Salaam',
                'address' => 'Masaki, Kinondoni',
                'is_published' => true,
                'is_featured' => true,
            ]);
        }
    }

    public function test_home_hero_search_uses_tanzanian_regions_and_mobile_scroll_rows(): void
    {
        $response = $this->get(route('public.home'));

        $response->assertStatus(200);
        $response->assertSee('hero-search', false);
        $response->assertSee('name="city"', false);
        $response->assertSee('value="Morogoro"', false);
        $response->assertSee('value="Zanzibar Urban/West"', false);
        $response->assertSee('scroll-x scroll-x-md-grid', false);
        $response->assertSee('data-tz-phone', false);
        $response->assertSee(route('public.locations.show', 'morogoro'));
    }

    public function test_property_card_renders_compact_price_actions_and_whatsapp_button(): void
    {
        $property = Property::where('is_published', true)->firstOrFail();

        $response = $this->get(route('public.properties'));

        $response->assertStatus(200);
        $response->assertSee(format_money_short($property->price));
        $response->assertSee('title="'.e($property->formatted_price).'"', false);
        $response->assertSee('class="favorite-btn" data-property-id="'.$property->id.'"', false);
        $response->assertSee('share-property-btn', false);
        $response->assertSee('compare-toggle-btn', false);
        $response->assertSee('btn-whatsapp', false);
        $response->assertSee('badge-verified', false);
        $response->assertSee('loading="lazy"', false);
    }

    public function test_listing_pages_render_responsive_filter_drawer_and_sort_controls(): void
    {
        $properties = $this->get(route('public.properties'));
        $properties->assertStatus(200);
        $properties->assertSee('offcanvas-lg offcanvas-start', false);
        $properties->assertSee('id="filterOffcanvas"', false);
        $properties->assertSee('id="sortSelect"', false);
        $properties->assertSee('id="toggleMapBtn"', false);

        $land = $this->get(route('public.land'));
        $land->assertStatus(200);
        $land->assertSee('Buy Surveyed Land & Cadastral Plots in Tanzania', false);
        $land->assertSee('id="filterOffcanvas"', false);
        $land->assertSee('value="Dodoma"', false);

        $location = $this->get(route('public.locations.show', 'morogoro'));
        $location->assertStatus(200);
        $location->assertSee('Properties in Morogoro');
        $location->assertSee('sticky-lg-top', false);
    }

    public function test_empty_states_render_with_a_call_to_action(): void
    {
        $response = $this->get(route('public.properties', ['q' => 'no-such-listing-xyz-123']));

        $response->assertStatus(200);
        $response->assertSee('empty-state', false);
        $response->assertSee('Reset Filters');

        $favorites = $this->get(route('public.favorites'));
        $favorites->assertStatus(200);
        $favorites->assertSee('No saved properties yet');

        $compare = $this->get(route('public.compare'));
        $compare->assertStatus(200);
        $compare->assertSee('No properties selected to compare');
    }

    public function test_compare_table_pins_the_feature_column_on_phones(): void
    {
        $property = Property::where('is_published', true)->firstOrFail();

        $response = $this->get(route('public.compare', ['ids' => $property->id]));

        $response->assertStatus(200);
        $response->assertSee('compare-table', false);
        $response->assertSee('position-sticky start-0', false);
        $response->assertSee($property->title);
        $response->assertSee('compare-remove-btn', false);
    }

    public function test_marketplace_copy_renders_in_swahili(): void
    {
        $this->withSession(['locale' => 'sw']);

        $home = $this->get(route('public.home'));
        $home->assertStatus(200);
        $home->assertSee('Matangazo Mapya');
        $this->assertDoesNotMatchRegularExpression('/marketplace\.[a-z_]+/', $home->getContent(), 'Untranslated marketplace keys leaked into the Swahili home page.');

        $land = $this->get(route('public.land'));
        $land->assertStatus(200);
        $land->assertSee('Nunua Ardhi na Viwanja Vilivyopimwa Tanzania');

        $favorites = $this->get(route('public.favorites'));
        $favorites->assertSee('Hujahifadhi mali yoyote bado');
    }
}
