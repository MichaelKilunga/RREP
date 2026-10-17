@extends('layouts.app')

@section('title', __('app.add_property'))

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
@endphp

<div class="page-header">
    <div class="min-w-0">
        <h1 class="brand-font">{{ __('app.add_property') }}</h1>
        <p class="text-muted small mb-0">{{ __('admin.create_property_subtitle') }}</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.properties.index') }}" class="btn btn-light border"><i class="bi bi-arrow-left me-1"></i> {{ __('admin.back_to_list') }}</a>
        <button type="submit" form="propertyForm" class="btn btn-primary"><i class="bi bi-check2-circle me-1"></i> {{ __('admin.save_property') }}</button>
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

<form action="{{ route('admin.properties.store') }}" method="POST" id="propertyForm">
    @csrf
    <div class="row g-4">
        <div class="col-lg-8">
            <!-- Basics -->
            <div class="card mb-4">
                <div class="card-header brand-font"><i class="bi bi-house-door me-2 text-primary"></i>{{ __('admin.section_basics') }}</div>
                <div class="card-body">
                    <p class="text-muted small">{{ __('admin.required_hint') }}</p>
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="propTitle" class="form-label fw-semibold small">{{ __('admin.field_title') }} *</label>
                            <input type="text" name="title" id="propTitle" class="form-control @error('title') is-invalid @enderror" placeholder="{{ __('admin.field_title_placeholder') }}" value="{{ old('title') }}" required autocomplete="off">
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="propType" class="form-label fw-semibold small">{{ __('admin.field_property_type') }} *</label>
                            <select name="property_type_id" id="propType" class="form-select @error('property_type_id') is-invalid @enderror" required>
                                <option value="">{{ __('admin.select_type') }}</option>
                                @foreach($types as $t)
                                    <option value="{{ $t->id }}" @selected(old('property_type_id') == $t->id)>{{ $t->name }} ({{ $t->category }})</option>
                                @endforeach
                            </select>
                            @error('property_type_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="listingType" class="form-label fw-semibold small">{{ __('admin.field_listing_type') }} *</label>
                            <select name="listing_type" id="listingType" class="form-select" required>
                                @foreach($listingOptions as $value => $label)
                                    <option value="{{ $value }}" @selected(old('listing_type', 'Sale') == $value)>{{ $label }}</option>
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
                            <input type="text" name="address" id="propAddress" class="form-control @error('address') is-invalid @enderror" placeholder="{{ __('admin.field_plot_street_placeholder') }}" value="{{ old('address') }}" required autocomplete="street-address">
                            @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="propState" class="form-label fw-semibold small">{{ __('admin.field_ward_district') }}</label>
                            <input type="text" name="state" id="propState" class="form-control" placeholder="{{ __('admin.field_ward_district_placeholder') }}" value="{{ old('state') }}" autocomplete="address-level2">
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="propCity" class="form-label fw-semibold small">{{ __('admin.field_region') }} *</label>
                            <select name="city" id="propCity" class="form-select @error('city') is-invalid @enderror" required autocomplete="address-level1">
                                <option value="">{{ __('admin.select_region') }}</option>
                                @foreach(tz_regions() as $region)
                                    <option value="{{ $region }}" @selected(old('city') == $region)>{{ $region }}</option>
                                @endforeach
                            </select>
                            @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="propCountry" class="form-label fw-semibold small">{{ __('admin.field_country') }}</label>
                            <input type="text" id="propCountry" class="form-control" value="{{ __('admin.tanzania') }}" readonly>
                        </div>
                        <div class="col-6 col-md-4">
                            <label for="propLat" class="form-label fw-semibold small">{{ __('admin.field_latitude') }}</label>
                            <input type="number" step="0.00000001" name="latitude" id="propLat" class="form-control" placeholder="-6.7495" value="{{ old('latitude') }}">
                        </div>
                        <div class="col-6 col-md-4">
                            <label for="propLng" class="form-label fw-semibold small">{{ __('admin.field_longitude') }}</label>
                            <input type="number" step="0.00000001" name="longitude" id="propLng" class="form-control" placeholder="39.2818" value="{{ old('longitude') }}">
                        </div>
                        <div class="col-12">
                            <div class="form-text"><i class="bi bi-info-circle me-1"></i>{{ __('admin.gps_hint') }}</div>
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
                            <input type="number" min="0" name="bedrooms" id="propBeds" class="form-control" placeholder="3" value="{{ old('bedrooms') }}">
                        </div>
                        <div class="col-6 col-md-3">
                            <label for="propBaths" class="form-label fw-semibold small">{{ __('admin.field_bathrooms') }}</label>
                            <input type="number" min="0" name="bathrooms" id="propBaths" class="form-control" placeholder="2" value="{{ old('bathrooms') }}">
                        </div>
                        <div class="col-6 col-md-3">
                            <label for="propArea" class="form-label fw-semibold small">{{ __('admin.field_area_size') }}</label>
                            <input type="text" inputmode="decimal" name="area_size" id="propArea" class="form-control" placeholder="600" value="{{ old('area_size') }}">
                        </div>
                        <div class="col-6 col-md-3">
                            <label for="propAreaUnit" class="form-label fw-semibold small">{{ __('admin.field_area_unit') }}</label>
                            <select name="area_unit" id="propAreaUnit" class="form-select">
                                <option value="Sqm" @selected(old('area_unit', 'Sqm') == 'Sqm')>{{ __('admin.unit_sqm') }}</option>
                                <option value="Acres" @selected(old('area_unit') == 'Acres')>{{ __('admin.unit_acres') }}</option>
                                <option value="Hectares" @selected(old('area_unit') == 'Hectares')>{{ __('admin.unit_hectares') }}</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-4">
                            <label for="propFloors" class="form-label fw-semibold small">{{ __('admin.field_floors') }}</label>
                            <input type="number" min="0" name="floors" id="propFloors" class="form-control" placeholder="1" value="{{ old('floors') }}">
                        </div>
                        <div class="col-6 col-md-4">
                            <label for="propParking" class="form-label fw-semibold small">{{ __('admin.field_parking') }}</label>
                            <input type="number" min="0" name="parking_spaces" id="propParking" class="form-control" placeholder="2" value="{{ old('parking_spaces') }}">
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="propYear" class="form-label fw-semibold small">{{ __('admin.field_year_built') }}</label>
                            <input type="number" min="1900" max="{{ date('Y') + 5 }}" name="year_built" id="propYear" class="form-control" placeholder="{{ date('Y') }}" value="{{ old('year_built') }}">
                        </div>
                    </div>

                    <div class="form-label fw-semibold small mb-2">{{ __('admin.field_amenities') }}</div>
                    <div class="row g-2">
                        @foreach($amenities as $am)
                            <div class="col-12 col-sm-6 col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input amenity-checkbox" type="checkbox" name="amenities[]" value="{{ $am->id }}" id="am_{{ $am->id }}" data-name="{{ $am->name }}" @checked(in_array($am->id, old('amenities', [])))>
                                    <label class="form-check-label small" for="am_{{ $am->id }}">{{ $am->name }}</label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Land & Title -->
            <div class="card mb-4">
                <div class="card-header brand-font"><i class="bi bi-file-earmark-text me-2 text-primary"></i>{{ __('admin.section_land_title') }}</div>
                <div class="card-body">
                    <p class="text-muted small">{{ __('admin.land_hint') }}</p>
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label for="parcelNumber" class="form-label fw-semibold small">{{ __('admin.field_parcel_number') }}</label>
                            <input type="text" name="parcel_number" id="parcelNumber" class="form-control" placeholder="{{ __('admin.field_parcel_number_placeholder') }}" value="{{ old('parcel_number') }}">
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="deedNumber" class="form-label fw-semibold small">{{ __('admin.field_deed_number') }}</label>
                            <input type="text" name="deed_number" id="deedNumber" class="form-control" placeholder="{{ __('admin.field_deed_number_placeholder') }}" value="{{ old('deed_number') }}">
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="deedType" class="form-label fw-semibold small">{{ __('admin.field_title_deed_type') }}</label>
                            <select name="title_deed_type" id="deedType" class="form-select">
                                @foreach($deedTypes as $value => $label)
                                    <option value="{{ $value }}" @selected(old('title_deed_type', 'Right of Occupancy') == $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="acreage" class="form-label fw-semibold small">{{ __('admin.field_acreage') }}</label>
                            <input type="text" inputmode="decimal" name="acreage" id="acreage" class="form-control" placeholder="0.25" value="{{ old('acreage') }}">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Description -->
            <div class="card mb-4">
                <div class="card-header brand-font d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <span><i class="bi bi-card-text me-2 text-primary"></i>{{ __('admin.section_media_description') }}</span>
                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill" id="btnAiDescription">
                        <i class="bi bi-stars text-warning me-1"></i> {{ __('admin.ai_generate') }}
                    </button>
                </div>
                <div class="card-body">
                    <label for="propDescription" class="form-label fw-semibold small">{{ __('admin.field_description') }}</label>
                    <textarea name="description" id="propDescription" rows="6" class="form-control" placeholder="{{ __('admin.field_description_placeholder') }}">{{ old('description') }}</textarea>
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
                            <input type="text" inputmode="decimal" name="price" id="propPrice" class="form-control fw-bold @error('price') is-invalid @enderror" placeholder="45000000" value="{{ old('price') }}">
                        </div>
                        @error('price')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="propRent" class="form-label fw-semibold small">{{ __('admin.field_rent_price') }}</label>
                        <div class="input-group">
                            <span class="input-group-text">TSh</span>
                            <input type="text" inputmode="decimal" name="rent_price" id="propRent" class="form-control" placeholder="800000" value="{{ old('rent_price') }}">
                        </div>
                    </div>
                    <div class="mb-0">
                        <label for="propRentPeriod" class="form-label fw-semibold small">{{ __('admin.field_rent_period') }}</label>
                        <select name="rent_period" id="propRentPeriod" class="form-select">
                            <option value="Monthly" @selected(old('rent_period', 'Monthly') == 'Monthly')>{{ __('admin.period_monthly') }}</option>
                            <option value="Quarterly" @selected(old('rent_period') == 'Quarterly')>{{ __('admin.period_quarterly') }}</option>
                            <option value="Annually" @selected(old('rent_period') == 'Annually')>{{ __('admin.period_annually') }}</option>
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
                                <option value="{{ $value }}" @selected(old('status', 'Available') == $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="propBranch" class="form-label fw-semibold small">{{ __('admin.field_branch') }}</label>
                        <select name="branch_id" id="propBranch" class="form-select">
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}" @selected(old('branch_id') == $b->id)>{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-0">
                        <label for="propOwner" class="form-label fw-semibold small">{{ __('admin.field_owner') }}</label>
                        <select name="property_owner_id" id="propOwner" class="form-select">
                            <option value="">{{ __('admin.internal_asset') }}</option>
                            @foreach($owners as $o)
                                <option value="{{ $o->id }}" @selected(old('property_owner_id') == $o->id)>{{ $o->full_name }} ({{ $o->company_name ?? __('admin.individual') }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg fw-bold">
                    <i class="bi bi-check2-circle me-1"></i> {{ __('admin.save_property') }}
                </button>
                <a href="{{ route('admin.properties.index') }}" class="btn btn-light border">{{ __('app.cancel') }}</a>
            </div>
        </div>
    </div>
</form>
@endsection

@section('scripts')
<script>
$(document).ready(function () {
    const aiLabel = '<i class="bi bi-stars text-warning me-1"></i> {{ __('admin.ai_generate') }}';

    $('#btnAiDescription').on('click', function () {
        const title = $('#propTitle').val();
        const type = $('#propType option:selected').text();
        const location = $('#propAddress').val() + ', ' + $('#propCity').val();
        const price = $('#propPrice').val();
        const bedrooms = $('#propBeds').val();

        let amenities = [];
        $('.amenity-checkbox:checked').each(function () {
            amenities.push($(this).data('name'));
        });

        if (!title) {
            Swal.fire('', @json(__('admin.ai_fill_title_first')), 'info');
            return;
        }

        const btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> {{ __('admin.ai_generating') }}');

        $.ajax({
            url: "{{ route('admin.properties.ai_description') }}",
            method: "POST",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: {
                title: title,
                type: type,
                location: location,
                price: price,
                bedrooms: bedrooms,
                amenities: amenities
            },
            success: function (res) {
                btn.prop('disabled', false).html(aiLabel);
                $('#propDescription').val(res.description);
                window.RREP && window.RREP.toast ? window.RREP.toast(@json(__('admin.ai_success'))) : Swal.fire('', @json(__('admin.ai_success')), 'success');
            },
            error: function () {
                btn.prop('disabled', false).html(aiLabel);
                Swal.fire('', @json(__('admin.ai_failed')), 'error');
            }
        });
    });
});
</script>
@endsection
