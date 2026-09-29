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
                            <div class="p-3 bg-light border rounded-3 mb-2">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-file-earmark-zip text-primary fs-4"></i>
                                        <span class="fw-bold">Upload / Replace Shapefile (.zip, .geojson)</span>
                                    </div>
                                    @if($project->geojson_data)
                                        <span class="badge bg-success">Shapefile Loaded</span>
                                    @endif
                                </div>
                                <input type="file" name="shapefile" class="form-control mt-2" accept=".zip,.geojson,.json,.kml">
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
