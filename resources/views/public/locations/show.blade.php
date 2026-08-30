@extends('layouts.public')

@section('title', "Properties for Sale & Rent in {$cityName}, Tanzania")
@section('meta_description', "Explore {$totalCount} verified properties, residential houses, and surveyed land plots in {$cityName}. Direct WhatsApp and viewing booking.")

@section('content')
<!-- Hero Section Scoped to Location -->
<div class="bg-dark text-white py-5 position-relative" style="background: linear-gradient(135deg, #091224 0%, #1e3a8a 100%);">
    <div class="container position-relative py-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb small text-white-50 mb-2">
                <li class="breadcrumb-item"><a href="{{ route('public.home') }}" class="text-white-50 text-decoration-none">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('public.locations') }}" class="text-white-50 text-decoration-none">Locations</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">{{ $cityName }}</li>
            </ol>
        </nav>

        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <span class="badge bg-primary px-3 py-2 rounded-pill mb-2">Location Landing Guide</span>
                <h1 class="brand-font display-5 fw-bold text-white mb-2">Properties in {{ $cityName }}</h1>
                <p class="lead text-white-50 mb-4">
                    Discover verified residential homes, surveyed land subdivisions, and high-yield investment properties across {{ $cityName }}.
                </p>

                <!-- Location Statistics Badges -->
                <div class="d-flex flex-wrap gap-2">
                    <span class="badge bg-white bg-opacity-10 text-white p-2 px-3 border border-light border-opacity-25">
                        <i class="bi bi-building me-1 text-warning"></i> <strong>{{ $totalCount }}</strong> Total Listings
                    </span>
                    <span class="badge bg-white bg-opacity-10 text-white p-2 px-3 border border-light border-opacity-25">
                        <i class="bi bi-house-door me-1 text-primary"></i> <strong>{{ $housesCount }}</strong> Houses & Villas
                    </span>
                    <span class="badge bg-white bg-opacity-10 text-white p-2 px-3 border border-light border-opacity-25">
                        <i class="bi bi-map me-1 text-success"></i> <strong>{{ $plotsCount }}</strong> Surveyed Plots
                    </span>
                    <span class="badge bg-white bg-opacity-10 text-white p-2 px-3 border border-light border-opacity-25">
                        <i class="bi bi-briefcase me-1 text-info"></i> <strong>{{ $commercialCount }}</strong> Commercial
                    </span>
                </div>
            </div>

            <!-- Scoped Search Form -->
            <div class="col-lg-5">
                <div class="card p-4 rounded-4 shadow-lg border-0 bg-white text-dark">
                    <h5 class="brand-font mb-3">Search in {{ $cityName }}</h5>
                    <form action="{{ route('public.properties') }}" method="GET">
                        <input type="hidden" name="city" value="{{ $cityName }}">

                        <div class="mb-2">
                            <input type="text" name="q" class="form-control form-control-sm" placeholder="Neighborhood or Area (e.g. Kihonda, Masaki...)">
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <select name="listing_type" class="form-select form-select-sm">
                                    <option value="">Buy or Rent</option>
                                    <option value="Sale">Buy (Sale)</option>
                                    <option value="Rent">Rent</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <select name="bedrooms" class="form-select form-select-sm">
                                    <option value="">Bedrooms</option>
                                    <option value="2">2+ Beds</option>
                                    <option value="3">3+ Beds</option>
                                    <option value="4">4+ Beds</option>
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold shadow-sm">
                            <i class="bi bi-search me-1"></i> Search in {{ $cityName }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container py-5">
    <!-- Featured Projects in this location if any -->
    @if($projectsInLocation->count())
        <div class="mb-5">
            <span class="section-tag">Developments in {{ $cityName }}</span>
            <h3 class="brand-font mb-3">Featured Real Estate Projects in {{ $cityName }}</h3>
            <div class="row g-4">
                @foreach($projectsInLocation as $proj)
                    <div class="col-md-6">
                        @include('public.partials.project-card', ['proj' => $proj])
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- All Listings Grid -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <span class="section-tag">Marketplace Listings</span>
            <h3 class="brand-font mb-0">All Properties in {{ $cityName }}</h3>
        </div>
    </div>

    <div class="row g-4">
        @forelse($properties as $p)
            <div class="col-md-6 col-lg-4">
                @include('public.partials.property-card', ['p' => $p])
            </div>
        @empty
            <div class="col-12 text-center text-muted py-5">
                <h5>No properties currently listed in {{ $cityName }}.</h5>
                <a href="{{ route('public.properties') }}" class="btn btn-primary btn-sm mt-2">View All Nationwide Listings</a>
            </div>
        @endforelse
    </div>

    <div class="mt-5 d-flex justify-content-center">
        {{ $properties->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
