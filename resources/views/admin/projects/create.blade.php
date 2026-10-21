@extends('layouts.app')

@section('title', __('admin.create_project'))

@section('content')
@php
    $projectTypes = [
        'Master-Planned Land' => __('admin.type_master_planned'),
        'Residential Estate' => __('admin.type_residential_estate'),
        'Commercial Plaza' => __('admin.type_commercial_plaza'),
        'Industrial Park' => __('admin.type_industrial_park'),
    ];
    $projectStatuses = [
        'Selling' => __('admin.status_selling'),
        'Upcoming' => __('admin.status_upcoming'),
        'Pre-Launch' => __('admin.status_pre_launch'),
        'Completed' => __('admin.status_completed'),
        'Sold Out' => __('admin.status_sold_out'),
    ];
@endphp

<div class="page-header">
    <div class="min-w-0">
        <h1 class="brand-font">{{ __('admin.create_project') }}</h1>
        <p class="text-muted small mb-0">{{ __('admin.create_project_subtitle') }}</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.projects.index') }}" class="btn btn-light border"><i class="bi bi-arrow-left me-1"></i> {{ __('admin.back_to_list') }}</a>
        <button type="submit" form="projectForm" class="btn btn-primary"><i class="bi bi-check2-circle me-1"></i> {{ __('admin.create_project_button') }}</button>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger d-flex align-items-start gap-2" role="alert">
        <i class="bi bi-exclamation-triangle-fill fs-5"></i>
        <div class="small">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    </div>
@endif

<form action="{{ route('admin.projects.store') }}" method="POST" enctype="multipart/form-data" id="projectForm">
    @csrf

    <div class="row g-4">
        <div class="col-12 col-xl-8">
    <!-- Basics -->
    <div class="card mb-4">
        <div class="card-header brand-font"><i class="bi bi-map me-2 text-primary"></i>{{ __('admin.section_basics') }}</div>
        <div class="card-body">
            <p class="text-muted small">{{ __('admin.required_hint') }}</p>

            <div class="btn-group w-100 mb-3" role="group" aria-label="{{ __('admin.project_category') }}">
                <input type="radio" class="btn-check" name="project_category" id="catLand" value="land" @checked(old('project_category', 'land') === 'land')>
                <label class="btn btn-outline-primary fw-semibold" for="catLand"><i class="bi bi-map me-1"></i>{{ __('admin.project_cat_land') }}</label>
                <input type="radio" class="btn-check" name="project_category" id="catService" value="service" @checked(old('project_category') === 'service')>
                <label class="btn btn-outline-primary fw-semibold" for="catService"><i class="bi bi-briefcase me-1"></i>{{ __('admin.project_cat_service') }}</label>
            </div>

            <div class="row g-3 service-only d-none">
                <div class="col-12 col-md-6">
                    <label for="clientName" class="form-label fw-semibold small">{{ __('admin.field_client_name') }}</label>
                    <input type="text" name="client_name" id="clientName" class="form-control" value="{{ old('client_name') }}" placeholder="{{ __('admin.field_client_name_placeholder') }}" disabled>
                </div>
                <div class="col-12 col-md-6">
                    <label for="serviceCategoryId" class="form-label fw-semibold small">{{ __('admin.field_service_category') }} *</label>
                    <select name="company_service_id" id="serviceCategoryId" class="form-select @error('company_service_id') is-invalid @enderror" disabled>
                        <option value="">{{ __('admin.select_service') }}</option>
                        @foreach(\App\Models\CompanyService::orderBy('display_order')->get() as $svc)
                            <option value="{{ $svc->id }}" @selected(old('company_service_id') == $svc->id)>{{ $svc->title }}</option>
                        @endforeach
                    </select>
                    @error('company_service_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="row g-3">
                <div class="col-12 col-md-8">
                    <label for="projectTitle" class="form-label fw-semibold small">{{ __('admin.field_project_title') }} *</label>
                    <input type="text" name="title" id="projectTitle" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" required placeholder="{{ __('admin.field_project_title_placeholder') }}" autocomplete="off">
                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12 col-md-4">
                    <label for="developerName" class="form-label fw-semibold small">{{ __('admin.developer') }} *</label>
                    <input type="text" name="developer_name" id="developerName" class="form-control" value="{{ old('developer_name') }}" required placeholder="{{ __('admin.field_developer_placeholder') }}" autocomplete="organization">
                </div>
                <div class="col-12 col-sm-6">
                    <label for="projectType" class="form-label fw-semibold small">{{ __('admin.field_project_type') }} *</label>
                    <select name="project_type" id="projectType" class="form-select" required>
                        @foreach($projectTypes as $value => $label)
                            <option value="{{ $value }}" @selected(old('project_type', 'Master-Planned Land') == $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm-6">
                    <label for="projectStatus" class="form-label fw-semibold small">{{ __('admin.field_project_status') }} *</label>
                    <select name="project_status" id="projectStatus" class="form-select" required
                        data-land-options='@json($projectStatuses)'
                        data-service-options='@json(['Upcoming' => __('admin.phase_upcoming'), 'Ongoing' => __('admin.phase_ongoing'), 'Completed' => __('admin.phase_completed')])'>
                        @foreach($projectStatuses as $value => $label)
                            <option value="{{ $value }}" @selected(old('project_status', 'Selling') == $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Location -->
    <div class="card mb-4">
        <div class="card-header brand-font"><i class="bi bi-geo-alt me-2 text-primary"></i>{{ __('admin.section_location') }}</div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label for="locationName" class="form-label fw-semibold small">{{ __('admin.field_location_name') }} *</label>
                    <input type="text" name="location_name" id="locationName" class="form-control @error('location_name') is-invalid @enderror" value="{{ old('location_name') }}" required placeholder="{{ __('admin.field_location_name_placeholder') }}">
                    @error('location_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12 col-md-6">
                    <label for="projectCity" class="form-label fw-semibold small">{{ __('admin.field_city') }} *</label>
                    <input type="text" name="city" id="projectCity" class="form-control" value="{{ old('city') }}" required placeholder="{{ __('admin.field_city_placeholder') }}" autocomplete="address-level2">
                </div>
                <div class="col-12 col-md-4">
                    <label for="projectDistrict" class="form-label fw-semibold small">{{ __('admin.field_district') }}</label>
                    <input type="text" name="district" id="projectDistrict" class="form-control" value="{{ old('district') }}" placeholder="{{ __('admin.field_district_placeholder') }}">
                </div>
                <div class="col-12 col-md-4">
                    <label for="projectRegion" class="form-label fw-semibold small">{{ __('admin.field_region') }}</label>
                    <select name="region" id="projectRegion" class="form-select" autocomplete="address-level1">
                        <option value="">{{ __('admin.select_region') }}</option>
                        @foreach(tz_regions() as $region)
                            <option value="{{ $region }}" @selected(old('region') == $region)>{{ $region }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <label for="projectCouncil" class="form-label fw-semibold small">{{ __('admin.council') }}</label>
                    <input type="text" name="council" id="projectCouncil" class="form-control" value="{{ old('council') }}" placeholder="{{ __('admin.field_council_placeholder') }}">
                </div>
                <div class="col-6">
                    <label for="projectLat" class="form-label fw-semibold small">{{ __('admin.field_latitude') }}</label>
                    <input type="number" step="0.00000001" name="latitude" id="projectLat" class="form-control" value="{{ old('latitude') }}" placeholder="-6.1630">
                </div>
                <div class="col-6">
                    <label for="projectLng" class="form-label fw-semibold small">{{ __('admin.field_longitude') }}</label>
                    <input type="number" step="0.00000001" name="longitude" id="projectLng" class="form-control" value="{{ old('longitude') }}" placeholder="35.7516">
                </div>
            </div>
        </div>
    </div>

    <!-- Description & publishing -->
    <div class="card mb-4">
        <div class="card-header brand-font"><i class="bi bi-card-text me-2 text-primary"></i>{{ __('admin.section_media_description') }}</div>
        <div class="card-body">
            <div class="row g-3 mb-3">
                <div class="col-12 col-md-6">
                    <label for="heroImageFile" class="form-label fw-semibold small">{{ __('admin.project_image_upload') }}</label>
                    <input type="file" name="hero_image_file" id="heroImageFile" class="form-control @error('hero_image_file') is-invalid @enderror" accept="image/*">
                    @error('hero_image_file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">{{ __('admin.project_image_hint') }}</div>
                </div>
                <div class="col-12 col-md-6">
                    <label for="heroImageUrl" class="form-label fw-semibold small">{{ __('admin.project_image_url') }}</label>
                    <input type="url" name="hero_image" id="heroImageUrl" class="form-control" value="{{ old('hero_image') }}" placeholder="https://...">
                </div>
            </div>
            <div>
                <label for="projectDescription" class="form-label fw-semibold small">{{ __('admin.field_project_description') }}</label>
                <textarea name="description" id="projectDescription" class="form-control" rows="4" placeholder="{{ __('admin.field_project_description_placeholder') }}">{{ old('description') }}</textarea>
            </div>

        </div>
    </div>

        </div>

        <div class="col-12 col-xl-4">
    <!-- Shapefile upload -->
    <div class="card mb-4 border-primary land-only">
        <div class="card-header brand-font d-flex align-items-center gap-2">
            <span class="btn-icon bg-primary text-white"><i class="bi bi-file-earmark-zip-fill"></i></span>
            <span>{{ __('admin.shapefile_upload') }}</span>
        </div>
        <div class="card-body">
            <p class="text-secondary small">{{ __('admin.shapefile_hint') }}</p>
            <label for="shapefileInput" class="form-label fw-semibold small">{{ __('admin.select_file') }}</label>
            <input type="file" name="shapefile" id="shapefileInput" class="form-control @error('shapefile') is-invalid @enderror" accept=".zip,.geojson,.json,.kml">
            @error('shapefile')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    <!-- Gallery (service projects) -->
    <div class="card mb-4 service-only d-none">
        <div class="card-header brand-font"><i class="bi bi-images me-2 text-primary"></i>{{ __('admin.section_gallery') }}</div>
        <div class="card-body">
            <label for="galleryFiles" class="form-label fw-semibold small">{{ __('admin.gallery_upload') }}</label>
            <input type="file" name="gallery_files[]" id="galleryFiles" class="form-control @error('gallery_files.*') is-invalid @enderror" accept="image/*" multiple disabled>
            @error('gallery_files.*')<div class="invalid-feedback">{{ $message }}</div>@enderror
            <div class="form-text">{{ __('admin.gallery_hint') }}</div>
        </div>
    </div>

    <!-- Plots & pricing -->
    <div class="card mb-4 land-only">
        <div class="card-header brand-font"><i class="bi bi-grid-3x3 me-2 text-primary"></i>{{ __('admin.section_plots_pricing') }}</div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12">
                    <label for="startingPrice" class="form-label fw-semibold small">{{ __('admin.starting_price') }} *</label>
                    <div class="input-group">
                        <span class="input-group-text">TSh</span>
                        <input type="text" inputmode="decimal" name="starting_price" id="startingPrice" class="form-control fw-bold @error('starting_price') is-invalid @enderror" value="{{ old('starting_price') }}" required placeholder="15000000">
                    </div>
                    @error('starting_price')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="col-6">
                    <label for="initialPayment" class="form-label fw-semibold small">{{ __('admin.field_initial_payment') }}</label>
                    <div class="input-group">
                        <input type="number" min="0" max="100" name="initial_payment_percent" id="initialPayment" class="form-control" value="{{ old('initial_payment_percent', 50) }}">
                        <span class="input-group-text">%</span>
                    </div>
                </div>
                <div class="col-6">
                    <label for="totalUnits" class="form-label fw-semibold small">{{ __('admin.field_total_plots') }}</label>
                    <input type="number" min="0" name="total_units" id="totalUnits" class="form-control" value="{{ old('total_units', 50) }}">
                </div>
                <div class="col-6">
                    <label for="availableUnits" class="form-label fw-semibold small">{{ __('admin.field_available_plots') }}</label>
                    <input type="number" min="0" name="available_units" id="availableUnits" class="form-control" value="{{ old('available_units', 50) }}">
                </div>
                <div class="col-12">
                    <label for="soldPlots" class="form-label fw-semibold small">{{ __('admin.field_sold_plots') }}</label>
                    <input type="number" min="0" name="sold_plots" id="soldPlots" class="form-control" value="{{ old('sold_plots', 0) }}">
                </div>
                <div class="col-12">
                    <label for="projectAmenities" class="form-label fw-semibold small">{{ __('admin.field_amenities') }}</label>
                    <input type="text" name="amenities" id="projectAmenities" class="form-control" value="{{ old('amenities') }}" placeholder="{{ __('admin.field_amenities_placeholder') }}">
                    <div class="form-text">{{ __('admin.field_amenities_hint') }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Publishing -->
    <div class="card mb-4">
        <div class="card-header brand-font"><i class="bi bi-rocket-takeoff me-2 text-primary"></i>{{ __('admin.section_publishing') }}</div>
        <div class="card-body">
            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" name="is_published" id="isPublished" value="1" @checked(old('is_published', true))>
                <label class="form-check-label fw-semibold" for="isPublished">{{ __('admin.publish_immediately') }}</label>
            </div>
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary fw-bold"><i class="bi bi-check2-circle me-1"></i> {{ __('admin.create_project_button') }}</button>
                <a href="{{ route('admin.projects.index') }}" class="btn btn-light border">{{ __('app.cancel') }}</a>
            </div>
        </div>
    </div>
        </div>
    </div>
</form>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const statusSelect = document.getElementById('projectStatus');
    const landOptions = JSON.parse(statusSelect.dataset.landOptions);
    const serviceOptions = JSON.parse(statusSelect.dataset.serviceOptions);

    function applyCategory() {
        const isService = document.getElementById('catService').checked;

        document.querySelectorAll('.land-only').forEach(function (el) {
            el.classList.toggle('d-none', isService);
            el.querySelectorAll('input, select, textarea').forEach(function (input) { input.disabled = isService; });
        });
        document.querySelectorAll('.service-only').forEach(function (el) {
            el.classList.toggle('d-none', !isService);
            el.querySelectorAll('input, select, textarea').forEach(function (input) { input.disabled = !isService; });
        });

        const locationName = document.getElementById('locationName');
        if (locationName) { locationName.required = !isService; }
        const startingPrice = document.getElementById('startingPrice');
        if (startingPrice) { startingPrice.required = !isService; }

        const current = statusSelect.value;
        const options = isService ? serviceOptions : landOptions;
        statusSelect.innerHTML = '';
        Object.entries(options).forEach(function ([value, label]) {
            const opt = new Option(label, value, false, value === current);
            statusSelect.add(opt);
        });
    }

    document.getElementById('catLand').addEventListener('change', applyCategory);
    document.getElementById('catService').addEventListener('change', applyCategory);
    applyCategory();
});
</script>
@endsection
