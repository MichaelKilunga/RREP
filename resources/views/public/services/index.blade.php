@extends('layouts.public')

@section('title', 'Real Estate & Land Survey Services in Tanzania')

@section('content')
<div class="bg-dark text-white py-5 text-center" style="background: linear-gradient(135deg, #091224 0%, #1e3a8a 100%);">
    <div class="container py-3">
        <span class="badge bg-primary px-3 py-2 rounded-pill mb-2">Professional Services</span>
        <h1 class="brand-font display-5 fw-bold text-white mb-2">Real Estate & Land Survey Services</h1>
        <p class="lead text-white-50 mx-auto mb-0" style="max-width: 650px;">
            Certified geomatics land surveying, digital property marketing, valuation advisory, and property asset management.
        </p>
    </div>
</div>

<div class="container py-5">
    <div class="row g-4">
        <!-- 1. Land Survey -->
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 p-4 border rounded-4 shadow-sm bg-white hover-shadow transition d-flex flex-column">
                <div class="rounded-3 bg-primary text-white d-flex align-items-center justify-content-center mb-3" style="width: 52px; height: 52px; font-size: 1.5rem;">
                    <i class="bi bi-compass"></i>
                </div>
                <h4 class="brand-font mb-2">Land Survey & Cadastral Mapping</h4>
                <p class="text-muted small mb-4 flex-grow-1">
                    Boundary surveying, GPS beacon relocation and fixation, subdivision master plans, topographical surveys, and official Ministry of Lands town planning drawings.
                </p>
                <a href="{{ route('public.services.land_survey') }}" class="btn btn-primary btn-sm fw-bold">
                    Request Land Survey <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>

        <!-- 2. Property Sales -->
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 p-4 border rounded-4 shadow-sm bg-white hover-shadow transition d-flex flex-column">
                <div class="rounded-3 bg-success text-white d-flex align-items-center justify-content-center mb-3" style="width: 52px; height: 52px; font-size: 1.5rem;">
                    <i class="bi bi-houses"></i>
                </div>
                <h4 class="brand-font mb-2">Property Sales & Brokerage</h4>
                <p class="text-muted small mb-4 flex-grow-1">
                    Connecting qualified buyers with verified title-deed properties, luxury residential villas, and commercial land assets across Tanzania.
                </p>
                <a href="{{ route('public.services.detail', 'property-sales') }}" class="btn btn-outline-success btn-sm fw-bold">
                    Learn More <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>

        <!-- 3. Property Rentals -->
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 p-4 border rounded-4 shadow-sm bg-white hover-shadow transition d-flex flex-column">
                <div class="rounded-3 bg-warning text-dark d-flex align-items-center justify-content-center mb-3" style="width: 52px; height: 52px; font-size: 1.5rem;">
                    <i class="bi bi-key"></i>
                </div>
                <h4 class="brand-font mb-2">Property Rentals & Leasing</h4>
                <p class="text-muted small mb-4 flex-grow-1">
                    Executive residential apartments, furnished corporate villas, Grade-A office suites, and retail storefronts with verified lease agreements.
                </p>
                <a href="{{ route('public.services.detail', 'property-rentals') }}" class="btn btn-outline-warning btn-sm fw-bold">
                    Learn More <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>

        <!-- 4. Property Management -->
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 p-4 border rounded-4 shadow-sm bg-white hover-shadow transition d-flex flex-column">
                <div class="rounded-3 bg-info text-white d-flex align-items-center justify-content-center mb-3" style="width: 52px; height: 52px; font-size: 1.5rem;">
                    <i class="bi bi-building-gear"></i>
                </div>
                <h4 class="brand-font mb-2">Facility & Property Management</h4>
                <p class="text-muted small mb-4 flex-grow-1">
                    End-to-end management for landlords: tenant screening, automated rent roll invoicing, digital arrears management, and preventive maintenance.
                </p>
                <a href="{{ route('public.services.detail', 'property-management') }}" class="btn btn-outline-info btn-sm fw-bold">
                    Learn More <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>

        <!-- 5. Property Valuation -->
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 p-4 border rounded-4 shadow-sm bg-white hover-shadow transition d-flex flex-column">
                <div class="rounded-3 bg-danger text-white d-flex align-items-center justify-content-center mb-3" style="width: 52px; height: 52px; font-size: 1.5rem;">
                    <i class="bi bi-calculator"></i>
                </div>
                <h4 class="brand-font mb-2">Property Valuation & Appraisal</h4>
                <p class="text-muted small mb-4 flex-grow-1">
                    Certified valuation reports for mortgage financing, taxation, asset sales, and dispute settlement by registered valuation surveyors.
                </p>
                <a href="{{ route('public.services.detail', 'property-valuation') }}" class="btn btn-outline-danger btn-sm fw-bold">
                    Learn More <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>

        <!-- 6. Real Estate Investment Advisory -->
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 p-4 border rounded-4 shadow-sm bg-white hover-shadow transition d-flex flex-column">
                <div class="rounded-3 bg-dark text-white d-flex align-items-center justify-content-center mb-3" style="width: 52px; height: 52px; font-size: 1.5rem;">
                    <i class="bi bi-graph-up-arrow"></i>
                </div>
                <h4 class="brand-font mb-2">Investment Advisory & Feasibility</h4>
                <p class="text-muted small mb-4 flex-grow-1">
                    Feasibility studies, ROI projections, joint-venture structuring, and diaspora property acquisition advisory.
                </p>
                <a href="{{ route('public.services.detail', 'investment-advisory') }}" class="btn btn-outline-dark btn-sm fw-bold">
                    Learn More <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
