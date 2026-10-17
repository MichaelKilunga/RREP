@extends('layouts.app')

@section('title', __('admin.edit_property').' - '.$property->title)

@section('content')
@php
    $statusOptions = [
        'Available' => __('admin.status_available'),
        'Reserved' => __('admin.status_reserved'),
        'Under Contract' => __('admin.status_under_contract'),
        'Sold' => __('admin.status_sold'),
        'Leased' => __('admin.status_leased'),
        'Maintenance' => __('admin.status_maintenance'),
        'Off Market' => __('admin.status_off_market'),
    ];
    $listingOptions = [
        'Sale' => __('admin.listing_sale'),
        'Rent' => __('admin.listing_rent'),
        'Lease' => __('admin.listing_lease'),
        'Joint Venture' => __('admin.listing_joint_venture'),
    ];
    $deedTypes = [
        'Right of Occupancy' => __('admin.deed_granted_ro'),
        'Customary Right of Occupancy' => __('admin.deed_customary_ro'),
        'Freehold' => __('admin.deed_freehold'),
        'Leasehold' => __('admin.deed_leasehold'),
        'Offer Letter' => __('admin.deed_offer_letter'),
        'None' => __('admin.deed_none'),
    ];
    $selectedAmenities = old('amenities', $property->amenities->pluck('id')->all());
    $regions = tz_regions();
    $currentRegion = old('city', $property->city);
    $parcel = $property->landParcel;
@endphp

<div class="page-header">
    <div class="min-w-0">
        <h1 class="brand-font">{{ __('admin.edit_property') }}</h1>
        <p class="text-muted small mb-0 text-truncate">{{ $property->title }} &bull; {{ $property->property_code }}</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.properties.show', $property) }}" class="btn btn-light border"><i class="bi bi-arrow-left me-1"></i> {{ __('admin.back_to_property') }}</a>
        <button type="submit" form="propertyEditForm" class="btn btn-primary"><i class="bi bi-check2-circle me-1"></i> {{ __('app.save') }}</button>
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

<form action="{{ route('admin.properties.update', $property) }}" method="POST" id="propertyEditForm">
    @csrf
    @method('PUT')
    <div class="row g-4">
        <div class="col-lg-8">
            <!-- Basics -->
            <div class="card mb-4">
                <div class="card-header brand-font"><i class="bi bi-house-door me-2 text-primary"></i>{{ __('admin.section_basics') }}</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="propTitle" class="form-label fw-semibold small">{{ __('admin.field_title') }} *</label>
                            <input type="text" name="title" id="propTitle" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $property->title) }}" required autocomplete="off">
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="propType" class="form-label fw-semibold small">{{ __('admin.field_property_type') }} *</label>
                            <select name="property_type_id" id="propType" class="form-select" required>
                                @foreach($types as $t)
                                    <option value="{{ $t->id }}" @selected(old('property_type_id', $property->property_type_id) == $t->id)>{{ $t->name }} ({{ $t->category }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="listingType" class="form-label fw-semibold small">{{ __('admin.field_listing_type') }} *</label>
                            <select name="listing_type" id="listingType" class="form-select" required>
                                @foreach($listingOptions as $value => $label)
                                    <option value="{{ $value }}" @selected(old('listing_type', $property->listing_type) == $value)>{{ $label }}</option>
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
                        <div class="col-12">
                            <label for="propAddress" class="form-label fw-semibold small">{{ __('admin.field_plot_street') }} *</label>
                            <input type="text" name="address" id="propAddress" class="form-control @error('address') is-invalid @enderror" value="{{ old('address', $property->address) }}" placeholder="{{ __('admin.field_plot_street_placeholder') }}" required autocomplete="street-address">
                            @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="propState" class="form-label fw-semibold small">{{ __('admin.field_ward_district') }}</label>
                            <input type="text" name="state" id="propState" class="form-control" value="{{ old('state', $property->state) }}" placeholder="{{ __('admin.field_ward_district_placeholder') }}" autocomplete="address-level2">
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="propCity" class="form-label fw-semibold small">{{ __('admin.field_region') }} *</label>
                            <select name="city" id="propCity" class="form-select" required autocomplete="address-level1">
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
                            <label for="propCountry" class="form-label fw-semibold small">{{ __('admin.field_country') }}</label>
                            <input type="text" id="propCountry" class="form-control" value="{{ $property->country ?: __('admin.tanzania') }}" readonly>
                        </div>
                        <div class="col-6 col-md-4">
                            <label for="propLat" class="form-label fw-semibold small">{{ __('admin.field_latitude') }}</label>
                            <input type="number" step="0.00000001" name="latitude" id="propLat" class="form-control" value="{{ old('latitude', $property->latitude) }}" placeholder="-6.7495">
                        </div>
                        <div class="col-6 col-md-4">
                            <label for="propLng" class="form-label fw-semibold small">{{ __('admin.field_longitude') }}</label>
                            <input type="number" step="0.00000001" name="longitude" id="propLng" class="form-control" value="{{ old('longitude', $property->longitude) }}" placeholder="39.2818">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Specifications -->
            <div class="card mb-4">
                <div class="card-header brand-font"><i class="bi bi-rulers me-2 text-primary"></i>{{ __('admin.section_specs') }}</div>
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-6 col-md-3">
                            <label for="propBeds" class="form-label fw-semibold small">{{ __('admin.field_bedrooms') }}</label>
                            <input type="number" min="0" name="bedrooms" id="propBeds" class="form-control" value="{{ old('bedrooms', $property->bedrooms) }}">
                        </div>
                        <div class="col-6 col-md-3">
                            <label for="propBaths" class="form-label fw-semibold small">{{ __('admin.field_bathrooms') }}</label>
                            <input type="number" min="0" name="bathrooms" id="propBaths" class="form-control" value="{{ old('bathrooms', $property->bathrooms) }}">
                        </div>
                        <div class="col-6 col-md-3">
                            <label for="propArea" class="form-label fw-semibold small">{{ __('admin.field_area_size') }}</label>
                            <input type="text" inputmode="decimal" name="area_size" id="propArea" class="form-control" value="{{ old('area_size', $property->area_size) }}">
                        </div>
                        <div class="col-6 col-md-3">
                            <label for="propAreaUnit" class="form-label fw-semibold small">{{ __('admin.field_area_unit') }}</label>
                            <select name="area_unit" id="propAreaUnit" class="form-select">
                                <option value="Sqm" @selected(old('area_unit', $property->area_unit) == 'Sqm')>{{ __('admin.unit_sqm') }}</option>
                                <option value="Acres" @selected(old('area_unit', $property->area_unit) == 'Acres')>{{ __('admin.unit_acres') }}</option>
                                <option value="Hectares" @selected(old('area_unit', $property->area_unit) == 'Hectares')>{{ __('admin.unit_hectares') }}</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-4">
                            <label for="propFloors" class="form-label fw-semibold small">{{ __('admin.field_floors') }}</label>
                            <input type="number" min="0" name="floors" id="propFloors" class="form-control" value="{{ old('floors', $property->floors) }}">
                        </div>
                        <div class="col-6 col-md-4">
                            <label for="propParking" class="form-label fw-semibold small">{{ __('admin.field_parking') }}</label>
                            <input type="number" min="0" name="parking_spaces" id="propParking" class="form-control" value="{{ old('parking_spaces', $property->parking_spaces) }}">
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="propYear" class="form-label fw-semibold small">{{ __('admin.field_year_built') }}</label>
                            <input type="number" min="1900" max="{{ date('Y') + 5 }}" name="year_built" id="propYear" class="form-control" value="{{ old('year_built', $property->year_built) }}">
                        </div>
                    </div>

                    <div class="form-label fw-semibold small mb-2">{{ __('admin.field_amenities') }}</div>
                    <div class="row g-2">
                        @foreach($amenities as $am)
                            <div class="col-12 col-sm-6 col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="amenities[]" value="{{ $am->id }}" id="am_{{ $am->id }}" @checked(in_array($am->id, $selectedAmenities))>
                                    <label class="form-check-label small" for="am_{{ $am->id }}">{{ $am->name }}</label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Land & Title (read-only summary) -->
            @if($parcel)
                <div class="card mb-4">
                    <div class="card-header brand-font"><i class="bi bi-file-earmark-text me-2 text-primary"></i>{{ __('admin.section_land_title') }}</div>
                    <div class="card-body">
                        <p class="text-muted small">{{ __('admin.land_edit_hint') }}</p>
                        <dl class="row g-2 small mb-0">
                            <dt class="col-6 col-md-4 text-muted fw-semibold">{{ __('admin.field_parcel_number') }}</dt>
                            <dd class="col-6 col-md-8 mb-1">{{ $parcel->parcel_number }}{{ $parcel->block_number ? ' / '.$parcel->block_number : '' }}</dd>
                            <dt class="col-6 col-md-4 text-muted fw-semibold">{{ __('admin.field_deed_number') }}</dt>
                            <dd class="col-6 col-md-8 mb-1">{{ $parcel->deed_number ?: __('admin.not_set') }}</dd>
                            <dt class="col-6 col-md-4 text-muted fw-semibold">{{ __('admin.field_title_deed_type') }}</dt>
                            <dd class="col-6 col-md-8 mb-1">{{ $deedTypes[$parcel->title_deed_type] ?? $parcel->title_deed_type }}</dd>
                            <dt class="col-6 col-md-4 text-muted fw-semibold">{{ __('admin.field_acreage') }}</dt>
                            <dd class="col-6 col-md-8 mb-0">{{ __('admin.acres_count', ['count' => rtrim(rtrim(number_format((float) $parcel->acreage, 2), '0'), '.')]) }}</dd>
                        </dl>
                    </div>
                </div>
            @endif

            <!-- Description -->
            <div class="card mb-4">
                <div class="card-header brand-font"><i class="bi bi-card-text me-2 text-primary"></i>{{ __('admin.section_media_description') }}</div>
                <div class="card-body">
                    <label for="propDescription" class="form-label fw-semibold small">{{ __('admin.field_description') }}</label>
                    <textarea name="description" id="propDescription" rows="6" class="form-control" placeholder="{{ __('admin.field_description_placeholder') }}">{{ old('description', $property->description) }}</textarea>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Pricing -->
            <div class="card mb-4">
                <div class="card-header brand-font"><i class="bi bi-cash-coin me-2 text-primary"></i>{{ __('admin.section_pricing') }}</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="propPrice" class="form-label fw-semibold small">{{ __('admin.field_price') }}</label>
                        <div class="input-group">
                            <span class="input-group-text">TSh</span>
                            <input type="text" inputmode="decimal" name="price" id="propPrice" class="form-control fw-bold" value="{{ old('price', $property->price !== null ? (float) $property->price : '') }}">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="propRent" class="form-label fw-semibold small">{{ __('admin.field_rent_price') }}</label>
                        <div class="input-group">
                            <span class="input-group-text">TSh</span>
                            <input type="text" inputmode="decimal" name="rent_price" id="propRent" class="form-control" value="{{ old('rent_price', $property->rent_price !== null ? (float) $property->rent_price : '') }}">
                        </div>
                    </div>
                    <div class="mb-0">
                        <label for="propRentPeriod" class="form-label fw-semibold small">{{ __('admin.field_rent_period') }}</label>
                        <select name="rent_period" id="propRentPeriod" class="form-select">
                            <option value="Monthly" @selected(old('rent_period', $property->rent_period) == 'Monthly')>{{ __('admin.period_monthly') }}</option>
                            <option value="Quarterly" @selected(old('rent_period', $property->rent_period) == 'Quarterly')>{{ __('admin.period_quarterly') }}</option>
                            <option value="Annually" @selected(old('rent_period', $property->rent_period) == 'Annually')>{{ __('admin.period_annually') }}</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Publishing -->
            <div class="card mb-4">
                <div class="card-header brand-font"><i class="bi bi-megaphone me-2 text-primary"></i>{{ __('admin.section_publishing') }}</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="propStatus" class="form-label fw-semibold small">{{ __('admin.field_status') }} *</label>
                        <select name="status" id="propStatus" class="form-select fw-semibold" required>
                            @foreach($statusOptions as $value => $label)
                                <option value="{{ $value }}" @selected(old('status', $property->status) == $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="propBranch" class="form-label fw-semibold small">{{ __('admin.field_branch') }}</label>
                        <select name="branch_id" id="propBranch" class="form-select">
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}" @selected(old('branch_id', $property->branch_id) == $b->id)>{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-0">
                        <label for="propOwner" class="form-label fw-semibold small">{{ __('admin.field_owner') }}</label>
                        <select name="property_owner_id" id="propOwner" class="form-select">
                            <option value="">{{ __('admin.internal_asset') }}</option>
                            @foreach($owners as $o)
                                <option value="{{ $o->id }}" @selected(old('property_owner_id', $property->property_owner_id) == $o->id)>{{ $o->full_name }} ({{ $o->company_name ?? __('admin.individual') }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg fw-bold">
                    <i class="bi bi-check2-circle me-1"></i> {{ __('app.save') }}
                </button>
                <a href="{{ route('admin.properties.show', $property) }}" class="btn btn-light border">{{ __('app.cancel') }}</a>
            </div>
        </div>
    </div>
</form>
@endsection
