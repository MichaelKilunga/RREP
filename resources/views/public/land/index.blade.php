@extends('layouts.public')

@section('title', __('marketplace.land_page_title'))

@section('styles')
<style>
    .filter-sticky { top: 84px; }
</style>
@endsection

@section('content')
@php
    $regionOptions = collect(tz_regions())->merge($cities)->filter()->unique()->sort()->values();
    $activeFilters = collect(request()->except('page'))->filter(fn ($value) => $value !== null && $value !== '')->count();
    $whatsappGeneral = 'https://wa.me/'.setting('contact_whatsapp', '255784100200').'?text='.urlencode(__('marketplace.whatsapp_general'));
@endphp

<!-- Page hero -->
<section class="page-hero hero-img-plot py-4 py-lg-5">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb small mb-2">
                <li class="breadcrumb-item"><a href="{{ route('public.home') }}" class="text-white-50 text-decoration-none">{{ __('marketplace.home') }}</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">{{ __('marketplace.land_plots') }}</li>
            </ol>
        </nav>
        <span class="badge bg-success bg-opacity-75 border border-light border-opacity-25 rounded-pill px-3 py-1.5 mb-2 text-wrap">
            <i class="bi bi-compass text-warning me-1"></i>{{ __('marketplace.land_badge') }}
        </span>
        <h1 class="brand-font text-white mb-2 text-balance">{!! __('marketplace.land_title') !!}</h1>
        <p class="text-white-50 mb-0 max-w-2xl">{{ __('marketplace.land_subtitle') }}</p>
    </div>
</section>

<div class="container py-4">
    <!-- Value propositions -->
    <div class="row g-2 g-md-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="card h-100 p-2.5 p-md-3 d-flex flex-row align-items-center gap-3">
                <span class="btn-icon bg-success text-white flex-shrink-0"><i class="bi bi-compass fs-5"></i></span>
                <div class="min-w-0">
                    <h2 class="h6 brand-font mb-0">{{ __('marketplace.gps_beacon_relocation') }}</h2>
                    <small class="text-muted d-none d-sm-block">{{ __('marketplace.gps_beacon_desc') }}</small>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card h-100 p-2.5 p-md-3 d-flex flex-row align-items-center gap-3">
                <span class="btn-icon bg-primary text-white flex-shrink-0"><i class="bi bi-file-earmark-check fs-5"></i></span>
                <div class="min-w-0">
                    <h2 class="h6 brand-font mb-0">{{ __('marketplace.title_verification') }}</h2>
                    <small class="text-muted d-none d-sm-block">{{ __('marketplace.title_verification_desc') }}</small>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card h-100 p-2.5 p-md-3 d-flex flex-row align-items-center gap-3">
                <span class="btn-icon bg-warning text-dark flex-shrink-0"><i class="bi bi-phone fs-5"></i></span>
                <div class="min-w-0">
                    <h2 class="h6 brand-font mb-0">{{ __('marketplace.installments') }}</h2>
                    <small class="text-muted d-none d-sm-block">{{ __('marketplace.installments_desc') }}</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Filters: sticky sidebar on lg+, offcanvas drawer below lg -->
        <div class="col-lg-3">
            <div class="offcanvas-lg offcanvas-start" tabindex="-1" id="filterOffcanvas" aria-labelledby="landFilterLabel">
                <div class="offcanvas-header border-bottom">
                    <h2 class="offcanvas-title h5 brand-font mb-0" id="landFilterLabel"><i class="bi bi-funnel text-success me-2"></i>{{ __('marketplace.filters') }}</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#filterOffcanvas" aria-label="{{ __('app.cancel') }}"></button>
                </div>
                <div class="offcanvas-body p-3 p-lg-0">
                    <div class="card border shadow-sm p-3 p-xl-4 rounded-4 bg-white w-100 sticky-lg-top filter-sticky">
                        <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                            <h2 class="h5 brand-font mb-0"><i class="bi bi-funnel text-success me-2"></i>{{ __('marketplace.filters') }}</h2>
                            <a href="{{ route('public.land') }}" class="btn btn-sm btn-link text-danger text-decoration-none p-0 fw-semibold">{{ __('marketplace.reset_all') }}</a>
                        </div>
                        <form action="{{ route('public.land') }}" method="GET" data-no-loading>
                            <div class="mb-3">
                                <label for="landCity" class="form-label small fw-bold text-muted">{{ __('marketplace.region') }}</label>
                                <select id="landCity" name="city" class="form-select">
                                    <option value="">{{ __('marketplace.all_regions') }}</option>
                                    @foreach($regionOptions as $region)
                                        <option value="{{ $region }}" @selected(request('city') === $region)>{{ $region }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="landMinAcres" class="form-label small fw-bold text-muted">{{ __('marketplace.min_acres') }}</label>
                                <input type="number" id="landMinAcres" name="min_acres" class="form-control" inputmode="decimal" min="0" step="0.1" placeholder="0.5" value="{{ request('min_acres') }}">
                            </div>
                            <div class="mb-3">
                                <label for="landMaxPrice" class="form-label small fw-bold text-muted">{{ __('marketplace.max_price') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text">TSh</span>
                                    <input type="number" id="landMaxPrice" name="max_price" class="form-control" inputmode="decimal" min="0" placeholder="50,000,000" value="{{ request('max_price') }}">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-success w-100 fw-bold shadow-sm"><i class="bi bi-search me-1"></i>{{ __('marketplace.filter') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Results -->
        <div class="col-lg-9 min-w-0">
            <div class="page-header">
                <div class="min-w-0">
                    <h2 class="brand-font mb-1">{{ __('marketplace.available_land') }}</h2>
                    <p class="text-muted small mb-0">{{ __('marketplace.showing_plots', ['count' => $landListings->total()]) }}</p>
                </div>
                <div class="page-actions">
                    <button type="button" class="btn btn-outline-secondary rounded-pill d-lg-none" data-bs-toggle="offcanvas" data-bs-target="#filterOffcanvas" aria-controls="filterOffcanvas">
                        <i class="bi bi-funnel me-1"></i>{{ __('marketplace.filters') }}
                        @if($activeFilters > 0)
                            <span class="badge bg-success rounded-pill ms-1">{{ $activeFilters }}</span>
                        @endif
                    </button>
                    <a href="{{ route('public.services.land_survey') }}" class="btn btn-outline-success rounded-pill fw-bold">
                        <i class="bi bi-compass me-1"></i>{{ __('marketplace.need_survey') }}
                    </a>
                </div>
            </div>

            <div class="row g-3 g-md-4">
                @forelse($landListings as $p)
                    <div class="col-12 col-sm-6 col-xl-4">
                        @include('public.partials.land-card', ['p' => $p])
                    </div>
                @empty
                    <div class="col-12">
                        <div class="card empty-state">
                            <div class="empty-icon"><i class="bi bi-map"></i></div>
                            <h5>{{ __('marketplace.no_land_found') }}</h5>
                            <p class="small mb-3 max-w-xl mx-auto">{{ __('marketplace.no_land_hint') }}</p>
                            <div class="d-flex flex-wrap justify-content-center gap-2">
                                <a href="{{ route('public.land') }}" class="btn btn-success rounded-pill px-4 fw-bold">{{ __('marketplace.reset_filters') }}</a>
                                <a href="{{ $whatsappGeneral }}" target="_blank" rel="noopener" class="btn btn-whatsapp rounded-pill px-4"><i class="bi bi-whatsapp me-1"></i>{{ __('marketplace.whatsapp') }}</a>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>

            @if($landListings->hasPages())
                <div class="mt-4 overflow-x-auto pb-1">
                    {{ $landListings->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
