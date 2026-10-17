@extends('layouts.app')

@section('title', __('ops.report_rent_short'))

@section('content')
@php
    $leaseBadge = ['Active' => 'active', 'Expired' => 'overdue', 'Terminated' => 'cancelled', 'Pending' => 'pending', 'Draft' => 'inactive'];
    $activeLeases = $leases->where('status', 'Active')->count();
    $depositsHeld = $leases->where('status', 'Active')->sum('deposit_amount');
    $byStatus = $leases->groupBy('status')->map->count();
@endphp

<div class="page-header d-print-none">
    <div class="min-w-0">
        <h1 class="brand-font mb-1">{{ __('ops.report_rent_heading') }}</h1>
        <p class="text-muted small mb-0">{{ __('ops.report_generated_at', ['date' => now()->format('d M Y, H:i')]) }} EAT</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('reports.index') }}" class="btn btn-light border"><i class="bi bi-arrow-left me-1"></i> {{ __('ops.reports_center') }}</a>
        <button type="button" onclick="window.print()" class="btn btn-dark"><i class="bi bi-printer me-1"></i> {{ __('ops.print') }}</button>
    </div>
</div>

<h2 class="h5 brand-font d-none d-print-block mb-3">Rent Roll &amp; Lease Arrears Report</h2>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="kpi-card kpi-success">
            <div class="kpi-label">{{ __('ops.monthly_rent_roll') }}</div>
            <div class="kpi-value text-success">{{ format_money_short($totalMonthlyRent) }}</div>
            <span class="kpi-icon"><i class="bi bi-key"></i></span>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-label">{{ __('ops.active_leases') }}</div>
            <div class="kpi-value">{{ $activeLeases }} / {{ $leases->count() }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card kpi-info">
            <div class="kpi-label">{{ __('ops.deposits_held') }}</div>
            <div class="kpi-value">{{ format_money_short($depositsHeld) }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card kpi-warning">
            <div class="kpi-label">{{ __('ops.annualised_rent') }}</div>
            <div class="kpi-value">{{ format_money_short($totalMonthlyRent * 12) }}</div>
        </div>
    </div>
</div>

<div class="card mb-4 d-print-none">
    <div class="card-body">
        <div class="filter-bar">
            <div>
                <label for="rentSearch" class="visually-hidden">{{ __('app.search') }}</label>
                <input type="search" id="rentSearch" class="form-control" placeholder="{{ __('ops.quick_filter_placeholder') }}" data-table-filter="#rentTable">
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-12 col-lg-5">
        <div class="card h-100">
            <div class="card-header brand-font">{{ __('ops.leases_by_status') }}</div>
            <div class="card-body">
                <div class="ratio ratio-4x3"><canvas id="leaseStatusChart" role="img" aria-label="{{ __('ops.leases_by_status') }}"></canvas></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-7">
        <div class="card h-100">
            <div class="card-header brand-font">{{ __('ops.rent_by_property') }}</div>
            <div class="card-body">
                <div class="ratio ratio-16x9"><canvas id="rentPropertyChart" role="img" aria-label="{{ __('ops.rent_by_property') }}"></canvas></div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 table-stack small" id="rentTable">
            <thead class="table-light">
                <tr>
                    <th>{{ __('ops.lease_no') }}</th>
                    <th>{{ __('ops.property_unit') }}</th>
                    <th>{{ __('ops.tenant') }}</th>
                    <th>{{ __('ops.monthly_rent') }}</th>
                    <th>{{ __('ops.deposit_held') }}</th>
                    <th>{{ __('ops.cycle') }}</th>
                    <th>{{ __('ops.period') }}</th>
                    <th>{{ __('app.status') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($leases as $ls)
                    <tr>
                        <td class="stack-primary"><span class="fw-bold text-primary">{{ $ls->lease_number }}</span></td>
                        <td class="fw-semibold">{{ $ls->property?->title }}</td>
                        <td>{{ $ls->tenant?->customer?->full_name }}</td>
                        <td class="fw-bold text-success">{{ format_currency($ls->rent_amount) }}</td>
                        <td>{{ format_currency($ls->deposit_amount) }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $ls->payment_cycle }}</span></td>
                        <td>{{ $ls->start_date->format('d M Y') }} &rarr; {{ $ls->end_date->format('d M Y') }}</td>
                        <td><span class="badge badge-status-{{ $leaseBadge[$ls->status] ?? strtolower((string) $ls->status) }}">{{ $ls->status }}</span></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="stack-no-label">
                            <div class="empty-state">
                                <div class="empty-icon"><i class="bi bi-key"></i></div>
                                <h6>{{ __('ops.no_leases') }}</h6>
                                <p class="small mb-0">{{ __('ops.no_leases_hint') }}</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.querySelector('[data-table-filter]');
    if (input) {
        const table = document.querySelector(input.dataset.tableFilter);
        input.addEventListener('input', function () {
            const q = input.value.toLowerCase().trim();
            table.querySelectorAll('tbody tr').forEach(function (row) {
                row.classList.toggle('d-none', q !== '' && !row.textContent.toLowerCase().includes(q));
            });
        });
    }

    if (!window.Chart) { return; }
    const byStatus = @json($byStatus);
    const byProperty = @json($leases->where('status', 'Active')->groupBy(fn ($l) => $l->property?->title ?? '-')->map(fn ($g) => (float) $g->sum('rent_amount'))->sortDesc()->take(8));
    const palette = ['#16a34a', '#d97706', '#dc2626', '#0284c7', '#475569'];
    const statusCanvas = document.getElementById('leaseStatusChart');
    const propertyCanvas = document.getElementById('rentPropertyChart');

    if (statusCanvas && Object.keys(byStatus).length) {
        new Chart(statusCanvas, {
            type: 'doughnut',
            data: { labels: Object.keys(byStatus), datasets: [{ data: Object.values(byStatus), backgroundColor: palette }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
        });
    }
    if (propertyCanvas && Object.keys(byProperty).length) {
        new Chart(propertyCanvas, {
            type: 'bar',
            data: { labels: Object.keys(byProperty), datasets: [{ label: 'TSh', data: Object.values(byProperty), backgroundColor: '#0284c7', borderRadius: 6 }] },
            options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } },
                scales: { x: { ticks: { callback: function (v) { return v >= 1e6 ? (v / 1e6).toFixed(1) + 'M' : v >= 1e3 ? (v / 1e3).toFixed(0) + 'K' : v; } } } } }
        });
    }
});
</script>
@endsection
