@extends('layouts.app')

@section('title', __('app.property_list'))

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h3 class="brand-font mb-1">{{ __('app.property_list') }}</h3>
        <p class="text-muted small mb-0">Total {{ $properties->total() }} real estate assets registered</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('properties.create') }}" class="btn btn-primary btn-sm d-flex align-items-center gap-1 shadow-sm">
            <i class="bi bi-plus-lg"></i> {{ __('app.add_property') }}
        </a>
    </div>
</div>

<!-- Filters Card -->
<div class="card mb-4 p-3 bg-white">
    <form action="{{ route('properties.index') }}" method="GET" class="row g-2 align-items-center">
        <div class="col-md-3">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search title, code, address..." value="{{ request('search') }}">
        </div>
        <div class="col-md-2">
            <select name="type" class="form-select form-select-sm">
                <option value="">All Types</option>
                @foreach($types as $t)
                    <option value="{{ $t->id }}" {{ request('type') == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select name="listing_type" class="form-select form-select-sm">
                <option value="">All Listings</option>
                <option value="Sale" {{ request('listing_type') == 'Sale' ? 'selected' : '' }}>Sale</option>
                <option value="Rent" {{ request('listing_type') == 'Rent' ? 'selected' : '' }}>Rent</option>
                <option value="Lease" {{ request('listing_type') == 'Lease' ? 'selected' : '' }}>Lease</option>
            </select>
        </div>
        <div class="col-md-2">
            <select name="status" class="form-select form-select-sm">
                <option value="">All Statuses</option>
                <option value="Available" {{ request('status') == 'Available' ? 'selected' : '' }}>Available</option>
                <option value="Reserved" {{ request('status') == 'Reserved' ? 'selected' : '' }}>Reserved</option>
                <option value="Under Contract" {{ request('status') == 'Under Contract' ? 'selected' : '' }}>Under Contract</option>
                <option value="Sold" {{ request('status') == 'Sold' ? 'selected' : '' }}>Sold</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-dark btn-sm w-100"><i class="bi bi-funnel me-1"></i> Filter</button>
        </div>
        <div class="col-md-1">
            <a href="{{ route('properties.index') }}" class="btn btn-light border btn-sm w-100">Reset</a>
        </div>
    </form>
</div>

<!-- Properties Table Card -->
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: 0.875rem;">
            <thead class="table-light">
                <tr>
                    <th style="width: 320px;">Property</th>
                    <th>Type</th>
                    <th>Listing</th>
                    <th>Price / Rate</th>
                    <th>Location</th>
                    <th>Units/Acreage</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($properties as $p)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-3 bg-light border d-flex align-items-center justify-content-center text-secondary" style="width: 48px; height: 48px;">
                                    <i class="bi bi-building fs-4 text-primary"></i>
                                </div>
                                <div>
                                    <a href="{{ route('properties.show', $p) }}" class="fw-bold text-dark text-decoration-none">
                                        {{ $p->title }}
                                    </a>
                                    <div class="text-muted small">{{ $p->property_code }} &bull; {{ $p->branch?->name }}</div>
                                </div>
                            </div>
                        </td>
                        <td><span class="badge bg-light text-dark border">{{ $p->propertyType?->name }}</span></td>
                        <td><span class="badge bg-secondary-subtle text-secondary">{{ $p->listing_type }}</span></td>
                        <td class="fw-bold text-success">{{ $p->formatted_price }}</td>
                        <td>{{ $p->city }}, {{ $p->address }}</td>
                        <td>
                            @if($p->landParcel)
                                <span class="badge bg-info-subtle text-info"><i class="bi bi-map me-1"></i>{{ $p->landParcel->acreage }} Acres</span>
                            @elseif($p->units->count() > 0)
                                <span class="badge bg-primary-subtle text-primary">{{ $p->units->count() }} Units</span>
                            @else
                                <span class="text-muted">{{ $p->area_size ? "{$p->area_size} {$p->area_unit}" : '-' }}</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-status-{{ strtolower(str_replace(' ', '', $p->status)) }}">{{ $p->status }}</span>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('properties.show', $p) }}" class="btn btn-light border" title="View Details"><i class="bi bi-eye"></i></a>
                                <a href="{{ route('properties.edit', $p) }}" class="btn btn-light border" title="Edit"><i class="bi bi-pencil"></i></a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">No properties matched your search query.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3 border-top d-flex justify-content-end">
        {{ $properties->links() }}
    </div>
</div>
@endsection
