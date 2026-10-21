<?php

namespace Tests\Feature;

use App\Models\LandParcel;
use App\Models\RealEstateProject;
use App\Models\User;
use App\Services\ShapefileService;
use Illuminate\Http\Testing\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProjectManagementFixesTest extends TestCase
{
    private function admin(): User
    {
        return User::where('email', 'admin@rehospace.com')->firstOrFail();
    }

    private function fixtureZip(): UploadedFile
    {
        return new UploadedFile(base_path('tests/fixtures/minara-shapefile.zip'), 'minara.zip', 'application/zip', null, true);
    }

    public function test_project_edit_links_bind_by_uuid_and_render(): void
    {
        $project = RealEstateProject::firstOrFail();

        $url = route('admin.projects.edit', $project);
        $this->assertStringContainsString($project->uuid, $url, 'Edit URL must carry the uuid, not the numeric id.');

        $this->actingAs($this->admin())->get($url)->assertStatus(200);
        $this->actingAs($this->admin())->get(route('admin.projects.index'))
            ->assertSee($project->uuid)
            ->assertSee('projectPreviewModal', false)
            ->assertDontSee('admin/projects/'.$project->id.'/edit', false);
    }

    public function test_project_hero_image_can_be_uploaded(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin())->post(route('admin.projects.store'), [
            'title' => 'Chihoni Nala Gardens',
            'developer_name' => 'Avenix',
            'project_type' => 'Master-Planned Land',
            'project_status' => 'Selling',
            'starting_price' => 500000,
            'location_name' => 'Chihoni',
            'city' => 'Dodoma',
            'is_published' => '1',
            'hero_image_file' => File::image('plot.jpg', 1200, 700),
        ])->assertRedirect(route('admin.projects.index'));

        $project = RealEstateProject::where('slug', 'chihoni-nala-gardens')->firstOrFail();
        $this->assertStringStartsWith('/storage/projects/', $project->hero_image);
        Storage::disk('public')->assertExists(Str::after($project->hero_image, '/storage/'));
    }

    public function test_real_esri_shapefile_zip_imports_parcels_with_wgs84_coordinates(): void
    {
        $collection = app(ShapefileService::class)->parseUploadedFile($this->fixtureZip());

        $this->assertSame(18, count($collection['features']));

        $first = $collection['features'][0];
        $this->assertSame('23', (string) $first['properties']['parcel_number']);
        $this->assertSame('C', $first['properties']['block_number']);
        $this->assertSame('Available', $first['properties']['status']);
        $this->assertEqualsWithDelta(588.0, $first['properties']['area_sqm'], 0.01);

        [$lng, $lat] = $first['geometry']['coordinates'][0][0];
        $this->assertGreaterThan(29, $lng);
        $this->assertLessThan(41, $lng);
        $this->assertGreaterThan(-12, $lat);
        $this->assertLessThan(-0.5, $lat);
    }

    public function test_shapefile_upload_creates_parcels_and_updates_plot_counts(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin())->post(route('admin.projects.store'), [
            'title' => 'Minara Estate',
            'developer_name' => 'Avenix',
            'project_type' => 'Master-Planned Land',
            'project_status' => 'Selling',
            'starting_price' => 500000,
            'location_name' => 'Chihoni Nala',
            'city' => 'Dodoma',
            'is_published' => '1',
            'shapefile' => $this->fixtureZip(),
        ])->assertRedirect(route('admin.projects.index'));

        $project = RealEstateProject::where('slug', 'minara-estate')->firstOrFail();
        $parcels = LandParcel::where('real_estate_project_id', $project->id)->get();

        $this->assertCount(18, $parcels);
        $this->assertSame(18, $project->fresh()->total_units);
        $this->assertNotEmpty(json_decode($parcels->first()->geojson_geometry, true)['coordinates'] ?? []);

        // The public map payload must carry the plot shapes.
        $this->get(route('public.projects.show', $project->slug))
            ->assertStatus(200)
            ->assertSee('FeatureCollection', false);
    }

    public function test_unpublished_project_is_hidden_from_guests_but_previews_for_staff(): void
    {
        $project = RealEstateProject::firstOrFail();
        $project->update(['is_published' => false]);

        $this->get(route('public.projects.show', $project->slug))->assertStatus(404);

        $this->actingAs($this->admin())
            ->get(route('public.projects.show', $project->slug))
            ->assertStatus(200)
            ->assertSee(__('admin.draft_preview_notice'));
    }

    public function test_project_prices_show_with_thousand_separators(): void
    {
        $project = RealEstateProject::firstOrFail();
        $project->update(['starting_price' => 500000, 'is_published' => true]);

        $this->assertStringContainsString('500,000', $project->fresh()->formatted_price);

        $this->actingAs($this->admin())->get(route('admin.projects.index'))
            ->assertSee('data-price="TSh 500,000"', false);
    }
}
