@extends('layouts.app')

@section('title', __('ops.report_agents_short'))

@section('content')
@php
    $totalVolume = $agents->sum('total_sales_volume');
    $totalEarned = $agents->sum(fn ($ag) => ($ag->total_sales_volume * $ag->commission_rate) / 100);
    $totalDeals = $agents->sum(fn ($ag) => $ag->salesDeals->count());
    $topAgents = $agents->sortByDesc('total_sales_volume')->take(8);
@endphp

<div class="page-header d-print-none">
    <div class="min-w-0">
        <h1 class="brand-font mb-1">{{ __('ops.report_agents_heading') }}</h1>
        <p class="text-muted small mb-0">{{ __('ops.report_generated_at', ['date' => now()->format('d M Y, H:i')]) }} EAT</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('reports.index') }}" class="btn btn-light border"><i class="bi bi-arrow-left me-1"></i> {{ __('ops.reports_center') }}</a>
        <button type="button" onclick="window.print()" class="btn btn-dark"><i class="bi bi-printer me-1"></i> {{ __('ops.print') }}</button>
    </div>
</div>

<h2 class="h5 brand-font d-none d-print-block mb-3">Agent Performance &amp; Commissions Report</h2>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-label">{{ __('ops.agents_count') }}</div>
            <div class="kpi-value">{{ $agents->count() }}</div>
            <span class="kpi-icon"><i class="bi bi-people"></i></span>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card kpi-success">
            <div class="kpi-label">{{ __('ops.total_sales_volume') }}</div>
            <div class="kpi-value text-success">{{ format_money_short($totalVolume) }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card kpi-warning">
            <div class="kpi-label">{{ __('ops.commission_earned') }}</div>
            <div class="kpi-value">{{ format_money_short($totalEarned) }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card kpi-info">
            <div class="kpi-label">{{ __('ops.closed_deals') }}</div>
            <div class="kpi-value">{{ $totalDeals }}</div>
        </div>
    </div>
</div>

<div class="card mb-4 d-print-none">
    <div class="card-body">
        <div class="filter-bar">
            <div>
                <label for="agentSearch" class="visually-hidden">{{ __('app.search') }}</label>
                <input type="search" id="agentSearch" class="form-control" placeholder="{{ __('ops.quick_filter_placeholder') }}" data-table-filter="#agentTable">
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header brand-font">{{ __('ops.sales_volume_by_agent') }}</div>
    <div class="card-body">
        <div class="ratio ratio-16x9"><canvas id="agentVolumeChart" role="img" aria-label="{{ __('ops.sales_volume_by_agent') }}"></canvas></div>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 table-stack small" id="agentTable">
            <thead class="table-light">
                <tr>
                    <th>{{ __('ops.agent') }}</th>
                    <th>{{ __('ops.license') }}</th>
                    <th>{{ __('ops.designation') }}</th>
                    <th>{{ __('ops.rate') }}</th>
                    <th>{{ __('ops.total_sales_volume') }}</th>
                    <th>{{ __('ops.closed_deals') }}</th>
                    <th>{{ __('ops.commission_earned') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($agents as $ag)
                    <tr>
                        <td class="stack-primary"><span class="fw-bold">{{ $ag->user?->name }}</span></td>
                        <td>{{ $ag->license_number ?? '-' }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $ag->designation }}</span></td>
                        <td>{{ $ag->commission_rate }}%</td>
                        <td class="fw-bold text-primary">{{ format_currency($ag->total_sales_volume) }}</td>
                        <td>{{ $ag->salesDeals->count() }}</td>
                        <td class="fw-bold text-success">{{ format_currency(($ag->total_sales_volume * $ag->commission_rate) / 100) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="stack-no-label">
                            <div class="empty-state">
                                <div class="empty-icon"><i class="bi bi-award"></i></div>
                                <h6>{{ __('ops.no_report_rows') }}</h6>
                                <p class="small mb-0">{{ __('ops.no_report_rows_hint') }}</p>
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
    const labels = @json($topAgents->map(fn ($ag) => $ag->user?->name ?? '-')->values());
    const values = @json($topAgents->map(fn ($ag) => (float) $ag->total_sales_volume)->values());
    const canvas = document.getElementById('agentVolumeChart');
    if (canvas && labels.length) {
        new Chart(canvas, {
            type: 'bar',
            data: { labels: labels, datasets: [{ label: 'TSh', data: values, backgroundColor: '#d97706', borderRadius: 6 }] },
            options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } },
                scales: { x: { ticks: { callback: function (v) { return v >= 1e9 ? (v / 1e9).toFixed(1) + 'B' : v >= 1e6 ? (v / 1e6).toFixed(0) + 'M' : v; } } } } }
        });
    }
});
</script>
@endsection
