@extends('layouts.app')

@section('title', __('ops.finance_overview'))

@section('content')
@php
    $agingRows = [
        'current' => ['label' => __('ops.aging_current'), 'icon' => 'bi-check-circle', 'tone' => '#0ca30c'],
        'd1_30' => ['label' => __('ops.aging_1_30'), 'icon' => 'bi-exclamation-circle', 'tone' => '#fab219'],
        'd31_60' => ['label' => __('ops.aging_31_60'), 'icon' => 'bi-exclamation-triangle', 'tone' => '#ec835a'],
        'd61_90' => ['label' => __('ops.aging_61_90'), 'icon' => 'bi-exclamation-octagon', 'tone' => '#d03b3b'],
        'd90' => ['label' => __('ops.aging_90'), 'icon' => 'bi-x-octagon', 'tone' => '#d03b3b'],
    ];
    $agingTotal = array_sum($aging);
@endphp

<div class="page-header">
    <div class="min-w-0">
        <h1 class="brand-font mb-1"><i class="bi bi-graph-up text-primary me-2"></i>{{ __('ops.finance_overview') }}</h1>
        <p class="text-muted small mb-0">{{ __('ops.finance_overview_subtitle', ['month' => now()->translatedFormat('F Y')]) }}</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('finance.invoices') }}" class="btn btn-light border"><i class="bi bi-receipt me-1"></i>{{ __('app.invoices') }}</a>
        <a href="{{ route('finance.payments') }}" class="btn btn-light border"><i class="bi bi-cash-stack me-1"></i>{{ __('ops.payments_title') }}</a>
        @can('view_expenses')<a href="{{ route('finance.expenses') }}" class="btn btn-light border"><i class="bi bi-wallet2 me-1"></i>{{ __('app.expenses') }}</a>@endcan
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl-2"><div class="kpi-card"><div class="kpi-label">{{ __('ops.invoiced_month') }}</div><div class="kpi-value" title="{{ format_currency($kpis['invoiced_month'], null, 0) }}">{{ format_money_short($kpis['invoiced_month']) }}</div></div></div>
    <div class="col-6 col-md-4 col-xl-2"><div class="kpi-card kpi-success"><div class="kpi-label">{{ __('ops.collected_month') }}</div><div class="kpi-value" title="{{ format_currency($kpis['collected_month'], null, 0) }}">{{ format_money_short($kpis['collected_month']) }}</div></div></div>
    <div class="col-6 col-md-4 col-xl-2"><div class="kpi-card kpi-warning"><div class="kpi-label">{{ __('ops.expenses_this_month') }}</div><div class="kpi-value" title="{{ format_currency($kpis['expenses_month'], null, 0) }}">{{ format_money_short($kpis['expenses_month']) }}</div></div></div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="kpi-card {{ $kpis['net_month'] >= 0 ? 'kpi-success' : 'kpi-danger' }}">
            <div class="kpi-label">{{ __('ops.net_cash_month') }}</div>
            <div class="kpi-value" title="{{ format_currency($kpis['net_month'], null, 0) }}">
                <i class="bi {{ $kpis['net_month'] >= 0 ? 'bi-arrow-up-right' : 'bi-arrow-down-right' }} small"></i>
                {{ ($kpis['net_month'] < 0 ? '−' : '').format_money_short(abs($kpis['net_month'])) }}
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2"><div class="kpi-card kpi-info"><div class="kpi-label">{{ __('ops.outstanding') }}</div><div class="kpi-value" title="{{ format_currency($kpis['outstanding'], null, 0) }}">{{ format_money_short($kpis['outstanding']) }}</div></div></div>
    <div class="col-6 col-md-4 col-xl-2"><div class="kpi-card kpi-danger"><div class="kpi-label">{{ __('ops.overdue') }}</div><div class="kpi-value" title="{{ format_currency($kpis['overdue'], null, 0) }}">{{ format_money_short($kpis['overdue']) }}</div><small class="text-muted">{{ trans_choice('ops.invoices_count', $kpis['overdue_count'], ['count' => $kpis['overdue_count']]) }}</small></div></div>
</div>

<div class="row g-4 mb-4">
    <div class="col-12 col-xl-8">
        <div class="card h-100">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <span class="brand-font">{{ __('ops.cash_in_vs_out') }}</span>
                    <small class="text-muted d-block">{{ __('ops.last_6_months') }}</small>
                </div>
                <button type="button" class="btn btn-sm btn-light border" data-bs-toggle="collapse" data-bs-target="#cashTable" aria-expanded="false" aria-controls="cashTable"><i class="bi bi-table me-1"></i>{{ __('ops.show_table') }}</button>
            </div>
            <div class="card-body">
                <div style="position: relative; height: 280px;">
                    <canvas id="cashChart" role="img" aria-label="{{ __('ops.cash_in_vs_out') }}"></canvas>
                </div>
                <div class="collapse mt-3" id="cashTable">
                    <div class="table-responsive">
                        <table class="table table-sm small mb-0" style="font-variant-numeric: tabular-nums;">
                            <thead class="table-light"><tr><th>{{ __('ops.month') }}</th><th class="text-end">{{ __('ops.collected') }}</th><th class="text-end">{{ __('app.expenses') }}</th><th class="text-end">{{ __('ops.net') }}</th></tr></thead>
                            <tbody>
                                @foreach($series as $row)
                                    <tr>
                                        <td>{{ $row['label'] }}</td>
                                        <td class="text-end">{{ format_currency($row['collected'], null, 0) }}</td>
                                        <td class="text-end">{{ format_currency($row['expenses'], null, 0) }}</td>
                                        <td class="text-end fw-semibold">{{ format_currency($row['collected'] - $row['expenses'], null, 0) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-4">
        <div class="card h-100 p-3">
            <h2 class="h6 brand-font mb-1">{{ __('ops.receivables_aging') }}</h2>
            <p class="text-muted small mb-3">{{ __('ops.receivables_aging_hint') }}</p>
            @foreach($agingRows as $key => $meta)
                @php $share = $agingTotal > 0 ? round($aging[$key] / $agingTotal * 100) : 0; @endphp
                <div class="d-flex justify-content-between small mb-1">
                    <span><i class="bi {{ $meta['icon'] }} me-1" style="color: {{ $meta['tone'] }};"></i>{{ $meta['label'] }}</span>
                    <span class="fw-semibold" style="font-variant-numeric: tabular-nums;">{{ format_money_short($aging[$key]) }}</span>
                </div>
                <div class="progress progress-thin rounded-pill mb-2" role="progressbar" aria-label="{{ $meta['label'] }}" aria-valuenow="{{ $share }}" aria-valuemin="0" aria-valuemax="100">
                    <div class="progress-bar rounded-pill" style="width: {{ $share }}%; background: {{ $meta['tone'] }};"></div>
                </div>
            @endforeach
            <a href="{{ route('finance.invoices', ['status' => 'overdue']) }}" class="btn btn-sm btn-outline-danger mt-auto">{{ __('ops.chase_overdue') }}</a>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-6">
        <div class="card h-100">
            <div class="card-header brand-font">{{ __('ops.top_debtors') }}</div>
            <div class="list-group list-group-flush small">
                @forelse($topDebtors as $debtor)
                    <a href="{{ route('finance.invoices', ['customer' => $debtor->id, 'status' => 'open']) }}" class="list-group-item list-group-item-action d-flex justify-content-between gap-2">
                        <span class="text-truncate">{{ $debtor->full_name }}<span class="text-muted d-block">{{ format_phone_tz($debtor->phone) }}</span></span>
                        <span class="fw-bold text-danger text-nowrap">{{ format_currency($debtor->balance, null, 0) }}</span>
                    </a>
                @empty
                    <div class="list-group-item text-muted">{{ __('ops.no_debtors') }}</div>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center"><span class="brand-font">{{ __('ops.recent_payments') }}</span><a href="{{ route('finance.payments') }}" class="small">{{ __('app.view_all') }}</a></div>
            <div class="list-group list-group-flush small">
                @forelse($recentPayments as $pay)
                    <a href="{{ route('finance.payments.receipt', $pay) }}" class="list-group-item list-group-item-action d-flex justify-content-between gap-2">
                        <span class="text-truncate">{{ $pay->customer?->full_name }}<span class="text-muted d-block">{{ $pay->payment_date?->format('d M Y') }} · {{ tz_payment_methods()[$pay->payment_method] ?? $pay->payment_method }}</span></span>
                        <span class="fw-bold text-success text-nowrap">{{ format_currency($pay->amount, null, 0) }}</span>
                    </a>
                @empty
                    <div class="list-group-item text-muted">{{ __('ops.no_payments') }}</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const canvas = document.getElementById('cashChart');
    if (!canvas || !window.Chart) { return; }
    const series = {!! json_encode($series) !!};
    const labels = {!! json_encode(['collected' => __('ops.collected'), 'expenses' => __('app.expenses')]) !!};
    const compact = (v) => {
        const a = Math.abs(v);
        if (a >= 1e9) { return (v / 1e9).toFixed(1).replace(/\.0$/, '') + 'B'; }
        if (a >= 1e6) { return (v / 1e6).toFixed(1).replace(/\.0$/, '') + 'M'; }
        if (a >= 1e3) { return (v / 1e3).toFixed(0) + 'K'; }
        return String(v);
    };
    const bar = { maxBarThickness: 24, borderRadius: { topLeft: 4, topRight: 4 }, borderSkipped: 'bottom', borderWidth: 0 };

    new Chart(canvas, {
        type: 'bar',
        data: {
            labels: series.map(s => s.label),
            datasets: [
                Object.assign({ label: labels.collected, data: series.map(s => s.collected), backgroundColor: '#2a78d6' }, bar),
                Object.assign({ label: labels.expenses, data: series.map(s => s.expenses), backgroundColor: '#eb6834' }, bar),
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { position: 'top', align: 'end', labels: { color: '#52514e', usePointStyle: true, pointStyle: 'rectRounded', boxWidth: 10 } },
                tooltip: { callbacks: { label: (ctx) => ctx.dataset.label + ': TSh ' + Math.round(ctx.parsed.y).toLocaleString('en-US') } }
            },
            scales: {
                x: { grid: { display: false }, border: { color: '#c3c2b7' }, ticks: { color: '#898781' } },
                y: { beginAtZero: true, grid: { color: '#e1e0d9', lineWidth: 1 }, border: { display: false }, ticks: { color: '#898781', callback: (v) => compact(v) } }
            }
        }
    });
});
</script>
@endsection
