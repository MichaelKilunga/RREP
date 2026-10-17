@extends('layouts.public')

@section('title', __('pages.projects_page_title'))
@section('meta_description', __('app.explore_verified_land_projects_inspect_interactive'))

@section('content')
<!-- Hero -->
<section class="page-hero hero-img-plot py-4 py-md-5">
    <div class="container py-2 py-md-3">
        <div class="row align-items-center g-3 g-md-4">
            <div class="col-12 col-lg-8">
                <a href="{{ route('public.home') }}" class="text-white-50 text-decoration-none small d-inline-flex align-items-center mb-2 hover-white">
                    <i class="bi bi-arrow-left me-1"></i> {{ __('app.back_to_home') }}
                </a>
                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                    <span class="badge bg-white bg-opacity-10 border border-white border-opacity-25 text-white px-2.5 py-1.5 rounded-pill">
                        <i class="bi bi-layers-half me-1"></i> {{ __('pages.cadastral_gis_master_plans') }}
                    </span>
                    <span class="badge bg-success bg-opacity-25 border border-success border-opacity-50 text-white px-2.5 py-1.5 rounded-pill">
                        <i class="bi bi-file-earmark-check-fill me-1"></i> {{ __('pages.title_deed_verified') }}
                    </span>
                </div>
                <h1 class="fw-extrabold mb-2">{{ __('app.land_projects_and_interactive_plot_maps') }}</h1>
                <p class="text-white-50 mb-0 max-w-2xl">{{ __('app.explore_verified_land_projects_inspect_interactive') }}</p>
            </div>
            <div class="col-12 col-lg-4 text-lg-end">
                <div class="d-inline-flex flex-column align-items-lg-end bg-white bg-opacity-10 backdrop-blur px-3 py-2.5 rounded-4 border border-white border-opacity-10">
                    <span class="text-white-50 small text-uppercase fw-semibold">{{ __('app.active_projects') }}</span>
                    <span class="fs-3 fw-extrabold text-white lh-1">{{ $projects->total() }}</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Filters -->
<section class="py-3 py-md-4 bg-light border-bottom">
    <div class="container">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
            <form action="{{ route('public.projects') }}" method="GET" class="filter-bar" role="search">
                <div class="flex-grow-1">
                    <label for="projectSearch" class="form-label small fw-bold text-muted mb-1">{{ __('app.search_project_or_council') }}</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="search" id="projectSearch" name="search" class="form-control border-start-0 bg-light ps-0" placeholder="{{ __('app.halmashauri_region_project_name') }}" value="{{ request('search') }}">
                    </div>
                </div>
                <div>
                    <label for="projectRegion" class="form-label small fw-bold text-muted mb-1">{{ __('app.region_council_filter') }}</label>
                    <select id="projectRegion" name="region" class="form-select bg-light" onchange="this.form.submit()">
                        <option value="">{{ __('app.all_regions_and_councils') }}</option>
                        @foreach($regions as $reg)
                            <option value="{{ $reg }}" @selected(request('region') == $reg)>{{ $reg }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-actions align-self-end">
                    <button type="submit" class="btn btn-primary fw-bold rounded-3 px-4">
                        <i class="bi bi-funnel me-1"></i> {{ __('app.filter_2') }}
                    </button>
                    @if(request()->filled('search') || request()->filled('region'))
                        <a href="{{ route('public.projects') }}" class="btn btn-outline-secondary rounded-3" title="{{ __('pages.reset_filters') }}" aria-label="{{ __('pages.reset_filters') }}">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>
</section>

<!-- Project cards -->
<section class="py-4 py-md-5 bg-light">
    <div class="container">
        <div class="row g-3 g-md-4">
            @forelse($projects as $project)
                @php
                    $total = max(1, (int) $project->total_units);
                    $sold = (int) $project->sold_plots;
                    $soldPercent = min(100, max(0, (int) round(($sold / $total) * 100)));
                    $isAvailable = (int) $project->available_units > 0;
                @endphp
                <div class="col-12 col-lg-6">
                    <div class="card border-0 rounded-4 shadow-sm hover-shadow transition-all h-100 overflow-hidden bg-white d-flex flex-column">
                        <!-- Image / map banner -->
                        <a href="{{ route('public.projects.show', $project->slug) }}" class="property-card-img-wrapper d-block text-decoration-none">
                            @if($project->hero_image)
                                <img src="{{ $project->hero_image_url }}" alt="{{ $project->title }}" loading="lazy" onerror="this.onerror=null;this.src='{{ asset('images/project-placeholder.jpg') }}'">
                            @else
                                <img src="{{ asset('images/project-placeholder.jpg') }}" alt="{{ $project->title }}" loading="lazy">
                            @endif
                            <div class="position-absolute top-0 start-0 w-100 h-100 p-3 d-flex flex-column justify-content-between bg-dark bg-opacity-20">
                                <div class="d-flex align-items-start justify-content-between gap-2">
                                    <span class="badge bg-dark bg-opacity-75 text-white border border-white border-opacity-25 px-2.5 py-1.5 rounded-pill text-truncate">
                                        <i class="bi bi-geo-alt-fill text-danger me-1"></i>{{ $project->council ?? $project->district ?? $project->location_name }}
                                    </span>
                                    <span class="badge {{ $isAvailable ? 'bg-success' : 'bg-danger' }} px-2.5 py-1.5 rounded-pill fw-bold flex-shrink-0">
                                        {{ $isAvailable ? __('app.available') : __('app.sold_out') }}
                                    </span>
                                </div>
                                <div class="d-flex align-items-end justify-content-between gap-2">
                                    <h2 class="fw-bold text-white mb-0 text-shadow-sm fs-5 line-clamp-2 min-w-0">{{ $project->title }}</h2>
                                    <span class="badge bg-primary px-2.5 py-1 rounded-2 text-white flex-shrink-0">
                                        <i class="bi bi-layers-half me-1"></i>{{ __('pages.gis_ready') }}
                                    </span>
                                </div>
                            </div>
                        </a>

                        <div class="card-body p-3 p-sm-4 d-flex flex-column">
                            <p class="text-secondary small mb-3 line-clamp-2">
                                {{ $project->description ?: __('app.prime_surveyed_land_project_with_complete') }}
                            </p>

                            <!-- Allocation progress -->
                            <div class="p-3 bg-light rounded-3 mb-3">
                                <div class="d-flex align-items-center justify-content-between mb-1.5 small fw-semibold">
                                    <span class="text-dark"><i class="bi bi-pie-chart-fill text-primary me-1"></i>{{ __('app.plot_allocation_progress') }}</span>
                                    <span class="text-success fw-bold">{{ $soldPercent }}% {{ __('app.allocated') }}</span>
                                </div>
                                <div class="progress rounded-pill mb-3" role="progressbar" aria-label="{{ __('app.plot_allocation_progress') }}" aria-valuenow="{{ $soldPercent }}" aria-valuemin="0" aria-valuemax="100" style="height: 8px;">
                                    <div class="progress-bar bg-success rounded-pill" style="width: {{ $soldPercent }}%"></div>
                                </div>
                                <div class="row text-center g-2">
                                    <div class="col-3">
                                        <div class="bg-white py-2 px-1 rounded-2 border shadow-xs">
                                            <small class="d-block text-muted text-uppercase fw-bold">{{ __('app.total_2') }}</small>
                                            <strong class="text-dark">{{ $project->total_units }}</strong>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="bg-white py-2 px-1 rounded-2 border shadow-xs">
                                            <small class="d-block text-muted text-uppercase fw-bold">{{ __('app.sold') }}</small>
                                            <strong class="text-danger">{{ $project->sold_plots }}</strong>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="bg-white py-2 px-1 rounded-2 border shadow-xs">
                                            <small class="d-block text-muted text-uppercase fw-bold">{{ __('app.available') }}</small>
                                            <strong class="text-success">{{ $project->available_units }}</strong>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="bg-white py-2 px-1 rounded-2 border shadow-xs">
                                            <small class="d-block text-muted text-uppercase fw-bold">{{ __('app.deposit') }}</small>
                                            <strong class="text-primary">{{ (int) $project->initial_payment_percent }}%</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Feature chips -->
                            <div class="d-flex flex-wrap gap-1.5 mb-3">
                                <span class="badge bg-white text-secondary border rounded-pill px-2.5 py-1 fw-medium">
                                    <i class="bi bi-file-earmark-check-fill text-success me-1"></i>{{ __('app.title_deed_guaranteed') }}
                                </span>
                                <span class="badge bg-white text-secondary border rounded-pill px-2.5 py-1 fw-medium">
                                    <i class="bi bi-signpost-split-fill text-primary me-1"></i>{{ __('app.access_roads') }}
                                </span>
                                <span class="badge bg-white text-secondary border rounded-pill px-2.5 py-1 fw-medium">
                                    <i class="bi bi-lightning-charge-fill text-warning me-1"></i>{{ __('app.utilities') }}
                                </span>
                                @if($project->grace_period_days)
                                    <span class="badge bg-white text-secondary border rounded-pill px-2.5 py-1 fw-medium">
                                        <i class="bi bi-clock-history text-info me-1"></i>{{ $project->grace_period_days }} {{ __('app.days_grace') }}
                                    </span>
                                @endif
                            </div>

                            <!-- Price & CTA -->
                            <div class="d-flex flex-wrap align-items-center justify-content-between pt-3 border-top mt-auto gap-2">
                                <div class="min-w-0">
                                    <small class="text-muted d-block fw-semibold">{{ __('app.starting_price') }}</small>
                                    <span class="fw-extrabold text-primary fs-5">{{ format_money_short($project->starting_price) }}</span>
                                    <small class="text-muted d-block">{{ format_currency($project->starting_price, null, 0) }}</small>
                                </div>
                                <a href="{{ route('public.projects.show', $project->slug) }}" class="btn btn-primary rounded-pill px-4 fw-bold d-inline-flex align-items-center flex-grow-1 flex-sm-grow-0 justify-content-center">
                                    <span>{{ __('app.view_plot_map_and_shapes') }}</span>
                                    <i class="bi bi-arrow-right ms-2"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="empty-state bg-white rounded-4 shadow-sm border">
                        <div class="empty-icon"><i class="bi bi-map"></i></div>
                        <h5>{{ __('app.no_projects_found') }}</h5>
                        <p class="small mb-3">{{ __('app.try_adjusting_your_search_keywords_or') }}</p>
                        <a href="{{ route('public.projects') }}" class="btn btn-primary rounded-pill px-4 fw-semibold">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> {{ __('app.show_all_projects') }}
                        </a>
                    </div>
                </div>
            @endforelse

            @if($projects->hasPages())
                <div class="col-12 d-flex justify-content-center mt-3">
                    {{ $projects->links() }}
                </div>
            @endif
        </div>
    </div>
</section>
@endsection
