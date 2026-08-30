@extends('layouts.app')

@section('title', 'Agent Commissions Report')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 d-print-none">
    <div>
        <h3 class="brand-font mb-1">Agent Performance & Commissions Report</h3>
        <p class="text-muted small mb-0">Track broker closing performance, commissions earned, and payout statements</p>
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
                    <th>Agent Name</th>
                    <th>License #</th>
                    <th>Designation</th>
                    <th>Rate</th>
                    <th>Total Sales Volume</th>
                    <th>Closed Deals</th>
                    <th>Total Commission Earned</th>
                </tr>
            </thead>
            <tbody>
                @foreach($agents as $ag)
                    <tr>
                        <td class="fw-bold">{{ $ag->user?->name }}</td>
                        <td>{{ $ag->license_number ?? '-' }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $ag->designation }}</span></td>
                        <td>{{ $ag->commission_rate }}%</td>
                        <td class="fw-bold text-primary">{{ format_currency($ag->total_sales_volume) }}</td>
                        <td>{{ $ag->salesDeals->count() }} Deals</td>
                        <td class="fw-bold text-success">{{ format_currency(($ag->total_sales_volume * $ag->commission_rate) / 100) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
