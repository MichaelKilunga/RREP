@extends('layouts.app')

@section('title', __('ops.report_requests_short'))

@section('content')
@php
    $newCount = $requests->where('status', 'New')->count();
    $resolvedCount = $requests->whereIn('status', ['Resolved', 'Closed'])->count();
@endphp

<div class="page-header d-print-none">
    <div class="min-w-0">
        <h1 class="brand-font mb-1">{{ __('ops.report_requests_heading') }}</h1>
        <p class="text-muted small mb-0">{{ __('ops.report_generated_at', ['date' => now()->format('d M Y, H:i')]) }} EAT</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('reports.index') }}" class="btn btn-light border"><i class="bi bi-arrow-left me-1"></i> {{ __('ops.reports_center') }}</a>
        <a href="{{ route('reports.requests', ['export' => 'csv']) }}" class="btn btn-outline-primary"><i class="bi bi-filetype-csv me-1"></i> CSV</a>
        <button type="button" onclick="window.print()" class="btn btn-dark"><i class="bi bi-printer me-1"></i> {{ __('ops.print') }}</button>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-label">{{ __('ops.requests_title') }}</div>
            <div class="kpi-value">{{ $requests->count() }}</div>
            <span class="kpi-icon"><i class="bi bi-inboxes"></i></span>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card kpi-info">
            <div class="kpi-label">{{ __('app.new_requests') }}</div>
            <div class="kpi-value">{{ $newCount }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card kpi-success">
            <div class="kpi-label">{{ __('ops.requests_resolved') }}</div>
            <div class="kpi-value">{{ $resolvedCount }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card kpi-warning">
            <div class="kpi-label">{{ __('ops.requests_resolution_rate') }}</div>
            <div class="kpi-value">{{ $requests->count() ? number_format($resolvedCount / $requests->count() * 100, 1) : 0 }}%</div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-12 col-lg-6">
        <div class="card p-3 h-100">
            <h2 class="h6 brand-font mb-3">{{ __('ops.requests_by_type') }}</h2>
            @foreach($byType as $row)
                <div class="d-flex justify-content-between align-items-center small mb-1">
                    <span>{{ $row->inquiry_type }}</span><span class="fw-bold">{{ $row->count }}</span>
                </div>
                <div class="progress progress-thin rounded-pill mb-2">
                    <div class="progress-bar bg-primary rounded-pill" style="width: {{ $requests->count() ? ($row->count / $requests->count()) * 100 : 0 }}%"></div>
                </div>
            @endforeach
        </div>
    </div>
    <div class="col-12 col-lg-6">
        <div class="card p-3 h-100">
            <h2 class="h6 brand-font mb-3">{{ __('ops.requests_by_status') }}</h2>
            @foreach($byStatus as $row)
                <div class="d-flex justify-content-between align-items-center small mb-1">
                    <span>{{ __('ops.request_status_'.strtolower($row->status)) }}</span><span class="fw-bold">{{ $row->count }}</span>
                </div>
                <div class="progress progress-thin rounded-pill mb-2">
                    <div class="progress-bar bg-success rounded-pill" style="width: {{ $requests->count() ? ($row->count / $requests->count()) * 100 : 0 }}%"></div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover table-stack align-middle mb-0 small">
            <thead class="table-light">
                <tr>
                    <th>{{ __('ops.request_col_type') }}</th>
                    <th>{{ __('ops.request_col_contact') }}</th>
                    <th>{{ __('ops.request_col_subject') }}</th>
                    <th>{{ __('ops.request_col_received') }}</th>
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
                            <div class="fw-semibold small">{{ $req->property?->title ?? $req->project?->title ?? __('ops.general_request') }}</div>
                            <div class="text-muted small text-truncate" style="max-width: 360px;">{{ Str::limit($req->message, 100) }}</div>
                        </td>
                        <td class="text-nowrap text-muted">{{ $req->created_at?->format('d M Y, H:i') }}</td>
                        <td><span class="badge bg-light text-dark border">{{ __('ops.request_status_'.strtolower($req->status)) }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="5"><div class="empty-state"><div class="empty-icon"><i class="bi bi-inboxes"></i></div><h6>{{ __('ops.no_requests') }}</h6></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
