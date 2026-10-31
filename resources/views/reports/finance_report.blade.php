@extends('layouts.app')

@section('title', __('ops.report_finance_short'))

@section('content')
@include('reports.partials.header', ['title' => __('ops.report_finance_heading'), 'icon' => 'bi-cash-coin', 'routeName' => 'reports.finance'])

<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl-2"><div class="kpi-card"><div class="kpi-label">{{ __('ops.invoiced') }}</div><div class="kpi-value" title="{{ format_currency($stats['invoiced'], null, 0) }}">{{ format_money_short($stats['invoiced']) }}</div></div></div>
    <div class="col-6 col-md-4 col-xl-2"><div class="kpi-card kpi-success"><div class="kpi-label">{{ __('ops.collected') }}</div><div class="kpi-value" title="{{ format_currency($stats['collected'], null, 0) }}">{{ format_money_short($stats['collected']) }}</div></div></div>
    <div class="col-6 col-md-4 col-xl-2"><div class="kpi-card kpi-warning"><div class="kpi-label">{{ __('app.expenses') }}</div><div class="kpi-value" title="{{ format_currency($stats['expenses'], null, 0) }}">{{ format_money_short($stats['expenses']) }}</div></div></div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="kpi-card {{ $stats['net'] >= 0 ? 'kpi-success' : 'kpi-danger' }}">
            <div class="kpi-label">{{ __('ops.net') }}</div>
            <div class="kpi-value" title="{{ format_currency($stats['net'], null, 0) }}">
                <i class="bi {{ $stats['net'] >= 0 ? 'bi-arrow-up-right' : 'bi-arrow-down-right' }} small"></i>
                {{ ($stats['net'] < 0 ? '−' : '').format_money_short(abs($stats['net'])) }}
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2"><div class="kpi-card kpi-info"><div class="kpi-label">{{ __('ops.collection_rate') }}</div><div class="kpi-value">{{ $stats['collection_rate'] === null ? '—' : $stats['collection_rate'].'%' }}</div><small class="text-muted">{{ __('ops.collection_rate_hint') }}</small></div></div>
    <div class="col-6 col-md-4 col-xl-2"><div class="kpi-card kpi-danger"><div class="kpi-label">{{ __('ops.outstanding') }}</div><div class="kpi-value" title="{{ format_currency($stats['outstanding'], null, 0) }}">{{ format_money_short($stats['outstanding']) }}</div><small class="text-muted">{{ __('ops.as_of_today') }}</small></div></div>
</div>

<div class="card mb-4">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <span class="brand-font">{{ __('ops.cash_in_vs_out') }}</span>
        <button type="button" class="btn btn-sm btn-light border d-print-none" data-bs-toggle="collapse" data-bs-target="#statementTable" aria-expanded="false" aria-controls="statementTable"><i class="bi bi-table me-1"></i>{{ __('ops.show_table') }}</button>
    </div>
    <div class="card-body">
        <div style="position: relative; height: 280px;">
            <canvas id="cashChart" role="img" aria-label="{{ __('ops.cash_in_vs_out') }}"></canvas>
        </div>
        <div class="collapse mt-3 d-print-block" id="statementTable">
            <div class="table-responsive">
                <table class="table table-sm small mb-0" style="font-variant-numeric: tabular-nums;">
                    <thead class="table-light"><tr><th>{{ __('ops.month') }}</th><th class="text-end">{{ __('ops.collected') }}</th><th class="text-end">{{ __('app.expenses') }}</th><th class="text-end">{{ __('ops.net') }}</th></tr></thead>
                    <tbody>
                        @foreach($months as $row)
                            <tr>
                                <td>{{ $row['label'] }}</td>
                                <td class="text-end">{{ format_currency($row['collected'], null, 0) }}</td>
                                <td class="text-end">{{ format_currency($row['expenses'], null, 0) }}</td>
                                <td class="text-end fw-semibold {{ $row['collected'] - $row['expenses'] < 0 ? 'text-danger' : '' }}">{{ format_currency($row['collected'] - $row['expenses'], null, 0) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="fw-bold">
                        <tr><td>{{ __('ops.total') }}</td><td class="text-end">{{ format_currency($stats['collected'], null, 0) }}</td><td class="text-end">{{ format_currency($stats['expenses'], null, 0) }}</td><td class="text-end">{{ format_currency($stats['net'], null, 0) }}</td></tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-12 col-lg-6">
        <div class="card h-100">
            <div class="card-header brand-font">{{ __('ops.expenses_by_category') }}</div>
            <div class="card-body">
                @if($expensesByCategory->isNotEmpty())
                    <div style="position: relative; height: {{ max(140, $expensesByCategory->count() * 34) }}px;">
                        <canvas id="expenseChart" role="img" aria-label="{{ __('ops.expenses_by_category') }}"></canvas>
                    </div>
                @else
                    <p class="text-muted small mb-0">{{ __('ops.no_report_rows') }}</p>
                @endif
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-6">
        <div class="card h-100 p-3">
            <h2 class="h6 brand-font mb-3">{{ __('ops.by_payment_method') }}</h2>
            @forelse($paymentsByMethod as $method => $amount)
                @php $share = $stats['collected'] > 0 ? round($amount / $stats['collected'] * 100) : 0; @endphp
                <div class="d-flex justify-content-between small mb-1">
                    <span>{{ $method }}</span>
                    <span class="fw-semibold" style="font-variant-numeric: tabular-nums;">{{ format_currency($amount, null, 0) }} <span class="text-muted fw-normal">· {{ $share }}%</span></span>
                </div>
                <div class="progress progress-thin rounded-pill mb-2" role="progressbar" aria-label="{{ $method }}" aria-valuenow="{{ $share }}" aria-valuemin="0" aria-valuemax="100">
                    <div class="progress-bar rounded-pill" style="width: {{ $share }}%; background: #2a78d6;"></div>
                </div>
            @empty
                <p class="text-muted small mb-0">{{ __('ops.no_payments') }}</p>
            @endforelse
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <span class="brand-font">{{ __('ops.payments_title') }} <span class="badge bg-light text-dark border ms-1">{{ $payments->count() }}</span></span>
        <div class="d-print-none" style="max-width: 260px;">
            <label for="paymentSearch" class="visually-hidden">{{ __('app.search') }}</label>
            <input type="search" id="paymentSearch" class="form-control form-control-sm" placeholder="{{ __('ops.quick_filter_placeholder') }}" data-table-filter="#paymentTable">
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover table-stack align-middle mb-0 small" id="paymentTable">
            <thead class="table-light">
                <tr>
                    <th>{{ __('ops.payment_no') }}</th>
                    <th>{{ __('ops.customer') }}</th>
                    <th>{{ __('ops.for_invoice') }}</th>
                    <th>{{ __('ops.date') }}</th>
                    <th>{{ __('ops.by_payment_method') }}</th>
                    <th class="text-end">{{ __('ops.amount') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments->sortByDesc('payment_date') as $pay)
                    <tr>
                        <td class="stack-primary fw-semibold"><a href="{{ route('finance.payments.receipt', $pay) }}">{{ $pay->payment_number }}</a></td>
                        <td>{{ $pay->customer?->full_name ?? '—' }}</td>
                        <td>@if($pay->invoice)<a href="{{ route('finance.invoices.show', $pay->invoice) }}">{{ $pay->invoice->invoice_number }}</a>@else — @endif</td>
                        <td class="text-nowrap">{{ $pay->payment_date?->format('d M Y') }}</td>
                        <td>{{ tz_payment_methods()[$pay->payment_method] ?? $pay->payment_method }}</td>
                        <td class="text-end fw-bold text-nowrap">{{ format_currency($pay->amount, null, 0) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="stack-no-label"><div class="empty-state"><div class="empty-icon"><i class="bi bi-cash-stack"></i></div><h6>{{ __('ops.no_payments') }}</h6></div></td></tr>
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
    const months = {!! json_encode($months->values()) !!};
    const labels = {!! json_encode(['collected' => __('ops.collected'), 'expenses' => __('app.expenses')]) !!};
    ReportCharts.bars(document.getElementById('cashChart'), {
        labels: months.map(m => m.label),
        money: true,
        datasets: [
            { label: labels.collected, data: months.map(m => m.collected) },
            { label: labels.expenses, data: months.map(m => m.expenses) },
        ],
    });

    const byCategory = {!! json_encode($expensesByCategory) !!};
    ReportCharts.bars(document.getElementById('expenseChart'), {
        labels: Object.keys(byCategory),
        money: true,
        horizontal: true,
        datasets: [{ label: labels.expenses, data: Object.values(byCategory), color: ReportCharts.palette[1] }],
    });
});
</script>
@endsection
