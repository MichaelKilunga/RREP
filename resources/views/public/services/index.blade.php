@extends('layouts.public')

@section('title', 'Our Services - Land Sales, Surveying & Construction Management')

@section('content')
<!-- Service Hero Banner (PKB Style Header) -->
<section class="py-5 text-white" style="background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%);">
    <div class="container text-center py-4">
        <h1 class="display-4 fw-extrabold text-white mb-2">Our Services</h1>
        <p class="lead text-white-50 max-w-xl mx-auto mb-0">Professional geomatics, land surveying, property sales, title deeds facilitation, and construction management.</p>
    </div>
</section>

<!-- 8 Services Grid (PKB Image 3 Layout) -->
<section class="py-5 bg-white">
    <div class="container py-3">
        <div class="row g-4">
            @foreach($services as $index => $service)
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100 border rounded-4 p-4 shadow-sm hover-shadow transition-all bg-white d-flex flex-column justify-content-between">
                        <div>
                            <!-- Line-Art Blue Icon (PKB style) -->
                            <div class="rounded-3 bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center mb-3" style="width: 56px; height: 56px; font-size: 1.65rem;">
                                <i class="bi {{ $service->icon }}"></i>
                            </div>

                            <h4 class="fw-bold text-dark fs-6 mb-2">{{ $service->title }}</h4>
                            <p class="text-muted small mb-3 line-clamp-3" style="font-size: 0.85rem;">{{ $service->short_description }}</p>

                            @if(!empty($service->sub_services))
                                <div class="d-flex flex-wrap gap-1 mb-3">
                                    @foreach($service->sub_services as $sub)
                                        <span class="badge bg-light text-primary border border-primary-subtle fw-medium" style="font-size: 0.7rem;">{{ $sub }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <!-- Arrow Action Button -->
                        <div class="pt-3 border-top border-light d-flex align-items-center justify-content-between">
                            <span class="text-muted small fw-semibold">Service #0{{ $index + 1 }}</span>
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;" data-bs-toggle="modal" data-bs-target="#serviceModal" data-service="{{ $service->title }}" title="Request Quote">
                                <i class="bi bi-arrow-right fs-6"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Ready to Work With Us Banner (PKB Image 3 Footer Banner) -->
<section class="py-5 bg-dark text-white text-center">
    <div class="container py-3">
        <h2 class="display-6 fw-extrabold text-white mb-2">Ready to work with us?</h2>
        <p class="lead text-white-50 mb-4 max-w-2xl mx-auto">Join us for innovative geospatial solutions driving precision and success in land management and infrastructure.</p>
        <div class="d-flex justify-content-center gap-3">
            <a href="{{ route('public.contact') }}" class="btn btn-primary btn-lg px-4 fw-bold rounded-pill">
                Get A Quote <i class="bi bi-arrow-up-right-circle ms-1"></i>
            </a>
            <a href="https://wa.me/{{ setting('contact_whatsapp', '255784100200') }}" target="_blank" class="btn btn-success btn-lg px-4 fw-bold rounded-pill">
                <i class="bi bi-whatsapp me-1"></i> WhatsApp Us
            </a>
        </div>
    </div>
</section>

<!-- Service Modal -->
<div class="modal fade" id="serviceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header border-0 bg-light rounded-top-4">
                <h5 class="modal-header-title fw-bold text-dark mb-0">Request Service / Quote</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form action="{{ route('public.inquire') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Service Requested</label>
                        <input type="text" id="serviceModalTitle" name="service_name" class="form-control bg-light" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Your Full Name</label>
                        <input type="text" name="name" class="form-control" required placeholder="Enter full name">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Phone / WhatsApp Number</label>
                        <input type="tel" name="phone" class="form-control" required placeholder="e.g. 0784 100 200">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Specific Requirements</label>
                        <textarea name="message" class="form-control" rows="3" required placeholder="Tell us more about your land, plot location or survey needs"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 fw-bold py-2 rounded-pill">Submit Quote Request</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const serviceModal = document.getElementById('serviceModal');
    if (serviceModal) {
        serviceModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const service = button.getAttribute('data-service') || 'General Service Request';
            document.getElementById('serviceModalTitle').value = service;
        });
    }
});
</script>
@endsection
