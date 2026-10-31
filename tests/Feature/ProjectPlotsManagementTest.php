<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\LandParcel;
use App\Models\RealEstateProject;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Admin project page: plot inventory, map, plot CRUD, bulk actions, export,
 * reservation consistency, duplicate, and shapefile re-upload safety.
 */
class ProjectPlotsManagementTest extends TestCase
{
    private function admin(): User
    {
        return User::where('email', 'admin@rehospace.com')->firstOrFail();
    }

    private function landProject(): RealEstateProject
    {
        return RealEstateProject::where('project_category', 'land')->firstOrFail();
    }

    private function plot(RealEstateProject $project, string $number, array $overrides = []): LandParcel
    {
        return LandParcel::create(array_merge([
            'uuid' => Str::uuid(), 'real_estate_project_id' => $project->id, 'parcel_number' => $number,
            'block_number' => 'A', 'status' => 'Available', 'acreage' => 0.15, 'area_sqm' => 500, 'price' => 5000000,
            'boundary_coordinates_json' => [[35.59, -6.13], [35.591, -6.13], [35.591, -6.131], [35.59, -6.13]],
        ], $overrides));
    }

    public function test_project_page_shows_kpis_inventory_and_map_data(): void
    {
        $project = $this->landProject();
        $this->plot($project, '901');
        $this->plot($project, '902', ['status' => 'Sold']);

        $this->actingAs($this->admin())->get(route('admin.projects.show', $project))
            ->assertOk()
            ->assertSee('Plot inventory')
            ->assertSee('Plot 901')
            ->assertSee('id="adminPlotMap"', false)
            ->assertSee('FeatureCollection', false)
            ->assertSee(route('admin.projects.plots.export', $project), false);
    }

    public function test_plots_can_be_added_priced_from_rate_edited_and_kept_unique(): void
    {
        $project = $this->landProject();
        $project->update(['price_per_sqm' => 10000]);

        $this->actingAs($this->admin())->post(route('admin.projects.plots.store', $project), [
            'parcel_number' => '77', 'block_number' => 'Z', 'area_sqm' => 650, 'status' => 'Available',
        ])->assertSessionHas('success');

        $plot = $project->parcels()->where('parcel_number', '77')->firstOrFail();
        $this->assertSame(6500000.0, (float) $plot->price);

        // Same number in the same block is rejected.
        $this->actingAs($this->admin())->post(route('admin.projects.plots.store', $project), [
            'parcel_number' => '77', 'block_number' => 'Z', 'status' => 'Available',
        ])->assertSessionHasErrors('parcel_number');

        $this->actingAs($this->admin())->put(route('admin.projects.plots.update', [$project, $plot]), [
            'parcel_number' => '77', 'block_number' => 'Z', 'area_sqm' => 650, 'price' => 7000000, 'status' => 'Reserved', 'land_use' => 'Biashara',
        ])->assertSessionHas('success');

        $plot->refresh();
        $this->assertSame(7000000.0, (float) $plot->price);
        $this->assertSame('Reserved', $plot->status);
        $this->assertSame('Biashara', $plot->land_use);
    }

    public function test_status_changes_keep_reservations_and_counters_consistent(): void
    {
        $project = $this->landProject();
        $plot = $this->plot($project, '950', ['status' => 'Reserved']);
        $hold = Reservation::create([
            'land_parcel_id' => $plot->id, 'customer_id' => Customer::firstOrFail()->id, 'reservation_number' => 'RESV-TEST-1',
            'reservation_fee' => 1000, 'deposit_paid' => 1000, 'reserved_from' => now(), 'reserved_until' => now()->addDays(14), 'status' => 'Active',
        ]);

        $this->actingAs($this->admin())
            ->postJson(route('admin.projects.plots.status', [$project, $plot]), ['status' => 'Sold'])
            ->assertOk()
            ->assertJsonStructure(['message', 'counts' => ['total', 'Available', 'Reserved', 'Sold']]);

        $this->assertSame('Converted', $hold->fresh()->status);
        $this->assertSame($project->parcels()->where('status', 'Sold')->count(), (int) $project->fresh()->sold_plots);

        $hold->refresh()->update(['status' => 'Active']);
        $this->actingAs($this->admin())->postJson(route('admin.projects.plots.status', [$project, $plot]), ['status' => 'Available'])->assertOk();
        $this->assertSame('Cancelled', $hold->fresh()->status);
    }

    public function test_bulk_status_price_rate_and_delete(): void
    {
        $project = $this->landProject();
        $a = $this->plot($project, '961', ['area_sqm' => 400]);
        $b = $this->plot($project, '962', ['area_sqm' => 800]);
        $route = route('admin.projects.plots.bulk', $project);

        $this->actingAs($this->admin())->post($route, ['action' => 'status', 'status' => 'Sold', 'ids' => [$a->id, $b->id]])->assertSessionHas('success');
        $this->assertSame('Sold', $a->fresh()->status);

        $this->actingAs($this->admin())->post($route, ['action' => 'price', 'price' => 3000000, 'ids' => [$a->id]]);
        $this->assertSame(3000000.0, (float) $a->fresh()->price);

        $this->actingAs($this->admin())->post($route, ['action' => 'rate', 'rate' => 12000, 'ids' => [$a->id, $b->id]]);
        $this->assertSame(4800000.0, (float) $a->fresh()->price);
        $this->assertSame(9600000.0, (float) $b->fresh()->price);

        $this->actingAs($this->admin())->post($route, ['action' => 'delete', 'ids' => [$b->id]]);
        $this->assertSoftDeleted($b);
    }

    public function test_plots_export_as_csv(): void
    {
        $project = $this->landProject();
        $this->plot($project, '971');

        $response = $this->actingAs($this->admin())->get(route('admin.projects.plots.export', $project));
        $response->assertOk();
        $this->assertStringContainsString('971', $response->streamedContent());
    }

    public function test_reuploading_a_shapefile_keeps_plot_status_price_and_ids(): void
    {
        Storage::fake('public');
        $zip = fn () => new UploadedFile(base_path('tests/fixtures/minara-shapefile.zip'), 'minara.zip', 'application/zip', null, true);

        $this->actingAs($this->admin())->post(route('admin.projects.store'), [
            'title' => 'Minara Resync', 'developer_name' => 'Avenix', 'project_type' => 'Master-Planned Land', 'project_status' => 'Selling',
            'starting_price' => 500000, 'location_name' => 'Chihoni Nala', 'city' => 'Dodoma', 'shapefile' => $zip(),
        ]);
        $project = RealEstateProject::where('slug', 'minara-resync')->firstOrFail();
        $sold = $project->parcels()->first();
        $sold->update(['status' => 'Sold', 'price' => 12345678]);

        $this->actingAs($this->admin())->put(route('admin.projects.update', $project), [
            'title' => 'Minara Resync', 'developer_name' => 'Avenix', 'project_type' => 'Master-Planned Land', 'project_status' => 'Selling',
            'location_name' => 'Chihoni Nala', 'city' => 'Dodoma', 'shapefile' => $zip(),
        ])->assertRedirect();

        $this->assertSame(18, $project->parcels()->count());
        $again = LandParcel::find($sold->id);
        $this->assertNotNull($again, 'Re-upload must not recreate existing plots');
        $this->assertSame('Sold', $again->status);
        $this->assertSame(12345678.0, (float) $again->price);
        $this->assertSame(1, (int) $project->fresh()->sold_plots);
    }

    public function test_duplicate_creates_an_unpublished_copy_without_plots(): void
    {
        $project = $this->landProject();
        $this->actingAs($this->admin())->post(route('admin.projects.duplicate', $project))->assertRedirect();

        $copy = RealEstateProject::where('title', $project->title.' (copy)')->firstOrFail();
        $this->assertFalse($copy->is_published);
        $this->assertSame(0, $copy->parcels()->count());
        $this->assertNotSame($project->slug, $copy->slug);
    }

    public function test_viewers_see_the_project_but_cannot_change_plots(): void
    {
        $agent = User::where('email', 'agent@rehospace.com')->firstOrFail();
        $project = $this->landProject();
        $plot = $this->plot($project, '981');

        $this->actingAs($agent)->get(route('admin.projects.show', $project))
            ->assertOk()
            ->assertSee('Plot 981')
            ->assertDontSee('<select class="form-select form-select-sm w-auto plot-status-select', false)
            ->assertDontSee('id="addPlotBtn"', false);
        $this->actingAs($agent)->postJson(route('admin.projects.plots.status', [$project, $plot]), ['status' => 'Sold'])->assertForbidden();
    }

    public function test_service_projects_show_their_details(): void
    {
        $service = RealEstateProject::create([
            'uuid' => Str::uuid(), 'title' => 'Dodoma Market Supervision', 'slug' => 'dodoma-market-supervision',
            'developer_name' => 'Avenix', 'project_type' => 'Master-Planned Land', 'project_category' => 'service',
            'client_name' => 'Halmashauri ya Dodoma', 'project_status' => 'Ongoing', 'starting_price' => 0, 'currency' => 'TZS',
            'location_name' => 'Nzuguni', 'city' => 'Dodoma',
        ]);

        $this->actingAs($this->admin())->get(route('admin.projects.show', $service))
            ->assertOk()
            ->assertSee('Halmashauri ya Dodoma')
            ->assertDontSee('Plot inventory');
    }
}
