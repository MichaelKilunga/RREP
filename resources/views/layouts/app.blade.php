<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $org = current_organization();
        $branding = $org?->branding ?: \App\Models\BrandingConfig::first();
        $companyName = setting('company_name', $org?->name ?? 'RehoSpace');
        $companySubtitle = setting('company_subtitle', $branding?->company_tagline ?? 'Enterprise Portal');
        $brandMonogram = setting('brand_monogram', 'R');
        $faviconUrl = $branding?->favicon ?: setting('site_favicon');
        $primaryColor = $branding?->primary_color ?? '#0f52ba';
        $secondaryColor = $branding?->secondary_color ?? '#495057';
        $accentColor = $branding?->accent_color ?? '#00a86b';
        $isLightSidebar = ($branding?->sidebar_theme ?? 'dark') === 'light';
    @endphp

    <title>@yield('title', __('app.platform_name')) - {{ $companyName }}</title>

    <!-- Dynamic Favicon Injected from System Admin Branding -->
    @if(!empty($faviconUrl))
        <link rel="icon" type="image/x-icon" href="{{ $faviconUrl }}">
        <link rel="shortcut icon" href="{{ $faviconUrl }}">
        <link rel="apple-touch-icon" href="{{ $faviconUrl }}">
    @else
        <link rel="icon" type="image/x-icon" href="/favicon.ico">
    @endif

    <!-- Google Fonts: Inter & Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome & Bootstrap Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" rel="stylesheet">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
    <!-- Leaflet GIS CSS -->
    <link href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" rel="stylesheet">
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.7/dist/sweetalert2.min.css" rel="stylesheet">

    <style>
        :root {
            --rrep-primary: {{ $primaryColor }};
            --rrep-secondary: {{ $secondaryColor }};
            --rrep-accent: {{ $accentColor }};
            --rrep-sidebar-bg: {{ $isLightSidebar ? '#ffffff' : '#121929' }};
            --rrep-sidebar-hover: {{ $isLightSidebar ? '#f1f5f9' : '#1e293b' }};
            --rrep-sidebar-color: {{ $isLightSidebar ? '#475569' : '#94a3b8' }};
            --rrep-body-bg: #f8fafc;
            --rrep-card-border: #e2e8f0;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: var(--rrep-body-bg);
            color: #1e293b;
            overflow-x: hidden;
        }

        h1, h2, h3, h4, h5, h6, .brand-font {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 700;
        }

        /* Sidebar Styling */
        .sidebar {
            width: 280px;
            height: 100vh;
            height: 100dvh;
            max-height: 100dvh;
            background-color: var(--rrep-sidebar-bg);
            color: var(--rrep-sidebar-color);
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1045;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), width 0.3s ease;
            box-shadow: 4px 0 24px rgba(0, 0, 0, 0.12);
            overflow: hidden;
            border-right: 1px solid {{ $isLightSidebar ? 'rgba(0, 0, 0, 0.08)' : 'rgba(255, 255, 255, 0.07)' }};
        }

        .sidebar-scrollable {
            flex: 1 1 auto;
            overflow-y: auto;
            overflow-x: hidden;
            padding: 0.85rem 0.65rem 4rem;
            scrollbar-width: thin;
            scrollbar-color: {{ $isLightSidebar ? 'rgba(0,0,0,0.15) transparent' : 'rgba(255,255,255,0.15) transparent' }};
        }

        .sidebar-scrollable::-webkit-scrollbar {
            width: 5px;
        }

        .sidebar-scrollable::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar-scrollable::-webkit-scrollbar-thumb {
            background: {{ $isLightSidebar ? 'rgba(0,0,0,0.15)' : 'rgba(255,255,255,0.15)' }};
            border-radius: 6px;
        }

        .sidebar-scrollable::-webkit-scrollbar-thumb:hover {
            background: {{ $isLightSidebar ? 'rgba(0,0,0,0.3)' : 'rgba(255,255,255,0.3)' }};
        }

        .sidebar .brand-header {
            flex-shrink: 0;
            padding: 1.15rem 1.25rem;
            background: {{ $isLightSidebar ? '#ffffff' : 'rgba(15, 23, 42, 0.95)' }};
            border-bottom: 1px solid {{ $isLightSidebar ? 'rgba(0, 0, 0, 0.08)' : 'rgba(255, 255, 255, 0.08)' }};
            backdrop-filter: blur(8px);
            z-index: 2;
        }

        .sidebar-footer {
            flex-shrink: 0;
            padding: 0.85rem 1rem;
            background: {{ $isLightSidebar ? '#f8fafc' : 'rgba(11, 17, 32, 0.9)' }};
            border-top: 1px solid {{ $isLightSidebar ? 'rgba(0, 0, 0, 0.08)' : 'rgba(255, 255, 255, 0.08)' }};
            backdrop-filter: blur(8px);
        }

        /* Direct items (Dashboard, Marketplace, etc.) */
        .sidebar .nav-item-direct {
            display: flex;
            align-items: center;
            padding: 0.58rem 0.85rem;
            font-size: 0.85rem;
            font-weight: 500;
            color: var(--rrep-sidebar-color);
            border-radius: 0.5rem;
            margin: 0.15rem 0;
            text-decoration: none;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            min-width: 0;
        }

        .sidebar .nav-item-direct:hover {
            color: {{ $isLightSidebar ? '#0f172a' : '#ffffff' }};
            background-color: var(--rrep-sidebar-hover);
        }

        .sidebar .nav-item-direct.active {
            background: linear-gradient(135deg, var(--rrep-primary) 0%, #2563eb 100%);
            color: #ffffff !important;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(15, 82, 186, 0.3);
        }

        .sidebar .nav-item-direct .nav-icon {
            font-size: 1rem;
            width: 1.75rem;
            height: 1.75rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.4rem;
            margin-right: 0.65rem;
            flex-shrink: 0;
            background: {{ $isLightSidebar ? 'rgba(15, 82, 186, 0.08)' : 'rgba(255, 255, 255, 0.06)' }};
            color: {{ $isLightSidebar ? 'var(--rrep-primary)' : '#94a3b8' }};
            transition: all 0.2s ease;
        }

        .sidebar .nav-item-direct:hover .nav-icon {
            color: {{ $isLightSidebar ? 'var(--rrep-primary)' : '#ffffff' }};
            background: {{ $isLightSidebar ? 'rgba(15, 82, 186, 0.15)' : 'rgba(255, 255, 255, 0.12)' }};
        }

        .sidebar .nav-item-direct.active .nav-icon {
            background: rgba(255, 255, 255, 0.2);
            color: #ffffff;
        }

        /* Collapsible Accordion Groups */
        .nav-group {
            margin-bottom: 0.3rem;
        }

        .nav-group-trigger {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            padding: 0.58rem 0.85rem;
            font-size: 0.84rem;
            font-weight: 600;
            color: var(--rrep-sidebar-color);
            background: transparent;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            text-align: left;
            text-decoration: none;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            min-width: 0;
        }

        .nav-group-trigger:hover {
            color: {{ $isLightSidebar ? '#0f172a' : '#ffffff' }};
            background-color: var(--rrep-sidebar-hover);
        }

        .nav-group.active-group .nav-group-trigger {
            color: {{ $isLightSidebar ? 'var(--rrep-primary)' : '#f8fafc' }};
        }

        .nav-group-trigger .group-left {
            display: flex;
            align-items: center;
            min-width: 0;
            flex: 1 1 auto;
            gap: 0.65rem;
        }

        .nav-group-trigger .group-icon {
            font-size: 0.95rem;
            width: 1.75rem;
            height: 1.75rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.4rem;
            flex-shrink: 0;
            background: {{ $isLightSidebar ? 'rgba(15, 82, 186, 0.08)' : 'rgba(255, 255, 255, 0.05)' }};
            color: {{ $isLightSidebar ? 'var(--rrep-primary)' : '#94a3b8' }};
            transition: all 0.2s ease;
        }

        .nav-group-trigger:hover .group-icon,
        .nav-group.active-group .nav-group-trigger .group-icon {
            background: {{ $isLightSidebar ? 'rgba(15, 82, 186, 0.15)' : 'rgba(255, 255, 255, 0.12)' }};
            color: {{ $isLightSidebar ? 'var(--rrep-primary)' : '#60a5fa' }};
        }

        .nav-group-trigger .group-title {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            min-width: 0;
            letter-spacing: -0.01em;
        }

        .nav-group-trigger .group-right {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            margin-left: 0.5rem;
            flex-shrink: 0;
        }

        .nav-group-trigger .chevron-icon {
            font-size: 0.72rem;
            color: #64748b;
            transition: transform 0.25s ease, color 0.2s ease;
        }

        .nav-group-trigger[aria-expanded="true"] .chevron-icon {
            transform: rotate(180deg);
            color: {{ $isLightSidebar ? '#0f172a' : '#cbd5e1' }};
        }

        /* Subgroup / Children */
        .nav-subgroup {
            margin: 0.2rem 0 0.4rem 1.6rem;
            padding-left: 0.75rem;
            border-left: 1.5px solid {{ $isLightSidebar ? 'rgba(0, 0, 0, 0.08)' : 'rgba(255, 255, 255, 0.1)' }};
            display: flex;
            flex-direction: column;
            gap: 0.15rem;
        }

        .nav-sublink {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            padding: 0.46rem 0.7rem;
            font-size: 0.82rem;
            font-weight: 500;
            color: var(--rrep-sidebar-color);
            border-radius: 0.4rem;
            text-decoration: none;
            transition: all 0.18s ease;
            min-width: 0;
        }

        .nav-sublink:hover {
            color: {{ $isLightSidebar ? '#0f172a' : '#ffffff' }};
            background-color: var(--rrep-sidebar-hover);
            padding-left: 0.85rem;
        }

        .nav-sublink.active {
            background: linear-gradient(135deg, var(--rrep-primary) 0%, #2563eb 100%);
            color: #ffffff !important;
            font-weight: 600;
            box-shadow: 0 4px 10px rgba(15, 82, 186, 0.35);
        }

        .nav-sublink i {
            font-size: 0.88rem;
            width: 1.15rem;
            text-align: center;
            flex-shrink: 0;
            opacity: 0.8;
        }

        .nav-sublink:hover i, .nav-sublink.active i {
            opacity: 1;
        }

        .nav-sublink .sublink-label {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            min-width: 0;
            flex: 1 1 auto;
        }

        .sidebar-badge {
            font-size: 0.62rem;
            font-weight: 700;
            padding: 0.15rem 0.4rem;
            border-radius: 9999px;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            flex-shrink: 0;
            line-height: 1.2;
        }

        .sidebar-section-divider {
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #64748b;
            padding: 0.85rem 0.85rem 0.3rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        /* Backdrop on mobile */
        .sidebar-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(4px);
            z-index: 1040;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.25s ease, visibility 0.25s ease;
        }

        .sidebar-backdrop.show {
            opacity: 1;
            visibility: visible;
        }

        /* Main Content Wrapper */
        .main-wrapper {
            margin-left: 280px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: margin-left 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        body.sidebar-collapsed .main-wrapper {
            margin-left: 0;
        }

        body.sidebar-collapsed .sidebar {
            transform: translateX(-100%);
        }

        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
                box-shadow: none;
            }
            .sidebar.show {
                transform: translateX(0);
                box-shadow: 6px 0 30px rgba(0, 0, 0, 0.25);
            }
            .main-wrapper {
                margin-left: 0 !important;
            }
        }

        /* Top Navbar */
        .topbar {
            height: 70px;
            background: #ffffff;
            border-bottom: 1px solid var(--rrep-card-border);
            padding: 0 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 999;
        }

        /* Cards & Buttons */
        .card {
            border: 1px solid var(--rrep-card-border);
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.04);
        }

        .card-header {
            background: #ffffff;
            border-bottom: 1px solid var(--rrep-card-border);
            padding: 1rem 1.25rem;
            font-weight: 600;
        }

        .btn-primary {
            background-color: var(--rrep-primary);
            border-color: var(--rrep-primary);
        }

        .btn-primary:hover, .btn-primary:focus {
            filter: brightness(0.9);
            background-color: var(--rrep-primary);
            border-color: var(--rrep-primary);
        }

        .btn-outline-primary {
            color: var(--rrep-primary);
            border-color: var(--rrep-primary);
        }

        .btn-outline-primary:hover, .btn-outline-primary:focus {
            background-color: var(--rrep-primary);
            border-color: var(--rrep-primary);
            color: #ffffff;
        }

        .btn-accent {
            background-color: var(--rrep-accent);
            border-color: var(--rrep-accent);
            color: #ffffff;
        }

        .text-primary {
            color: var(--rrep-primary) !important;
        }

        .bg-primary {
            background-color: var(--rrep-primary) !important;
        }

        .border-primary {
            border-color: var(--rrep-primary) !important;
        }

        .badge-status-available { background-color: #dcfce7; color: #15803d; }
        .badge-status-reserved { background-color: #fef9c3; color: #a16207; }
        .badge-status-sold { background-color: #fee2e2; color: #b91c1c; }
        .badge-status-leased { background-color: #e0e7ff; color: #4338ca; }
        .badge-status-undercontract { background-color: #f3e8ff; color: #7e22ce; }

    </style>

    @if(!empty($branding?->custom_css))
        <!-- Custom CSS Configured in System Admin Panel -->
        <style id="rrep-custom-branding-css">
            {!! $branding->custom_css !!}
        </style>
    @endif
    @yield('styles')
</head>
<body>

    <!-- Sidebar Navigation -->
    <aside class="sidebar" id="appSidebar">
        <!-- Brand Header -->
        <div class="brand-header d-flex align-items-center justify-content-between">
            <a href="{{ route('dashboard') }}" class="text-decoration-none d-flex align-items-center gap-2 min-w-0" style="min-width: 0;">
                @if(!empty($branding?->header_logo))
                    <img src="{{ $branding->header_logo }}" alt="{{ $companyName }}" class="rounded-2 shadow-sm flex-shrink-0" style="max-height: 38px; max-width: 48px; object-fit: contain;">
                @else
                    <div class="rounded-3 text-white d-flex align-items-center justify-content-center shadow-sm flex-shrink-0" style="width: 38px; height: 38px; font-weight: 800; font-size: 1.2rem; background-color: var(--rrep-primary);">
                        {{ $brandMonogram }}
                    </div>
                @endif
                <div class="min-w-0" style="min-width: 0;">
                    <div class="{{ $isLightSidebar ? 'text-dark' : 'text-white' }} fw-bold brand-font text-truncate" style="font-size: 1.02rem; line-height: 1.1; max-width: 165px;" title="{!! strip_tags($companyName) !!}">
                        {!! $companyName !!}
                    </div>
                    <span class="badge {{ $isLightSidebar ? 'bg-light text-dark border' : 'bg-secondary' }} text-truncate d-inline-block" style="font-size: 0.65rem; max-width: 165px;">
                        {!! $companySubtitle !!}
                    </span>
                </div>
            </a>
            <button class="btn btn-sm {{ $isLightSidebar ? 'btn-light text-dark' : 'btn-dark text-white-50' }} border-0 d-lg-none flex-shrink-0" id="sidebarCloseBtn" aria-label="Close Navigation">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <!-- Scrollable Navigation Items -->
        <div class="sidebar-scrollable">
            @php
                $groupPropertiesActive = request()->routeIs('properties.*') || request()->routeIs('documents.*');
                $groupCrmActive = request()->routeIs('crm.*') || request()->routeIs('reservations.*') || request()->routeIs('appointments.*') || request()->routeIs('deals.*');
                $groupFinanceActive = request()->routeIs('finance.*');
                $groupSurveyActive = request()->routeIs('survey.*');
                $groupIntelActive = request()->routeIs('reports.*') || request()->routeIs('ai.*') || request()->routeIs('marketing.*');
                $groupPortalsActive = request()->routeIs('portals.*') || request()->routeIs('workflows.*') || request()->routeIs('compliance.*');
                $groupSettingsActive = request()->routeIs('users.*') || request()->routeIs('settings.*') || request()->routeIs('loyalty.*') || request()->routeIs('notifications.*');
            @endphp

            <!-- Main Overview -->
            <div class="sidebar-section-divider">
                <span>{{ __('app.dashboard') }}</span>
            </div>
            <a href="{{ route('dashboard') }}" class="nav-item-direct {{ request()->routeIs('dashboard') ? 'active' : '' }}" title="{{ __('app.dashboard') }}">
                <span class="nav-icon"><i class="bi bi-grid-1x2-fill"></i></span>
                <span class="sublink-label">{{ __('app.dashboard') }}</span>
            </a>

            <!-- Section: Core Modules -->
            <div class="sidebar-section-divider mt-2">
                <span>Operations & Assets</span>
            </div>

            <!-- 1. Properties & Land -->
            <div class="nav-group {{ $groupPropertiesActive ? 'active-group' : '' }}">
                <button class="nav-group-trigger {{ $groupPropertiesActive ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#navCollapseProperties" aria-expanded="{{ $groupPropertiesActive ? 'true' : 'false' }}">
                    <div class="group-left">
                        <span class="group-icon"><i class="bi bi-buildings"></i></span>
                        <span class="group-title">{{ __('app.properties') }} & Assets</span>
                    </div>
                    <div class="group-right">
                        <i class="bi bi-chevron-down chevron-icon"></i>
                    </div>
                </button>
                <div class="collapse {{ $groupPropertiesActive ? 'show' : '' }}" id="navCollapseProperties">
                    <div class="nav-subgroup">
                        <a href="{{ route('properties.index') }}" class="nav-sublink {{ request()->routeIs('properties.index*') || request()->routeIs('properties.show') ? 'active' : '' }}" title="{{ __('app.property_list') }}">
                            <i class="bi bi-building"></i>
                            <span class="sublink-label">{{ __('app.property_list') }}</span>
                        </a>
                        <a href="{{ route('properties.create') }}" class="nav-sublink {{ request()->routeIs('properties.create') ? 'active' : '' }}" title="{{ __('app.add_property') }}">
                            <i class="bi bi-plus-circle text-success"></i>
                            <span class="sublink-label">{{ __('app.add_property') }}</span>
                        </a>
                        <a href="{{ route('documents.index') }}" class="nav-sublink {{ request()->routeIs('documents.*') ? 'active' : '' }}" title="EDMS Document Vault">
                            <i class="bi bi-folder-check text-info"></i>
                            <span class="sublink-label">Document Vault</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- 2. CRM & Commercial Sales -->
            <div class="nav-group {{ $groupCrmActive ? 'active-group' : '' }}">
                <button class="nav-group-trigger {{ $groupCrmActive ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#navCollapseCRM" aria-expanded="{{ $groupCrmActive ? 'true' : 'false' }}">
                    <div class="group-left">
                        <span class="group-icon"><i class="bi bi-people"></i></span>
                        <span class="group-title">{{ __('app.crm_sales') }}</span>
                    </div>
                    <div class="group-right">
                        <i class="bi bi-chevron-down chevron-icon"></i>
                    </div>
                </button>
                <div class="collapse {{ $groupCrmActive ? 'show' : '' }}" id="navCollapseCRM">
                    <div class="nav-subgroup">
                        <a href="{{ route('crm.leads') }}" class="nav-sublink {{ request()->routeIs('crm.leads') ? 'active' : '' }}" title="{{ __('app.leads') }} Pipeline">
                            <i class="bi bi-funnel text-info"></i>
                            <span class="sublink-label">{{ __('app.leads') }} Pipeline</span>
                        </a>
                        <a href="{{ route('crm.customers') }}" class="nav-sublink {{ request()->routeIs('crm.customers') ? 'active' : '' }}" title="{{ __('app.customers') }}">
                            <i class="bi bi-person-lines-fill"></i>
                            <span class="sublink-label">{{ __('app.customers') }}</span>
                        </a>
                        <a href="{{ route('reservations.index') }}" class="nav-sublink {{ request()->routeIs('reservations.*') ? 'active' : '' }}" title="{{ __('app.reservations') }}">
                            <i class="bi bi-bookmark-check text-warning"></i>
                            <span class="sublink-label">{{ __('app.reservations') }}</span>
                        </a>
                        <a href="{{ route('appointments.index') }}" class="nav-sublink {{ request()->routeIs('appointments.*') ? 'active' : '' }}" title="{{ __('app.appointments') }}">
                            <i class="bi bi-calendar-event"></i>
                            <span class="sublink-label">{{ __('app.appointments') }}</span>
                        </a>
                        <a href="{{ route('deals.index') }}" class="nav-sublink {{ request()->routeIs('deals.*') ? 'active' : '' }}" title="{{ __('app.deals') }} & Contracts">
                            <i class="bi bi-briefcase"></i>
                            <span class="sublink-label">{{ __('app.deals') }} & Contracts</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- 3. Finance & Billing -->
            <div class="nav-group {{ $groupFinanceActive ? 'active-group' : '' }}">
                <button class="nav-group-trigger {{ $groupFinanceActive ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#navCollapseFinance" aria-expanded="{{ $groupFinanceActive ? 'true' : 'false' }}">
                    <div class="group-left">
                        <span class="group-icon"><i class="bi bi-credit-card-2-front"></i></span>
                        <span class="group-title">{{ __('app.finance') }} & Accounts</span>
                    </div>
                    <div class="group-right">
                        <i class="bi bi-chevron-down chevron-icon"></i>
                    </div>
                </button>
                <div class="collapse {{ $groupFinanceActive ? 'show' : '' }}" id="navCollapseFinance">
                    <div class="nav-subgroup">
                        <a href="{{ route('finance.invoices') }}" class="nav-sublink {{ request()->routeIs('finance.invoices*') ? 'active' : '' }}" title="Tax Invoices & Receipts">
                            <i class="bi bi-receipt"></i>
                            <span class="sublink-label">Invoices & Receipts</span>
                        </a>
                        <a href="{{ route('finance.expenses') }}" class="nav-sublink {{ request()->routeIs('finance.expenses*') ? 'active' : '' }}" title="Operating Expenses">
                            <i class="bi bi-wallet2 text-danger"></i>
                            <span class="sublink-label">Operating Expenses</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- 4. Land Survey & GIS -->
            <div class="nav-group {{ $groupSurveyActive ? 'active-group' : '' }}">
                <button class="nav-group-trigger {{ $groupSurveyActive ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#navCollapseSurvey" aria-expanded="{{ $groupSurveyActive ? 'true' : 'false' }}">
                    <div class="group-left">
                        <span class="group-icon"><i class="bi bi-geo-alt"></i></span>
                        <span class="group-title">{{ __('app.survey_gis') }}</span>
                    </div>
                    <div class="group-right">
                        <i class="bi bi-chevron-down chevron-icon"></i>
                    </div>
                </button>
                <div class="collapse {{ $groupSurveyActive ? 'show' : '' }}" id="navCollapseSurvey">
                    <div class="nav-subgroup">
                        <a href="{{ route('survey.index') }}" class="nav-sublink {{ request()->routeIs('survey.index*') || request()->routeIs('survey.show') ? 'active' : '' }}" title="{{ __('app.survey_projects') }}">
                            <i class="bi bi-compass"></i>
                            <span class="sublink-label">{{ __('app.survey_projects') }}</span>
                        </a>
                        <a href="{{ route('survey.map') }}" class="nav-sublink {{ request()->routeIs('survey.map') ? 'active' : '' }}" title="{{ __('app.gis_map_viewer') }}">
                            <i class="bi bi-map text-success"></i>
                            <span class="sublink-label">{{ __('app.gis_map_viewer') }}</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Section: Intelligence & Portals -->
            <div class="sidebar-section-divider mt-2">
                <span>Intelligence & Portals</span>
            </div>

            <!-- 5. Intelligence & Marketing -->
            <div class="nav-group {{ $groupIntelActive ? 'active-group' : '' }}">
                <button class="nav-group-trigger {{ $groupIntelActive ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#navCollapseIntel" aria-expanded="{{ $groupIntelActive ? 'true' : 'false' }}">
                    <div class="group-left">
                        <span class="group-icon"><i class="bi bi-cpu"></i></span>
                        <span class="group-title">Intelligence & AI</span>
                    </div>
                    <div class="group-right">
                        <i class="bi bi-chevron-down chevron-icon"></i>
                    </div>
                </button>
                <div class="collapse {{ $groupIntelActive ? 'show' : '' }}" id="navCollapseIntel">
                    <div class="nav-subgroup">
                        <a href="{{ route('reports.index') }}" class="nav-sublink {{ request()->routeIs('reports.*') ? 'active' : '' }}" title="Reports Center & Analytics">
                            <i class="bi bi-bar-chart-line"></i>
                            <span class="sublink-label">Reports & Analytics</span>
                        </a>
                        <a href="{{ route('ai.chat') }}" class="nav-sublink {{ request()->routeIs('ai.*') ? 'active' : '' }}" title="AI Smart Studio">
                            <i class="bi bi-stars text-warning"></i>
                            <span class="sublink-label">AI Smart Studio</span>
                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle sidebar-badge">AI</span>
                        </a>
                        <a href="{{ route('marketing.index') }}" class="nav-sublink {{ request()->routeIs('marketing.*') ? 'active' : '' }}" title="Campaigns & Marketing">
                            <i class="bi bi-megaphone text-info"></i>
                            <span class="sublink-label">Campaigns & Marketing</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- 6. Portals & Governance -->
            <div class="nav-group {{ $groupPortalsActive ? 'active-group' : '' }}">
                <button class="nav-group-trigger {{ $groupPortalsActive ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#navCollapsePortals" aria-expanded="{{ $groupPortalsActive ? 'true' : 'false' }}">
                    <div class="group-left">
                        <span class="group-icon"><i class="bi bi-shield-check"></i></span>
                        <span class="group-title">Portals & Governance</span>
                    </div>
                    <div class="group-right">
                        <i class="bi bi-chevron-down chevron-icon"></i>
                    </div>
                </button>
                <div class="collapse {{ $groupPortalsActive ? 'show' : '' }}" id="navCollapsePortals">
                    <div class="nav-subgroup">
                        <a href="{{ route('portals.client') }}" class="nav-sublink {{ request()->routeIs('portals.client') ? 'active' : '' }}" title="Client Self-Service Portal">
                            <i class="bi bi-person-workspace"></i>
                            <span class="sublink-label">Client Portal</span>
                        </a>
                        <a href="{{ route('portals.owner') }}" class="nav-sublink {{ request()->routeIs('portals.owner') ? 'active' : '' }}" title="Landlord & Owner Portal">
                            <i class="bi bi-building-check"></i>
                            <span class="sublink-label">Owner Portal</span>
                        </a>
                        <a href="{{ route('workflows.index') }}" class="nav-sublink {{ request()->routeIs('workflows.*') ? 'active' : '' }}" title="Approval Workflows">
                            <i class="bi bi-diagram-3"></i>
                            <span class="sublink-label">Approval Workflows</span>
                        </a>
                        <a href="{{ route('compliance.kyc') }}" class="nav-sublink {{ request()->routeIs('compliance.*') ? 'active' : '' }}" title="KYC & Regulatory Compliance">
                            <i class="bi bi-patch-check text-info"></i>
                            <span class="sublink-label">KYC & Compliance</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Section: System & Settings -->
            <div class="sidebar-section-divider mt-2">
                <span>{{ __('app.settings') }} & Control</span>
            </div>

            <!-- 7. Administration & Settings -->
            <div class="nav-group {{ $groupSettingsActive ? 'active-group' : '' }}">
                <button class="nav-group-trigger {{ $groupSettingsActive ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#navCollapseSettings" aria-expanded="{{ $groupSettingsActive ? 'true' : 'false' }}">
                    <div class="group-left">
                        <span class="group-icon"><i class="bi bi-gear-wide-connected"></i></span>
                        <span class="group-title">{{ __('app.settings') }} & Admin</span>
                    </div>
                    <div class="group-right">
                        <i class="bi bi-chevron-down chevron-icon"></i>
                    </div>
                </button>
                <div class="collapse {{ $groupSettingsActive ? 'show' : '' }}" id="navCollapseSettings">
                    <div class="nav-subgroup">
                        <a href="{{ route('users.index') }}" class="nav-sublink {{ request()->routeIs('users.*') ? 'active' : '' }}" title="User & Staff Management">
                            <i class="bi bi-people-fill text-info"></i>
                            <span class="sublink-label">User & Staff Directory</span>
                        </a>
                        <a href="{{ route('settings.rbac') }}" class="nav-sublink {{ request()->routeIs('settings.rbac') ? 'active' : '' }}" title="Role Matrix & Permissions">
                            <i class="bi bi-shield-lock text-primary"></i>
                            <span class="sublink-label">Role Matrix & RBAC</span>
                        </a>
                        <a href="{{ route('loyalty.index') }}" class="nav-sublink {{ request()->routeIs('loyalty.*') ? 'active' : '' }}" title="Customer Loyalty & Retention">
                            <i class="bi bi-gift text-warning"></i>
                            <span class="sublink-label">Loyalty & Rewards</span>
                        </a>
                        <a href="{{ route('settings.index') }}" class="nav-sublink {{ request()->routeIs('settings.index') ? 'active' : '' }}" title="System Settings">
                            <i class="bi bi-sliders"></i>
                            <span class="sublink-label">System Configuration</span>
                        </a>
                        <a href="{{ route('notifications.index') }}" class="nav-sublink {{ request()->routeIs('notifications.*') ? 'active' : '' }}" title="System Notifications">
                            <i class="bi bi-bell text-secondary"></i>
                            <span class="sublink-label">Notification Logs</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Public Marketplace Link -->
            <div class="sidebar-section-divider mt-2">
                <span>{{ __('app.marketplace') }}</span>
            </div>
            <a href="{{ route('marketplace.index') }}" target="_blank" class="nav-item-direct" title="Live Marketplace (Opens in new tab)">
                <span class="nav-icon"><i class="bi bi-globe2 text-success"></i></span>
                <span class="sublink-label">Live Marketplace</span>
                <span class="badge bg-success-subtle text-success border border-success-subtle sidebar-badge">LIVE</span>
                <i class="bi bi-box-arrow-up-right ms-1 text-muted" style="font-size: 0.7rem;"></i>
            </a>
        </div>

        <!-- Pinned Bottom User Card -->
        <div class="sidebar-footer d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2 min-w-0" style="min-width: 0;">
                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold flex-shrink-0 shadow-sm" style="width: 34px; height: 34px; font-size: 0.85rem;">
                    {{ substr(auth()->user()?->name ?? 'A', 0, 1) }}
                </div>
                <div class="min-w-0" style="min-width: 0;">
                    <div class="{{ $isLightSidebar ? 'text-dark' : 'text-white' }} fw-semibold text-truncate" style="font-size: 0.82rem; line-height: 1.2;" title="{{ auth()->user()?->name }}">
                        {{ auth()->user()?->name ?? 'Administrator' }}
                    </div>
                    <span class="badge {{ $isLightSidebar ? 'bg-secondary-subtle text-secondary' : 'bg-dark text-white-50 border border-secondary' }}" style="font-size: 0.62rem; padding: 0.15rem 0.4rem;">
                        {{ auth()->user()?->job_title ?? 'Staff' }}
                    </span>
                </div>
            </div>
            <form action="{{ route('logout') }}" method="POST" class="m-0 flex-shrink-0">
                @csrf
                <button type="submit" class="btn btn-sm btn-link {{ $isLightSidebar ? 'text-muted hover-dark' : 'text-white-50 hover-white' }} p-1 text-decoration-none" title="{{ __('app.logout') }}" aria-label="{{ __('app.logout') }}">
                    <i class="bi bi-box-arrow-right fs-6"></i>
                </button>
            </form>
        </div>
    </aside>

    <!-- Mobile Backdrop Overlay -->
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    <!-- Main Content Wrapper -->
    <div class="main-wrapper">
        <!-- Topbar -->
        <header class="topbar">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-light border d-flex align-items-center justify-content-center" id="sidebarToggleBtn" title="Toggle Navigation Sidebar" aria-label="Toggle Navigation Sidebar" style="width: 38px; height: 38px;">
                    <i class="bi bi-list fs-5"></i>
                </button>

                <!-- Branch Selector -->
                <form action="{{ route('settings.switch_branch') }}" method="POST" class="d-none d-md-flex align-items-center gap-2">
                    @csrf
                    <i class="bi bi-geo-alt-fill text-primary"></i>
                    <select name="branch_id" class="form-select form-select-sm" onchange="this.form.submit()" style="font-size: 0.85rem; font-weight: 500; min-width: 180px;">
                        <option value="all" {{ session('current_branch_id') === 'all' ? 'selected' : '' }}>🏢 {{ __('app.all_branches') }}</option>
                        @foreach(\App\Models\Branch::all() as $b)
                            <option value="{{ $b->id }}" {{ session('current_branch_id') == $b->id ? 'selected' : '' }}>
                                {{ $b->name }}
                            </option>
                        @endforeach
                    </select>
                </form>

                <!-- Environment Mode Indicator -->
                <a href="{{ route('settings.index') }}#environment" class="badge {{ is_production_mode() ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-warning-subtle text-warning-emphasis border border-warning-subtle' }} text-decoration-none py-1 px-2 d-none d-sm-inline-flex align-items-center gap-1" title="Click to manage System Environment">
                    <i class="bi {{ is_production_mode() ? 'bi-shield-check' : 'bi-code-slash' }}"></i>
                    <span>{{ is_production_mode() ? 'Live / Production' : 'Dev / Local' }}</span>
                </a>
            </div>

            <!-- Right Controls -->
            <div class="d-flex align-items-center gap-3">
                <!-- AI Quick Launch Button -->
                <button class="btn btn-sm btn-outline-primary d-flex align-items-center gap-1 rounded-pill px-3" data-bs-toggle="offcanvas" data-bs-target="#aiOffcanvas">
                    <i class="bi bi-stars text-warning"></i> <span class="d-none d-sm-inline">Ask AI</span>
                </button>

                <!-- Language Switcher -->
                <div class="dropdown">
                    <button class="btn btn-sm btn-light dropdown-toggle d-flex align-items-center gap-1 rounded-pill px-3" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-translate text-secondary"></i>
                        <span class="text-uppercase fw-bold" style="font-size: 0.8rem;">{{ app()->getLocale() }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                        <li><a class="dropdown-item d-flex align-items-center gap-2 {{ app()->getLocale() == 'en' ? 'active' : '' }}" href="?lang=en">🇺🇸 {{ __('app.english') }}</a></li>
                        <li><a class="dropdown-item d-flex align-items-center gap-2 {{ app()->getLocale() == 'sw' ? 'active' : '' }}" href="?lang=sw">🇹🇿 {{ __('app.swahili') }}</a></li>
                    </ul>
                </div>

                <!-- User Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-light d-flex align-items-center gap-2 rounded-pill p-1 pe-3 border" type="button" data-bs-toggle="dropdown">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 0.85rem;">
                            {{ substr(auth()->user()?->name ?? 'A', 0, 1) }}
                        </div>
                        <span class="d-none d-md-inline fw-semibold text-dark" style="font-size: 0.85rem;">{{ auth()->user()?->name ?? 'Administrator' }}</span>
                        <i class="bi bi-chevron-down" style="font-size: 0.75rem;"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                        <li class="px-3 py-2 border-bottom">
                            <div class="fw-bold">{{ auth()->user()?->name }}</div>
                            <small class="text-muted">{{ auth()->user()?->email }}</small>
                            <div class="mt-1"><span class="badge bg-primary-subtle text-primary">{{ auth()->user()?->job_title ?? 'Staff' }}</span></div>
                        </li>
                        <li><a class="dropdown-item py-2" href="{{ route('settings.index') }}"><i class="bi bi-sliders me-2"></i> {{ __('app.settings') }}</a></li>
                        <li><a class="dropdown-item py-2" href="{{ route('portals.client') }}"><i class="bi bi-person me-2"></i> Client Portal</a></li>
                        <li><a class="dropdown-item py-2" href="{{ route('portals.owner') }}"><i class="bi bi-building me-2"></i> Owner Portal</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger py-2"><i class="bi bi-box-arrow-right me-2"></i> {{ __('app.logout') }}</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Main Body -->
        <main class="flex-grow-1 p-3 p-md-4">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
                    <i class="bi bi-check-circle-fill fs-5"></i>
                    <div>{{ session('success') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('info'))
                <div class="alert alert-info alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
                    <i class="bi bi-info-circle-fill fs-5"></i>
                    <div>{{ session('info') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
                    <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                    <div>{{ session('error') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="bg-white border-top py-3 px-4 text-center text-muted" style="font-size: 0.85rem;">
            &copy; {{ date('Y') }} <strong>{{ $companyName }}</strong>. {{ setting('footer_copyright', 'All Rights Reserved. Built on RREP Architecture.') }}
        </footer>
    </div>

    <!-- AI Assistant Offcanvas Drawer -->
    <div class="offcanvas offcanvas-end shadow-lg" tabindex="-1" id="aiOffcanvas" style="width: 420px;">
        <div class="offcanvas-header bg-dark text-white">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-stars text-warning fs-4"></i>
                <h5 class="offcanvas-title brand-font text-white mb-0">{{ $companyName }} AI Assistant</h5>
            </div>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body d-flex flex-column p-0">
            <div class="flex-grow-1 p-3 overflow-auto" id="aiChatBox" style="background: #f1f5f9;">
                <div class="d-flex gap-2 mb-3">
                    <div class="rounded-circle bg-dark text-warning p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                        <i class="bi bi-stars"></i>
                    </div>
                    <div class="card p-3 shadow-sm border-0" style="max-width: 85%; font-size: 0.875rem;">
                        Hello! I am your <strong>{{ $companyName }} Intelligent Assistant</strong>. How can I assist you with properties, valuation estimates, GIS coordinates, or CRM deals today?
                    </div>
                </div>
            </div>

            <div class="p-3 bg-white border-top">
                <div class="d-flex flex-wrap gap-1 mb-2">
                    <button class="btn btn-sm btn-light border rounded-pill text-muted px-2 py-0 ai-chip" style="font-size: 0.75rem;">📊 Estimate Masaki Villa</button>
                    <button class="btn btn-sm btn-light border rounded-pill text-muted px-2 py-0 ai-chip" style="font-size: 0.75rem;">📝 Draft Sale Contract</button>
                </div>
                <div class="input-group">
                    <input type="text" class="form-control" id="aiPromptInput" placeholder="Ask AI assistant...">
                    <button class="btn btn-primary" id="aiSendBtn"><i class="bi bi-send-fill"></i></button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.7/dist/sweetalert2.all.min.js"></script>

    <script>
        $(document).ready(function () {
            // Restore desktop sidebar collapsed preference
            if ($(window).width() >= 992 && localStorage.getItem('rrep_sidebar_collapsed') === 'true') {
                $('body').addClass('sidebar-collapsed');
            }

            // Sidebar Toggle (Responsive for Mobile & Desktop)
            $('#sidebarToggleBtn').on('click', function () {
                if ($(window).width() < 992) {
                    $('#appSidebar').toggleClass('show');
                    $('#sidebarBackdrop').toggleClass('show');
                } else {
                    $('body').toggleClass('sidebar-collapsed');
                    localStorage.setItem('rrep_sidebar_collapsed', $('body').hasClass('sidebar-collapsed'));
                }
            });

            // Close Sidebar via Close Button or Backdrop
            function closeMobileSidebar() {
                $('#appSidebar').removeClass('show');
                $('#sidebarBackdrop').removeClass('show');
            }

            $('#sidebarCloseBtn, #sidebarBackdrop').on('click', function () {
                closeMobileSidebar();
            });

            // Auto-close mobile sidebar when clicking any navigation link
            $('#appSidebar .nav-sublink, #appSidebar .nav-item-direct').on('click', function () {
                if ($(window).width() < 992) {
                    closeMobileSidebar();
                }
            });

            // ESC key to close sidebar on mobile
            $(document).on('keydown', function (e) {
                if (e.key === 'Escape' && $('#appSidebar').hasClass('show')) {
                    closeMobileSidebar();
                }
            });

            $('.datatable').DataTable({
                responsive: true,
                pageLength: 10,
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "{{ __('app.search') }}"
                }
            });

            $('.select2').select2({
                theme: 'bootstrap-5'
            });

            function sendAiMessage(prompt) {
                if (!prompt.trim()) return;
                
                $('#aiChatBox').append(`
                    <div class="d-flex justify-content-end mb-3">
                        <div class="card p-2 px-3 bg-primary text-white border-0 shadow-sm" style="max-width: 85%; font-size: 0.875rem;">
                            ${prompt}
                        </div>
                    </div>
                `);
                $('#aiPromptInput').val('');
                $('#aiChatBox').scrollTop($('#aiChatBox')[0].scrollHeight);

                $('#aiChatBox').append(`
                    <div class="d-flex gap-2 mb-3 ai-loading">
                        <div class="rounded-circle bg-dark text-warning p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                            <i class="bi bi-stars"></i>
                        </div>
                        <div class="card p-3 shadow-sm border-0 text-muted" style="max-width: 85%; font-size: 0.875rem;">
                            <span class="spinner-border spinner-border-sm text-primary me-2"></span> Analyzing real estate data...
                        </div>
                    </div>
                `);

                $.ajax({
                    url: "{{ route('ai.ask') }}",
                    method: "POST",
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    data: { prompt: prompt },
                    success: function (res) {
                        $('.ai-loading').remove();
                        $('#aiChatBox').append(`
                            <div class="d-flex gap-2 mb-3">
                                <div class="rounded-circle bg-dark text-warning p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                    <i class="bi bi-stars"></i>
                                </div>
                                <div class="card p-3 shadow-sm border-0 bg-white" style="max-width: 85%; font-size: 0.875rem;">
                                    ${res.response.replace(/\\n/g, '<br>')}
                                </div>
                            </div>
                        `);
                        $('#aiChatBox').scrollTop($('#aiChatBox')[0].scrollHeight);
                    },
                    error: function () {
                        $('.ai-loading').remove();
                        $('#aiChatBox').append(`
                            <div class="alert alert-danger p-2 small mb-3">Error contacting AI service.</div>
                        `);
                    }
                });
            }

            $('#aiSendBtn').on('click', function () {
                sendAiMessage($('#aiPromptInput').val());
            });

            $('#aiPromptInput').on('keypress', function (e) {
                if (e.which === 13) {
                    sendAiMessage($(this).val());
                }
            });

            $('.ai-chip').on('click', function () {
                sendAiMessage($(this).text().replace(/^[^\s]+\s/, ''));
            });
        });
    </script>
    @yield('scripts')
</body>
</html>
