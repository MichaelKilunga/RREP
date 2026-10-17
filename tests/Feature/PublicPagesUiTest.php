<?php

namespace Tests\Feature;

use App\Models\Faq;
use App\Models\Property;
use App\Models\RealEstateProject;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * Responsive / Tanzania-context behaviour of the public content and detail pages.
 */
class PublicPagesUiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        SystemSetting::setVal('feature_online_reservations_enabled', '1');
        SystemSetting::setVal('feature_online_bookings_enabled', '1');
    }

    public function test_property_detail_uses_tanzanian_phone_inputs_compact_price_and_mobile_action_bar(): void
    {
        $property = Property::where('is_published', true)->firstOrFail();

        $response = $this->get(route('public.properties.show', $property));

        $response->assertOk();
        $response->assertSee('class="public-shell has-action-bar"', false);
        $response->assertSee('mobile-action-bar', false);
        $response->assertSee('type="tel" id="inquiryPhone" name="phone"', false);
        $response->assertSee('data-tz-phone', false);
        $response->assertSee('<span class="input-group-text">TSh</span>', false);
        $response->assertSee(format_money_short($property->listing_type === 'Rent' ? $property->rent_price : $property->price));
        $response->assertSee('ratio ratio-16x9', false);
        $response->assertSee('btn-whatsapp', false);
        $response->assertSee(route('public.inquire'));
        $response->assertSee(route('public.viewing.book'));
        $response->assertSee(route('public.reservation.reserve'));
    }

    public function test_property_detail_renders_in_swahili(): void
    {
        $property = Property::where('is_published', true)->firstOrFail();

        $response = $this->withSession(['locale' => 'sw'])->get(route('public.properties.show', $property));

        $response->assertOk();
        $response->assertSee('Maelezo ya Jengo');
        $response->assertSee('Weka Miadi');
        $response->assertDontSee('pages.');
    }

    public function test_project_map_page_has_bottom_sheet_drawer_and_fullscreen_inquiry_modal(): void
    {
        $project = RealEstateProject::where('is_published', true)->first() ?: RealEstateProject::create([
            'title' => 'Kigamboni Garden Plots',
            'slug' => 'kigamboni-garden-plots',
            'developer_name' => 'Avenix Developments',
            'city' => 'Dar es Salaam',
            'location_name' => 'Kigamboni',
            'is_published' => true,
        ]);

        $response = $this->get(route('public.projects.show', $project->slug));

        $response->assertOk();
        $response->assertSee('id="plotDrawer"', false);
        $response->assertSee('id="drawerToggle"', false);
        $response->assertSee('modal-fullscreen-sm-down', false);
        $response->assertSee('data-plot-info="'.e($project->title).'"', false);
        $response->assertSee('name="message"', false);
        $response->assertSee('Plot Details');
    }

    public function test_contact_page_offers_click_to_call_whatsapp_region_select_and_office_hours_in_eat(): void
    {
        $response = $this->get(route('public.contact'));

        $response->assertOk();
        $response->assertSee('href="tel:', false);
        $response->assertSee('https://wa.me/', false);
        $response->assertSee('id="contactRegion" name="region"', false);
        $response->assertSee('Dar es Salaam');
        $response->assertSee('Zanzibar Urban/West');
        $response->assertSee('(EAT, UTC+3)');
        $response->assertSee('id="contactOfficeMap"', false);
        $response->assertSee('type="tel" id="contactPhone" name="phone"', false);
    }

    public function test_faq_and_blog_show_empty_states_when_nothing_matches(): void
    {
        $faq = $this->get(route('public.faq', ['q' => 'zzz-no-such-question-zzz']));
        $faq->assertOk();
        $faq->assertSee('empty-state', false);
        $faq->assertSee('id="faqSearch"', false);

        if (Faq::where('is_active', true)->exists()) {
            $this->get(route('public.faq'))->assertSee('accordion accordion-flush', false);
        }

        $blog = $this->get(route('public.blog', ['category' => 'zzz-no-such-category-zzz']));
        $blog->assertOk();
        $blog->assertSee('empty-state', false);
        $blog->assertSee('Real Estate Insights & Buyer Guides', false);
    }

    public function test_every_content_page_renders_in_swahili_without_untranslated_keys(): void
    {
        $this->withSession(['locale' => 'sw']);

        foreach (['public.projects', 'public.services', 'public.services.land_survey', 'public.blog', 'public.about', 'public.contact', 'public.faq', 'public.privacy', 'public.terms', 'public.cookies'] as $routeName) {
            $response = $this->get(route($routeName));

            $this->assertSame(200, $response->status(), "Swahili route [{$routeName}] did not render.");
            $response->assertDontSee('pages.');
        }
    }

    public function test_services_and_legal_pages_render_bilingual_copy(): void
    {
        $services = $this->withSession(['locale' => 'sw'])->get(route('public.services'));
        $services->assertOk();
        $services->assertSee('Huduma Zetu');
        $services->assertSee('modal-fullscreen-sm-down', false);

        $terms = $this->withSession(['locale' => 'sw'])->get(route('public.terms'));
        $terms->assertOk();
        $terms->assertSee('Masharti ya Huduma');
        $terms->assertSee('max-w-850', false);

        $privacy = $this->withSession(['locale' => 'en'])->get(route('public.privacy'));
        $privacy->assertOk();
        $privacy->assertSee('Privacy Policy');
        $privacy->assertDontSee('pages.');
    }
}
