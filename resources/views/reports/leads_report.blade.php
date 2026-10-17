@extends('layouts.app')

@section('title', __('ops.report_leads_short'))

@section('content')
@php
    $won = $leads->where('stage', 'Won')->count();
    $lost = $leads->where('stage', 'Lost')->count();
    $conversion = $leads->count() > 0 ? ($won / $leads->count()) * 100 : 0;
    $pipelineValue = $leads->whereNotIn('stage', ['Won', 'Lost'])->sum('estimated_value');
@endphp

<div class="page-header d-print-none">
    <div class="min-w-0">
        <h1 class="brand-font mb-1">{{ __('ops.report_leads_heading') }}</h1>
        <p class="text-muted small mb-0">{{ __('ops.report_generated_at', ['date' => now()->format('d M Y, H:i')]) }} EAT</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('reports.index') }}" class="btn btn-light border"><i class="bi bi-arrow-left me-1"></i> {{ __('ops.reports_center') }}</a>
        <button type="button" onclick="window.print()" class="btn btn-dark"><i class="bi bi-printer me-1"></i> {{ __('ops.print') }}</button>
    </div>
</div>

<h2 class="h5 brand-font d-none d-print-block mb-3">Lead Conversion &amp; Pipeline Analytics</h2>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-label">{{ __('app.total_leads') }}</div>
            <div class="kpi-value">{{ $leads->count() }}</div>
            <span class="kpi-icon"><i class="bi bi-funnel"></i></span>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card kpi-success">
            <div class="kpi-label">{{ __('ops.won_lost') }}</div>
            <div class="kpi-value"><span class="text-success">{{ $won }}</span> / <span class="text-danger">{{ $lost }}</span></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card kpi-info">
            <div class="kpi-label">{{ __('ops.conversion_rate') }}</div>
            <div class="kpi-value">{{ number_format($conversion, 1) }}%</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card kpi-warning">
            <div class="kpi-label">{{ __('ops.open_pipeline_value') }}</div>
            <div class="kpi-value">{{ format_money_short($pipelineValue) }}</div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-12 col-lg-6">
        <div class="card h-100">
            <div class="card-header brand-font">{{ __('ops.lead_sources') }}</div>
            <div class="card-body">
                <div class="ratio ratio-16x9 mb-3"><canvas id="leadSourceChart" role="img" aria-label="{{ __('ops.lead_sources') }}"></canvas></div>
                <ul class="list-group list-group-flush">
                    @forelse($sources as $src)
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span>{{ $src->source }}</span>
                            <span class="badge bg-primary rounded-pill">{{ $src->count }}</span>
                        </li>
                    @empty
                        <li class="list-group-item px-0 text-muted small">{{ __('ops.no_report_rows') }}</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-6">
        <div class="card h-100">
            <div class="card-header brand-font">{{ __('ops.pipeline_stages') }}</div>
            <div class="card-body">
                <div class="ratio ratio-16x9 mb-3"><canvas id="leadStageChart" role="img" aria-label="{{ __('ops.pipeline_stages') }}"></canvas></div>
                <ul class="list-group list-group-flush">
                    @forelse($stages as $stg)
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span>{{ __('ops.stage_'.strtolower($stg->stage)) }}</span>
                            <span class="badge bg-secondary rounded-pill">{{ $stg->count }}</span>
                        </li>
                    @empty
                        <li class="list-group-item px-0 text-muted small">{{ __('ops.no_report_rows') }}</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header brand-font d-flex justify-content-between align-items-center gap-2">
        <span>{{ __('ops.recent_leads') }}</span>
        <div class="d-print-none" style="max-width: 260px;">
            <label for="leadSearch" class="visually-hidden">{{ __('app.search') }}</label>
            <input type="search" id="leadSearch" class="form-control form-control-sm" placeholder="{{ __('ops.quick_filter_placeholder') }}" data-table-filter="#leadTable">
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 table-stack small" id="leadTable">
            <thead class="table-light">
                <tr>
                    <th>{{ __('ops.customer') }}</th>
                    <th>{{ __('ops.lead_title') }}</th>
                    <th>{{ __('ops.source') }}</th>
                    <th>{{ __('ops.pipeline_stage') }}</th>
                    <th>{{ __('ops.agent') }}</th>
                    <th>{{ __('ops.estimated_value') }}</th>
                    <th>{{ __('ops.date') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($leads->take(50) as $lead)
                    <tr>
                        <td class="stack-primary"><span class="fw-bold">{{ $lead->customer?->full_name }}</span></td>
                        <td>{{ $lead->title }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $lead->source }}</span></td>
                        <td><span class="badge badge-status-{{ $lead->stage === 'Won' ? 'paid' : ($lead->stage === 'Lost' ? 'cancelled' : 'review') }}">{{ __('ops.stage_'.strtolower($lead->stage)) }}</span></td>
                        <td>{{ $lead->agent?->user?->name ?? __('ops.unassigned') }}</td>
                        <td>{{ $lead->estimated_value ? format_currency($lead->estimated_value) : '-' }}</td>
                        <td>{{ $lead->created_at?->format('d M Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="stack-no-label">
                            <div class="empty-state">
                                <div class="empty-icon"><i class="bi bi-funnel"></i></div>
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
    const sources = @json($sources->pluck('count', 'source'));
    const stages = @json($stages->pluck('count', 'stage'));
    const palette = ['#0f52ba', '#10b981', '#d97706', '#dc2626', '#4338ca', '#0284c7', '#7e22ce', '#475569', '#16a34a'];
    const sourceCanvas = document.getElementById('leadSourceChart');
    const stageCanvas = document.getElementById('leadStageChart');

    if (sourceCanvas && Object.keys(sources).length) {
        new Chart(sourceCanvas, {
            type: 'doughnut',
            data: { labels: Object.keys(sources), datasets: [{ data: Object.values(sources), backgroundColor: palette }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'right' } } }
        });
    }
    if (stageCanvas && Object.keys(stages).length) {
        new Chart(stageCanvas, {
            type: 'bar',
            data: { labels: Object.keys(stages), datasets: [{ label: '#', data: Object.values(stages), backgroundColor: palette[0], borderRadius: 6 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
        });
    }
});
</script>
@endsection
