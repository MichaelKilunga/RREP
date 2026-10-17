@extends('layouts.public')

@section('title', __('marketplace.location_page_title', ['city' => $cityName]))
@section('meta_description', __('marketplace.location_meta', ['count' => $totalCount, 'city' => $cityName]))

@section('styles')
<style>
    .filter-sticky { top: 84px; }
</style>
@endsection

@section('content')
<!-- Location hero -->
<section class="page-hero hero-img-plot py-4 py-lg-5">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb small mb-2">
                <li class="breadcrumb-item"><a href="{{ route('public.home') }}" class="text-white-50 text-decoration-none">{{ __('marketplace.home') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('public.locations') }}" class="text-white-50 text-decoration-none">{{ __('marketplace.locations') }}</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">{{ $cityName }}</li>
            </ol>
        </nav>

        <span class="badge bg-primary rounded-pill px-3 py-1.5 mb-2">{{ __('marketplace.location_guide') }}</span>
        <h1 class="brand-font text-white mb-2">{{ __('marketplace.properties_in', ['city' => $cityName]) }}</h1>
        <p class="text-white-50 mb-3 max-w-2xl">{{ __('marketplace.location_intro', ['city' => $cityName]) }}</p>

        <div class="row g-2 max-w-3xl">
            <div class="col-6 col-md-3">
                <div class="bg-white bg-opacity-10 border border-light border-opacity-25 rounded-3 p-2 text-center">
                    <div class="h4 fw-extrabold text-white mb-0">{{ $totalCount }}</div>
                    <small class="text-white-50 text-uppercase fw-semibold"><i class="bi bi-building text-warning me-1"></i>{{ __('marketplace.total_listings') }}</small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="bg-white bg-opacity-10 border border-light border-opacity-25 rounded-3 p-2 text-center">
                    <div class="h4 fw-extrabold text-white mb-0">{{ $housesCount }}</div>
                    <small class="text-white-50 text-uppercase fw-semibold"><i class="bi bi-house-door text-info me-1"></i>{{ __('marketplace.houses_villas') }}</small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="bg-white bg-opacity-10 border border-light border-opacity-25 rounded-3 p-2 text-center">
                    <div class="h4 fw-extrabold text-white mb-0">{{ $plotsCount }}</div>
                    <small class="text-white-50 text-uppercase fw-semibold"><i class="bi bi-map text-success me-1"></i>{{ __('marketplace.surveyed_plots') }}</small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="bg-white bg-opacity-10 border border-light border-opacity-25 rounded-3 p-2 text-center">
                    <div class="h4 fw-extrabold text-white mb-0">{{ $commercialCount }}</div>
                    <small class="text-white-50 text-uppercase fw-semibold"><i class="bi bi-briefcase text-warning me-1"></i>{{ __('marketplace.commercial') }}</small>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="container py-4">
    <div class="row g-4">
        <!-- Scoped search: sticky sidebar on lg+, offcanvas below lg -->
        <div class="col-lg-3">
            <div class="offcanvas-lg offcanvas-start" tabindex="-1" id="filterOffcanvas" aria-labelledby="locationFilterLabel">
                <div class="offcanvas-header border-bottom">
                    <h2 class="offcanvas-title h5 brand-font mb-0" id="locationFilterLabel"><i class="bi bi-search text-primary me-2"></i>{{ __('marketplace.search_in', ['city' => $cityName]) }}</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#filterOffcanvas" aria-label="{{ __('app.cancel') }}"></button>
                </div>
                <div class="offcanvas-body p-3 p-lg-0">
                    <div class="card border shadow-sm p-3 p-xl-4 rounded-4 bg-white w-100 sticky-lg-top filter-sticky">
                        <h2 class="h5 brand-font mb-3 d-none d-lg-block"><i class="bi bi-search text-primary me-2"></i>{{ __('marketplace.search_in', ['city' => $cityName]) }}</h2>
                        <form action="{{ route('public.properties') }}" method="GET" data-no-loading>
                            <input type="hidden" name="city" value="{{ $cityName }}">

                            <div class="mb-3">
                                <label for="locKeyword" class="form-label small fw-bold text-muted">{{ __('marketplace.keyword_or_id') }}</label>
                                <input type="search" id="locKeyword" name="q" class="form-control" placeholder="{{ __('marketplace.neighborhood_placeholder') }}" autocomplete="off">
                            </div>
                            <div class="mb-3">
                                <label for="locListingType" class="form-label small fw-bold text-muted">{{ __('marketplace.listing_purpose') }}</label>
                                <select id="locListingType" name="listing_type" class="form-select">
                                    <option value="">{{ __('marketplace.any_purpose') }}</option>
                                    <option value="Sale">{{ __('marketplace.buy') }}</option>
                                    <option value="Rent">{{ __('marketplace.rent') }}</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="locBedrooms" class="form-label small fw-bold text-muted">{{ __('app.bedrooms') }}</label>
                                <select id="locBedrooms" name="bedrooms" class="form-select">
                                    <option value="">{{ __('marketplace.any') }}</option>
                                    @foreach([1, 2, 3, 4] as $bed)
                                        <option value="{{ $bed }}">{{ __('marketplace.beds_plus', ['count' => $bed]) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 fw-bold shadow-sm">
                                <i class="bi bi-search me-1"></i>{{ __('marketplace.search_in', ['city' => $cityName]) }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-9 min-w-0">
            @if($projectsInLocation->count())
                <div class="mb-4 mb-lg-5">
                    <span class="section-tag">{{ __('marketplace.developments_in', ['city' => $cityName]) }}</span>
                    <h2 class="h4 brand-font mb-3">{{ __('marketplace.featured_projects_in', ['city' => $cityName]) }}</h2>
                    <div class="row g-3 g-md-4">
                        @foreach($projectsInLocation as $proj)
                            <div class="col-12 col-md-6">
                                @include('public.partials.project-card', ['proj' => $proj])
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="page-header">
                <div class="min-w-0">
                    <span class="section-tag">{{ __('marketplace.marketplace_listings') }}</span>
                    <h2 class="brand-font mb-0">{{ __('marketplace.all_properties_in', ['city' => $cityName]) }}</h2>
                </div>
                <div class="page-actions">
                    <button type="button" class="btn btn-outline-secondary rounded-pill d-lg-none" data-bs-toggle="offcanvas" data-bs-target="#filterOffcanvas" aria-controls="filterOffcanvas">
                        <i class="bi bi-funnel me-1"></i>{{ __('marketplace.filters') }}
                    </button>
                </div>
            </div>

            <div class="row g-3 g-md-4">
                @forelse($properties as $p)
                    <div class="col-12 col-sm-6 col-xl-4">
                        @include('public.partials.property-card', ['p' => $p])
                    </div>
                @empty
                    <div class="col-12">
                        <div class="card empty-state">
                            <div class="empty-icon"><i class="bi bi-geo-alt"></i></div>
                            <h5>{{ __('marketplace.no_properties_in', ['city' => $cityName]) }}</h5>
                            <p class="small mb-3 max-w-xl mx-auto">{{ __('marketplace.no_listings_hint') }}</p>
                            <a href="{{ route('public.properties') }}" class="btn btn-primary rounded-pill px-4 fw-bold">{{ __('marketplace.view_nationwide') }}</a>
                        </div>
                    </div>
                @endforelse
            </div>

            @if($properties->hasPages())
                <div class="mt-4 overflow-x-auto pb-1">
                    {{ $properties->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
