<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\LandParcel;
use App\Models\PropertyInquiry;
use App\Models\RealEstateProject;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class RequestsPanelTest extends TestCase
{
    private function admin(): User
    {
        return User::where('email', 'admin@rehospace.com')->firstOrFail();
    }

    public function test_plot_request_from_a_project_map_is_typed_and_listed_in_the_requests_panel(): void
    {
        Http::fake();
        $project = RealEstateProject::where('is_published', true)->firstOrFail();

        $this->post(route('public.inquire'), [
            'name' => 'Rehema Saidi',
            'phone' => '0716888999',
            'email' => 'rehema@example.com',
            'message' => 'Nahitaji kiwanja hiki.',
            'project_id' => $project->id,
            'plot_info' => $project->title.' - Plot 23, Block C',
        ])->assertSessionHas('success');

        $inquiry = PropertyInquiry::latest('id')->firstOrFail();
        $this->assertSame('Plot Reservation Request', $inquiry->inquiry_type);
        $this->assertSame('Project Plot Map', $inquiry->source);

        $this->actingAs($this->admin())->get(route('crm.requests'))
            ->assertStatus(200)
            ->assertSee('Rehema Saidi')
            ->assertSee('Plot 23, Block C');

        // Type filter narrows the list.
        $this->actingAs($this->admin())->get(route('crm.requests', ['type' => 'Plot Reservation Request']))
            ->assertSee('Rehema Saidi');
    }

    public function test_land_card_shows_compact_availability_and_drawer_holds_the_details(): void
    {
        $project = RealEstateProject::where('project_category', 'land')->where('is_published', true)->firstOrFail();
        $project->update(['total_units' => 18, 'available_units' => 18, 'sold_plots' => 0, 'initial_payment_percent' => 50]);

        $list = $this->get(route('public.projects'));
        $list->assertSee('plots available');
        $list->assertSee('Deposit 50%');
        $list->assertDontSee('progress rounded-pill mb-3', false); // the old heavy block
        $list->assertDontSee('Siku za Mkopeshano');

        $map = $this->get(route('public.projects.show', $project->slug));
        $map->assertSee('Project summary');
        $map->assertDontSee('attrApplicationFee', false);
        $map->assertDontSee('attrFirstInstallment', false);

        // Application-fee and ownership rows start hidden with no invented default values.
        $map->assertSee('id="attrAppFeeRow" class="d-none"', false);
        $map->assertDontSee('id="attrAppFee">TSh', false);
        $map->assertSee('id="attrTitleDeedRow" class="d-none"', false);

        // The inquiry modal's submit sits in a sticky footer so it cannot scroll out of view,
        // and the mobile-money deposit hint is gone (an inquiry involves no payment).
        $map->assertSee('form="plotInquiryForm"', false);
        $map->assertDontSee('Pay deposits via M-Pesa');

        // Before a plot is selected the drawer is titled "Project Details"
        // (JS swaps it to "Plot Details" on selection) and pulses for attention.
        $map->assertSee('Project Details');
        $map->assertSee('attention-pulse', false);

        // Clients who skip the deposit percentage get no deposit line anywhere.
        $project->update(['initial_payment_percent' => 0]);
        $this->get(route('public.projects'))->assertDontSee('Deposit 0%');
        $this->get(route('public.projects.show', $project->slug))->assertDontSee('Deposit');
    }

    public function test_application_fee_is_editable_per_project_and_feeds_the_plot_drawer(): void
    {
        $project = RealEstateProject::where('project_category', 'land')->where('is_published', true)->firstOrFail();
        LandParcel::create([
            'uuid' => Str::uuid(),
            'real_estate_project_id' => $project->id,
            'parcel_number' => '36',
            'block_number' => 'C',
            'status' => 'Available',
            'acreage' => 0.15,
            'area_sqm' => 600,
            'boundary_coordinates_json' => [[35.59, -6.13], [35.591, -6.13], [35.591, -6.131], [35.59, -6.13]],
        ]);

        $this->actingAs($this->admin())->put(route('admin.projects.update', $project), [
            'title' => $project->title,
            'developer_name' => $project->developer_name,
            'project_type' => $project->project_type,
            'project_status' => $project->project_status,
            'starting_price' => (float) $project->starting_price,
            'location_name' => $project->location_name,
            'city' => $project->city,
            'initial_payment_percent' => 40,
            'application_fee' => 15000,
            'is_published' => '1',
        ])->assertRedirect(route('admin.projects.index'));

        $project->refresh();
        $this->assertSame(15000.0, (float) $project->application_fee);
        $this->assertSame(40.0, (float) $project->initial_payment_percent);

        // The plot payload carries the owner-set fee, no hardcoded 20,000 anywhere.
        $this->get(route('public.projects.show', $project->slug))
            ->assertSee('"application_fee":15000', false)
            ->assertDontSee('"application_fee":20000', false);
    }

    public function test_request_status_can_be_updated(): void
    {
        $inquiry = PropertyInquiry::create([
            'customer_id' => Customer::first()?->id,
            'inquiry_type' => 'Service Quote',
            'name' => 'Status Test',
            'phone' => '0712000111',
            'message' => 'Test',
            'source' => 'Service Quote Form',
            'status' => 'New',
        ]);

        $this->actingAs($this->admin())
            ->post(route('crm.requests.status', $inquiry), ['status' => 'Contacted'])
            ->assertRedirect();

        $this->assertSame('Contacted', $inquiry->fresh()->status);
    }

    public function test_projects_page_hero_is_owner_editable_and_old_search_is_gone(): void
    {
        SystemSetting::setVal('landing_projects_page_title_en', 'Avenix Flagship Projects');
        SystemSetting::setVal('landing_projects_page_badge_2_en', '');

        $this->get(route('public.projects'))
            ->assertSee('Avenix Flagship Projects')
            ->assertDontSee('Title Deed Verified')
            ->assertDontSee('id="projectSearch"', false)
            ->assertSee('col-12 col-sm-6 col-xl-4', false)
            ->assertDontSee('Active Projects');
    }
}
