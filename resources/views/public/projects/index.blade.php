@extends('layouts.public')

@section('title', 'Land Projects & Plot Maps - TAUSI Style Directory')

@section('content')
<!-- Header Banner (TAUSI Style) -->
<section class="py-4 bg-primary text-white position-relative" style="background: linear-gradient(135deg, #0b4596 0%, #1e3a8a 100%);">
    <div class="container py-2">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <a href="{{ route('public.home') }}" class="text-white-50 text-decoration-none small"><i class="bi bi-arrow-left me-1"></i> Back to Home</a>
                <h2 class="fw-bold text-white mb-0 mt-1">Land Projects Directory</h2>
            </div>
            <span class="badge bg-white text-primary px-3 py-2 rounded-pill fw-bold">
                <i class="bi bi-map me-1"></i> Shapefile Enabled Maps
            </span>
        </div>
    </div>
</section>

<!-- Filter & Search Bar (TAUSI Image 1 Header) -->
<section class="py-3 bg-light border-bottom">
    <div class="container">
        <form action="{{ route('public.projects') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-8">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Halmashauri / Region / Project Name..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <select name="region" class="form-select" onchange="this.form.submit()">
                    <option value="">All Regions / Councils</option>
                    @foreach($regions as $reg)
                        <option value="{{ $reg }}" {{ request('region') == $reg ? 'selected' : '' }}>{{ $reg }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel"></i></button>
            </div>
        </form>
    </div>
</section>

<!-- Project Cards List (Exact TAUSI Image 1 Layout) -->
<section class="py-5 bg-white">
    <div class="container">
        <div class="row g-4 justify-content-center">
            @forelse($projects as $project)
                <div class="col-lg-10">
                    <div class="card border rounded-4 shadow-sm overflow-hidden bg-white hover-shadow transition-all">
                        <!-- Card Header (TAUSI Image 1 style) -->
                        <div class="card-header bg-primary bg-opacity-10 py-3 px-4 d-flex align-items-center justify-content-between border-0">
                            <div>
                                <h4 class="fw-bold text-primary mb-0 fs-5 text-uppercase" style="letter-spacing: 0.02em;">{{ $project->title }}</h4>
                                <small class="text-muted fw-semibold">
                                    <i class="bi bi-geo-alt-fill text-danger me-1"></i>{{ $project->council ?? $project->district ?? $project->location_name }}
                                </small>
                            </div>
                            <span class="badge {{ $project->available_units > 0 ? 'bg-success' : 'bg-danger' }} px-3 py-2 rounded-pill fw-bold">
                                {{ $project->available_units > 0 ? 'Available' : 'Sold-out' }}
                            </span>
                        </div>

                        <!-- Card Body (TAUSI Image 1 style) -->
                        <div class="card-body p-4">
                            <h6 class="fw-bold text-dark mb-1">Project Description</h6>
                            <p class="text-muted small mb-4">{{ $project->description ?: 'Mradi upo eneo zuri la kiuchumi, una huduma za barabara, maji na umeme.' }}</p>

                            <!-- 5 Metrics Bar (TAUSI exact table metrics) -->
                            <div class="row text-center g-2 py-3 px-2 bg-light rounded-3 border mb-3">
                                <div class="col">
                                    <span class="d-block text-muted text-uppercase" style="font-size: 0.68rem; font-weight: 700;">Number of plots</span>
                                    <strong class="text-primary fs-5">{{ $project->total_units }}</strong>
                                </div>
                                <div class="col border-start border-end">
                                    <span class="d-block text-muted text-uppercase" style="font-size: 0.68rem; font-weight: 700;">Sold</span>
                                    <strong class="text-danger fs-5">{{ $project->sold_plots }}</strong>
                                </div>
                                <div class="col border-end">
                                    <span class="d-block text-muted text-uppercase" style="font-size: 0.68rem; font-weight: 700;">Available</span>
                                    <strong class="text-success fs-5">{{ $project->available_units }}</strong>
                                </div>
                                <div class="col border-end">
                                    <span class="d-block text-muted text-uppercase" style="font-size: 0.68rem; font-weight: 700;">Grace Period</span>
                                    <strong class="text-dark fs-6">{{ $project->grace_period_days }} Days</strong>
                                </div>
                                <div class="col">
                                    <span class="d-block text-muted text-uppercase" style="font-size: 0.68rem; font-weight: 700;">Initial Payment</span>
                                    <strong class="text-primary fs-6">{{ (int)$project->initial_payment_percent }}%</strong>
                                </div>
                            </div>

                            <div class="d-flex align-items-center justify-content-between pt-2">
                                <div>
                                    <small class="text-muted d-block" style="font-size: 0.75rem;">Starting Price</small>
                                    <span class="fw-extrabold text-primary fs-5">TZS {{ number_format($project->starting_price) }}</span>
                                </div>
                                <a href="{{ route('public.projects.show', $project->slug) }}" class="btn btn-outline-primary btn-lg rounded-circle p-0 d-flex align-items-center justify-content-center shadow-sm" style="width: 44px; height: 44px;" title="View Plot Map & Shapes">
                                    <i class="bi bi-chevron-right fs-5"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <i class="bi bi-map fs-1 text-muted d-block mb-2"></i>
                    <h5 class="fw-bold text-dark">No Projects Found</h5>
                    <p class="text-muted">Try adjusting your search query or region filter.</p>
                </div>
            @endforelse

            <div class="col-12 d-flex justify-content-center mt-4">
                {{ $projects->links() }}
            </div>
        </div>
    </div>
</section>
@endsection
