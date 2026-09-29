@extends('layouts.app')

@section('title', 'Add Project & Upload Shapefile - Admin Panel')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">Add Land Project & Upload Shapefile</h3>
            <p class="text-muted small mb-0">Create project details and upload ESRI Shapefile (.zip), GeoJSON, or KML format files.</p>
        </div>
        <a href="{{ route('admin.projects.index') }}" class="btn btn-outline-secondary rounded-pill">
            <i class="bi bi-arrow-left me-1"></i> Back to List
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-4 p-4 max-w-4xl">
        <form action="{{ route('admin.projects.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row g-3">
                <!-- Shapefile Upload Section (Highlighted Box) -->
                <div class="col-12">
                    <div class="p-4 bg-primary bg-opacity-10 border border-primary-subtle rounded-4 mb-2">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="bi bi-file-earmark-zip-fill text-primary fs-3"></i>
                            <h5 class="fw-bold text-dark mb-0">GIS Shapefile Upload (.zip, .geojson, .kml)</h5>
                        </div>
                        <p class="text-muted small mb-3">Upload a compressed ZIP folder containing Shapefiles (.shp, .dbf, .prj) or a standard GeoJSON file. The system will extract plot boundaries and coordinates automatically!</p>
                        <input type="file" name="shapefile" class="form-control" accept=".zip,.geojson,.json,.kml">
                    </div>
                </div>

                <div class="col-md-8">
                    <label class="form-label fw-bold">Project Title *</label>
                    <input type="text" name="title" class="form-control" required placeholder="e.g. MAISAKA KATANI PROJECT">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold">Developer / Authority Name *</label>
                    <input type="text" name="developer_name" class="form-control" required placeholder="e.g. Babati Urban Development Authority">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold">Project Type *</label>
                    <select name="project_type" class="form-select" required>
                        <option value="Master-Planned Land">Master-Planned Land</option>
                        <option value="Residential Estate">Residential Estate</option>
                        <option value="Commercial Plaza">Commercial Plaza</option>
                        <option value="Industrial Park">Industrial Park</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold">Project Status *</label>
                    <select name="project_status" class="form-select" required>
                        <option value="Selling">Selling</option>
                        <option value="Upcoming">Upcoming</option>
                        <option value="Pre-Launch">Pre-Launch</option>
                        <option value="Completed">Completed</option>
                        <option value="Sold Out">Sold Out</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold">Starting Price (TZS) *</label>
                    <input type="number" name="starting_price" class="form-control" required placeholder="15000000" min="0">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold">City / Location Name *</label>
                    <input type="text" name="city" class="form-control" required placeholder="e.g. Babati or Kibaha">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold">Region</label>
                    <input type="text" name="region" class="form-control" placeholder="e.g. Manyara">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold">Council / Authority</label>
                    <input type="text" name="council" class="form-control" placeholder="e.g. Halmashauri ya Mji Babati">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold">Total Plots</label>
                    <input type="number" name="total_units" class="form-control" value="50">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold">Available Plots</label>
                    <input type="number" name="available_units" class="form-control" value="30">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold">Initial Payment %</label>
                    <input type="number" name="initial_payment_percent" class="form-control" value="50">
                </div>

                <div class="col-12">
                    <label class="form-label fw-bold">Project Description</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Location amenities, distance from SGR/highway, water, electricity details"></textarea>
                </div>

                <div class="col-12">
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" name="is_published" id="isPublished" value="1" checked>
                        <label class="form-check-label fw-semibold" for="isPublished">Publish Project Immediately</label>
                    </div>
                </div>

                <div class="col-12 pt-3">
                    <button type="submit" class="btn btn-primary fw-bold px-5 rounded-pill">Create Project & Parse Shapefile</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
