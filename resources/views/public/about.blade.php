@extends('layouts.public')

@section('title', 'About Us - Verified Land & Geomatics Services')

@section('content')
<!-- About Hero -->
<section class="py-5 text-white" style="background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%);">
    <div class="container text-center py-4">
        <h1 class="display-4 fw-extrabold text-white mb-2">About Us</h1>
        <p class="lead text-white-50 max-w-xl mx-auto mb-0">Your trusted partner in Land Sales, Cadastral Surveying, TP Drawings, Title Deeds, and Construction Management.</p>
    </div>
</section>

<section class="py-5 bg-white">
    <div class="container py-3">
        <div class="row g-5 align-items-center mb-5">
            <div class="col-lg-6">
                <span class="text-uppercase text-primary fw-bold small tracking-wider">Trusted Geomatics & Land Partner</span>
                <h2 class="fw-bold text-dark mb-3">Professional Land Management & Surveying in Tanzania</h2>
                <p class="text-muted leading-relaxed">
                    We deliver transparent, accurate, and reliable real estate solutions. From master-planned land sales and town planning drawings to certified cadastral surveys and title deed facilitation, we protect and elevate your property investments.
                </p>
                <div class="row g-3 mt-3">
                    <div class="col-6">
                        <div class="p-3 rounded-4 bg-light border">
                            <i class="bi bi-shield-check text-success fs-3 mb-2 d-block"></i>
                            <h6 class="fw-bold text-dark mb-1">Cadastral Integrity</h6>
                            <small class="text-muted">Verified survey beacons and Ministry approvals.</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 rounded-4 bg-light border">
                            <i class="bi bi-file-earmark-check text-primary fs-3 mb-2 d-block"></i>
                            <h6 class="fw-bold text-dark mb-1">Title Deeds Support</h6>
                            <small class="text-muted">End-to-end Certificate of Occupancy processing.</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="rounded-4 overflow-hidden shadow">
                    <img src="https://images.unsplash.com/photo-1500382017468-9049fed747ef?w=800&auto=format&fit=crop&q=80" alt="Land Surveying" class="w-100 object-fit-cover" style="height: 380px;">
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5 bg-primary text-white text-center">
    <div class="container py-2">
        <h2 class="fw-extrabold text-white mb-2">Need Consultation or Service Assistance?</h2>
        <p class="lead text-white-50 mb-4 max-w-xl mx-auto">Get in touch with our team directly on WhatsApp or by phone.</p>
        <div class="d-flex justify-content-center gap-3">
            <a href="tel:{{ setting('contact_phone', '+255784100200') }}" class="btn btn-light text-primary btn-lg px-4 fw-bold rounded-pill">
                <i class="bi bi-telephone-fill me-2"></i> {{ setting('contact_phone', '+255 784 100 200') }}
            </a>
            <a href="https://wa.me/{{ setting('contact_whatsapp', '255784100200') }}" target="_blank" class="btn btn-success btn-lg px-4 fw-bold rounded-pill">
                <i class="bi bi-whatsapp me-2"></i> WhatsApp Direct
            </a>
        </div>
    </div>
</section>
@endsection
