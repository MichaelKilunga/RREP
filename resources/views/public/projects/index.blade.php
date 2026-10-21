@extends('layouts.public')

@section('title', __('pages.projects_page_title'))
@section('meta_description', __('app.explore_verified_land_projects_inspect_interactive'))

@section('content')
<!-- Hero -->
<section class="page-hero hero-img-plot py-4 py-md-5">
    <div class="container py-2 py-md-3">
        <div class="row align-items-center g-3 g-md-4">
            <div class="col-12 col-lg-9">
                <a href="{{ route('public.home') }}" class="text-white-50 text-decoration-none small d-inline-flex align-items-center mb-2 hover-white">
                    <i class="bi bi-arrow-left me-1"></i> {{ __('app.back_to_home') }}
                </a>
                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                    @php $projBadge1 = setting_trans('landing_projects_page_badge', 'Ramani za Viwanja & Mipango Miji', 'Cadastral GIS & Master Plans'); @endphp
                    @if(trim((string) $projBadge1) !== '')
                    <span class="badge bg-white bg-opacity-10 border border-white border-opacity-25 text-white px-2.5 py-1.5 rounded-pill">
                        <i class="bi bi-layers-half me-1"></i> {{ $projBadge1 }}
                    </span>
                    @endif
                    @php $projBadge2 = setting_trans('landing_projects_page_badge_2', 'Hati Miliki Zimethibitishwa', 'Title Deed Verified'); @endphp
                    @if(trim((string) $projBadge2) !== '')
                    <span class="badge bg-success bg-opacity-25 border border-success border-opacity-50 text-white px-2.5 py-1.5 rounded-pill">
                        <i class="bi bi-file-earmark-check-fill me-1"></i> {{ $projBadge2 }}
                    </span>
                    @endif
                </div>
                <h1 class="fw-extrabold mb-2">{{ setting_trans('landing_projects_page_title', 'Miradi ya Viwanja & Ramani za Mipango Miji', 'Land Projects & Interactive Plot Maps') }}</h1>
                @php $projSubtitle = setting_trans('landing_projects_page_subtitle', 'Chagua miradi ya viwanja vilivyopimwa, kagua ramani halisi za GIS na hali ya upatikanaji wa viwanja.', 'Explore verified land projects, inspect interactive GIS shapefile boundaries, cadastral layouts, and plot availability.'); @endphp
                @if(trim((string) $projSubtitle) !== '')
                    <p class="text-white-50 mb-0 max-w-2xl">{{ $projSubtitle }}</p>
                @endif
            </div>
        </div>
    </div>
</section>

<!-- Filters -->
<!-- Category switcher + one-tap pills -->
<section class="py-3 bg-white border-bottom">
    <div class="container">
        <div class="d-flex flex-wrap align-items-center gap-2 project-switcher">
            <div class="btn-group" role="group" aria-label="{{ __('pages.project_type_filter') }}">
                <a href="{{ route('public.projects') }}" class="btn btn-sm {{ ! ($category ?? null) ? 'btn-primary' : 'btn-outline-primary' }} fw-semibold">{{ __('pages.projects_all') }}</a>
                <a href="{{ route('public.projects', ['category' => 'land']) }}" class="btn btn-sm {{ ($category ?? null) === 'land' ? 'btn-primary' : 'btn-outline-primary' }} fw-semibold">{{ __('pages.projects_land') }}</a>
                <a href="{{ route('public.projects', ['category' => 'service']) }}" class="btn btn-sm {{ ($category ?? null) === 'service' ? 'btn-primary' : 'btn-outline-primary' }} fw-semibold">{{ __('pages.projects_service') }}</a>
            </div>
        </div>

        @if(($category ?? null) !== 'service' && ($landLocations ?? collect())->count())
            <div class="scroll-x mt-2 pb-1" aria-label="{{ __('pages.filter_by_location') }}">
                @foreach($landLocations as $loc)
                    <a href="{{ route('public.projects', array_filter(['category' => $category ?? null, 'location' => $loc])) }}"
                       class="btn btn-sm rounded-pill px-3 {{ request('location') === $loc ? 'btn-dark' : 'btn-outline-dark' }}">
                        <i class="bi bi-geo-alt me-1"></i>{{ $loc }}
                    </a>
                @endforeach
            </div>
        @endif

        @if(($category ?? null) === 'service' && ($serviceCategories ?? collect())->count())
            <div class="scroll-x mt-2 pb-1" aria-label="{{ __('pages.filter_by_service') }}">
                @foreach($serviceCategories as $svc)
                    <a href="{{ route('public.projects', ['category' => 'service', 'service' => $svc->slug]) }}"
                       class="btn btn-sm rounded-pill px-3 {{ request('service') === $svc->slug ? 'btn-dark' : 'btn-outline-dark' }}">
                        <i class="bi bi-briefcase me-1"></i>{{ $svc->title }}
                    </a>
                @endforeach
            </div>
        @endif
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
                @if($project->isServiceProject())
                    <div class="col-12 col-sm-6 col-xl-4">
                        @include('public.partials.service-project-card', ['project' => $project])
                    </div>
                @else
                <div class="col-12 col-sm-6 col-xl-4">
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

                        <div class="card-body p-3 d-flex flex-column">
                            <p class="text-secondary small mb-3 line-clamp-2">
                                {{ $project->description ?: __('app.prime_surveyed_land_project_with_complete') }}
                            </p>

                            <!-- Allocation progress -->
                            <!-- Availability (compact) -->
                            <div class="mb-3">
                                <div class="d-flex align-items-center justify-content-between small mb-1">
                                    <span class="fw-semibold text-dark">
                                        <i class="bi bi-grid-3x3-gap me-1 text-success"></i><span class="text-success fw-bold">{{ $project->available_units }}</span>/{{ $project->total_units }} {{ __('app.plots_available_short') }}
                                    </span>
                                    @if((int) $project->initial_payment_percent > 0)
                                        <span class="text-muted">{{ __('app.deposit_short', ['percent' => (int) $project->initial_payment_percent]) }}</span>
                                    @endif
                                </div>
                                <div class="progress progress-thin rounded-pill" role="progressbar" aria-label="{{ __('app.plot_allocation_progress') }}" aria-valuenow="{{ 100 - $soldPercent }}" aria-valuemin="0" aria-valuemax="100">
                                    <div class="progress-bar bg-success rounded-pill" style="width: {{ 100 - $soldPercent }}%"></div>
                                </div>
                            </div>
                            <!-- Price & CTA -->
                            <div class="d-flex flex-wrap align-items-center justify-content-between pt-3 border-top mt-auto gap-2">
                                <div class="min-w-0">
                                    <small class="text-muted d-block fw-semibold">{{ __('app.starting_price') }}</small>
                                    <span class="fw-extrabold text-primary fs-5" title="{{ format_currency($project->starting_price, null, 0) }}">{{ format_money_short($project->starting_price) }}</span>
                                </div>
                                <a href="{{ route('public.projects.show', $project->slug) }}" class="btn btn-primary rounded-pill px-4 fw-bold d-inline-flex align-items-center flex-grow-1 flex-sm-grow-0 justify-content-center">
                                    <span>{{ __('app.view_plot_map_and_shapes') }}</span>
                                    <i class="bi bi-arrow-right ms-2"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
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
