<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('app.platform_name')) - {{ config('app.name') }}</title>

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

    @php
        $org = current_organization();
        $branding = $org?->branding;
        $primaryColor = $branding?->primary_color ?? '#0f52ba';
        $secondaryColor = $branding?->secondary_color ?? '#495057';
        $accentColor = $branding?->accent_color ?? '#00a86b';
    @endphp

    <style>
        :root {
            --rrep-primary: {{ $primaryColor }};
            --rrep-secondary: {{ $secondaryColor }};
            --rrep-accent: {{ $accentColor }};
            --rrep-sidebar-bg: #121929;
            --rrep-sidebar-hover: #1e293b;
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
            width: 270px;
            min-height: 100vh;
            background-color: var(--rrep-sidebar-bg);
            color: #94a3b8;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            transition: all 0.3s ease;
            box-shadow: 2px 0 12px rgba(0, 0, 0, 0.08);
            overflow-y: auto;
        }

        .sidebar .brand-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .sidebar .nav-link {
            color: #94a3b8;
            padding: 0.6rem 1.25rem;
            font-size: 0.865rem;
            font-weight: 500;
            border-radius: 0.375rem;
            margin: 0.1rem 0.75rem;
            display: flex;
            align-items: center;
            transition: all 0.2s ease;
        }

        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: #ffffff;
            background-color: var(--rrep-sidebar-hover);
        }

        .sidebar .nav-link.active {
            background-color: var(--rrep-primary);
            color: #ffffff;
            font-weight: 600;
        }

        .sidebar .nav-link i {
            font-size: 1.05rem;
            width: 1.65rem;
            margin-right: 0.4rem;
        }

        .sidebar-section-title {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            padding: 0.85rem 1.5rem 0.25rem;
        }

        /* Main Content Wrapper */
        .main-wrapper {
            margin-left: 270px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: margin 0.3s ease;
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

        .btn-primary:hover {
            background-color: #0b3d8c;
            border-color: #0b3d8c;
        }

        .btn-accent {
            background-color: var(--rrep-accent);
            border-color: var(--rrep-accent);
            color: #ffffff;
        }

        .badge-status-available { background-color: #dcfce7; color: #15803d; }
        .badge-status-reserved { background-color: #fef9c3; color: #a16207; }
        .badge-status-sold { background-color: #fee2e2; color: #b91c1c; }
        .badge-status-leased { background-color: #e0e7ff; color: #4338ca; }
        .badge-status-undercontract { background-color: #f3e8ff; color: #7e22ce; }

        @media (max-width: 991.98px) {
            .sidebar { margin-left: -270px; }
            .sidebar.show { margin-left: 0; }
            .main-wrapper { margin-left: 0; }
        }
    </style>
    @yield('styles')
</head>
<body>

    <!-- Sidebar Navigation -->
    <aside class="sidebar" id="appSidebar">
        <div class="brand-header d-flex align-items-center justify-content-between">
            <a href="{{ route('dashboard') }}" class="text-decoration-none d-flex align-items-center gap-2">
                <div class="rounded-3 bg-primary text-white d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; font-weight: 800; font-size: 1.2rem;">
                    R
                </div>
                <div>
                    <div class="text-white fw-bold brand-font" style="font-size: 1.05rem; line-height: 1.1;">{{ current_organization()?->name ?? 'RehoSpace' }}</div>
                    <span class="badge bg-secondary" style="font-size: 0.65rem;">RREP Enterprise v1.0</span>
                </div>
            </a>
            <button class="btn btn-sm text-white-50 d-lg-none" id="sidebarCloseBtn"><i class="bi bi-x-lg"></i></button>
        </div>

        <div class="py-2">
            <div class="sidebar-section-title">{{ __('app.dashboard') }}</div>
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i> {{ __('app.dashboard') }}
            </a>

            <!-- Properties & Assets (BM-001, BM-004, BM-010) -->
            <div class="sidebar-section-title">{{ __('app.properties') }} & Assets</div>
            <a href="{{ route('properties.index') }}" class="nav-link {{ request()->routeIs('properties.index*') || request()->routeIs('properties.show') ? 'active' : '' }}">
                <i class="bi bi-building"></i> {{ __('app.property_list') }}
            </a>
            <a href="{{ route('properties.create') }}" class="nav-link {{ request()->routeIs('properties.create') ? 'active' : '' }}">
                <i class="bi bi-plus-circle"></i> {{ __('app.add_property') }}
            </a>
            <a href="{{ route('documents.index') }}" class="nav-link {{ request()->routeIs('documents.*') ? 'active' : '' }}">
                <i class="bi bi-folder-check"></i> EDMS Document Vault
            </a>

            <!-- CRM & Sales (BM-003, BM-006, BM-007, BM-009) -->
            <div class="sidebar-section-title">{{ __('app.crm_sales') }}</div>
            <a href="{{ route('crm.leads') }}" class="nav-link {{ request()->routeIs('crm.leads') ? 'active' : '' }}">
                <i class="bi bi-funnel"></i> {{ __('app.leads') }} Pipeline
            </a>
            <a href="{{ route('crm.customers') }}" class="nav-link {{ request()->routeIs('crm.customers') ? 'active' : '' }}">
                <i class="bi bi-people"></i> {{ __('app.customers') }}
            </a>
            <a href="{{ route('reservations.index') }}" class="nav-link {{ request()->routeIs('reservations.*') ? 'active' : '' }}">
                <i class="bi bi-bookmark-check"></i> {{ __('app.reservations') }}
            </a>
            <a href="{{ route('appointments.index') }}" class="nav-link {{ request()->routeIs('appointments.*') ? 'active' : '' }}">
                <i class="bi bi-calendar-event"></i> {{ __('app.appointments') }}
            </a>
            <a href="{{ route('deals.index') }}" class="nav-link {{ request()->routeIs('deals.*') ? 'active' : '' }}">
                <i class="bi bi-briefcase"></i> {{ __('app.deals') }} & Contracts
            </a>

            <!-- Finance & Billing (BM-011) -->
            <div class="sidebar-section-title">{{ __('app.finance') }} & Billing</div>
            <a href="{{ route('finance.invoices') }}" class="nav-link {{ request()->routeIs('finance.invoices*') ? 'active' : '' }}">
                <i class="bi bi-receipt"></i> Tax Invoices & Receipts
            </a>
            <a href="{{ route('finance.expenses') }}" class="nav-link {{ request()->routeIs('finance.expenses*') ? 'active' : '' }}">
                <i class="bi bi-cash-stack"></i> Operating Expenses
            </a>

            <!-- Land Survey & GIS (BM-008) -->
            <div class="sidebar-section-title">{{ __('app.survey_gis') }}</div>
            <a href="{{ route('survey.index') }}" class="nav-link {{ request()->routeIs('survey.index*') || request()->routeIs('survey.show') ? 'active' : '' }}">
                <i class="bi bi-geo-alt"></i> {{ __('app.survey_projects') }}
            </a>
            <a href="{{ route('survey.map') }}" class="nav-link {{ request()->routeIs('survey.map') ? 'active' : '' }}">
                <i class="bi bi-map"></i> {{ __('app.gis_map_viewer') }}
            </a>

            <!-- Reports Center & BI (BM-015) -->
            <div class="sidebar-section-title">Intelligence & Reports</div>
            <a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-bar-graph"></i> Reports Center (BM-015)
            </a>
            <a href="{{ route('ai.chat') }}" class="nav-link {{ request()->routeIs('ai.*') ? 'active' : '' }}">
                <i class="bi bi-stars text-warning"></i> AI Smart Studio (BM-020)
            </a>
            <a href="{{ route('marketing.index') }}" class="nav-link {{ request()->routeIs('marketing.*') ? 'active' : '' }}">
                <i class="bi bi-megaphone"></i> Campaigns & Marketing
            </a>

            <!-- Advanced Enterprise Modules (BM-013, BM-014, BM-019) -->
            <div class="sidebar-section-title">Portals & Governance</div>
            <a href="{{ route('portals.client') }}" class="nav-link {{ request()->routeIs('portals.client') ? 'active' : '' }}">
                <i class="bi bi-person-workspace"></i> Client Portal (BM-013)
            </a>
            <a href="{{ route('portals.owner') }}" class="nav-link {{ request()->routeIs('portals.owner') ? 'active' : '' }}">
                <i class="bi bi-building-check"></i> Owner Portal (BM-013)
            </a>
            <a href="{{ route('workflows.index') }}" class="nav-link {{ request()->routeIs('workflows.*') ? 'active' : '' }}">
                <i class="bi bi-diagram-3"></i> Approval Workflows (BM-014)
            </a>
            <a href="{{ route('compliance.kyc') }}" class="nav-link {{ request()->routeIs('compliance.*') ? 'active' : '' }}">
                <i class="bi bi-shield-check"></i> KYC & Compliance (BM-019)
            </a>

            <!-- Public Marketplace -->
            <div class="sidebar-section-title">{{ __('app.marketplace') }}</div>
            <a href="{{ route('marketplace.index') }}" target="_blank" class="nav-link">
                <i class="bi bi-globe"></i> Live Marketplace <i class="bi bi-box-arrow-up-right ms-auto" style="font-size: 0.75rem;"></i>
            </a>

            <!-- System Administration (FM-001..FM-009, BM-018) -->
            <div class="sidebar-section-title">{{ __('app.settings') }} & Admin</div>
            <a href="{{ route('settings.index') }}" class="nav-link {{ request()->routeIs('settings.index') ? 'active' : '' }}">
                <i class="bi bi-gear"></i> System Settings (FM-004)
            </a>
            <a href="{{ route('settings.rbac') }}" class="nav-link {{ request()->routeIs('settings.rbac') ? 'active' : '' }}">
                <i class="bi bi-shield-lock"></i> Role Matrix (FM-003)
            </a>
            <a href="{{ route('notifications.index') }}" class="nav-link {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
                <i class="bi bi-bell"></i> Notifications (FM-008)
            </a>
        </div>
    </aside>

    <!-- Main Content Wrapper -->
    <div class="main-wrapper">
        <!-- Topbar -->
        <header class="topbar">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-light d-lg-none" id="sidebarToggleBtn">
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
            &copy; {{ date('Y') }} {{ current_organization()?->name ?? 'RehoSpace' }}. Powered by <strong>RehoSpace Real Estate Platform (RREP) Enterprise</strong>.
        </footer>
    </div>

    <!-- AI Assistant Offcanvas Drawer -->
    <div class="offcanvas offcanvas-end shadow-lg" tabindex="-1" id="aiOffcanvas" style="width: 420px;">
        <div class="offcanvas-header bg-dark text-white">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-stars text-warning fs-4"></i>
                <h5 class="offcanvas-title brand-font text-white mb-0">RehoSpace AI Assistant</h5>
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
                        Hello! I am your <strong>RehoSpace Intelligent Assistant</strong>. How can I assist you with properties, valuation estimates, GIS coordinates, or CRM deals today?
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
            $('#sidebarToggleBtn').on('click', function () {
                $('#appSidebar').toggleClass('show');
            });
            $('#sidebarCloseBtn').on('click', function () {
                $('#appSidebar').removeClass('show');
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
