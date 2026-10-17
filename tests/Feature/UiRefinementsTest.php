<?php

namespace Tests\Feature;

use App\Models\CompanyService;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Support\Str;
use Tests\TestCase;

class UiRefinementsTest extends TestCase
{
    public function test_mobile_drawer_mirrors_the_desktop_navigation(): void
    {
        $response = $this->get(route('public.home'));
        $response->assertStatus(200);

        // The stale drawer entries are gone.
        $response->assertDontSee('Browse All Locations');
        $response->assertDontSee('All Property Services');
        $response->assertDontSee('Insights & Buyer Guides');
        $response->assertDontSee('About Platform');
        $response->assertDontSee('Contact Offices');

        // The drawer carries the same five links as the desktop navbar plus the language switch.
        $html = $response->getContent();
        $drawerStart = strpos($html, 'id="mobileOffcanvas"');
        $drawerEnd = strpos($html, 'Main Content Injection', $drawerStart);
        $this->assertNotFalse($drawerStart);
        $this->assertNotFalse($drawerEnd);
        $drawer = substr($html, $drawerStart, $drawerEnd - $drawerStart);

        foreach (['Home', 'About Us', 'Services', 'Projects', 'Contact Us', 'Kiswahili', 'English'] as $label) {
            $this->assertStringContainsString($label, $drawer, "Drawer is missing [{$label}].");
        }
        $this->assertStringContainsString(route('lang.switch', 'sw'), $drawer);
        $this->assertStringContainsString(route('public.about'), $drawer);
    }

    public function test_landing_listing_sections_hide_when_marketplace_module_is_off(): void
    {
        $this->get(route('public.home'))
            ->assertSee('id="hero-search"', false)
            ->assertSee('id="quick-browse"', false)
            ->assertSee('id="featured-properties"', false)
            ->assertSee('id="latest-listings"', false)
            ->assertSee('id="land-opportunities"', false);

        SystemSetting::setVal('feature_marketplace_functions_enabled', '0');

        $this->get(route('public.home'))
            ->assertStatus(200)
            ->assertDontSee('id="hero-search"', false)
            ->assertDontSee('id="quick-browse"', false)
            ->assertDontSee('id="featured-properties"', false)
            ->assertDontSee('id="latest-listings"', false)
            ->assertDontSee('id="land-opportunities"', false);
    }

    public function test_hero_search_respects_its_own_switch(): void
    {
        SystemSetting::setVal('landing_search_enabled', '0');

        $this->get(route('public.home'))
            ->assertDontSee('id="hero-search"', false)
            ->assertSee('id="quick-browse"', false)
            ->assertSee('id="latest-listings"', false);
    }

    public function test_landing_listing_sections_respect_their_individual_switches(): void
    {
        SystemSetting::setVal('landing_featured_enabled', '0');
        SystemSetting::setVal('landing_land_enabled', '0');

        $this->get(route('public.home'))
            ->assertDontSee('id="featured-properties"', false)
            ->assertDontSee('id="land-opportunities"', false)
            ->assertSee('id="latest-listings"', false);
    }

    public function test_settings_expose_the_landing_section_visibility_toggles(): void
    {
        $admin = User::where('email', 'admin@rehospace.com')->firstOrFail();

        $this->actingAs($admin)->get(route('settings.index'))
            ->assertSee('landing_search_enabled', false)
            ->assertSee('landing_categories_enabled', false)
            ->assertSee('landing_featured_enabled', false)
            ->assertSee('landing_latest_enabled', false)
            ->assertSee('landing_land_enabled', false);
    }

    public function test_page_heroes_carry_scene_backgrounds(): void
    {
        $this->get(route('public.about'))->assertSee('hero-img-survey', false);
        $this->get(route('public.properties'))->assertSee('hero-img-plot', false);
        $this->get(route('public.home'))->assertSee('hero-dodoma-plot.jpg', false);
    }

    public function test_staff_create_forms_distribute_evenly_across_the_width(): void
    {
        $admin = User::where('email', 'admin@rehospace.com')->firstOrFail();

        $this->actingAs($admin)->get(route('admin.projects.create'))
            ->assertDontSee('max-w-4xl')
            ->assertSee('col-xl-8', false)
            ->assertSee('col-xl-4', false);

        $this->actingAs($admin)->get(route('admin.services.create'))
            ->assertDontSee('max-w-3xl')
            ->assertSee('col-xl-8', false);

        $service = CompanyService::first() ?: CompanyService::create([
            'uuid' => Str::uuid(),
            'title' => 'Cadastral Survey Service',
            'slug' => 'cadastral-survey-service',
            'category' => 'Surveying',
            'short_description' => 'Boundary and beacon services.',
            'icon' => 'bi-map',
            'display_order' => 1,
            'is_active' => true,
        ]);
        $this->actingAs($admin)->get(route('admin.services.edit', $service))
            ->assertDontSee('max-w-3xl')
            ->assertSee('col-xl-8', false);
    }

    public function test_staff_console_scales_up_while_public_site_does_not(): void
    {
        $admin = User::where('email', 'admin@rehospace.com')->firstOrFail();

        $this->actingAs($admin)->get(route('dashboard'))
            ->assertSee('class="app-html"', false);

        $this->get(route('public.home'))
            ->assertDontSee('app-html', false);

        $this->get(route('login'))
            ->assertDontSee('app-html', false);
    }
}
