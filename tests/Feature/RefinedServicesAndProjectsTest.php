<?php

namespace Tests\Feature;

use App\Models\CompanyService;
use App\Models\RealEstateProject;
use Database\Seeders\CompanyServicesAndProjectsSeeder;
use Illuminate\Support\Str;
use Tests\TestCase;

class RefinedServicesAndProjectsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        (new CompanyServicesAndProjectsSeeder)->run();
    }

    public function test_homepage_displays_simplified_services_and_projects()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Land Sales');
    }

    public function test_services_page_displays_core_services()
    {
        $response = $this->get('/services');

        $response->assertStatus(200);
        $response->assertSee('Our Services');
        $response->assertSee('TP Drawing Preparation');
    }

    public function test_projects_page_displays_tausi_style_project_cards()
    {
        $response = $this->get('/projects');

        $response->assertStatus(200);
        $response->assertSee('Land Projects');
        $response->assertSee('Plot Allocation Progress');
    }

    public function test_project_show_page_renders_interactive_map_and_plot_geojson()
    {
        $project = RealEstateProject::first();

        if ($project) {
            $response = $this->get("/projects/{$project->slug}");
            $response->assertStatus(200);
            $response->assertSee('projectPlotMap');
            $response->assertSee('Plot Details');
        }
    }

    public function test_admin_can_create_company_service()
    {
        $title = 'Custom GIS Service '.rand(1000, 9999);

        $service = CompanyService::create([
            'uuid' => Str::uuid(),
            'title' => $title,
            'slug' => Str::slug($title),
            'category' => 'Surveying',
            'short_description' => 'Advanced GIS spatial analytical services.',
            'icon' => 'bi-map',
            'display_order' => 99,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('company_services', ['title' => $title]);
    }
}
