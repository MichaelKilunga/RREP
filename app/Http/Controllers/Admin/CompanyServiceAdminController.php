<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanyService;
use App\Models\PropertyInquiry;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CompanyServiceAdminController extends Controller
{
    public function index(Request $request)
    {
        $services = CompanyService::withCount([
            'requests',
            'requests as new_requests_count' => fn ($q) => $q->where('status', 'New'),
            'projects',
        ])
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w->where('title', 'like', "%{$request->q}%")->orWhere('category', 'like', "%{$request->q}%")))
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->category))
            ->when($request->filled('status'), fn ($q) => $q->where('is_active', $request->status === 'active'))
            ->orderBy('display_order')
            ->orderBy('title')
            ->get();

        $stats = [
            'total' => CompanyService::count(),
            'active' => CompanyService::where('is_active', true)->count(),
            'requests_month' => PropertyInquiry::whereNotNull('company_service_id')->where('created_at', '>=', now()->startOfMonth())->count(),
            'requests_new' => PropertyInquiry::whereNotNull('company_service_id')->where('status', 'New')->count(),
        ];
        $categories = CompanyService::query()->distinct()->orderBy('category')->pluck('category')->filter();

        return view('admin.services.index', compact('services', 'stats', 'categories'));
    }

    public function show(CompanyService $service)
    {
        $service->loadCount(['requests', 'projects']);
        $requests = $service->requests()->latest()->take(15)->get();
        $projects = $service->projects()->latest()->get();
        $stats = [
            'new' => $service->requests()->where('status', 'New')->count(),
            'month' => $service->requests()->where('created_at', '>=', now()->startOfMonth())->count(),
            'resolved' => $service->requests()->whereIn('status', ['Resolved', 'Closed'])->count(),
        ];

        return view('admin.services.show', compact('service', 'requests', 'projects', 'stats'));
    }

    public function create()
    {
        return view('admin.services.create');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $service = CompanyService::create($data + [
            'uuid' => Str::uuid(),
            'slug' => $this->uniqueSlug($data['title']),
            'thumbnail' => $request->hasFile('thumbnail_file')
                ? '/storage/'.$request->file('thumbnail_file')->store('services', 'public')
                : null,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.services.show', $service)->with('success', __('admin.service_created'));
    }

    public function edit(CompanyService $service)
    {
        return view('admin.services.edit', compact('service'));
    }

    public function update(Request $request, CompanyService $service)
    {
        $data = $this->validated($request);

        if ($data['title'] !== $service->title) {
            $data['slug'] = $this->uniqueSlug($data['title'], $service->id);
        }
        $data['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('thumbnail_file')) {
            $data['thumbnail'] = '/storage/'.$request->file('thumbnail_file')->store('services', 'public');
        } elseif ($request->boolean('remove_thumbnail')) {
            $data['thumbnail'] = null;
        }

        $service->update($data);

        return redirect()->route('admin.services.show', $service)->with('success', __('admin.service_updated'));
    }

    public function destroy(CompanyService $service)
    {
        $service->delete();

        return redirect()->route('admin.services.index')->with('success', __('admin.service_deleted'));
    }

    public function toggle(CompanyService $service)
    {
        $service->update(['is_active' => ! $service->is_active]);

        return back()->with('success', $service->is_active ? __('admin.service_now_active') : __('admin.service_now_hidden'));
    }

    /**
     * Swap display order with the neighbour above/below.
     */
    public function move(Request $request, CompanyService $service)
    {
        $direction = $request->input('direction') === 'up' ? 'up' : 'down';
        $ordered = CompanyService::orderBy('display_order')->orderBy('title')->get()->values();

        // Normalise to 1..n first so equal orders can still be swapped.
        $ordered->each(fn (CompanyService $s, int $i) => $s->display_order !== $i + 1 ? $s->update(['display_order' => $i + 1]) : null);

        $index = $ordered->search(fn (CompanyService $s) => $s->id === $service->id);
        $swapWith = $ordered->get($direction === 'up' ? $index - 1 : $index + 1);

        if ($swapWith) {
            $current = $service->fresh()->display_order;
            $service->update(['display_order' => $swapWith->display_order]);
            $swapWith->update(['display_order' => $current]);
        }

        return back();
    }

    public function duplicate(CompanyService $service)
    {
        $copy = $service->replicate(['uuid', 'slug']);
        $copy->title = $service->title.' ('.__('admin.copy_suffix').')';
        $copy->slug = $this->uniqueSlug($copy->title);
        $copy->is_active = false;
        $copy->display_order = (int) CompanyService::max('display_order') + 1;
        $copy->save();

        return redirect()->route('admin.services.edit', $copy)->with('success', __('admin.service_duplicated'));
    }

    public function bulkAction(Request $request)
    {
        $data = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:company_services,id',
            'action' => 'required|string|in:activate,deactivate,delete',
        ]);

        $count = count($data['ids']);

        match ($data['action']) {
            'activate' => CompanyService::whereIn('id', $data['ids'])->update(['is_active' => true]),
            'deactivate' => CompanyService::whereIn('id', $data['ids'])->update(['is_active' => false]),
            'delete' => CompanyService::whereIn('id', $data['ids'])->delete(),
        };

        return response()->json([
            'success' => true,
            'message' => __('admin.services_bulk_'.$data['action'], ['count' => $count]),
            'action' => $data['action'],
            'ids' => $data['ids'],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:50',
            'short_description' => 'required|string|max:1000',
            'full_description' => 'nullable|string',
            'icon' => 'required|string|max:100',
            'display_order' => 'nullable|integer',
            'sub_services' => 'nullable|string',
            'thumbnail_file' => 'nullable|file|mimes:jpg,jpeg,png,webp,gif|max:'.upload_limit_kb(),
            'cta_text' => 'nullable|string|max:60',
            'cta_url' => 'nullable|string|max:255',
        ], self::imageMessages('thumbnail_file'));

        $data['sub_services'] = array_values(array_filter(array_map('trim', explode(',', (string) ($data['sub_services'] ?? '')))));
        $data['display_order'] = $data['display_order'] ?? 0;
        unset($data['thumbnail_file']);

        return $data;
    }

    protected function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'service';
        $slug = $base;
        $i = 2;
        while (CompanyService::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
