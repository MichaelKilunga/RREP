@extends('layouts.app')

@section('title', 'Manage Projects & Shapefiles - Admin Panel')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">Land Projects & Shapefile Management</h3>
            <p class="text-muted small mb-0">Create master-planned land projects, upload GIS Shapefiles (.zip), and manage plot boundaries.</p>
        </div>
        <a href="{{ route('admin.projects.create') }}" class="btn btn-primary fw-bold rounded-pill">
            <i class="bi bi-file-earmark-zip me-1"></i> Add Project / Upload Shapefile
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Project Title</th>
                            <th>Location / Council</th>
                            <th>Plots Stat</th>
                            <th>Starting Price</th>
                            <th>GIS / Shapefile</th>
                            <th>Status</th>
                            <th class="pe-4 text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($projects as $proj)
                            <tr>
                                <td class="ps-4">
                                    <strong class="text-dark d-block">{{ $proj->title }}</strong>
                                    <small class="text-muted">{{ $proj->developer_name }}</small>
                                </td>
                                <td>
                                    <span class="fw-semibold text-dark">{{ $proj->city }}</span><br>
                                    <small class="text-muted">{{ $proj->council ?? $proj->district ?? 'Tanzania' }}</small>
                                </td>
                                <td>
                                    <div class="small">
                                        <span class="text-dark fw-bold">{{ $proj->total_units }}</span> Total |
                                        <span class="text-success fw-bold">{{ $proj->available_units }}</span> Avail |
                                        <span class="text-danger fw-bold">{{ $proj->sold_plots }}</span> Sold
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-bold text-primary">TZS {{ number_format($proj->starting_price) }}</span>
                                </td>
                                <td>
                                    @if($proj->geojson_data || $proj->shapefile_path)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                            <i class="bi bi-geo-alt-fill me-1"></i> Shapefile Loaded
                                        </span>
                                    @else
                                        <span class="badge bg-light text-muted border">No Shapefile</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $proj->is_published ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $proj->is_published ? 'Published' : 'Draft' }}
                                    </span>
                                </td>
                                <td class="pe-4 text-end">
                                    <a href="{{ route('public.projects.show', $proj->slug) }}" target="_blank" class="btn btn-sm btn-outline-info rounded-circle me-1" title="View Map">
                                        <i class="bi bi-map"></i>
                                    </a>
                                    <a href="{{ route('admin.projects.edit', $proj->id) }}" class="btn btn-sm btn-outline-primary rounded-circle me-1" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('admin.projects.destroy', $proj->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this project?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">No projects added yet. Click "Add Project / Upload Shapefile" to get started.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
