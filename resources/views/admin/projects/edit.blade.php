@extends('layouts.app')

@section('title', 'Edit Project & Shapefile - Admin Panel')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">Edit Project: {{ $project->title }}</h3>
            <p class="text-muted small mb-0">Update project attributes, upload updated Shapefile, or inspect plot parcels.</p>
        </div>
        <a href="{{ route('admin.projects.index') }}" class="btn btn-outline-secondary rounded-pill">
            <i class="bi bi-arrow-left me-1"></i> Back to List
        </a>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 p-4">
                <form action="{{ route('admin.projects.update', $project->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="p-4 bg-white border border-2 border-primary rounded-4 shadow-sm mb-3">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded-3 p-2.5 bg-primary text-white shadow-sm d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                                            <i class="bi bi-file-earmark-zip-fill fs-4"></i>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold text-dark mb-0 fs-5">Upload / Replace GIS Shapefile (.zip, .geojson)</h6>
                                            <small class="text-muted">Overwrites existing boundary coordinates with new Shapefile data</small>
                                        </div>
                                    </div>
                                    @if($project->geojson_data)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1.5 rounded-pill fw-bold">
                                            <i class="bi bi-check-circle-fill me-1"></i> GIS Shapefile Active
                                        </span>
                                    @endif
                                </div>
                                <div class="p-3 bg-light rounded-3 border border-secondary-subtle">
                                    <label class="form-label fw-bold text-dark mb-1 small"><i class="bi bi-upload text-primary me-1"></i> Select New GIS Archive File:</label>
                                    <input type="file" name="shapefile" class="form-control form-control-lg bg-white border text-dark font-monospace small" accept=".zip,.geojson,.json,.kml">
                                    <small class="text-muted d-block mt-1"><i class="bi bi-info-circle me-1"></i> Supported: <code>.zip</code> (ESRI Shapefiles), <code>.geojson</code>, <code>.kml</code></small>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-8">
                            <label class="form-label fw-bold">Project Title *</label>
                            <input type="text" name="title" class="form-control" value="{{ $project->title }}" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Developer / Authority Name *</label>
                            <input type="text" name="developer_name" class="form-control" value="{{ $project->developer_name }}" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Project Type *</label>
                            <select name="project_type" class="form-select" required>
                                @foreach(['Master-Planned Land', 'Residential Estate', 'Commercial Plaza', 'Industrial Park'] as $pt)
                                    <option value="{{ $pt }}" {{ $project->project_type == $pt ? 'selected' : '' }}>{{ $pt }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Project Status *</label>
                            <select name="project_status" class="form-select" required>
                                @foreach(['Selling', 'Upcoming', 'Pre-Launch', 'Completed', 'Sold Out'] as $ps)
                                    <option value="{{ $ps }}" {{ $project->project_status == $ps ? 'selected' : '' }}>{{ $ps }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Starting Price (TZS) *</label>
                            <input type="number" name="starting_price" class="form-control" value="{{ (int)$project->starting_price }}" required min="0">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">City / Location *</label>
                            <input type="text" name="city" class="form-control" value="{{ $project->city }}" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Region</label>
                            <input type="text" name="region" class="form-control" value="{{ $project->region }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Council / Authority</label>
                            <input type="text" name="council" class="form-control" value="{{ $project->council }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Total Plots</label>
                            <input type="number" name="total_units" class="form-control" value="{{ $project->total_units }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Available Plots</label>
                            <input type="number" name="available_units" class="form-control" value="{{ $project->available_units }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Sold Plots</label>
                            <input type="number" name="sold_plots" class="form-control" value="{{ $project->sold_plots }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold">Project Description</label>
                            <textarea name="description" class="form-control" rows="3">{{ $project->description }}</textarea>
                        </div>

                        <div class="col-12 pt-3">
                            <button type="submit" class="btn btn-primary fw-bold px-5 rounded-pill">Save Changes</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-4">
                <h5 class="fw-bold text-dark mb-3">Parcels / Plots List ({{ $parcels->count() }})</h5>
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-sm text-center align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th>Plot</th>
                                <th>Block</th>
                                <th>Status</th>
                                <th>Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($parcels as $p)
                                <tr>
                                    <td class="fw-bold">#{{ $p->parcel_number }}</td>
                                    <td>{{ $p->block_number ?? 'A' }}</td>
                                    <td>
                                        <span class="badge {{ $p->status === 'Sold' ? 'bg-danger' : 'bg-success' }}" style="font-size: 0.65rem;">
                                            {{ $p->status }}
                                        </span>
                                    </td>
                                    <td class="small">{{ number_format($p->price) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-muted small">No parcels extracted. Upload a .zip shapefile to extract plots.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
