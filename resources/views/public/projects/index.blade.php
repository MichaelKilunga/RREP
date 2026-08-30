@extends('layouts.public')

@section('title', 'Real Estate Developments, Housing Estates & Master Plans in Tanzania')

@section('content')
<!-- Projects Hero Header -->
<div class="py-5 text-white position-relative overflow-hidden" style="background: linear-gradient(135deg, #091224 0%, #1e3a8a 60%, #0f172a 100%);">
    <div class="position-absolute top-0 start-0 w-100 h-100 opacity-10" style="background: radial-gradient(#60a5fa 1px, transparent 1px); background-size: 24px 24px;"></div>
    <div class="container position-relative py-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb small text-white-50 mb-3">
                <li class="breadcrumb-item"><a href="{{ route('public.home') }}" class="text-white-50 text-decoration-none">Home</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">Developments</li>
            </ol>
        </nav>

        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <span class="badge bg-primary bg-opacity-75 text-white px-3 py-1 rounded-pill mb-2 border border-light border-opacity-25" style="font-size: 0.8rem;">
                    <i class="bi bi-diagram-3-fill me-1"></i> Master-Planned Real Estate
                </span>
                <h1 class="brand-font display-5 fw-bold text-white mb-2">Discover New Developments & Projects</h1>
                <p class="lead text-white-50 mb-0" style="max-width: 650px; font-size: 1.05rem;">
                    Invest in premier residential housing estates, satellite cities, luxury beachfront villas, and commercial plazas across Tanzania.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <div class="d-inline-flex flex-column align-items-lg-end gap-1 bg-white bg-opacity-10 p-3 px-4 rounded-4 border border-light border-opacity-25 backdrop-blur">
                    <span class="text-white-50 small">Total Development Schemes</span>
                    <h3 class="brand-font text-warning mb-0">{{ $projects->total() }} Active Schemes</h3>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container py-5">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h3 class="brand-font mb-0">Active Development Schemes</h3>
            <p class="text-muted small mb-0">Browse master-planned estates and unit availability</p>
        </div>
    </div>

    <div class="row g-4">
        @forelse($projects as $proj)
            <div class="col-md-6 col-lg-4">
                @include('public.partials.project-card', ['proj' => $proj])
            </div>
        @empty
            <div class="col-12 text-center text-muted py-5">
                <h5>No projects currently listed.</h5>
                <a href="{{ route('public.properties') }}" class="btn btn-primary btn-sm mt-2">Browse All Properties</a>
            </div>
        @endforelse
    </div>

    <div class="mt-4 d-flex justify-content-center">
        {{ $projects->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
