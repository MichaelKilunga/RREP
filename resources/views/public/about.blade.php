@extends('layouts.public')

@section('title', 'About REMS Real Estate Platform & Technology')

@section('content')
<div class="bg-dark text-white py-5 text-center" style="background: linear-gradient(135deg, #091224 0%, #1e3a8a 100%);">
    <div class="container py-3">
        <span class="badge bg-primary px-3 py-2 rounded-pill mb-2">Our Mission & Ecosystem</span>
        <h1 class="brand-font display-5 fw-bold text-white mb-2">Pioneering Trusted Real Estate in Tanzania</h1>
        <p class="lead text-white-50 mx-auto mb-0" style="max-width: 650px;">
            The Reusable Real Estate Management Platform (REMS) unites property marketplace discovery, cadastral land surveying, and digital transaction workflows.
        </p>
    </div>
</div>

<div class="container py-5">
    <div class="row g-5 align-items-center mb-5">
        <div class="col-lg-6">
            <span class="section-tag">Who We Are</span>
            <h2 class="brand-font mb-3">A Digital-First Real Estate & Land Survey Ecosystem</h2>
            <p class="text-secondary" style="line-height: 1.8;">
                REMS was architected to address the systemic challenges of land fraud, boundary litigation, and opaque property discovery in the Tanzanian and broader East African market.
            </p>
            <p class="text-secondary" style="line-height: 1.8;">
                By integrating licensed geomatics land surveyors, verified property owners, and registered brokers into a single digital platform, we provide buyers, tenants, and institutional investors with unshakeable confidence in every square meter.
            </p>

            <div class="row g-3 mt-2">
                <div class="col-6">
                    <div class="p-3 rounded-4 bg-white border shadow-sm">
                        <i class="bi bi-shield-check text-success fs-3 mb-2 d-block"></i>
                        <h6 class="brand-font mb-1">Cadastral Integrity</h6>
                        <small class="text-muted">Direct verification of beacon coordinates against Ministry records.</small>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-3 rounded-4 bg-white border shadow-sm">
                        <i class="bi bi-laptop text-primary fs-3 mb-2 d-block"></i>
                        <h6 class="brand-font mb-1">Modern Platform</h6>
                        <small class="text-muted">Real-time CRM, automated invoicing, and digital viewing bookings.</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="rounded-4 overflow-hidden shadow-lg">
                <img src="https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=800&auto=format&fit=crop&q=80" alt="REMS Platform Headquarters" class="w-100 object-fit-cover" style="height: 420px;">
            </div>
        </div>
    </div>

    <!-- Platform Stats -->
    <div class="card p-4 p-md-5 border-0 rounded-4 text-white shadow-lg mb-5" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">
        <div class="row g-4 text-center">
            <div class="col-6 col-md-3 border-end border-secondary">
                <h2 class="brand-font text-warning display-5 fw-bold mb-1">{{ number_format($stats['properties']) }}+</h2>
                <span class="text-white-50 small">Verified Listings</span>
            </div>
            <div class="col-6 col-md-3 border-end border-secondary">
                <h2 class="brand-font text-success display-5 fw-bold mb-1">{{ number_format($stats['surveys']) }}+</h2>
                <span class="text-white-50 small">Cadastral Surveys</span>
            </div>
            <div class="col-6 col-md-3 border-end border-secondary">
                <h2 class="brand-font text-info display-5 fw-bold mb-1">{{ number_format($stats['locations']) }}</h2>
                <span class="text-white-50 small">Regional Hubs</span>
            </div>
            <div class="col-6 col-md-3">
                <h2 class="brand-font text-primary display-5 fw-bold mb-1">{{ number_format($stats['agents']) }}+</h2>
                <span class="text-white-50 small">Certified Agents</span>
            </div>
        </div>
    </div>
</div>
@endsection
