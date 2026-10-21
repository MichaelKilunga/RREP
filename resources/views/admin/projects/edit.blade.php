@extends('layouts.app')

@section('title', __('admin.edit_project').' - '.$project->title)

@section('content')
@php
    $projectTypes = \App\Models\TaxonomyTerm::options('project_type', ['Master-Planned Land', 'Residential Estate', 'Commercial Plaza', 'Industrial Park']);
    $projectStatuses = [
        'Selling' => __('admin.status_selling'),
        'Upcoming' => __('admin.status_upcoming'),
        'Pre-Launch' => __('admin.status_pre_launch'),
        'Completed' => __('admin.status_completed'),
        'Sold Out' => __('admin.status_sold_out'),
    ];
    $regions = tz_regions();
    $currentRegion = old('region', $project->region);
    $total = max((int) $project->total_units, 0);
    $sold = max((int) $project->sold_plots, 0);
    $soldPercent = $total > 0 ? min(100, (int) round($sold / $total * 100)) : 0;
@endphp

<div class="page-header">
    <div class="min-w-0">
        <h1 class="brand-font text-truncate">{{ __('admin.edit_project') }}: {{ $project->title }}</h1>
        <p class="text-muted small mb-0">{{ __('admin.edit_project_subtitle') }}</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.projects.index') }}" class="btn btn-light border"><i class="bi bi-arrow-left me-1"></i> {{ __('admin.back_to_list') }}</a>
        <button type="submit" form="projectEditForm" class="btn btn-primary"><i class="bi bi-check2-circle me-1"></i> {{ __('app.save') }}</button>
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

<div class="row g-4">
    <div class="col-lg-8">
        <form action="{{ route('admin.projects.update', $project) }}" method="POST" enctype="multipart/form-data" id="projectEditForm">
            @csrf
            @method('PUT')

            <!-- Shapefile -->
            <div class="card land-only mb-4 border-primary">
                <div class="card-header brand-font d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <span class="d-flex align-items-center gap-2">
                        <span class="btn-icon bg-primary text-white"><i class="bi bi-file-earmark-zip-fill"></i></span>
                        {{ __('admin.shapefile_upload') }}
                    </span>
                    @if($project->geojson_data || $project->shapefile_path)
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill"><i class="bi bi-check-circle-fill me-1"></i>{{ __('admin.shapefile_active') }}</span>
                    @endif
                </div>
                <div class="card-body">
                    <p class="text-secondary small">{{ __('admin.shapefile_hint') }} {{ __('admin.shapefile_replace_hint') }}</p>
                    <label for="shapefileInput" class="form-label fw-semibold small">{{ __('admin.select_file') }}</label>
                    <input type="file" name="shapefile" id="shapefileInput" class="form-control @error('shapefile') is-invalid @enderror" accept=".zip,.geojson,.json,.kml">
                    @error('shapefile')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <!-- Basics -->
            <div class="card mb-4">
                <div class="card-header brand-font"><i class="bi bi-map me-2 text-primary"></i>{{ __('admin.section_basics') }}</div>
                <div class="card-body">
                    <div class="btn-group w-100 mb-3" role="group" aria-label="{{ __('admin.project_category') }}">
                        <input type="radio" class="btn-check" name="project_category" id="catLand" value="land" @checked(old('project_category', $project->project_category ?? 'land') === 'land')>
                        <label class="btn btn-outline-primary fw-semibold" for="catLand"><i class="bi bi-map me-1"></i>{{ __('admin.project_cat_land') }}</label>
                        <input type="radio" class="btn-check" name="project_category" id="catService" value="service" @checked(old('project_category', $project->project_category) === 'service')>
                        <label class="btn btn-outline-primary fw-semibold" for="catService"><i class="bi bi-briefcase me-1"></i>{{ __('admin.project_cat_service') }}</label>
                    </div>

                    <div class="row g-3 service-only d-none mb-0">
                        <div class="col-12 col-md-6">
                            <label for="clientName" class="form-label fw-semibold small">{{ __('admin.field_client_name') }}</label>
                            <input type="text" name="client_name" id="clientName" class="form-control" value="{{ old('client_name', $project->client_name) }}" placeholder="{{ __('admin.field_client_name_placeholder') }}" disabled>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="serviceCategoryId" class="form-label fw-semibold small">{{ __('admin.field_service_category') }} *</label>
                            <select name="company_service_id" id="serviceCategoryId" class="form-select @error('company_service_id') is-invalid @enderror" disabled>
                                <option value="">{{ __('admin.select_service') }}</option>
                                @foreach(\App\Models\CompanyService::orderBy('display_order')->get() as $svc)
                                    <option value="{{ $svc->id }}" @selected(old('company_service_id', $project->company_service_id) == $svc->id)>{{ $svc->title }}</option>
                                @endforeach
                            </select>
                            @error('company_service_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-12 col-md-8">
                            <label for="projectTitle" class="form-label fw-semibold small">{{ __('admin.field_project_title') }} *</label>
                            <input type="text" name="title" id="projectTitle" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $project->title) }}" required autocomplete="off">
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="developerName" class="form-label fw-semibold small">{{ __('admin.developer') }} *</label>
                            <input type="text" name="developer_name" id="developerName" class="form-control" value="{{ old('developer_name', $project->developer_name) }}" required autocomplete="organization">
                        </div>
                        <div class="col-12 col-sm-6">
                            <label for="projectType" class="form-label fw-semibold small">{{ __('admin.field_project_type') }} *</label>
                            <select name="project_type" id="projectType" class="form-select" required>
                                @foreach($projectTypes as $type)
                                    <option value="{{ $type }}" @selected(old('project_type', $project->project_type) == $type)>{{ $type }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label for="projectStatus" class="form-label fw-semibold small">{{ __('admin.field_project_status') }} *</label>
                            <select name="project_status" id="projectStatus" class="form-select" required
                                data-current="{{ old('project_status', $project->project_status) }}"
                                data-land-options='@json($projectStatuses)'
                                data-service-options='@json(['Upcoming' => __('admin.phase_upcoming'), 'Ongoing' => __('admin.phase_ongoing'), 'Completed' => __('admin.phase_completed')])'>
                                @foreach($projectStatuses as $value => $label)
                                    <option value="{{ $value }}" @selected(old('project_status', $project->project_status) == $value)>{{ $label }}</option>
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
                            <input type="text" name="location_name" id="locationName" class="form-control @error('location_name') is-invalid @enderror" value="{{ old('location_name', $project->location_name ?? $project->city) }}" required placeholder="{{ __('admin.field_location_name_placeholder') }}">
                            @error('location_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="projectCity" class="form-label fw-semibold small">{{ __('admin.field_city') }} *</label>
                            <input type="text" name="city" id="projectCity" class="form-control" value="{{ old('city', $project->city) }}" required autocomplete="address-level2">
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="projectDistrict" class="form-label fw-semibold small">{{ __('admin.field_district') }}</label>
                            <input type="text" name="district" id="projectDistrict" class="form-control" value="{{ old('district', $project->district) }}" placeholder="{{ __('admin.field_district_placeholder') }}">
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="projectRegion" class="form-label fw-semibold small">{{ __('admin.field_region') }}</label>
                            <select name="region" id="projectRegion" class="form-select" autocomplete="address-level1">
                                <option value="">{{ __('admin.select_region') }}</option>
                                @if($currentRegion && ! in_array($currentRegion, $regions, true))
                                    <option value="{{ $currentRegion }}" selected>{{ $currentRegion }}</option>
                                @endif
                                @foreach($regions as $region)
                                    <option value="{{ $region }}" @selected($currentRegion == $region)>{{ $region }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="projectCouncil" class="form-label fw-semibold small">{{ __('admin.council') }}</label>
                            <input type="text" name="council" id="projectCouncil" class="form-control" value="{{ old('council', $project->council) }}" placeholder="{{ __('admin.field_council_placeholder') }}">
                        </div>
                        <div class="col-6">
                            <label for="projectLat" class="form-label fw-semibold small">{{ __('admin.field_latitude') }}</label>
                            <input type="number" step="0.00000001" name="latitude" id="projectLat" class="form-control" value="{{ old('latitude', $project->latitude) }}">
                        </div>
                        <div class="col-6">
                            <label for="projectLng" class="form-label fw-semibold small">{{ __('admin.field_longitude') }}</label>
                            <input type="number" step="0.00000001" name="longitude" id="projectLng" class="form-control" value="{{ old('longitude', $project->longitude) }}">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Plots & pricing -->
            <div class="card land-only mb-4">
                <div class="card-header brand-font"><i class="bi bi-grid-3x3 me-2 text-primary"></i>{{ __('admin.section_plots_pricing') }}</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label for="startingPrice" class="form-label fw-semibold small">{{ __('admin.starting_price') }}</label>
                            <div class="input-group">
                                <span class="input-group-text">TSh</span>
                                <input type="text" inputmode="decimal" name="starting_price" id="startingPrice" class="form-control fw-bold @error('starting_price') is-invalid @enderror" value="{{ old('starting_price', (int) $project->starting_price) }}" required>
                            </div>
                            @error('starting_price')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6 d-flex flex-column justify-content-end">
                            <div class="small text-muted mb-1">
                                <span class="fw-bold text-danger">{{ $sold }}</span> {{ __('admin.plots_sold') }} &bull;
                                <span class="fw-bold text-success">{{ max((int) $project->available_units, 0) }}</span> {{ __('admin.plots_available') }} &bull;
                                <span class="fw-bold">{{ $total }}</span> {{ __('admin.plots_total') }}
                            </div>
                            <div class="progress" role="progressbar" aria-label="{{ __('app.plot_allocation_progress') }}" aria-valuenow="{{ $soldPercent }}" aria-valuemin="0" aria-valuemax="100" style="height: 8px;">
                                <div class="progress-bar bg-danger" style="width: {{ $soldPercent }}%"></div>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <label for="totalUnits" class="form-label fw-semibold small">{{ __('admin.field_total_plots') }}</label>
                            <input type="number" min="0" name="total_units" id="totalUnits" class="form-control" value="{{ old('total_units', $project->total_units) }}">
                        </div>
                        <div class="col-6 col-md-4">
                            <label for="availableUnits" class="form-label fw-semibold small">{{ __('admin.field_available_plots') }}</label>
                            <input type="number" min="0" name="available_units" id="availableUnits" class="form-control" value="{{ old('available_units', $project->available_units) }}">
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="soldPlots" class="form-label fw-semibold small">{{ __('admin.field_sold_plots') }}</label>
                            <input type="number" min="0" name="sold_plots" id="soldPlots" class="form-control" value="{{ old('sold_plots', $project->sold_plots) }}">
                        </div>
                        <div class="col-6">
                            <label for="initialPayment" class="form-label fw-semibold small">{{ __('admin.field_initial_payment') }}</label>
                            <div class="input-group">
                                <input type="number" min="0" max="100" name="initial_payment_percent" id="initialPayment" class="form-control" value="{{ old('initial_payment_percent', (int) $project->initial_payment_percent) }}">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <label for="applicationFee" class="form-label fw-semibold small">{{ __('admin.field_application_fee') }}</label>
                            <div class="input-group">
                                <span class="input-group-text">TSh</span>
                                <input type="number" min="0" step="any" inputmode="decimal" name="application_fee" id="applicationFee" class="form-control" value="{{ old('application_fee', $project->application_fee === null ? '' : (int) $project->application_fee) }}">
                            </div>
                            <div class="form-text">{{ __('admin.field_application_fee_help') }}</div>
                        </div>
                        <div class="col-12">
                            <label for="pricePerSqm" class="form-label fw-semibold small">{{ __('admin.field_price_per_sqm') }}</label>
                            <div class="input-group">
                                <span class="input-group-text">TSh</span>
                                <input type="number" min="0" step="any" inputmode="decimal" name="price_per_sqm" id="pricePerSqm" class="form-control" value="{{ old('price_per_sqm', $project->price_per_sqm === null ? '' : (float) $project->price_per_sqm) }}" placeholder="15000">
                                <span class="input-group-text">/ m&sup2;</span>
                            </div>
                            <div class="form-text">{{ __('admin.field_price_per_sqm_help') }}</div>
                        </div>
                        <div class="col-12">
                            <label for="projectAmenities" class="form-label fw-semibold small">{{ __('admin.field_amenities') }}</label>
                            <input type="text" name="amenities" id="projectAmenities" class="form-control" value="{{ old('amenities', implode(', ', (array) $project->amenities_json)) }}" placeholder="{{ __('admin.field_amenities_placeholder') }}">
                            <div class="form-text">{{ __('admin.field_amenities_hint') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Description & publishing -->
            <div class="card mb-4">
                <div class="card-header brand-font"><i class="bi bi-card-text me-2 text-primary"></i>{{ __('admin.section_media_description') }}</div>
                <div class="card-body">
                    <div class="row g-3 align-items-end mb-3">
                        <div class="col-12 col-md-4">
                            <div class="ratio ratio-16x9 rounded-3 overflow-hidden border bg-light">
                                <img src="{{ $project->hero_image_url }}" id="heroImagePreview" class="object-fit-cover" alt="{{ $project->title }}">
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="heroImageFile" class="form-label fw-semibold small">{{ __('admin.project_image_upload') }}</label>
                            <input type="file" name="hero_image_file" id="heroImageFile" class="form-control @error('hero_image_file') is-invalid @enderror" accept="image/*" data-preview-target="#heroImagePreview">
                            @error('hero_image_file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="heroImageUrl" class="form-label fw-semibold small">{{ __('admin.project_image_url') }}</label>
                            <input type="url" name="hero_image" id="heroImageUrl" class="form-control" value="{{ old('hero_image', $project->hero_image) }}" placeholder="https://...">
                        </div>
                    </div>

                    <div class="service-only d-none mb-3">
                        <label for="galleryFiles" class="form-label fw-semibold small">{{ __('admin.section_gallery') }}</label>
                        @if(count($project->gallery))
                            <div class="row g-2 mb-2">
                                @foreach($project->gallery as $image)
                                    <div class="col-4 col-md-3">
                                        <div class="border rounded-3 overflow-hidden position-relative">
                                            <div class="ratio ratio-4x3"><img src="{{ $image }}" class="object-fit-cover" alt="Gallery {{ $loop->iteration }}" loading="lazy"></div>
                                            <label class="position-absolute top-0 end-0 m-1 badge bg-danger d-flex align-items-center gap-1" style="cursor: pointer;">
                                                <input type="checkbox" name="remove_gallery[]" value="{{ $image }}" class="form-check-input m-0"> <i class="bi bi-trash"></i>
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="form-text mb-2">{{ __('admin.gallery_remove_hint') }}</div>
                        @endif
                        <input type="file" name="gallery_files[]" id="galleryFiles" class="form-control @error('gallery_files.*') is-invalid @enderror" accept="image/*" multiple disabled>
                        @error('gallery_files.*')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="form-text">{{ __('admin.gallery_hint') }}</div>
                    </div>
                    <div class="mb-3">
                        <label for="projectDescription" class="form-label fw-semibold small">{{ __('admin.field_project_description') }}</label>
                        <textarea name="description" id="projectDescription" class="form-control" rows="4" placeholder="{{ __('admin.field_project_description_placeholder') }}">{{ old('description', $project->description) }}</textarea>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_published" id="isPublished" value="1" @checked(old('is_published', $project->is_published))>
                        <label class="form-check-label fw-semibold" for="isPublished">{{ __('admin.publish_immediately') }}</label>
                    </div>
                </div>
            </div>

            <div class="d-grid d-sm-flex gap-2 justify-content-sm-end">
                <a href="{{ route('admin.projects.index') }}" class="btn btn-light border">{{ __('app.cancel') }}</a>
                <button type="submit" class="btn btn-primary btn-lg fw-bold"><i class="bi bi-check2-circle me-1"></i> {{ __('app.save') }}</button>
            </div>
        </form>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header brand-font d-flex justify-content-between align-items-center">
                <span><i class="bi bi-grid-3x3-gap me-2 text-primary"></i>{{ __('admin.parcels_list') }}</span>
                <span class="badge bg-light text-dark border">{{ $parcels->count() }}</span>
            </div>
            @if($parcels->isEmpty())
                <div class="empty-state py-4">
                    <div class="empty-icon"><i class="bi bi-bounding-box"></i></div>
                    <p class="small mb-0">{{ __('admin.no_parcels') }}</p>
                </div>
            @else
                <div class="table-responsive overflow-auto" style="max-height: 420px;">
                    <table class="table table-sm table-stack align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('admin.plot') }}</th>
                                <th>{{ __('admin.block') }}</th>
                                <th>{{ __('app.status') }}</th>
                                <th>{{ __('app.price') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($parcels as $p)
                                <tr>
                                    <td class="stack-primary fw-bold">#{{ $p->parcel_number }}</td>
                                    <td>{{ $p->block_number ?? '-' }}</td>
                                    <td><span class="badge {{ $p->status === 'Sold' ? 'bg-danger' : 'bg-success' }}">{{ $p->status === 'Sold' ? __('admin.status_sold') : __('admin.status_available') }}</span></td>
                                    <td class="small" title="{{ format_currency($p->price) }}">{{ format_money_short($p->price) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const statusSelect = document.getElementById('projectStatus');
    const landOptions = JSON.parse(statusSelect.dataset.landOptions);
    const serviceOptions = JSON.parse(statusSelect.dataset.serviceOptions);
    const initial = statusSelect.dataset.current;

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
        if (startingPrice) { startingPrice.required = false; }

        const current = statusSelect.value || initial;
        const options = isService ? serviceOptions : landOptions;
        statusSelect.innerHTML = '';
        Object.entries(options).forEach(function ([value, label]) {
            statusSelect.add(new Option(label, value, false, value === current));
        });
        if (!statusSelect.value && Object.keys(options).includes(initial)) { statusSelect.value = initial; }
    }

    document.getElementById('catLand').addEventListener('change', applyCategory);
    document.getElementById('catService').addEventListener('change', applyCategory);
    applyCategory();
});
</script>
@endsection
