@extends('layouts.app')

@section('title', __('ops.report_projects_short'))

@section('content')
<div class="page-header d-print-none">
    <div class="min-w-0">
        <h1 class="brand-font mb-1">{{ __('ops.report_projects_heading') }}</h1>
        <p class="text-muted small mb-0">{{ __('ops.report_generated_at', ['date' => now()->format('d M Y, H:i')]) }} EAT</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('reports.index') }}" class="btn btn-light border"><i class="bi bi-arrow-left me-1"></i> {{ __('ops.reports_center') }}</a>
        <a href="{{ route('reports.projects', ['export' => 'csv']) }}" class="btn btn-outline-primary"><i class="bi bi-filetype-csv me-1"></i> CSV</a>
        <button type="button" onclick="window.print()" class="btn btn-dark"><i class="bi bi-printer me-1"></i> {{ __('ops.print') }}</button>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-label">{{ __('app.active_projects') }}</div>
            <div class="kpi-value">{{ $totals['projects'] }}</div>
            <span class="kpi-icon"><i class="bi bi-map"></i></span>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card kpi-success">
            <div class="kpi-label">{{ __('app.plots_available') }}</div>
            <div class="kpi-value"><span class="text-success">{{ $totals['plots_available'] }}</span> / {{ $totals['plots_total'] }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card kpi-danger">
            <div class="kpi-label">{{ __('app.sold') }}</div>
            <div class="kpi-value">{{ $totals['plots_sold'] }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card kpi-warning">
            <div class="kpi-label">{{ __('ops.active_plot_holds') }}</div>
            <div class="kpi-value">{{ $totals['active_holds'] }}</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover table-stack align-middle mb-0 small">
            <thead class="table-light">
                <tr>
                    <th>{{ __('admin.project') }}</th>
                    <th>{{ __('ops.report_col_category') }}</th>
                    <th>{{ __('app.status') }}</th>
                    <th>{{ __('app.location') }}</th>
                    <th class="text-end">{{ __('ops.report_col_plots') }}</th>
                    <th class="text-end">{{ __('app.starting_price') }}</th>
                    <th>{{ __('admin.published') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($projects as $p)
                    <tr>
                        <td class="stack-primary fw-bold">{{ $p->title }}
                            @if($p->service)<div class="text-muted small fw-normal">{{ $p->service->title }}</div>@endif
                        </td>
                        <td><span class="badge bg-light text-dark border">{{ $p->project_category === 'service' ? __('pages.projects_service') : __('pages.projects_land') }}</span></td>
                        <td>{{ $p->project_status }}</td>
                        <td>{{ trim(($p->location_name ?: '').', '.$p->city, ', ') }}</td>
                        <td class="text-end">
                            @if($p->project_category === 'land')
                                <span class="text-success fw-bold">{{ $p->available_units }}</span>/{{ $p->total_units }}
                                <small class="text-danger d-block">{{ $p->sold_plots }} {{ __('app.sold') }}</small>
                            @else
                                &mdash;
                            @endif
                        </td>
                        <td class="text-end fw-bold">{{ $p->starting_price > 0 ? format_money_short($p->starting_price) : '—' }}</td>
                        <td>
                            <span class="badge {{ $p->is_published ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">
                                {{ $p->is_published ? __('admin.published') : __('admin.draft') }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7"><div class="empty-state"><div class="empty-icon"><i class="bi bi-map"></i></div><h6>{{ __('app.no_projects_found') }}</h6></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
