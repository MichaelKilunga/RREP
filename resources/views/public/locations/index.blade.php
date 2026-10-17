@extends('layouts.public')

@section('title', __('marketplace.locations_page_title'))

@section('content')
<section class="page-hero hero-img-plot py-4 py-lg-5 text-center">
    <div class="container">
        <span class="badge bg-primary rounded-pill px-3 py-1.5 mb-2">{{ __('marketplace.regional_directory') }}</span>
        <h1 class="brand-font text-white mb-2 text-balance">{{ __('marketplace.explore_by_location') }}</h1>
        <p class="text-white-50 mx-auto mb-0 max-w-2xl">{{ __('marketplace.locations_subtitle') }}</p>
    </div>
</section>

<div class="container py-4 py-lg-5">
    <div class="row g-3 g-md-4">
        @foreach($locations as $loc)
            <div class="col-12 col-sm-6 col-lg-4">
                <a href="{{ route('public.locations.show', $loc['slug']) }}" class="card text-decoration-none overflow-hidden rounded-4 border-0 shadow-sm position-relative text-white transition-zoom-parent h-100">
                    <div class="ratio ratio-4x3">
                        <img src="{{ $loc['image'] }}" class="object-fit-cover transition-zoom" alt="{{ $loc['name'] }}" loading="lazy" decoding="async">
                        <div class="bg-dark bg-opacity-50"></div>
                        <div class="d-flex flex-column justify-content-end p-3 p-md-4">
                            <span class="badge bg-primary rounded-pill align-self-start mb-2">{{ __('marketplace.properties_count', ['count' => $loc['count']]) }}</span>
                            <h2 class="h4 brand-font text-white mb-1">{{ $loc['name'] }}</h2>
                            <p class="small text-white-50 mb-2 line-clamp-2">{{ $loc['desc'] }}</p>
                            <span class="small fw-bold text-white">{{ __('marketplace.view_all_in', ['city' => $loc['name']]) }} <i class="bi bi-arrow-right ms-1"></i></span>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    <!-- All Tanzanian regions as quick search chips -->
    <div class="mt-4 mt-lg-5">
        <span class="section-tag">{{ __('marketplace.all_locations') }}</span>
        <h2 class="h4 brand-font mb-3">{{ __('marketplace.browse_by_location') }}</h2>
        <div class="d-flex flex-wrap gap-2">
            @foreach(tz_regions() as $region)
                <a href="{{ route('public.properties', ['city' => $region]) }}" class="btn btn-light border rounded-pill btn-sm px-3">
                    <i class="bi bi-geo-alt text-danger me-1"></i>{{ $region }}
                </a>
            @endforeach
        </div>
    </div>
</div>
@endsection
