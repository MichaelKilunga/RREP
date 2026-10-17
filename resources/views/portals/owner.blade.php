@extends('layouts.app')

@section('title', __('portal.owner_title'))

@section('content')
@php
    $companyName = setting('company_name', current_organization()?->name ?? 'RehoSpace');
    $whatsappNumber = preg_replace('/\D+/', '', (string) setting('contact_whatsapp', '255784100200'));
    $underReview = $properties->where('submission_status', 'Under Review')->count();
    $livePublished = $properties->where('is_published', true)->count();
    $deedTypes = [
        'Right of Occupancy' => __('portal.deed_right_of_occupancy'),
        'Customary Right of Occupancy' => __('portal.deed_customary'),
        'Freehold' => __('portal.deed_freehold'),
        'Leasehold' => __('portal.deed_leasehold'),
        'Offer Letter' => __('portal.deed_offer_letter'),
    ];
    $zoningOptions = [
        'Residential' => __('portal.zoning_residential'),
        'Commercial' => __('portal.zoning_commercial'),
        'Agricultural' => __('portal.zoning_agricultural'),
        'Industrial' => __('portal.zoning_industrial'),
        'Mixed Use' => __('portal.zoning_mixed'),
    ];
    $areaUnits = [
        'Sqm' => __('portal.unit_sqm'),
        'Acres' => __('portal.unit_acres'),
        'Hectares' => __('portal.unit_hectares'),
    ];
@endphp

<div class="page-header">
    <div class="min-w-0">
        <h3 class="brand-font"><i class="bi bi-building-check text-primary me-2"></i>{!! $companyName !!} {{ __('portal.owner_title') }}</h3>
        <p class="text-muted small mb-0">{{ __('portal.owner_label') }}: <strong>{{ $owner->full_name }}</strong> ({{ $owner->company_name ?? __('portal.individual') }}). {{ __('portal.owner_subtitle') }}</p>
    </div>
    <div class="page-actions">
        <button type="button" class="btn btn-primary fw-semibold" data-bs-toggle="modal" data-bs-target="#submitPlotModal">
            <i class="bi bi-cloud-arrow-up me-1"></i> {{ __('portal.submit_new_plot') }}
        </button>
    </div>
</div>

<!-- Portfolio KPI strip -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4">
        <div class="kpi-card" style="--kpi-accent: var(--rrep-primary);">
            <div class="kpi-label">{{ __('portal.kpi_submitted') }}</div>
            <div class="kpi-value">{{ $properties->count() }}</div>
            <span class="kpi-icon"><i class="bi bi-collection"></i></span>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="kpi-card" style="--kpi-accent: #f59e0b;">
            <div class="kpi-label">{{ __('portal.kpi_under_review') }}</div>
            <div class="kpi-value text-warning">{{ $underReview }}</div>
            <span class="kpi-icon"><i class="bi bi-hourglass-split"></i></span>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="kpi-card" style="--kpi-accent: #198754;">
            <div class="kpi-label">{{ __('portal.kpi_live') }}</div>
            <div class="kpi-value text-success">{{ $livePublished }}</div>
            <span class="kpi-icon"><i class="bi bi-shop"></i></span>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <!-- Submissions & review status -->
    <div class="col-12 col-xl-8">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center gap-2">
                <h5 class="brand-font mb-0 text-truncate">{{ __('portal.submissions_heading') }}</h5>
                <span class="badge bg-primary text-nowrap">{{ trans_choice('portal.properties_count', $properties->count(), ['count' => $properties->count()]) }}</span>
            </div>
            @if($properties->isEmpty())
                <div class="empty-state">
                    <div class="empty-icon"><i class="bi bi-cloud-arrow-up"></i></div>
                    <h6>{{ __('portal.no_submissions') }}</h6>
                    <p class="small mb-3">{{ __('portal.no_submissions_hint') }}</p>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#submitPlotModal">
                        <i class="bi bi-cloud-arrow-up me-1"></i> {{ __('portal.submit_new_plot') }}
                    </button>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover table-stack align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('portal.property_title') }}</th>
                                <th>{{ __('portal.code') }}</th>
                                <th>{{ __('portal.type') }}</th>
                                <th>{{ __('portal.area') }}</th>
                                <th>{{ __('portal.asking_price') }}</th>
                                <th>{{ __('portal.review_stage') }}</th>
                                <th>{{ __('portal.marketplace_status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($properties as $pr)
                                <tr>
                                    <td class="stack-primary">
                                        <div class="fw-semibold text-dark line-clamp-2">{{ $pr->title }}</div>
                                        <small class="text-muted">{{ collect([$pr->address, $pr->city])->filter()->implode(', ') }}</small>
                                    </td>
                                    <td class="fw-semibold text-primary font-monospace">{{ $pr->property_code }}</td>
                                    <td>{{ $pr->propertyType?->name ?? __('portal.land_parcel') }}</td>
                                    <td>{{ $pr->area_size }} {{ $pr->area_unit }}</td>
                                    <td class="fw-bold text-success">{{ format_money_short($pr->price) }}</td>
                                    <td>
                                        @if($pr->submission_status === 'Under Review')
                                            <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i>{{ __('portal.status_under_review') }}</span>
                                        @elseif(in_array($pr->submission_status, ['Approved', 'Published']))
                                            <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>{{ __('portal.status_approved') }}</span>
                                        @elseif($pr->submission_status === 'Rejected')
                                            <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>{{ __('portal.status_rejected') }}</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $pr->submission_status }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($pr->is_published)
                                            <span class="badge bg-success-subtle text-success fw-bold">{{ __('portal.status_live') }}</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-muted">{{ __('portal.status_unpublished') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <!-- Guidance panel -->
    <div class="col-12 col-xl-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3">
                <h5 class="brand-font mb-0"><i class="bi bi-info-circle text-primary me-2"></i>{{ __('portal.guidance_heading') }}</h5>
            </div>
            <div class="card-body">
                <ol class="list-unstyled mb-3">
                    @foreach([1, 2, 3, 4] as $step)
                        <li class="d-flex gap-3 mb-3">
                            <span class="badge rounded-pill bg-primary flex-shrink-0 fs-6">{{ $step }}</span>
                            <div class="min-w-0">
                                <div class="fw-semibold">{{ __('portal.guidance_step_'.$step.'_title') }}</div>
                                <div class="small text-muted">{{ __('portal.guidance_step_'.$step.'_text') }}</div>
                            </div>
                        </li>
                    @endforeach
                </ol>
                <div class="alert alert-light border small mb-3"><i class="bi bi-file-earmark-check text-primary me-1"></i> {{ __('portal.guidance_note') }}</div>
                <a href="https://wa.me/{{ $whatsappNumber }}" target="_blank" rel="noopener" class="btn btn-whatsapp w-100"><i class="bi bi-whatsapp me-1"></i> {{ __('portal.support_whatsapp') }}</a>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Submit Plot for Sale (Owner Workflow) -->
<div class="modal fade" id="submitPlotModal" tabindex="-1" aria-labelledby="submitPlotModalLabel">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content">
            <form action="{{ route('portals.owner.submit_property') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title brand-font" id="submitPlotModalLabel">{{ __('portal.form_title') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('portal.close') }}"></button>
                </div>
                <div class="modal-body bg-light">
                    <p class="small text-muted mb-3">{{ __('portal.form_intro') }}</p>

                    @if($errors->any())
                        <div class="alert alert-danger small">
                            <div class="fw-semibold mb-1">{{ __('portal.fix_errors') }}</div>
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Card 1: Owner & KYC -->
                    <div class="card border-0 shadow-xs mb-3">
                        <div class="card-header bg-white fw-semibold"><i class="bi bi-person-vcard text-primary me-2"></i>{{ __('portal.section_owner') }}</div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <label for="ownerNameField" class="form-label small fw-semibold">{{ __('portal.owner_name') }}</label>
                                    <input type="text" id="ownerNameField" class="form-control" value="{{ $owner->full_name }}" readonly>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label for="ownerPhoneField" class="form-label small fw-semibold">{{ __('portal.owner_phone') }}</label>
                                    <input type="tel" name="phone" id="ownerPhoneField" class="form-control @error('phone') is-invalid @enderror" data-tz-phone placeholder="0784 100 200" value="{{ old('phone', $owner->phone) }}" autocomplete="tel">
                                    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12 col-md-6">
                                    <label for="nidaField" class="form-label small fw-semibold">{{ __('portal.nida_number') }}</label>
                                    <input type="text" name="national_id" id="nidaField" class="form-control @error('national_id') is-invalid @enderror" inputmode="numeric" placeholder="19900101-12345-00001-23" value="{{ old('national_id', $owner->national_id) }}" autocomplete="off">
                                    @error('national_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12 col-md-6">
                                    <label for="tinField" class="form-label small fw-semibold">{{ __('portal.tin_number') }} <span class="text-muted fw-normal">({{ __('portal.optional') }})</span></label>
                                    <input type="text" name="tax_pin" id="tinField" class="form-control @error('tax_pin') is-invalid @enderror" inputmode="numeric" placeholder="123-456-789" value="{{ old('tax_pin', $owner->tax_pin) }}" autocomplete="off">
                                    @error('tax_pin')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 2: Plot / property details -->
                    <div class="card border-0 shadow-xs mb-3">
                        <div class="card-header bg-white fw-semibold"><i class="bi bi-geo-alt text-primary me-2"></i>{{ __('portal.section_plot') }}</div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-12 col-md-8">
                                    <label for="titleField" class="form-label small fw-semibold">{{ __('portal.title_field') }}</label>
                                    <input type="text" name="title" id="titleField" class="form-control @error('title') is-invalid @enderror" placeholder="{{ __('portal.title_placeholder') }}" value="{{ old('title') }}" required>
                                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12 col-md-4">
                                    <label for="propertyTypeField" class="form-label small fw-semibold">{{ __('portal.category') }}</label>
                                    <select name="property_type_id" id="propertyTypeField" class="form-select @error('property_type_id') is-invalid @enderror" required>
                                        @foreach($propertyTypes as $type)
                                            <option value="{{ $type->id }}" @selected(old('property_type_id') == $type->id)>{{ $type->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12 col-sm-6 col-md-4">
                                    <label for="listingTypeField" class="form-label small fw-semibold">{{ __('portal.listing_type') }}</label>
                                    <select name="listing_type" id="listingTypeField" class="form-select">
                                        <option value="Sale" @selected(old('listing_type', 'Sale') === 'Sale')>{{ __('portal.listing_sale') }}</option>
                                        <option value="Rent" @selected(old('listing_type') === 'Rent')>{{ __('portal.listing_rent') }}</option>
                                        <option value="Lease" @selected(old('listing_type') === 'Lease')>{{ __('portal.listing_lease') }}</option>
                                    </select>
                                </div>
                                <div class="col-12 col-sm-6 col-md-4">
                                    <label for="deedTypeField" class="form-label small fw-semibold">{{ __('portal.title_deed_type') }}</label>
                                    <select name="title_deed_type" id="deedTypeField" class="form-select @error('title_deed_type') is-invalid @enderror">
                                        @foreach($deedTypes as $value => $label)
                                            <option value="{{ $value }}" @selected(old('title_deed_type', 'Right of Occupancy') === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 col-sm-6 col-md-4">
                                    <label for="zoningField" class="form-label small fw-semibold">{{ __('portal.zoning') }}</label>
                                    <select name="zoning" id="zoningField" class="form-select">
                                        @foreach($zoningOptions as $value => $label)
                                            <option value="{{ $value }}" @selected(old('zoning', 'Residential') === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12 col-sm-6">
                                    <label for="regionField" class="form-label small fw-semibold">{{ __('portal.region') }}</label>
                                    <select name="city" id="regionField" class="form-select @error('city') is-invalid @enderror" required>
                                        <option value="">{{ __('portal.select_region') }}</option>
                                        @foreach(tz_regions() as $region)
                                            <option value="{{ $region }}" @selected(old('city') === $region)>{{ $region }}</option>
                                        @endforeach
                                    </select>
                                    @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12 col-sm-6">
                                    <label for="addressField" class="form-label small fw-semibold">{{ __('portal.address_field') }}</label>
                                    <input type="text" name="address" id="addressField" class="form-control @error('address') is-invalid @enderror" placeholder="{{ __('portal.address_placeholder') }}" value="{{ old('address') }}" autocomplete="street-address" required>
                                    @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-12 col-sm-6">
                                    <label for="areaSizeField" class="form-label small fw-semibold">{{ __('portal.area_size') }}</label>
                                    <div class="input-group">
                                        <input type="number" step="0.01" min="0.1" name="area_size" id="areaSizeField" class="form-control @error('area_size') is-invalid @enderror" inputmode="decimal" placeholder="1.5" value="{{ old('area_size') }}" required>
                                        <select name="area_unit" id="areaUnitField" class="form-select flex-grow-0 w-auto" aria-label="{{ __('portal.area_size') }}">
                                            @foreach($areaUnits as $value => $label)
                                                <option value="{{ $value }}" @selected(old('area_unit', 'Sqm') === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error('area_size')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12 col-sm-6">
                                    <label for="priceField" class="form-label small fw-semibold">{{ __('portal.asking_price_tzs') }}</label>
                                    <div class="input-group">
                                        <span class="input-group-text">TSh</span>
                                        <input type="number" step="1" min="0" name="price" id="priceField" class="form-control @error('price') is-invalid @enderror" inputmode="decimal" placeholder="45000000" value="{{ old('price') }}" required>
                                    </div>
                                    @error('price')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-6">
                                    <label for="latitudeField" class="form-label small fw-semibold">{{ __('portal.gps_latitude') }} <span class="text-muted fw-normal">({{ __('portal.optional') }})</span></label>
                                    <input type="number" step="0.00000001" name="latitude" id="latitudeField" class="form-control" inputmode="decimal" placeholder="-6.82350" value="{{ old('latitude') }}">
                                </div>
                                <div class="col-6">
                                    <label for="longitudeField" class="form-label small fw-semibold">{{ __('portal.gps_longitude') }} <span class="text-muted fw-normal">({{ __('portal.optional') }})</span></label>
                                    <input type="number" step="0.00000001" name="longitude" id="longitudeField" class="form-control" inputmode="decimal" placeholder="39.26950" value="{{ old('longitude') }}">
                                </div>

                                <div class="col-12">
                                    <label for="descriptionField" class="form-label small fw-semibold">{{ __('portal.description_field') }}</label>
                                    <textarea name="description" id="descriptionField" class="form-control @error('description') is-invalid @enderror" rows="4" placeholder="{{ __('portal.description_placeholder') }}" required>{{ old('description') }}</textarea>
                                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 3: Photos & title deed -->
                    <div class="card border-0 shadow-xs">
                        <div class="card-header bg-white fw-semibold"><i class="bi bi-images text-primary me-2"></i>{{ __('portal.section_uploads') }}</div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <label for="propertyImageField" class="form-label small fw-semibold">{{ __('portal.photo_field') }}</label>
                                    <input type="file" name="property_image" id="propertyImageField" class="form-control @error('property_image') is-invalid @enderror" accept="image/*" capture="environment">
                                    @error('property_image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12 col-md-6">
                                    <label for="deedDocumentField" class="form-label small fw-semibold">{{ __('portal.deed_field') }}</label>
                                    <input type="file" name="deed_document" id="deedDocumentField" class="form-control @error('deed_document') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png">
                                    <div class="form-text">{{ __('portal.deed_hint') }}</div>
                                    @error('deed_document')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-grid d-sm-flex gap-2">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('app.cancel') }}</button>
                    <button type="submit" class="btn btn-primary fw-semibold m-0"><i class="bi bi-send-check me-1"></i> {{ __('portal.submit_for_verification') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@if($errors->any())
<script>
document.addEventListener('DOMContentLoaded', function () {
    var modal = document.getElementById('submitPlotModal');
    if (modal && window.bootstrap) {
        window.bootstrap.Modal.getOrCreateInstance(modal).show();
    }
});
</script>
@endif
@endsection
