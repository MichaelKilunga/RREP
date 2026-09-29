@extends('layouts.public')

@section('title', 'Land Sales, Cadastral Survey & Property Management')

@section('content')
<!-- Hero Section (Clean & Impactful) -->
<section class="hero-section py-5 position-relative bg-dark text-white" style="background: linear-gradient(135deg, #091224 0%, #1e3a8a 100%); min-height: 420px;">
    <div class="container py-4 py-lg-5 text-center">
        <div class="max-w-3xl mx-auto">
            <span class="badge bg-primary px-3 py-2 rounded-pill fw-semibold mb-3">
                <i class="bi bi-patch-check-fill me-1"></i> {{ app()->getLocale() === 'sw' ? 'Portal Iliyothibitishwa ya Ardhi' : 'Verified Land & Survey Portal' }}
            </span>
            <h1 class="display-4 fw-extrabold text-white mb-3 text-balance">
                {{ app()->getLocale() === 'sw' ? 'Miradi ya Viwanja & Upimaji wa Ardhi Tanzania' : 'Cadastral Land & Master Projects in Tanzania' }}
            </h1>
            <p class="lead text-white-50 mb-4 mx-auto fs-5" style="max-width: 680px;">
                {{ app()->getLocale() === 'sw' ? 'Pata viwanja vilivyopimwa vyenye Hati Miliki na Michoro ya Mipango Miji kwa uwazi na haraka.' : 'Verified land plots with Title Deeds, Town Planning drawings, and professional geomatics.' }}
            </p>
            <div class="d-flex flex-wrap justify-content-center gap-3 align-items-center">
                <a href="{{ route('public.projects') }}" class="btn btn-primary btn-lg px-4 fw-bold rounded-pill shadow">
                    <i class="bi bi-map-fill me-2"></i> {{ app()->getLocale() === 'sw' ? 'Tazama Miradi' : 'Explore Projects' }}
                </a>
                <a href="{{ route('public.services') }}" class="btn btn-outline-light btn-lg px-4 fw-semibold rounded-pill">
                    <i class="bi bi-briefcase me-2"></i> {{ app()->getLocale() === 'sw' ? 'Huduma Zetu' : 'Our Services' }}
                </a>
            </div>
        </div>
    </div>
</section>

<!-- 1. OUR SERVICES PANEL (PKB Inspired Style) -->
<section class="py-5 bg-white">
    <div class="container">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <span class="text-uppercase text-primary fw-bold small tracking-wider">{{ app()->getLocale() === 'sw' ? 'Ufumbuzi wa Kitaalamu' : 'Professional Solutions' }}</span>
                <h2 class="fw-bold text-dark mb-0">{{ app()->getLocale() === 'sw' ? 'Huduma Zetu 8 Kuu' : 'Our 8 Core Services' }}</h2>
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

<!-- 2. TAUSI LAND PROJECTS SHOWCASE (Image 1 Style) -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <span class="text-uppercase text-primary fw-bold small tracking-wider">{{ app()->getLocale() === 'sw' ? 'Miradi ya Mipango Miji' : 'Verified Master Plans' }}</span>
                <h2 class="fw-bold text-dark mb-0">{{ app()->getLocale() === 'sw' ? 'Miradi ya Viwanja & Ramani za Shapefile' : 'Land Projects & Plot Maps' }}</h2>
            </div>
            <a href="{{ route('public.projects') }}" class="btn btn-primary rounded-pill fw-bold btn-sm px-4">
                {{ app()->getLocale() === 'sw' ? 'Miradi Yote' : 'Explore All' }} <i class="bi bi-map ms-1"></i>
            </a>
        </div>

        <div class="row g-4">
            @foreach($featuredProjects as $project)
                <div class="col-lg-6">
                    <div class="card border rounded-4 shadow-sm overflow-hidden bg-white">
                        <div class="card-header bg-primary bg-opacity-10 py-3 px-4 d-flex align-items-center justify-content-between border-0">
                            <div>
                                <h5 class="fw-bold text-dark mb-0 fs-6">{{ $project->title }}</h5>
                                <small class="text-muted"><i class="bi bi-geo-alt-fill text-danger me-1"></i>{{ $project->council ?? $project->location_name }}</small>
                            </div>
                            <span class="badge {{ $project->available_units > 0 ? 'bg-success' : 'bg-danger' }} px-3 py-2 rounded-pill fw-semibold">
                                {{ $project->available_units > 0 ? (app()->getLocale() === 'sw' ? 'Inapatikana' : 'Available') : (app()->getLocale() === 'sw' ? 'Imekwisha' : 'Sold Out') }}
                            </span>
                        </div>
                        <div class="card-body p-4">
                            <p class="text-muted small mb-3 line-clamp-2">{{ $project->description }}</p>

                            <!-- TAUSI Statistics Metrics Box -->
                            <div class="row text-center g-2 py-2 px-3 bg-light rounded-3 mb-3 border">
                                <div class="col-3">
                                    <span class="d-block text-muted text-uppercase" style="font-size: 0.65rem; font-weight: 700;">{{ app()->getLocale() === 'sw' ? 'Jumla' : 'Total Plots' }}</span>
                                    <strong class="text-dark fs-6">{{ $project->total_units }}</strong>
                                </div>
                                <div class="col-3">
                                    <span class="d-block text-muted text-uppercase" style="font-size: 0.65rem; font-weight: 700;">{{ app()->getLocale() === 'sw' ? 'Uuzaji' : 'Sold' }}</span>
                                    <strong class="text-danger fs-6">{{ $project->sold_plots }}</strong>
                                </div>
                                <div class="col-3">
                                    <span class="d-block text-muted text-uppercase" style="font-size: 0.65rem; font-weight: 700;">{{ app()->getLocale() === 'sw' ? 'Yaliyopo' : 'Available' }}</span>
                                    <strong class="text-success fs-6">{{ $project->available_units }}</strong>
                                </div>
                                <div class="col-3">
                                    <span class="d-block text-muted text-uppercase" style="font-size: 0.65rem; font-weight: 700;">{{ app()->getLocale() === 'sw' ? 'Tango %' : 'Deposit %' }}</span>
                                    <strong class="text-primary fs-6">{{ (int)$project->initial_payment_percent }}%</strong>
                                </div>
                            </div>

                            <div class="d-flex align-items-center justify-content-between pt-2">
                                <div>
                                    <small class="text-muted d-block" style="font-size: 0.75rem;">{{ app()->getLocale() === 'sw' ? 'Anzia Bei' : 'Starting Price' }}</small>
                                    <span class="fw-extrabold text-primary fs-5">TZS {{ number_format($project->starting_price) }}</span>
                                </div>
                                <a href="{{ route('public.projects.show', $project->slug) }}" class="btn btn-primary rounded-pill px-4 fw-bold btn-sm">
                                    {{ app()->getLocale() === 'sw' ? 'Tazama Ramani' : 'View Map' }} <i class="bi bi-chevron-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- 3. QUICK METRICS & TRUST -->
<section class="py-5 bg-dark text-white">
    <div class="container text-center py-3">
        <div class="row g-4">
            <div class="col-6 col-md-3">
                <h2 class="display-5 fw-extrabold text-white mb-1">{{ $stats['projects'] }}+</h2>
                <span class="text-white-50 text-uppercase small fw-bold">{{ app()->getLocale() === 'sw' ? 'Miradi ya Mipango' : 'Master Projects' }}</span>
            </div>
            <div class="col-6 col-md-3">
                <h2 class="display-5 fw-extrabold text-white mb-1">{{ $stats['plots'] }}+</h2>
                <span class="text-white-50 text-uppercase small fw-bold">{{ app()->getLocale() === 'sw' ? 'Viwanja Vilivyopimwa' : 'Surveyed Plots' }}</span>
            </div>
            <div class="col-6 col-md-3">
                <h2 class="display-5 fw-extrabold text-white mb-1">8</h2>
                <span class="text-white-50 text-uppercase small fw-bold">{{ app()->getLocale() === 'sw' ? 'Huduma Kuu' : 'Core Services' }}</span>
            </div>
            <div class="col-6 col-md-3">
                <h2 class="display-5 fw-extrabold text-white mb-1">{{ $stats['clients'] }}+</h2>
                <span class="text-white-50 text-uppercase small fw-bold">{{ app()->getLocale() === 'sw' ? 'Wateja Walioridhika' : 'Satisfied Clients' }}</span>
            </div>
        </div>
    </div>
</section>

<!-- 4. CREATIVE STYLIZED CTA BANNER (With Image Backdrop) -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="card border-0 rounded-4 overflow-hidden shadow-lg position-relative text-white" style="background: linear-gradient(135deg, rgba(15, 23, 42, 0.88) 0%, rgba(30, 58, 138, 0.88) 100%), url('https://images.unsplash.com/photo-1500382017468-9049fed747ef?w=1600&auto=format&fit=crop&q=80') center/cover no-repeat;">
            <div class="card-body p-4 p-md-5 text-center">
                <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold mb-3">
                    <i class="bi bi-compass-fill me-1"></i> {{ app()->getLocale() === 'sw' ? 'Wataalamu wa Upimaji Ardhi' : 'Geomatics & Land Advisory' }}
                </span>
                <h2 class="display-6 fw-extrabold text-white mb-3">
                    {{ app()->getLocale() === 'sw' ? 'Unahitaji Kupima Ardhi au Kupata Kiwanja?' : 'Ready to Secure Your Land or Request a Survey?' }}
                </h2>
                <p class="lead text-white-50 mb-4 max-w-2xl mx-auto fs-6" style="max-width: 640px;">
                    {{ app()->getLocale() === 'sw' ? 'Wasiliana na wataalamu wetu wa geomatics na ardhi kupata msaada wa haraka na wa uhakika.' : 'Contact our geomatics consultants and surveyors today for swift assistance.' }}
                </p>
                <div class="d-flex flex-wrap justify-content-center gap-3">
                    <a href="tel:{{ setting('contact_phone', '+255784100200') }}" class="btn btn-light text-primary btn-lg px-4 fw-bold rounded-pill shadow">
                        <i class="bi bi-telephone-fill me-2"></i> {{ setting('contact_phone', '+255 784 100 200') }}
                    </a>
                    <a href="{{ route('public.contact') }}" class="btn btn-outline-light btn-lg px-4 fw-bold rounded-pill">
                        <i class="bi bi-envelope-fill me-2"></i> {{ app()->getLocale() === 'sw' ? 'Wasiliana Nasi' : 'Contact Us' }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

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
