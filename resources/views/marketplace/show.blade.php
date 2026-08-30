@extends('layouts.public')

@section('title', $property->title)

@section('content')
<div class="container py-4">
    <nav class="mb-3" aria-label="breadcrumb">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('marketplace.index') }}">Marketplace</a></li>
            <li class="breadcrumb-item active">{{ $property->city }}</li>
            <li class="breadcrumb-item active">{{ $property->title }}</li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Main Details -->
        <div class="col-lg-8">
            <div class="card mb-4 overflow-hidden">
                <div style="height: 380px; background: #0f172a;" class="d-flex align-items-center justify-content-center text-white-50">
                    <div class="text-center">
                        <i class="bi bi-building fs-1 mb-2 d-block text-white"></i>
                        <span class="fs-5">{{ $property->title }}</span>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                        <div>
                            <span class="badge bg-primary fs-6 me-2">{{ $property->listing_type }}</span>
                            <span class="badge bg-light text-dark border fs-6">{{ $property->propertyType?->name }}</span>
                        </div>
                        <h3 class="fw-bold text-success mb-0 brand-font">{{ $property->formatted_price }}</h3>
                    </div>

                    <h2 class="brand-font mb-2">{{ $property->title }}</h2>
                    <p class="text-muted"><i class="bi bi-geo-alt-fill text-danger me-1"></i>{{ $property->address }}, {{ $property->city }}, Tanzania</p>

                    <div class="row g-3 text-center my-4 py-3 bg-light rounded-3">
                        <div class="col-3 border-end">
                            <div class="text-muted small">Bedrooms</div>
                            <div class="fw-bold fs-5">{{ $property->bedrooms ?? '-' }}</div>
                        </div>
                        <div class="col-3 border-end">
                            <div class="text-muted small">Bathrooms</div>
                            <div class="fw-bold fs-5">{{ $property->bathrooms ?? '-' }}</div>
                        </div>
                        <div class="col-3 border-end">
                            <div class="text-muted small">Area Size</div>
                            <div class="fw-bold fs-5">{{ $property->area_size ? "{$property->area_size} {$property->area_unit}" : '-' }}</div>
                        </div>
                        <div class="col-3">
                            <div class="text-muted small">Status</div>
                            <div class="fw-bold text-success fs-6">{{ $property->status }}</div>
                        </div>
                    </div>

                    <h5 class="brand-font mb-2">Description</h5>
                    <div class="text-secondary mb-4" style="line-height: 1.7;">
                        {!! nl2br(e($property->description)) !!}
                    </div>

                    <h5 class="brand-font mb-2">Amenities & Features</h5>
                    <div class="d-flex flex-wrap gap-2 mb-4">
                        @forelse($property->amenities as $am)
                            <span class="badge bg-light text-dark border p-2"><i class="bi bi-check-circle-fill text-success me-1"></i>{{ $am->name }}</span>
                        @empty
                            <span class="text-muted small">No specific amenities listed.</span>
                        @endforelse
                    </div>

                    @if($property->latitude && $property->longitude)
                        <h5 class="brand-font mb-2">Location Map</h5>
                        <div id="publicPropertyMap" style="height: 300px;" class="rounded-3 border"></div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar Inquiry Form -->
        <div class="col-lg-4">
            <div class="card p-4 shadow-sm mb-4 sticky-top" style="top: 90px;">
                <h5 class="brand-font mb-3">Inquire or Schedule Viewing</h5>
                <form action="{{ route('marketplace.inquire') }}" method="POST">
                    @csrf
                    <input type="hidden" name="property_id" value="{{ $property->id }}">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Your Full Name *</label>
                        <input type="text" name="name" class="form-control" placeholder="John Doe" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Phone Number *</label>
                        <input type="text" name="phone" class="form-control" placeholder="+255 7..." required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Email Address</label>
                        <input type="email" name="email" class="form-control" placeholder="john@example.com">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Your Inquiry / Preferred Date</label>
                        <textarea name="message" rows="3" class="form-control" placeholder="I am interested in viewing this property this Saturday..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">
                        <i class="bi bi-envelope-fill me-1"></i> Send Property Inquiry
                    </button>
                </form>
                <div class="text-center text-muted small mt-3">
                    <i class="bi bi-shield-lock text-success me-1"></i> Your details are safe with {{ current_organization()?->name }}
                </div>
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
    const map = L.map('publicPropertyMap').setView([lat, lng], 15);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    L.marker([lat, lng]).addTo(map).bindPopup("{{ $property->title }}").openPopup();
});
</script>
@endif
@endsection
