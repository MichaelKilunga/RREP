@extends('layouts.app')

@section('title', __('ops.report_sales_short'))

@section('content')
@php
    $dealBadge = ['Draft' => 'inactive', 'Pending Approval' => 'pending', 'Active' => 'active', 'Completed' => 'paid', 'Defaulted' => 'overdue', 'Terminated' => 'cancelled'];
    $totalCollected = $deals->sum(fn ($d) => $d->invoices->sum('paid_amount'));
    $outstanding = $deals->sum(fn ($d) => $d->invoices->sum('balance_due'));
    $monthly = $deals->groupBy(fn ($d) => $d->agreement_date->format('Y-m'))->map(fn ($g) => (float) $g->sum('sale_price'))->sortKeys()->take(-12);
    $byPlan = $deals->groupBy('payment_plan_type')->map->count();
@endphp

<div class="page-header d-print-none">
    <div class="min-w-0">
        <h1 class="brand-font mb-1">{{ __('ops.report_sales_heading') }}</h1>
        <p class="text-muted small mb-0">{{ __('ops.report_generated_at', ['date' => now()->format('d M Y, H:i')]) }} EAT</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('reports.index') }}" class="btn btn-light border"><i class="bi bi-arrow-left me-1"></i> {{ __('ops.reports_center') }}</a>
        <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}" class="btn btn-outline-success"><i class="bi bi-file-earmark-excel me-1"></i> {{ __('ops.export_csv') }}</a>
        <button type="button" onclick="window.print()" class="btn btn-dark"><i class="bi bi-printer me-1"></i> {{ __('ops.print') }}</button>
    </div>
</div>

<h2 class="h5 brand-font d-none d-print-block mb-3">Sales Revenue &amp; Collections Report</h2>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="kpi-card kpi-success">
            <div class="kpi-label">{{ __('ops.total_sales') }}</div>
            <div class="kpi-value text-success">{{ format_money_short($totalSalesValue) }}</div>
            <span class="kpi-icon"><i class="bi bi-cash-stack"></i></span>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-label">{{ __('ops.collected') }}</div>
            <div class="kpi-value">{{ format_money_short($totalCollected) }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card kpi-danger">
            <div class="kpi-label">{{ __('ops.outstanding') }}</div>
            <div class="kpi-value text-danger">{{ format_money_short($outstanding) }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card kpi-warning">
            <div class="kpi-label">{{ __('ops.total_commissions') }}</div>
            <div class="kpi-value">{{ format_money_short($totalCommissions) }}</div>
        </div>
    </div>
</div>

<div class="card mb-4 d-print-none">
    <div class="card-body">
        <div class="filter-bar">
            <div>
                <label for="salesSearch" class="visually-hidden">{{ __('app.search') }}</label>
                <input type="search" id="salesSearch" class="form-control" placeholder="{{ __('ops.quick_filter_placeholder') }}" data-table-filter="#salesTable">
            </div>
            <div class="filter-actions">
                <span class="text-muted small align-self-center"><span data-filter-count="#salesTable">{{ $deals->count() }}</span> {{ __('ops.rows') }}</span>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-12 col-lg-8">
        <div class="card h-100">
            <div class="card-header brand-font">{{ __('ops.monthly_sales') }}</div>
            <div class="card-body">
                <div class="ratio ratio-16x9"><canvas id="salesMonthlyChart" role="img" aria-label="{{ __('ops.monthly_sales') }}"></canvas></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-4">
        <div class="card h-100">
            <div class="card-header brand-font">{{ __('ops.deals_by_plan') }}</div>
            <div class="card-body">
                <div class="ratio ratio-4x3"><canvas id="salesPlanChart" role="img" aria-label="{{ __('ops.deals_by_plan') }}"></canvas></div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 table-stack small" id="salesTable">
            <thead class="table-light">
                <tr>
                    <th>{{ __('ops.deal_no') }}</th>
                    <th>{{ __('ops.property') }}</th>
                    <th>{{ __('ops.buyer') }}</th>
                    <th>{{ __('ops.closing_agent') }}</th>
                    <th>{{ __('ops.contract_price') }}</th>
                    <th>{{ __('ops.payment_plan') }}</th>
                    <th>{{ __('ops.commission') }}</th>
                    <th>{{ __('app.status') }}</th>
                    <th>{{ __('ops.agreement_date') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($deals as $d)
                    <tr>
                        <td class="stack-primary"><span class="fw-bold text-primary">{{ $d->deal_number }}</span></td>
                        <td class="fw-semibold">{{ $d->property?->title }}</td>
                        <td>{{ $d->customer?->full_name }}</td>
                        <td>{{ $d->agent?->user?->name ?? __('ops.direct_sale') }}</td>
                        <td class="fw-bold text-success">{{ format_currency($d->sale_price) }}</td>
                        <td><span class="badge bg-light text-dark border">{{ __('ops.plan_'.strtolower($d->payment_plan_type)) }} ({{ $d->total_installments }}x)</span></td>
                        <td>{{ format_currency($d->commission_amount) }}</td>
                        <td><span class="badge badge-status-{{ $dealBadge[$d->status] ?? strtolower(str_replace(' ', '', $d->status)) }}">{{ $d->status }}</span></td>
                        <td>{{ $d->agreement_date->format('d M Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="stack-no-label">
                            <div class="empty-state">
                                <div class="empty-icon"><i class="bi bi-cash-stack"></i></div>
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
        const counter = document.querySelector('[data-filter-count]');
        input.addEventListener('input', function () {
            const q = input.value.toLowerCase().trim();
            let visible = 0;
            table.querySelectorAll('tbody tr').forEach(function (row) {
                const show = !q || row.textContent.toLowerCase().includes(q);
                row.classList.toggle('d-none', !show);
                if (show) { visible++; }
            });
            if (counter) { counter.textContent = visible; }
        });
    }

    if (!window.Chart) { return; }
    const monthly = @json($monthly);
    const byPlan = @json($byPlan);
    const palette = ['#0f52ba', '#10b981', '#d97706', '#4338ca'];
    const monthlyCanvas = document.getElementById('salesMonthlyChart');
    const planCanvas = document.getElementById('salesPlanChart');

    if (monthlyCanvas && Object.keys(monthly).length) {
        new Chart(monthlyCanvas, {
            type: 'bar',
            data: { labels: Object.keys(monthly), datasets: [{ label: 'TSh', data: Object.values(monthly), backgroundColor: palette[1], borderRadius: 6 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } },
                scales: { y: { ticks: { callback: function (v) { return v >= 1e9 ? (v / 1e9).toFixed(1) + 'B' : v >= 1e6 ? (v / 1e6).toFixed(0) + 'M' : v; } } } } }
        });
    }
    if (planCanvas && Object.keys(byPlan).length) {
        new Chart(planCanvas, {
            type: 'doughnut',
            data: { labels: Object.keys(byPlan), datasets: [{ data: Object.values(byPlan), backgroundColor: palette }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
        });
    }
});
</script>
@endsection
