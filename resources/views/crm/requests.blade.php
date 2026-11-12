@extends('layouts.app')

@section('title', __('ops.requests_title'))

@section('content')
@php
    $canManage = auth()->user()->can('manage_requests');
    $statusTone = ['New' => 'warning', 'Contacted' => 'info', 'Resolved' => 'success', 'Closed' => 'secondary'];
    $typeLabel = function ($type) {
        $key = 'ops.request_type_'.Str::slug($type, '_');
        return __($key) !== $key ? __($key) : $type;
    };
@endphp

<div class="page-header">
    <div class="min-w-0">
        <h1 class="brand-font mb-1"><i class="bi bi-inboxes text-primary me-2"></i>{{ __('ops.requests_title') }}</h1>
        <p class="text-muted small mb-0">{{ __('ops.requests_subtitle') }}</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('crm.requests', array_merge(request()->except('page', 'mine'), ['mine' => request()->boolean('mine') ? null : 1])) }}" class="btn {{ request()->boolean('mine') ? 'btn-primary' : 'btn-light border' }}"><i class="bi bi-person-check me-1"></i>{{ __('ops.assigned_to_me') }}</a>
        @can('view_reports')<a href="{{ route('reports.requests') }}" class="btn btn-light border"><i class="bi bi-bar-chart me-1"></i>{{ __('app.reports') }}</a>@endcan
    </div>
</div>

<div class="card p-2 p-md-3 mb-3">
    <div class="scroll-x pb-1 mb-2">
        <a href="{{ route('crm.requests', request()->except('type', 'page')) }}" class="btn btn-sm rounded-pill px-3 {{ ! request('type') ? 'btn-primary' : 'btn-outline-primary' }}">
            {{ __('admin.cat_all') }} <span class="badge bg-white text-primary ms-1">{{ $typeCounts->sum() }}</span>
        </a>
        @foreach($typeCounts as $type => $count)
            <a href="{{ route('crm.requests', array_merge(request()->except('page'), ['type' => $type])) }}" class="btn btn-sm rounded-pill px-3 {{ request('type') === $type ? 'btn-primary' : 'btn-outline-primary' }}">
                {{ $typeLabel($type) }}
                <span class="badge {{ request('type') === $type ? 'bg-white text-primary' : 'bg-primary-subtle text-primary' }} ms-1">{{ $count }}</span>
            </a>
        @endforeach
    </div>
    <form method="GET" action="{{ route('crm.requests') }}" class="filter-bar">
        @foreach(request()->only('type', 'mine') as $k => $v)<input type="hidden" name="{{ $k }}" value="{{ $v }}">@endforeach
        <div class="flex-grow-1">
            <div class="input-group input-group-sm">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="search" name="q" class="form-control" value="{{ request('q') }}" placeholder="{{ __('ops.request_search_placeholder') }}" aria-label="{{ __('app.search') }}">
            </div>
        </div>
        <div class="btn-group btn-group-sm" role="group" aria-label="{{ __('app.status') }}">
            <a href="{{ route('crm.requests', request()->except('status', 'page')) }}" class="btn {{ ! request('status') ? 'btn-dark' : 'btn-outline-dark' }}">{{ __('ops.all_statuses') }}</a>
            @foreach(\App\Http\Controllers\CRMController::REQUEST_STATUSES as $st)
                <a href="{{ route('crm.requests', array_merge(request()->except('page'), ['status' => $st])) }}" class="btn {{ request('status') === $st ? 'btn-'.$statusTone[$st] : 'btn-outline-'.$statusTone[$st] }}">{{ __('ops.request_status_'.strtolower($st)) }} <span class="small">({{ $statusCounts[$st] ?? 0 }})</span></a>
            @endforeach
        </div>
        <div class="filter-actions">
            <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-search"></i></button>
            <a href="{{ route('crm.requests') }}" class="btn btn-sm btn-light border">{{ __('ops.reset') }}</a>
        </div>
    </form>
</div>

<div class="card">
    @if($canManage && $requests->count() > 0)
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 py-2">
            <span class="small text-muted">{{ __('ops.bulk_select_hint') }}</span>
            <button type="button" class="btn btn-sm btn-light border" data-bulk-all="requests" aria-pressed="false" data-label-all="{{ __('admin.select_all') }}" data-label-none="{{ __('admin.deselect_all') }}">
                <i class="bi bi-check2-square me-1"></i><span data-bulk-all-label>{{ __('admin.select_all') }} ({{ $requests->count() }})</span>
            </button>
        </div>
    @endif
    <div class="table-responsive">
        <table class="table table-hover table-stack align-middle mb-0">
            <thead class="table-light">
                <tr>
                    @if($canManage)<th class="stack-check" style="width:2.5rem;"><input type="checkbox" class="form-check-input" id="reqSelectAll" data-bulk-all="requests" aria-label="{{ __('admin.select_all') }}"></th>@endif
                    <th>{{ __('ops.request_col_type') }}</th>
                    <th>{{ __('ops.request_col_contact') }}</th>
                    <th>{{ __('ops.request_col_subject') }}</th>
                    <th>{{ __('ops.request_col_received') }}</th>
                    <th>{{ __('app.status') }}</th>
                    <th class="text-end">{{ __('app.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $req)
                    <tr>
                        @if($canManage)<td class="stack-check"><input type="checkbox" class="form-check-input req-check" value="{{ $req->id }}" data-bulk-item="requests" aria-label="{{ $req->name }}"></td>@endif
                        <td class="stack-primary">
                            <span class="badge bg-primary-subtle text-primary">{{ $typeLabel($req->inquiry_type) }}</span>
                            <div class="text-muted small mt-1">{{ $req->source }}</div>
                        </td>
                        <td>
                            <div class="fw-semibold">
                                @if($req->customer)
                                    <a href="{{ route('crm.customers.show', $req->customer) }}" class="text-dark">{{ $req->name }}</a>
                                @else
                                    {{ $req->name }}
                                @endif
                            </div>
                            <div class="d-flex flex-wrap align-items-center gap-2 small">
                                <a href="tel:{{ preg_replace('/\D+/', '', $req->phone) }}" class="text-decoration-none">{{ format_phone_tz($req->phone) }}</a>
                                <a href="https://wa.me/{{ preg_replace('/\D+/', '', format_phone_tz($req->phone)) }}" target="_blank" rel="noopener" class="btn btn-whatsapp btn-sm py-0 px-2" title="WhatsApp"><i class="bi bi-whatsapp"></i></a>
                                @if($req->email)<span class="text-muted">{{ $req->email }}</span>@endif
                            </div>
                        </td>
                        <td>
                            <div class="fw-semibold small">{{ $req->property?->title ?? $req->project?->title ?? $req->service?->title ?? __('ops.general_request') }}</div>
                            <div class="text-muted small" style="max-width: 420px;">{{ Str::limit($req->message, 160) }}</div>
                            @if($req->preferred_date)
                                <div class="small text-info"><i class="bi bi-calendar-event me-1"></i>{{ $req->preferred_date->format('d M Y') }} {{ $req->preferred_time }}</div>
                            @endif
                            @if($req->staff_notes)
                                <div class="small mt-1 p-1 px-2 rounded bg-light border"><i class="bi bi-sticky me-1 text-warning"></i>{{ Str::limit($req->staff_notes, 120) }}</div>
                            @endif
                        </td>
                        <td class="text-nowrap small text-muted" title="{{ $req->created_at }}">
                            {{ $req->created_at->format('d M Y, H:i') }}
                            <div>{{ $req->created_at->diffForHumans() }}</div>
                        </td>
                        <td>
                            <span class="badge bg-{{ $statusTone[$req->status] ?? 'secondary' }}{{ in_array($req->status, ['New']) ? ' text-dark' : '' }}">{{ __('ops.request_status_'.strtolower($req->status)) }}</span>
                            @if($req->assignee)<div class="small text-muted mt-1"><i class="bi bi-person me-1"></i>{{ $req->assignee->name }}</div>@endif
                            @if($req->lead_id)<div class="small text-success mt-1"><i class="bi bi-funnel me-1"></i>{{ __('ops.in_pipeline') }}</div>@endif
                        </td>
                        <td class="stack-actions text-end">
                            @if($canManage)
                                <div class="d-inline-flex gap-1">
                                    <button type="button" class="btn btn-sm btn-primary req-handle-btn" title="{{ __('ops.handle_request') }}"
                                        data-url="{{ route('crm.requests.update', $req) }}" data-name="{{ $req->name }}"
                                        data-status="{{ $req->status }}" data-assigned="{{ $req->assigned_to }}" data-notes="{{ $req->staff_notes }}">
                                        <i class="bi bi-pencil-square"></i><span class="d-md-none ms-1">{{ __('ops.handle_request') }}</span>
                                    </button>
                                    @can('create_leads')
                                        @unless($req->lead_id)
                                            <form action="{{ route('crm.requests.convert', $req) }}" method="POST" class="m-0">@csrf
                                                <button type="submit" class="btn btn-sm btn-light border" title="{{ __('ops.convert_to_lead') }}" aria-label="{{ __('ops.convert_to_lead') }}"><i class="bi bi-funnel text-info"></i></button>
                                            </form>
                                        @endunless
                                    @endcan
                                    <form action="{{ route('crm.requests.destroy', $req) }}" method="POST" class="m-0" onsubmit="return confirm(@json(__('ops.request_delete_confirm')));">@csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-light border" title="{{ __('app.delete') }}" aria-label="{{ __('app.delete') }}"><i class="bi bi-trash text-danger"></i></button>
                                    </form>
                                </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
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

@if($canManage)
<!-- Bulk bar -->
<form action="{{ route('crm.requests.bulk') }}" method="POST" id="reqBulkForm">
    @csrf
    <div class="bulk-bar flex-column d-none" id="reqBulkBar">
        <div class="bulk-inner">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary rounded-pill fs-6" id="reqBulkCount">0</span>
                <span class="text-white small fw-semibold">{{ __('ops.requests_selected') }}</span>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <button type="button" class="btn btn-sm btn-outline-light rounded-pill px-3" data-bulk-all="requests" data-label-all="{{ __('admin.select_all') }}" data-label-none="{{ __('admin.deselect_all') }}">
                    <i class="bi bi-check2-all me-1"></i><span data-bulk-all-label>{{ __('admin.select_all') }}</span>
                </button>
                <div class="input-group input-group-sm w-auto">
                    <select name="status" class="form-select form-select-sm" aria-label="{{ __('app.status') }}">
                        @foreach(\App\Http\Controllers\CRMController::REQUEST_STATUSES as $st)
                            <option value="{{ $st }}">{{ __('ops.request_status_'.strtolower($st)) }}</option>
                        @endforeach
                    </select>
                    <button type="submit" name="action" value="status" class="btn btn-light">{{ __('admin.set_status') }}</button>
                </div>
                <button type="submit" name="action" value="delete" class="btn btn-sm btn-danger rounded-pill px-3" data-confirm="{{ __('ops.requests_bulk_delete_confirm') }}"><i class="bi bi-trash me-1"></i>{{ __('app.delete') }}</button>
            </div>
        </div>
    </div>
</form>

<!-- Handle request (shared) -->
<div class="modal fade" id="handleRequestModal" tabindex="-1" aria-labelledby="handleRequestLabel">
    <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
        <form class="modal-content" method="POST" id="handleRequestForm">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title" id="handleRequestLabel">{{ __('ops.handle_request') }}: <span id="handleRequestName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('app.cancel') }}"></button>
            </div>
            <div class="modal-body">
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label small fw-semibold" for="handleStatus">{{ __('app.status') }}</label>
                        <select id="handleStatus" name="status" class="form-select">
                            @foreach(\App\Http\Controllers\CRMController::REQUEST_STATUSES as $st)
                                <option value="{{ $st }}">{{ __('ops.request_status_'.strtolower($st)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold" for="handleAssigned">{{ __('ops.assigned_to') }}</label>
                        <select id="handleAssigned" name="assigned_to" class="form-select">
                            <option value="">{{ __('ops.unassigned') }}</option>
                            @foreach($staff as $member)
                                <option value="{{ $member->id }}">{{ $member->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <label class="form-label small fw-semibold" for="handleNotes">{{ __('ops.internal_notes') }}</label>
                <textarea id="handleNotes" name="staff_notes" rows="4" class="form-control" maxlength="2000" placeholder="{{ __('ops.request_notes_placeholder') }}"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">{{ __('app.cancel') }}</button>
                <button type="submit" class="btn btn-primary">{{ __('app.save') }}</button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.req-handle-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const form = document.getElementById('handleRequestForm');
            form.action = btn.dataset.url;
            form.status.value = btn.dataset.status;
            form.assigned_to.value = btn.dataset.assigned || '';
            form.staff_notes.value = btn.dataset.notes || '';
            document.getElementById('handleRequestName').textContent = btn.dataset.name;
            bootstrap.Modal.getOrCreateInstance(document.getElementById('handleRequestModal')).show();
        });
    });

    const checks = Array.from(document.querySelectorAll('.req-check'));
    const bar = document.getElementById('reqBulkBar');
    function refresh() {
        const n = checks.filter(c => c.checked).length;
        if (!bar) { return; }
        document.getElementById('reqBulkCount').textContent = n;
        bar.classList.toggle('d-none', n === 0);
        bar.classList.toggle('d-flex', n > 0);
    }
    checks.forEach(c => c.addEventListener('change', refresh));
    document.getElementById('reqBulkForm')?.addEventListener('submit', function (e) {
        const msg = e.submitter && e.submitter.dataset.confirm;
        if (msg && !window.confirm(msg)) { e.preventDefault(); return; }
        this.querySelectorAll('input[name="ids[]"]').forEach(i => i.remove());
        checks.filter(c => c.checked).forEach(c => {
            const input = document.createElement('input');
            input.type = 'hidden'; input.name = 'ids[]'; input.value = c.value;
            this.appendChild(input);
        });
    });
});
</script>
@endsection
