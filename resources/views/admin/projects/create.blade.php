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
                    <select name="project_status" id="projectStatus" class="form-select" required>
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
            <div>
                <label for="projectDescription" class="form-label fw-semibold small">{{ __('admin.field_project_description') }}</label>
                <textarea name="description" id="projectDescription" class="form-control" rows="4" placeholder="{{ __('admin.field_project_description_placeholder') }}">{{ old('description') }}</textarea>
            </div>

        </div>
    </div>

        </div>

        <div class="col-12 col-xl-4">
    <!-- Shapefile upload -->
    <div class="card mb-4 border-primary">
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

    <!-- Plots & pricing -->
    <div class="card mb-4">
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
                    <label for="gracePeriod" class="form-label fw-semibold small">{{ __('admin.field_grace_period') }}</label>
                    <input type="number" min="0" name="grace_period_days" id="gracePeriod" class="form-control" value="{{ old('grace_period_days', 30) }}">
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
