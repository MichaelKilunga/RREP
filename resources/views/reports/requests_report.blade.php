@extends('layouts.app')

@section('title', __('ops.report_requests_short'))

@section('content')
@php
    $statusTone = ['New' => 'info', 'Contacted' => 'warning', 'Resolved' => 'success', 'Closed' => 'secondary'];
@endphp

@include('reports.partials.header', ['title' => __('ops.report_requests_heading'), 'icon' => 'bi-inboxes', 'routeName' => 'reports.requests'])

<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl-2"><div class="kpi-card"><div class="kpi-label">{{ __('ops.requests_title') }}</div><div class="kpi-value">{{ $stats['total'] }}</div></div></div>
    <div class="col-6 col-md-4 col-xl-2"><div class="kpi-card kpi-info"><div class="kpi-label">{{ __('ops.still_new') }}</div><div class="kpi-value">{{ $stats['new'] }}</div><small class="text-muted">{{ __('ops.still_new_hint') }}</small></div></div>
    <div class="col-6 col-md-4 col-xl-2"><div class="kpi-card kpi-success"><div class="kpi-label">{{ __('ops.requests_resolved') }}</div><div class="kpi-value">{{ $stats['resolved'] }}</div></div></div>
    <div class="col-6 col-md-4 col-xl-2"><div class="kpi-card kpi-warning"><div class="kpi-label">{{ __('ops.requests_resolution_rate') }}</div><div class="kpi-value">{{ $stats['resolution_rate'] }}%</div></div></div>
    <div class="col-6 col-md-4 col-xl-2"><div class="kpi-card"><div class="kpi-label">{{ __('ops.converted_to_leads') }}</div><div class="kpi-value">{{ $stats['converted'] }}</div></div></div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="kpi-card kpi-info">
            <div class="kpi-label">{{ __('ops.avg_response_time') }}</div>
            <div class="kpi-value">{{ $stats['avg_response_hours'] === null ? '—' : ($stats['avg_response_hours'] < 48 ? __('ops.hours_short', ['n' => $stats['avg_response_hours']]) : __('ops.days_short', ['n' => round($stats['avg_response_hours'] / 24, 1)])) }}</div>
            <small class="text-muted">{{ __('ops.avg_response_hint') }}</small>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-12 col-xl-7">
        <div class="card h-100">
            <div class="card-header brand-font">{{ __('ops.requests_over_time') }}</div>
            <div class="card-body">
                <div style="position: relative; height: 240px;">
                    <canvas id="requestsChart" role="img" aria-label="{{ __('ops.requests_over_time') }}"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-5">
        <div class="card h-100 p-3">
            <h2 class="h6 brand-font mb-3">{{ __('ops.requests_by_type') }}</h2>
            @forelse($byType as $type => $count)
                @php $share = $stats['total'] ? round($count / $stats['total'] * 100) : 0; @endphp
                <div class="d-flex justify-content-between small mb-1"><span>{{ $type }}</span><span class="fw-semibold">{{ $count }} <span class="text-muted fw-normal">· {{ $share }}%</span></span></div>
                <div class="progress progress-thin rounded-pill mb-2" role="progressbar" aria-label="{{ $type }}" aria-valuenow="{{ $share }}" aria-valuemin="0" aria-valuemax="100">
                    <div class="progress-bar rounded-pill" style="width: {{ $share }}%; background: #2a78d6;"></div>
                </div>
            @empty
                <p class="text-muted small mb-0">{{ __('ops.no_report_rows') }}</p>
            @endforelse

            <h2 class="h6 brand-font mt-3 mb-2">{{ __('ops.requests_by_status') }}</h2>
            <div class="d-flex flex-wrap gap-2">
                @foreach(\App\Http\Controllers\CRMController::REQUEST_STATUSES as $status)
                    <a href="{{ route('crm.requests', ['status' => $status]) }}" class="badge rounded-pill bg-{{ $statusTone[$status] }}-subtle text-{{ $statusTone[$status] }}-emphasis border text-decoration-none py-2 px-3 d-print-none">
                        {{ __('ops.request_status_'.strtolower($status)) }} · {{ $byStatus[$status] ?? 0 }}
                    </a>
                    <span class="d-none d-print-inline small me-2">{{ __('ops.request_status_'.strtolower($status)) }}: {{ $byStatus[$status] ?? 0 }}</span>
                @endforeach
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <span class="brand-font">{{ __('ops.requests_title') }} <span class="badge bg-light text-dark border ms-1">{{ $requests->count() }}</span></span>
        <div class="d-print-none" style="max-width: 260px;">
            <label for="requestSearch" class="visually-hidden">{{ __('app.search') }}</label>
            <input type="search" id="requestSearch" class="form-control form-control-sm" placeholder="{{ __('ops.quick_filter_placeholder') }}" data-table-filter="#requestTable">
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover table-stack align-middle mb-0 small" id="requestTable">
            <thead class="table-light">
                <tr>
                    <th>{{ __('ops.request_col_type') }}</th>
                    <th>{{ __('ops.request_col_contact') }}</th>
                    <th>{{ __('ops.request_col_subject') }}</th>
                    <th>{{ __('ops.request_col_received') }}</th>
                    <th>{{ __('ops.handled_by') }}</th>
                    <th>{{ __('app.status') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $req)
                    <tr>
                        <td class="stack-primary"><span class="badge bg-primary-subtle text-primary">{{ $req->inquiry_type }}</span></td>
                        <td>
                            <div class="fw-semibold">{{ $req->name }}</div>
                            <small class="text-muted">{{ format_phone_tz($req->phone) }}</small>
                        </td>
                        <td>
                            <div class="fw-semibold small">{{ $req->property?->title ?? $req->project?->title ?? $req->service?->title ?? __('ops.general_request') }}</div>
                            <div class="text-muted small text-truncate" style="max-width: 360px;">{{ Str::limit($req->message, 100) }}</div>
                        </td>
                        <td class="text-nowrap text-muted">{{ $req->created_at?->format('d M Y, H:i') }}</td>
                        <td>
                            {{ $req->assignee?->name ?? '—' }}
                            @if($req->handled_at)<small class="text-muted d-block">{{ $req->handled_at->format('d M, H:i') }}</small>@endif
                        </td>
                        <td><span class="badge bg-{{ $statusTone[$req->status] ?? 'secondary' }}-subtle text-{{ $statusTone[$req->status] ?? 'secondary' }}-emphasis">{{ __('ops.request_status_'.strtolower($req->status)) }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="stack-no-label"><div class="empty-state"><div class="empty-icon"><i class="bi bi-inboxes"></i></div><h6>{{ __('ops.no_requests') }}</h6><p class="small mb-0">{{ __('ops.no_report_rows_hint') }}</p></div></td></tr>
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
    ReportCharts.bars(document.getElementById('requestsChart'), {
        labels: trend.labels,
        datasets: [{ label: {!! json_encode(__('ops.requests_title')) !!}, data: trend.series.requests }],
    });
});
</script>
@endsection
