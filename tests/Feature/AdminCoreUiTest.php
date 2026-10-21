<?php

namespace Tests\Feature;

use App\Models\MediaFile;
use App\Models\Property;
use App\Models\User;
use Tests\TestCase;

class AdminCoreUiTest extends TestCase
{
    protected function getAdmin(): User
    {
        return User::where('email', 'admin@rehospace.com')->firstOrFail();
    }

    public function test_dashboard_renders_kpi_cards_and_the_recent_requests_queue(): void
    {
        $response = $this->actingAs($this->getAdmin())->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('class="page-header"', false);
        $response->assertSee('kpi-card', false);
        $response->assertSee('Recent Website Requests');
        $response->assertSee('Plots Available');
        $response->assertSee('TSh', false);
        $response->assertDontSee('SURVEY PROJECTS');
    }

    public function test_properties_index_uses_filter_bar_region_select_and_bulk_bar(): void
    {
        $response = $this->actingAs($this->getAdmin())->get(route('admin.properties.index'));

        $response->assertStatus(200);
        $response->assertSee('Property Inventory');
        $response->assertSee('class="filter-bar p-3"', false);
        $response->assertSee('<option value="Dar es Salaam"', false);
        $response->assertSee('id="bulkActionsBar"', false);
        $response->assertSee('bulk-bar', false);
        $response->assertSee('row-checkbox', false);
    }

    public function test_properties_index_shows_empty_state_when_filters_match_nothing(): void
    {
        $response = $this->actingAs($this->getAdmin())->get(route('admin.properties.index', ['search' => 'zzz-no-such-property-zzz']));

        $response->assertStatus(200);
        $response->assertSee('empty-state', false);
        $response->assertSee('No properties matched your search');
    }

    public function test_property_create_form_groups_fields_and_offers_tanzanian_title_deed_types(): void
    {
        $response = $this->actingAs($this->getAdmin())->get(route('admin.properties.create'));

        $response->assertStatus(200);
        $response->assertSee('Land &amp; Title', false);
        $response->assertSee('Right of Occupancy (CRO)');
        $response->assertSee('Customary Right of Occupancy');
        $response->assertSee('<span class="input-group-text">TSh</span>', false);
        $response->assertSee('inputmode="decimal"', false);
        $response->assertSee('name="city"', false);
        $response->assertSee('id="btnAiDescription"', false);
    }

    public function test_property_show_renders_related_records_and_contact_cards(): void
    {
        $property = Property::query()->firstOrFail();

        $response = $this->actingAs($this->getAdmin())->get(route('admin.properties.show', $property));

        $response->assertStatus(200);
        $response->assertSee($property->property_code);
        $response->assertSee('Sales deals');
        $response->assertSee('Owner / Landlord');
        $response->assertSee('CRM actions');
    }

    public function test_documents_index_offers_tanzanian_document_types_and_empty_state(): void
    {
        MediaFile::query()->delete();

        $response = $this->actingAs($this->getAdmin())->get(route('documents.index'));

        $response->assertStatus(200);
        $response->assertSee('EDMS');
        $response->assertSee('<option value="Title Deed"', false);
        $response->assertSee('<option value="Survey Plan"', false);
        $response->assertSee('<option value="NIDA Copy"', false);
        $response->assertSee('<option value="TIN Certificate"', false);
        $response->assertSee('No documents yet');
    }

    public function test_projects_index_shows_plot_progress_bars(): void
    {
        $response = $this->actingAs($this->getAdmin())->get(route('admin.projects.index'));

        $response->assertStatus(200);
        $response->assertSee('id="bulkProjectsBar"', false);
        $response->assertSee('proj-checkbox', false);
        $response->assertSee('progress-bar', false);
    }

    public function test_staff_pages_render_swahili_copy(): void
    {
        $this->withSession(['locale' => 'sw']);
        $admin = $this->getAdmin();

        $dashboard = $this->actingAs($admin)->get(route('dashboard'));
        $dashboard->assertStatus(200);
        $dashboard->assertSee('Vitendo vya Haraka');

        $documents = $this->actingAs($admin)->get(route('documents.index'));
        $documents->assertStatus(200);
        $documents->assertSee('Hifadhi ya Nyaraka (EDMS)');

        $services = $this->actingAs($admin)->get(route('admin.services.create'));
        $services->assertStatus(200);
        $services->assertSee('Ongeza Huduma');
    }
}
