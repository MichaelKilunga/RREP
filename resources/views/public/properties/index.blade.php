@extends('layouts.public')

@section('title', $pageTitle)

@section('styles')
<style>
    .filter-sticky { top: 84px; }
    #propertiesMap { height: 300px; }
    @media (min-width: 768px) { #propertiesMap { height: 420px; } }
</style>
@endsection

@section('content')
@php
    $activeFilters = collect(request()->except('page', 'sort'))
        ->filter(fn ($value) => $value !== null && $value !== '' && $value !== [])
        ->count();
@endphp

<!-- Page hero -->
<section class="page-hero hero-img-plot py-4">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb small mb-2">
                <li class="breadcrumb-item"><a href="{{ route('public.home') }}" class="text-white-50 text-decoration-none">{{ __('marketplace.home') }}</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">{{ __('marketplace.properties') }}</li>
            </ol>
        </nav>
        <div class="page-header mb-0">
            <div class="min-w-0">
                <h1 class="brand-font text-white mb-1">{{ $pageTitle }}</h1>
                <p class="text-white-50 small mb-0">{{ __('marketplace.showing_verified', ['count' => $properties->total()]) }}</p>
            </div>
            <div class="page-actions">
                <button type="button" class="btn btn-outline-light rounded-pill d-lg-none" data-bs-toggle="offcanvas" data-bs-target="#filterOffcanvas" aria-controls="filterOffcanvas">
                    <i class="bi bi-funnel me-1"></i>{{ __('marketplace.filters') }}
                    @if($activeFilters > 0)
                        <span class="badge bg-light text-dark rounded-pill ms-1">{{ $activeFilters }}</span>
                    @endif
                </button>
                <button type="button" class="btn btn-light rounded-pill shadow-sm" id="toggleMapBtn" aria-expanded="false" aria-controls="resultsMapContainer">
                    <i class="bi bi-map me-1"></i><span id="mapBtnText">{{ __('marketplace.show_map') }}</span>
                </button>
            </div>
        </div>
    </div>
</section>

<div class="container py-4">
    <!-- Collapsible Leaflet map of this page's results -->
    <div id="resultsMapContainer" class="mb-4 d-none">
        <div class="card border rounded-4 overflow-hidden shadow-sm">
            <div class="card-header bg-white py-2.5 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <strong class="brand-font text-primary small"><i class="bi bi-geo-alt-fill text-danger me-1"></i>{{ __('marketplace.map_title') }}</strong>
                <span class="badge bg-light text-dark border">{{ __('marketplace.on_this_page', ['count' => $properties->count()]) }}</span>
            </div>
            <div id="propertiesMap" class="w-100" role="region" aria-label="{{ __('marketplace.map_title') }}"></div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Filters: static sticky sidebar on lg+, offcanvas drawer below lg -->
        <div class="col-lg-3">
            <div class="offcanvas-lg offcanvas-start" tabindex="-1" id="filterOffcanvas" aria-labelledby="filterOffcanvasLabel">
                <div class="offcanvas-header border-bottom">
                    <h2 class="offcanvas-title h5 brand-font mb-0" id="filterOffcanvasLabel"><i class="bi bi-funnel text-primary me-2"></i>{{ __('marketplace.filter_properties') }}</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#filterOffcanvas" aria-label="{{ __('app.cancel') }}"></button>
                </div>
                <div class="offcanvas-body p-3 p-lg-0">
                    <div class="sticky-lg-top filter-sticky w-100">
                        @include('public.partials.filter-sidebar', ['idPrefix' => 'flt'])
                    </div>
                </div>
            </div>
        </div>

        <!-- Results -->
        <div class="col-lg-9 min-w-0">
            <div class="card p-2.5 mb-3 d-flex flex-row flex-wrap justify-content-between align-items-center gap-2">
                <div class="small text-muted">
                    {{ __('marketplace.showing_range', ['from' => $properties->firstItem() ?? 0, 'to' => $properties->lastItem() ?? 0, 'total' => $properties->total()]) }}
                </div>

                <form action="{{ url()->current() }}" method="GET" id="sortForm" class="d-flex align-items-center gap-2 ms-auto" data-no-loading>
                    @foreach(request()->except('sort', 'page') as $key => $val)
                        @if(is_array($val))
                            @foreach($val as $item)
                                <input type="hidden" name="{{ $key }}[]" value="{{ $item }}">
                            @endforeach
                        @else
                            <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                        @endif
                    @endforeach
                    <label for="sortSelect" class="small fw-bold text-muted text-nowrap mb-0">{{ __('marketplace.sort_by') }}</label>
                    <select id="sortSelect" name="sort" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                        <option value="newest" @selected($sort === 'newest')>{{ __('marketplace.sort_newest') }}</option>
                        <option value="price_asc" @selected($sort === 'price_asc')>{{ __('marketplace.sort_price_asc') }}</option>
                        <option value="price_desc" @selected($sort === 'price_desc')>{{ __('marketplace.sort_price_desc') }}</option>
                        <option value="views" @selected($sort === 'views')>{{ __('marketplace.sort_views') }}</option>
                        <option value="size_desc" @selected($sort === 'size_desc')>{{ __('marketplace.sort_size') }}</option>
                    </select>
                </form>
            </div>

            <div class="row g-3 g-md-4">
                @forelse($properties as $p)
                    <div class="col-12 col-sm-6 col-xl-4">
                        @include('public.partials.property-card', ['p' => $p])
                    </div>
                @empty
                    <div class="col-12">
                        <div class="card empty-state">
                            <div class="empty-icon"><i class="bi bi-search"></i></div>
                            <h5>{{ __('marketplace.no_properties_found') }}</h5>
                            <p class="small mb-3 max-w-xl mx-auto">{{ __('marketplace.no_properties_hint') }}</p>
                            <div class="d-flex flex-wrap justify-content-center gap-2">
                                <a href="{{ route('public.properties') }}" class="btn btn-primary rounded-pill px-4 fw-bold">{{ __('marketplace.reset_filters') }}</a>
                                <a href="https://wa.me/{{ setting('contact_whatsapp', '255784100200') }}?text={{ urlencode(__('marketplace.whatsapp_general')) }}" target="_blank" rel="noopener" class="btn btn-whatsapp rounded-pill px-4"><i class="bi bi-whatsapp me-1"></i>{{ __('marketplace.whatsapp') }}</a>
                            </div>
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

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    let mapInitialized = false;
    let map = null;
    const showLabel = @json(__('marketplace.show_map'));
    const hideLabel = @json(__('marketplace.hide_map'));
    const viewLabel = @json(__('marketplace.view'));

    $('#toggleMapBtn').on('click', function () {
        const container = $('#resultsMapContainer');
        if (container.hasClass('d-none')) {
            container.removeClass('d-none');
            $('#mapBtnText').text(hideLabel);
            $(this).attr('aria-expanded', 'true');

            if (!mapInitialized) {
                map = L.map('propertiesMap').setView([-6.7924, 39.2083], 7);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors'
                }).addTo(map);

                const markers = [];
                @foreach($properties as $prop)
                    @if($prop->latitude && $prop->longitude)
                        (function () {
                            const marker = L.marker([{{ $prop->latitude }}, {{ $prop->longitude }}]).addTo(map);
                            marker.bindPopup(`
                                <div style="min-width: 180px;">
                                    <strong>{{ addslashes($prop->title) }}</strong><br>
                                    <span class="badge bg-primary mt-1">{{ $prop->listing_type }}</span>
                                    <div class="fw-bold text-success mt-1">{{ $prop->formatted_price }}</div>
                                    <a href="{{ route('public.properties.show', $prop) }}" class="btn btn-sm btn-outline-primary w-100 mt-2 py-0">${viewLabel}</a>
                                </div>
                            `);
                            markers.push([{{ $prop->latitude }}, {{ $prop->longitude }}]);
                        })();
                    @endif
                @endforeach

                if (markers.length > 0) {
                    map.fitBounds(markers, { padding: [40, 40] });
                }
                mapInitialized = true;
            } else {
                map.invalidateSize();
            }
            container[0].scrollIntoView({ behavior: 'smooth', block: 'start' });
        } else {
            container.addClass('d-none');
            $('#mapBtnText').text(showLabel);
            $(this).attr('aria-expanded', 'false');
        }
    });
});
</script>
@endsection
