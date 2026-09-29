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
        $projects = RealEstateProject::withCount('inquiries')->latest()->paginate(15);

        return view('admin.projects.index', compact('projects'));
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
            'starting_price' => 'required|numeric|min:0',
            'location_name' => 'required|string|max:255',
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
            'description' => 'nullable|string',
            'hero_image' => 'nullable|string',
            'shapefile' => 'nullable|file|mimes:zip,geojson,json,kml|max:20480',
        ]);

        $project = RealEstateProject::create([
            'uuid' => Str::uuid(),
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'developer_name' => $request->developer_name,
            'project_type' => $request->project_type,
            'project_status' => $request->project_status,
            'starting_price' => $request->starting_price,
            'currency' => 'TZS',
            'location_name' => $request->location_name,
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
            'hero_image' => $request->hero_image,
            'description' => $request->description,
            'is_featured' => $request->has('is_featured'),
            'is_published' => $request->has('is_published'),
        ]);

        if ($request->hasFile('shapefile')) {
            $this->processShapefileUpload($request->file('shapefile'), $project);
        }

        return redirect()->route('admin.projects.index')->with('success', 'Project created successfully with GIS Shapefile support!');
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
            'starting_price' => 'required|numeric|min:0',
            'location_name' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'region' => 'nullable|string|max:255',
            'district' => 'nullable|string|max:255',
            'council' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'total_units' => 'nullable|integer',
            'available_units' => 'nullable|integer',
            'sold_plots' => 'nullable|integer',
            'description' => 'nullable|string',
            'shapefile' => 'nullable|file|mimes:zip,geojson,json,kml|max:20480',
        ]);

        $project->update([
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'developer_name' => $request->developer_name,
            'project_type' => $request->project_type,
            'project_status' => $request->project_status,
            'starting_price' => $request->starting_price,
            'location_name' => $request->location_name,
            'city' => $request->city,
            'region' => $request->region ?? $request->city,
            'district' => $request->district ?? $request->city,
            'council' => $request->council,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'total_units' => $request->total_units ?? $project->total_units,
            'available_units' => $request->available_units ?? $project->available_units,
            'sold_plots' => $request->sold_plots ?? $project->sold_plots,
            'description' => $request->description,
            'is_featured' => $request->has('is_featured'),
            'is_published' => $request->has('is_published'),
        ]);

        if ($request->hasFile('shapefile')) {
            $this->processShapefileUpload($request->file('shapefile'), $project);
        }

        return redirect()->route('admin.projects.index')->with('success', 'Project updated successfully!');
    }

    public function destroy(RealEstateProject $project)
    {
        $project->delete();

        return redirect()->route('admin.projects.index')->with('success', 'Project deleted successfully.');
    }

    protected function processShapefileUpload($file, RealEstateProject $project): void
    {
        $path = $file->store('shapefiles', 'public');
        $project->update(['shapefile_path' => $path]);

        try {
            $geojson = $this->shapefileService->parseUploadedFile($file);
            $project->update(['geojson_data' => json_encode($geojson)]);

            // Generate LandParcel rows if features present
            if (! empty($geojson['features'])) {
                foreach ($geojson['features'] as $index => $feature) {
                    $props = $feature['properties'] ?? [];
                    $coords = $feature['geometry']['coordinates'] ?? [];

                    LandParcel::create([
                        'uuid' => Str::uuid(),
                        'real_estate_project_id' => $project->id,
                        'parcel_number' => $props['parcel_number'] ?? $props['plot_no'] ?? (string) ($index + 1),
                        'block_number' => $props['block_number'] ?? $props['block'] ?? 'Block A',
                        'title_deed_type' => 'Right of Occupancy (Title Deed)',
                        'acreage' => 0.15,
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
            }
        } catch (\Throwable $e) {
            // Log or soft fail shapefile parse
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
