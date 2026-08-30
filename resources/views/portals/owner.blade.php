@extends('layouts.app')

@section('title', 'Property Owner Portal')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="brand-font mb-1"><i class="bi bi-building-check text-primary me-2"></i>Landlord & Property Owner Portal (BM-013)</h3>
        <p class="text-muted small mb-0">Owner: <strong>{{ $owner->full_name }}</strong> ({{ $owner->company_name ?? 'Individual' }}) &bull; Bank: {{ $owner->bank_name ?? 'CRDB Bank' }}</p>
    </div>
</div>

<!-- Portfolio Assets -->
<div class="card mb-4">
    <div class="card-header brand-font d-flex justify-content-between align-items-center">
        <span>My Real Estate Assets</span>
        <span class="badge bg-primary">{{ $properties->count() }} Properties Listed</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
            <thead class="table-light">
                <tr>
                    <th>Code</th>
                    <th>Property Title</th>
                    <th>Type</th>
                    <th>Location</th>
                    <th>Listing</th>
                    <th>Valuation / Rent</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($properties as $pr)
                    <tr>
                        <td class="fw-bold text-primary">{{ $pr->property_code }}</td>
                        <td class="fw-semibold">{{ $pr->title }}</td>
                        <td>{{ $pr->propertyType?->name }}</td>
                        <td>{{ $pr->city }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $pr->listing_type }}</span></td>
                        <td class="fw-bold text-success">{{ $pr->formatted_price }}</td>
                        <td><span class="badge badge-status-{{ strtolower(str_replace(' ', '', $pr->status)) }}">{{ $pr->status }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-3">No properties registered under this owner.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Active Leases -->
<div class="card">
    <div class="card-header brand-font">Tenancy Leases & Remittances</div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
            <thead class="table-light">
                <tr>
                    <th>Lease #</th>
                    <th>Property</th>
                    <th>Tenant</th>
                    <th>Rent Rate</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($leases as $ls)
                    <tr>
                        <td class="fw-bold text-primary">{{ $ls->lease_number }}</td>
                        <td>{{ $ls->property?->title }}</td>
                        <td>{{ $ls->tenant?->customer?->full_name }}</td>
                        <td class="fw-bold text-success">{{ format_currency($ls->rent_amount) }} / mo</td>
                        <td><span class="badge bg-success-subtle text-success">{{ $ls->status }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-3">No active leases.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
