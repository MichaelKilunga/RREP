<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- SEO Metadata -->
    @php
        $org = current_organization();
        $branding = $org?->branding ?: \App\Models\BrandingConfig::first();
        $faviconUrl = $branding?->favicon ?: setting('site_favicon');
        $primaryColor = $branding?->primary_color ?? '#0f52ba';
        $secondaryColor = $branding?->secondary_color ?? '#1e293b';
        $accentColor = $branding?->accent_color ?? '#10b981';
        $companyName = setting('company_name', $org?->name ?? 'RehoSpace');
        $tagline = $branding?->company_tagline ?? setting('company_tagline', 'Verified Real Estate & Land Survey Marketplace');
        $phone = setting('contact_phone', '+255 784 100 200');
        $whatsappNumber = setting('contact_whatsapp', '255784100200');
        $email = setting('contact_email', 'info@rehospace.co.tz');
        $address = setting('contact_address', 'Plot 42, Victoria Business Tower, New Bagamoyo Road, Dar es Salaam');

        // Resolve Open Graph Image with priority fallback chain:
        // 1. @yield('og_image')
        // 2. setting('og_default_image')
        // 3. $branding?->header_logo (if raster: png, jpg, jpeg, webp)
        // 4. asset('images/og-default.jpg')
        $customOgImage = View::hasSection('og_image') ? trim((string) View::getSection('og_image')) : null;
        $settingOgImage = setting('og_default_image');
        $brandLogo = $branding?->header_logo;
        $isBrandRaster = $brandLogo && !str_ends_with(strtolower(parse_url($brandLogo, PHP_URL_PATH) ?? ''), '.svg');

        $resolvedOgImage = $customOgImage ?: ($settingOgImage ?: ($isBrandRaster ? $brandLogo : asset('images/og-default.jpg')));

        // Ensure absolute URL with appropriate scheme
        if ($resolvedOgImage && !str_starts_with($resolvedOgImage, 'http://') && !str_starts_with($resolvedOgImage, 'https://')) {
            $resolvedOgImage = asset(ltrim($resolvedOgImage, '/'));
        }

        // Ensure HTTPS protocol if request is secure or app configured for HTTPS
        if ($resolvedOgImage && (request()->isSecure() || str_starts_with(config('app.url'), 'https://'))) {
            $resolvedOgImage = preg_replace('/^http:/i', 'https:', $resolvedOgImage);
        }

        // Determine MIME type
        $ogPath = parse_url($resolvedOgImage, PHP_URL_PATH) ?? '';
        $ogExt = strtolower(pathinfo($ogPath, PATHINFO_EXTENSION));
        $ogMimeType = match ($ogExt) {
            'png' => 'image/png',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
            default => 'image/jpeg',
        };
    @endphp

    <title>@yield('title', 'REMS Real Estate Marketplace & Land Survey Platform') - {{ $companyName }}</title>
    @if($faviconUrl)
        <link rel="icon" href="{{ $faviconUrl }}">
        <link rel="shortcut icon" href="{{ $faviconUrl }}">
        <link rel="apple-touch-icon" href="{{ $faviconUrl }}">
    @endif
    <meta name="description" content="@yield('meta_description', 'Discover verified houses, apartments, cadastral surveyed land plots, and commercial developments across Tanzania on the REMS digital real estate marketplace.')">
    <link rel="canonical" href="{{ url()->current() }}">

    <!-- Progressive Web App -->
    <link rel="manifest" href="{{ route('pwa.manifest') }}">
    <meta name="theme-color" content="{{ $primaryColor }}">
    <link rel="apple-touch-icon" sizes="192x192" href="{{ route('pwa.icon', 192) }}">

    @php
        $pinterestVerify = setting('pinterest_domain_verify');
        if ($pinterestVerify) {
            if (preg_match('/content=["\']([^"\']+)["\']/i', $pinterestVerify, $matches)) {
                $pinterestVerify = trim($matches[1]);
            } else {
                $pinterestVerify = trim(strip_tags($pinterestVerify));
            }
        }
    @endphp
    @if($pinterestVerify)
        <meta name="p:domain_verify" content="{{ $pinterestVerify }}">
    @endif

    <!-- Open Graph / WhatsApp / Facebook / LinkedIn -->
    <meta property="og:site_name" content="{{ $companyName }}">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="@yield('title', 'REMS Real Estate Marketplace')">
    <meta property="og:description" content="@yield('meta_description', 'Discover verified houses, apartments, cadastral surveyed land plots, and commercial developments across Tanzania.')">
    <meta property="og:image" content="{{ $resolvedOgImage }}">
    <meta property="og:image:secure_url" content="{{ $resolvedOgImage }}">
    <meta property="og:image:type" content="{{ $ogMimeType }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="{{ $companyName }} - Verified Real Estate & Land Surveys">
    <meta property="og:locale" content="{{ str_replace('-', '_', app()->getLocale()) }}_{{ strtoupper(app()->getLocale() == 'sw' ? 'TZ' : 'US') }}">

    <!-- Microdata Image Fallback for WhatsApp & Search Engines -->
    <link rel="image_src" href="{{ $resolvedOgImage }}">
    <meta itemprop="name" content="@yield('title', 'REMS Real Estate Marketplace') - {{ $companyName }}">
    <meta itemprop="description" content="@yield('meta_description', 'Discover verified houses, apartments, cadastral surveyed land plots, and commercial developments across Tanzania.')">
    <meta itemprop="image" content="{{ $resolvedOgImage }}">

    <!-- Twitter / X -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', 'REMS Real Estate Marketplace')">
    <meta name="twitter:description" content="@yield('meta_description', 'Discover verified properties and land survey opportunities.')">
    <meta name="twitter:image" content="{{ $resolvedOgImage }}">
    <meta name="twitter:image:alt" content="{{ $companyName }}">

    <!-- Structured Data / JSON-LD for Google Rich Results -->
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "RealEstateAgent",
        "name": "{{ $companyName }}",
        "image": "{{ $resolvedOgImage }}",
        "url": "{{ url('/') }}",
        "description": "Verified Real Estate and Cadastral Land Survey Marketplace in Tanzania",
        "telephone": "{{ $phone }}",
        "email": "{{ $email }}",
        "address": {
            "@@type": "PostalAddress",
            "streetAddress": "{{ $address }}",
            "addressLocality": "Dar es Salaam",
            "addressCountry": "TZ"
        }
    }
    </script>

    <!-- Stylesheets (vendor via CDN, app design system via Vite) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.7/dist/sweetalert2.min.css" rel="stylesheet">
    {{ Vite::fonts() }}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Tenant brand tokens (configured under Settings > Branding) -->
    <style>
        :root {
            --rrep-primary: {{ $primaryColor }};
            --rrep-primary-rgb: {{ brand_rgb($primaryColor) }};
            --rrep-primary-hover: {{ brand_shade($primaryColor, -18) }};
            --rrep-secondary: {{ $secondaryColor }};
            --rrep-accent: {{ $accentColor }};
            --rrep-accent-hover: {{ brand_shade($accentColor, -15) }};
        }
        .hero-section::before {
            background-image: url("{{ setting('landing_hero_image') ?: asset('images/hero-dodoma-plot.jpg') }}");
        }
    </style>
    @if(!empty($branding?->custom_css))
        <style>
            {!! $branding->custom_css !!}
        </style>
    @endif
    @yield('styles')
</head>
<body class="public-shell @yield('body_class')">

    <!-- 1. Sleek Single-Line Top Bar (Non-wrapping & Crisp) -->
    @if(setting('landing_topbar_enabled', '1') === '1')
    <div class="header-topbar py-1 d-none d-lg-block" id="headerTopbar">
        <div class="container-fluid px-3 px-lg-4 px-xl-5 d-flex align-items-center justify-content-between text-nowrap">
            <!-- Left: Regional Hubs Ticker -->
            <div class="d-flex align-items-center gap-3 overflow-hidden">
                <span class="d-flex align-items-center gap-1 text-white">
                    <i class="bi bi-geo-alt-fill text-danger"></i>
                    <strong class="text-white-50">{{ setting_trans('landing_topbar_ticker_label', 'Tanzania:', 'Tanzania:') }}</strong> {{ setting_trans('landing_topbar_ticker_text', 'Dar es Salaam • Dodoma') }}
                </span>
                <span class="text-white-50">•</span>
                <a href="tel:{{ $phone }}" class="d-flex align-items-center gap-1">
                    <i class="bi bi-telephone-fill text-success"></i> {{ $phone }}
                </a>
            </div>

            <!-- Right: Survey Fast Portal & Staff Entry -->
            <div class="d-flex align-items-center gap-2">
                <a href="{{ setting('landing_topbar_survey_link', route('public.services.land_survey')) }}" class="topbar-badge text-warning text-decoration-none">
                    <i class="bi {{ setting('landing_topbar_survey_icon', 'bi-compass') }} me-1"></i> {{ setting_trans('landing_topbar_survey_text', 'Upimaji Ardhi', 'Survey and Mapping') }}
                </a>
                {{-- <span class="text-white-50">|</span>
                <a href="{{ route('login') }}" class="text-white-50 hover-white">
                    <i class="bi {{ setting('landing_topbar_staff_icon', 'bi-person-lock') }} me-1"></i> {{ setting_trans('landing_topbar_staff_text', 'Mfumo wa Wafanyakazi', 'Staff Portal') }}
                </a> --}}
            </div>
        </div>
    </div>
    @endif

    <!-- 2. Smart Sticky Navbar with Dropdowns & Prominent Actions -->
    <nav class="navbar navbar-expand-xl public-navbar sticky-top py-2" id="mainNavbar">
        <div class="container-fluid px-3 px-lg-4 px-xxl-5">
            <!-- Brand Logo -->
            <a class="navbar-brand d-flex align-items-center gap-2 py-0 me-2 me-xl-3 flex-shrink-0" href="{{ route('public.home') }}">
                @if(!empty($branding?->header_logo))
                    <img src="{{ $branding->header_logo }}" alt="{{ $companyName }}" class="img-fluid rounded-2" style="max-height: 40px; max-width: 140px; object-fit: contain;">
                @else
                    <div class="rounded-3 bg-primary text-white d-flex align-items-center justify-content-center shadow-sm flex-shrink-0" style="width: 36px; height: 36px; font-weight: 800; font-size: 1.15rem;">
                        {{ setting('brand_monogram', 'R') }}
                    </div>
                @endif
                <div>
                    <div class="brand-font fw-bold brand-name">
                        {{ $companyName }}
                    </div>
                    <small class="text-muted text-uppercase d-block brand-sub">{{ setting('company_subtitle', 'Real Estate & Land') }}</small>
                </div>
            </a>

            <!-- Mobile Quick Actions & Hamburger (<1200px) -->
            <div class="d-flex align-items-center gap-2 d-xl-none ms-auto me-2">
                <button class="navbar-toggler border-0 p-1" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileOffcanvas">
                    <i class="bi bi-list fs-2 text-dark"></i>
                </button>
            </div>

            <!-- Desktop Smart Nav Items (>=1200px) -->
            <div class="collapse navbar-collapse" id="desktopNav">
                <ul class="navbar-nav me-auto mb-2 mb-xl-0 align-items-center gap-2">
                    <!-- 1. Home -->
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('public.home') ? 'active' : '' }}" href="{{ route('public.home') }}">
                            {{ setting_trans('landing_nav_home_label', 'Nyumbani', 'Home') }}
                        </a>
                    </li>

                    <!-- 2. About Us -->
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('public.about') ? 'active' : '' }}" href="{{ route('public.about') }}">
                            {{ setting_trans('landing_nav_about_label', 'Kuhusu Sisi', 'About Us') }}
                        </a>
                    </li>

                    <!-- 3. Services -->
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('public.services*') ? 'active' : '' }}" href="{{ route('public.services') }}">
                            {{ setting_trans('landing_nav_services_label', 'Huduma Zetu', 'Services') }}
                        </a>
                    </li>

                    <!-- 4. Projects -->
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('public.projects*') ? 'active' : '' }}" href="{{ route('public.projects') }}">
                            {{ setting_trans('landing_nav_projects_label', 'Miradi', 'Projects') }}
                        </a>
                    </li>

                    <!-- 5. Contact Us -->
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('public.contact') ? 'active' : '' }}" href="{{ route('public.contact') }}">
                            {{ setting_trans('landing_nav_contact_label', 'Wasiliana Nasi', 'Contact Us') }}
                        </a>
                    </li>
                </ul>

                <!-- Right Action Cluster -->
                <div class="d-flex align-items-center gap-2 text-nowrap flex-shrink-0 ms-auto">
                    <!-- Language Switcher Pill -->
                    <div class="dropdown">
                        <button class="btn btn-sm btn-light border rounded-pill px-3 py-1 text-nowrap fw-semibold small d-flex align-items-center gap-1" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-globe text-primary"></i> {{ __('app.current_language') }}
                            <i class="bi bi-chevron-down ms-1" style="font-size: 0.65rem;"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 small">
                            <li>
                                <a class="dropdown-item d-flex align-items-center justify-content-between {{ app()->getLocale() === 'sw' ? 'fw-bold text-primary bg-light' : '' }}" href="{{ route('lang.switch', 'sw') }}">
                                    <span>🇹🇿 Kiswahili</span>
                                    @if(app()->getLocale() === 'sw') <i class="bi bi-check-lg text-primary ms-2"></i> @endif
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center justify-content-between {{ app()->getLocale() === 'en' ? 'fw-bold text-primary bg-light' : '' }}" href="{{ route('lang.switch', 'en') }}">
                                    <span>🇬🇧 English</span>
                                    @if(app()->getLocale() === 'en') <i class="bi bi-check-lg text-primary ms-2"></i> @endif
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Staff Portal Login -->
                    <a href="{{ route('login') }}" class="btn btn-outline-dark btn-sm rounded-pill px-3 fw-semibold flex-shrink-0">
                        <i class="bi bi-person me-1"></i> {{ setting_trans('landing_nav_login_btn_text', 'Ingia', 'Login') }}
                    </a>

                    <!-- Highlighted List Property Button -->
                    @if(setting('landing_nav_list_btn_enabled', '1') === '1' && is_module_enabled('property_owner_submissions', true))
                    <a href="{{ setting('landing_nav_list_btn_url', route('login')) }}" class="btn btn-list-property btn-sm px-3 fw-bold d-flex align-items-center gap-1 flex-shrink-0 text-nowrap">
                        <i class="bi {{ setting('landing_nav_list_btn_icon', 'bi-plus-circle-fill') }}"></i> {{ setting_trans('landing_nav_list_btn_text', 'Weka Tangazo', 'List Property') }}
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </nav>

    <!-- Mobile Offcanvas Drawer Menu -->
    <div class="offcanvas offcanvas-start shadow-lg" tabindex="-1" id="mobileOffcanvas" style="width: min(320px, 86vw);">
        <div class="offcanvas-header border-bottom bg-dark text-white">
            <div class="d-flex align-items-center gap-2">
                @if(!empty($branding?->header_logo))
                    <img src="{{ $branding->header_logo }}" alt="{{ $companyName }}" class="img-fluid rounded-2" style="max-height: 36px; max-width: 120px; object-fit: contain;">
                @else
                    <div class="rounded-3 bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 36px; height: 36px;">{{ setting('brand_monogram', 'R') }}</div>
                @endif
                <div>
                    <h6 class="brand-font mb-0 text-white">{{ $companyName }}</h6>
                    <small class="text-white-50" style="font-size: 0.7rem;">{{ setting('company_subtitle', 'Real Estate & Land Survey') }}</small>
                </div>
            </div>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body d-flex flex-column p-3">
            @php
                $drawerLinks = [
                    ['route' => 'public.home', 'match' => 'public.home', 'icon' => 'bi-house-door', 'label' => setting_trans('landing_nav_home_label', 'Nyumbani', 'Home')],
                    ['route' => 'public.about', 'match' => 'public.about', 'icon' => 'bi-info-circle', 'label' => setting_trans('landing_nav_about_label', 'Kuhusu Sisi', 'About Us')],
                    ['route' => 'public.services', 'match' => 'public.services*', 'icon' => 'bi-briefcase', 'label' => setting_trans('landing_nav_services_label', 'Huduma Zetu', 'Services')],
                    ['route' => 'public.projects', 'match' => 'public.projects*', 'icon' => 'bi-diagram-3', 'label' => setting_trans('landing_nav_projects_label', 'Miradi', 'Projects')],
                    ['route' => 'public.contact', 'match' => 'public.contact', 'icon' => 'bi-headset', 'label' => setting_trans('landing_nav_contact_label', 'Wasiliana Nasi', 'Contact Us')],
                ];
            @endphp

            <!-- Primary Navigation (mirrors the desktop navbar) -->
            <nav aria-label="{{ __('app.nav_menu') }}">
                <ul class="nav flex-column gap-1 mb-3">
                    @foreach($drawerLinks as $link)
                        <li class="nav-item">
                            <a class="drawer-link {{ request()->routeIs($link['match']) ? 'active' : '' }}"
                               href="{{ route($link['route']) }}" @if(request()->routeIs($link['match'])) aria-current="page" @endif>
                                <i class="bi {{ $link['icon'] }}"></i> {{ $link['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </nav>

            <!-- Survey Fast Link (mirrors the desktop top utility bar) -->
            <a href="{{ setting('landing_topbar_survey_link', route('public.services.land_survey')) }}" class="btn btn-success-subtle text-success border border-success-subtle w-100 py-2 fw-bold text-start d-flex align-items-center gap-2 mb-3">
                <i class="bi {{ setting('landing_topbar_survey_icon', 'bi-compass') }}"></i> {{ setting_trans('landing_topbar_survey_text', 'Upimaji Ardhi', 'Survey and Mapping') }}
            </a>

            <!-- Language Switcher -->
            <div class="border-top pt-3 mb-3">
                <span class="text-muted text-uppercase fw-bold d-block mb-2" style="font-size: 0.68rem; letter-spacing: 0.05em;">{{ __('app.language') }}</span>
                <div class="btn-group w-100" role="group" aria-label="{{ __('app.language') }}">
                    <a href="{{ route('lang.switch', 'sw') }}" class="btn btn-sm {{ app()->getLocale() === 'sw' ? 'btn-primary' : 'btn-outline-secondary' }}">Kiswahili</a>
                    <a href="{{ route('lang.switch', 'en') }}" class="btn btn-sm {{ app()->getLocale() === 'en' ? 'btn-primary' : 'btn-outline-secondary' }}">English</a>
                </div>
            </div>

            <!-- Drawer Bottom CTA -->
            <div class="mt-auto border-top pt-3">
                @if(setting('landing_nav_list_btn_enabled', '1') === '1' && is_module_enabled('property_owner_submissions', true))
                <a href="{{ setting('landing_nav_list_btn_url', route('login')) }}" class="btn btn-list-property w-100 mb-2 py-2 fw-bold d-flex align-items-center justify-content-center gap-2">
                    <i class="bi {{ setting('landing_nav_list_btn_icon', 'bi-plus-circle-fill') }}"></i> {{ setting_trans('landing_nav_list_btn_text', 'Weka Tangazo', 'List Property') }}
                </a>
                @endif
                <a href="{{ route('login') }}" class="btn btn-outline-dark w-100 py-2 fw-semibold small">
                    <i class="bi bi-person-lock me-1"></i> {{ setting_trans('landing_nav_login_btn_text', 'Ingia', 'Login') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content Injection -->
    <main>
        @if(session('success'))
            <div class="container mt-4">
                <div class="alert alert-success alert-dismissible fade show shadow-sm d-flex align-items-center gap-2" role="alert">
                    <i class="bi bi-check-circle-fill fs-4 text-success"></i>
                    <div>{{ session('success') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="container mt-4">
                <div class="alert alert-danger alert-dismissible fade show shadow-sm d-flex align-items-center gap-2" role="alert">
                    <i class="bi bi-exclamation-triangle-fill fs-4 text-danger"></i>
                    <div>{{ session('error') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Mobile Bottom Navigation (phones only) -->
    <nav class="public-bottom-nav" aria-label="{{ __('app.nav_menu') }}">
        <a href="{{ route('public.home') }}" class="{{ request()->routeIs('public.home') ? 'active' : '' }}">
            <i class="bi bi-house-door"></i><span>{{ __('app.nav_home') }}</span>
        </a>
        <a href="{{ route('public.services.land_survey') }}" class="{{ request()->routeIs('public.services.land_survey') || request()->routeIs('public.survey*') ? 'active' : '' }}">
            <i class="bi bi-compass"></i><span>{{ __('app.nav_survey') }}</span>
        </a>
        <a href="{{ route('public.projects') }}" class="{{ request()->routeIs('public.projects*') || request()->routeIs('public.land') ? 'active' : '' }}">
            <i class="bi bi-diagram-3"></i><span>{{ __('app.nav_projects') }}</span>
        </a>
        <button type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileOffcanvas" aria-label="{{ __('app.nav_menu') }}">
            <i class="bi bi-list"></i><span>{{ __('app.nav_menu') }}</span>
        </button>
    </nav>

    <!-- Floating WhatsApp Widget -->
    @if(setting('landing_whatsapp_enabled', '1') === '1')
    <a href="https://wa.me/{{ $whatsappNumber }}?text={{ urlencode(setting('landing_whatsapp_message', 'Hello REMS Real Estate Platform, I am interested in exploring property listings and land opportunities.')) }}" target="_blank" class="whatsapp-float" title="Chat with Real Estate Advisor on WhatsApp">
        <i class="bi bi-whatsapp"></i>
    </a>
    @endif

    <!-- Comparison Quick Drawer -->
    <div class="compare-bar" id="compareDrawer">
        <div class="container d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <i class="bi bi-columns-gap fs-4 text-warning"></i>
                <div>
                    <strong id="compareCount">0</strong> Properties Selected for Comparison
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-sm btn-outline-light" id="clearCompareBtn">Clear</button>
                <a href="{{ route('public.compare') }}" class="btn btn-sm btn-accent fw-bold" id="compareNowBtn">
                    Compare Now <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Global Share Modal -->
    <div class="modal fade" id="shareModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title brand-font"><i class="bi bi-share-fill text-primary me-2"></i> Share Property</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <p class="text-muted small mb-3">Share this verified real estate listing with your network</p>
                    <div class="d-flex justify-content-center gap-3 mb-4">
                        <a href="#" id="shareWhatsApp" target="_blank" class="btn btn-success rounded-circle p-3" style="width: 50px; height: 50px;"><i class="bi bi-whatsapp fs-5"></i></a>
                        <a href="#" id="shareFacebook" target="_blank" class="btn btn-primary rounded-circle p-3" style="width: 50px; height: 50px;"><i class="bi bi-facebook fs-5"></i></a>
                        <a href="#" id="shareTwitter" target="_blank" class="btn btn-dark rounded-circle p-3" style="width: 50px; height: 50px;"><i class="bi bi-twitter-x fs-5"></i></a>
                        <a href="#" id="shareEmail" class="btn btn-secondary rounded-circle p-3" style="width: 50px; height: 50px;"><i class="bi bi-envelope fs-5"></i></a>
                    </div>
                    <div class="input-group">
                        <input type="text" id="shareUrlInput" class="form-control" readonly>
                        <button class="btn btn-outline-primary fw-bold" id="copyShareUrlBtn">
                            <i class="bi bi-clipboard me-1"></i> Copy Link
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Professional Clean Footer -->
    <footer class="public-footer pt-5 pb-4 mt-5">
        <div class="container">
            <div class="row g-4 pb-4 border-bottom border-secondary">
                <!-- Col 1: Brand & Contact Info -->
                <div class="col-lg-4 col-md-6">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        @if(!empty($branding?->header_logo))
                            <img src="{{ $branding->header_logo }}" alt="{{ $companyName }}" class="img-fluid rounded-2" style="max-height: 38px; max-width: 140px; object-fit: contain;">
                        @else
                            <div class="rounded-3 bg-primary text-white d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; font-weight: 800; font-size: 1.1rem;">{{ setting('brand_monogram', 'R') }}</div>
                        @endif
                        <h5 class="brand-font text-white mb-0">{{ $companyName }}</h5>
                    </div>
                    <p class="text-white-50 small mb-3">
                        {{ setting_trans('footer_bio', $tagline, $tagline) }}
                    </p>
                </div>

                <!-- Col 2: Dynamic Navigation Links -->
                <div class="col-lg-3 col-md-6">
                    <h6 class="text-white brand-font mb-3">{{ setting_trans('footer_nav_title', 'Kurasa Kuu', 'Quick Navigation') }}</h6>
                    <ul class="list-unstyled text-white-50 small d-flex flex-column gap-2 mb-0">
                        <li><a href="{{ route('public.home') }}" class="text-white-50 text-decoration-none hover-white"><i class="bi bi-chevron-right me-1 small"></i> {{ setting_trans('landing_nav_home_label', 'Nyumbani', 'Home') }}</a></li>
                        <li><a href="{{ route('public.about') }}" class="text-white-50 text-decoration-none hover-white"><i class="bi bi-chevron-right me-1 small"></i> {{ setting_trans('landing_nav_about_label', 'Kuhusu Sisi', 'About Us') }}</a></li>
                        <li><a href="{{ route('public.services') }}" class="text-white-50 text-decoration-none hover-white"><i class="bi bi-chevron-right me-1 small"></i> {{ setting_trans('landing_nav_services_label', 'Huduma Zetu', 'Services') }}</a></li>
                        <li><a href="{{ route('public.projects') }}" class="text-white-50 text-decoration-none hover-white"><i class="bi bi-chevron-right me-1 small"></i> {{ setting_trans('landing_nav_projects_label', 'Miradi ya Viwanja', 'Projects') }}</a></li>
                        <li><a href="{{ route('public.contact') }}" class="text-white-50 text-decoration-none hover-white"><i class="bi bi-chevron-right me-1 small"></i> {{ setting_trans('landing_nav_contact_label', 'Wasiliana Nasi', 'Contact Us') }}</a></li>
                    </ul>
                </div>

                <!-- Col 3: Newsletter Subscribe & Dynamic Socials -->
                <div class="col-lg-5 col-md-12">
                    <h6 class="text-white brand-font mb-2">{{ setting_trans('footer_newsletter_title', 'Pata Taarifa za Viwanja', 'Stay Updated') }}</h6>
                    <p class="text-white-50 small mb-3">{{ setting_trans('footer_newsletter_subtitle', 'Jiunge kupata taarifa za miradi mipya ya viwanja vilivyopimwa.', 'Subscribe for newly verified land projects and survey updates.') }}</p>
                    <form action="{{ route('public.newsletter.subscribe') }}" method="POST" class="mb-3">
                        @csrf
                        <div class="input-group mb-1">
                            <input type="text" name="contact" class="form-control bg-dark text-white border-secondary" placeholder="{{ __('app.enter_phone_number_or_email') }}" required>
                            <button type="submit" class="btn btn-primary fw-bold px-4">
                                <i class="bi bi-send-fill me-1"></i> {{ setting_trans('footer_newsletter_btn_text', 'Jiunge', 'Subscribe') }}
                            </button>
                        </div>
                        <small class="text-white-50 d-block" style="font-size: 0.75rem;">
                            <i class="bi bi-phone text-success me-1"></i> {{ __('app.e_g_0784100200_or_email') }}
                        </small>
                    </form>
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 pt-2">
                        <div class="d-flex flex-wrap gap-2">
                            @if(setting('social_facebook'))<a href="{{ setting('social_facebook') }}" target="_blank" class="btn btn-outline-light btn-icon" title="Facebook"><i class="bi bi-facebook"></i></a>@endif
                            @if(setting('social_instagram'))<a href="{{ setting('social_instagram') }}" target="_blank" class="btn btn-outline-light btn-icon" title="Instagram"><i class="bi bi-instagram"></i></a>@endif
                            @if(setting('social_linkedin'))<a href="{{ setting('social_linkedin') }}" target="_blank" class="btn btn-outline-light btn-icon" title="LinkedIn"><i class="bi bi-linkedin"></i></a>@endif
                            @if(setting('social_youtube'))<a href="{{ setting('social_youtube') }}" target="_blank" class="btn btn-outline-light btn-icon" title="YouTube"><i class="bi bi-youtube"></i></a>@endif
                            @if(setting('social_twitter'))<a href="{{ setting('social_twitter') }}" target="_blank" class="btn btn-outline-light btn-icon" title="Twitter / X"><i class="bi bi-twitter-x"></i></a>@endif
                            @if(setting('social_tiktok'))<a href="{{ setting('social_tiktok') }}" target="_blank" class="btn btn-outline-light btn-icon" title="TikTok"><i class="bi bi-tiktok"></i></a>@endif
                        </div>
                        {{-- <a href="{{ route('login') }}" class="btn btn-outline-light btn-sm rounded-pill px-3 fw-semibold small">
                            <i class="bi bi-lock-fill me-1"></i> {{ setting_trans('landing_topbar_staff_text', 'Mfumo wa Wafanyakazi', 'Staff Portal') }}
                        </a> --}}
                    </div>
                </div>
            </div>

            <!-- Contact Strip (single wrapping row) -->
            <div class="d-flex flex-wrap align-items-center justify-content-between column-gap-4 row-gap-2 py-3 border-bottom# border-secondary text-white-50 small">
                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}" class="text-white-50 text-decoration-none text-nowrap">
                    <i class="bi bi-telephone-fill text-success me-1"></i> {{ format_phone_tz($phone) }}
                </a>
                <a href="https://wa.me/{{ $whatsappNumber }}" target="_blank" rel="noopener" class="text-white-50 text-decoration-none text-nowrap">
                    <i class="bi bi-whatsapp text-success me-1"></i> {{ format_phone_tz($whatsappNumber) }}
                </a>
                <a href="mailto:{{ $email }}" class="text-white-50 text-decoration-none text-nowrap">
                    <i class="bi bi-envelope-fill text-info me-1"></i> {{ $email }}
                </a>
                <span class="d-inline-flex align-items-baseline wrap gap-1 min-w-0">
                    <i class="bi bi-geo-alt-fill text-danger"></i> {{ $address }}
                </span>
            </div>

            <!-- Bottom Copyright Line -->
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 pt-3 small text-white-50">
                <div>
                    &copy; {{ date('Y') }} <strong>{{ $companyName }}</strong>. {{ setting_trans('footer_copyright', 'Haki Zote Zimehifadhiwa.', 'All Rights Reserved.') }}
                </div>
                <div class="d-flex gap-3">
                    <a href="{{ route('public.privacy') }}" class="text-white-50 text-decoration-none">{{ __('app.privacy_policy') }}</a>
                    <a href="{{ route('public.terms') }}" class="text-white-50 text-decoration-none">{{ __('app.terms_and_conditions') }}</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Core JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.7/dist/sweetalert2.all.min.js"></script>

    <!-- Smart Header & Marketplace Interactivity Script -->
    <script>
        // Smart Sticky Header Transition on Scroll
        window.addEventListener('scroll', function() {
            const navbar = document.getElementById('mainNavbar');
            if (window.scrollY > 40) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Favorites & Comparison Storage Management
        const REMS = {
            getFavorites: function() {
                try {
                    return JSON.parse(localStorage.getItem('rems_favorites') || '[]');
                } catch(e) { return []; }
            },
            toggleFavorite: function(id) {
                let favs = this.getFavorites();
                const numId = parseInt(id);
                const idx = favs.indexOf(numId);
                if (idx === -1) {
                    favs.push(numId);
                    this.showToast('Property saved to Favorites!', 'success');
                } else {
                    favs.splice(idx, 1);
                    this.showToast('Property removed from Favorites', 'info');
                }
                localStorage.setItem('rems_favorites', JSON.stringify(favs));
                this.updateCounters();
                return idx === -1;
            },
            getCompare: function() {
                try {
                    return JSON.parse(localStorage.getItem('rems_compare') || '[]');
                } catch(e) { return []; }
            },
            toggleCompare: function(id) {
                let comp = this.getCompare();
                const numId = parseInt(id);
                const idx = comp.indexOf(numId);
                if (idx === -1) {
                    if (comp.length >= 4) {
                        this.showToast('You can compare up to 4 properties simultaneously.', 'warning');
                        return false;
                    }
                    comp.push(numId);
                    this.showToast('Property added to comparison!', 'success');
                } else {
                    comp.splice(idx, 1);
                    this.showToast('Property removed from comparison', 'info');
                }
                localStorage.setItem('rems_compare', JSON.stringify(comp));
                this.updateCounters();
                return idx === -1;
            },
            updateCounters: function() {
                const favs = this.getFavorites();
                const comp = this.getCompare();
                $('.favorites-count').text(favs.length);

                // Update active heart buttons
                $('.favorite-btn').each(function() {
                    const id = parseInt($(this).data('property-id'));
                    if (favs.includes(id)) {
                        $(this).addClass('active').find('i').removeClass('bi-heart').addClass('bi-heart-fill');
                    } else {
                        $(this).removeClass('active').find('i').removeClass('bi-heart-fill').addClass('bi-heart');
                    }
                });

                // Update comparison bar
                if (comp.length > 0) {
                    $('#compareCount').text(comp.length);
                    $('#compareNowBtn').attr('href', '{{ route("public.compare") }}?ids=' + comp.join(','));
                    $('#compareDrawer').fadeIn();
                } else {
                    $('#compareDrawer').fadeOut();
                }
            },
            showToast: function(title, icon = 'success') {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2500,
                    timerProgressBar: true,
                    icon: icon,
                    title: title
                });
            }
        };

        $(document).ready(function() {
            REMS.updateCounters();

            // Favorite button click
            $(document).on('click', '.favorite-btn', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const id = $(this).data('property-id');
                REMS.toggleFavorite(id);
            });

            // Compare toggle click
            $(document).on('click', '.compare-toggle-btn', function(e) {
                e.preventDefault();
                const id = $(this).data('property-id');
                REMS.toggleCompare(id);
            });

            // Clear comparison drawer
            $('#clearCompareBtn').on('click', function() {
                localStorage.removeItem('rems_compare');
                REMS.updateCounters();
            });

            // Share Modal Trigger
            $(document).on('click', '.share-property-btn', function(e) {
                e.preventDefault();
                const url = $(this).data('url') || window.location.href;
                const title = $(this).data('title') || document.title;

                $('#shareUrlInput').val(url);
                $('#shareWhatsApp').attr('href', 'https://wa.me/?text=' + encodeURIComponent(title + ' ' + url));
                $('#shareFacebook').attr('href', 'https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(url));
                $('#shareTwitter').attr('href', 'https://twitter.com/intent/tweet?text=' + encodeURIComponent(title) + '&url=' + encodeURIComponent(url));
                $('#shareEmail').attr('href', 'mailto:?subject=' + encodeURIComponent(title) + '&body=' + encodeURIComponent(url));

                const modal = new bootstrap.Modal(document.getElementById('shareModal'));
                modal.show();
            });

            // Copy Share URL
            $('#copyShareUrlBtn').on('click', function() {
                const copyText = document.getElementById('shareUrlInput');
                copyText.select();
                copyText.setSelectionRange(0, 99999);
                navigator.clipboard.writeText(copyText.value);
                REMS.showToast('Link copied to clipboard!', 'success');
            });
        });
    </script>

    @yield('scripts')
</body>
</html>
