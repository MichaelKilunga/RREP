<?php

namespace Tests\Feature;

use App\Models\AppNotification;
use App\Models\CompanyService;
use App\Models\Lead;
use App\Models\PropertyInquiry;
use App\Models\RealEstateProject;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Http\Testing\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class ClientMeetingUpdatesTest extends TestCase
{
    private function admin(): User
    {
        return User::where('email', 'admin@rehospace.com')->firstOrFail();
    }

    private function service(): CompanyService
    {
        return CompanyService::first() ?: CompanyService::create([
            'uuid' => Str::uuid(),
            'title' => 'Town Planning',
            'slug' => 'town-planning',
            'category' => 'Planning',
            'short_description' => 'Layouts and TP drawings.',
            'icon' => 'bi-buildings',
            'display_order' => 1,
            'is_active' => true,
        ]);
    }

    public function test_service_project_with_gallery_is_created_and_listed_publicly(): void
    {
        Storage::fake('public');
        $service = $this->service();

        $this->actingAs($this->admin())->post(route('admin.projects.store'), [
            'project_category' => 'service',
            'title' => 'Dodoma Market Construction Supervision',
            'developer_name' => 'Avenix',
            'project_type' => 'Master-Planned Land',
            'project_status' => 'Ongoing',
            'company_service_id' => $service->id,
            'client_name' => 'Halmashauri ya Dodoma',
            'city' => 'Dodoma',
            'is_published' => '1',
            'gallery_files' => [File::image('site-a.jpg', 1200, 800), File::image('site-b.jpg', 1200, 800)],
        ])->assertRedirect(route('admin.projects.index'));

        $project = RealEstateProject::where('slug', 'dodoma-market-construction-supervision')->firstOrFail();
        $this->assertSame('service', $project->project_category);
        $this->assertCount(2, $project->gallery);
        $this->assertSame($service->id, $project->company_service_id);

        $this->get(route('public.projects', ['category' => 'service']))
            ->assertStatus(200)
            ->assertSee('View Project')
            ->assertSee('Halmashauri ya Dodoma')
            ->assertSee($service->title);

        $this->get(route('public.projects.show', $project->slug))
            ->assertStatus(200)
            ->assertSee('Halmashauri ya Dodoma')
            ->assertDontSee('id="projectPlotMap"', false);
    }

    public function test_land_project_page_keeps_the_plot_map_with_renamed_ownership_label(): void
    {
        $project = RealEstateProject::where('project_category', 'land')->where('is_published', true)->firstOrFail();

        $this->get(route('public.projects.show', $project->slug))
            ->assertStatus(200)
            ->assertSee('id="projectPlotMap"', false)
            ->assertSee('Ownership Status')
            ->assertDontSee('copyPlotGeoJson');

        $this->get(route('public.projects'))
            ->assertSee('Land Projects & Interactive Plot Maps')
            ->assertSee('All Projects')
            ->assertSee('Other Projects');
    }

    public function test_bottom_nav_offers_services_instead_of_land_survey(): void
    {
        $html = $this->get(route('public.home'))->getContent();
        $navStart = strpos($html, 'public-bottom-nav');
        $nav = substr($html, $navStart, strpos($html, '</nav>', $navStart) - $navStart);

        $this->assertStringContainsString(route('public.services'), $nav);
        $this->assertStringNotContainsString(route('public.services.land_survey'), $nav);
    }

    public function test_admin_project_list_filters_by_category_and_search(): void
    {
        $service = $this->service();
        RealEstateProject::create([
            'uuid' => Str::uuid(), 'title' => 'Mnadani Survey Contract', 'slug' => 'mnadani-survey-contract',
            'developer_name' => 'Avenix', 'project_type' => 'Master-Planned Land', 'project_category' => 'service',
            'company_service_id' => $service->id, 'client_name' => 'Greton Ltd', 'project_status' => 'Completed',
            'starting_price' => 0, 'currency' => 'TZS', 'location_name' => 'Mnadani', 'city' => 'Dodoma',
            'is_published' => true,
        ]);
        $land = RealEstateProject::where('project_category', 'land')->firstOrFail();

        $this->actingAs($this->admin())
            ->get(route('admin.projects.index', ['category' => 'service', 'q' => 'Mnadani']))
            ->assertStatus(200)
            ->assertSee('Mnadani Survey Contract')
            ->assertDontSee($land->title);
    }

    public function test_hero_slideshow_renders_when_extra_images_are_configured(): void
    {
        SystemSetting::setVal('landing_hero_image_2', '/images/hero-survey-team.jpg');

        $html = $this->get(route('public.home'))->getContent();
        $this->assertStringContainsString('hero-slides', $html);
        $this->assertGreaterThanOrEqual(2, substr_count($html, 'hero-slide '));
    }

    public function test_service_thumbnail_and_editable_cta_render_on_the_services_page(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin())->post(route('admin.services.store'), [
            'title' => 'Beacon Replacement',
            'category' => 'Surveying',
            'short_description' => 'Replace lost plot beacons.',
            'icon' => 'bi-geo',
            'display_order' => 9,
            'is_active' => '1',
            'cta_text' => 'Omba Bei',
            'cta_url' => '/contact',
            'thumbnail_file' => File::image('beacon.jpg', 800, 450),
        ])->assertRedirect(route('admin.services.index'));

        $service = CompanyService::where('slug', 'beacon-replacement')->firstOrFail();
        $this->assertStringStartsWith('/storage/services/', $service->thumbnail);

        $this->get(route('public.services'))
            ->assertSee('Omba Bei')
            ->assertSee($service->thumbnail, false);
    }

    public function test_project_create_form_drops_grace_days_and_gains_amenities_and_type_switch(): void
    {
        $this->actingAs($this->admin())->get(route('admin.projects.create'))
            ->assertStatus(200)
            ->assertDontSee('grace_period_days')
            ->assertSee('name="amenities"', false)
            ->assertSee('name="project_category"', false)
            ->assertSee('gallery_files[]', false);
    }

    public function test_service_quote_request_notifies_staff_and_is_traceable(): void
    {
        Http::fake();

        $this->post(route('public.inquire'), [
            'name' => 'Neema Mushi',
            'phone' => '0713222333',
            'email' => 'neema@example.com',
            'message' => 'Nahitaji makadirio ya gharama ya upimaji.',
            'service_name' => 'Land Surveying & Mapping',
        ])->assertSessionHas('success');

        $lead = Lead::latest('id')->firstOrFail();
        $this->assertSame('Service Quote: Land Surveying & Mapping', $lead->title);
        $this->assertSame('Service Quote Request', $lead->source);

        $inquiry = PropertyInquiry::latest('id')->firstOrFail();
        $this->assertSame('Service Quote', $inquiry->inquiry_type);
        $this->assertStringContainsString('Land Surveying', $inquiry->message);

        $note = AppNotification::where('type', 'inquiry')->latest('id')->firstOrFail();
        $this->assertStringContainsString('Land Surveying & Mapping', $note->title.$note->body);
    }

    public function test_services_page_hero_is_owner_editable(): void
    {
        $this->service();
        SystemSetting::setVal('landing_services_page_title_en', 'What Avenix Does');
        SystemSetting::setVal('landing_services_page_subtitle_en', '');
        SystemSetting::setVal('landing_services_cta_default_en', 'Omba Bei Sasa');

        $this->get(route('public.services'))
            ->assertSee('What Avenix Does')
            ->assertSee('Omba Bei Sasa');
    }

    public function test_home_projects_row_scrolls_on_phones(): void
    {
        $html = $this->get(route('public.home'))->getContent();
        $this->assertStringContainsString('scroll-x-md-grid', $html);
        $section = substr($html, strpos($html, 'LAND PROJECTS'));
        $this->assertStringContainsString('col-11 col-sm-9 col-md-8 col-lg-6', $section);
    }

    public function test_leads_cards_show_the_recorded_time(): void
    {
        $this->actingAs($this->admin())->get(route('crm.leads'))
            ->assertStatus(200)
            ->assertSee('bi-clock', false);
    }
}
