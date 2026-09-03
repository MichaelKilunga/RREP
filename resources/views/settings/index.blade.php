@extends('layouts.app')

@section('title', __('app.settings'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="brand-font mb-1">{{ __('app.settings') }} & System Administration</h3>
        <p class="text-muted small mb-0">Dynamic gateway integrations, SMS templates, module feature flags, branding tokens, and social hooks</p>
    </div>
</div>

<!-- Environment Status Summary Card -->
<div class="card border-0 shadow-sm mb-4 {{ is_production_mode() ? 'bg-success-subtle border-start border-4 border-success' : 'bg-warning-subtle border-start border-4 border-warning' }}">
    <div class="card-body p-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle p-2 {{ is_production_mode() ? 'bg-success text-white' : 'bg-warning text-dark' }} d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                    <i class="bi {{ is_production_mode() ? 'bi-shield-check-fill' : 'bi-laptop' }} fs-5"></i>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2">
                        <h6 class="mb-0 fw-bold">System Status: <span class="{{ is_production_mode() ? 'text-success' : 'text-dark' }}">{{ is_production_mode() ? 'PRODUCTION (LIVE)' : 'LOCAL (DEVELOPMENT)' }}</span></h6>
                        <span class="badge {{ is_production_mode() ? 'bg-success' : 'bg-warning text-dark' }}">{{ is_production_mode() ? 'LIVE' : 'DEV' }}</span>
                    </div>
                    <p class="text-muted small mb-0">
                        @if(is_production_mode())
                            Production mode active. Debugging is disabled, Quick 1-Click Role Login is suppressed, and unneeded seed data is removed.
                        @else
                            Development mode active. Detailed debugging is enabled and Quick 1-Click Role Login is active on the login screen.
                        @endif
                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-light text-dark border">Debug: {{ $isDebug ? 'ENABLED' : 'DISABLED' }}</span>
                <span class="badge bg-light text-dark border">1-Click Login: {{ is_production_mode() ? 'OFF' : 'ON' }}</span>
                <span class="badge bg-light text-dark border">Demo Records: {{ $demoStats['total'] }}</span>
            </div>
        </div>
    </div>
</div>

<!-- Nav Tabs for Settings -->
<ul class="nav nav-pills mb-4" id="settingsTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active fw-semibold" id="environment-tab" data-bs-toggle="pill" data-bs-target="#environment" type="button" role="tab">
            <i class="bi bi-hdd-network-fill me-1"></i> Environment & Lifecycle
            @if(is_production_mode())
                <span class="badge bg-success ms-1">LIVE</span>
            @else
                <span class="badge bg-warning text-dark ms-1">LOCAL</span>
            @endif
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-semibold" id="pushsms-tab" data-bs-toggle="pill" data-bs-target="#pushsms" type="button" role="tab">
            <i class="bi bi-chat-dots-fill me-1"></i> PushSMS Gateway (RehoPush)
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-semibold" id="toggles-tab" data-bs-toggle="pill" data-bs-target="#toggles" type="button" role="tab">
            <i class="bi bi-toggles me-1"></i> Feature Toggle Matrix (SRS 3.4)
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-semibold" id="social-tab" data-bs-toggle="pill" data-bs-target="#social" type="button" role="tab">
            <i class="bi bi-share me-1"></i> Contact & Social Media Hooks
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-semibold" id="branding-tab" data-bs-toggle="pill" data-bs-target="#branding" type="button" role="tab">
            <i class="bi bi-palette-fill me-1"></i> Branding & Identity
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-semibold text-primary" id="landing-tab" data-bs-toggle="pill" data-bs-target="#landing-cms" type="button" role="tab">
            <i class="bi bi-globe2 me-1"></i> Landing Page & Public CMS
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-semibold" id="branches-tab" data-bs-toggle="pill" data-bs-target="#branches" type="button" role="tab">
            <i class="bi bi-building me-1"></i> Branches & Audit Trail
        </button>
    </li>
</ul>

<div class="tab-content" id="settingsTabContent">
    <!-- TAB 0: Environment & Deployment Lifecycle -->
    <div class="tab-pane fade show active" id="environment" role="tabpanel">
        <div class="row g-4 mb-4">
            <!-- Production (Live) Card -->
            <div class="col-lg-6">
                <div class="card shadow-sm h-100 {{ is_production_mode() ? 'border-success border-2' : '' }}">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <span class="p-1 px-2 rounded bg-success text-white small fw-bold">LIVE</span>
                            <h5 class="brand-font mb-0 text-success">Production Environment</h5>
                        </div>
                        @if(is_production_mode())
                            <span class="badge bg-success"><i class="bi bi-check2-circle me-1"></i>Active Mode</span>
                        @endif
                    </div>
                    <div class="card-body d-flex flex-column">
                        <p class="text-muted small mb-3">
                            Brings the platform into live production deployment. When active, all demo testing helpers are strictly disabled for security.
                        </p>

                        <div class="bg-light rounded p-3 mb-3 small">
                            <h6 class="fw-bold small text-dark mb-2"><i class="bi bi-shield-shaded text-success me-1"></i> Production Enforcements:</h6>
                            <ul class="list-unstyled mb-0 d-flex flex-column gap-1 text-muted">
                                <li><i class="bi bi-check-circle-fill text-success me-2"></i><strong>Quick 1-Click Role Login:</strong> Completely removed from the login screen.</li>
                                <li><i class="bi bi-check-circle-fill text-success me-2"></i><strong>Credentials Pre-fill:</strong> Removed from login inputs.</li>
                                <li><i class="bi bi-check-circle-fill text-success me-2"></i><strong>Debug & Stack Traces:</strong> Suppressed (<code>APP_DEBUG=false</code>).</li>
                                <li><i class="bi bi-check-circle-fill text-success me-2"></i><strong>Demo & Mock Seeded Data:</strong> Automatically wiped clean.</li>
                                <li><i class="bi bi-check-circle-fill text-success me-2"></i><strong>Core Safeguards:</strong> Super Admin, Roles, Permissions, Modules & Settings remain intact.</li>
                            </ul>
                        </div>

                        <form action="{{ route('settings.environment') }}" method="POST" class="mt-auto" onsubmit="return confirm('Confirm switching to LIVE PRODUCTION mode? This will update system configuration and can purge demo data.');">
                            @csrf
                            <input type="hidden" name="environment" value="production">

                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" name="purge_demo_data" value="1" id="purgeDemoCheck" checked>
                                <label class="form-check-label small fw-semibold" for="purgeDemoCheck">
                                    Purge unnecessary seeded demo data (Properties, Leads, Deals, Mock accounts)
                                </label>
                            </div>

                            @if(is_production_mode())
                                <button type="submit" class="btn btn-outline-success w-100 fw-semibold">
                                    <i class="bi bi-arrow-repeat me-1"></i> Re-apply Production Mode & Clean Caches
                                </button>
                            @else
                                <button type="submit" class="btn btn-success w-100 fw-bold py-2 shadow-sm">
                                    <i class="bi bi-cloud-arrow-up-fill me-1"></i> Go Live / Bring to Production
                                </button>
                            @endif
                        </form>
                    </div>
                </div>
            </div>

            <!-- Local (Development) Card -->
            <div class="col-lg-6">
                <div class="card shadow-sm h-100 {{ is_local_mode() ? 'border-warning border-2' : '' }}">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <span class="p-1 px-2 rounded bg-warning text-dark small fw-bold">LOCAL</span>
                            <h5 class="brand-font mb-0 text-warning-emphasis">Development Environment</h5>
                        </div>
                        @if(is_local_mode())
                            <span class="badge bg-warning text-dark"><i class="bi bi-check2-circle me-1"></i>Active Mode</span>
                        @endif
                    </div>
                    <div class="card-body d-flex flex-column">
                        <p class="text-muted small mb-3">
                            Switches the platform to local development. Enables rapid role testing, developer debugging, and demo inspection tools.
                        </p>

                        <div class="bg-light rounded p-3 mb-3 small">
                            <h6 class="fw-bold small text-dark mb-2"><i class="bi bi-code-slash text-warning me-1"></i> Development Capabilities:</h6>
                            <ul class="list-unstyled mb-0 d-flex flex-column gap-1 text-muted">
                                <li><i class="bi bi-check-circle-fill text-warning me-2"></i><strong>Quick 1-Click Role Login:</strong> Enabled on <code>/login</code> for fast testing.</li>
                                <li><i class="bi bi-check-circle-fill text-warning me-2"></i><strong>Pre-fill Helper:</strong> Instant Super Admin login autofill.</li>
                                <li><i class="bi bi-check-circle-fill text-warning me-2"></i><strong>Detailed Error Reports:</strong> Active (<code>APP_DEBUG=true</code>).</li>
                                <li><i class="bi bi-check-circle-fill text-warning me-2"></i><strong>Demo Data Seeding:</strong> Available on-demand to test workflows.</li>
                            </ul>
                        </div>

                        <form action="{{ route('settings.environment') }}" method="POST" class="mt-auto" onsubmit="return confirm('Switch system environment to LOCAL DEVELOPMENT mode?');">
                            @csrf
                            <input type="hidden" name="environment" value="local">

                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" name="reseed_demo_data" value="1" id="reseedDemoCheck">
                                <label class="form-check-label small fw-semibold" for="reseedDemoCheck">
                                    Re-seed mock sample data (if current database is empty)
                                </label>
                            </div>

                            @if(is_local_mode())
                                <button type="submit" class="btn btn-outline-warning text-dark w-100 fw-semibold">
                                    <i class="bi bi-arrow-repeat me-1"></i> Re-apply Local Mode & Clear Caches
                                </button>
                            @else
                                <button type="submit" class="btn btn-warning text-dark w-100 fw-bold py-2 shadow-sm">
                                    <i class="bi bi-laptop me-1"></i> Switch to Local / Development
                                </button>
                            @endif
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Seeded Demo Data Management Section -->
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="brand-font mb-0">Seeded Demo Data Status & Controls</h5>
                    <small class="text-muted">Inspect and manage mock demonstration data in the system</small>
                </div>
                <span class="badge {{ $demoStats['total'] > 0 ? 'bg-danger-subtle text-danger border border-danger-subtle' : 'bg-success-subtle text-success border border-success-subtle' }}">
                    {{ $demoStats['total'] }} Demo Items in Database
                </span>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-4 col-lg-2">
                        <div class="p-3 bg-light rounded text-center border">
                            <div class="text-muted small fw-semibold">Properties</div>
                            <div class="fs-4 fw-bold text-dark">{{ $demoStats['properties'] }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2">
                        <div class="p-3 bg-light rounded text-center border">
                            <div class="text-muted small fw-semibold">Customers</div>
                            <div class="fs-4 fw-bold text-dark">{{ $demoStats['customers'] }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2">
                        <div class="p-3 bg-light rounded text-center border">
                            <div class="text-muted small fw-semibold">CRM Leads</div>
                            <div class="fs-4 fw-bold text-dark">{{ $demoStats['leads'] }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2">
                        <div class="p-3 bg-light rounded text-center border">
                            <div class="text-muted small fw-semibold">Sales Deals</div>
                            <div class="fs-4 fw-bold text-dark">{{ $demoStats['deals'] }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2">
                        <div class="p-3 bg-light rounded text-center border">
                            <div class="text-muted small fw-semibold">Invoices</div>
                            <div class="fs-4 fw-bold text-dark">{{ $demoStats['invoices'] }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2">
                        <div class="p-3 bg-light rounded text-center border">
                            <div class="text-muted small fw-semibold">Demo Logins</div>
                            <div class="fs-4 fw-bold text-dark">{{ $demoStats['demo_users'] }}</div>
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-wrap justify-content-between align-items-center pt-3 border-top gap-2">
                    <div>
                        <span class="small text-muted"><i class="bi bi-info-circle me-1"></i><strong>Safety Lock:</strong> The Super Admin account (<code>{{ auth()->user()?->email ?? 'admin' }}</code>), organization, branches, role permissions, and licensed modules are always protected from deletion.</span>
                    </div>
                    <div class="d-flex gap-2">
                        @if($demoStats['total'] > 0)
                            <form action="{{ route('settings.purge_demo_data') }}" method="POST" onsubmit="return confirm('Are you sure you want to permanently remove all seeded demo data? This cannot be undone.');">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger btn-sm fw-semibold">
                                    <i class="bi bi-trash3-fill me-1"></i> Purge Seeded Demo Data Now
                                </button>
                            </form>
                        @endif
                        @if(is_local_mode())
                            <form action="{{ route('settings.seed_demo_data') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-outline-secondary btn-sm fw-semibold">
                                    <i class="bi bi-database-fill-add me-1"></i> Re-Seed Demo Data
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 1: PushSMS Gateway -->
    <div class="tab-pane fade" id="pushsms" role="tabpanel">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="brand-font mb-0">PushSMS Gateway Configuration (RehoPush / Skypush)</h5>
                        <span class="badge bg-primary">send_sms.md Compliant</span>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('settings.pushsms') }}" method="POST">
                            @csrf
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">API Base URL</label>
                                    <input type="url" name="pushsms_base_url" class="form-control" value="{{ setting('pushsms_base_url', config('services.pushsms.url', 'https://pushsms.rehospace.com')) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">API Key (X-API-KEY Header)</label>
                                    <input type="password" name="pushsms_api_key" class="form-control" placeholder="••••••••••••••••" value="{{ setting('pushsms_api_key', config('services.pushsms.api_key')) }}">
                                </div>
                            </div>
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Alphanumeric Sender ID (Max 11 chars)</label>
                                    <input type="text" name="pushsms_sender_id" class="form-control text-uppercase" maxlength="11" value="{{ setting('pushsms_sender_id', config('services.pushsms.sender', 'REALESTATE')) }}" required>
                                    <small class="text-muted">Must be passed as both <code>sender</code> and <code>sender_id</code>.</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Client Application Identifier</label>
                                    <input type="text" name="pushsms_client_app" class="form-control" value="{{ setting('pushsms_client_app', config('services.pushsms.client_app', 'RREP')) }}" required>
                                </div>
                            </div>
                            <div class="form-check form-switch mb-4">
                                <input class="form-check-input" type="checkbox" name="sms_enabled" value="1" id="smsEnabledSwitch" @checked(setting('sms_enabled', '1') === '1')>
                                <label class="form-check-label fw-semibold" for="smsEnabledSwitch">Enable Global Outbound Push SMS Dispatch</label>
                            </div>

                            <hr class="my-4">

                            <h6 class="fw-bold mb-3">SRS Automated SMS Event Templates</h6>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Event A Template (Plot Booking / Reservation Confirmation)</label>
                                <textarea name="sms_template_event_a" class="form-control" rows="2">{{ setting('sms_template_event_a', 'Habari {customer_name}, ombi lako la kiwanja {plot_code} limepokelewa kikamilifu! Ref: {ref_number}. Tafadhali tembelea akaunti yako kuona ankara/maelezo. - {company_name}') }}</textarea>
                                <small class="text-muted">Placeholders: {customer_name}, {plot_code}, {ref_number}, {amount}, {company_name}</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Event B Template (Land Survey & Site Analysis Completion - SRS Mandatory)</label>
                                <textarea name="sms_template_event_b" class="form-control" rows="2">{{ setting('sms_template_event_b', 'Ukaguzi na uchambuzi wa site yako umekamilika, tafadhari angalia kwenye account yako kuona nyaraka zako.') }}</textarea>
                                <small class="text-muted">Placeholders: {customer_name}, {survey_code}</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Invoice Issued SMS Template</label>
                                <textarea name="sms_template_invoice_issued" class="form-control" rows="2">{{ setting('sms_template_invoice_issued', 'Habari {customer_name}, ankara mpya #{invoice_number} ya kiasi cha {total_amount} imeandaliwa. Tarehe ya mwisho: {due_date}. - {company_name}') }}</textarea>
                            </div>

                            <button type="submit" class="btn btn-primary fw-semibold">
                                <i class="bi bi-save me-1"></i> Save PushSMS Gateway Settings
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Live Balance & Status Card -->
            <div class="col-lg-4">
                <div class="card shadow-sm mb-4 border-0 bg-primary text-white">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-white-50 small fw-semibold text-uppercase">Live PushSMS Units</span>
                            <span class="badge bg-white text-primary">Pre-loaded Pool</span>
                        </div>
                        <div class="h2 fw-bold mb-1" id="liveBalanceText">
                            @if(isset($smsBalance['status']) && $smsBalance['status'] === 'success' && is_numeric($smsBalance['balance']))
                                {{ number_format((float) $smsBalance['balance']) }} <span class="fs-6 fw-normal">Units</span>
                            @else
                                {{ $smsBalance['balance'] ?? 'Checking...' }}
                            @endif
                        </div>
                        <div class="small text-white-50">Sender ID: <strong>{{ setting('pushsms_sender_id', config('services.pushsms.sender', 'AVENIX LTD')) }}</strong></div>

                        <hr class="border-white-50 my-3">

                        <button type="button" class="btn btn-light btn-sm w-100 fw-semibold" onclick="refreshSmsBalance()">
                            <i class="bi bi-arrow-repeat me-1"></i> Refresh Live Balance
                        </button>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h6 class="brand-font mb-0">Integration Specifications</h6>
                    </div>
                    <div class="card-body small">
                        <ul class="list-unstyled mb-0 d-flex flex-column gap-2">
                            <li><i class="bi bi-check-circle-fill text-success me-2"></i><strong>Endpoint:</strong> <code>/api/v1/send</code> (POST)</li>
                            <li><i class="bi bi-check-circle-fill text-success me-2"></i><strong>Headers:</strong> <code>application/json</code>, <code>X-API-KEY</code></li>
                            <li><i class="bi bi-check-circle-fill text-success me-2"></i><strong>Dual Params:</strong> <code>sender</code> & <code>sender_id</code> simultaneously</li>
                            <li><i class="bi bi-check-circle-fill text-success me-2"></i><strong>Normalization:</strong> 4-step E.164 (<code>255...</code>)</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 2: Feature Toggle Matrix (SRS Section 3.4) -->
    <div class="tab-pane fade" id="toggles" role="tabpanel">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="brand-font mb-0">Dynamic Feature Toggle System (SRS Section 3.4)</h5>
                <p class="text-muted small mb-0">Activate or deactivate platform modules on-the-fly without requiring source code modifications</p>
            </div>
            <div class="card-body">
                <form action="{{ route('settings.toggles') }}" method="POST">
                    @csrf
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="card border p-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="property_owner_submissions" value="1" id="toggle_owners" @checked($featureToggles['property_owner_submissions'])>
                                    <label class="form-check-label fw-bold text-dark" for="toggle_owners">
                                        1. Property Owner Submissions
                                    </label>
                                </div>
                                <small class="text-muted ps-4 d-block mt-1">Allows external landlords & plot owners to submit properties for sale/lease in their portal.</small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card border p-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="marketplace_functions" value="1" id="toggle_marketplace" @checked($featureToggles['marketplace_functions'])>
                                    <label class="form-check-label fw-bold text-dark" for="toggle_marketplace">
                                        2. Marketplace Functions
                                    </label>
                                </div>
                                <small class="text-muted ps-4 d-block mt-1">Enables public browsing, search matrix, and filtering across land parcels and houses.</small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card border p-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="online_reservations" value="1" id="toggle_reservations" @checked($featureToggles['online_reservations'])>
                                    <label class="form-check-label fw-bold text-dark" for="toggle_reservations">
                                        3. Online Reservations
                                    </label>
                                </div>
                                <small class="text-muted ps-4 d-block mt-1">Direct frontend capability to place a hold/reservation on a plot with auto-invoicing.</small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card border p-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="online_bookings" value="1" id="toggle_bookings" @checked($featureToggles['online_bookings'])>
                                    <label class="form-check-label fw-bold text-dark" for="toggle_bookings">
                                        4. Online Bookings (Survey & Viewings)
                                    </label>
                                </div>
                                <small class="text-muted ps-4 d-block mt-1">Calendar scheduling for site visits and cadastral land survey team bookings.</small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card border p-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="payment_processing" value="1" id="toggle_payments" @checked($featureToggles['payment_processing'])>
                                    <label class="form-check-label fw-bold text-dark" for="toggle_payments">
                                        5. Payment Processing
                                    </label>
                                </div>
                                <small class="text-muted ps-4 d-block mt-1">Recording payments, receipts, bank deposits, and mobile money reconciliations.</small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card border p-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="whatsapp_notifications" value="1" id="toggle_whatsapp" @checked($featureToggles['whatsapp_notifications'])>
                                    <label class="form-check-label fw-bold text-dark" for="toggle_whatsapp">
                                        6. WhatsApp Notifications & Chat Routing
                                    </label>
                                </div>
                                <small class="text-muted ps-4 d-block mt-1">Automated WhatsApp direct routing for listing links, status alerts, and inquiries.</small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card border p-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="sms_notifications" value="1" id="toggle_sms" @checked($featureToggles['sms_notifications'])>
                                    <label class="form-check-label fw-bold text-dark" for="toggle_sms">
                                        7. Push SMS Notifications
                                    </label>
                                </div>
                                <small class="text-muted ps-4 d-block mt-1">Dispatches SMS Event A, Event B, and invoice alerts to client mobile numbers.</small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card border p-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="blog_module" value="1" id="toggle_blog" @checked($featureToggles['blog_module'])>
                                    <label class="form-check-label fw-bold text-dark" for="toggle_blog">
                                        8. Blog & Advertisement Module
                                    </label>
                                </div>
                                <small class="text-muted ps-4 d-block mt-1">SEO content marketing hub for real estate articles and promotional banners.</small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card border p-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="testimonials_module" value="1" id="toggle_testimonials" @checked($featureToggles['testimonials_module'])>
                                    <label class="form-check-label fw-bold text-dark" for="toggle_testimonials">
                                        9. Testimonials & Social Proof Module
                                    </label>
                                </div>
                                <small class="text-muted ps-4 d-block mt-1">Client reviews and completed land transaction case studies section.</small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card border p-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="crm_lead_tracking" value="1" id="toggle_crm" @checked($featureToggles['crm_lead_tracking'])>
                                    <label class="form-check-label fw-bold text-dark" for="toggle_crm">
                                        10. CRM Lead Tracking Engine
                                    </label>
                                </div>
                                <small class="text-muted ps-4 d-block mt-1">Tracks customer inquiries, pipeline stages, agent assignments, and follow-ups.</small>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary fw-semibold">
                            <i class="bi bi-save me-1"></i> Save Feature Toggles
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- TAB 3: Contact & Social Media Profile Hooks (SRS 5.0) -->
    <div class="tab-pane fade" id="social" role="tabpanel">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="brand-font mb-0">Contact Info & Social Media Profile Hooks</h5>
                <p class="text-muted small mb-0">Configured in header, footer, and open graph social sharing cards</p>
            </div>
            <div class="card-body">
                <form action="{{ route('settings.social') }}" method="POST">
                    @csrf
                    <h6 class="fw-bold mb-3">Corporate Contact Information</h6>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Primary Phone Number</label>
                            <input type="text" name="contact_phone" class="form-control" value="{{ setting('contact_phone', '+255 784 100 200') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">WhatsApp Business Number</label>
                            <input type="text" name="contact_whatsapp" class="form-control" value="{{ setting('contact_whatsapp', '255784100200') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Official Email</label>
                            <input type="email" name="contact_email" class="form-control" value="{{ setting('contact_email', 'info@avenix.co.tz') }}">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-semibold">Physical Head Office Address</label>
                        <input type="text" name="contact_address" class="form-control" value="{{ setting('contact_address', 'Plot 42, Victoria Business Tower, New Bagamoyo Road, Dar es Salaam') }}">
                    </div>

                    <hr class="my-4">

                    <h6 class="fw-bold mb-3">Social Media Profile Structural Links (SRS Section 5.0)</h6>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold"><i class="bi bi-facebook text-primary me-1"></i> Facebook Page URL</label>
                            <input type="url" name="social_facebook" class="form-control" placeholder="https://facebook.com/avenix" value="{{ setting('social_facebook', '') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold"><i class="bi bi-instagram text-danger me-1"></i> Instagram Handle / URL</label>
                            <input type="url" name="social_instagram" class="form-control" placeholder="https://instagram.com/avenix" value="{{ setting('social_instagram', '') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold"><i class="bi bi-threads text-dark me-1"></i> Threads Profile URL</label>
                            <input type="url" name="social_threads" class="form-control" placeholder="https://threads.net/@avenix" value="{{ setting('social_threads', '') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold"><i class="bi bi-pinterest text-danger me-1"></i> Pinterest Business URL</label>
                            <input type="url" name="social_pinterest" class="form-control" placeholder="https://pinterest.com/avenix" value="{{ setting('social_pinterest', '') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold"><i class="bi bi-google text-primary me-1"></i> Google Business Profile URL</label>
                            <input type="url" name="social_google_business" class="form-control" placeholder="https://maps.google.com/..." value="{{ setting('social_google_business', '') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold"><i class="bi bi-tiktok text-dark me-1"></i> TikTok Profile URL</label>
                            <input type="url" name="social_tiktok" class="form-control" placeholder="https://tiktok.com/@avenix" value="{{ setting('social_tiktok', '') }}">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary fw-semibold">
                        <i class="bi bi-save me-1"></i> Save Contact & Social Hooks
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- TAB 4: Branding & White-Label (FM-005) -->
    <div class="tab-pane fade" id="branding" role="tabpanel">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="brand-font mb-0">Tenant Branding, Identity & Favicon Customization</h5>
                <span class="badge bg-primary-subtle text-primary">Public & Staff Identity</span>
            </div>
            <div class="card-body">
                <form action="{{ route('settings.branding') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-3 mb-4">
                        <div class="col-md-5">
                            <label class="form-label small fw-semibold">Company / Brand Name</label>
                            <input type="text" name="company_name" class="form-control" value="{{ setting('company_name', $org?->name ?? 'RehoSpace') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Subtitle / Business Domain</label>
                            <input type="text" name="company_subtitle" class="form-control" value="{{ setting('company_subtitle', 'Real Estate & Land Survey') }}" placeholder="e.g. Real Estate & Land Survey">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Brand Monogram Letter</label>
                            <input type="text" name="brand_monogram" class="form-control text-center fw-bold fs-5" maxlength="3" value="{{ setting('brand_monogram', 'R') }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Tagline / Slogan</label>
                            <input type="text" name="company_tagline" class="form-control" value="{{ $branding?->company_tagline ?? setting('company_tagline', 'Verified Real Estate & Land Survey Marketplace') }}">
                        </div>
                    </div>

                    <div class="row g-4 mb-4 p-3 bg-light rounded-3 border">
                        <!-- Header Logo -->
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-2"><i class="bi bi-image text-primary me-1"></i> Header Brand Logo</h6>
                            <div class="d-flex align-items-center gap-3 mb-2">
                                @if(!empty($branding?->header_logo))
                                    <div class="p-2 bg-white border rounded shadow-sm text-center">
                                        <img src="{{ $branding->header_logo }}" alt="Current Logo" class="img-fluid" style="max-height: 48px; max-width: 140px; object-fit: contain;">
                                    </div>
                                @else
                                    <div class="rounded-3 bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 48px; height: 48px;">
                                        {{ setting('brand_monogram', 'R') }}
                                    </div>
                                @endif
                                <div class="flex-grow-1">
                                    <label class="form-label small fw-semibold mb-1">Upload New Logo (PNG, SVG, JPG, WebP)</label>
                                    <input type="file" name="header_logo_file" class="form-control form-control-sm" accept="image/*">
                                </div>
                            </div>
                            <label class="form-label small text-muted mb-1">Or Direct Logo Image URL:</label>
                            <input type="url" name="header_logo_url" class="form-control form-control-sm" value="{{ $branding?->header_logo }}" placeholder="https://example.com/logo.png">
                        </div>

                        <!-- Favicon -->
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-2"><i class="bi bi-browser-chrome text-warning me-1"></i> Browser Favicon & App Icon</h6>
                            <div class="d-flex align-items-center gap-3 mb-2">
                                @if(!empty($branding?->favicon))
                                    <div class="p-2 bg-white border rounded shadow-sm text-center">
                                        <img src="{{ $branding->favicon }}" alt="Current Favicon" style="width: 36px; height: 36px; object-fit: contain;">
                                    </div>
                                @else
                                    <div class="rounded bg-secondary text-white d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                        <i class="bi bi-globe"></i>
                                    </div>
                                @endif
                                <div class="flex-grow-1">
                                    <label class="form-label small fw-semibold mb-1">Upload New Favicon (.ico, .png, .svg)</label>
                                    <input type="file" name="favicon_file" class="form-control form-control-sm" accept="image/*,.ico">
                                </div>
                            </div>
                            <label class="form-label small text-muted mb-1">Or Direct Favicon URL:</label>
                            <input type="url" name="favicon_url" class="form-control form-control-sm" value="{{ $branding?->favicon }}" placeholder="https://example.com/favicon.png">
                        </div>
                        <!-- Social Share / Open Graph Image (WhatsApp, Facebook, LinkedIn) -->
                        <div class="col-12 mt-3 pt-3 border-top">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <h6 class="fw-bold mb-0">
                                    <i class="bi bi-whatsapp text-success me-1"></i>
                                    <i class="bi bi-share text-primary me-1"></i>
                                    Social Share & WhatsApp Link Preview Card (Open Graph)
                                </h6>
                                <span class="badge bg-success-subtle text-success border border-success-subtle small">
                                    Recommended: 1200 x 630 px, JPG/PNG, &lt; 300KB
                                </span>
                            </div>
                            <p class="small text-muted mb-3">
                                This image is displayed automatically in preview cards when links to your platform are shared on <strong>WhatsApp, Facebook, LinkedIn, Twitter/X, and Telegram</strong>. WhatsApp strictly drops images larger than 300KB.
                            </p>
                            @php
                                $currentOgImage = setting('og_default_image') ?: asset('images/og-default.jpg');
                            @endphp
                            <div class="row g-3 align-items-center">
                                <div class="col-md-5">
                                    <div class="position-relative rounded-3 overflow-hidden border shadow-sm bg-dark text-center" style="max-height: 180px;">
                                        <img src="{{ $currentOgImage }}" alt="Social Share Preview" class="img-fluid w-100 object-fit-cover" style="max-height: 180px;">
                                        <span class="position-absolute bottom-0 start-0 m-2 badge bg-dark bg-opacity-75 text-white small">
                                            <i class="bi bi-eye me-1"></i> Active Link Preview
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-7">
                                    <div class="mb-2">
                                        <label class="form-label small fw-semibold mb-1">Upload New Preview Card (JPG, PNG, WebP — strictly under 2MB, optimized &lt; 300KB)</label>
                                        <input type="file" name="social_share_image_file" class="form-control form-control-sm" accept="image/jpeg,image/png,image/webp">
                                    </div>
                                    <label class="form-label small text-muted mb-1">Or Direct Image URL:</label>
                                    <input type="url" name="social_share_image_url" class="form-control form-control-sm" value="{{ setting('og_default_image') }}" placeholder="{{ asset('images/og-default.jpg') }}">
                                    <small class="text-muted d-block mt-1">If blank, defaults to the high-definition RehoSpace brand card.</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Primary Color</label>
                            <input type="color" name="primary_color" class="form-control form-control-color w-100" value="{{ $branding?->primary_color ?? '#0f52ba' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Secondary Color</label>
                            <input type="color" name="secondary_color" class="form-control form-control-color w-100" value="{{ $branding?->secondary_color ?? '#495057' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Accent Color</label>
                            <input type="color" name="accent_color" class="form-control form-control-color w-100" value="{{ $branding?->accent_color ?? '#00a86b' }}">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-semibold"><i class="bi bi-code-slash me-1"></i> Custom CSS Injection</label>
                        <textarea name="custom_css" class="form-control font-monospace small" rows="4" placeholder="/* Custom CSS overrides for public portal */">{{ $branding?->custom_css }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-primary fw-semibold px-4">
                        <i class="bi bi-save me-1"></i> Save Branding & Identity
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- TAB: Landing Page & Public CMS Configurator -->
    <div class="tab-pane fade" id="landing-cms" role="tabpanel">
        <form action="{{ route('settings.landing') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- CMS Header Card -->
            <div class="card shadow-sm border-0 mb-4 bg-primary text-white">
                <div class="card-body p-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <h4 class="brand-font mb-1 text-white"><i class="bi bi-globe2 me-2"></i> Public Landing Page & CMS Administration</h4>
                        <p class="mb-0 text-white-50 small">Configure 100% of visible texts, images, hero graphics, badges, button links, icons, and visibility switches across the public portal.</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('public.home') }}" target="_blank" class="btn btn-light btn-sm fw-bold">
                            <i class="bi bi-box-arrow-up-right me-1"></i> Preview Live Portal
                        </a>
                        <button type="submit" class="btn btn-warning btn-sm fw-bold shadow">
                            <i class="bi bi-save me-1"></i> Save All Landing Settings
                        </button>
                    </div>
                </div>
            </div>

            <!-- SECTION 1: Master Visibility Toggles Matrix -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="brand-font mb-0 text-primary"><i class="bi bi-toggles me-2"></i> 1. Landing Page Section Visibility Matrix</h5>
                    <small class="text-muted">Instantly toggle sections on or off on the public landing page.</small>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-sm-6 col-md-4 col-lg-3">
                            <div class="form-check form-switch p-3 border rounded-3 bg-light">
                                <input type="hidden" name="landing_topbar_enabled" value="0">
                                <input class="form-check-input ms-0 me-2" type="checkbox" name="landing_topbar_enabled" value="1" id="toggleTopbar" {{ setting('landing_topbar_enabled', '1') === '1' ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold small" for="toggleTopbar">Top Utility Bar</label>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-4 col-lg-3">
                            <div class="form-check form-switch p-3 border rounded-3 bg-light">
                                <input type="hidden" name="landing_categories_enabled" value="0">
                                <input class="form-check-input ms-0 me-2" type="checkbox" name="landing_categories_enabled" value="1" id="toggleCategories" {{ setting('landing_categories_enabled', '1') === '1' ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold small" for="toggleCategories">Marketplace Categories</label>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-4 col-lg-3">
                            <div class="form-check form-switch p-3 border rounded-3 bg-light">
                                <input type="hidden" name="landing_featured_enabled" value="0">
                                <input class="form-check-input ms-0 me-2" type="checkbox" name="landing_featured_enabled" value="1" id="toggleFeatured" {{ setting('landing_featured_enabled', '1') === '1' ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold small" for="toggleFeatured">Featured Listings</label>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-4 col-lg-3">
                            <div class="form-check form-switch p-3 border rounded-3 bg-light">
                                <input type="hidden" name="landing_locations_enabled" value="0">
                                <input class="form-check-input ms-0 me-2" type="checkbox" name="landing_locations_enabled" value="1" id="toggleLocations" {{ setting('landing_locations_enabled', '1') === '1' ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold small" for="toggleLocations">Locations Showcase</label>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-4 col-lg-3">
                            <div class="form-check form-switch p-3 border rounded-3 bg-light">
                                <input type="hidden" name="landing_latest_enabled" value="0">
                                <input class="form-check-input ms-0 me-2" type="checkbox" name="landing_latest_enabled" value="1" id="toggleLatest" {{ setting('landing_latest_enabled', '1') === '1' ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold small" for="toggleLatest">Newly Listed Properties</label>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-4 col-lg-3">
                            <div class="form-check form-switch p-3 border rounded-3 bg-light">
                                <input type="hidden" name="landing_projects_enabled" value="0">
                                <input class="form-check-input ms-0 me-2" type="checkbox" name="landing_projects_enabled" value="1" id="toggleProjects" {{ setting('landing_projects_enabled', '1') === '1' ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold small" for="toggleProjects">Developments & Projects</label>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-4 col-lg-3">
                            <div class="form-check form-switch p-3 border rounded-3 bg-light">
                                <input type="hidden" name="landing_land_enabled" value="0">
                                <input class="form-check-input ms-0 me-2" type="checkbox" name="landing_land_enabled" value="1" id="toggleLand" {{ setting('landing_land_enabled', '1') === '1' ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold small" for="toggleLand">Land & Cadastral Plots</label>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-4 col-lg-3">
                            <div class="form-check form-switch p-3 border rounded-3 bg-light">
                                <input type="hidden" name="landing_services_enabled" value="0">
                                <input class="form-check-input ms-0 me-2" type="checkbox" name="landing_services_enabled" value="1" id="toggleServices" {{ setting('landing_services_enabled', '1') === '1' ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold small" for="toggleServices">Services (3 Cards)</label>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-4 col-lg-3">
                            <div class="form-check form-switch p-3 border rounded-3 bg-light">
                                <input type="hidden" name="landing_trust_enabled" value="0">
                                <input class="form-check-input ms-0 me-2" type="checkbox" name="landing_trust_enabled" value="1" id="toggleTrust" {{ setting('landing_trust_enabled', '1') === '1' ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold small" for="toggleTrust">Trust Protocol & Stats</label>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-4 col-lg-3">
                            <div class="form-check form-switch p-3 border rounded-3 bg-light">
                                <input type="hidden" name="landing_testimonials_enabled" value="0">
                                <input class="form-check-input ms-0 me-2" type="checkbox" name="landing_testimonials_enabled" value="1" id="toggleTestimonials" {{ setting('landing_testimonials_enabled', '1') === '1' ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold small" for="toggleTestimonials">Customer Testimonials</label>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-4 col-lg-3">
                            <div class="form-check form-switch p-3 border rounded-3 bg-light">
                                <input type="hidden" name="landing_blog_enabled" value="0">
                                <input class="form-check-input ms-0 me-2" type="checkbox" name="landing_blog_enabled" value="1" id="toggleBlog" {{ setting('landing_blog_enabled', '1') === '1' ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold small" for="toggleBlog">Insights & Blog Section</label>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-4 col-lg-3">
                            <div class="form-check form-switch p-3 border rounded-3 bg-light">
                                <input type="hidden" name="landing_owner_cta_enabled" value="0">
                                <input class="form-check-input ms-0 me-2" type="checkbox" name="landing_owner_cta_enabled" value="1" id="toggleOwnerCta" {{ setting('landing_owner_cta_enabled', '1') === '1' ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold small" for="toggleOwnerCta">Property Owner Banner CTA</label>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-4 col-lg-3">
                            <div class="form-check form-switch p-3 border rounded-3 bg-light">
                                <input type="hidden" name="landing_nav_favorites_enabled" value="0">
                                <input class="form-check-input ms-0 me-2" type="checkbox" name="landing_nav_favorites_enabled" value="1" id="toggleFavorites" {{ setting('landing_nav_favorites_enabled', '1') === '1' ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold small" for="toggleFavorites">Navbar Saved Favorites</label>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-4 col-lg-3">
                            <div class="form-check form-switch p-3 border rounded-3 bg-light">
                                <input type="hidden" name="landing_whatsapp_enabled" value="0">
                                <input class="form-check-input ms-0 me-2" type="checkbox" name="landing_whatsapp_enabled" value="1" id="toggleWhatsapp" {{ setting('landing_whatsapp_enabled', '1') === '1' ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold small" for="toggleWhatsapp">Floating WhatsApp Widget</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION 2: Topbar & Header Utility Settings -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="brand-font mb-0 text-primary"><i class="bi bi-window-dock me-2"></i> 2. Topbar & Header Utility Customization</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Top Utility Announcement / Ticker</label>
                            <input type="text" name="landing_topbar_announcement" class="form-control" value="{{ setting('landing_topbar_announcement', 'Verified Title Deeds & Cadastral GPS Surveying Across Tanzania') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Hotline Phone</label>
                            <input type="text" name="landing_topbar_phone" class="form-control" value="{{ setting('landing_topbar_phone', '+255 784 100 200') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Support Email</label>
                            <input type="email" name="landing_topbar_email" class="form-control" value="{{ setting('landing_topbar_email', 'info@avenix.co.tz') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Survey Button Label</label>
                            <input type="text" name="landing_topbar_survey_text" class="form-control" value="{{ setting('landing_topbar_survey_text', 'Land Survey Portal') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Survey Button Icon</label>
                            <input type="text" name="landing_topbar_survey_icon" class="form-control" value="{{ setting('landing_topbar_survey_icon', 'bi-compass') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Survey Button Link URL</label>
                            <input type="text" name="landing_topbar_survey_link" class="form-control" value="{{ setting('landing_topbar_survey_link', route('public.services.land_survey')) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Staff Portal Label (Topbar)</label>
                            <input type="text" name="landing_topbar_staff_text" class="form-control" value="{{ setting('landing_topbar_staff_text', 'Staff Portal') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Staff Portal Icon (Topbar)</label>
                            <input type="text" name="landing_topbar_staff_icon" class="form-control" value="{{ setting('landing_topbar_staff_icon', 'bi-person-lock') }}">
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION 3: Main Navbar Links & Action Buttons -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="brand-font mb-0 text-primary"><i class="bi bi-menu-button-wide me-2"></i> 3. Navbar Menu Labels & Action Buttons</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Nav Item 1 (Home)</label>
                            <input type="text" name="landing_nav_home_label" class="form-control" value="{{ setting('landing_nav_home_label', 'Home') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Nav Item 2 (Properties)</label>
                            <input type="text" name="landing_nav_properties_label" class="form-control" value="{{ setting('landing_nav_properties_label', 'Properties') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Nav Item 3 (Land)</label>
                            <input type="text" name="landing_nav_land_label" class="form-control" value="{{ setting('landing_nav_land_label', 'Land & Plots') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Nav Item 4 (Developments)</label>
                            <input type="text" name="landing_nav_developments_label" class="form-control" value="{{ setting('landing_nav_developments_label', 'Developments') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Nav Item 5 (Locations)</label>
                            <input type="text" name="landing_nav_locations_label" class="form-control" value="{{ setting('landing_nav_locations_label', 'Locations') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Nav Item 6 (Services)</label>
                            <input type="text" name="landing_nav_services_label" class="form-control" value="{{ setting('landing_nav_services_label', 'Services') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Nav Item 7 (Insights)</label>
                            <input type="text" name="landing_nav_insights_label" class="form-control" value="{{ setting('landing_nav_insights_label', 'Insights') }}">
                        </div>
                    </div>

                    <h6 class="fw-bold mb-3 border-top pt-3">Navbar Action Buttons</h6>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Login Button Text</label>
                            <input type="text" name="landing_nav_login_btn_text" class="form-control" value="{{ setting('landing_nav_login_btn_text', 'Login') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">List Property CTA Text</label>
                            <input type="text" name="landing_nav_list_btn_text" class="form-control" value="{{ setting('landing_nav_list_btn_text', 'List Property') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">List Property Icon</label>
                            <input type="text" name="landing_nav_list_btn_icon" class="form-control" value="{{ setting('landing_nav_list_btn_icon', 'bi-plus-circle-fill') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">List Property Target URL</label>
                            <input type="text" name="landing_nav_list_btn_url" class="form-control" value="{{ setting('landing_nav_list_btn_url', route('login')) }}">
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION 4: Hero Banner & Search Engine -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="brand-font mb-0 text-primary"><i class="bi bi-image-fill me-2"></i> 4. Hero Banner & Search Bar Engine</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Hero Badge Icon</label>
                            <input type="text" name="landing_hero_badge_icon" class="form-control" value="{{ setting('landing_hero_badge_icon', 'bi-patch-check-fill') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Hero Badge Text</label>
                            <input type="text" name="landing_hero_badge_text" class="form-control" value="{{ setting('landing_hero_badge_text', "Tanzania's Premier Real Estate & Land Survey Ecosystem") }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Hero Title Headline (Supports multi-line text)</label>
                            <textarea name="landing_hero_title" class="form-control" rows="2">{{ setting('landing_hero_title', "Find a Place to Call Home.\nDiscover Opportunities to Invest.") }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Hero Subtitle / Description</label>
                            <textarea name="landing_hero_subtitle" class="form-control" rows="2">{{ setting('landing_hero_subtitle', 'Discover verified houses, luxury apartments, surveyed cadastral land plots, and master-planned developments in one powerful digital marketplace.') }}</textarea>
                        </div>
                    </div>

                    <div class="p-3 bg-light rounded-3 border mb-4">
                        <h6 class="fw-bold mb-2"><i class="bi bi-card-image text-primary me-1"></i> Hero Background Image</h6>
                        <div class="row g-3 align-items-center">
                            <div class="col-md-4 text-center">
                                @php
                                    $currentHeroImg = setting('landing_hero_image', 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=1600&auto=format&fit=crop&q=80');
                                @endphp
                                <img src="{{ $currentHeroImg }}" class="img-fluid rounded-3 border shadow-sm" style="max-height: 120px; width: 100%; object-fit: cover;" alt="Hero Preview">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Upload New Hero Image</label>
                                <input type="file" name="landing_hero_image_file" class="form-control" accept="image/*">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Or Hero Image URL</label>
                                <input type="url" name="landing_hero_image" class="form-control" value="{{ setting('landing_hero_image') }}" placeholder="https://images.unsplash.com/...">
                            </div>
                        </div>
                    </div>

                    <h6 class="fw-bold mb-3">Search Bar Engine Customization</h6>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Tab 1 (Buy)</label>
                            <input type="text" name="landing_search_tab_buy" class="form-control" value="{{ setting('landing_search_tab_buy', 'Buy') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Tab 2 (Rent)</label>
                            <input type="text" name="landing_search_tab_rent" class="form-control" value="{{ setting('landing_search_tab_rent', 'Rent') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Tab 3 (Land & Plots)</label>
                            <input type="text" name="landing_search_tab_land" class="form-control" value="{{ setting('landing_search_tab_land', 'Land & Plots') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Tab 4 (Developments)</label>
                            <input type="text" name="landing_search_tab_developments" class="form-control" value="{{ setting('landing_search_tab_developments', 'Developments') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Search Button Text</label>
                            <input type="text" name="landing_search_btn_text" class="form-control" value="{{ setting('landing_search_btn_text', 'Search') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Search Button Icon</label>
                            <input type="text" name="landing_search_btn_icon" class="form-control" value="{{ setting('landing_search_btn_icon', 'bi-search') }}">
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION 5: Section Headings, Tags & CTA Buttons -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="brand-font mb-0 text-primary"><i class="bi bi-fonts me-2"></i> 5. Marketplace Section Headings, Tags & CTAs</h5>
                </div>
                <div class="card-body">
                    <!-- Marketplace Categories -->
                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">Marketplace Categories</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Section Tag</label>
                            <input type="text" name="landing_categories_tag" class="form-control" value="{{ setting('landing_categories_tag', 'Marketplace Categories') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Section Title</label>
                            <input type="text" name="landing_categories_title" class="form-control" value="{{ setting('landing_categories_title', 'Explore by Property Type') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">CTA Button Text</label>
                            <input type="text" name="landing_categories_cta_text" class="form-control" value="{{ setting('landing_categories_cta_text', 'View All Categories') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">CTA Target URL</label>
                            <input type="text" name="landing_categories_cta_url" class="form-control" value="{{ setting('landing_categories_cta_url', route('public.properties')) }}">
                        </div>
                    </div>

                    <!-- Featured Properties -->
                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">Featured Properties</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Section Tag</label>
                            <input type="text" name="landing_featured_tag" class="form-control" value="{{ setting('landing_featured_tag', 'Handpicked Selection') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Section Title</label>
                            <input type="text" name="landing_featured_title" class="form-control" value="{{ setting('landing_featured_title', 'Featured Real Estate Listings') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">CTA Button Text</label>
                            <input type="text" name="landing_featured_cta_text" class="form-control" value="{{ setting('landing_featured_cta_text', 'Browse All Featured') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">CTA Target URL</label>
                            <input type="text" name="landing_featured_cta_url" class="form-control" value="{{ setting('landing_featured_cta_url', route('public.properties', ['sort' => 'views'])) }}">
                        </div>
                    </div>

                    <!-- Newly Listed Properties -->
                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">Newly Listed Properties</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Section Tag</label>
                            <input type="text" name="landing_latest_tag" class="form-control" value="{{ setting('landing_latest_tag', 'Fresh Market Additions') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Section Title</label>
                            <input type="text" name="landing_latest_title" class="form-control" value="{{ setting('landing_latest_title', 'Newly Listed Properties') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">CTA Button Text</label>
                            <input type="text" name="landing_latest_cta_text" class="form-control" value="{{ setting('landing_latest_cta_text', 'Explore Marketplace') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">CTA Target URL</label>
                            <input type="text" name="landing_latest_cta_url" class="form-control" value="{{ setting('landing_latest_cta_url', route('public.properties')) }}">
                        </div>
                    </div>

                    <!-- Featured Developments -->
                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">Developments & Master-Planned Projects</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Section Tag</label>
                            <input type="text" name="landing_projects_tag" class="form-control" value="{{ setting('landing_projects_tag', 'Major Opportunities') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Section Title</label>
                            <input type="text" name="landing_projects_title" class="form-control" value="{{ setting('landing_projects_title', 'Discover New Developments') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">CTA Button Text</label>
                            <input type="text" name="landing_projects_cta_text" class="form-control" value="{{ setting('landing_projects_cta_text', 'All Projects') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">CTA Target URL</label>
                            <input type="text" name="landing_projects_cta_url" class="form-control" value="{{ setting('landing_projects_cta_url', route('public.projects')) }}">
                        </div>
                    </div>

                    <!-- Land & Plot Marketplace -->
                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">Cadastral Land & Plot Opportunities</h6>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Section Tag</label>
                            <input type="text" name="landing_land_tag" class="form-control" value="{{ setting('landing_land_tag', 'Cadastral Surveyed Parcels') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Section Title</label>
                            <input type="text" name="landing_land_title" class="form-control" value="{{ setting('landing_land_title', 'Land & Plot Opportunities') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">CTA Button Text</label>
                            <input type="text" name="landing_land_cta_text" class="form-control" value="{{ setting('landing_land_cta_text', 'All Land Listings') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">CTA Target URL</label>
                            <input type="text" name="landing_land_cta_url" class="form-control" value="{{ setting('landing_land_cta_url', route('public.land')) }}">
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION 6: Locations Showcase Customizer (6 Hubs) -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="brand-font mb-0 text-primary"><i class="bi bi-geo-alt-fill me-2"></i> 6. Explore Locations Showcase (Images & Descriptions)</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Section Tag</label>
                            <input type="text" name="landing_locations_tag" class="form-control" value="{{ setting('landing_locations_tag', 'Geographic Reach') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Section Title</label>
                            <input type="text" name="landing_locations_title" class="form-control" value="{{ setting('landing_locations_title', 'Explore Properties by Location') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">CTA Button Text</label>
                            <input type="text" name="landing_locations_cta_text" class="form-control" value="{{ setting('landing_locations_cta_text', 'All Regions') }}">
                        </div>
                    </div>

                    <!-- 6 Location Cards Accordion or Grid -->
                    @php
                        $locDefaults = [
                            1 => ['name' => 'Dar es Salaam', 'desc' => 'Coastal Commercial Capital, Masaki, Oysterbay & Kigamboni', 'image' => 'https://images.unsplash.com/photo-1590523741831-ab7e8b8f9c7f?w=600&auto=format&fit=crop&q=80'],
                            2 => ['name' => 'Morogoro', 'desc' => 'High-Growth SGR Hub, Agricultural Valleys & Kihonda Plots', 'image' => 'https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?w=600&auto=format&fit=crop&q=80'],
                            3 => ['name' => 'Dodoma', 'desc' => 'Government Capital, Prime Commercial Parcels & Ihumwa', 'image' => 'https://images.unsplash.com/photo-1577495508048-b635879837f1?w=600&auto=format&fit=crop&q=80'],
                            4 => ['name' => 'Arusha', 'desc' => 'Tourism Gateway, Mount Meru Views & Diplomatic Residences', 'image' => 'https://images.unsplash.com/photo-1580618672591-eb180b1a973f?w=600&auto=format&fit=crop&q=80'],
                            5 => ['name' => 'Mwanza', 'desc' => 'Lake Victoria Economic Hub, Capripoint & Commercial Assets', 'image' => 'https://images.unsplash.com/photo-1516426122078-c23e76319801?w=600&auto=format&fit=crop&q=80'],
                            6 => ['name' => 'Zanzibar', 'desc' => 'Exclusive Beachfront Resorts, Luxury Villas & Stone Town Heritage', 'image' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=600&auto=format&fit=crop&q=80'],
                        ];
                    @endphp
                    <div class="row g-4">
                        @for($i = 1; $i <= 6; $i++)
                            @php
                                $dName = $locDefaults[$i]['name'];
                                $dDesc = $locDefaults[$i]['desc'];
                                $dImg = $locDefaults[$i]['image'];
                                $cName = setting("landing_location_{$i}_name", $dName);
                                $cDesc = setting("landing_location_{$i}_desc", $dDesc);
                                $cImg = setting("landing_location_{$i}_image", $dImg);
                            @endphp
                            <div class="col-md-6 col-lg-4">
                                <div class="p-3 border rounded-3 bg-light h-100 d-flex flex-column">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="badge bg-primary">Location #{{ $i }}</span>
                                    </div>
                                    <div class="mb-2 text-center">
                                        <img src="{{ $cImg }}" class="img-fluid rounded border shadow-sm" style="height: 110px; width: 100%; object-fit: cover;" alt="Location Preview">
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small fw-semibold mb-1">City / Region Name</label>
                                        <input type="text" name="landing_location_{{ $i }}_name" class="form-control form-control-sm" value="{{ $cName }}">
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small fw-semibold mb-1">Subtitle / Description</label>
                                        <input type="text" name="landing_location_{{ $i }}_desc" class="form-control form-control-sm" value="{{ $cDesc }}">
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small fw-semibold mb-1">Upload Card Image</label>
                                        <input type="file" name="landing_location_{{ $i }}_image_file" class="form-control form-control-sm" accept="image/*">
                                    </div>
                                    <div class="mt-auto">
                                        <label class="form-label small text-muted mb-1">Or Image URL</label>
                                        <input type="url" name="landing_location_{{ $i }}_image" class="form-control form-control-sm" value="{{ $cImg }}">
                                    </div>
                                </div>
                            </div>
                        @endfor
                    </div>
                </div>
            </div>

            <!-- SECTION 7: Professional Services (3 Cards) -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="brand-font mb-0 text-primary"><i class="bi bi-gear-wide-connected me-2"></i> 7. Professional Services Section (3 Cards)</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Section Tag</label>
                            <input type="text" name="landing_services_tag" class="form-control" value="{{ setting('landing_services_tag', 'End-to-End Solutions') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Section Title</label>
                            <input type="text" name="landing_services_title" class="form-control" value="{{ setting('landing_services_title', 'Comprehensive Property & Survey Services') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Section Subtitle</label>
                            <input type="text" name="landing_services_subtitle" class="form-control" value="{{ setting('landing_services_subtitle', 'From professional land surveying and cadastral beaconing to property marketing and asset management.') }}">
                        </div>
                    </div>

                    <div class="row g-4">
                        <!-- Service Card 1 -->
                        <div class="col-md-4">
                            <div class="p-3 border rounded-3 bg-light h-100">
                                <span class="badge bg-primary mb-2">Service Card 1</span>
                                <div class="mb-2">
                                    <label class="form-label small fw-semibold mb-1">Icon (Bootstrap Icon class)</label>
                                    <input type="text" name="landing_service_1_icon" class="form-control form-control-sm" value="{{ setting('landing_service_1_icon', 'bi-compass') }}">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small fw-semibold mb-1">Service Title</label>
                                    <input type="text" name="landing_service_1_title" class="form-control form-control-sm" value="{{ setting('landing_service_1_title', 'Land Survey & GIS Mapping') }}">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small fw-semibold mb-1">Description</label>
                                    <textarea name="landing_service_1_desc" class="form-control form-control-sm" rows="3">{{ setting('landing_service_1_desc', 'Boundary surveying, cadastral plot beaconing, topographical contours, RTK GPS setting-out, and town planning compliance.') }}</textarea>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small fw-semibold mb-1">Button Text</label>
                                    <input type="text" name="landing_service_1_btn_text" class="form-control form-control-sm" value="{{ setting('landing_service_1_btn_text', 'Request Land Survey') }}">
                                </div>
                                <div>
                                    <label class="form-label small fw-semibold mb-1">Button Target Link</label>
                                    <input type="text" name="landing_service_1_btn_url" class="form-control form-control-sm" value="{{ setting('landing_service_1_btn_url', route('public.services.land_survey')) }}">
                                </div>
                            </div>
                        </div>

                        <!-- Service Card 2 -->
                        <div class="col-md-4">
                            <div class="p-3 border rounded-3 bg-light h-100">
                                <span class="badge bg-success mb-2">Service Card 2</span>
                                <div class="mb-2">
                                    <label class="form-label small fw-semibold mb-1">Icon</label>
                                    <input type="text" name="landing_service_2_icon" class="form-control form-control-sm" value="{{ setting('landing_service_2_icon', 'bi-houses') }}">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small fw-semibold mb-1">Service Title</label>
                                    <input type="text" name="landing_service_2_title" class="form-control form-control-sm" value="{{ setting('landing_service_2_title', 'Property Sales & Marketing') }}">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small fw-semibold mb-1">Description</label>
                                    <textarea name="landing_service_2_desc" class="form-control form-control-sm" rows="3">{{ setting('landing_service_2_desc', 'Connect qualified buyers with verified title-deed properties, luxury villas, and commercial real estate assets.') }}</textarea>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small fw-semibold mb-1">Button Text</label>
                                    <input type="text" name="landing_service_2_btn_text" class="form-control form-control-sm" value="{{ setting('landing_service_2_btn_text', 'Learn More') }}">
                                </div>
                                <div>
                                    <label class="form-label small fw-semibold mb-1">Button Target Link</label>
                                    <input type="text" name="landing_service_2_btn_url" class="form-control form-control-sm" value="{{ setting('landing_service_2_btn_url', route('public.services.detail', 'property-sales')) }}">
                                </div>
                            </div>
                        </div>

                        <!-- Service Card 3 -->
                        <div class="col-md-4">
                            <div class="p-3 border rounded-3 bg-light h-100">
                                <span class="badge bg-warning text-dark mb-2">Service Card 3</span>
                                <div class="mb-2">
                                    <label class="form-label small fw-semibold mb-1">Icon</label>
                                    <input type="text" name="landing_service_3_icon" class="form-control form-control-sm" value="{{ setting('landing_service_3_icon', 'bi-building-gear') }}">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small fw-semibold mb-1">Service Title</label>
                                    <input type="text" name="landing_service_3_title" class="form-control form-control-sm" value="{{ setting('landing_service_3_title', 'Property Management') }}">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small fw-semibold mb-1">Description</label>
                                    <textarea name="landing_service_3_desc" class="form-control form-control-sm" rows="3">{{ setting('landing_service_3_desc', 'Automated tenant billing, lease agreements, digital rent collection, and facility maintenance coordination.') }}</textarea>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small fw-semibold mb-1">Button Text</label>
                                    <input type="text" name="landing_service_3_btn_text" class="form-control form-control-sm" value="{{ setting('landing_service_3_btn_text', 'Learn More') }}">
                                </div>
                                <div>
                                    <label class="form-label small fw-semibold mb-1">Button Target Link</label>
                                    <input type="text" name="landing_service_3_btn_url" class="form-control form-control-sm" value="{{ setting('landing_service_3_btn_url', route('public.services.detail', 'property-management')) }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION 8: Trust Protocol & Statistics Counters -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="brand-font mb-0 text-primary"><i class="bi bi-shield-check me-2"></i> 8. Trust Protocol & Platform Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Trust Badge</label>
                            <input type="text" name="landing_trust_badge" class="form-control" value="{{ setting('landing_trust_badge', 'Trust & Verification Protocol') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Main Heading</label>
                            <input type="text" name="landing_trust_title" class="form-control" value="{{ setting('landing_trust_title', 'Why Real Estate Clients Choose REMS') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Description</label>
                            <input type="text" name="landing_trust_desc" class="form-control" value="{{ setting('landing_trust_desc', 'We eliminate land disputes and fraudulent transactions by pairing modern digital marketplace convenience with ground-level cadastral verification.') }}">
                        </div>
                    </div>

                    <h6 class="fw-bold mb-3 border-top pt-3">3 Trust Pillars</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="p-3 border rounded-3 bg-light">
                                <label class="form-label small fw-semibold mb-1">Pillar 1 Icon & Title</label>
                                <div class="input-group mb-2">
                                    <input type="text" name="landing_trust_1_icon" class="form-control form-control-sm" value="{{ setting('landing_trust_1_icon', 'bi-patch-check-fill') }}">
                                    <input type="text" name="landing_trust_1_title" class="form-control form-control-sm" value="{{ setting('landing_trust_1_title', 'Cadastral & Title Verification') }}">
                                </div>
                                <label class="form-label small fw-semibold mb-1">Description</label>
                                <textarea name="landing_trust_1_desc" class="form-control form-control-sm" rows="2">{{ setting('landing_trust_1_desc', 'Every listing undergoes rigorous registry and beacon verification before receiving the Verified badge.') }}</textarea>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="p-3 border rounded-3 bg-light">
                                <label class="form-label small fw-semibold mb-1">Pillar 2 Icon & Title</label>
                                <div class="input-group mb-2">
                                    <input type="text" name="landing_trust_2_icon" class="form-control form-control-sm" value="{{ setting('landing_trust_2_icon', 'bi-whatsapp') }}">
                                    <input type="text" name="landing_trust_2_title" class="form-control form-control-sm" value="{{ setting('landing_trust_2_title', 'Real-Time WhatsApp & Direct Support') }}">
                                </div>
                                <label class="form-label small fw-semibold mb-1">Description</label>
                                <textarea name="landing_trust_2_desc" class="form-control form-control-sm" rows="2">{{ setting('landing_trust_2_desc', 'Directly connect with certified local agents and schedule in-person site viewings instantly.') }}</textarea>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="p-3 border rounded-3 bg-light">
                                <label class="form-label small fw-semibold mb-1">Pillar 3 Icon & Title</label>
                                <div class="input-group mb-2">
                                    <input type="text" name="landing_trust_3_icon" class="form-control form-control-sm" value="{{ setting('landing_trust_3_icon', 'bi-shield-lock-fill') }}">
                                    <input type="text" name="landing_trust_3_title" class="form-control form-control-sm" value="{{ setting('landing_trust_3_title', 'Privacy & Secure Transactions') }}">
                                </div>
                                <label class="form-label small fw-semibold mb-1">Description</label>
                                <textarea name="landing_trust_3_desc" class="form-control form-control-sm" rows="2">{{ setting('landing_trust_3_desc', 'Owner contact privacy protection and structured digital sales contracts for maximum buyer security.') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <h6 class="fw-bold mb-3 border-top pt-3">4 Platform Statistics (Labels & Manual Value Overrides)</h6>
                    <p class="text-muted small mb-3">Leave override fields blank to compute dynamically from the database.</p>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="p-3 border rounded-3 bg-light">
                                <label class="form-label small fw-semibold mb-1">Stat 1 Label</label>
                                <input type="text" name="landing_stat_1_label" class="form-control form-control-sm mb-2" value="{{ setting('landing_stat_1_label', 'Verified Properties') }}">
                                <label class="form-label small text-muted mb-1">Value Override</label>
                                <input type="number" name="landing_stat_1_override" class="form-control form-control-sm" value="{{ setting('landing_stat_1_override') }}" placeholder="Auto">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 border rounded-3 bg-light">
                                <label class="form-label small fw-semibold mb-1">Stat 2 Label</label>
                                <input type="text" name="landing_stat_2_label" class="form-control form-control-sm mb-2" value="{{ setting('landing_stat_2_label', 'Cadastral Land Surveys') }}">
                                <label class="form-label small text-muted mb-1">Value Override</label>
                                <input type="number" name="landing_stat_2_override" class="form-control form-control-sm" value="{{ setting('landing_stat_2_override') }}" placeholder="Auto">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 border rounded-3 bg-light">
                                <label class="form-label small fw-semibold mb-1">Stat 3 Label</label>
                                <input type="text" name="landing_stat_3_label" class="form-control form-control-sm mb-2" value="{{ setting('landing_stat_3_label', 'Regions Across Tanzania') }}">
                                <label class="form-label small text-muted mb-1">Value Override</label>
                                <input type="number" name="landing_stat_3_override" class="form-control form-control-sm" value="{{ setting('landing_stat_3_override') }}" placeholder="Auto">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 border rounded-3 bg-light">
                                <label class="form-label small fw-semibold mb-1">Stat 4 Label</label>
                                <input type="text" name="landing_stat_4_label" class="form-control form-control-sm mb-2" value="{{ setting('landing_stat_4_label', 'Satisfied Clients') }}">
                                <label class="form-label small text-muted mb-1">Value Override</label>
                                <input type="number" name="landing_stat_4_override" class="form-control form-control-sm" value="{{ setting('landing_stat_4_override') }}" placeholder="Auto">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION 9: Testimonials, Blog & Owner CTA -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="brand-font mb-0 text-primary"><i class="bi bi-chat-square-quote me-2"></i> 9. Testimonials, Blog & Property Owner CTA</h5>
                </div>
                <div class="card-body">
                    <!-- Testimonials -->
                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">Customer Testimonials Section</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Tag</label>
                            <input type="text" name="landing_testimonials_tag" class="form-control" value="{{ setting('landing_testimonials_tag', 'Client Experiences') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Heading</label>
                            <input type="text" name="landing_testimonials_title" class="form-control" value="{{ setting('landing_testimonials_title', 'What Our Customers Say') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Subtitle</label>
                            <input type="text" name="landing_testimonials_subtitle" class="form-control" value="{{ setting('landing_testimonials_subtitle', 'Trusted by property buyers, land investors, tenants, and commercial developers.') }}">
                        </div>
                    </div>

                    <!-- Blog -->
                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">Real Estate Insights & Blog</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Tag</label>
                            <input type="text" name="landing_blog_tag" class="form-control" value="{{ setting('landing_blog_tag', 'Knowledge Hub') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Heading</label>
                            <input type="text" name="landing_blog_title" class="form-control" value="{{ setting('landing_blog_title', 'Real Estate Insights & Guides') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">CTA Button Text</label>
                            <input type="text" name="landing_blog_cta_text" class="form-control" value="{{ setting('landing_blog_cta_text', 'View All Articles') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">CTA Target URL</label>
                            <input type="text" name="landing_blog_cta_url" class="form-control" value="{{ setting('landing_blog_cta_url', route('public.blog')) }}">
                        </div>
                    </div>

                    <!-- Property Owner CTA Banner -->
                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">Property Owner Action Banner</h6>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Banner Heading</label>
                            <input type="text" name="landing_owner_cta_title" class="form-control" value="{{ setting('landing_owner_cta_title', 'Have a Property to Sell or Rent?') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Banner Subtitle / Description</label>
                            <input type="text" name="landing_owner_cta_desc" class="form-control" value="{{ setting('landing_owner_cta_desc', 'Reach thousands of active property buyers and prospective tenants. List your property on the REMS marketplace with full verification support.') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Button 1 Text</label>
                            <input type="text" name="landing_owner_cta_1_text" class="form-control" value="{{ setting('landing_owner_cta_1_text', 'List Your Property') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Button 1 Icon</label>
                            <input type="text" name="landing_owner_cta_1_icon" class="form-control" value="{{ setting('landing_owner_cta_1_icon', 'bi-plus-circle') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Button 1 Link URL</label>
                            <input type="text" name="landing_owner_cta_1_url" class="form-control" value="{{ setting('landing_owner_cta_1_url', route('login')) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Button 2 Text</label>
                            <input type="text" name="landing_owner_cta_2_text" class="form-control" value="{{ setting('landing_owner_cta_2_text', 'Speak with Listing Agent') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Button 2 Icon</label>
                            <input type="text" name="landing_owner_cta_2_icon" class="form-control" value="{{ setting('landing_owner_cta_2_icon', 'bi-whatsapp') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Button 2 Link URL (Blank for default WhatsApp)</label>
                            <input type="text" name="landing_owner_cta_2_url" class="form-control" value="{{ setting('landing_owner_cta_2_url') }}" placeholder="Auto WhatsApp">
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION 10: WhatsApp Widget & Footer Customizer -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="brand-font mb-0 text-primary"><i class="bi bi-layout-text-window me-2"></i> 10. WhatsApp Chat Widget & Footer Content</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Floating WhatsApp Default Message</label>
                            <input type="text" name="landing_whatsapp_message" class="form-control" value="{{ setting('landing_whatsapp_message', 'Hello REMS Real Estate Platform, I am interested in exploring property listings and land opportunities.') }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Footer Brand Bio Paragraph</label>
                            <textarea name="footer_bio" class="form-control" rows="3">{{ setting('footer_bio', 'Verified Real Estate & Land Survey Marketplace. The premier digital property marketplace and cadastral surveying ecosystem in Tanzania, connecting verified sellers, buyers, tenants, and surveyors.') }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Newsletter Title</label>
                            <input type="text" name="footer_newsletter_title" class="form-control" value="{{ setting('footer_newsletter_title', 'Stay Updated') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Newsletter Subtitle</label>
                            <input type="text" name="footer_newsletter_subtitle" class="form-control" value="{{ setting('footer_newsletter_subtitle', 'Get newly verified listings & market reports delivered.') }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Footer Copyright Line</label>
                            <input type="text" name="footer_copyright" class="form-control" value="{{ setting('footer_copyright', 'All Rights Reserved. Built on RREP Architecture.') }}">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fixed Save Action Footer -->
            <div class="d-flex justify-content-end gap-2 mb-5">
                <button type="submit" class="btn btn-primary btn-lg fw-bold px-5 shadow">
                    <i class="bi bi-check2-circle me-1"></i> Save All Landing Page & CMS Settings
                </button>
            </div>
        </form>
    </div>

    <!-- TAB 5: Branches & Audit Trail -->
    <div class="tab-pane fade" id="branches" role="tabpanel">
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="brand-font mb-0">Organization Branches</h5>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            @foreach($branches as $br)
                                <li class="list-group-item d-flex justify-content-between align-items-center p-3">
                                    <div>
                                        <div class="fw-bold">{{ $br->name }} @if($br->is_main)<span class="badge bg-primary ms-1">HQ</span>@endif</div>
                                        <small class="text-muted">{{ $br->city }} &bull; Code: {{ $br->code }}</small>
                                    </div>
                                    <span class="badge bg-success-subtle text-success">{{ $br->status }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="brand-font mb-0">Recent Administrative Audit Logs</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>User</th>
                                        <th>Action</th>
                                        <th>Timestamp</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($auditLogs as $log)
                                        <tr>
                                            <td>{{ $log->user?->name ?? 'System' }}</td>
                                            <td><span class="badge bg-light text-dark border">{{ $log->action }}</span></td>
                                            <td class="small text-muted">{{ $log->created_at->diffForHumans() }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="3" class="text-center text-muted py-3">No logs found</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (window.location.hash) {
        const hashTarget = window.location.hash;
        const triggerBtn = document.querySelector(`button[data-bs-target="${hashTarget}"]`);
        if (triggerBtn) {
            const tabInstance = bootstrap.Tab.getOrCreateInstance(triggerBtn);
            tabInstance.show();
        }
    }
});

function refreshSmsBalance() {
    const el = document.getElementById('liveBalanceText');
    el.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Checking...';
    fetch("{{ route('settings.sms_balance') }}")
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success' && !isNaN(data.balance)) {
                el.innerHTML = Number(data.balance).toLocaleString() + ' <span class="fs-6 fw-normal">Units</span>';
            } else {
                el.innerText = data.balance || data.message || 'Unavailable';
            }
        })
        .catch(err => {
            el.innerText = 'Error checking balance';
        });
}
</script>
@endsection
