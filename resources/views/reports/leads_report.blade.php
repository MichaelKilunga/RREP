@extends('layouts.app')

@section('title', __('ops.report_leads_short'))

@section('content')
@include('reports.partials.header', ['title' => __('ops.report_leads_heading'), 'icon' => 'bi-funnel', 'routeName' => 'reports.leads'])

<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl-2"><div class="kpi-card"><div class="kpi-label">{{ __('ops.new_leads') }}</div><div class="kpi-value">{{ $stats['total'] }}</div></div></div>
    <div class="col-6 col-md-4 col-xl-2"><div class="kpi-card kpi-success"><div class="kpi-label">{{ __('ops.won_lost') }}</div><div class="kpi-value"><span class="text-success">{{ $stats['won'] }}</span> / <span class="text-danger">{{ $stats['lost'] }}</span></div></div></div>
    <div class="col-6 col-md-4 col-xl-2"><div class="kpi-card kpi-info"><div class="kpi-label">{{ __('ops.conversion_rate') }}</div><div class="kpi-value">{{ $stats['conversion'] }}%</div></div></div>
    <div class="col-6 col-md-4 col-xl-2"><div class="kpi-card kpi-warning"><div class="kpi-label">{{ __('ops.open_pipeline_value') }}</div><div class="kpi-value" title="{{ format_currency($stats['pipeline_value'], null, 0) }}">{{ format_money_short($stats['pipeline_value']) }}</div></div></div>
    <div class="col-6 col-md-4 col-xl-2"><div class="kpi-card kpi-success"><div class="kpi-label">{{ __('ops.won_value') }}</div><div class="kpi-value" title="{{ format_currency($stats['won_value'], null, 0) }}">{{ format_money_short($stats['won_value']) }}</div></div></div>
    <div class="col-6 col-md-4 col-xl-2"><div class="kpi-card"><div class="kpi-label">{{ __('ops.followups_overdue') }}</div><div class="kpi-value">{{ $leads->whereNotIn('stage', ['Won', 'Lost'])->filter(fn ($l) => $l->next_followup_at && $l->next_followup_at->isPast())->count() }}</div></div></div>
</div>

<div class="row g-4 mb-4">
    <div class="col-12 col-lg-6">
        <div class="card h-100">
            <div class="card-header brand-font">{{ __('ops.pipeline_stages') }}</div>
            <div class="card-body">
                <div style="position: relative; height: 300px;">
                    <canvas id="leadStageChart" role="img" aria-label="{{ __('ops.pipeline_stages') }}"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-6">
        <div class="card h-100">
            <div class="card-header brand-font">{{ __('ops.lead_sources') }}</div>
            <div class="card-body">
                @if($sources->isNotEmpty())
                    <div style="position: relative; height: {{ max(160, $sources->count() * 34) }}px;">
                        <canvas id="leadSourceChart" role="img" aria-label="{{ __('ops.lead_sources') }}"></canvas>
                    </div>
                @else
                    <p class="text-muted small mb-0">{{ __('ops.no_report_rows') }}</p>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header brand-font">{{ __('ops.leads_by_agent') }}</div>
    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0 small" style="font-variant-numeric: tabular-nums;">
            <thead class="table-light"><tr><th>{{ __('ops.agent') }}</th><th class="text-end">{{ __('ops.new_leads') }}</th><th class="text-end">{{ __('ops.stage_won') }}</th><th class="text-end">{{ __('ops.conversion_rate') }}</th></tr></thead>
            <tbody>
                @forelse($byAgent as $agent => $row)
                    <tr>
                        <td class="fw-semibold">{{ $agent }}</td>
                        <td class="text-end">{{ $row['total'] }}</td>
                        <td class="text-end text-success fw-bold">{{ $row['won'] }}</td>
                        <td class="text-end">{{ $row['total'] ? round($row['won'] / $row['total'] * 100, 1) : 0 }}%</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-muted">{{ __('ops.no_report_rows') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-header brand-font d-flex flex-wrap justify-content-between align-items-center gap-2">
        <span>{{ __('ops.recent_leads') }} <span class="badge bg-light text-dark border ms-1">{{ $leads->count() }}</span></span>
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
                    <th class="text-end">{{ __('ops.estimated_value') }}</th>
                    <th>{{ __('ops.date') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($leads as $lead)
                    <tr>
                        <td class="stack-primary">
                            @if($lead->customer)<a href="{{ route('crm.customers.show', $lead->customer) }}" class="fw-bold">{{ $lead->customer->full_name }}</a>@else — @endif
                        </td>
                        <td>{{ $lead->title }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $lead->source ?: '—' }}</span></td>
                        <td><span class="badge badge-status-{{ $lead->stage === 'Won' ? 'paid' : ($lead->stage === 'Lost' ? 'cancelled' : 'review') }}">{{ __('ops.stage_'.strtolower($lead->stage)) }}</span></td>
                        <td>{{ $lead->agent?->user?->name ?? __('ops.unassigned') }}</td>
                        <td class="text-end text-nowrap">{{ $lead->estimated_value ? format_currency($lead->estimated_value, null, 0) : '—' }}</td>
                        <td class="text-nowrap">{{ $lead->created_at?->format('d M Y') }}</td>
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
@include('reports.partials.chart-kit')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const stages = {!! json_encode($stages->mapWithKeys(fn ($count, $stage) => [__('ops.stage_'.strtolower($stage)) => $count])) !!};
    const sources = {!! json_encode($sources) !!};
    const leadsLabel = {!! json_encode(__('ops.new_leads')) !!};

    ReportCharts.bars(document.getElementById('leadStageChart'), {
        labels: Object.keys(stages),
        horizontal: true,
        datasets: [{ label: leadsLabel, data: Object.values(stages) }],
    });
    ReportCharts.bars(document.getElementById('leadSourceChart'), {
        labels: Object.keys(sources),
        horizontal: true,
        datasets: [{ label: leadsLabel, data: Object.values(sources) }],
    });
});
</script>
@endsection
