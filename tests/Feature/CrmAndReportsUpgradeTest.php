<?php

namespace Tests\Feature;

use App\Models\LandParcel;
use App\Models\Lead;
use App\Models\RealEstateProject;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * CRM lead management controls, module-aware reports center and the
 * price label that only says "starting" when plot prices actually vary.
 */
class CrmAndReportsUpgradeTest extends TestCase
{
    private function admin(): User
    {
        return User::where('email', 'admin@rehospace.com')->firstOrFail();
    }

    public function test_leads_can_be_edited_deleted_and_bulk_managed(): void
    {
        $leads = Lead::take(2)->get();
        $this->assertGreaterThanOrEqual(2, $leads->count());
        [$a, $b] = [$leads[0], $leads[1]];

        // Edit
        $this->actingAs($this->admin())->put(route('crm.leads.update', $a), [
            'title' => 'Updated lead headline',
            'source' => 'WhatsApp',
            'stage' => 'Qualified',
            'priority' => 'High',
            'estimated_value' => 12000000,
        ])->assertRedirect()->assertSessionHas('success');
        $a->refresh();
        $this->assertSame('Updated lead headline', $a->title);
        $this->assertSame('Qualified', $a->stage);

        // Bulk move
        $this->actingAs($this->admin())->post(route('crm.leads.bulk'), [
            'action' => 'move',
            'stage' => 'Contacted',
            'ids' => [$a->id, $b->id],
        ])->assertRedirect()->assertSessionHas('success');
        $this->assertSame('Contacted', $a->fresh()->stage);
        $this->assertSame('Contacted', $b->fresh()->stage);

        // Single delete + bulk delete
        $this->actingAs($this->admin())->delete(route('crm.leads.destroy', $a))->assertRedirect();
        $this->assertNull(Lead::find($a->id));

        $this->actingAs($this->admin())->post(route('crm.leads.bulk'), [
            'action' => 'delete',
            'ids' => [$b->id],
        ])->assertRedirect();
        $this->assertNull(Lead::find($b->id));

        // The page carries the controls: checkboxes, bulk bar, edit modal.
        $page = $this->actingAs($this->admin())->get(route('crm.leads'));
        $page->assertSee('bulkLeadsBar', false);
        $page->assertSee('lead-check', false);
    }

    public function test_reports_center_is_module_aware_and_projects_requests_reports_render(): void
    {
        $index = $this->actingAs($this->admin())->get(route('reports.index'));
        $index->assertSee(route('reports.projects'), false);
        $index->assertSee(route('reports.requests'), false);
        $index->assertSee(route('reports.leads'), false);
        $index->assertDontSee(route('reports.sales'), false);
        $index->assertDontSee(route('reports.properties'), false);
        $index->assertDontSee(route('reports.survey'), false);

        SystemSetting::setVal('feature_deals_module_enabled', '1');
        SystemSetting::setVal('feature_properties_module_enabled', '1');
        SystemSetting::setVal('feature_land_survey_module_enabled', '1');
        $index = $this->actingAs($this->admin())->get(route('reports.index'));
        $index->assertSee(route('reports.sales'), false);
        $index->assertSee(route('reports.properties'), false);
        $index->assertSee(route('reports.survey'), false);

        $this->actingAs($this->admin())->get(route('reports.projects'))
            ->assertStatus(200)
            ->assertSee('Active Plot Holds');
        $this->actingAs($this->admin())->get(route('reports.requests'))
            ->assertStatus(200)
            ->assertSee('Resolution Rate');

        // CSV exports stream.
        $this->actingAs($this->admin())->get(route('reports.projects', ['export' => 'csv']))
            ->assertStatus(200)
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_deals_panel_is_hidden_until_its_module_is_toggled_on(): void
    {
        $dash = $this->actingAs($this->admin())->get(route('dashboard'));
        $this->assertStringNotContainsString(route('deals.index'), $dash->getContent());

        SystemSetting::setVal('feature_deals_module_enabled', '1');
        $dash = $this->actingAs($this->admin())->get(route('dashboard'));
        $this->assertStringContainsString(route('deals.index'), $dash->getContent());
    }

    public function test_home_lists_projects_before_services_and_pinned_project_first(): void
    {
        $html = $this->get(route('public.home'))->getContent();
        $projectsPos = strpos($html, 'LAND PROJECTS SHOWCASE');
        $servicesPos = strpos($html, '<!-- 7. SERVICES -->');
        $this->assertNotFalse($projectsPos);
        $this->assertNotFalse($servicesPos);
        $this->assertLessThan($servicesPos, $projectsPos, 'Projects section must come before Services');

        // Pin the OLDEST published project; it must jump to the first card.
        $pinned = RealEstateProject::where('is_published', true)->oldest()->firstOrFail();
        $this->actingAs($this->admin())->post(route('admin.projects.pin', $pinned))->assertRedirect();
        $this->assertTrue($pinned->fresh()->is_pinned);

        $html = $this->get(route('public.projects'))->getContent();
        $firstCard = strpos($html, $pinned->title);
        foreach (RealEstateProject::where('is_published', true)->where('id', '!=', $pinned->id)->pluck('title') as $other) {
            $pos = strpos($html, $other);
            if ($pos !== false) {
                $this->assertLessThan($pos, $firstCard, 'Pinned project must list first');
            }
        }

        // Pinning another project moves the pin (only one at a time).
        $second = RealEstateProject::where('is_published', true)->where('id', '!=', $pinned->id)->firstOrFail();
        $this->actingAs($this->admin())->post(route('admin.projects.pin', $second));
        $this->assertFalse($pinned->fresh()->is_pinned);
        $this->assertTrue($second->fresh()->is_pinned);
    }

    public function test_starting_price_is_optional_on_project_forms(): void
    {
        $project = RealEstateProject::where('project_category', 'land')->where('is_published', true)->firstOrFail();
        $before = (float) $project->starting_price;

        // Updating without a starting price keeps the existing one.
        $this->actingAs($this->admin())->put(route('admin.projects.update', $project), [
            'title' => $project->title,
            'developer_name' => $project->developer_name,
            'project_type' => $project->project_type,
            'project_status' => $project->project_status,
            'location_name' => $project->location_name,
            'city' => $project->city,
            'is_published' => '1',
        ])->assertRedirect(route('admin.projects.index'))->assertSessionHasNoErrors();
        $this->assertSame($before, (float) $project->fresh()->starting_price);

        // Creating a land project without one works too (price comes later from the rate).
        $this->actingAs($this->admin())->post(route('admin.projects.store'), [
            'project_category' => 'land',
            'title' => 'Rate Priced Estate',
            'developer_name' => 'Avenix',
            'project_type' => 'Master-Planned Land',
            'project_status' => 'Selling',
            'location_name' => 'Nala',
            'city' => 'Dodoma',
            'price_per_sqm' => 12000,
        ])->assertRedirect(route('admin.projects.index'))->assertSessionHasNoErrors();
        $this->assertSame(0.0, (float) RealEstateProject::where('slug', 'rate-priced-estate')->firstOrFail()->starting_price);
    }

    public function test_price_per_sqm_computes_each_plot_price_from_its_area(): void
    {
        $project = RealEstateProject::where('project_category', 'land')->where('is_published', true)->firstOrFail();

        $small = LandParcel::create([
            'uuid' => Str::uuid(), 'real_estate_project_id' => $project->id, 'parcel_number' => '21',
            'block_number' => 'A', 'status' => 'Available', 'acreage' => 0.1, 'area_sqm' => 400, 'price' => 0,
            'boundary_coordinates_json' => [[35.59, -6.13], [35.591, -6.13], [35.591, -6.131], [35.59, -6.13]],
        ]);
        $large = LandParcel::create([
            'uuid' => Str::uuid(), 'real_estate_project_id' => $project->id, 'parcel_number' => '22',
            'block_number' => 'A', 'status' => 'Available', 'acreage' => 0.2, 'area_sqm' => 900, 'price' => 0,
            'boundary_coordinates_json' => [[35.6, -6.14], [35.601, -6.14], [35.601, -6.141], [35.6, -6.14]],
        ]);

        $this->actingAs($this->admin())->put(route('admin.projects.update', $project), [
            'title' => $project->title,
            'developer_name' => $project->developer_name,
            'project_type' => $project->project_type,
            'project_status' => $project->project_status,
            'starting_price' => (float) $project->starting_price,
            'location_name' => $project->location_name,
            'city' => $project->city,
            'price_per_sqm' => 15000,
            'is_published' => '1',
        ])->assertRedirect(route('admin.projects.index'));

        // Each plot's price = its area x the rate; the project price is the cheapest plot.
        $this->assertSame(400 * 15000.0, (float) $small->fresh()->price);
        $this->assertSame(900 * 15000.0, (float) $large->fresh()->price);
        $this->assertSame(400 * 15000.0, (float) $project->fresh()->starting_price);

        // The public map hands each plot its own computed price.
        $this->get(route('public.projects.show', $project->slug))
            ->assertSee('"price":6000000', false)
            ->assertSee('"price":13500000', false);
    }

    public function test_price_label_says_starting_only_when_plot_prices_vary(): void
    {
        $project = RealEstateProject::where('project_category', 'land')->where('is_published', true)->firstOrFail();

        // Single uniform price: the card says just "Price".
        $this->get(route('public.projects'))
            ->assertSee('Price')
            ->assertDontSee('Starting price');

        foreach ([['11', 5000000], ['12', 8000000]] as [$no, $price]) {
            LandParcel::create([
                'uuid' => Str::uuid(), 'real_estate_project_id' => $project->id, 'parcel_number' => $no,
                'block_number' => 'A', 'status' => 'Available', 'acreage' => 0.15, 'area_sqm' => 600, 'price' => $price,
            ]);
        }

        $this->get(route('public.projects'))->assertSee('Starting price');
    }
}
