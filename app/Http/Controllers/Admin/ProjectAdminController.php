<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LandParcel;
use App\Models\RealEstateProject;
use App\Services\ShapefileService;
use Illuminate\Http\Request;
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
            'starting_price' => 'required_if:project_category,land|nullable|numeric|min:0',
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
            'starting_price' => $request->input('project_category') === 'service' ? 0 : $request->starting_price,
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
            'starting_price' => 'required_if:project_category,land|nullable|numeric|min:0',
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
            'starting_price' => $request->input('project_category') === 'service' ? ($project->starting_price ?? 0) : $request->starting_price,
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
            'description' => $request->description,
            'is_featured' => $request->has('is_featured'),
            'is_published' => $request->has('is_published'),
        ]);

        $this->applyHeroImage($request, $project);
        $this->applyGallery($request, $project);

        if ($request->hasFile('shapefile')) {
            $this->processShapefileUpload($request->file('shapefile'), $project);
        }

        return redirect()->route('admin.projects.index')->with('success', __('admin.project_updated'));
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

            // Regenerate LandParcel rows from the new file
            if (! empty($geojson['features'])) {
                LandParcel::where('real_estate_project_id', $project->id)->delete();

                foreach ($geojson['features'] as $index => $feature) {
                    $props = $feature['properties'] ?? [];
                    $coords = $feature['geometry']['coordinates'] ?? [];

                    LandParcel::create([
                        'uuid' => Str::uuid(),
                        'real_estate_project_id' => $project->id,
                        'parcel_number' => $props['parcel_number'] ?? $props['plot_no'] ?? (string) ($index + 1),
                        'block_number' => $props['block_number'] ?? $props['block'] ?? 'Block A',
                        'title_deed_type' => 'Right of Occupancy (Title Deed)',
                        'acreage' => isset($props['area_sqm']) && is_numeric($props['area_sqm']) ? round(((float) $props['area_sqm']) / 4046.8564224, 4) : 0.15,
                        'area_sqm' => $props['area_sqm'] ?? 600.00,
                        'price' => $props['price'] ?? $project->starting_price,
                        'status' => $props['status'] ?? 'Available',
                        'land_use' => $props['land_use'] ?? 'Makazi / Residential',
                        'region' => $project->region,
                        'district' => $project->district,
                        'council' => $project->council,
                        'lease_details' => '99-Year Leasehold',
                        'boundary_coordinates_json' => is_array($coords) ? ($coords[0] ?? $coords) : null,
                        'geojson_geometry' => json_encode($feature['geometry'] ?? []),
                    ]);
                }

                $imported = LandParcel::where('real_estate_project_id', $project->id)->get();
                $project->update([
                    'total_units' => $imported->count(),
                    'available_units' => $imported->where('status', 'Available')->count(),
                    'sold_plots' => $imported->where('status', 'Sold')->count(),
                ]);
            }
        } catch (\Throwable $e) {
            report($e); // keep the upload, surface the parse problem in logs
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
