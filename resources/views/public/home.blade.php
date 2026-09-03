@extends('layouts.public')

@section('title', setting('landing_meta_title', 'Verified Real Estate Marketplace & Cadastral Land Survey Platform'))
@section('meta_description', setting('landing_meta_description', 'Discover verified houses, apartments, cadastral surveyed land plots, and prime commercial developments across Tanzania on the RehoSpace digital real estate marketplace.'))

@section('content')

<!-- 02. HERO SECTION -->
@php
    $heroBgImage = setting('landing_hero_image', 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=1600&auto=format&fit=crop&q=80');
@endphp
<style>
    .hero-section::before {
        background: url("{{ $heroBgImage }}") center/cover no-repeat !important;
    }
</style>
<section class="hero-section py-4 py-md-5 py-lg-6 position-relative text-center">
    <div class="container position-relative py-3 py-md-4 py-lg-5" style="z-index: 2;">
        <span class="badge bg-primary bg-opacity-25 text-white border border-light border-opacity-25 px-3 py-2 rounded-pill mb-3 fw-semibold text-wrap" style="max-width: 100%; white-space: normal; line-height: 1.4; font-size: 0.8125rem;">
            <i class="bi {{ setting('landing_hero_badge_icon', 'bi-patch-check-fill') }} text-warning me-1"></i> {{ setting('landing_hero_badge_text', "Tanzania's Premier Real Estate & Land Survey Ecosystem") }}
        </span>
        <h1 class="brand-font hero-title text-white mb-3">
            {!! nl2br(e(setting('landing_hero_title', "Find a Place to Call Home.\nDiscover Opportunities to Invest."))) !!}
        </h1>
        <p class="lead text-white-50 mx-auto mb-4 mb-lg-5 px-2" style="max-width: 750px; font-size: clamp(0.95rem, 2.2vw, 1.15rem); line-height: 1.6;">
            {{ setting('landing_hero_subtitle', 'Discover verified houses, luxury apartments, surveyed cadastral land plots, and master-planned developments in one powerful digital marketplace.') }}
        </p>

        <!-- Search Bar Engine Component -->
        @include('public.partials.search-bar')
    </div>
</section>

<!-- 03. QUICK CATEGORIES -->
@if(setting('landing_categories_enabled', '1') === '1')
<section class="py-5 bg-white border-bottom">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-end mb-4">
            <div>
                <span class="section-tag">{{ setting('landing_categories_tag', 'Marketplace Categories') }}</span>
                <h3 class="brand-font mb-0">{{ setting('landing_categories_title', 'Explore by Property Type') }}</h3>
            </div>
            <a href="{{ setting('landing_categories_cta_url', route('public.properties')) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-bold mt-2 mt-md-0">
                {{ setting('landing_categories_cta_text', 'View All Categories') }} <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="row g-3 g-lg-4">
            @foreach($propertyTypes->take(4) as $type)
                <div class="col-6 col-md-3 col-lg-3">
                    <a href="{{ route('public.properties', ['type' => $type->id]) }}" class="card text-decoration-none h-100 p-3 p-lg-4 text-center border rounded-4 hover-shadow transition" style="background: #f8fafc;">
                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px; font-size: 1.5rem;">
                            <i class="bi bi-{{ $type->icon ?: 'building' }}"></i>
                        </div>
                        <h6 class="brand-font text-dark mb-1">{{ $type->name }}</h6>
                        <span class="text-muted small">{{ $type->properties_count }} Verified Listings</span>
                    </a>
                </div>
            @endforeach
        </div>
        @if($propertyTypes->count() > 4)
        <div class="text-center mt-4 pt-2">
            <a href="{{ setting('landing_categories_cta_url', route('public.properties')) }}" class="btn btn-outline-primary rounded-pill px-4 fw-semibold">
                {{ setting('landing_categories_cta_text', 'View All Categories') }} <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
        @endif
    </div>
</section>
@endif

<!-- 04. FEATURED PROPERTIES -->
@if(setting('landing_featured_enabled', '1') === '1')
<section class="py-5">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-end mb-4">
            <div>
                <span class="section-tag">{{ setting('landing_featured_tag', 'Handpicked Selection') }}</span>
                <h3 class="brand-font mb-0">{{ setting('landing_featured_title', 'Featured Real Estate Listings') }}</h3>
            </div>
            <a href="{{ setting('landing_featured_cta_url', route('public.properties', ['sort' => 'views'])) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-bold mt-2 mt-md-0">
                {{ setting('landing_featured_cta_text', 'Browse All Featured') }} <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="row g-4">
            @forelse($featuredProperties->take(3) as $p)
                <div class="col-md-4 col-lg-4">
                    @include('public.partials.property-card', ['p' => $p])
                </div>
            @empty
                <div class="col-12 text-center text-muted py-5">
                    <h5>No featured properties currently available.</h5>
                </div>
            @endforelse
        </div>
        @if($featuredProperties->isNotEmpty())
        <div class="text-center mt-4 pt-2">
            <a href="{{ setting('landing_featured_cta_url', route('public.properties', ['sort' => 'views'])) }}" class="btn btn-outline-primary rounded-pill px-4 fw-semibold">
                {{ setting('landing_featured_cta_text', 'Browse All Featured') }} <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
        @endif
    </div>
</section>
@endif

<!-- 05. EXPLORE LOCATIONS -->
@if(setting('landing_locations_enabled', '1') === '1')
<section class="py-5 bg-white border-top border-bottom">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-end mb-4">
            <div>
                <span class="section-tag">{{ setting('landing_locations_tag', 'Geographic Reach') }}</span>
                <h3 class="brand-font mb-0">{{ setting('landing_locations_title', 'Explore Properties by Location') }}</h3>
            </div>
            <a href="{{ setting('landing_locations_cta_url', route('public.locations')) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-bold mt-2 mt-md-0">
                {{ setting('landing_locations_cta_text', 'All Regions') }} <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="row g-4">
            @foreach(collect($locations)->take(3) as $loc)
                <div class="col-12 col-md-4 col-lg-4">
                    <a href="{{ route('public.locations.show', $loc['slug']) }}" class="card text-decoration-none overflow-hidden rounded-4 border-0 shadow-sm position-relative text-white" style="height: 240px;">
                        <img src="{{ $loc['image'] }}" class="w-100 h-100 object-fit-cover transition-zoom" alt="{{ $loc['name'] }}" loading="lazy">
                        <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(180deg, rgba(0,0,0,0.1) 0%, rgba(15,23,42,0.85) 100%);"></div>
                        <div class="position-absolute bottom-0 start-0 p-4 w-100">
                            <span class="badge bg-primary mb-2">{{ $loc['count'] }} Properties</span>
                            <h4 class="brand-font text-white mb-1">{{ $loc['name'] }}</h4>
                            <p class="small text-white-50 mb-0">{{ $loc['desc'] }}</p>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
        <div class="text-center mt-4 pt-2">
            <a href="{{ setting('landing_locations_cta_url', route('public.locations')) }}" class="btn btn-outline-primary rounded-pill px-4 fw-semibold">
                {{ setting('landing_locations_cta_text', 'All Regions') }} <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
    </div>
</section>
@endif

<!-- 06. NEWLY LISTED PROPERTIES -->
@if(setting('landing_latest_enabled', '1') === '1')
<section class="py-5">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-end mb-4">
            <div>
                <span class="section-tag">{{ setting('landing_latest_tag', 'Fresh Market Additions') }}</span>
                <h3 class="brand-font mb-0">{{ setting('landing_latest_title', 'Newly Listed Properties') }}</h3>
            </div>
            <a href="{{ setting('landing_latest_cta_url', route('public.properties')) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-bold mt-2 mt-md-0">
                {{ setting('landing_latest_cta_text', 'Explore Marketplace') }} <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="row g-4">
            @forelse($latestProperties->take(3) as $p)
                <div class="col-md-4 col-lg-4">
                    @include('public.partials.property-card', ['p' => $p])
                </div>
            @empty
                <div class="col-12 text-center text-muted py-5">
                    <h5>No listings found.</h5>
                </div>
            @endforelse
        </div>
        @if($latestProperties->isNotEmpty())
        <div class="text-center mt-4 pt-2">
            <a href="{{ setting('landing_latest_cta_url', route('public.properties')) }}" class="btn btn-outline-primary rounded-pill px-4 fw-semibold">
                {{ setting('landing_latest_cta_text', 'Explore Marketplace') }} <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
        @endif
    </div>
</section>
@endif

<!-- 07. FEATURED DEVELOPMENTS & PROJECTS -->
@if(setting('landing_projects_enabled', '1') === '1')
<section class="py-5 bg-light border-top border-bottom">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-end mb-4">
            <div>
                <span class="section-tag">{{ setting('landing_projects_tag', 'Major Opportunities') }}</span>
                <h3 class="brand-font mb-0">{{ setting('landing_projects_title', 'Discover New Developments') }}</h3>
            </div>
            <a href="{{ setting('landing_projects_cta_url', route('public.projects')) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-bold mt-2 mt-md-0">
                {{ setting('landing_projects_cta_text', 'All Projects') }} <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="row g-4">
            @foreach($featuredProjects->take(3) as $proj)
                <div class="col-md-4 col-lg-4">
                    @include('public.partials.project-card', ['proj' => $proj])
                </div>
            @endforeach
        </div>
        @if($featuredProjects->isNotEmpty())
        <div class="text-center mt-4 pt-2">
            <a href="{{ setting('landing_projects_cta_url', route('public.projects')) }}" class="btn btn-outline-primary rounded-pill px-4 fw-semibold">
                {{ setting('landing_projects_cta_text', 'All Projects') }} <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
        @endif
    </div>
</section>
@endif

<!-- 08. LAND & PLOT MARKETPLACE -->
@if(setting('landing_land_enabled', '1') === '1')
<section class="py-5">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-end mb-4">
            <div>
                <span class="section-tag">{{ setting('landing_land_tag', 'Cadastral Surveyed Parcels') }}</span>
                <h3 class="brand-font mb-0">{!! setting('landing_land_title', 'Land & Plot Opportunities') !!}</h3>
            </div>
            <a href="{{ setting('landing_land_cta_url', route('public.land')) }}" class="btn btn-outline-success btn-sm rounded-pill px-3 fw-bold mt-2 mt-md-0">
                {{ setting('landing_land_cta_text', 'All Land Listings') }} <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="row g-4">
            @foreach($landOpportunities->take(4) as $p)
                <div class="col-md-6 col-lg-3">
                    @include('public.partials.land-card', ['p' => $p])
                </div>
            @endforeach
        </div>
        @if($landOpportunities->isNotEmpty())
        <div class="text-center mt-4 pt-2">
            <a href="{{ setting('landing_land_cta_url', route('public.land')) }}" class="btn btn-outline-success rounded-pill px-4 fw-semibold">
                {{ setting('landing_land_cta_text', 'All Land Listings') }} <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
        @endif
    </div>
</section>
@endif

<!-- 09. REAL ESTATE & LAND SURVEY SERVICES -->
@if(setting('landing_services_enabled', '1') === '1')
<section class="py-5 bg-white border-top border-bottom">
    <div class="container">
        <div class="text-center max-w-700 mx-auto mb-5">
            <span class="section-tag">{{ setting('landing_services_tag', 'End-to-End Solutions') }}</span>
            <h2 class="brand-font mb-2">{{ setting('landing_services_title', 'Comprehensive Property & Survey Services') }}</h2>
            <p class="text-muted">{{ setting('landing_services_subtitle', 'From professional land surveying and cadastral beaconing to property marketing and asset management.') }}</p>
        </div>

        <div class="row g-4">
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 p-4 border rounded-4 shadow-sm hover-shadow transition">
                    <div class="rounded-3 bg-primary text-white d-flex align-items-center justify-content-center mb-3" style="width: 48px; height: 48px; font-size: 1.4rem;">
                        <i class="bi {{ setting('landing_service_1_icon', 'bi-compass') }}"></i>
                    </div>
                    <h5 class="brand-font mb-2">{!! setting('landing_service_1_title', 'Land Survey & GIS Mapping') !!}</h5>
                    <p class="text-muted small mb-3">{{ setting('landing_service_1_desc', 'Boundary surveying, cadastral plot beaconing, topographical contours, RTK GPS setting-out, and town planning compliance.') }}</p>
                    <a href="{{ setting('landing_service_1_btn_url', route('public.services.land_survey')) }}" class="fw-bold text-primary text-decoration-none mt-auto small">
                        {{ setting('landing_service_1_btn_text', 'Request Land Survey') }} <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="card h-100 p-4 border rounded-4 shadow-sm hover-shadow transition">
                    <div class="rounded-3 bg-success text-white d-flex align-items-center justify-content-center mb-3" style="width: 48px; height: 48px; font-size: 1.4rem;">
                        <i class="bi {{ setting('landing_service_2_icon', 'bi-houses') }}"></i>
                    </div>
                    <h5 class="brand-font mb-2">{!! setting('landing_service_2_title', 'Property Sales & Marketing') !!}</h5>
                    <p class="text-muted small mb-3">{{ setting('landing_service_2_desc', 'Connect qualified buyers with verified title-deed properties, luxury villas, and commercial real estate assets.') }}</p>
                    <a href="{{ setting('landing_service_2_btn_url', route('public.services.detail', 'property-sales')) }}" class="fw-bold text-success text-decoration-none mt-auto small">
                        {{ setting('landing_service_2_btn_text', 'Learn More') }} <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="card h-100 p-4 border rounded-4 shadow-sm hover-shadow transition">
                    <div class="rounded-3 bg-warning text-dark d-flex align-items-center justify-content-center mb-3" style="width: 48px; height: 48px; font-size: 1.4rem;">
                        <i class="bi {{ setting('landing_service_3_icon', 'bi-building-gear') }}"></i>
                    </div>
                    <h5 class="brand-font mb-2">{!! setting('landing_service_3_title', 'Property Management') !!}</h5>
                    <p class="text-muted small mb-3">{{ setting('landing_service_3_desc', 'Automated tenant billing, lease agreements, digital rent collection, and facility maintenance coordination.') }}</p>
                    <a href="{{ setting('landing_service_3_btn_url', route('public.services.detail', 'property-management')) }}" class="fw-bold text-warning text-decoration-none mt-auto small">
                        {{ setting('landing_service_3_btn_text', 'Learn More') }} <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
@endif

<!-- 10. WHY CHOOSE REMS (TRUST & VALUE) -->
@if(setting('landing_trust_enabled', '1') === '1')
<section class="py-5 bg-dark text-white">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-5">
                <span class="badge bg-primary px-3 py-2 mb-3">{{ setting('landing_trust_badge', 'Trust & Verification Protocol') }}</span>
                <h2 class="brand-font text-white display-6 fw-bold mb-3">{{ setting('landing_trust_title', 'Why Real Estate Clients Choose REMS') }}</h2>
                <p class="text-white-50 mb-4" style="line-height: 1.7;">
                    {{ setting('landing_trust_desc', 'We eliminate land disputes and fraudulent transactions by pairing modern digital marketplace convenience with ground-level cadastral verification.') }}
                </p>
                <div class="d-flex flex-column gap-3">
                    <div class="d-flex align-items-start gap-3">
                        <div class="rounded-circle bg-primary bg-opacity-20 text-primary p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="bi {{ setting('landing_trust_1_icon', 'bi-patch-check-fill') }} fs-5 text-warning"></i>
                        </div>
                        <div>
                            <h6 class="text-white brand-font mb-1">{{ setting('landing_trust_1_title', 'Cadastral & Title Verification') }}</h6>
                            <p class="text-white-50 small mb-0">{{ setting('landing_trust_1_desc', 'Every listing undergoes rigorous registry and beacon verification before receiving the Verified badge.') }}</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-start gap-3">
                        <div class="rounded-circle bg-primary bg-opacity-20 text-primary p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="bi {{ setting('landing_trust_2_icon', 'bi-whatsapp') }} fs-5 text-success"></i>
                        </div>
                        <div>
                            <h6 class="text-white brand-font mb-1">{{ setting('landing_trust_2_title', 'Real-Time WhatsApp & Direct Support') }}</h6>
                            <p class="text-white-50 small mb-0">{{ setting('landing_trust_2_desc', 'Directly connect with certified local agents and schedule in-person site viewings instantly.') }}</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-start gap-3">
                        <div class="rounded-circle bg-primary bg-opacity-20 text-primary p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="bi {{ setting('landing_trust_3_icon', 'bi-shield-lock-fill') }} fs-5 text-info"></i>
                        </div>
                        <div>
                            <h6 class="text-white brand-font mb-1">{{ setting('landing_trust_3_title', 'Privacy & Secure Transactions') }}</h6>
                            <p class="text-white-50 small mb-0">{{ setting('landing_trust_3_desc', 'Owner contact privacy protection and structured digital sales contracts for maximum buyer security.') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <!-- 11. DYNAMIC PLATFORM STATISTICS -->
                <div class="row g-3">
                    <div class="col-6">
                        <div class="card p-4 rounded-4 bg-secondary bg-opacity-25 border-0 text-center text-white h-100">
                            <h2 class="brand-font text-warning display-5 fw-bold mb-1">{{ number_format($stats['total_properties']) }}+</h2>
                            <span class="text-white-50 small fw-semibold">{{ setting('landing_stat_1_label', 'Verified Properties') }}</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card p-4 rounded-4 bg-secondary bg-opacity-25 border-0 text-center text-white h-100">
                            <h2 class="brand-font text-success display-5 fw-bold mb-1">{{ number_format($stats['survey_projects']) }}+</h2>
                            <span class="text-white-50 small fw-semibold">{{ setting('landing_stat_2_label', 'Cadastral Land Surveys') }}</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card p-4 rounded-4 bg-secondary bg-opacity-25 border-0 text-center text-white h-100">
                            <h2 class="brand-font text-info display-5 fw-bold mb-1">{{ number_format($stats['total_locations']) }}</h2>
                            <span class="text-white-50 small fw-semibold">{{ setting('landing_stat_3_label', 'Regions Across Tanzania') }}</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card p-4 rounded-4 bg-secondary bg-opacity-25 border-0 text-center text-white h-100">
                            <h2 class="brand-font text-primary display-5 fw-bold mb-1">{{ number_format($stats['satisfied_clients']) }}+</h2>
                            <span class="text-white-50 small fw-semibold">{{ setting('landing_stat_4_label', 'Satisfied Clients') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endif

<!-- 12. TESTIMONIALS / SOCIAL PROOF -->
@if(setting('landing_testimonials_enabled', '1') === '1')
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center max-w-700 mx-auto mb-5">
            <span class="section-tag">{{ setting('landing_testimonials_tag', 'Client Experiences') }}</span>
            <h2 class="brand-font mb-2">{{ setting('landing_testimonials_title', 'What Our Customers Say') }}</h2>
            <p class="text-muted">{{ setting('landing_testimonials_subtitle', 'Trusted by property buyers, land investors, tenants, and commercial developers.') }}</p>
        </div>

        <div class="row g-4">
            @foreach($testimonials as $t)
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100 p-4 border rounded-4 shadow-sm bg-white d-flex flex-column">
                        <div class="text-warning mb-3">
                            @for($i = 0; $i < $t->rating; $i++)
                                <i class="bi bi-star-fill"></i>
                            @endfor
                        </div>
                        <p class="text-muted small mb-4 flex-grow-1" style="line-height: 1.6;">
                            "{{ $t->feedback }}"
                        </p>
                        <div class="d-flex align-items-center gap-2 border-top pt-3">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 38px; height: 38px; font-size: 0.9rem;">
                                {{ substr($t->customer_name, 0, 1) }}
                            </div>
                            <div>
                                <h6 class="brand-font text-dark mb-0" style="font-size: 0.9rem;">{{ $t->customer_name }}</h6>
                                <small class="text-muted" style="font-size: 0.75rem;">{{ $t->customer_role }} • {{ $t->location }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- 13. REAL ESTATE INSIGHTS & BLOG -->
@if(setting('landing_blog_enabled', '1') === '1')
<section class="py-5 bg-white border-top border-bottom">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-end mb-4">
            <div>
                <span class="section-tag">{{ setting('landing_blog_tag', 'Knowledge Hub') }}</span>
                <h3 class="brand-font mb-0">{!! setting('landing_blog_title', 'Real Estate Insights & Guides') !!}</h3>
            </div>
            <a href="{{ setting('landing_blog_cta_url', route('public.blog')) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-bold mt-2 mt-md-0">
                {{ setting('landing_blog_cta_text', 'View All Articles') }} <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="row g-4">
            @foreach($articles as $art)
                <div class="col-md-4">
                    <div class="card h-100 border rounded-4 overflow-hidden shadow-sm hover-shadow transition">
                        <div style="height: 190px; background: #e2e8f0;" class="position-relative">
                            <img src="{{ $art->featured_image_url }}" alt="{{ $art->title }}" class="w-100 h-100 object-fit-cover" loading="lazy" onerror="this.src='https://images.unsplash.com/photo-1560518883-ce09059eeffa?w=600&auto=format&fit=crop&q=80'">
                            <span class="position-absolute top-0 start-0 m-3 badge bg-primary">{{ $art->category }}</span>
                        </div>
                        <div class="card-body p-4 d-flex flex-column">
                            <small class="text-muted mb-2"><i class="bi bi-clock me-1"></i> {{ $art->reading_time_minutes }} min read • {{ $art->published_at ? $art->published_at->format('M d, Y') : '' }}</small>
                            <h5 class="brand-font mb-2">
                                <a href="{{ route('public.blog.show', $art->slug) }}" class="text-dark text-decoration-none hover-primary">
                                    {{ $art->title }}
                                </a>
                            </h5>
                            <p class="text-muted small mb-3 flex-grow-1">
                                {{ Str::limit($art->excerpt, 110) }}
                            </p>
                            <a href="{{ route('public.blog.show', $art->slug) }}" class="fw-bold text-primary text-decoration-none small mt-auto">
                                Read Full Guide <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- 14. PROPERTY OWNER CTA -->
@if(setting('landing_owner_cta_enabled', '1') === '1')
<section class="py-5" style="background: linear-gradient(135deg, #0f52ba 0%, #092c68 100%); color: #ffffff;">
    <div class="container text-center py-4">
        <h2 class="brand-font text-white display-6 fw-bold mb-3">{{ setting('landing_owner_cta_title', 'Have a Property to Sell or Rent?') }}</h2>
        <p class="lead text-white-50 mx-auto mb-4" style="max-width: 650px;">
            {{ setting('landing_owner_cta_desc', 'Reach thousands of active property buyers and prospective tenants. List your property on the REMS marketplace with full verification support.') }}
        </p>
        <div class="d-flex flex-wrap justify-content-center gap-3">
            <a href="{{ setting('landing_owner_cta_1_url', route('login')) }}" class="btn btn-warning btn-lg rounded-pill px-4 fw-bold shadow">
                <i class="bi {{ setting('landing_owner_cta_1_icon', 'bi-plus-circle') }} me-1"></i> {{ setting('landing_owner_cta_1_text', 'List Your Property') }}
            </a>
            <a href="{{ setting('landing_owner_cta_2_url', 'https://wa.me/' . setting('contact_whatsapp', '255784100200') . '?text=' . urlencode('Hello, I am a property owner and would like to list my property with REMS.')) }}" target="_blank" class="btn btn-outline-light btn-lg rounded-pill px-4 fw-semibold">
                <i class="bi {{ setting('landing_owner_cta_2_icon', 'bi-whatsapp') }} me-1"></i> {{ setting('landing_owner_cta_2_text', 'Speak with Listing Agent') }}
            </a>
        </div>
    </div>
</section>
@endif

@endsection
