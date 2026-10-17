@extends('layouts.app')

@section('title', $property->title)

@section('content')
@php
    $statusLabels = [
        'Available' => __('admin.status_available'),
        'Reserved' => __('admin.status_reserved'),
        'Under Contract' => __('admin.status_under_contract'),
        'Sold' => __('admin.status_sold'),
        'Leased' => __('admin.status_leased'),
        'Maintenance' => __('admin.status_maintenance'),
        'Off Market' => __('admin.status_off_market'),
    ];
    $deedTypes = [
        'Right of Occupancy' => __('admin.deed_granted_ro'),
        'Customary Right of Occupancy' => __('admin.deed_customary_ro'),
        'Freehold' => __('admin.deed_freehold'),
        'Leasehold' => __('admin.deed_leasehold'),
        'Offer Letter' => __('admin.deed_offer_letter'),
        'None' => __('admin.deed_none'),
    ];
    $isRental = in_array($property->listing_type, ['Rent', 'Lease'], true);
    $parcel = $property->landParcel;
    $owner = $property->owner;
    $ownerPhone = $owner?->phone ? format_phone_tz($owner->phone) : null;
    $ownerWhatsApp = $ownerPhone ? preg_replace('/\D+/', '', $ownerPhone) : null;
    $branch = $property->branch;
    $branchPhone = $branch?->phone ? format_phone_tz($branch->phone) : null;
    $branchWhatsApp = $branchPhone ? preg_replace('/\D+/', '', $branchPhone) : null;
    $companyName = setting('company_name', current_organization()?->name ?? 'RehoSpace');
    $specs = [
        ['label' => __('admin.field_bedrooms'), 'value' => $property->bedrooms, 'icon' => 'bi-door-closed'],
        ['label' => __('admin.field_bathrooms'), 'value' => $property->bathrooms, 'icon' => 'bi-droplet'],
        ['label' => __('admin.field_area_size'), 'value' => $property->area_size ? rtrim(rtrim(number_format((float) $property->area_size, 2), '0'), '.').' '.$property->area_unit : null, 'icon' => 'bi-rulers'],
        ['label' => __('admin.type'), 'value' => $property->propertyType?->name, 'icon' => 'bi-house'],
        ['label' => __('admin.field_floors'), 'value' => $property->floors, 'icon' => 'bi-layers'],
        ['label' => __('admin.field_parking'), 'value' => $property->parking_spaces, 'icon' => 'bi-car-front'],
        ['label' => __('admin.field_year_built'), 'value' => $property->year_built, 'icon' => 'bi-calendar3'],
        ['label' => __('admin.field_branch'), 'value' => $branch?->name, 'icon' => 'bi-diagram-3'],
    ];
    $mediaItems = $property->media->filter(fn ($m) => $m->mediaFile);
@endphp

<div class="page-header">
    <div class="min-w-0">
        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
            <h1 class="brand-font mb-0">{{ $property->title }}</h1>
            <span class="badge badge-status-{{ strtolower(str_replace(' ', '', $property->status)) }}">{{ $statusLabels[$property->status] ?? $property->status }}</span>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2 small text-muted">
            <span><i class="bi bi-geo-alt me-1"></i>{{ $property->address }}, {{ $property->city }}</span>
            <span class="d-none d-sm-inline">&bull;</span>
            <span>{{ __('admin.code') }}: <strong class="text-dark">{{ $property->property_code }}</strong></span>
        </div>
        <div class="fs-4 fw-extrabold text-success mt-1">{{ $property->formatted_price }}</div>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.properties.edit', $property) }}" class="btn btn-primary"><i class="bi bi-pencil me-1"></i> {{ __('app.edit') }}</a>
        <a href="{{ route('marketplace.show', $property) }}" target="_blank" rel="noopener" class="btn btn-outline-primary"><i class="bi bi-globe me-1"></i> {{ __('admin.view_on_marketplace') }}</a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <!-- Specifications -->
        <div class="card mb-4">
            <div class="card-header brand-font"><i class="bi bi-rulers me-2 text-primary"></i>{{ __('admin.specifications') }}</div>
            <div class="card-body">
                <div class="row g-2 mb-4">
                    @foreach($specs as $spec)
                        @if($spec['value'] !== null && $spec['value'] !== '')
                            <div class="col-6 col-md-3">
                                <div class="border rounded-3 p-2.5 h-100 bg-light">
                                    <div class="text-muted small"><i class="bi {{ $spec['icon'] }} me-1"></i>{{ $spec['label'] }}</div>
                                    <div class="fw-bold">{{ $spec['value'] }}</div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>

                <h6 class="brand-font mb-2">{{ __('app.description') }}</h6>
                @if($property->description)
                    <div class="p-3 bg-light rounded-3 mb-4 text-secondary leading-relaxed">{!! nl2br(e($property->description)) !!}</div>
                @else
                    <p class="text-muted small mb-4">{{ __('admin.none') }}</p>
                @endif

                <h6 class="brand-font mb-2">{{ __('app.amenities') }}</h6>
                <div class="d-flex flex-wrap gap-2">
                    @forelse($property->amenities as $am)
                        <span class="badge bg-light text-dark border p-2"><i class="bi bi-check-circle-fill text-success me-1"></i>{{ $am->name }}</span>
                    @empty
                        <span class="text-muted small">{{ __('admin.no_amenities') }}</span>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Land & Title -->
        @if($parcel)
            <div class="card mb-4">
                <div class="card-header brand-font"><i class="bi bi-file-earmark-text me-2 text-primary"></i>{{ __('admin.land_info') }}</div>
                <div class="card-body">
                    <dl class="row g-2 small mb-0">
                        <dt class="col-6 col-md-4 text-muted fw-semibold">{{ __('admin.field_parcel_number') }}</dt>
                        <dd class="col-6 col-md-8 mb-1">{{ $parcel->parcel_number }}</dd>
                        @if($parcel->block_number)
                            <dt class="col-6 col-md-4 text-muted fw-semibold">{{ __('admin.block') }}</dt>
                            <dd class="col-6 col-md-8 mb-1">{{ $parcel->block_number }}</dd>
                        @endif
                        <dt class="col-6 col-md-4 text-muted fw-semibold">{{ __('admin.field_title_deed_type') }}</dt>
                        <dd class="col-6 col-md-8 mb-1">{{ $deedTypes[$parcel->title_deed_type] ?? $parcel->title_deed_type }}</dd>
                        <dt class="col-6 col-md-4 text-muted fw-semibold">{{ __('admin.field_deed_number') }}</dt>
                        <dd class="col-6 col-md-8 mb-1">{{ $parcel->deed_number ?: __('admin.not_set') }}</dd>
                        <dt class="col-6 col-md-4 text-muted fw-semibold">{{ __('admin.survey_plan') }}</dt>
                        <dd class="col-6 col-md-8 mb-1">{{ $parcel->survey_plan_number ?: __('admin.not_set') }}</dd>
                        <dt class="col-6 col-md-4 text-muted fw-semibold">{{ __('admin.field_acreage') }}</dt>
                        <dd class="col-6 col-md-8 mb-1">{{ __('admin.acres_count', ['count' => rtrim(rtrim(number_format((float) $parcel->acreage, 2), '0'), '.')]) }}</dd>
                        @if($parcel->zoning)
                            <dt class="col-6 col-md-4 text-muted fw-semibold">{{ __('admin.zoning') }}</dt>
                            <dd class="col-6 col-md-8 mb-1">{{ $parcel->zoning }}</dd>
                        @endif
                        @if($parcel->tenure_years_remaining)
                            <dt class="col-6 col-md-4 text-muted fw-semibold">{{ __('admin.tenure_years') }}</dt>
                            <dd class="col-6 col-md-8 mb-0">{{ $parcel->tenure_years_remaining }} {{ __('admin.years') }}</dd>
                        @endif
                    </dl>
                </div>
            </div>
        @endif

        <!-- Media -->
        <div class="card mb-4">
            <div class="card-header brand-font"><i class="bi bi-images me-2 text-primary"></i>{{ __('admin.media') }}</div>
            <div class="card-body">
                @if($mediaItems->isNotEmpty())
                    <div class="row g-2">
                        @foreach($mediaItems as $item)
                            <div class="col-6 col-md-4">
                                <a href="{{ $item->mediaFile->url }}" target="_blank" rel="noopener" class="d-block ratio ratio-4x3 rounded-3 overflow-hidden border">
                                    <img src="{{ $item->mediaFile->url }}" alt="{{ $item->caption ?? $property->title }}" loading="lazy" class="object-fit-cover w-100 h-100">
                                </a>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state py-4">
                        <div class="empty-icon"><i class="bi bi-camera"></i></div>
                        <p class="mb-0">{{ __('admin.no_media') }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Map -->
        @if($property->latitude && $property->longitude)
            <div class="card mb-4">
                <div class="card-header brand-font"><i class="bi bi-geo-alt-fill text-danger me-2"></i>{{ __('admin.map_title') }}</div>
                <div class="card-body p-0">
                    <div class="ratio ratio-16x9"><div id="propertyMap"></div></div>
                </div>
            </div>
        @endif

        <!-- AI valuation -->
        @if($valuation)
            <div class="card border-primary-subtle bg-primary-subtle p-3 mb-4">
                <div class="d-flex gap-3">
                    <i class="bi bi-stars text-warning fs-2"></i>
                    <div class="min-w-0">
                        <h6 class="brand-font text-primary mb-1">{{ __('admin.ai_valuation') }}</h6>
                        <p class="small text-dark mb-0">{!! nl2br(e($valuation['analysis'])) !!}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Related: leads -->
        <div class="card mb-4">
            <div class="card-header brand-font d-flex justify-content-between align-items-center">
                <span><i class="bi bi-people me-2 text-primary"></i>{{ __('admin.related_leads') }}</span>
                <span class="badge bg-light text-dark border">{{ $property->leads->count() }}</span>
            </div>
            @if($property->leads->isEmpty())
                <div class="empty-state py-4">
                    <p class="mb-2">{{ __('admin.no_leads') }}</p>
                    <a href="{{ route('crm.leads') }}" class="btn btn-sm btn-outline-primary">{{ __('admin.create_lead') }}</a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-stack align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('admin.customer') }}</th>
                                <th>{{ __('admin.stage') }}</th>
                                <th>{{ __('admin.estimated_value') }}</th>
                                <th>{{ __('admin.date') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($property->leads as $lead)
                                <tr>
                                    <td class="stack-primary">
                                        <div class="fw-semibold">{{ $lead->customer?->full_name ?? $lead->title }}</div>
                                        <div class="text-muted small">{{ $lead->title }}</div>
                                    </td>
                                    <td><span class="badge bg-secondary-subtle text-secondary">{{ $lead->stage }}</span></td>
                                    <td title="{{ $lead->estimated_value ? format_currency($lead->estimated_value) : '' }}">{{ $lead->estimated_value ? format_money_short($lead->estimated_value) : '-' }}</td>
                                    <td class="text-muted small">{{ $lead->created_at?->format('d M Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Related: reservations -->
        <div class="card mb-4">
            <div class="card-header brand-font d-flex justify-content-between align-items-center">
                <span><i class="bi bi-bookmark-check me-2 text-primary"></i>{{ __('admin.related_reservations') }}</span>
                <span class="badge bg-light text-dark border">{{ $property->reservations->count() }}</span>
            </div>
            @if($property->reservations->isEmpty())
                <div class="empty-state py-4">
                    <p class="mb-2">{{ __('admin.no_reservations') }}</p>
                    <a href="{{ route('reservations.index') }}" class="btn btn-sm btn-outline-primary">{{ __('admin.place_reservation') }}</a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-stack align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('admin.reservation_no') }}</th>
                                <th>{{ __('admin.customer') }}</th>
                                <th>{{ __('admin.deposit_paid') }}</th>
                                <th>{{ __('admin.reserved_until') }}</th>
                                <th>{{ __('app.status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($property->reservations as $res)
                                <tr>
                                    <td class="stack-primary fw-semibold">{{ $res->reservation_number }}</td>
                                    <td>{{ $res->customer?->full_name ?? '-' }}</td>
                                    <td title="{{ format_currency($res->deposit_paid) }}">{{ format_money_short($res->deposit_paid) }}</td>
                                    <td>{{ $res->reserved_until ? \Illuminate\Support\Carbon::parse($res->reserved_until)->format('d M Y') : '-' }}</td>
                                    <td><span class="badge badge-status-{{ strtolower($res->status) }}">{{ $res->status }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Related: deals -->
        <div class="card mb-4">
            <div class="card-header brand-font d-flex justify-content-between align-items-center">
                <span><i class="bi bi-briefcase me-2 text-primary"></i>{{ __('admin.related_deals') }}</span>
                <span class="badge bg-light text-dark border">{{ $property->salesDeals->count() }}</span>
            </div>
            @if($property->salesDeals->isEmpty())
                <div class="empty-state py-4">
                    <p class="mb-2">{{ __('admin.no_deals') }}</p>
                    <a href="{{ route('deals.index') }}" class="btn btn-sm btn-outline-primary">{{ __('admin.create_deal') }}</a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-stack align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('admin.deal_no') }}</th>
                                <th>{{ __('admin.customer') }}</th>
                                <th>{{ __('admin.sale_price') }}</th>
                                <th>{{ __('admin.agreement_date') }}</th>
                                <th>{{ __('app.status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($property->salesDeals as $deal)
                                <tr>
                                    <td class="stack-primary fw-semibold">{{ $deal->deal_number }}</td>
                                    <td>{{ $deal->customer?->full_name ?? '-' }}</td>
                                    <td class="fw-semibold text-success" title="{{ format_currency($deal->sale_price) }}">{{ format_money_short($deal->sale_price) }}</td>
                                    <td>{{ $deal->agreement_date ? \Illuminate\Support\Carbon::parse($deal->agreement_date)->format('d M Y') : '-' }}</td>
                                    <td><span class="badge badge-status-{{ strtolower(str_replace(' ', '', $deal->status)) }}">{{ $deal->status }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Owner -->
        <div class="card mb-4">
            <div class="card-header brand-font"><i class="bi bi-person-badge me-2 text-primary"></i>{{ __('admin.owner_card') }}</div>
            <div class="card-body">
                @if($owner)
                    <div class="fw-bold">{{ $owner->full_name }}</div>
                    @if($owner->company_name)
                        <div class="text-muted small mb-2">{{ $owner->company_name }}</div>
                    @endif
                    @if($ownerPhone)
                        <div class="small mb-1"><i class="bi bi-telephone me-2 text-primary"></i><a href="tel:{{ $ownerWhatsApp ? '+'.$ownerWhatsApp : $owner->phone }}" class="text-decoration-none">{{ $ownerPhone }}</a></div>
                    @endif
                    @if($owner->email)
                        <div class="small mb-2"><i class="bi bi-envelope me-2 text-primary"></i><a href="mailto:{{ $owner->email }}" class="text-decoration-none text-break">{{ $owner->email }}</a></div>
                    @endif
                    <span class="badge {{ $owner->kyc_status === 'Verified' ? 'badge-verified' : 'bg-warning-subtle text-warning-emphasis' }} mb-3"><i class="bi bi-shield-check me-1"></i>{{ $owner->kyc_status === 'Verified' ? __('admin.kyc_verified') : __('admin.kyc_pending') }}</span>
                    @if($ownerWhatsApp)
                        <div class="d-grid gap-2">
                            <a href="https://wa.me/{{ $ownerWhatsApp }}" target="_blank" rel="noopener" class="btn btn-whatsapp"><i class="bi bi-whatsapp me-1"></i> {{ __('admin.whatsapp') }}</a>
                            <a href="tel:+{{ $ownerWhatsApp }}" class="btn btn-outline-primary"><i class="bi bi-telephone me-1"></i> {{ __('admin.call') }}</a>
                        </div>
                    @endif
                @else
                    <div class="text-muted small">{{ __('admin.company_owned', ['company' => $companyName]) }}</div>
                @endif
            </div>
        </div>

        <!-- Branch -->
        @if($branch)
            <div class="card mb-4">
                <div class="card-header brand-font"><i class="bi bi-diagram-3 me-2 text-primary"></i>{{ __('admin.branch_card') }}</div>
                <div class="card-body">
                    <div class="fw-bold">{{ $branch->name }}</div>
                    @if($branch->city)
                        <div class="text-muted small mb-2">{{ $branch->city }}</div>
                    @endif
                    @if($branchPhone)
                        <div class="small mb-3"><i class="bi bi-telephone me-2 text-primary"></i><a href="tel:+{{ $branchWhatsApp }}" class="text-decoration-none">{{ $branchPhone }}</a></div>
                        <a href="https://wa.me/{{ $branchWhatsApp }}" target="_blank" rel="noopener" class="btn btn-whatsapp w-100"><i class="bi bi-whatsapp me-1"></i> {{ __('admin.whatsapp') }}</a>
                    @endif
                </div>
            </div>
        @endif

        <!-- CRM actions -->
        <div class="card mb-4">
            <div class="card-header brand-font"><i class="bi bi-lightning-charge me-2 text-primary"></i>{{ __('admin.crm_actions') }}</div>
            <div class="card-body d-grid gap-2">
                <a href="{{ route('crm.leads') }}" class="btn btn-outline-primary"><i class="bi bi-plus-circle me-1"></i> {{ __('admin.create_lead') }}</a>
                <a href="{{ route('reservations.index') }}" class="btn btn-outline-warning text-dark"><i class="bi bi-bookmark-check me-1"></i> {{ __('admin.place_reservation') }}</a>
                <a href="{{ route('deals.index') }}" class="btn btn-outline-success"><i class="bi bi-briefcase me-1"></i> {{ __('admin.create_deal') }}</a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@if($property->latitude && $property->longitude)
<script>
document.addEventListener('DOMContentLoaded', function () {
    const lat = {{ (float) $property->latitude }};
    const lng = {{ (float) $property->longitude }};
    const map = L.map('propertyMap').setView([lat, lng], 15);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    L.marker([lat, lng]).addTo(map)
        .bindPopup(@json('<strong>'.e($property->title).'</strong><br>'.e($property->formatted_price)))
        .openPopup();
});
</script>
@endif
@endsection
