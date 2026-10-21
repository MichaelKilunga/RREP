@extends('layouts.public')

{{-- No title section: the homepage uses the owner-editable SEO defaults from the layout. --}}

@section('styles')
<style>
    .hero-search .form-control, .hero-search .form-select, .hero-search .btn-search { min-height: 48px; }
    .scroll-x-md-grid { padding-top: 0.5rem; padding-bottom: 0.75rem; }
    @media (min-width: 768px) {
        .scroll-x-md-grid { overflow: visible; flex-wrap: wrap !important; scroll-snap-type: none; }
    }
</style>
@endsection

@section('content')
@php
    $contactPhone = setting('contact_phone', '+255 784 100 200');
    $whatsappGeneral = 'https://wa.me/'.setting('contact_whatsapp', '255784100200').'?text='.urlencode(__('marketplace.whatsapp_general'));
    $popularLocations = count($locations) ? $locations : [
        ['name' => 'Dar es Salaam', 'slug' => 'dar-es-salaam'],
        ['name' => 'Morogoro', 'slug' => 'morogoro'],
        ['name' => 'Dodoma', 'slug' => 'dodoma'],
        ['name' => 'Arusha', 'slug' => 'arusha'],
        ['name' => 'Mwanza', 'slug' => 'mwanza'],
        ['name' => 'Zanzibar', 'slug' => 'zanzibar'],
    ];
@endphp

<!-- 1. HERO + SEARCH -->
<section class="hero-section py-4 py-lg-5">
    @php
        $heroSlides = array_slice(array_values(array_unique(array_filter([
            setting('landing_hero_image') ?: asset('images/hero-dodoma-plot.jpg'),
            setting('landing_hero_image_2') ?: asset('images/hero-survey-team.jpg'),
            setting('landing_hero_image_3') ?: asset('images/hero-aerial-plots.jpg'),
        ]))), 0, 3);
    @endphp
    @if(count($heroSlides) > 1)
        <div class="hero-slides" aria-hidden="true">
            @foreach($heroSlides as $slide)
                <div class="hero-slide {{ $loop->first ? 'is-active' : '' }}" style="background-image: url('{{ $slide }}')"></div>
            @endforeach
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const slides = document.querySelectorAll('.hero-slide');
                if (slides.length < 2) return;
                let current = 0;
                setInterval(function () {
                    slides[current].classList.remove('is-active');
                    current = (current + 1) % slides.length;
                    slides[current].classList.add('is-active');
                }, 3500);
            });
        </script>
    @endif
    <div class="container py-2 py-lg-4">
        <div class="text-center max-w-3xl mx-auto mb-4">
            <span class="badge bg-primary rounded-pill px-3 py-2 fw-semibold mb-3 text-wrap">
                <i class="bi bi-patch-check-fill me-1"></i>{{ setting_trans('landing_hero_badge_text', 'Portal Iliyothibitishwa ya Ardhi', 'Verified Land & Survey Portal') }}
            </span>
            <h1 class="hero-title text-white mb-3 text-balance">
                {{ setting_trans('landing_hero_title', 'Miradi ya Viwanja & Upimaji wa Ardhi Tanzania', 'Cadastral Land & Master Projects in Tanzania') }}
            </h1>
            <p class="lead text-white-50 mb-0 mx-auto max-w-2xl">
                {{ setting_trans('landing_hero_subtitle', 'Pata viwanja vilivyopimwa vyenye Hati Miliki na Michoro ya Mipango Miji kwa uwazi na haraka.', 'Verified land plots with Title Deeds, Town Planning drawings, and professional geomatics.') }}
            </p>
        </div>

        @if($showSearchSection ?? true)
            <div id="hero-search">
                @include('public.partials.search-bar')
            </div>
        @endif

        <div class="d-flex flex-wrap justify-content-center gap-2 mt-3 mt-sm-4">
            <a href="{{ route('public.projects') }}" class="btn btn-light text-primary btn-lg px-3 px-sm-4 fw-bold rounded-pill shadow flex-fill flex-sm-grow-0">
                <i class="bi bi-map-fill me-2"></i>{{ setting_trans('landing_hero_cta_primary_text', 'Tazama Miradi', 'Explore Projects') }}
            </a>
            <a href="{{ route('public.services') }}" class="btn btn-outline-light btn-lg px-3 px-sm-4 fw-semibold rounded-pill flex-fill flex-sm-grow-0">
                <i class="bi bi-briefcase me-2"></i>{{ setting_trans('landing_hero_cta_secondary_text', 'Huduma Zetu', 'Our Services') }}
            </a>
        </div>
    </div>
</section>

<!-- 2. QUICK BROWSE: property types + popular locations (CMS switch + marketplace module) -->
@if($showBrowseSection ?? true)
<section id="quick-browse" class="py-3 bg-white border-bottom">
    <div class="container">
        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
            <span class="small fw-bold text-muted text-uppercase tracking-wider me-1">{{ __('marketplace.browse_by_type') }}</span>
        </div>
        <div class="scroll-x pb-2">
            <a href="{{ route('public.buy') }}" class="btn btn-light border rounded-pill btn-sm px-3"><i class="bi bi-bag-check text-primary me-1"></i>{{ __('marketplace.for_sale') }}</a>
            <a href="{{ route('public.rent') }}" class="btn btn-light border rounded-pill btn-sm px-3"><i class="bi bi-key text-warning me-1"></i>{{ __('marketplace.for_rent') }}</a>
            <a href="{{ route('public.land') }}" class="btn btn-light border rounded-pill btn-sm px-3"><i class="bi bi-map text-success me-1"></i>{{ __('marketplace.land_plots') }}</a>
            @foreach($propertyTypes as $type)
                @php
                    $typeIcon = match ($type->category) {
                        'Residential' => 'bi-house-door',
                        'Commercial' => 'bi-briefcase',
                        'Land', 'Agricultural' => 'bi-map',
                        default => 'bi-building',
                    };
                @endphp
                <a href="{{ route('public.properties', ['type' => $type->id]) }}" class="btn btn-light border rounded-pill btn-sm px-3">
                    <i class="bi {{ $typeIcon }} text-primary me-1"></i>{{ $type->name }}
                    <span class="badge bg-primary-subtle text-primary rounded-pill ms-1">{{ $type->properties_count }}</span>
                </a>
            @endforeach
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2 mt-2 mb-2">
            <span class="small fw-bold text-muted text-uppercase tracking-wider me-1">{{ __('marketplace.popular_locations') }}</span>
        </div>
        <div class="scroll-x pb-1">
            @foreach($popularLocations as $loc)
                <a href="{{ route('public.locations.show', $loc['slug']) }}" class="btn btn-outline-dark rounded-pill btn-sm px-3">
                    <i class="bi bi-geo-alt-fill text-danger me-1"></i>{{ $loc['name'] }}
                </a>
            @endforeach
            <a href="{{ route('public.locations') }}" class="btn btn-primary rounded-pill btn-sm px-3">
                {{ __('marketplace.all_locations') }} <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
    </div>
</section>
@endif

<!-- 3. FEATURED PROPERTIES (CMS switch + marketplace module) -->
@if(($showFeaturedSection ?? true) && $featuredProperties->count())
<section id="featured-properties" class="py-4 py-lg-5 bg-white">
    <div class="container">
        <div class="page-header header-inline">
            <div class="min-w-0">
                <span class="section-tag">{{ __('marketplace.featured_tag') }}</span>
                <h2 class="brand-font mb-0">{{ __('marketplace.featured_properties') }}</h2>
            </div>
            <a href="{{ route('public.properties') }}" class="btn btn-outline-primary btn-sm rounded-pill fw-semibold">
                {{ __('marketplace.view_all') }} <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
        <div class="row g-3 g-md-4 scroll-x scroll-x-md-grid gap-0 flex-nowrap">
            @foreach($featuredProperties as $p)
                <div class="col-10 col-sm-7 col-md-6 col-lg-4">
                    @include('public.partials.property-card', ['p' => $p])
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- 4. LATEST LISTINGS (CMS switch + marketplace module) -->
@if($showLatestSection ?? true)
<section id="latest-listings" class="py-4 py-lg-5 {{ $featuredProperties->count() ? 'bg-light' : 'bg-white' }}">
    <div class="container">
        <div class="page-header header-inline">
            <div class="min-w-0">
                <span class="section-tag">{{ __('marketplace.latest_tag') }}</span>
                <h2 class="brand-font mb-0">{{ __('marketplace.latest_listings') }}</h2>
            </div>
            <a href="{{ route('public.properties') }}" class="btn btn-outline-primary btn-sm rounded-pill fw-semibold">
                {{ __('marketplace.view_all') }} <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
        @if($latestProperties->count())
            <div class="row g-3 g-md-4 scroll-x scroll-x-md-grid gap-0 flex-nowrap">
                @foreach($latestProperties as $p)
                    <div class="col-10 col-sm-7 col-md-6 col-lg-4">
                        @include('public.partials.property-card', ['p' => $p])
                    </div>
                @endforeach
            </div>
        @else
            <div class="card empty-state">
                <div class="empty-icon"><i class="bi bi-houses"></i></div>
                <h5>{{ __('marketplace.no_listings_yet') }}</h5>
                <p class="small mb-3 max-w-xl mx-auto">{{ __('marketplace.no_listings_hint') }}</p>
                <a href="{{ $whatsappGeneral }}" target="_blank" rel="noopener" class="btn btn-whatsapp rounded-pill px-4"><i class="bi bi-whatsapp me-1"></i>{{ __('marketplace.chat_whatsapp') }}</a>
            </div>
        @endif
    </div>
</section>
@endif

<!-- 5. LAND OPPORTUNITIES (CMS switch + marketplace module) -->
@if(($showLandSection ?? true) && $landOpportunities->count())
<section id="land-opportunities" class="py-4 py-lg-5 bg-white">
    <div class="container">
        <div class="page-header header-inline">
            <div class="min-w-0">
                <span class="section-tag text-success">{{ __('marketplace.land_tag') }}</span>
                <h2 class="brand-font mb-0">{{ __('marketplace.land_opportunities') }}</h2>
            </div>
            <a href="{{ route('public.land') }}" class="btn btn-outline-success btn-sm rounded-pill fw-semibold">
                {{ __('marketplace.view_all') }} <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
        <div class="row g-3 g-md-4 scroll-x scroll-x-md-grid gap-0 flex-nowrap">
            @foreach($landOpportunities as $p)
                <div class="col-10 col-sm-7 col-md-6 col-lg-3">
                    @include('public.partials.land-card', ['p' => $p])
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- 6. SERVICES -->
@if(setting('landing_services_enabled', '1') === '1')
<section class="py-4 py-lg-5 bg-light">
    <div class="container">
        <div class="page-header header-inline">
            <div class="min-w-0">
                <span class="section-tag">{{ setting_trans('landing_services_tag', 'Ufumbuzi wa Kitaalamu', 'Professional Solutions') }}</span>
                <h2 class="brand-font mb-0">{{ setting_trans('landing_services_title', 'Huduma Zetu 8 Kuu', 'Our 8 Core Services') }}</h2>
            </div>
            <a href="{{ route('public.services') }}" class="btn btn-outline-primary btn-sm rounded-pill fw-semibold">
                {{ __('app.view_all') }} <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="row g-2 g-md-3 g-lg-4">
            @foreach($services as $service)
                <div class="col-6 col-lg-3">
                    <div class="card h-100 p-2.5 p-md-3 p-lg-4 hover-shadow transition-all d-flex flex-column">
                        @if($service->thumbnail_url)
                            <div class="ratio ratio-21x9 rounded-3 overflow-hidden mb-2"><img src="{{ $service->thumbnail_url }}" alt="{{ $service->title }}" class="object-fit-cover" loading="lazy"></div>
                        @else
                            <span class="btn-icon bg-primary-subtle text-primary mb-2 fs-5"><i class="bi {{ $service->icon }}"></i></span>
                        @endif
                        <h3 class="h6 fw-bold text-dark mb-1 line-clamp-2">{{ $service->title }}</h3>
                        <p class="text-muted small mb-2 line-clamp-2 d-none d-sm-block">{{ $service->short_description }}</p>
                        <div class="mt-auto pt-2">
                            @if($service->cta_url)
                                <a href="{{ $service->cta_url }}" class="btn btn-sm btn-outline-primary w-100 rounded-pill">{{ $service->cta_label }}</a>
                            @else
                                <button type="button" class="btn btn-sm btn-outline-primary w-100 rounded-pill" data-bs-toggle="modal" data-bs-target="#quoteModal" data-service="{{ $service->title }}">
                                    {{ $service->cta_label }}
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- 7. LAND PROJECTS SHOWCASE -->
@if(setting('landing_projects_enabled', '1') === '1')
<section class="py-4 py-lg-5 bg-white">
    <div class="container">
        <div class="page-header header-inline">
            <div class="min-w-0">
                <span class="section-tag"><i class="bi bi-layers-half me-1"></i>{{ setting_trans('landing_projects_tag', 'Ramani za Viwanja & Mipango Miji', 'Master Plans & Cadastral GIS') }}</span>
                <h2 class="brand-font mb-1">{{ setting_trans('landing_projects_title', 'Miradi ya Viwanja & Ramani za Shapefile', 'Land Projects & Interactive Plot Maps') }}</h2>
                <p class="text-muted small mb-0">{{ setting_trans('landing_projects_subtitle', 'Vipimo halisi vya GIS, michoro ya mipango miji na status ya viwanja vilivyopimwa.', 'Verified GIS boundaries, cadastral master plans, and live plot availability.') }}</p>
            </div>
            <a href="{{ route('public.projects') }}" class="btn btn-outline-primary btn-sm rounded-pill fw-semibold">
                {{ setting_trans('landing_projects_cta_text', 'Miradi Yote', 'Explore All') }} <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="row g-3 g-md-4 scroll-x scroll-x-md-grid gap-0 flex-nowrap">
            @forelse($featuredProjects as $project)
                @if($project->isServiceProject())
                    <div class="col-11 col-sm-9 col-md-6 col-lg-4">
                        @include('public.partials.service-project-card', ['project' => $project])
                    </div>
                @else
                @php
                    $total = max(1, (int) $project->total_units);
                    $sold = (int) $project->sold_plots;
                    $soldPercent = min(100, max(0, round(($sold / $total) * 100)));
                @endphp
                <div class="col-11 col-sm-9 col-md-6 col-lg-4">
                    <div class="card border-0 rounded-4 shadow-sm hover-shadow transition-all h-100 overflow-hidden d-flex flex-column">
                        <div class="ratio ratio-21x9 bg-dark">
                            @if($project->hero_image)
                                <img src="{{ $project->hero_image_url }}" alt="{{ $project->title }}" class="object-fit-cover opacity-90" loading="lazy" decoding="async">
                            @else
                                <div class="d-flex align-items-center justify-content-center text-white-50 bg-dark">
                                    <div class="text-center p-3">
                                        <i class="bi bi-map text-primary opacity-50 display-5 d-block mb-1"></i>
                                        <span class="small text-uppercase tracking-wider fw-semibold opacity-75">{{ __('marketplace.cadastral_map') }}</span>
                                    </div>
                                </div>
                            @endif
                            <div class="bg-dark bg-opacity-50 p-3 d-flex flex-column justify-content-between">
                                <div class="d-flex align-items-start justify-content-between gap-2">
                                    <span class="badge bg-dark bg-opacity-75 border border-white border-opacity-25 rounded-pill px-2.5 py-1.5 text-truncate">
                                        <i class="bi bi-geo-alt-fill text-danger me-1"></i>{{ $project->council ?? $project->location_name }}
                                    </span>
                                    <span class="badge {{ $project->available_units > 0 ? 'bg-success' : 'bg-danger' }} rounded-pill px-2.5 py-1.5 fw-bold flex-shrink-0">
                                        {{ $project->available_units > 0 ? __('app.available') : __('app.sold_out') }}
                                    </span>
                                </div>
                                <div class="d-flex align-items-end justify-content-between gap-2">
                                    <h3 class="h5 fw-bold text-white mb-0 text-shadow-sm line-clamp-1">{{ $project->title }}</h3>
                                    <span class="badge bg-primary rounded-2 shadow-xs flex-shrink-0"><i class="bi bi-layers-half me-1"></i>{{ __('marketplace.gis_ready') }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="card-body p-3 d-flex flex-column">
                            <p class="text-secondary small mb-3 line-clamp-2">{{ $project->description }}</p>

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
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 pt-3 border-top mt-auto">
                                <div class="min-w-0">
                                    <small class="text-muted d-block fw-semibold">{{ __('app.starting_price') }}</small>
                                    <span class="fw-extrabold text-primary fs-5" title="{{ format_currency($project->starting_price) }}">{{ format_money_short($project->starting_price) }}</span>
                                </div>
                                <a href="{{ route('public.projects.show', $project->slug) }}" class="btn btn-primary rounded-pill px-3.5 fw-bold btn-sm shadow-sm">
                                    {{ __('app.view_map') }} <i class="bi bi-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            @empty
                <div class="col-12">
                    <div class="card empty-state">
                        <div class="empty-icon"><i class="bi bi-diagram-3"></i></div>
                        <h5>{{ __('app.no_projects_found') }}</h5>
                        <p class="small mb-3">{{ __('marketplace.no_listings_hint') }}</p>
                        <a href="{{ route('public.projects') }}" class="btn btn-primary rounded-pill px-4 fw-bold">{{ __('app.show_all_projects') }}</a>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</section>
@endif

<!-- 8. TRUST METRICS -->
@if(setting('landing_trust_enabled', '1') === '1')
<section class="py-4 py-lg-5 bg-dark text-white bg-scene hero-img-plot">
    <div class="container text-center">
        <div class="row g-3 g-md-4">
            <div class="col-6 col-md-3 trust-stat">
                <div class="stat-value fw-extrabold text-white mb-1">{{ setting('landing_stat_1_override') ?: ($stats['projects'].'+') }}</div>
                <span class="stat-label text-white-50 text-uppercase fw-bold d-block mx-auto" style="max-width: 11rem;">{{ setting_trans('landing_stat_1_label', 'Miradi ya Mipango', 'Master Projects') }}</span>
            </div>
            <div class="col-6 col-md-3 trust-stat">
                <div class="stat-value fw-extrabold text-white mb-1">{{ setting('landing_stat_2_override') ?: ($stats['plots'].'+') }}</div>
                <span class="stat-label text-white-50 text-uppercase fw-bold d-block mx-auto" style="max-width: 11rem;">{{ setting_trans('landing_stat_2_label', 'Viwanja Vilivyopimwa', 'Surveyed Plots') }}</span>
            </div>
            <div class="col-6 col-md-3 trust-stat">
                <div class="stat-value fw-extrabold text-white mb-1">{{ setting('landing_stat_3_override') ?: ($stats['services'] ?? 8) }}</div>
                <span class="stat-label text-white-50 text-uppercase fw-bold d-block mx-auto" style="max-width: 11rem;">{{ setting_trans('landing_stat_3_label', 'Huduma Kuu', 'Core Services') }}</span>
            </div>
            <div class="col-6 col-md-3 trust-stat">
                <div class="stat-value fw-extrabold text-white mb-1">{{ setting('landing_stat_4_override') ?: ($stats['clients'].'+') }}</div>
                <span class="stat-label text-white-50 text-uppercase fw-bold d-block mx-auto" style="max-width: 11rem;">{{ setting_trans('landing_stat_4_label', 'Wateja Walioridhika', 'Satisfied Clients') }}</span>
            </div>
        </div>
    </div>
</section>
@endif

<!-- 9. OWNER / SURVEY CTA -->
@if(setting('landing_owner_cta_enabled', '1') === '1')
<section class="py-4 py-lg-5 bg-light">
    <div class="container">
        <div class="card page-hero hero-img-survey border-0 rounded-4 overflow-hidden shadow-lg">
            <div class="card-body p-3 p-sm-4 p-md-5 text-center">
                <span class="badge bg-warning text-dark rounded-pill px-3 py-2 fw-bold mb-3 text-wrap">
                    <i class="bi bi-compass-fill me-1"></i>{{ setting_trans('landing_cta_banner_badge', 'Wataalamu wa Upimaji Ardhi', 'Geomatics & Land Advisory') }}
                </span>
                <h2 class="fw-extrabold text-white mb-3 text-balance">
                    {{ setting_trans('landing_cta_banner_title', 'Unahitaji Kupima Ardhi au Kupata Kiwanja?', 'Ready to Secure Your Land or Request a Survey?') }}
                </h2>
                <p class="text-white-50 mb-4 max-w-2xl mx-auto">
                    {{ setting_trans('landing_cta_banner_subtitle', 'Wasiliana na wataalamu wetu wa geomatics na ardhi kupata msaada wa haraka na wa uhakika.', 'Contact our geomatics consultants and surveyors today for swift assistance.') }}
                </p>
                <div class="d-grid gap-2 d-sm-flex flex-wrap justify-content-sm-center">
                    <a href="{{ $whatsappGeneral }}" target="_blank" rel="noopener" class="btn btn-whatsapp btn-lg px-4 fw-bold rounded-pill shadow">
                        <i class="bi bi-whatsapp me-2"></i>{{ __('marketplace.chat_whatsapp') }}
                    </a>
                    <a href="tel:{{ preg_replace('/\s+/', '', $contactPhone) }}" class="btn btn-light text-primary btn-lg px-4 fw-bold rounded-pill shadow">
                        <i class="bi bi-telephone-fill me-2"></i>{{ format_phone_tz($contactPhone) }}
                    </a>
                    <a href="{{ route('public.contact') }}" class="btn btn-outline-light btn-lg px-4 fw-bold rounded-pill">
                        <i class="bi bi-envelope-fill me-2"></i>{{ setting_trans('landing_cta_banner_btn_text', 'Wasiliana Nasi', 'Contact Us') }}
                    </a>
                </div>
                <small class="text-white-50 d-block mt-3"><i class="bi bi-clock me-1"></i>{{ __('marketplace.office_hours') }}</small>
            </div>
        </div>
    </div>
</section>
@endif

<!-- Service Quote Modal -->
<div class="modal fade" id="quoteModal" tabindex="-1" aria-labelledby="quoteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header border-0 bg-light rounded-top-4">
                <h2 class="modal-title h5 fw-bold text-dark mb-0" id="quoteModalLabel">{{ __('marketplace.quote_title') }}</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('app.cancel') }}"></button>
            </div>
            <div class="modal-body p-4">
                <form action="{{ route('public.inquire') }}" method="POST" id="quoteModalForm">
                    @csrf
                    <input type="hidden" name="preferred_contact_method" value="WhatsApp">
                    <div class="mb-3">
                        <label for="modalServiceName" class="form-label fw-bold small">{{ __('marketplace.service_selected') }}</label>
                        <input type="text" id="modalServiceName" name="service_name" class="form-control bg-light" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="quoteName" class="form-label fw-bold small">{{ __('marketplace.full_name') }}</label>
                        <input type="text" id="quoteName" name="name" class="form-control" required autocomplete="name" placeholder="{{ __('marketplace.name_placeholder') }}">
                    </div>
                    <div class="mb-3">
                        <label for="quotePhone" class="form-label fw-bold small">{{ __('marketplace.phone_whatsapp') }}</label>
                        <input type="tel" id="quotePhone" name="phone" class="form-control" required data-tz-phone autocomplete="tel" placeholder="0784 100 200">
                    </div>
                    <div class="mb-3">
                        <label for="quoteMessage" class="form-label fw-bold small">{{ __('marketplace.details_location') }}</label>
                        <textarea id="quoteMessage" name="message" class="form-control" rows="3" required placeholder="{{ __('marketplace.details_placeholder') }}"></textarea>
                    </div>
                </form>
            </div>
            {{-- Sticky footer: the submit button never scrolls out of view --}}
            <div class="modal-footer border-top bg-white p-3 p-md-4 pt-3">
                <button type="submit" form="quoteModalForm" class="btn btn-primary w-100 fw-bold py-2 rounded-pill m-0">{{ __('marketplace.send_inquiry') }}</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const quoteModal = document.getElementById('quoteModal');
    if (quoteModal) {
        quoteModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const service = (button && button.getAttribute('data-service')) || @json(__('marketplace.general_inquiry'));
            document.getElementById('modalServiceName').value = service;
        });
    }
});
</script>
@endsection
