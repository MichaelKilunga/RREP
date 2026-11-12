@extends('layouts.app')

@section('title', __('ops.report_projects_short'))

@section('content')
@php
    $chartProjects = $land->filter(fn ($p) => $p->plots_available + $p->plots_reserved + $p->plots_sold > 0)->values();
@endphp

@include('reports.partials.header', ['title' => __('ops.report_projects_heading'), 'icon' => 'bi-map', 'routeName' => 'reports.projects'])

<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl-2"><div class="kpi-card"><div class="kpi-label">{{ __('app.active_projects') }}</div><div class="kpi-value">{{ $totals['projects'] }}</div></div></div>
    <div class="col-6 col-md-4 col-xl-2"><div class="kpi-card kpi-success"><div class="kpi-label">{{ __('app.plots_available') }}</div><div class="kpi-value">{{ $totals['plots_available'] }} <span class="text-muted fs-6">/ {{ $totals['plots_total'] }}</span></div></div></div>
    <div class="col-6 col-md-4 col-xl-2"><div class="kpi-card kpi-primary"><div class="kpi-label">{{ __('app.sold') }}</div><div class="kpi-value">{{ $totals['plots_sold'] }}</div><small class="text-muted">{{ $totals['plots_total'] ? round($totals['plots_sold'] / $totals['plots_total'] * 100) : 0 }}% {{ __('ops.of_all_plots') }}</small></div></div>
    <div class="col-6 col-md-4 col-xl-2"><div class="kpi-card kpi-warning"><div class="kpi-label">{{ __('ops.active_plot_holds') }}</div><div class="kpi-value">{{ $totals['active_holds'] }}</div></div></div>
    <div class="col-6 col-md-4 col-xl-2"><div class="kpi-card kpi-success"><div class="kpi-label">{{ __('ops.value_sold') }}</div><div class="kpi-value" title="{{ format_currency($totals['value_sold'], null, 0) }}">{{ format_money_short($totals['value_sold']) }}</div></div></div>
    <div class="col-6 col-md-4 col-xl-2"><div class="kpi-card kpi-info"><div class="kpi-label">{{ __('ops.value_for_sale') }}</div><div class="kpi-value" title="{{ format_currency($totals['value_available'], null, 0) }}">{{ format_money_short($totals['value_available']) }}</div><small class="text-muted">{{ __('ops.priced_available_plots') }}</small></div></div>
</div>

@if($chartProjects->isNotEmpty())
    <div class="card mb-4">
        <div class="card-header brand-font">{{ __('ops.plot_status_by_project') }}</div>
        <div class="card-body">
            <div style="position: relative; height: {{ max(160, $chartProjects->count() * 40 + 60) }}px;">
                <canvas id="plotStatusChart" role="img" aria-label="{{ __('ops.plot_status_by_project') }}"></canvas>
            </div>
        </div>
    </div>
@endif

<div class="card">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <span class="brand-font">{{ __('app.projects') }}</span>
        <div class="d-print-none" style="max-width: 260px;">
            <label for="projectSearch" class="visually-hidden">{{ __('app.search') }}</label>
            <input type="search" id="projectSearch" class="form-control form-control-sm" placeholder="{{ __('ops.quick_filter_placeholder') }}" data-table-filter="#projectTable">
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover table-stack align-middle mb-0 small" id="projectTable" style="font-variant-numeric: tabular-nums;">
            <thead class="table-light">
                <tr>
                    <th>{{ __('admin.project') }}</th>
                    <th>{{ __('ops.report_col_category') }}</th>
                    <th>{{ __('app.location') }}</th>
                    <th class="text-end">{{ __('app.plots_available') }}</th>
                    <th class="text-end">{{ __('ops.reservation_status_active') }}</th>
                    <th class="text-end">{{ __('app.sold') }}</th>
                    <th class="text-end">{{ __('ops.value_sold') }}</th>
                    <th class="text-end">{{ __('ops.value_for_sale') }}</th>
                    <th>{{ __('admin.published') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($projects as $p)
                    @php $isLand = $p->project_category !== 'service'; @endphp
                    <tr>
                        <td class="stack-primary fw-bold">
                            @can('view_projects')<a href="{{ route('admin.projects.show', $p) }}">{{ $p->title }}</a>@else{{ $p->title }}@endcan
                            @if($p->service)<div class="text-muted small fw-normal">{{ $p->service->title }}</div>@endif
                            @if($p->project_status)<div class="text-muted small fw-normal">{{ $p->project_status }}</div>@endif
                        </td>
                        <td><span class="badge bg-light text-dark border">{{ $isLand ? __('pages.projects_land') : __('pages.projects_service') }}</span></td>
                        <td>{{ $p->location_label ?: '—' }}</td>
                        @if($isLand)
                            <td class="text-end text-success fw-bold">{{ $p->plots_available }}</td>
                            <td class="text-end">{{ $p->plots_reserved }}</td>
                            <td class="text-end">{{ $p->plots_sold }}</td>
                            <td class="text-end text-nowrap">{{ $p->value_sold > 0 ? format_money_short($p->value_sold) : '—' }}</td>
                            <td class="text-end text-nowrap">{{ $p->value_available > 0 ? format_money_short($p->value_available) : '—' }}</td>
                        @else
                            <td class="text-end">—</td><td class="text-end">—</td><td class="text-end">—</td><td class="text-end">—</td><td class="text-end">—</td>
                        @endif
                        <td>
                            <span class="badge {{ $p->is_published ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">
                                {{ $p->is_published ? __('admin.published') : __('admin.draft') }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="stack-no-label"><div class="empty-state"><div class="empty-icon"><i class="bi bi-map"></i></div><h6>{{ __('app.no_projects_found') }}</h6></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
@include('reports.partials.chart-kit')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const rows = {!! json_encode($chartProjects->map(fn ($p) => ['title' => $p->title, 'sold' => (int) $p->plots_sold, 'reserved' => (int) $p->plots_reserved, 'available' => (int) $p->plots_available])) !!};
    const names = {!! json_encode(['sold' => __('app.sold'), 'reserved' => __('ops.reservation_status_active'), 'available' => __('app.plots_available')]) !!};
    ReportCharts.bars(document.getElementById('plotStatusChart'), {
        labels: rows.map(r => r.title),
        horizontal: true,
        stacked: true,
        datasets: [
            { label: names.sold, data: rows.map(r => r.sold) },
            { label: names.reserved, data: rows.map(r => r.reserved) },
            { label: names.available, data: rows.map(r => r.available) },
        ],
    });
});
</script>
@endsection
