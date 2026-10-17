@extends('layouts.app')

@section('title', __('app.notifications'))

@section('content')
@php
    $logBadge = ['Sent' => 'paid', 'Delivered' => 'active', 'Pending' => 'pending', 'Queued' => 'pending', 'Failed' => 'overdue'];
@endphp

<div class="page-header">
    <div class="min-w-0">
        <h1 class="brand-font mb-1"><i class="bi bi-bell-fill text-primary me-2"></i>{{ __('ops.notifications_title') }}</h1>
        <p class="text-muted small mb-0">{{ __('ops.notifications_subtitle') }}</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header brand-font d-flex justify-content-between align-items-center gap-2">
                <span>{{ __('ops.templates_title') }}</span>
                <span class="badge bg-primary-subtle text-primary">{{ $templates->count() }}</span>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($templates as $tmpl)
                        <li class="list-group-item p-3">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                                <div class="fw-bold min-w-0 text-truncate">{{ $tmpl->name }}</div>
                                <span class="badge bg-light text-dark border text-nowrap">{{ $tmpl->channel }}</span>
                            </div>
                            <code class="small">{{ $tmpl->code }}</code>
                            <div class="p-2 mt-2 bg-light rounded text-muted small font-monospace text-break">{{ $tmpl->body_template }}</div>
                        </li>
                    @empty
                        <li class="list-group-item">
                            <div class="empty-state py-4">
                                <div class="empty-icon"><i class="bi bi-chat-square-text"></i></div>
                                <h6>{{ __('ops.no_templates') }}</h6>
                                <p class="small mb-0">{{ __('ops.no_templates_hint') }}</p>
                            </div>
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header brand-font">{{ __('ops.logs_title') }}</div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 table-stack small">
                    <thead class="table-light">
                        <tr>
                            <th>{{ __('ops.recipient') }}</th>
                            <th>{{ __('ops.channel') }}</th>
                            <th>{{ __('app.status') }}</th>
                            <th>{{ __('ops.timestamp') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $lg)
                            <tr>
                                <td class="stack-primary">{{ $lg->customer?->full_name ?? format_phone_tz($lg->recipient) }}</td>
                                <td><span class="badge bg-light text-dark border">{{ $lg->channel }}</span></td>
                                <td><span class="badge badge-status-{{ $logBadge[$lg->status] ?? strtolower((string) $lg->status) }}">{{ $lg->status }}</span></td>
                                <td>{{ $lg->created_at->format('d M Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="stack-no-label">
                                    <div class="empty-state py-4">
                                        <div class="empty-icon"><i class="bi bi-send"></i></div>
                                        <h6>{{ __('ops.no_logs') }}</h6>
                                        <p class="small mb-0">{{ __('ops.no_logs_hint') }}</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
