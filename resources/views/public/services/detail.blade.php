@extends('layouts.public')

@section('title', $title)

@section('content')
<div class="bg-dark text-white py-5 text-center" style="background: linear-gradient(135deg, #091224 0%, #1e3a8a 100%);">
    <div class="container py-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb small text-white-50 justify-content-center mb-2">
                <li class="breadcrumb-item"><a href="{{ route('public.home') }}" class="text-white-50 text-decoration-none">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('public.services') }}" class="text-white-50 text-decoration-none">Services</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">{{ $title }}</li>
            </ol>
        </nav>
        <h1 class="brand-font display-5 fw-bold text-white mb-2">{{ $title }}</h1>
        <p class="lead text-white-50 mx-auto mb-0" style="max-width: 650px;">
            Professional real estate management and consulting solutions tailored for the East African market.
        </p>
    </div>
</div>

<div class="container py-5">
    <div class="row g-5">
        <div class="col-lg-8">
            <div class="card p-4 p-md-5 border rounded-4 bg-white shadow-sm mb-4">
                <h3 class="brand-font mb-3">About {{ $title }}</h3>
                <p class="text-secondary" style="line-height: 1.8;">
                    Our {{ strtolower($title) }} department delivers institutional-grade service backed by certified real estate professionals, registered valuation surveyors, and geomatics engineers. We leverage modern digital workflows, legal conveyancing due diligence, and transparent reporting to maximize value for our clients.
                </p>

                <h4 class="brand-font mt-4 mb-3">Key Highlights & Benefits</h4>
                <ul class="list-unstyled d-flex flex-column gap-2 text-muted">
                    <li><i class="bi bi-check-circle-fill text-success me-2"></i> End-to-end verified due diligence and title verification.</li>
                    <li><i class="bi bi-check-circle-fill text-success me-2"></i> Comprehensive market analytics across Dar es Salaam, Morogoro, Arusha, Dodoma, and Zanzibar.</li>
                    <li><i class="bi bi-check-circle-fill text-success me-2"></i> Dedicated account manager and transparent client portal reporting.</li>
                    <li><i class="bi bi-check-circle-fill text-success me-2"></i> High-conversion multi-channel marketing campaigns.</li>
                </ul>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="sticky-top" style="top: 90px;">
                <div class="card p-4 border rounded-4 bg-white shadow-sm mb-4">
                    <h5 class="brand-font mb-2">Request Service Consultation</h5>
                    <p class="text-muted small mb-3">Submit your details to speak with a dedicated service specialist.</p>

                    <form action="{{ route('public.contact.submit') }}" method="POST">
                        @csrf
                        <input type="hidden" name="subject" value="Service Inquiry: {{ $title }}">

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Full Name *</label>
                            <input type="text" name="name" class="form-control form-control-sm" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Phone Number *</label>
                            <input type="text" name="phone" class="form-control form-control-sm" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Email Address *</label>
                            <input type="email" name="email" class="form-control form-control-sm" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Service Requirements *</label>
                            <textarea name="message" rows="3" class="form-control form-control-sm" required placeholder="Describe your property or consultation requirements..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold shadow-sm">
                            Submit Consultation Request
                        </button>
                    </form>
                </div>

                <a href="https://wa.me/{{ setting('contact_whatsapp', '255784100200') }}?text={{ urlencode('Hello, I am inquiring about ' . $title . '.') }}" target="_blank" class="btn btn-success w-100 py-3 fw-bold rounded-4 shadow-sm d-flex align-items-center justify-content-center gap-2">
                    <i class="bi bi-whatsapp fs-5"></i> Chat on WhatsApp
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
