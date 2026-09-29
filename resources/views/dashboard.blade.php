@extends('layouts.app')

@section('title', __('app.dashboard'))

@section('content')
<!-- Hero System Admin Header Card -->
<div class="card border-0 text-white p-4 p-md-5 mb-4 rounded-4 shadow-sm" style="background: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 100%);">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <span class="badge bg-white bg-opacity-20 text-white rounded-pill px-3 py-1 mb-2 fw-semibold" style="letter-spacing: 0.05em; font-size: 0.72rem;">
                <i class="bi bi-shield-check me-1"></i> SYSTEM ADMIN CONSOLE
            </span>
            <h2 class="brand-font fw-bold mb-1 text-white">System & Operations Management</h2>
            <p class="text-white-50 mb-0 small" style="max-width: 650px;">
                Monitor, filter, activate, update, and manage all property listings, land survey projects, and customer inquiries.
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.properties.create') }}" class="btn btn-light rounded-pill px-4 py-2 fw-bold text-primary shadow-sm">
                <i class="bi bi-plus-circle me-1"></i> Add Property
            </a>
            <a href="{{ route('survey.index') }}" class="btn btn-outline-light rounded-pill px-3 py-2 fw-semibold">
                <i class="bi bi-compass me-1"></i> New Survey
            </a>
        </div>
    </div>
</div>

<!-- Alert Banner -->
<div class="alert alert-success border-0 bg-success-subtle text-success-emphasis rounded-3 p-3 mb-4 d-flex align-items-center justify-content-between shadow-sm">
    <div class="d-flex align-items-center gap-2">
        <i class="bi bi-check-circle-fill text-success fs-5"></i>
        <span class="fw-semibold">System operations and GIS mapping active. All services running smoothly.</span>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>

<!-- Metric Summary Cards Row -->
<div class="row g-3 mb-4">
    <!-- Total Properties -->
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card p-3 h-100 border-0 shadow-sm" style="border-top: 4px solid #2563eb !important;">
            <div class="text-muted text-uppercase fw-bold" style="font-size: 0.68rem; letter-spacing: 0.05em;">TOTAL PROPERTIES</div>
            <h3 class="fw-bold text-dark mb-0 mt-2">{{ $metrics['total_properties'] }}</h3>
        </div>
    </div>
    <!-- Active Listings -->
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card p-3 h-100 border-0 shadow-sm" style="border-top: 4px solid #16a34a !important;">
            <div class="text-muted text-uppercase fw-bold" style="font-size: 0.68rem; letter-spacing: 0.05em;">ACTIVE LISTINGS</div>
            <h3 class="fw-bold text-dark mb-0 mt-2">{{ $metrics['active_listings'] }}</h3>
        </div>
    </div>
    <!-- Pending Inquiries -->
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card p-3 h-100 border-0 shadow-sm" style="border-top: 4px solid #eab308 !important;">
            <div class="text-muted text-uppercase fw-bold" style="font-size: 0.68rem; letter-spacing: 0.05em;">PENDING LEADS</div>
            <h3 class="fw-bold text-dark mb-0 mt-2">{{ $metrics['total_leads'] }}</h3>
        </div>
    </div>
    <!-- Survey Projects -->
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card p-3 h-100 border-0 shadow-sm" style="border-top: 4px solid #dc2626 !important;">
            <div class="text-muted text-uppercase fw-bold" style="font-size: 0.68rem; letter-spacing: 0.05em;">SURVEY PROJECTS</div>
            <h3 class="fw-bold text-dark mb-0 mt-2">{{ $metrics['pending_reservations'] ?? 5 }}</h3>
        </div>
    </div>
    <!-- Active Deals -->
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card p-3 h-100 border-0 shadow-sm" style="border-top: 4px solid #06b6d4 !important;">
            <div class="text-muted text-uppercase fw-bold" style="font-size: 0.68rem; letter-spacing: 0.05em;">ACTIVE DEALS</div>
            <h3 class="fw-bold text-dark mb-0 mt-2">{{ $metrics['active_deals'] }}</h3>
        </div>
    </div>
    <!-- Revenue -->
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card p-3 h-100 border-0 shadow-sm" style="border-top: 4px solid #64748b !important;">
            <div class="text-muted text-uppercase fw-bold" style="font-size: 0.68rem; letter-spacing: 0.05em;">MONTHLY REVENUE</div>
            <h4 class="fw-bold text-dark mb-0 mt-2 text-truncate" style="font-size: 1.1rem;">{{ format_currency($metrics['revenue_this_month']) }}</h4>
        </div>
    </div>
</div>

<!-- Search & Filter Bar Card -->
<div class="card p-3 mb-4 border-0 shadow-sm bg-white">
    <div class="d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2 text-primary fw-bold">
            <i class="bi bi-funnel-fill"></i>
            <span>Filter & Search Records</span>
        </div>
        <button class="btn btn-outline-primary btn-sm rounded-pill px-3" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse">
            <i class="bi bi-sliders me-1"></i> Toggle Filters
        </button>
    </div>
    <div class="collapse mt-3" id="filterCollapse">
        <div class="row g-2 pt-2 border-top">
            <div class="col-md-4">
                <input type="text" class="form-control form-control-sm" placeholder="Search title, code, region...">
            </div>
            <div class="col-md-4">
                <select class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    <option value="Available">Available</option>
                    <option value="Reserved">Reserved</option>
                    <option value="Sold">Sold</option>
                </select>
            </div>
            <div class="col-md-4">
                <button type="button" class="btn btn-primary btn-sm w-100 fw-bold">Apply Filter</button>
            </div>
        </div>
    </div>
</div>

<!-- Clean Data List Container -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <h5 class="brand-font mb-0 text-dark">Properties & Land Projects List</h5>
        <span class="badge bg-light text-dark border">Total: {{ count($recentProperties) }} records</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Property Title</th>
                    <th>Type</th>
                    <th>Location</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentProperties as $index => $p)
                    <tr>
                        <td class="text-muted small">{{ $index + 1 }}</td>
                        <td>
                            <a href="{{ route('admin.properties.show', $p) }}" class="fw-bold text-dark text-decoration-none">
                                {{ Str::limit($p->title, 32) }}
                            </a>
                            <div class="text-muted small">{{ $p->property_code }}</div>
                        </td>
                        <td><span class="badge bg-light text-dark border">{{ $p->propertyType?->name ?? 'Land' }}</span></td>
                        <td>{{ $p->city }}</td>
                        <td class="fw-bold text-success">{{ $p->formatted_price }}</td>
                        <td><span class="badge badge-status-{{ strtolower($p->status) }}">{{ $p->status }}</span></td>
                        <td class="text-end">
                            <a href="{{ route('admin.properties.show', $p) }}" class="btn btn-sm btn-light border p-1 px-2" title="View"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('admin.properties.edit', $p) }}" class="btn btn-sm btn-light border p-1 px-2 text-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No records found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

