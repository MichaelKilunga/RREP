@extends('layouts.app')

@section('title', __('ops.report_sms_short'))

@section('content')
@php
    $statusMeta = [
        'Sent' => ['tone' => 'success', 'icon' => 'bi-check-circle'],
        'Failed' => ['tone' => 'danger', 'icon' => 'bi-x-octagon'],
        'Skipped' => ['tone' => 'warning', 'icon' => 'bi-slash-circle'],
    ];
    $shownLogs = $logs->take(200);
@endphp

@include('reports.partials.header', ['title' => __('ops.report_sms_heading'), 'icon' => 'bi-chat-dots', 'routeName' => 'reports.sms'])

<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl"><div class="kpi-card"><div class="kpi-label">{{ __('ops.sms_attempted') }}</div><div class="kpi-value">{{ $stats['total'] }}</div></div></div>
    <div class="col-6 col-md-4 col-xl"><div class="kpi-card kpi-success"><div class="kpi-label"><i class="bi bi-check-circle me-1"></i>{{ __('ops.sms_status_sent') }}</div><div class="kpi-value">{{ $stats['sent'] }}</div></div></div>
    <div class="col-6 col-md-4 col-xl"><div class="kpi-card kpi-danger"><div class="kpi-label"><i class="bi bi-x-octagon me-1"></i>{{ __('ops.sms_status_failed') }}</div><div class="kpi-value">{{ $stats['failed'] }}</div></div></div>
    <div class="col-6 col-md-4 col-xl"><div class="kpi-card kpi-warning"><div class="kpi-label"><i class="bi bi-slash-circle me-1"></i>{{ __('ops.sms_status_skipped') }}</div><div class="kpi-value">{{ $stats['skipped'] }}</div></div></div>
    <div class="col-12 col-md-4 col-xl"><div class="kpi-card kpi-info"><div class="kpi-label">{{ __('ops.sms_success_rate') }}</div><div class="kpi-value">{{ $stats['success_rate'] === null ? '—' : $stats['success_rate'].'%' }}</div></div></div>
</div>

<div class="row g-4 mb-4">
    <div class="col-12 col-xl-7">
        <div class="card h-100">
            <div class="card-header brand-font">{{ __('ops.sms_over_time') }}</div>
            <div class="card-body">
                <div style="position: relative; height: 240px;">
                    <canvas id="smsChart" role="img" aria-label="{{ __('ops.sms_over_time') }}"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-5">
        <div class="card h-100 p-3">
            <h2 class="h6 brand-font mb-1">{{ __('ops.sms_failure_reasons') }}</h2>
            <p class="text-muted small mb-3">{{ __('ops.sms_failure_reasons_hint') }}</p>
            @forelse($failureReasons as $reason => $count)
                <div class="d-flex justify-content-between gap-3 small py-1 border-bottom">
                    <span class="text-break"><i class="bi bi-exclamation-triangle text-danger me-1"></i>{{ $reason }}</span>
                    <span class="fw-bold">{{ $count }}</span>
                </div>
            @empty
                <p class="text-success small mb-0"><i class="bi bi-check-circle me-1"></i>{{ __('ops.sms_no_failures') }}</p>
            @endforelse
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header brand-font">{{ __('ops.sms_by_template') }}</div>
    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0 small" style="font-variant-numeric: tabular-nums;">
            <thead class="table-light"><tr><th>{{ __('ops.sms_template') }}</th><th class="text-end">{{ __('ops.sms_attempted') }}</th><th class="text-end">{{ __('ops.sms_status_sent') }}</th><th class="text-end">{{ __('ops.sms_success_rate') }}</th></tr></thead>
            <tbody>
                @forelse($byTemplate as $template => $row)
                    <tr>
                        <td class="fw-semibold"><code>{{ $template }}</code></td>
                        <td class="text-end">{{ $row['total'] }}</td>
                        <td class="text-end">{{ $row['sent'] }}</td>
                        <td class="text-end {{ $row['total'] && $row['sent'] / $row['total'] < 0.8 ? 'text-danger fw-bold' : '' }}">{{ $row['total'] ? round($row['sent'] / $row['total'] * 100, 1) : 0 }}%</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-muted">{{ __('ops.no_report_rows') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <span class="brand-font">{{ __('ops.sms_log') }} <span class="badge bg-light text-dark border ms-1">{{ $logs->count() }}</span></span>
        <div class="d-print-none" style="max-width: 260px;">
            <label for="smsSearch" class="visually-hidden">{{ __('app.search') }}</label>
            <input type="search" id="smsSearch" class="form-control form-control-sm" placeholder="{{ __('ops.quick_filter_placeholder') }}" data-table-filter="#smsTable">
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover table-stack align-middle mb-0 small" id="smsTable">
            <thead class="table-light">
                <tr>
                    <th>{{ __('ops.date') }}</th>
                    <th>{{ __('ops.sms_recipient') }}</th>
                    <th>{{ __('ops.sms_template') }}</th>
                    <th>{{ __('ops.sms_message') }}</th>
                    <th>{{ __('app.status') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($shownLogs as $log)
                    @php $meta = $statusMeta[$log->status] ?? ['tone' => 'secondary', 'icon' => 'bi-question-circle']; @endphp
                    <tr>
                        <td class="stack-primary text-nowrap">{{ $log->created_at?->format('d M Y, H:i') }}</td>
                        <td class="text-nowrap">{{ format_phone_tz($log->recipient) }}</td>
                        <td><code>{{ $log->template_code ?: 'manual' }}</code></td>
                        <td>
                            <div class="text-truncate" style="max-width: 380px;" title="{{ $log->body }}">{{ $log->body }}</div>
                            @if($log->error_message && $log->status !== 'Sent')<small class="text-danger d-block text-truncate" style="max-width: 380px;" title="{{ $log->error_message }}">{{ $log->error_message }}</small>@endif
                        </td>
                        <td><span class="badge bg-{{ $meta['tone'] }}-subtle text-{{ $meta['tone'] }}-emphasis"><i class="bi {{ $meta['icon'] }} me-1"></i>{{ __('ops.sms_status_'.strtolower($log->status)) }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="stack-no-label"><div class="empty-state"><div class="empty-icon"><i class="bi bi-chat-dots"></i></div><h6>{{ __('ops.no_report_rows') }}</h6><p class="small mb-0">{{ __('ops.no_report_rows_hint') }}</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($logs->count() > $shownLogs->count())
        <div class="card-footer small text-muted">{{ __('ops.showing_latest', ['shown' => $shownLogs->count(), 'total' => $logs->count()]) }}</div>
    @endif
</div>
@endsection

@section('scripts')
@include('reports.partials.chart-kit')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const trend = {!! json_encode($series) !!};
    const names = {!! json_encode(['sent' => __('ops.sms_status_sent'), 'failed' => __('ops.sms_failed_or_skipped')]) !!};
    ReportCharts.bars(document.getElementById('smsChart'), {
        labels: trend.labels,
        datasets: [
            { label: names.sent, data: trend.series.sent, color: ReportCharts.status.good },
            { label: names.failed, data: trend.series.failed, color: ReportCharts.status.critical },
        ],
    });
});
</script>
@endsection
