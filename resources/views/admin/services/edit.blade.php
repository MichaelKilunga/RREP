@extends('layouts.app')

@section('title', 'Edit Service - Admin Panel')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">Edit Service: {{ $service->title }}</h3>
            <p class="text-muted small mb-0">Update service details and display configuration.</p>
        </div>
        <a href="{{ route('admin.services.index') }}" class="btn btn-outline-secondary rounded-pill">
            <i class="bi bi-arrow-left me-1"></i> Back to List
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-4 p-4 max-w-3xl">
        <form action="{{ route('admin.services.update', $service->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label fw-bold">Service Title *</label>
                    <input type="text" name="title" class="form-control" value="{{ $service->title }}" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold">Category *</label>
                    <select name="category" class="form-select" required>
                        @foreach(['Land', 'Surveying', 'Planning', 'Consultancy', 'Legal', 'Development', 'Property'] as $cat)
                            <option value="{{ $cat }}" {{ $service->category == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-8">
                    <label class="form-label fw-bold">Bootstrap Icon Class *</label>
                    <input type="text" name="icon" class="form-control" value="{{ $service->icon }}" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold">Display Order</label>
                    <input type="number" name="display_order" class="form-control" value="{{ $service->display_order }}" min="0">
                </div>

                <div class="col-12">
                    <label class="form-label fw-bold">Short Description *</label>
                    <textarea name="short_description" class="form-control" rows="2" required>{{ $service->short_description }}</textarea>
                </div>

                <div class="col-12">
                    <label class="form-label fw-bold">Sub-Services (Comma Separated)</label>
                    <input type="text" name="sub_services" class="form-control" value="{{ is_array($service->sub_services) ? implode(', ', $service->sub_services) : '' }}">
                </div>

                <div class="col-12">
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" name="is_active" id="isActive" value="1" {{ $service->is_active ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="isActive">Publish & Active on Website</label>
                    </div>
                </div>

                <div class="col-12 pt-3">
                    <button type="submit" class="btn btn-primary fw-bold px-5 rounded-pill">Update Service</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
