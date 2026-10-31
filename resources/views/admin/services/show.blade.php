@extends('layouts.app')

@section('title', $service->title)

@section('content')
@php $canManage = auth()->user()->can('manage_services'); @endphp

<div class="page-header">
    <div class="min-w-0">
        <a href="{{ route('admin.services.index') }}" class="small text-muted text-decoration-none"><i class="bi bi-arrow-left me-1"></i>{{ __('admin.services_title') }}</a>
        <h1 class="brand-font mb-1 text-truncate"><i class="bi {{ $service->icon }} text-primary me-2"></i>{{ $service->title }}</h1>
        <div class="d-flex flex-wrap gap-1 small">
            <span class="badge bg-light text-secondary border">{{ $service->category }}</span>
            <span class="badge {{ $service->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $service->is_active ? __('admin.active') : __('admin.disabled') }}</span>
        </div>
    </div>
    <div class="page-actions">
        <a href="{{ route('public.services.detail', $service->slug) }}" target="_blank" rel="noopener" class="btn btn-light border"><i class="bi bi-box-arrow-up-right me-1"></i>{{ __('admin.view_on_website') }}</a>
        @if($canManage)
            <form action="{{ route('admin.services.toggle', $service) }}" method="POST" class="m-0">@csrf
                <button class="btn btn-light border"><i class="bi {{ $service->is_active ? 'bi-eye-slash' : 'bi-eye' }} me-1"></i>{{ $service->is_active ? __('admin.hide_service') : __('admin.show_service') }}</button>
            </form>
            <a href="{{ route('admin.services.edit', $service) }}" class="btn btn-primary"><i class="bi bi-pencil me-1"></i>{{ __('app.edit') }}</a>
        @endif
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3"><div class="kpi-card"><div class="kpi-label">{{ __('admin.quote_requests') }}</div><div class="kpi-value">{{ $service->requests_count }}</div><span class="kpi-icon"><i class="bi bi-inboxes"></i></span></div></div>
    <div class="col-6 col-md-3"><div class="kpi-card kpi-warning"><div class="kpi-label">{{ __('admin.quote_requests_new') }}</div><div class="kpi-value">{{ $stats['new'] }}</div></div></div>
    <div class="col-6 col-md-3"><div class="kpi-card kpi-info"><div class="kpi-label">{{ __('admin.quote_requests_month') }}</div><div class="kpi-value">{{ $stats['month'] }}</div></div></div>
    <div class="col-6 col-md-3"><div class="kpi-card kpi-success"><div class="kpi-label">{{ __('admin.requests_resolved') }}</div><div class="kpi-value">{{ $stats['resolved'] }}</div></div></div>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-4">
        <div class="card overflow-hidden mb-4">
            @if($service->thumbnail_url)
                <div class="ratio ratio-16x9"><img src="{{ $service->thumbnail_url }}" alt="{{ $service->title }}" class="object-fit-cover"></div>
            @endif
            <div class="card-body">
                <p class="text-muted small">{{ $service->short_description }}</p>
                @if(! empty($service->sub_services))
                    <div class="d-flex flex-wrap gap-1 mb-3">
                        @foreach($service->sub_services as $sub)
                            <span class="badge bg-light text-primary border fw-semibold">{{ $sub }}</span>
                        @endforeach
                    </div>
                @endif
                <dl class="row small mb-0">
                    <dt class="col-5 text-muted">{{ __('admin.cta_button') }}</dt><dd class="col-7">{{ $service->cta_label }}</dd>
                    <dt class="col-5 text-muted">{{ __('admin.order') }}</dt><dd class="col-7">#{{ $service->display_order }}</dd>
                    <dt class="col-5 text-muted">{{ __('admin.nav_projects_short') }}</dt><dd class="col-7">{{ $service->projects_count }}</dd>
                </dl>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="brand-font">{{ __('admin.service_projects') }}</span>
                @can('manage_projects')<a href="{{ route('admin.projects.create') }}" class="small">{{ __('admin.add_project') }}</a>@endcan
            </div>
            <div class="list-group list-group-flush small">
                @forelse($projects as $project)
                    <a href="{{ route('admin.projects.show', $project) }}" class="list-group-item list-group-item-action d-flex justify-content-between gap-2">
                        <span class="text-truncate">{{ $project->title }}</span>
                        <span class="badge bg-light text-dark border flex-shrink-0">{{ $project->project_status }}</span>
                    </a>
                @empty
                    <div class="list-group-item text-muted">{{ __('admin.no_service_projects') }}</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="brand-font">{{ __('admin.service_quote_requests') }}</span>
                @can('view_requests')<a href="{{ route('crm.requests', ['type' => 'Service Quote']) }}" class="small">{{ __('app.view_all') }}</a>@endcan
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-stack align-middle mb-0 small">
                    <thead class="table-light">
                        <tr>
                            <th>{{ __('ops.request_col_contact') }}</th>
                            <th>{{ __('ops.request_col_subject') }}</th>
                            <th>{{ __('ops.request_col_received') }}</th>
                            <th>{{ __('app.status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $req)
                            <tr>
                                <td class="stack-primary">
                                    <div class="fw-semibold">{{ $req->name }}</div>
                                    <div class="d-flex gap-2 align-items-center">
                                        <a href="tel:{{ preg_replace('/\D+/', '', $req->phone) }}" class="text-decoration-none">{{ format_phone_tz($req->phone) }}</a>
                                        <a href="https://wa.me/{{ preg_replace('/\D+/', '', format_phone_tz($req->phone)) }}" target="_blank" rel="noopener" class="btn btn-whatsapp btn-sm py-0 px-2" aria-label="WhatsApp"><i class="bi bi-whatsapp"></i></a>
                                    </div>
                                </td>
                                <td class="text-muted" style="max-width: 360px;">{{ Str::limit(preg_replace('/^\[.+?\]\s*/u', '', (string) $req->message), 140) }}</td>
                                <td class="text-nowrap text-muted">{{ $req->created_at?->format('d M Y, H:i') }}<div>{{ $req->created_at?->diffForHumans() }}</div></td>
                                <td><span class="badge bg-light text-dark border">{{ __('ops.request_status_'.strtolower($req->status)) }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="4"><div class="empty-state py-4"><div class="empty-icon"><i class="bi bi-inboxes"></i></div><h6>{{ __('admin.no_service_requests') }}</h6></div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
