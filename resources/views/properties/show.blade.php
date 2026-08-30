@extends('layouts.app')

@section('title', $property->title)

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <h3 class="brand-font mb-0">{{ $property->title }}</h3>
            <span class="badge badge-status-{{ strtolower(str_replace(' ', '', $property->status)) }} fs-6">{{ $property->status }}</span>
        </div>
        <p class="text-muted small mb-0"><i class="bi bi-geo-alt me-1"></i>{{ $property->address }}, {{ $property->city }} &bull; Code: <strong>{{ $property->property_code }}</strong></p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('properties.edit', $property) }}" class="btn btn-light border btn-sm"><i class="bi bi-pencil me-1"></i> Edit</a>
        <a href="{{ route('marketplace.show', $property) }}" target="_blank" class="btn btn-outline-primary btn-sm"><i class="bi bi-globe me-1"></i> View on Marketplace</a>
    </div>
</div>

<div class="row g-4">
    <!-- Main Property Info -->
    <div class="col-lg-8">
        <!-- Details Card -->
        <div class="card mb-4">
            <div class="card-header brand-font d-flex justify-content-between">
                <span>Property Specifications</span>
                <span class="text-success fw-bold fs-5">{{ $property->formatted_price }}</span>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-4 text-center">
                    <div class="col-3 p-2 border-end">
                        <div class="text-muted small">Bedrooms</div>
                        <div class="fw-bold fs-5">{{ $property->bedrooms ?? '-' }}</div>
                    </div>
                    <div class="col-3 p-2 border-end">
                        <div class="text-muted small">Bathrooms</div>
                        <div class="fw-bold fs-5">{{ $property->bathrooms ?? '-' }}</div>
                    </div>
                    <div class="col-3 p-2 border-end">
                        <div class="text-muted small">Area Size</div>
                        <div class="fw-bold fs-5">{{ $property->area_size ? "{$property->area_size} {$property->area_unit}" : '-' }}</div>
                    </div>
                    <div class="col-3 p-2">
                        <div class="text-muted small">Type</div>
                        <div class="fw-bold fs-6">{{ $property->propertyType?->name }}</div>
                    </div>
                </div>

                <h6 class="brand-font mb-2">Description</h6>
                <div class="p-3 bg-light rounded-3 mb-4 text-secondary" style="line-height: 1.6;">
                    {!! nl2br(e($property->description)) !!}
                </div>

                <h6 class="brand-font mb-2">Amenities</h6>
                <div class="d-flex flex-wrap gap-2 mb-4">
                    @forelse($property->amenities as $am)
                        <span class="badge bg-light text-dark border p-2"><i class="bi bi-check-circle-fill text-success me-1"></i>{{ $am->name }}</span>
                    @empty
                        <span class="text-muted small">No amenities listed.</span>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- GIS Map Preview if Lat/Lng -->
        @if($property->latitude && $property->longitude)
            <div class="card mb-4">
                <div class="card-header brand-font"><i class="bi bi-geo-alt-fill text-danger me-2"></i>Geospatial GIS Location</div>
                <div class="card-body p-0">
                    <div id="propertyMap" style="height: 300px; width: 100%;"></div>
                </div>
            </div>
        @endif

        <!-- AI Valuation Analysis Card -->
        @if($valuation)
            <div class="card border-primary-subtle bg-primary-subtle p-3 mb-4">
                <div class="d-flex gap-3">
                    <i class="bi bi-stars text-warning fs-2"></i>
                    <div>
                        <h6 class="brand-font text-primary mb-1">RehoSpace AI Automated Valuation Analysis</h6>
                        <p class="small text-dark mb-0">{!! nl2br(e($valuation['analysis'])) !!}</p>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Sidebar Column -->
    <div class="col-lg-4">
        <!-- Owner Details -->
        <div class="card mb-4">
            <div class="card-header brand-font">Owner / Landlord</div>
            <div class="card-body">
                @if($property->owner)
                    <div class="fw-bold fs-6">{{ $property->owner->full_name }}</div>
                    <div class="text-muted small mb-2">{{ $property->owner->company_name }}</div>
                    <div class="small mb-1"><i class="bi bi-telephone me-2 text-primary"></i>{{ $property->owner->phone }}</div>
                    <div class="small mb-2"><i class="bi bi-envelope me-2 text-primary"></i>{{ $property->owner->email ?? 'N/A' }}</div>
                    <span class="badge bg-success-subtle text-success"><i class="bi bi-shield-check me-1"></i>KYC Verified</span>
                @else
                    <div class="text-muted small">Owned directly by {{ current_organization()?->name }}</div>
                @endif
            </div>
        </div>

        <!-- Quick CRM Actions -->
        <div class="card mb-4">
            <div class="card-header brand-font">CRM Actions</div>
            <div class="card-body d-grid gap-2">
                <a href="{{ route('crm.leads') }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-plus-circle me-1"></i> Create Lead for this Property</a>
                <a href="{{ route('reservations.index') }}" class="btn btn-outline-warning btn-sm text-dark"><i class="bi bi-bookmark-check me-1"></i> Place Reservation Hold</a>
                <a href="{{ route('deals.index') }}" class="btn btn-outline-success btn-sm"><i class="bi bi-briefcase me-1"></i> Create Sales Deal</a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@if($property->latitude && $property->longitude)
<script>
document.addEventListener('DOMContentLoaded', function () {
    const lat = {{ $property->latitude }};
    const lng = {{ $property->longitude }};
    const map = L.map('propertyMap').setView([lat, lng], 15);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    L.marker([lat, lng]).addTo(map)
        .bindPopup("<strong>{{ $property->title }}</strong><br>{{ $property->formatted_price }}")
        .openPopup();
});
</script>
@endif
@endsection
