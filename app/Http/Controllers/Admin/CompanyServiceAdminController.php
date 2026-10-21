<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanyService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CompanyServiceAdminController extends Controller
{
    public function index()
    {
        $services = CompanyService::orderBy('display_order')->get();

        return view('admin.services.index', compact('services'));
    }

    public function create()
    {
        return view('admin.services.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:50',
            'short_description' => 'required|string|max:1000',
            'full_description' => 'nullable|string',
            'icon' => 'required|string|max:100',
            'display_order' => 'nullable|integer',
            'sub_services' => 'nullable|string',
            'thumbnail_file' => 'nullable|image|max:4096',
            'cta_text' => 'nullable|string|max:60',
            'cta_url' => 'nullable|string|max:255',
        ]);

        $subServices = [];
        if ($request->filled('sub_services')) {
            $subServices = array_values(array_filter(array_map('trim', explode(',', $request->sub_services))));
        }

        CompanyService::create([
            'uuid' => Str::uuid(),
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'category' => $request->category,
            'short_description' => $request->short_description,
            'full_description' => $request->full_description,
            'icon' => $request->icon,
            'display_order' => $request->display_order ?? 0,
            'sub_services' => $subServices,
            'cta_text' => $request->cta_text,
            'cta_url' => $request->cta_url,
            'thumbnail' => $request->hasFile('thumbnail_file')
                ? '/storage/'.$request->file('thumbnail_file')->store('services', 'public')
                : null,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.services.index')->with('success', 'Service created successfully!');
    }

    public function edit(CompanyService $service)
    {
        return view('admin.services.edit', compact('service'));
    }

    public function update(Request $request, CompanyService $service)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:50',
            'short_description' => 'required|string|max:1000',
            'full_description' => 'nullable|string',
            'icon' => 'required|string|max:100',
            'display_order' => 'nullable|integer',
            'sub_services' => 'nullable|string',
            'thumbnail_file' => 'nullable|image|max:4096',
            'cta_text' => 'nullable|string|max:60',
            'cta_url' => 'nullable|string|max:255',
        ]);

        $subServices = [];
        if ($request->filled('sub_services')) {
            $subServices = array_values(array_filter(array_map('trim', explode(',', $request->sub_services))));
        }

        $service->update([
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'category' => $request->category,
            'short_description' => $request->short_description,
            'full_description' => $request->full_description,
            'icon' => $request->icon,
            'display_order' => $request->display_order ?? 0,
            'sub_services' => $subServices,
            'cta_text' => $request->cta_text,
            'cta_url' => $request->cta_url,
        ]);

        if ($request->hasFile('thumbnail_file')) {
            $service->update(['thumbnail' => '/storage/'.$request->file('thumbnail_file')->store('services', 'public')]);
        }

        return redirect()->route('admin.services.index')->with('success', 'Service updated successfully!');
    }

    public function destroy(CompanyService $service)
    {
        $service->delete();

        return redirect()->route('admin.services.index')->with('success', 'Service deleted successfully.');
    }

    public function bulkAction(Request $request)
    {
        $data = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:company_services,id',
            'action' => 'required|string|in:activate,deactivate,delete',
        ]);

        $count = count($data['ids']);

        if ($data['action'] === 'activate') {
            CompanyService::whereIn('id', $data['ids'])->update(['is_active' => true]);

            return response()->json([
                'success' => true,
                'message' => "Successfully activated {$count} services.",
                'action' => 'activate',
                'ids' => $data['ids'],
            ]);
        }

        if ($data['action'] === 'deactivate') {
            CompanyService::whereIn('id', $data['ids'])->update(['is_active' => false]);

            return response()->json([
                'success' => true,
                'message' => "Successfully deactivated {$count} services.",
                'action' => 'deactivate',
                'ids' => $data['ids'],
            ]);
        }

        if ($data['action'] === 'delete') {
            CompanyService::whereIn('id', $data['ids'])->delete();

            return response()->json([
                'success' => true,
                'message' => "Successfully deleted {$count} services.",
                'action' => 'delete',
                'ids' => $data['ids'],
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Invalid action.'], 422);
    }
}
