@extends('layouts.public')

@section('title', 'Explore Verified Real Estate Properties')

@section('content')
<!-- Hero Search Section -->
<section class="hero-banner">
    <div class="container text-center">
        <h1 class="brand-font display-5 fw-bold mb-2">Discover Prime Real Estate & Land in Tanzania</h1>
        <p class="lead mb-4 text-white-50">Verified properties, cadastral land parcels, luxury villas, and commercial spaces</p>

        <!-- Search Bar Card -->
        <div class="card p-3 p-md-4 shadow-lg mx-auto text-dark" style="max-width: 950px; border-radius: 1rem;">
            <form action="{{ route('marketplace.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-3">
                    <input type="text" name="city" class="form-control" placeholder="City / Location (e.g. Dar es Salaam)" value="{{ request('city') }}">
                </div>
                <div class="col-md-3">
                    <select name="type" class="form-select">
                        <option value="">All Property Types</option>
                        @foreach($types as $t)
                            <option value="{{ $t->id }}" {{ request('type') == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="listing_type" class="form-select">
                        <option value="">For Sale / Rent</option>
                        <option value="Sale" {{ request('listing_type') == 'Sale' ? 'selected' : '' }}>Buy (Sale)</option>
                        <option value="Rent" {{ request('listing_type') == 'Rent' ? 'selected' : '' }}>Rent</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100 py-2 fw-bold"><i class="bi bi-search me-1"></i> Search Properties</button>
                </div>
            </form>
        </div>
    </div>
</section>

<!-- Property Listings Grid -->
<section class="py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="brand-font mb-0">Featured & Available Properties</h3>
                <p class="text-muted small mb-0">Showing {{ $properties->total() }} verified listings</p>
            </div>
        </div>

        <div class="row g-4">
            @forelse($properties as $p)
                <div class="col-md-6 col-lg-4">
                    <div class="card property-card h-100 bg-white">
                        <div class="position-relative" style="height: 220px; background: #e2e8f0;">
                            <div class="w-100 h-100 d-flex align-items-center justify-content-center text-muted">
                                <i class="bi bi-building fs-1 text-secondary"></i>
                            </div>
                            <span class="position-absolute top-0 start-0 m-3 badge bg-primary fs-6">{{ $p->listing_type }}</span>
                            <span class="position-absolute bottom-0 start-0 m-3 badge bg-dark text-white p-2 fs-6 shadow">{{ $p->formatted_price }}</span>
                        </div>
                        <div class="card-body p-4 d-flex flex-column">
                            <span class="badge bg-light text-dark border align-self-start mb-2">{{ $p->propertyType?->name }}</span>
                            <h5 class="brand-font mb-2">
                                <a href="{{ route('marketplace.show', $p) }}" class="text-dark text-decoration-none">{{ $p->title }}</a>
                            </h5>
                            <p class="text-muted small mb-3"><i class="bi bi-geo-alt me-1 text-danger"></i>{{ $p->address }}, {{ $p->city }}</p>

                            <div class="mt-auto border-top pt-3 d-flex justify-content-between text-muted small">
                                <span><i class="bi bi-door-open me-1"></i>{{ $p->bedrooms ? $p->bedrooms . ' Beds' : '-' }}</span>
                                <span><i class="bi bi-badge-ad me-1"></i>{{ $p->area_size ? $p->area_size . ' ' . $p->area_unit : '-' }}</span>
                                <span><i class="bi bi-eye me-1"></i>{{ $p->views_count }} views</span>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-top p-3 text-center">
                            <a href="{{ route('marketplace.show', $p) }}" class="btn btn-outline-primary btn-sm w-100 fw-bold">View Full Details</a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center text-muted py-5">
                    <h5>No properties found matching your search.</h5>
                    <a href="{{ route('marketplace.index') }}" class="btn btn-primary btn-sm mt-2">Clear Filters</a>
                </div>
            @endforelse
        </div>

        <div class="mt-4 d-flex justify-content-center">
            {{ $properties->links() }}
        </div>
    </div>
</section>
@endsection
