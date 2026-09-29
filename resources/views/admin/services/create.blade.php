@extends('layouts.app')

@section('title', 'Create Service - Admin Panel')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">Create New Service</h3>
            <p class="text-muted small mb-0">Add a new company service to display on the public website.</p>
        </div>
        <a href="{{ route('admin.services.index') }}" class="btn btn-outline-secondary rounded-pill">
            <i class="bi bi-arrow-left me-1"></i> Back to List
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-4 p-4 max-w-3xl">
        <form action="{{ route('admin.services.store') }}" method="POST">
            @csrf
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label fw-bold">Service Title *</label>
                    <input type="text" name="title" class="form-control" required placeholder="e.g. Land Sales or Surveying">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold">Category *</label>
                    <select name="category" class="form-select" required>
                        <option value="Land">Land</option>
                        <option value="Surveying">Surveying</option>
                        <option value="Planning">Planning</option>
                        <option value="Consultancy">Consultancy</option>
                        <option value="Legal">Legal</option>
                        <option value="Development">Development</option>
                        <option value="Property">Property</option>
                    </select>
                </div>

                <div class="col-md-8">
                    <label class="form-label fw-bold">Bootstrap Icon Class *</label>
                    <input type="text" name="icon" class="form-control" value="bi-geo-alt-fill" required placeholder="e.g. bi-compass-fill, bi-rulers">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold">Display Order</label>
                    <input type="number" name="display_order" class="form-control" value="1" min="0">
                </div>

                <div class="col-12">
                    <label class="form-label fw-bold">Short Description *</label>
                    <textarea name="short_description" class="form-control" rows="2" required placeholder="Concise 1-2 sentence description for quick scanning"></textarea>
                </div>

                <div class="col-12">
                    <label class="form-label fw-bold">Sub-Services (Comma Separated)</label>
                    <input type="text" name="sub_services" class="form-control" placeholder="e.g. Cadastral Survey, Engineering Survey, Topographical Survey">
                    <small class="text-muted">Enter sub-services separated by commas.</small>
                </div>

                <div class="col-12">
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" name="is_active" id="isActive" value="1" checked>
                        <label class="form-check-label fw-semibold" for="isActive">Publish & Active on Website</label>
                    </div>
                </div>

                <div class="col-12 pt-3">
                    <button type="submit" class="btn btn-primary fw-bold px-5 rounded-pill">Create Service</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
