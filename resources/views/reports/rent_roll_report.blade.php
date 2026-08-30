@extends('layouts.app')

@section('title', 'Rent Roll Report')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 d-print-none">
    <div>
        <h3 class="brand-font mb-1">Rent Roll & Lease Arrears Report</h3>
        <p class="text-muted small mb-0">Total Active Monthly Rent Roll: <strong>{{ format_currency($totalMonthlyRent) }}</strong></p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('reports.index') }}" class="btn btn-light border btn-sm"><i class="bi bi-arrow-left me-1"></i> Reports Center</a>
        <button onclick="window.print()" class="btn btn-dark btn-sm"><i class="bi bi-printer me-1"></i> Print</button>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
            <thead class="table-light">
                <tr>
                    <th>Lease #</th>
                    <th>Property / Unit</th>
                    <th>Tenant</th>
                    <th>Monthly Rent</th>
                    <th>Deposit Held</th>
                    <th>Cycle</th>
                    <th>Period</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($leases as $ls)
                    <tr>
                        <td class="fw-bold text-primary">{{ $ls->lease_number }}</td>
                        <td class="fw-semibold">{{ $ls->property?->title }}</td>
                        <td>{{ $ls->tenant?->customer?->full_name }}</td>
                        <td class="fw-bold text-success">{{ format_currency($ls->rent_amount) }}</td>
                        <td>{{ format_currency($ls->deposit_amount) }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $ls->payment_cycle }}</span></td>
                        <td>{{ $ls->start_date->format('d M Y') }} &rarr; {{ $ls->end_date->format('d M Y') }}</td>
                        <td><span class="badge bg-success-subtle text-success">{{ $ls->status }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">No lease records listed.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
