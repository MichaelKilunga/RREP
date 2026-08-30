@extends('layouts.public')

@section('title', 'Explore Properties by City & Location across Tanzania')

@section('content')
<div class="bg-dark text-white py-5 text-center" style="background: linear-gradient(135deg, #091224 0%, #1e3a8a 100%);">
    <div class="container py-3">
        <span class="badge bg-primary px-3 py-2 rounded-pill mb-2">Regional Directory</span>
        <h1 class="brand-font display-5 fw-bold text-white mb-2">Explore Real Estate by Location</h1>
        <p class="lead text-white-50 mx-auto mb-0" style="max-width: 650px;">
            Find verified properties, land parcels, houses, and luxury developments across prime regions in Tanzania.
        </p>
    </div>
</div>

<div class="container py-5">
    <div class="row g-4">
        @foreach($locations as $loc)
            <div class="col-md-6 col-lg-4">
                <a href="{{ route('public.locations.show', $loc['slug']) }}" class="card text-decoration-none overflow-hidden rounded-4 border-0 shadow-sm position-relative text-white transition-zoom-parent" style="height: 280px;">
                    <img src="{{ $loc['image'] }}" class="w-100 h-100 object-fit-cover transition-zoom" alt="{{ $loc['name'] }}" loading="lazy">
                    <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(180deg, rgba(0,0,0,0.1) 0%, rgba(15,23,42,0.88) 100%);"></div>
                    <div class="position-absolute bottom-0 start-0 p-4 w-100">
                        <span class="badge bg-primary mb-2">{{ $loc['count'] }} Properties</span>
                        <h3 class="brand-font text-white mb-1">{{ $loc['name'] }}</h3>
                        <p class="small text-white-50 mb-3">{{ $loc['desc'] }}</p>
                        <span class="btn btn-outline-light btn-sm rounded-pill px-3 fw-bold">
                            View All Properties in {{ $loc['name'] }} <i class="bi bi-arrow-right ms-1"></i>
                        </span>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
</div>
@endsection
