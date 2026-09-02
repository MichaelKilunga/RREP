@extends('layouts.app')

@section('title', __('app.settings'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="brand-font mb-1">{{ __('app.settings') }} & System Administration</h3>
        <p class="text-muted small mb-0">Dynamic gateway integrations, SMS templates, module feature flags, branding tokens, and social hooks</p>
    </div>
</div>

<!-- Nav Tabs for Settings -->
<ul class="nav nav-pills mb-4" id="settingsTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active fw-semibold" id="pushsms-tab" data-bs-toggle="pill" data-bs-target="#pushsms" type="button" role="tab">
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
            <i class="bi bi-palette-fill me-1"></i> Branding & Appearance
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-semibold" id="branches-tab" data-bs-toggle="pill" data-bs-target="#branches" type="button" role="tab">
            <i class="bi bi-building me-1"></i> Branches & Audit Trail
        </button>
    </li>
</ul>

<div class="tab-content" id="settingsTabContent">
    <!-- TAB 1: PushSMS Gateway -->
    <div class="tab-pane fade show active" id="pushsms" role="tabpanel">
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
            <div class="card-header bg-white py-3">
                <h5 class="brand-font mb-0">Tenant Branding & White-Label Customization</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('settings.branding') }}" method="POST">
                    @csrf
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Company / Brand Name</label>
                            <input type="text" name="company_name" class="form-control" value="{{ setting('company_name', $org?->name ?? 'Avenix Co Ltd') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Tagline / Slogan</label>
                            <input type="text" name="company_tagline" class="form-control" value="{{ $branding?->company_tagline ?? setting('company_tagline', 'Verified Real Estate & Land Survey Marketplace') }}">
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
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
                    <button type="submit" class="btn btn-primary fw-semibold">
                        <i class="bi bi-save me-1"></i> Save Branding Settings
                    </button>
                </form>
            </div>
        </div>
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
