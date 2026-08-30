@extends('layouts.app')

@section('title', 'Sales Revenue Report')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 d-print-none">
    <div>
        <h3 class="brand-font mb-1">Sales Revenue & Collections Report</h3>
        <p class="text-muted small mb-0">Total Sales: <strong>{{ format_currency($totalSalesValue) }}</strong> &bull; Total Commissions: <strong>{{ format_currency($totalCommissions) }}</strong></p>
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
                    <th>Deal #</th>
                    <th>Property</th>
                    <th>Buyer</th>
                    <th>Closing Agent</th>
                    <th>Contract Price</th>
                    <th>Plan</th>
                    <th>Commission</th>
                    <th>Status</th>
                    <th>Agreement Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($deals as $d)
                    <tr>
                        <td class="fw-bold text-primary">{{ $d->deal_number }}</td>
                        <td class="fw-semibold">{{ $d->property?->title }}</td>
                        <td>{{ $d->customer?->full_name }}</td>
                        <td>{{ $d->agent?->user?->name ?? 'Direct' }}</td>
                        <td class="fw-bold text-success">{{ format_currency($d->sale_price) }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $d->payment_plan_type }} ({{ $d->total_installments }}x)</span></td>
                        <td>{{ format_currency($d->commission_amount) }}</td>
                        <td><span class="badge badge-status-{{ strtolower($d->status) }}">{{ $d->status }}</span></td>
                        <td>{{ $d->agreement_date->format('d M Y') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
