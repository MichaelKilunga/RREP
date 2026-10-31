@extends('layouts.app')

@section('title', __('ops.report_customers_short'))

@section('content')
@include('reports.partials.header', ['title' => __('ops.report_customers_heading'), 'icon' => 'bi-people', 'routeName' => 'reports.customers'])

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3"><div class="kpi-card"><div class="kpi-label">{{ __('ops.new_customers') }}</div><div class="kpi-value">{{ $stats['new'] }}</div></div></div>
    <div class="col-6 col-md-3"><div class="kpi-card kpi-info"><div class="kpi-label">{{ __('ops.total_customers') }}</div><div class="kpi-value">{{ $stats['total'] }}</div><small class="text-muted">{{ __('ops.as_of_today') }}</small></div></div>
    <div class="col-6 col-md-3"><div class="kpi-card kpi-success"><div class="kpi-label">{{ __('ops.customers_with_holds') }}</div><div class="kpi-value">{{ $stats['with_holds'] }}</div></div></div>
    <div class="col-6 col-md-3"><div class="kpi-card kpi-warning"><div class="kpi-label">{{ __('ops.corporate_customers') }}</div><div class="kpi-value">{{ $stats['corporate'] }}</div></div></div>
</div>

<div class="row g-4 mb-4">
    <div class="col-12 col-xl-7">
        <div class="card h-100">
            <div class="card-header brand-font">{{ __('ops.new_customers_over_time') }}</div>
            <div class="card-body">
                <div style="position: relative; height: 240px;">
                    <canvas id="customersChart" role="img" aria-label="{{ __('ops.new_customers_over_time') }}"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-5">
        <div class="card h-100">
            <div class="card-header brand-font">{{ __('ops.top_payers') }}</div>
            <div class="list-group list-group-flush small">
                @forelse($topPayers as $payer)
                    <a href="{{ route('crm.customers.show', $payer) }}" class="list-group-item list-group-item-action d-flex justify-content-between gap-2">
                        <span class="text-truncate">{{ $payer->full_name }}<span class="text-muted d-block">{{ format_phone_tz($payer->phone) }}</span></span>
                        <span class="fw-bold text-success text-nowrap">{{ format_currency($payer->paid, null, 0) }}</span>
                    </a>
                @empty
                    <div class="list-group-item text-muted">{{ __('ops.no_payments') }}</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    @foreach([
        ['title' => __('ops.by_city'), 'rows' => $byRegion],
        ['title' => __('ops.by_source'), 'rows' => $bySource],
        ['title' => __('ops.by_kyc'), 'rows' => $byKyc],
    ] as $breakdown)
        <div class="col-12 col-md-4">
            <div class="card h-100 p-3">
                <h2 class="h6 brand-font mb-3">{{ $breakdown['title'] }}</h2>
                @forelse($breakdown['rows']->take(8) as $label => $count)
                    @php $share = $stats['new'] ? round($count / $stats['new'] * 100) : 0; @endphp
                    <div class="d-flex justify-content-between small mb-1"><span class="text-truncate me-2">{{ $label }}</span><span class="fw-semibold">{{ $count }}</span></div>
                    <div class="progress progress-thin rounded-pill mb-2" role="progressbar" aria-label="{{ $label }}" aria-valuenow="{{ $share }}" aria-valuemin="0" aria-valuemax="100">
                        <div class="progress-bar rounded-pill" style="width: {{ $share }}%; background: #2a78d6;"></div>
                    </div>
                @empty
                    <p class="text-muted small mb-0">{{ __('ops.no_report_rows') }}</p>
                @endforelse
            </div>
        </div>
    @endforeach
</div>

<div class="card">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <span class="brand-font">{{ __('ops.new_customers') }} <span class="badge bg-light text-dark border ms-1">{{ $customers->count() }}</span></span>
        <div class="d-print-none" style="max-width: 260px;">
            <label for="customerSearch" class="visually-hidden">{{ __('app.search') }}</label>
            <input type="search" id="customerSearch" class="form-control form-control-sm" placeholder="{{ __('ops.quick_filter_placeholder') }}" data-table-filter="#customerTable">
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover table-stack align-middle mb-0 small" id="customerTable">
            <thead class="table-light">
                <tr>
                    <th>{{ __('ops.customer') }}</th>
                    <th>{{ __('ops.by_city') }}</th>
                    <th>{{ __('ops.source') }}</th>
                    <th class="text-end">{{ __('ops.requests_title') }}</th>
                    <th class="text-end">{{ __('app.leads') }}</th>
                    <th class="text-end">{{ __('ops.holds_placed') }}</th>
                    <th class="text-end">{{ __('ops.collected') }}</th>
                    <th>{{ __('ops.date') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $customer)
                    <tr>
                        <td class="stack-primary">
                            <a href="{{ route('crm.customers.show', $customer) }}" class="fw-semibold">{{ $customer->full_name }}</a>
                            <small class="text-muted d-block">{{ format_phone_tz($customer->phone) }}</small>
                        </td>
                        <td>{{ $customer->city ?: '—' }}</td>
                        <td>{{ $customer->source ?: '—' }}</td>
                        <td class="text-end">{{ $customer->requests_count }}</td>
                        <td class="text-end">{{ $customer->leads_count }}</td>
                        <td class="text-end">{{ $customer->reservations_count }}</td>
                        <td class="text-end text-nowrap">{{ $customer->paid_total > 0 ? format_currency($customer->paid_total, null, 0) : '—' }}</td>
                        <td class="text-nowrap">{{ $customer->created_at?->format('d M Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="stack-no-label"><div class="empty-state"><div class="empty-icon"><i class="bi bi-people"></i></div><h6>{{ __('ops.no_report_rows') }}</h6><p class="small mb-0">{{ __('ops.no_report_rows_hint') }}</p></div></td></tr>
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
    ReportCharts.bars(document.getElementById('customersChart'), {
        labels: trend.labels,
        datasets: [{ label: {!! json_encode(__('ops.new_customers')) !!}, data: trend.series.customers }],
    });
});
</script>
@endsection
