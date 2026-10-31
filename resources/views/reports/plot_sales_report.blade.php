@extends('layouts.app')

@section('title', __('ops.report_plot_sales_short'))

@section('content')
@php
    $statusTone = ['Active' => 'warning', 'Converted' => 'success', 'Expired' => 'secondary', 'Cancelled' => 'danger'];
@endphp

@include('reports.partials.header', ['title' => __('ops.report_plot_sales_heading'), 'icon' => 'bi-bookmark-check', 'routeName' => 'reports.plot_sales'])

<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl-2"><div class="kpi-card"><div class="kpi-label">{{ __('ops.holds_placed') }}</div><div class="kpi-value">{{ $stats['holds'] }}</div></div></div>
    <div class="col-6 col-md-4 col-xl-2"><div class="kpi-card kpi-success"><div class="kpi-label">{{ __('ops.plots_sold_period') }}</div><div class="kpi-value">{{ $stats['sales'] }}</div></div></div>
    <div class="col-6 col-md-4 col-xl-2"><div class="kpi-card kpi-info"><div class="kpi-label">{{ __('ops.conversion_rate') }}</div><div class="kpi-value">{{ $stats['conversion'] }}%</div><small class="text-muted">{{ __('ops.holds_to_sales_hint') }}</small></div></div>
    <div class="col-6 col-md-4 col-xl-2"><div class="kpi-card kpi-warning"><div class="kpi-label">{{ __('ops.expired_cancelled') }}</div><div class="kpi-value">{{ $stats['expired'] }} / {{ $stats['cancelled'] }}</div></div></div>
    <div class="col-6 col-md-4 col-xl-2"><div class="kpi-card"><div class="kpi-label">{{ __('ops.deposits_taken') }}</div><div class="kpi-value" title="{{ format_currency($stats['deposits'], null, 0) }}">{{ format_money_short($stats['deposits']) }}</div></div></div>
    <div class="col-6 col-md-4 col-xl-2"><div class="kpi-card kpi-success"><div class="kpi-label">{{ __('ops.sales_value') }}</div><div class="kpi-value" title="{{ format_currency($stats['sales_value'], null, 0) }}">{{ format_money_short($stats['sales_value']) }}</div></div></div>
</div>

<div class="row g-4 mb-4">
    <div class="col-12 col-xl-7">
        <div class="card h-100">
            <div class="card-header brand-font">{{ __('ops.holds_vs_sales') }}</div>
            <div class="card-body">
                <div style="position: relative; height: 260px;">
                    <canvas id="holdsChart" role="img" aria-label="{{ __('ops.holds_vs_sales') }}"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-5">
        <div class="card h-100">
            <div class="card-header brand-font">{{ __('ops.by_project') }}</div>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0 small" style="font-variant-numeric: tabular-nums;">
                    <thead class="table-light"><tr><th>{{ __('admin.project') }}</th><th class="text-end">{{ __('ops.holds_placed') }}</th><th class="text-end">{{ __('app.sold') }}</th><th class="text-end">{{ __('ops.deposits_taken') }}</th></tr></thead>
                    <tbody>
                        @forelse($byProject as $row)
                            <tr>
                                <td class="fw-semibold">{{ $row['name'] }}</td>
                                <td class="text-end">{{ $row['holds'] }}</td>
                                <td class="text-end text-success fw-bold">{{ $row['sold'] }}</td>
                                <td class="text-end">{{ format_money_short($row['deposits']) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-muted">{{ __('ops.no_report_rows') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <span class="brand-font">{{ __('ops.reservations_in_period') }} <span class="badge bg-light text-dark border ms-1">{{ $holds->count() }}</span></span>
        <div class="d-print-none" style="max-width: 260px;">
            <label for="holdSearch" class="visually-hidden">{{ __('app.search') }}</label>
            <input type="search" id="holdSearch" class="form-control form-control-sm" placeholder="{{ __('ops.quick_filter_placeholder') }}" data-table-filter="#holdTable">
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover table-stack align-middle mb-0 small" id="holdTable">
            <thead class="table-light">
                <tr>
                    <th>{{ __('ops.reservation') }}</th>
                    <th>{{ __('ops.customer') }}</th>
                    <th>{{ __('ops.plot_or_property') }}</th>
                    <th class="text-end">{{ __('ops.deposits_taken') }}</th>
                    <th>{{ __('ops.date') }}</th>
                    <th>{{ __('app.status') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($holds as $hold)
                    <tr>
                        <td class="stack-primary fw-semibold">{{ $hold->reservation_number }}</td>
                        <td>
                            @if($hold->customer)
                                <a href="{{ route('crm.customers.show', $hold->customer) }}" class="fw-semibold">{{ $hold->customer->full_name }}</a>
                                <small class="text-muted d-block">{{ format_phone_tz($hold->customer->phone) }}</small>
                            @else — @endif
                        </td>
                        <td>
                            @if($hold->landParcel)
                                {{ __('ops.plot_label', ['number' => $hold->landParcel->parcel_number]) }}@if($hold->landParcel->block_number) · {{ $hold->landParcel->block_number }}@endif
                                <small class="text-muted d-block">{{ $hold->landParcel->project?->title }}</small>
                            @else
                                {{ $hold->property?->title ?? '—' }}
                            @endif
                        </td>
                        <td class="text-end text-nowrap">{{ $hold->deposit_paid > 0 ? format_currency($hold->deposit_paid, null, 0) : '—' }}</td>
                        <td class="text-nowrap">{{ $hold->created_at?->format('d M Y') }}<small class="text-muted d-block">{{ __('ops.until_date', ['date' => $hold->reserved_until?->format('d M Y') ?? '—']) }}</small></td>
                        <td><span class="badge bg-{{ $statusTone[$hold->status] ?? 'secondary' }}-subtle text-{{ $statusTone[$hold->status] ?? 'secondary' }}-emphasis">{{ __('ops.reservation_status_'.strtolower($hold->status)) }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="stack-no-label"><div class="empty-state"><div class="empty-icon"><i class="bi bi-bookmark"></i></div><h6>{{ __('ops.no_report_rows') }}</h6><p class="small mb-0">{{ __('ops.no_report_rows_hint') }}</p></div></td></tr>
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
    const trend = {!! json_encode($series) !!};
    const names = {!! json_encode(['holds' => __('ops.holds_placed'), 'sales' => __('ops.plots_sold_period')]) !!};
    ReportCharts.bars(document.getElementById('holdsChart'), {
        labels: trend.labels,
        datasets: [
            { label: names.holds, data: trend.series.holds },
            { label: names.sales, data: trend.series.sales },
        ],
    });
});
</script>
@endsection
