@extends('layouts.app')

@section('title', __('ops.report_survey_short'))

@section('content')
@php
    $surveyBadge = ['Planning' => 'pending', 'Fieldwork' => 'review', 'Beaconing' => 'review', 'Computations' => 'review', 'Verification' => 'underreview', 'Approved' => 'active', 'Completed' => 'paid'];
    $byStatus = $surveys->groupBy('status')->map->count();
    $totalBeacons = $surveys->sum(fn ($s) => $s->beacons->count());
    $completed = $surveys->where('status', 'Completed')->count();
    $totalAcres = $surveys->where('area_unit', 'Acres')->sum('total_area');
@endphp

<div class="page-header d-print-none">
    <div class="min-w-0">
        <h1 class="brand-font mb-1">{{ __('ops.report_survey_heading') }}</h1>
        <p class="text-muted small mb-0">{{ __('ops.report_generated_at', ['date' => now()->format('d M Y, H:i')]) }} EAT</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('reports.index') }}" class="btn btn-light border"><i class="bi bi-arrow-left me-1"></i> {{ __('ops.reports_center') }}</a>
        <button type="button" onclick="window.print()" class="btn btn-dark"><i class="bi bi-printer me-1"></i> {{ __('ops.print') }}</button>
    </div>
</div>

<h2 class="h5 brand-font d-none d-print-block mb-3">Land Survey &amp; Cadastral Audit Report</h2>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-label">{{ __('app.survey_projects') }}</div>
            <div class="kpi-value">{{ $surveys->count() }}</div>
            <span class="kpi-icon"><i class="bi bi-geo-alt"></i></span>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card kpi-success">
            <div class="kpi-label">{{ __('ops.completed') }}</div>
            <div class="kpi-value text-success">{{ $completed }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card kpi-info">
            <div class="kpi-label">{{ __('ops.beacons') }}</div>
            <div class="kpi-value">{{ $totalBeacons }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card kpi-warning">
            <div class="kpi-label">{{ __('ops.acres_surveyed') }}</div>
            <div class="kpi-value">{{ rtrim(rtrim(number_format($totalAcres, 2), '0'), '.') }}</div>
        </div>
    </div>
</div>

<div class="card mb-4 d-print-none">
    <div class="card-body">
        <div class="filter-bar">
            <div>
                <label for="surveySearch" class="visually-hidden">{{ __('app.search') }}</label>
                <input type="search" id="surveySearch" class="form-control" placeholder="{{ __('ops.quick_filter_placeholder') }}" data-table-filter="#surveyTable">
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header brand-font">{{ __('ops.projects_by_stage') }}</div>
    <div class="card-body">
        <div class="ratio ratio-21x9"><canvas id="surveyStatusChart" role="img" aria-label="{{ __('ops.projects_by_stage') }}"></canvas></div>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 table-stack small" id="surveyTable">
            <thead class="table-light">
                <tr>
                    <th>{{ __('ops.project_code') }}</th>
                    <th>{{ __('ops.project_name') }}</th>
                    <th>{{ __('ops.location') }}</th>
                    <th>{{ __('ops.area_size') }}</th>
                    <th>{{ __('ops.lead_surveyor') }}</th>
                    <th>{{ __('ops.beacons') }}</th>
                    <th>{{ __('ops.milestones') }}</th>
                    <th>{{ __('app.status') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($surveys as $s)
                    <tr>
                        <td class="stack-primary"><span class="fw-bold text-primary">{{ $s->project_code }}</span></td>
                        <td class="fw-semibold">{{ $s->project_name }}</td>
                        <td>{{ $s->location_name }}</td>
                        <td>{{ $s->total_area ? rtrim(rtrim(number_format($s->total_area, 4), '0'), '.') : '-' }} {{ $s->area_unit }}</td>
                        <td>{{ $s->leadSurveyor?->name ?? __('ops.unassigned') }}</td>
                        <td><span class="badge bg-primary-subtle text-primary">{{ $s->beacons->count() }}</span></td>
                        <td>{{ $s->milestones->where('status', 'Completed')->count() }} / {{ $s->milestones->count() }}</td>
                        <td><span class="badge badge-status-{{ $surveyBadge[$s->status] ?? strtolower($s->status) }}">{{ __('ops.survey_status_'.strtolower($s->status)) }}</span></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="stack-no-label">
                            <div class="empty-state">
                                <div class="empty-icon"><i class="bi bi-geo-alt"></i></div>
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
    const byStatus = @json($byStatus);
    const canvas = document.getElementById('surveyStatusChart');
    if (canvas && Object.keys(byStatus).length) {
        new Chart(canvas, {
            type: 'bar',
            data: { labels: Object.keys(byStatus), datasets: [{ label: '#', data: Object.values(byStatus), backgroundColor: '#0f52ba', borderRadius: 6 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
        });
    }
});
</script>
@endsection
