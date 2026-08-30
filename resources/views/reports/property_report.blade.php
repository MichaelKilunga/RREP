@extends('layouts.app')

@section('title', 'Property Inventory Report')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 d-print-none">
    <div>
        <h3 class="brand-font mb-1">Property Inventory & Valuation Report</h3>
        <p class="text-muted small mb-0">Total Properties: {{ $properties->count() }} &bull; Gross Asset Value: <strong>{{ format_currency($totalValuation) }}</strong></p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('reports.index') }}" class="btn btn-light border btn-sm"><i class="bi bi-arrow-left me-1"></i> Reports Center</a>
        <a href="?export=csv" class="btn btn-outline-success btn-sm"><i class="bi bi-file-earmark-excel me-1"></i> Export CSV</a>
        <button onclick="window.print()" class="btn btn-dark btn-sm"><i class="bi bi-printer me-1"></i> Print</button>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
            <thead class="table-light">
                <tr>
                    <th>Code</th>
                    <th>Property Title</th>
                    <th>Type</th>
                    <th>City</th>
                    <th>Listing</th>
                    <th>Status</th>
                    <th>Price / Valuation</th>
                    <th>Rent Rate</th>
                    <th>Owner</th>
                </tr>
            </thead>
            <tbody>
                @foreach($properties as $p)
                    <tr>
                        <td class="fw-bold text-primary">{{ $p->property_code }}</td>
                        <td class="fw-semibold">{{ $p->title }}</td>
                        <td>{{ $p->propertyType?->name }}</td>
                        <td>{{ $p->city }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $p->listing_type }}</span></td>
                        <td><span class="badge badge-status-{{ strtolower(str_replace(' ', '', $p->status)) }}">{{ $p->status }}</span></td>
                        <td class="fw-bold text-success">{{ format_currency($p->price) }}</td>
                        <td>{{ $p->rent_price ? format_currency($p->rent_price) : '-' }}</td>
                        <td>{{ $p->owner?->full_name ?? 'Company Asset' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
