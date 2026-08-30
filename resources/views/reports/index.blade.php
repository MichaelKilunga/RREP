@extends('layouts.app')

@section('title', __('app.reports'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="brand-font mb-1"><i class="bi bi-file-earmark-bar-graph text-primary me-2"></i>{{ __('app.reports') }} & Business Intelligence</h3>
        <p class="text-muted small mb-0">Executive analytics, inventory valuations, sales performance, rent rolls, and cadastral audits</p>
    </div>
</div>

<div class="row g-4">
    <!-- Report 1: Property Inventory -->
    <div class="col-md-6 col-lg-4">
        <div class="card h-100 p-4 border shadow-sm">
            <div class="rounded-3 bg-primary-subtle text-primary p-3 d-inline-flex align-items-center justify-content-center mb-3" style="width: 50px; height: 50px;">
                <i class="bi bi-building fs-4"></i>
            </div>
            <h5 class="brand-font mb-2">Property Inventory & Valuation</h5>
            <p class="text-muted small flex-grow-1">Comprehensive asset breakdown, pricing, rental potentials, vacancy status, and owner allocations.</p>
            <a href="{{ route('reports.properties') }}" class="btn btn-outline-primary btn-sm fw-bold">Generate Report &rarr;</a>
        </div>
    </div>

    <!-- Report 2: Sales & Revenue -->
    <div class="col-md-6 col-lg-4">
        <div class="card h-100 p-4 border shadow-sm">
            <div class="rounded-3 bg-success-subtle text-success p-3 d-inline-flex align-items-center justify-content-center mb-3" style="width: 50px; height: 50px;">
                <i class="bi bi-cash-stack fs-4"></i>
            </div>
            <h5 class="brand-font mb-2">Sales Revenue & Collections</h5>
            <p class="text-muted small flex-grow-1">Closed deals volume, installment cashflow collections, outstanding receivables, and commission splits.</p>
            <a href="{{ route('reports.sales') }}" class="btn btn-outline-success btn-sm fw-bold">Generate Report &rarr;</a>
        </div>
    </div>

    <!-- Report 3: Agent Commissions -->
    <div class="col-md-6 col-lg-4">
        <div class="card h-100 p-4 border shadow-sm">
            <div class="rounded-3 bg-warning-subtle text-warning p-3 d-inline-flex align-items-center justify-content-center mb-3" style="width: 50px; height: 50px;">
                <i class="bi bi-award fs-4"></i>
            </div>
            <h5 class="brand-font mb-2">Agent Performance & Commissions</h5>
            <p class="text-muted small flex-grow-1">Broker performance metrics, total sales volume, earned commission payouts, and balances due.</p>
            <a href="{{ route('reports.agents') }}" class="btn btn-outline-warning text-dark btn-sm fw-bold">Generate Report &rarr;</a>
        </div>
    </div>

    <!-- Report 4: Rent Roll -->
    <div class="col-md-6 col-lg-4">
        <div class="card h-100 p-4 border shadow-sm">
            <div class="rounded-3 bg-info-subtle text-info p-3 d-inline-flex align-items-center justify-content-center mb-3" style="width: 50px; height: 50px;">
                <i class="bi bi-key fs-4"></i>
            </div>
            <h5 class="brand-font mb-2">Rent Roll & Arrears Ledger</h5>
            <p class="text-muted small flex-grow-1">Active lease agreements, monthly rental income schedules, deposit holdings, and overdue arrears.</p>
            <a href="{{ route('reports.rent_roll') }}" class="btn btn-outline-info btn-sm fw-bold">Generate Report &rarr;</a>
        </div>
    </div>

    <!-- Report 5: Cadastral Survey -->
    <div class="col-md-6 col-lg-4">
        <div class="card h-100 p-4 border shadow-sm">
            <div class="rounded-3 bg-secondary-subtle text-secondary p-3 d-inline-flex align-items-center justify-content-center mb-3" style="width: 50px; height: 50px;">
                <i class="bi bi-geo-alt fs-4"></i>
            </div>
            <h5 class="brand-font mb-2">Land Survey & Cadastral Status</h5>
            <p class="text-muted small flex-grow-1">Survey project progression, beacon coordinate counts, deed plan verification, and ministry approvals.</p>
            <a href="{{ route('reports.survey') }}" class="btn btn-outline-secondary btn-sm fw-bold">Generate Report &rarr;</a>
        </div>
    </div>

    <!-- Report 6: Leads & CRM -->
    <div class="col-md-6 col-lg-4">
        <div class="card h-100 p-4 border shadow-sm">
            <div class="rounded-3 bg-dark-subtle text-dark p-3 d-inline-flex align-items-center justify-content-center mb-3" style="width: 50px; height: 50px;">
                <i class="bi bi-funnel fs-4"></i>
            </div>
            <h5 class="brand-font mb-2">Lead Conversion & Pipeline</h5>
            <p class="text-muted small flex-grow-1">Prospect source attribution (Social, Walk-in, Website), conversion velocity, and lost reason analytics.</p>
            <a href="{{ route('reports.leads') }}" class="btn btn-outline-dark btn-sm fw-bold">Generate Report &rarr;</a>
        </div>
    </div>
</div>
@endsection
