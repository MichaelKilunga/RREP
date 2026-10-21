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

<div class="card mb-4">
    <div class="card-header brand-font d-flex flex-wrap justify-content-between align-items-center gap-2">
        <span><i class="bi bi-inbox me-2 text-primary"></i>{{ __('ops.inbox_title') }}
            @if($inbox->total())<span class="badge bg-primary-subtle text-primary ms-1">{{ $inbox->total() }}</span>@endif
        </span>
        <form action="{{ route('notifications.read_all') }}" method="POST" class="m-0">
            @csrf
            <button type="submit" class="btn btn-sm btn-light border"><i class="bi bi-check2-all me-1"></i>{{ __('app.mark_all_read') }}</button>
        </form>
    </div>
    <div class="list-group list-group-flush">
        @forelse($inbox as $note)
            <a href="{{ route('notifications.open', $note) }}" class="list-group-item list-group-item-action d-flex gap-3 align-items-start {{ $note->read_at ? '' : 'bg-primary bg-opacity-10' }}">
                <span class="btn-icon bg-light border flex-shrink-0"><i class="bi {{ $note->icon() }}"></i></span>
                <span class="min-w-0 flex-grow-1">
                    <span class="d-block fw-semibold">{{ $note->title }}</span>
                    <span class="d-block small text-muted">{{ $note->body }}</span>
                    <span class="d-block small text-muted">{{ $note->created_at->diffForHumans() }}</span>
                </span>
                @unless($note->read_at)<span class="badge bg-primary rounded-pill flex-shrink-0">{{ __('ops.new_label') }}</span>@endunless
            </a>
        @empty
            <div class="empty-state">
                <div class="empty-icon"><i class="bi bi-bell-slash"></i></div>
                <h6>{{ __('app.no_notifications') }}</h6>
                <p class="small mb-0">{{ __('ops.inbox_hint') }}</p>
            </div>
        @endforelse
    </div>
    @if($inbox->hasPages())
        <div class="card-footer d-flex justify-content-center overflow-auto">{{ $inbox->links('pagination::bootstrap-5') }}</div>
    @endif
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
