@extends('layouts.app')

@section('title', __('ops.requests_title'))

@section('content')
<div class="page-header">
    <div class="min-w-0">
        <h1 class="brand-font mb-1"><i class="bi bi-inboxes text-primary me-2"></i>{{ __('ops.requests_title') }}</h1>
        <p class="text-muted small mb-0">{{ __('ops.requests_subtitle') }}</p>
    </div>
</div>

<div class="card p-2 p-md-3 mb-3">
    <div class="d-flex flex-wrap align-items-center gap-2">
        <div class="scroll-x pb-1 flex-grow-1">
            <a href="{{ route('crm.requests') }}" class="btn btn-sm rounded-pill px-3 {{ ! request('type') ? 'btn-primary' : 'btn-outline-primary' }}">
                {{ __('admin.cat_all') }} <span class="badge bg-white text-primary ms-1">{{ $typeCounts->sum() }}</span>
            </a>
            @foreach($typeCounts as $type => $count)
                <a href="{{ route('crm.requests', ['type' => $type]) }}" class="btn btn-sm rounded-pill px-3 {{ request('type') === $type ? 'btn-primary' : 'btn-outline-primary' }}">
                    {{ __('ops.request_type_'.Str::slug($type, '_')) !== 'ops.request_type_'.Str::slug($type, '_') ? __('ops.request_type_'.Str::slug($type, '_')) : $type }}
                    <span class="badge {{ request('type') === $type ? 'bg-white text-primary' : 'bg-primary-subtle text-primary' }} ms-1">{{ $count }}</span>
                </a>
            @endforeach
        </div>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover table-stack align-middle mb-0">
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
                        <td class="stack-primary">
                            <span class="badge bg-primary-subtle text-primary">{{ $req->inquiry_type }}</span>
                            <div class="text-muted small mt-1">{{ $req->source }}</div>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $req->name }}</div>
                            <div class="d-flex flex-wrap align-items-center gap-2 small">
                                <a href="tel:{{ preg_replace('/\D+/', '', $req->phone) }}" class="text-decoration-none">{{ format_phone_tz($req->phone) }}</a>
                                <a href="https://wa.me/{{ preg_replace('/\D+/', '', format_phone_tz($req->phone)) }}" target="_blank" rel="noopener" class="btn btn-whatsapp btn-sm py-0 px-2" title="WhatsApp"><i class="bi bi-whatsapp"></i></a>
                                @if($req->email)<span class="text-muted">{{ $req->email }}</span>@endif
                            </div>
                        </td>
                        <td>
                            <div class="fw-semibold small">{{ $req->property?->title ?? $req->project?->title ?? __('ops.general_request') }}</div>
                            <div class="text-muted small" style="max-width: 420px;">{{ Str::limit($req->message, 160) }}</div>
                            @if($req->preferred_date)
                                <div class="small text-info"><i class="bi bi-calendar-event me-1"></i>{{ $req->preferred_date->format('d M Y') }} {{ $req->preferred_time }}</div>
                            @endif
                        </td>
                        <td class="text-nowrap small text-muted" title="{{ $req->created_at }}">
                            {{ $req->created_at->format('d M Y, H:i') }}
                            <div>{{ $req->created_at->diffForHumans() }}</div>
                        </td>
                        <td class="stack-actions">
                            <form action="{{ route('crm.requests.status', $req) }}" method="POST" class="m-0" data-no-loading>
                                @csrf
                                <select name="status" class="form-select form-select-sm w-auto badge-status-{{ strtolower($req->status) }}" onchange="this.form.submit()" aria-label="{{ __('app.status') }}">
                                    @foreach(['New', 'Contacted', 'Resolved', 'Closed'] as $status)
                                        <option value="{{ $status }}" @selected($req->status === $status)>{{ __('ops.request_status_'.strtolower($status)) }}</option>
                                    @endforeach
                                </select>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <div class="empty-state">
                                <div class="empty-icon"><i class="bi bi-inboxes"></i></div>
                                <h6>{{ __('ops.no_requests') }}</h6>
                                <p class="small mb-0">{{ __('ops.no_requests_hint') }}</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($requests->hasPages())
        <div class="p-3 border-top d-flex justify-content-center justify-content-md-end overflow-auto">
            {{ $requests->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
