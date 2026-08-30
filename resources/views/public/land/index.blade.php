@extends('layouts.public')

@section('title', 'Surveyed Land, Cadastral Plots & Farms for Sale in Tanzania')

@section('content')
<section class="hero-section py-5 text-center text-white position-relative overflow-hidden" style="background: linear-gradient(135deg, #064e3b 0%, #065f46 60%, #091224 100%);">
    <div class="position-absolute top-0 start-0 w-100 h-100 opacity-10" style="background: radial-gradient(#34d399 1px, transparent 1px); background-size: 24px 24px;"></div>
    <div class="container position-relative py-3" style="z-index: 2;">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb small text-white-50 justify-content-center mb-3">
                <li class="breadcrumb-item"><a href="{{ route('public.home') }}" class="text-white-50 text-decoration-none">Home</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">Land & Plots</li>
            </ol>
        </nav>
        <span class="badge bg-success bg-opacity-75 text-white border border-light border-opacity-25 px-3 py-1 rounded-pill mb-2">
            <i class="bi bi-compass text-warning me-1"></i> Cadastral Verified Land Marketplace
        </span>
        <h1 class="brand-font display-5 fw-bold text-white mb-2">Buy Surveyed Land & Cadastral Plots in Tanzania</h1>
        <p class="lead text-white-50 mx-auto mb-4" style="max-width: 700px; font-size: 1.05rem;">
            Explore verified residential building plots, commercial land parcels, and agricultural farm estates with guaranteed beacon accuracy and clean title deeds.
        </p>

        <div class="card p-3 shadow-lg border-0 mx-auto text-dark" style="max-width: 800px; border-radius: 1rem;">
            <form action="{{ route('public.land') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-12 col-md-5">
                    <select name="city" class="form-select">
                        <option value="">All Regions (Morogoro, Dar, Arusha...)</option>
                        @foreach($cities as $c)
                            <option value="{{ $c }}" {{ request('city') == $c ? 'selected' : '' }}>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <input type="number" name="min_acres" class="form-control" placeholder="Min Acres" value="{{ request('min_acres') }}">
                </div>
                <div class="col-6 col-md-2">
                    <input type="number" name="max_price" class="form-control" placeholder="Max TZS" value="{{ request('max_price') }}">
                </div>
                <div class="col-12 col-md-2">
                    <button type="submit" class="btn btn-success w-100 fw-bold py-2"><i class="bi bi-search me-1"></i> Filter</button>
                </div>
            </form>
        </div>
    </div>
</section>

<div class="container py-5">
    <!-- Value propositions for Land -->
    <div class="row g-3 mb-5">
        <div class="col-md-4">
            <div class="card p-3 border rounded-4 bg-white shadow-sm d-flex flex-row align-items-center gap-3">
                <div class="rounded-circle bg-success text-white p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                    <i class="bi bi-compass fs-4"></i>
                </div>
                <div>
                    <h6 class="brand-font mb-1">GPS Beacon Relocation</h6>
                    <small class="text-muted">Exact boundary coordinates verified by licensed geomatics engineers.</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3 border rounded-4 bg-white shadow-sm d-flex flex-row align-items-center gap-3">
                <div class="rounded-circle bg-primary text-white p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                    <i class="bi bi-file-earmark-check fs-4"></i>
                </div>
                <div>
                    <h6 class="brand-font mb-1">Title Deed Verification</h6>
                    <small class="text-muted">Clean registry due diligence to prevent multi-owner land conflicts.</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3 border rounded-4 bg-white shadow-sm d-flex flex-row align-items-center gap-3">
                <div class="rounded-circle bg-warning text-dark p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                    <i class="bi bi-calculator fs-4"></i>
                </div>
                <div>
                    <h6 class="brand-font mb-1">Flexible Installment Plans</h6>
                    <small class="text-muted">Direct purchase plans with zero interest options on selected estates.</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Listings Grid -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="brand-font mb-0">Available Surveyed Land Parcels</h3>
            <p class="text-muted small mb-0">Showing {{ $landListings->total() }} verified plots</p>
        </div>
        <a href="{{ route('public.services.land_survey') }}" class="btn btn-outline-success btn-sm rounded-pill fw-bold px-3">
            <i class="bi bi-compass me-1"></i> Need a Land Survey?
        </a>
    </div>

    <div class="row g-4">
        @forelse($landListings as $p)
            <div class="col-md-6 col-lg-3">
                @include('public.partials.land-card', ['p' => $p])
            </div>
        @empty
            <div class="col-12 text-center text-muted py-5">
                <h5>No land listings matching your filter criteria.</h5>
                <a href="{{ route('public.land') }}" class="btn btn-success btn-sm mt-2">Reset Filters</a>
            </div>
        @endforelse
    </div>

    <div class="mt-4 d-flex justify-content-center">
        {{ $landListings->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
