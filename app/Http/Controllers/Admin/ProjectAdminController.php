<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LandParcel;
use App\Models\RealEstateProject;
use App\Models\Reservation;
use App\Services\ShapefileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProjectAdminController extends Controller
{
    public function __construct(
        protected ShapefileService $shapefileService
    ) {}

    public function index()
    {
        $category = request('category');
        $projects = RealEstateProject::withCount('inquiries')
            ->with('service')
            ->when(in_array($category, ['land', 'service'], true), fn ($q) => $q->where('project_category', $category))
            ->when(request('q'), function ($q, $term) {
                $q->where(function ($query) use ($term) {
                    $query->where('title', 'like', "%{$term}%")
                        ->orWhere('council', 'like', "%{$term}%")
                        ->orWhere('location_name', 'like', "%{$term}%")
                        ->orWhere('client_name', 'like', "%{$term}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.projects.index', compact('projects', 'category'));
    }

    public function create()
    {
        return view('admin.projects.create');
    }

    public function show(RealEstateProject $project)
    {
        $project->load('service');
        $parcels = $project->parcels()->orderBy('block_number')->orderByRaw('CAST(parcel_number AS UNSIGNED), parcel_number')->get();

        $plotFeatures = $parcels->filter(fn (LandParcel $p) => is_array($p->boundary_coordinates_json) && $p->boundary_coordinates_json !== [])
            ->map(fn (LandParcel $p) => [
                'type' => 'Feature',
                'properties' => [
                    'uuid' => $p->uuid,
                    'parcel_number' => $p->parcel_number,
                    'block_number' => $p->block_number,
                    'status' => $p->status ?: 'Available',
                    'price' => (float) $p->price,
                    'area_sqm' => (float) $p->area_sqm,
                ],
                'geometry' => ['type' => 'Polygon', 'coordinates' => [$p->boundary_coordinates_json]],
            ])->values();

        $stats = [
            'total' => $parcels->count(),
            'available' => $parcels->where('status', 'Available')->count(),
            'reserved' => $parcels->where('status', 'Reserved')->count(),
            'sold' => $parcels->where('status', 'Sold')->count(),
            'value_available' => (float) $parcels->where('status', 'Available')->sum('price'),
            'value_sold' => (float) $parcels->where('status', 'Sold')->sum('price'),
            'area_total' => (float) $parcels->sum('area_sqm'),
        ];

        $requests = $project->inquiries()->latest()->take(8)->get();
        $reservations = Reservation::with(['customer', 'landParcel'])
            ->whereIn('land_parcel_id', $parcels->pluck('id'))
            ->latest()->take(8)->get();

        return view('admin.projects.show', [
            'project' => $project,
            'parcels' => $parcels,
            'plotGeojson' => ['type' => 'FeatureCollection', 'features' => $plotFeatures],
            'stats' => $stats,
            'blocks' => $parcels->pluck('block_number')->filter()->unique()->sort()->values(),
            'requests' => $requests,
            'reservations' => $reservations,
        ]);
    }

    /**
     * Copy a project's settings (not its plots, requests or history) as an unpublished draft.
     */
    public function duplicate(RealEstateProject $project)
    {
        $copy = $project->replicate(['uuid', 'slug', 'is_published', 'is_pinned', 'shapefile_path', 'geojson_data', 'total_units', 'available_units', 'sold_plots']);
        $copy->title = $project->title.' ('.__('admin.copy_suffix').')';
        $copy->slug = $this->uniqueSlug($copy->title);
        $copy->is_published = false;
        $copy->is_pinned = false;
        $copy->total_units = 0;
        $copy->available_units = 0;
        $copy->sold_plots = 0;
        $copy->save();

        return redirect()->route('admin.projects.edit', $copy)->with('success', __('admin.project_duplicated'));
    }

    public function togglePublish(RealEstateProject $project)
    {
        $project->update(['is_published' => ! $project->is_published]);

        return back()->with('success', $project->is_published ? __('admin.project_published_now') : __('admin.project_unpublished_now'));
    }

    protected function uniqueSlug(string $title): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $i = 2;
        while (RealEstateProject::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'developer_name' => 'required|string|max:255',
            'project_type' => 'required|string',
            'project_status' => 'required|string',
            'project_category' => 'nullable|in:land,service',
            'company_service_id' => 'required_if:project_category,service|nullable|exists:company_services,id',
            'client_name' => 'nullable|string|max:255',
            'amenities' => 'nullable|string|max:1000',
            'gallery_files' => 'nullable|array|max:12',
            'gallery_files.*' => 'image|max:4096',
            'remove_gallery' => 'nullable|array',
            'starting_price' => 'nullable|numeric|min:0',
            'location_name' => 'required_if:project_category,land|nullable|string|max:255',
            'city' => 'required|string|max:255',
            'region' => 'nullable|string|max:255',
            'district' => 'nullable|string|max:255',
            'council' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'total_units' => 'nullable|integer',
            'available_units' => 'nullable|integer',
            'sold_plots' => 'nullable|integer',
            'grace_period_days' => 'nullable|integer',
            'initial_payment_percent' => 'nullable|numeric',
            'application_fee' => 'nullable|numeric|min:0',
            'price_per_sqm' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'hero_image' => 'nullable|string',
            'hero_image_file' => 'nullable|image|max:4096',
            'shapefile' => 'nullable|file|mimes:zip,geojson,json,kml|max:20480',
        ]);

        $project = RealEstateProject::create([
            'uuid' => Str::uuid(),
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'developer_name' => $request->developer_name,
            'project_type' => $request->project_type,
            'project_category' => $request->input('project_category', 'land'),
            'company_service_id' => $request->input('project_category') === 'service' ? $request->company_service_id : null,
            'client_name' => $request->client_name,
            'amenities_json' => self::amenitiesFromInput($request->amenities),
            'project_status' => $request->project_status,
            'starting_price' => $request->input('project_category') === 'service' ? 0 : (float) ($request->starting_price ?? 0),
            'currency' => 'TZS',
            'location_name' => $request->location_name ?: $request->city,
            'city' => $request->city,
            'region' => $request->region ?? $request->city,
            'district' => $request->district ?? $request->city,
            'council' => $request->council,
            'latitude' => $request->latitude ?? -6.7924,
            'longitude' => $request->longitude ?? 39.2083,
            'total_units' => $request->total_units ?? 0,
            'available_units' => $request->available_units ?? 0,
            'sold_plots' => $request->sold_plots ?? 0,
            'grace_period_days' => $request->grace_period_days ?? 30,
            'initial_payment_percent' => $request->initial_payment_percent ?? 50.00,
            'application_fee' => $request->application_fee,
            'price_per_sqm' => $request->price_per_sqm,
            'hero_image' => $request->hero_image,
            'description' => $request->description,
            'is_featured' => $request->has('is_featured'),
            'is_published' => $request->has('is_published'),
        ]);

        $this->applyHeroImage($request, $project);
        $this->applyGallery($request, $project);

        if ($request->hasFile('shapefile')) {
            $this->processShapefileUpload($request->file('shapefile'), $project);
        }

        $this->applyPricePerSqm($project);

        return redirect()->route('admin.projects.index')->with('success', __('admin.project_created'));
    }

    public function edit(RealEstateProject $project)
    {
        $project->load('inquiries');
        $parcels = LandParcel::where('real_estate_project_id', $project->id)->get();

        return view('admin.projects.edit', compact('project', 'parcels'));
    }

    public function update(Request $request, RealEstateProject $project)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'developer_name' => 'required|string|max:255',
            'project_type' => 'required|string',
            'project_status' => 'required|string',
            'project_category' => 'nullable|in:land,service',
            'company_service_id' => 'required_if:project_category,service|nullable|exists:company_services,id',
            'client_name' => 'nullable|string|max:255',
            'amenities' => 'nullable|string|max:1000',
            'gallery_files' => 'nullable|array|max:12',
            'gallery_files.*' => 'image|max:4096',
            'remove_gallery' => 'nullable|array',
            'starting_price' => 'nullable|numeric|min:0',
            'location_name' => 'required_if:project_category,land|nullable|string|max:255',
            'city' => 'required|string|max:255',
            'region' => 'nullable|string|max:255',
            'district' => 'nullable|string|max:255',
            'council' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'total_units' => 'nullable|integer',
            'available_units' => 'nullable|integer',
            'sold_plots' => 'nullable|integer',
            'initial_payment_percent' => 'nullable|numeric',
            'application_fee' => 'nullable|numeric|min:0',
            'price_per_sqm' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'shapefile' => 'nullable|file|mimes:zip,geojson,json,kml|max:20480',
            'hero_image' => 'nullable|string',
            'hero_image_file' => 'nullable|image|max:4096',
        ]);

        $project->update([
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'developer_name' => $request->developer_name,
            'project_type' => $request->project_type,
            'project_category' => $request->input('project_category', $project->project_category ?? 'land'),
            'company_service_id' => $request->input('project_category') === 'service' ? $request->company_service_id : null,
            'client_name' => $request->client_name,
            'amenities_json' => self::amenitiesFromInput($request->amenities),
            'project_status' => $request->project_status,
            'starting_price' => $request->input('project_category') === 'service' ? ($project->starting_price ?? 0) : (float) ($request->starting_price ?? $project->starting_price ?? 0),
            'location_name' => $request->location_name ?: $request->city,
            'city' => $request->city,
            'region' => $request->region ?? $request->city,
            'district' => $request->district ?? $request->city,
            'council' => $request->council,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'total_units' => $request->total_units ?? $project->total_units,
            'available_units' => $request->available_units ?? $project->available_units,
            'sold_plots' => $request->sold_plots ?? $project->sold_plots,
            'initial_payment_percent' => $request->initial_payment_percent ?? $project->initial_payment_percent,
            'application_fee' => $request->application_fee,
            'price_per_sqm' => $request->price_per_sqm,
            'description' => $request->description,
            'is_featured' => $request->has('is_featured'),
            'is_published' => $request->has('is_published'),
        ]);

        $this->applyHeroImage($request, $project);
        $this->applyGallery($request, $project);

        if ($request->hasFile('shapefile')) {
            $this->processShapefileUpload($request->file('shapefile'), $project);
        }

        $this->applyPricePerSqm($project);

        return redirect()->route('admin.projects.index')->with('success', __('admin.project_updated'));
    }

    /**
     * Pin a project to the top of the public listings (one pin at a time);
     * pinning again unpins it.
     */
    public function togglePin(RealEstateProject $project)
    {
        $nowPinned = ! $project->is_pinned;

        if ($nowPinned) {
            RealEstateProject::where('is_pinned', true)->update(['is_pinned' => false]);
        }
        $project->update(['is_pinned' => $nowPinned]);

        return back()->with('success', $nowPinned ? __('admin.project_pinned') : __('admin.project_unpinned'));
    }

    public function destroy(RealEstateProject $project)
    {
        $project->delete();

        return redirect()->route('admin.projects.index')->with('success', 'Project deleted successfully.');
    }

    /**
     * Persist the uploaded hero image (file beats URL; empty inputs keep the current image).
     */
    /**
     * @return array<int, string>
     */
    protected static function amenitiesFromInput(?string $raw): array
    {
        return array_values(array_filter(array_map('trim', explode(',', (string) $raw))));
    }

    /**
     * Merge uploaded gallery images with the kept existing ones.
     */
    protected function applyGallery(Request $request, RealEstateProject $project): void
    {
        $existing = array_values(array_filter((array) $project->gallery_images_json));

        foreach ((array) $request->input('remove_gallery', []) as $path) {
            $existing = array_values(array_filter($existing, fn ($item) => $item !== $path));
        }

        foreach ((array) $request->file('gallery_files', []) as $file) {
            $existing[] = '/storage/'.$file->store('projects/gallery', 'public');
        }

        if ($existing !== array_values(array_filter((array) $project->gallery_images_json))) {
            $project->update(['gallery_images_json' => $existing]);
        }
    }

    protected function applyHeroImage(Request $request, RealEstateProject $project): void
    {
        if ($request->hasFile('hero_image_file')) {
            $project->update(['hero_image' => '/storage/'.$request->file('hero_image_file')->store('projects', 'public')]);
        } elseif ($request->filled('hero_image')) {
            $project->update(['hero_image' => $request->input('hero_image')]);
        }
    }

    protected function processShapefileUpload($file, RealEstateProject $project): void
    {
        $path = $file->store('shapefiles', 'public');
        $project->update(['shapefile_path' => $path]);

        try {
            $geojson = $this->shapefileService->parseUploadedFile($file);
            $project->update(['geojson_data' => json_encode($geojson)]);

            // Sync plots with the new file. Plots already in the system (same block +
            // plot number) keep their id, status and price so sales, holds and
            // manual edits survive a re-upload; only geometry and area refresh.
            if (! empty($geojson['features'])) {
                $plotKey = fn ($block, $number) => mb_strtolower(trim((string) $block)).'|'.mb_strtolower(trim((string) $number));
                $existing = LandParcel::where('real_estate_project_id', $project->id)->get()
                    ->keyBy(fn (LandParcel $p) => $plotKey($p->block_number, $p->parcel_number));
                $seen = [];

                foreach ($geojson['features'] as $index => $feature) {
                    $props = $feature['properties'] ?? [];
                    $coords = $feature['geometry']['coordinates'] ?? [];
                    $number = (string) ($props['parcel_number'] ?? $props['plot_no'] ?? ($index + 1));
                    $block = (string) ($props['block_number'] ?? $props['block'] ?? 'Block A');
                    $key = $plotKey($block, $number);
                    $seen[$key] = true;

                    $geometry = [
                        'acreage' => isset($props['area_sqm']) && is_numeric($props['area_sqm']) ? round(((float) $props['area_sqm']) / 4046.8564224, 4) : 0.15,
                        'area_sqm' => $props['area_sqm'] ?? 600.00,
                        'boundary_coordinates_json' => is_array($coords) ? ($coords[0] ?? $coords) : null,
                        'geojson_geometry' => json_encode($feature['geometry'] ?? []),
                    ];

                    if ($existing->has($key)) {
                        $existing[$key]->update($geometry);

                        continue;
                    }

                    LandParcel::create($geometry + [
                        'uuid' => Str::uuid(),
                        'real_estate_project_id' => $project->id,
                        'parcel_number' => $number,
                        'block_number' => $block,
                        'title_deed_type' => 'Right of Occupancy (Title Deed)',
                        'price' => $props['price'] ?? $project->starting_price,
                        'status' => $props['status'] ?? 'Available',
                        'land_use' => $props['land_use'] ?? 'Makazi / Residential',
                        'region' => $project->region,
                        'district' => $project->district,
                        'council' => $project->council,
                        'lease_details' => '99-Year Leasehold',
                    ]);
                }

                $existing->reject(fn (LandParcel $p, string $key) => isset($seen[$key]))->each->delete();
                $project->syncPlotCounts();
            }
        } catch (\Throwable $e) {
            report($e); // keep the upload, surface the parse problem in logs
        }
    }

    /**
     * When a project has a price per square meter, every plot's price is the
     * product of its area and that rate, and the project's advertised price
     * becomes the cheapest plot.
     */
    protected function applyPricePerSqm(RealEstateProject $project): void
    {
        $rate = (float) $project->price_per_sqm;
        if ($rate <= 0) {
            return;
        }

        LandParcel::where('real_estate_project_id', $project->id)
            ->whereNotNull('area_sqm')
            ->where('area_sqm', '>', 0)
            ->update(['price' => DB::raw('area_sqm * '.$rate)]);

        $cheapest = (float) LandParcel::where('real_estate_project_id', $project->id)->where('price', '>', 0)->min('price');
        if ($cheapest > 0) {
            $project->update(['starting_price' => $cheapest]);
        }
    }

    public function bulkAction(Request $request)
    {
        $data = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:real_estate_projects,id',
            'action' => 'required|string|in:change_status,delete,publish,unpublish',
            'status' => 'nullable|string',
        ]);

        $count = count($data['ids']);

        if ($data['action'] === 'publish') {
            RealEstateProject::whereIn('id', $data['ids'])->update(['is_published' => true]);

            return response()->json([
                'success' => true,
                'message' => "Successfully published {$count} projects.",
                'action' => 'publish',
                'ids' => $data['ids'],
            ]);
        }

        if ($data['action'] === 'unpublish') {
            RealEstateProject::whereIn('id', $data['ids'])->update(['is_published' => false]);

            return response()->json([
                'success' => true,
                'message' => "Successfully unpublished {$count} projects.",
                'action' => 'unpublish',
                'ids' => $data['ids'],
            ]);
        }

        if ($data['action'] === 'delete') {
            abort_unless($request->user()->can('delete_projects'), 403);
            RealEstateProject::whereIn('id', $data['ids'])->delete();

            return response()->json([
                'success' => true,
                'message' => "Successfully deleted {$count} projects.",
                'action' => 'delete',
                'ids' => $data['ids'],
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Invalid action.'], 422);
    }
}
