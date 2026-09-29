@extends('layouts.public')

@section('title', 'Land Sales, Cadastral Survey & Property Management')

@section('content')
<!-- Hero Section (Clean & Impactful) -->
<section class="hero-section py-5 position-relative bg-dark text-white" style="background: linear-gradient(135deg, #091224 0%, #1e3a8a 100%); min-height: 420px;">
    <div class="container py-4 py-lg-5 text-center">
        <div class="max-w-3xl mx-auto">
            <span class="badge bg-primary px-3 py-2 rounded-pill fw-semibold mb-3">
                <i class="bi bi-patch-check-fill me-1"></i> {{ setting('landing_hero_badge_text', app()->getLocale() === 'sw' ? 'Portal Iliyothibitishwa ya Ardhi' : 'Verified Land & Survey Portal') }}
            </span>
            <h1 class="display-4 fw-extrabold text-white mb-3 text-balance">
                {{ setting('landing_hero_title', app()->getLocale() === 'sw' ? 'Miradi ya Viwanja & Upimaji wa Ardhi Tanzania' : 'Cadastral Land & Master Projects in Tanzania') }}
            </h1>
            <p class="lead text-white-50 mb-4 mx-auto fs-5" style="max-width: 680px;">
                {{ setting('landing_hero_subtitle', app()->getLocale() === 'sw' ? 'Pata viwanja vilivyopimwa vyenye Hati Miliki na Michoro ya Mipango Miji kwa uwazi na haraka.' : 'Verified land plots with Title Deeds, Town Planning drawings, and professional geomatics.') }}
            </p>
            <div class="d-flex flex-wrap justify-content-center gap-3 align-items-center">
                <a href="{{ route('public.projects') }}" class="btn btn-primary btn-lg px-4 fw-bold rounded-pill shadow">
                    <i class="bi bi-map-fill me-2"></i> {{ setting('landing_hero_cta_primary_text', app()->getLocale() === 'sw' ? 'Tazama Miradi' : 'Explore Projects') }}
                </a>
                <a href="{{ route('public.services') }}" class="btn btn-outline-light btn-lg px-4 fw-semibold rounded-pill">
                    <i class="bi bi-briefcase me-2"></i> {{ setting('landing_hero_cta_secondary_text', app()->getLocale() === 'sw' ? 'Huduma Zetu' : 'Our Services') }}
                </a>
            </div>
        </div>
    </div>
</section>

<!-- 1. OUR SERVICES PANEL -->
@if(setting('landing_services_enabled', '1') === '1')
<section class="py-5 bg-white">
    <div class="container">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <span class="text-uppercase text-primary fw-bold small tracking-wider">{{ setting('landing_services_tag', app()->getLocale() === 'sw' ? 'Ufumbuzi wa Kitaalamu' : 'Professional Solutions') }}</span>
                <h2 class="fw-bold text-dark mb-0">{{ setting('landing_services_title', app()->getLocale() === 'sw' ? 'Huduma Zetu 8 Kuu' : 'Our 8 Core Services') }}</h2>
            </div>
            <a href="{{ route('public.services') }}" class="btn btn-outline-primary rounded-pill fw-semibold btn-sm">
                {{ app()->getLocale() === 'sw' ? 'Zote 8' : 'View All' }} <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="row g-4">
            @foreach($services as $service)
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100 border rounded-4 p-4 shadow-sm hover-shadow transition-all bg-white d-flex flex-column justify-content-between">
                        <div>
                            <div class="rounded-3 bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center mb-3" style="width: 52px; height: 52px; font-size: 1.5rem;">
                                <i class="bi {{ $service->icon }}"></i>
                            </div>
                            <h5 class="fw-bold text-dark mb-2 fs-6">{{ $service->title }}</h5>
                            <p class="text-muted small mb-3 line-clamp-2">{{ $service->short_description }}</p>
                        </div>
                        <div class="pt-2 border-top border-light d-flex align-items-center justify-content-between">
                            <span class="badge bg-light text-dark fw-semibold" style="font-size: 0.75rem;">{{ $service->category }}</span>
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" data-bs-toggle="modal" data-bs-target="#quoteModal" data-service="{{ $service->title }}" title="Request Quote">
                                <i class="bi bi-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- 2. MODERN GIS LAND PROJECTS SHOWCASE -->
@if(setting('landing_projects_enabled', '1') === '1')
<section class="py-5 bg-light position-relative">
    <div class="container">
        <div class="d-flex flex-wrap align-items-end justify-content-between mb-4 gap-3">
            <div>
                <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill bg-primary bg-opacity-10 text-primary fw-semibold small mb-2 border border-primary border-opacity-10">
                    <i class="bi bi-layers-half text-primary"></i>
                    <span>{{ setting('landing_projects_tag', app()->getLocale() === 'sw' ? 'Ramani za Viwanja & Mipango Miji' : 'Master Plans & Cadastral GIS') }}</span>
                </div>
                <h2 class="fw-extrabold text-dark mb-1 display-6 fs-3">{{ setting('landing_projects_title', app()->getLocale() === 'sw' ? 'Miradi ya Viwanja & Ramani za Shapefile' : 'Land Projects & Interactive Plot Maps') }}</h2>
                <p class="text-muted small mb-0">{{ setting('landing_projects_subtitle', app()->getLocale() === 'sw' ? 'Vipimo halisi vya GIS, michoro ya mipango miji na status ya viwanja vilivyopimwa.' : 'Verified GIS boundaries, cadastral master plans, and live plot availability.') }}</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('public.projects') }}" class="btn btn-outline-primary rounded-pill fw-bold btn-sm px-4 shadow-sm">
                    {{ setting('landing_projects_cta_text', app()->getLocale() === 'sw' ? 'Miradi Yote' : 'Explore All') }} <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>

        <div class="row g-4">
            @foreach($featuredProjects as $project)
                @php
                    $total = max(1, (int)$project->total_units);
                    $sold = (int)$project->sold_plots;
                    $available = (int)$project->available_units;
                    $soldPercent = min(100, max(0, round(($sold / $total) * 100)));
                @endphp
                <div class="col-lg-6">
                    <div class="card border-0 rounded-4 shadow-sm hover-shadow transition-all h-100 overflow-hidden bg-white d-flex flex-column justify-content-between">
                        <!-- Top Image / Map Banner -->
                        <div class="position-relative bg-dark" style="height: 180px; overflow: hidden;">
                            @if($project->hero_image)
                                <img src="{{ $project->hero_image_url }}" alt="{{ $project->title }}" class="w-100 h-100 object-fit-cover opacity-90">
                            @else
                                <div class="w-100 h-100 d-flex align-items-center justify-content-center text-white-50" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);">
                                    <div class="text-center p-3">
                                        <i class="bi bi-map text-primary opacity-50 display-4 d-block mb-1"></i>
                                        <span class="small text-uppercase tracking-wider fw-semibold opacity-75">Cadastral GIS Map</span>
                                    </div>
                                </div>
                            @endif

                            <!-- Overlay Gradient & Badges -->
                            <div class="position-absolute top-0 start-0 w-100 h-100 p-3 d-flex flex-column justify-content-between" style="background: linear-gradient(to bottom, rgba(15,23,42,0.65) 0%, rgba(15,23,42,0.15) 50%, rgba(15,23,42,0.85) 100%);">
                                <div class="d-flex align-items-center justify-content-between">
                                    <span class="badge bg-dark bg-opacity-75 text-white border border-white border-opacity-25 px-3 py-1.5 rounded-pill shadow-sm small">
                                        <i class="bi bi-geo-alt-fill text-danger me-1"></i>{{ $project->council ?? $project->location_name }}
                                    </span>
                                    <span class="badge {{ $project->available_units > 0 ? 'bg-success' : 'bg-danger' }} px-3 py-1.5 rounded-pill fw-bold shadow-sm">
                                        <i class="bi bi-circle-fill me-1 small opacity-75"></i> {{ $project->available_units > 0 ? (app()->getLocale() === 'sw' ? 'Inapatikana' : 'Available') : (app()->getLocale() === 'sw' ? 'Imekwisha' : 'Sold Out') }}
                                    </span>
                                </div>
                                <div class="d-flex align-items-center justify-content-between">
                                    <h5 class="fw-bold text-white mb-0 text-shadow-sm fs-5 me-2 line-clamp-1">{{ $project->title }}</h5>
                                    <span class="badge bg-primary px-2.5 py-1 rounded-2 shadow-xs text-white small flex-shrink-0" style="font-size: 0.72rem;">
                                        <i class="bi bi-layers-half me-1"></i>GIS Ready
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="card-body p-3 p-sm-4 d-flex flex-column justify-content-between">
                            <div>
                                <p class="text-secondary small mb-3 line-clamp-2" style="line-height: 1.5;">{{ $project->description }}</p>

                                <!-- Visual Sales Progress Tracker -->
                                <div class="p-2.5 p-sm-3 bg-light rounded-3 border border-light mb-3">
                                    <div class="d-flex align-items-center justify-content-between mb-1 small fw-semibold">
                                        <span class="text-dark small">
                                            <i class="bi bi-pie-chart-fill text-primary me-1"></i>{{ app()->getLocale() === 'sw' ? 'Hali ya Uuzaji wa Viwanja' : 'Plot Allocation Progress' }}
                                        </span>
                                        <span class="text-success fw-bold small">{{ $soldPercent }}% {{ app()->getLocale() === 'sw' ? 'Vimeuzwa' : 'Allocated' }}</span>
                                    </div>
                                    <div class="progress rounded-pill bg-secondary bg-opacity-10 mb-3" style="height: 8px;">
                                        <div class="progress-bar bg-success rounded-pill" role="progressbar" style="width: {{ $soldPercent }}%" aria-valuenow="{{ $soldPercent }}" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>

                                    <!-- 4 Stat Pills -->
                                    <div class="row text-center g-1.5 g-sm-2 pt-1">
                                        <div class="col-3">
                                            <div class="bg-white py-1.5 px-1 rounded-2 border shadow-xs">
                                                <small class="d-block text-muted text-uppercase fw-bold" style="font-size: 0.58rem;">{{ app()->getLocale() === 'sw' ? 'Jumla' : 'Total' }}</small>
                                                <strong class="text-dark fs-6">{{ $project->total_units }}</strong>
                                            </div>
                                        </div>
                                        <div class="col-3">
                                            <div class="bg-white py-1.5 px-1 rounded-2 border shadow-xs">
                                                <small class="d-block text-muted text-uppercase fw-bold" style="font-size: 0.58rem;">{{ app()->getLocale() === 'sw' ? 'Uuzaji' : 'Sold' }}</small>
                                                <strong class="text-danger fs-6">{{ $project->sold_plots }}</strong>
                                            </div>
                                        </div>
                                        <div class="col-3">
                                            <div class="bg-white py-1.5 px-1 rounded-2 border shadow-xs">
                                                <small class="d-block text-muted text-uppercase fw-bold" style="font-size: 0.58rem;">{{ app()->getLocale() === 'sw' ? 'Yaliyopo' : 'Available' }}</small>
                                                <strong class="text-success fs-6">{{ $project->available_units }}</strong>
                                            </div>
                                        </div>
                                        <div class="col-3">
                                            <div class="bg-white py-1.5 px-1 rounded-2 border shadow-xs">
                                                <small class="d-block text-muted text-uppercase fw-bold" style="font-size: 0.58rem;">{{ app()->getLocale() === 'sw' ? 'Tango %' : 'Deposit %' }}</small>
                                                <strong class="text-primary fs-6">{{ (int)$project->initial_payment_percent }}%</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Feature Chips -->
                                <div class="d-flex flex-wrap gap-1.5 mb-3">
                                    <span class="badge bg-white text-secondary border rounded-pill px-2.5 py-1" style="font-weight: 500; font-size: 0.75rem;">
                                        <i class="bi bi-file-earmark-check-fill text-success me-1"></i>{{ app()->getLocale() === 'sw' ? 'Hati Miliki' : 'Title Deed' }}
                                    </span>
                                    <span class="badge bg-white text-secondary border rounded-pill px-2.5 py-1" style="font-weight: 500; font-size: 0.75rem;">
                                        <i class="bi bi-signpost-split-fill text-primary me-1"></i>{{ app()->getLocale() === 'sw' ? 'Barabara' : 'Access Roads' }}
                                    </span>
                                    <span class="badge bg-white text-secondary border rounded-pill px-2.5 py-1" style="font-weight: 500; font-size: 0.75rem;">
                                        <i class="bi bi-lightning-charge-fill text-warning me-1"></i>{{ app()->getLocale() === 'sw' ? 'Maji & Umeme' : 'Utilities' }}
                                    </span>
                                </div>
                            </div>

                            <!-- Footer Price & CTA -->
                            <div class="d-flex flex-wrap align-items-center justify-content-between pt-3 border-top border-light mt-2 gap-2">
                                <div>
                                    <small class="text-muted d-block" style="font-size: 0.72rem; font-weight: 600;">{{ app()->getLocale() === 'sw' ? 'Anzia Bei' : 'Starting Price' }}</small>
                                    <span class="fw-extrabold text-primary fs-5">TZS {{ number_format($project->starting_price) }}</span>
                                </div>
                                <a href="{{ route('public.projects.show', $project->slug) }}" class="btn btn-primary rounded-pill px-3.5 py-2 fw-bold btn-sm shadow-sm d-inline-flex align-items-center">
                                    <span>{{ app()->getLocale() === 'sw' ? 'Tazama Ramani' : 'View Map' }}</span>
                                    <i class="bi bi-arrow-right ms-1.5"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- 3. QUICK METRICS & TRUST -->
@if(setting('landing_trust_enabled', '1') === '1')
<section class="py-5 bg-dark text-white">
    <div class="container text-center py-3">
        <div class="row g-4">
            <div class="col-6 col-md-3">
                <h2 class="display-5 fw-extrabold text-white mb-1">{{ setting('landing_stat_1_override') ?: ($stats['projects'].'+') }}</h2>
                <span class="text-white-50 text-uppercase small fw-bold">{{ setting('landing_stat_1_label', app()->getLocale() === 'sw' ? 'Miradi ya Mipango' : 'Master Projects') }}</span>
            </div>
            <div class="col-6 col-md-3">
                <h2 class="display-5 fw-extrabold text-white mb-1">{{ setting('landing_stat_2_override') ?: ($stats['plots'].'+') }}</h2>
                <span class="text-white-50 text-uppercase small fw-bold">{{ setting('landing_stat_2_label', app()->getLocale() === 'sw' ? 'Viwanja Vilivyopimwa' : 'Surveyed Plots') }}</span>
            </div>
            <div class="col-6 col-md-3">
                <h2 class="display-5 fw-extrabold text-white mb-1">{{ setting('landing_stat_3_override') ?: '8' }}</h2>
                <span class="text-white-50 text-uppercase small fw-bold">{{ setting('landing_stat_3_label', app()->getLocale() === 'sw' ? 'Huduma Kuu' : 'Core Services') }}</span>
            </div>
            <div class="col-6 col-md-3">
                <h2 class="display-5 fw-extrabold text-white mb-1">{{ setting('landing_stat_4_override') ?: ($stats['clients'].'+') }}</h2>
                <span class="text-white-50 text-uppercase small fw-bold">{{ setting('landing_stat_4_label', app()->getLocale() === 'sw' ? 'Wateja Walioridhika' : 'Satisfied Clients') }}</span>
            </div>
        </div>
    </div>
</section>
@endif

<!-- 4. CREATIVE STYLIZED CTA BANNER -->
@if(setting('landing_owner_cta_enabled', '1') === '1')
<section class="py-5 bg-light">
    <div class="container">
        <div class="card border-0 rounded-4 overflow-hidden shadow-lg position-relative text-white" style="background: linear-gradient(135deg, rgba(15, 23, 42, 0.88) 0%, rgba(30, 58, 138, 0.88) 100%), url('https://images.unsplash.com/photo-1500382017468-9049fed747ef?w=1600&auto=format&fit=crop&q=80') center/cover no-repeat;">
            <div class="card-body p-4 p-md-5 text-center">
                <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold mb-3">
                    <i class="bi bi-compass-fill me-1"></i> {{ setting('landing_cta_banner_badge', app()->getLocale() === 'sw' ? 'Wataalamu wa Upimaji Ardhi' : 'Geomatics & Land Advisory') }}
                </span>
                <h2 class="display-6 fw-extrabold text-white mb-3">
                    {{ setting('landing_cta_banner_title', app()->getLocale() === 'sw' ? 'Unahitaji Kupima Ardhi au Kupata Kiwanja?' : 'Ready to Secure Your Land or Request a Survey?') }}
                </h2>
                <p class="lead text-white-50 mb-4 max-w-2xl mx-auto fs-6" style="max-width: 640px;">
                    {{ setting('landing_cta_banner_subtitle', app()->getLocale() === 'sw' ? 'Wasiliana na wataalamu wetu wa geomatics na ardhi kupata msaada wa haraka na wa uhakika.' : 'Contact our geomatics consultants and surveyors today for swift assistance.') }}
                </p>
                <div class="d-flex flex-wrap justify-content-center gap-3">
                    <a href="tel:{{ setting('contact_phone', '+255784100200') }}" class="btn btn-light text-primary btn-lg px-4 fw-bold rounded-pill shadow">
                        <i class="bi bi-telephone-fill me-2"></i> {{ setting('contact_phone', '+255 784 100 200') }}
                    </a>
                    <a href="{{ route('public.contact') }}" class="btn btn-outline-light btn-lg px-4 fw-bold rounded-pill">
                        <i class="bi bi-envelope-fill me-2"></i> {{ setting('landing_cta_banner_btn_text', app()->getLocale() === 'sw' ? 'Wasiliana Nasi' : 'Contact Us') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
@endif

<!-- Service Quote Modal -->
<div class="modal fade" id="quoteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header border-0 bg-light rounded-top-4">
                <h5 class="modal-header-title fw-bold text-dark mb-0">Request Service Quote</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form action="{{ route('public.inquire') }}" method="POST">
                    @csrf
                    <input type="hidden" name="preferred_contact_method" value="WhatsApp">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Service Selected</label>
                        <input type="text" id="modalServiceName" name="service_name" class="form-control bg-light" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Full Name</label>
                        <input type="text" name="name" class="form-control" required placeholder="Enter your name">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Phone / WhatsApp Number</label>
                        <input type="tel" name="phone" class="form-control" required placeholder="e.g. 0784 100 200">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Details or Location</label>
                        <textarea name="message" class="form-control" rows="3" required placeholder="Specify location or project requirement"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 fw-bold py-2 rounded-pill">Send Inquiry</button>
                </form>
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
            const service = button.getAttribute('data-service') || 'General Service Inquiry';
            document.getElementById('modalServiceName').value = service;
        });
    }
});
</script>
@endsection
