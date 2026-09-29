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
        $companySubtitle = setting('company_subtitle', $branding?->company_tagline ?? 'Enterprise Admin');
        $brandMonogram = setting('brand_monogram', 'R');
        $faviconUrl = $branding?->favicon ?: setting('site_favicon');
        $primaryColor = $branding?->primary_color ?? '#0f52ba';
        $secondaryColor = $branding?->secondary_color ?? '#495057';
        $accentColor = $branding?->accent_color ?? '#10b981';
        $isLightSidebar = true; // Always crisp white light sidebar per user request
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
            --rrep-sidebar-bg: #ffffff;
            --rrep-sidebar-hover: #f1f5f9;
            --rrep-sidebar-color: #475569;
            --rrep-body-bg: #f4f6f9;
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
            background: #e6f4ea !important;
            color: #0d9488 !important;
            font-weight: 600;
            box-shadow: none;
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
            background: rgba(15, 82, 186, 0.08);
            color: var(--rrep-primary);
            transition: all 0.2s ease;
        }

        .sidebar .nav-item-direct:hover .nav-icon {
            color: var(--rrep-primary);
            background: rgba(15, 82, 186, 0.15);
        }

        .sidebar .nav-item-direct.active .nav-icon {
            background: rgba(13, 148, 136, 0.15) !important;
            color: #0d9488 !important;
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
            color: #0f172a;
            background-color: var(--rrep-sidebar-hover);
        }

        .nav-group.active-group .nav-group-trigger {
            color: var(--rrep-primary);
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
            background: rgba(15, 82, 186, 0.08);
            color: var(--rrep-primary);
            transition: all 0.2s ease;
        }

        .nav-group-trigger:hover .group-icon,
        .nav-group.active-group .nav-group-trigger .group-icon {
            background: rgba(15, 82, 186, 0.15);
            color: var(--rrep-primary);
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
            color: #0f172a;
        }

        /* Subgroup / Children */
        .nav-subgroup {
            margin: 0.2rem 0 0.4rem 1.6rem;
            padding-left: 0.75rem;
            border-left: 1.5px solid rgba(0, 0, 0, 0.08);
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
            color: #0f172a;
            background-color: var(--rrep-sidebar-hover);
            padding-left: 0.85rem;
        }

        .nav-sublink.active {
            background: #e6f4ea !important;
            color: #0d9488 !important;
            font-weight: 600;
            box-shadow: none;
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
                $groupPropertiesActive = request()->routeIs('admin.properties.*') || request()->routeIs('documents.*');
                $groupProjectsActive = request()->routeIs('admin.projects.*');
                $groupServicesActive = request()->routeIs('admin.services.*');
                $groupSurveyActive = request()->routeIs('survey.*');
                $groupCrmActive = request()->routeIs('crm.*') || request()->routeIs('reservations.*') || request()->routeIs('appointments.*') || request()->routeIs('deals.*');
                $groupFinanceActive = request()->routeIs('finance.*');
                $groupSettingsActive = request()->routeIs('users.*') || request()->routeIs('settings.*') || request()->routeIs('notifications.*');
            @endphp

            <!-- Dashboard -->
            <a href="{{ route('dashboard') }}" class="nav-item-direct {{ request()->routeIs('dashboard') ? 'active' : '' }}" title="Dashboard">
                <span class="nav-icon"><i class="bi bi-grid-1x2-fill"></i></span>
                <span class="sublink-label">Dashboard</span>
            </a>

            <!-- 1. Properties -->
            <div class="nav-group {{ $groupPropertiesActive ? 'active-group' : '' }}">
                <button class="nav-group-trigger {{ $groupPropertiesActive ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#navCollapseProperties" aria-expanded="{{ $groupPropertiesActive ? 'true' : 'false' }}">
                    <div class="group-left">
                        <span class="group-icon"><i class="bi bi-buildings"></i></span>
                        <span class="group-title">Properties</span>
                    </div>
                    <div class="group-right">
                        <i class="bi bi-chevron-down chevron-icon"></i>
                    </div>
                </button>
                <div class="collapse {{ $groupPropertiesActive ? 'show' : '' }}" id="navCollapseProperties">
                    <div class="nav-subgroup">
                        <a href="{{ route('admin.properties.index') }}" class="nav-sublink {{ request()->routeIs('admin.properties.index*') || request()->routeIs('admin.properties.show') ? 'active' : '' }}" title="Properties List">
                            <i class="bi bi-building"></i>
                            <span class="sublink-label">Properties List</span>
                        </a>
                        <a href="{{ route('admin.properties.create') }}" class="nav-sublink {{ request()->routeIs('admin.properties.create') ? 'active' : '' }}" title="Add Property">
                            <i class="bi bi-plus-circle text-success"></i>
                            <span class="sublink-label">Add Property</span>
                        </a>
                        <a href="{{ route('documents.index') }}" class="nav-sublink {{ request()->routeIs('documents.*') ? 'active' : '' }}" title="Documents">
                            <i class="bi bi-folder-check text-info"></i>
                            <span class="sublink-label">Documents</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- 2. Land Projects -->
            <div class="nav-group {{ $groupProjectsActive ? 'active-group' : '' }}">
                <button class="nav-group-trigger {{ $groupProjectsActive ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#navCollapseProjects" aria-expanded="{{ $groupProjectsActive ? 'true' : 'false' }}">
                    <div class="group-left">
                        <span class="group-icon"><i class="bi bi-map-fill"></i></span>
                        <span class="group-title">Projects</span>
                    </div>
                    <div class="group-right">
                        <i class="bi bi-chevron-down chevron-icon"></i>
                    </div>
                </button>
                <div class="collapse {{ $groupProjectsActive ? 'show' : '' }}" id="navCollapseProjects">
                    <div class="nav-subgroup">
                        <a href="{{ route('admin.projects.index') }}" class="nav-sublink {{ request()->routeIs('admin.projects.index*') ? 'active' : '' }}" title="Projects List">
                            <i class="bi bi-diagram-3"></i>
                            <span class="sublink-label">Projects List</span>
                        </a>
                        <a href="{{ route('admin.projects.create') }}" class="nav-sublink {{ request()->routeIs('admin.projects.create') ? 'active' : '' }}" title="New Project">
                            <i class="bi bi-plus-circle text-success"></i>
                            <span class="sublink-label">New Project</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- 3. Services -->
            <div class="nav-group {{ $groupServicesActive ? 'active-group' : '' }}">
                <button class="nav-group-trigger {{ $groupServicesActive ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#navCollapseServices" aria-expanded="{{ $groupServicesActive ? 'true' : 'false' }}">
                    <div class="group-left">
                        <span class="group-icon"><i class="bi bi-briefcase-fill"></i></span>
                        <span class="group-title">Services</span>
                    </div>
                    <div class="group-right">
                        <i class="bi bi-chevron-down chevron-icon"></i>
                    </div>
                </button>
                <div class="collapse {{ $groupServicesActive ? 'show' : '' }}" id="navCollapseServices">
                    <div class="nav-subgroup">
                        <a href="{{ route('admin.services.index') }}" class="nav-sublink {{ request()->routeIs('admin.services.index*') ? 'active' : '' }}" title="Services List">
                            <i class="bi bi-list-check"></i>
                            <span class="sublink-label">Services List</span>
                        </a>
                        <a href="{{ route('admin.services.create') }}" class="nav-sublink {{ request()->routeIs('admin.services.create') ? 'active' : '' }}" title="New Service">
                            <i class="bi bi-plus-circle text-success"></i>
                            <span class="sublink-label">New Service</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- 4. Land Survey & GIS -->
            <div class="nav-group {{ $groupSurveyActive ? 'active-group' : '' }}">
                <button class="nav-group-trigger {{ $groupSurveyActive ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#navCollapseSurvey" aria-expanded="{{ $groupSurveyActive ? 'true' : 'false' }}">
                    <div class="group-left">
                        <span class="group-icon"><i class="bi bi-compass"></i></span>
                        <span class="group-title">Land Survey</span>
                    </div>
                    <div class="group-right">
                        <i class="bi bi-chevron-down chevron-icon"></i>
                    </div>
                </button>
                <div class="collapse {{ $groupSurveyActive ? 'show' : '' }}" id="navCollapseSurvey">
                    <div class="nav-subgroup">
                        <a href="{{ route('survey.index') }}" class="nav-sublink {{ request()->routeIs('survey.index*') || request()->routeIs('survey.show') ? 'active' : '' }}" title="Surveys">
                            <i class="bi bi-bounding-box-circles"></i>
                            <span class="sublink-label">Surveys</span>
                        </a>
                        <a href="{{ route('survey.map') }}" class="nav-sublink {{ request()->routeIs('survey.map') ? 'active' : '' }}" title="GIS Map">
                            <i class="bi bi-map text-success"></i>
                            <span class="sublink-label">GIS Map</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- 5. Leads & CRM -->
            <div class="nav-group {{ $groupCrmActive ? 'active-group' : '' }}">
                <button class="nav-group-trigger {{ $groupCrmActive ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#navCollapseCRM" aria-expanded="{{ $groupCrmActive ? 'true' : 'false' }}">
                    <div class="group-left">
                        <span class="group-icon"><i class="bi bi-people"></i></span>
                        <span class="group-title">CRM & Leads</span>
                    </div>
                    <div class="group-right">
                        <i class="bi bi-chevron-down chevron-icon"></i>
                    </div>
                </button>
                <div class="collapse {{ $groupCrmActive ? 'show' : '' }}" id="navCollapseCRM">
                    <div class="nav-subgroup">
                        <a href="{{ route('crm.leads') }}" class="nav-sublink {{ request()->routeIs('crm.leads') ? 'active' : '' }}" title="Leads">
                            <i class="bi bi-funnel text-info"></i>
                            <span class="sublink-label">Leads</span>
                        </a>
                        <a href="{{ route('crm.customers') }}" class="nav-sublink {{ request()->routeIs('crm.customers') ? 'active' : '' }}" title="Customers">
                            <i class="bi bi-person-lines-fill"></i>
                            <span class="sublink-label">Customers</span>
                        </a>
                        <a href="{{ route('reservations.index') }}" class="nav-sublink {{ request()->routeIs('reservations.*') ? 'active' : '' }}" title="Reservations">
                            <i class="bi bi-bookmark-check text-warning"></i>
                            <span class="sublink-label">Reservations</span>
                        </a>
                        <a href="{{ route('deals.index') }}" class="nav-sublink {{ request()->routeIs('deals.*') ? 'active' : '' }}" title="Deals">
                            <i class="bi bi-briefcase"></i>
                            <span class="sublink-label">Deals</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- 6. Finance -->
            <div class="nav-group {{ $groupFinanceActive ? 'active-group' : '' }}">
                <button class="nav-group-trigger {{ $groupFinanceActive ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#navCollapseFinance" aria-expanded="{{ $groupFinanceActive ? 'true' : 'false' }}">
                    <div class="group-left">
                        <span class="group-icon"><i class="bi bi-credit-card-2-front"></i></span>
                        <span class="group-title">Finance</span>
                    </div>
                    <div class="group-right">
                        <i class="bi bi-chevron-down chevron-icon"></i>
                    </div>
                </button>
                <div class="collapse {{ $groupFinanceActive ? 'show' : '' }}" id="navCollapseFinance">
                    <div class="nav-subgroup">
                        <a href="{{ route('finance.invoices') }}" class="nav-sublink {{ request()->routeIs('finance.invoices*') ? 'active' : '' }}" title="Invoices">
                            <i class="bi bi-receipt"></i>
                            <span class="sublink-label">Invoices</span>
                        </a>
                        <a href="{{ route('finance.expenses') }}" class="nav-sublink {{ request()->routeIs('finance.expenses*') ? 'active' : '' }}" title="Expenses">
                            <i class="bi bi-wallet2 text-danger"></i>
                            <span class="sublink-label">Expenses</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- 7. Reports -->
            <a href="{{ route('reports.index') }}" class="nav-item-direct {{ request()->routeIs('reports.*') ? 'active' : '' }}" title="Reports">
                <span class="nav-icon"><i class="bi bi-bar-chart-line"></i></span>
                <span class="sublink-label">Reports</span>
            </a>

            <!-- 8. Users & Settings -->
            <div class="nav-group {{ $groupSettingsActive ? 'active-group' : '' }}">
                <button class="nav-group-trigger {{ $groupSettingsActive ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#navCollapseSettings" aria-expanded="{{ $groupSettingsActive ? 'true' : 'false' }}">
                    <div class="group-left">
                        <span class="group-icon"><i class="bi bi-gear-wide-connected"></i></span>
                        <span class="group-title">Settings</span>
                    </div>
                    <div class="group-right">
                        <i class="bi bi-chevron-down chevron-icon"></i>
                    </div>
                </button>
                <div class="collapse {{ $groupSettingsActive ? 'show' : '' }}" id="navCollapseSettings">
                    <div class="nav-subgroup">
                        <a href="{{ route('users.index') }}" class="nav-sublink {{ request()->routeIs('users.*') ? 'active' : '' }}" title="Users">
                            <i class="bi bi-people-fill text-info"></i>
                            <span class="sublink-label">Users</span>
                        </a>
                        <a href="{{ route('settings.rbac') }}" class="nav-sublink {{ request()->routeIs('settings.rbac') ? 'active' : '' }}" title="Roles & RBAC">
                            <i class="bi bi-shield-lock text-primary"></i>
                            <span class="sublink-label">Roles & RBAC</span>
                        </a>
                        <a href="{{ route('settings.index') }}" class="nav-sublink {{ request()->routeIs('settings.index') ? 'active' : '' }}" title="Config">
                            <i class="bi bi-sliders"></i>
                            <span class="sublink-label">System Config</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Website Link -->
            <a href="{{ route('public.home') }}" target="_blank" class="nav-item-direct mt-2" title="Website Link">
                <span class="nav-icon"><i class="bi bi-globe2 text-success"></i></span>
                <span class="sublink-label">View Website</span>
                <i class="bi bi-box-arrow-up-right ms-auto text-muted" style="font-size: 0.7rem;"></i>
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
                <button class="btn btn-light border d-flex align-items-center justify-content-center" id="sidebarToggleBtn" title="Toggle Navigation Sidebar" aria-label="Toggle Sidebar" style="width: 38px; height: 38px;">
                    <i class="bi bi-list fs-5"></i>
                </button>

                <!-- Branch Selector -->
                <form action="{{ route('settings.switch_branch') }}" method="POST" class="d-none d-md-flex align-items-center gap-2">
                    @csrf
                    <i class="bi bi-geo-alt-fill text-primary"></i>
                    <select name="branch_id" class="form-select form-select-sm border-0 bg-light" onchange="this.form.submit()" style="font-size: 0.85rem; font-weight: 500; min-width: 170px;">
                        <option value="all" {{ session('current_branch_id') === 'all' ? 'selected' : '' }}>🏢 {{ __('app.all_branches') }}</option>
                        @foreach(\App\Models\Branch::all() as $b)
                            <option value="{{ $b->id }}" {{ session('current_branch_id') == $b->id ? 'selected' : '' }}>
                                {{ $b->name }}
                            </option>
                        @endforeach
                    </select>
                </form>

                <!-- Global Action Search Input Trigger -->
                <div class="position-relative d-none d-md-block" style="width: 260px;">
                    <button class="btn btn-sm btn-light border w-100 text-start text-muted d-flex align-items-center justify-content-between rounded-pill px-3 py-1" data-bs-toggle="modal" data-bs-target="#adminActionSearchModal">
                        <span style="font-size: 0.82rem;"><i class="bi bi-search me-1 text-primary"></i> Search actions & settings...</span>
                        <kbd class="bg-white text-muted border px-1 rounded shadow-2xs" style="font-size: 0.65rem;">Ctrl+K</kbd>
                    </button>
                </div>
            </div>

            <!-- Right Controls: Date/Time, Online Status, Notifications & User -->
            <div class="d-flex align-items-center gap-3">
                <!-- System Live Date & Status -->
                <div class="d-none d-lg-flex align-items-center gap-2 text-muted small me-2">
                    <span id="topbarLiveClock" class="fw-semibold text-secondary" style="font-size: 0.82rem;">{{ date('n/j/Y, g:i:s A') }}</span>
                    <span class="badge bg-success px-2 py-1 rounded-pill fw-medium" style="font-size: 0.7rem;">Online</span>
                </div>

                <!-- Quick Nav Action Buttons -->
                <a href="{{ route('survey.map') }}" class="btn btn-sm btn-light border rounded-circle d-flex align-items-center justify-content-center p-0" style="width: 36px; height: 36px;" title="GIS Compass Viewer">
                    <i class="bi bi-compass text-primary"></i>
                </a>

                <!-- Notifications Bell -->
                <div class="dropdown">
                    <button class="btn btn-sm btn-light border rounded-circle position-relative d-flex align-items-center justify-content-center p-0" style="width: 36px; height: 36px;" data-bs-toggle="dropdown" title="Notifications">
                        <i class="bi bi-bell text-secondary"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-circle bg-danger p-1" style="font-size: 0.6rem;">4</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm p-2" style="width: 280px;">
                        <li class="dropdown-header fw-bold text-dark border-bottom pb-1 mb-2">Notifications</li>
                        <li><a class="dropdown-item small rounded mb-1" href="{{ route('crm.leads') }}"><i class="bi bi-funnel text-info me-2"></i> New Lead Inquiry Received</a></li>
                        <li><a class="dropdown-item small rounded mb-1" href="{{ route('survey.index') }}"><i class="bi bi-compass text-warning me-2"></i> Survey Beacon Update</a></li>
                        <li><a class="dropdown-item small rounded" href="{{ route('notifications.index') }}"><i class="bi bi-check-all text-success me-2"></i> View All Notifications</a></li>
                    </ul>
                </div>

                <!-- User Dropdown with Welcome Message -->
                <div class="dropdown">
                    <button class="btn btn-light d-flex align-items-center gap-2 rounded-pill p-1 pe-3 border" type="button" data-bs-toggle="dropdown">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 34px; height: 34px; font-size: 0.85rem;">
                            {{ substr(auth()->user()?->name ?? 'A', 0, 1) }}
                        </div>
                        <div class="text-start d-none d-md-block" style="line-height: 1.1;">
                            <small class="text-muted d-block" style="font-size: 0.65rem; font-weight: 600;">Welcome back</small>
                            <span class="fw-bold text-dark text-uppercase" style="font-size: 0.8rem;">{{ Str::limit(auth()->user()?->name ?? 'ADMINISTRATOR', 16) }}</span>
                        </div>
                        <i class="bi bi-chevron-down ms-1" style="font-size: 0.7rem;"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                        <li class="px-3 py-2 border-bottom">
                            <div class="fw-bold text-dark">{{ auth()->user()?->name }}</div>
                            <small class="text-muted d-block">{{ auth()->user()?->email }}</small>
                            <span class="badge bg-primary-subtle text-primary mt-1">{{ auth()->user()?->job_title ?? 'System Admin' }}</span>
                        </li>
                        <li><a class="dropdown-item py-2" href="{{ route('settings.index') }}"><i class="bi bi-sliders me-2"></i> {{ __('app.settings') }}</a></li>
                        <li><a class="dropdown-item py-2" href="{{ route('public.home') }}" target="_blank"><i class="bi bi-globe me-2"></i> View Public Website</a></li>
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

    <!-- Global Action & Settings Search Modal -->
    <div class="modal fade" id="adminActionSearchModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header border-0 pb-0 pt-3 px-3">
                    <div class="input-group input-group-lg border-bottom pb-2">
                        <span class="input-group-text bg-transparent border-0 text-primary fs-4"><i class="bi bi-search"></i></span>
                        <input type="text" id="actionSearchModalInput" class="form-control bg-transparent border-0 shadow-none fw-semibold" placeholder="Type an action, setting, or page... (e.g. logo, mpesa, add property, roles)" autofocus>
                        <button type="button" class="btn-close ms-2 align-self-center" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>
                <div class="modal-body p-3 overflow-auto" style="max-height: 440px;">
                    <div class="text-muted small px-2 mb-2 fw-bold text-uppercase" style="letter-spacing: 0.05em; font-size: 0.7rem;">
                        Quick Actions & Settings Registry
                    </div>
                    <div class="list-group list-group-flush" id="actionSearchResultsList"></div>
                </div>
                <div class="modal-footer bg-light border-0 py-2 px-3 d-flex justify-content-between align-items-center small text-muted">
                    <div><span class="badge bg-white text-dark border me-1">Ctrl+K</span> Quick Launcher</div>
                    <div>Press <kbd class="bg-white text-dark border px-1">ESC</kbd> to exit</div>
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
            // Action Search Registry & Filter Logic
            const adminActionsRegistry = [
                { title: "General Settings & Company Monogram", category: "System Configuration", url: "{{ route('settings.index') }}", icon: "bi-sliders", desc: "Update company name, tagline, phone, email, and monogram" },
                { title: "Upload Logo, Favicon & Brand Colors", category: "Branding", url: "{{ route('settings.index') }}#branding", icon: "bi-palette", desc: "Configure site logos, favicon, primary/secondary branding colors" },
                { title: "Payment Gateways & Mobile Money (M-Pesa, Tigo Pesa)", category: "Finance", url: "{{ route('settings.index') }}#payments", icon: "bi-credit-card-2-front", desc: "Configure mobile money integration and gateway credentials" },
                { title: "SMS & Email Communications Setup", category: "Communications", url: "{{ route('settings.index') }}#communications", icon: "bi-envelope-paper", desc: "Set up Twilio, Beem SMS, and SMTP email parameters" },
                { title: "Landing Page & Public Website Settings", category: "Website", url: "{{ route('settings.index') }}#landing", icon: "bi-layout-text-window", desc: "Manage hero titles, subtitles, banner images, and landing content" },
                { title: "Roles & RBAC Access Matrix", category: "Security", url: "{{ route('settings.rbac') }}", icon: "bi-shield-lock", desc: "Manage staff roles, permissions, and module access matrix" },
                { title: "User & Staff Directory", category: "User Management", url: "{{ route('users.index') }}", icon: "bi-people-fill", desc: "Create staff accounts, edit user roles, activate or ban accounts" },
                { title: "Add New Property Listing", category: "Properties", url: "{{ route('admin.properties.create') }}", icon: "bi-plus-circle", desc: "Publish a new house, apartment, or land plot listing" },
                { title: "View All Properties", category: "Properties", url: "{{ route('admin.properties.index') }}", icon: "bi-building", desc: "Filter, search, edit, or archive property listings" },
                { title: "Create Land Project / Estate Subdivision", category: "Land Projects", url: "{{ route('admin.projects.create') }}", icon: "bi-diagram-3-fill", desc: "Create master-planned land estate project & shapefile maps" },
                { title: "Manage Land Projects", category: "Land Projects", url: "{{ route('admin.projects.index') }}", icon: "bi-map-fill", desc: "Browse land projects, plot inventory, and GIS shapefiles" },
                { title: "Land Survey Projects & Beacon Relocation", category: "Land Survey", url: "{{ route('survey.index') }}", icon: "bi-compass", desc: "Manage cadastral boundary surveys, beaconing, and deed plan status" },
                { title: "Open Interactive GIS Parcel Map", category: "GIS Mapping", url: "{{ route('survey.map') }}", icon: "bi-geo-alt-fill", desc: "Spatial visualizer for surveyed plot boundaries and coordinates" },
                { title: "Manage Property Services", category: "Services", url: "{{ route('admin.services.index') }}", icon: "bi-list-check", desc: "View and update property valuation and survey services" },
                { title: "Add New Service Offering", category: "Services", url: "{{ route('admin.services.create') }}", icon: "bi-bag-plus", desc: "Add a new public service card to the services catalog" },
                { title: "Lead Inquiries & Customer Pipeline", category: "CRM", url: "{{ route('crm.leads') }}", icon: "bi-funnel-fill", desc: "View website buyer inquiries, contact requests, and deal pipeline" },
                { title: "Customer & Client Directory", category: "CRM", url: "{{ route('crm.customers') }}", icon: "bi-person-lines-fill", desc: "Manage buyer and tenant contacts, phone numbers, and histories" },
                { title: "Property Reservations", category: "CRM", url: "{{ route('reservations.index') }}", icon: "bi-bookmark-check", desc: "Track reserved land plots and client deposits" },
                { title: "Deals & Sales Contracts", category: "Commercial", url: "{{ route('deals.index') }}", icon: "bi-briefcase-fill", desc: "Manage active land sale contracts and deed transfers" },
                { title: "Invoices & Payment Receipts", category: "Finance", url: "{{ route('finance.invoices') }}", icon: "bi-receipt-cutoff", desc: "Create, view, and print customer tax invoices & receipts" },
                { title: "Operating Expenses Record", category: "Finance", url: "{{ route('finance.expenses') }}", icon: "bi-wallet2", desc: "Track office operational expenses and project budgets" },
                { title: "Reports & Analytics Center", category: "Analytics", url: "{{ route('reports.index') }}", icon: "bi-bar-chart-line-fill", desc: "View inventory valuation, sales reports, and cadastral audits" },
                { title: "AI Assistant Smart Studio", category: "AI Tools", url: "{{ route('ai.chat') }}", icon: "bi-stars", desc: "Ask AI to generate valuations, market insights, or contracts" },
                { title: "View Public Website Homepage", category: "Public Portal", url: "{{ route('public.home') }}", icon: "bi-globe2", desc: "Preview the live public website and property portal" }
            ];

            function renderActionResults(query) {
                const resultsContainer = $('#actionSearchResultsList');
                resultsContainer.empty();
                const q = query.toLowerCase().trim();

                const filtered = adminActionsRegistry.filter(item => 
                    item.title.toLowerCase().includes(q) || 
                    item.category.toLowerCase().includes(q) || 
                    item.desc.toLowerCase().includes(q)
                );

                if (filtered.length === 0) {
                    resultsContainer.append(`
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-search fs-3 d-block mb-2 text-secondary"></i>
                            <div>No matching action or setting found for "<strong>${query}</strong>"</div>
                            <small class="d-block mt-1">Try keywords like <em>logo, mpesa, survey, roles, users, properties</em></small>
                        </div>
                    `);
                    return;
                }

                filtered.forEach(item => {
                    resultsContainer.append(`
                        <a href="${item.url}" class="list-group-item list-group-item-action border-0 rounded-3 mb-1 p-3 d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-3 bg-primary-subtle text-primary p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px;">
                                    <i class="bi ${item.icon} fs-5"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark mb-0">${item.title}</div>
                                    <small class="text-muted d-block" style="font-size: 0.78rem;">${item.desc}</small>
                                </div>
                            </div>
                            <span class="badge bg-light text-dark border flex-shrink-0 ms-2" style="font-size: 0.7rem;">${item.category}</span>
                        </a>
                    `);
                });
            }

            // Keyboard shortcut Ctrl+K or Cmd+K
            $(document).on('keydown', function(e) {
                if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
                    e.preventDefault();
                    $('#adminActionSearchModal').modal('show');
                }
            });

            $('#adminActionSearchModal').on('shown.bs.modal', function () {
                $('#actionSearchModalInput').focus().select();
                renderActionResults('');
            });

            $('#actionSearchModalInput').on('input', function() {
                renderActionResults($(this).val());
            });

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
