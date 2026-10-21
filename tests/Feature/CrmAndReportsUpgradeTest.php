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
