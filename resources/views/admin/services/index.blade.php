@extends('layouts.app')

@section('title', __('admin.services_title'))

@section('content')
@php $canManage = auth()->user()->can('manage_services'); @endphp

<div class="page-header">
    <div class="min-w-0">
        <h1 class="brand-font">{{ __('admin.services_title') }}</h1>
        <p class="text-muted small mb-0">{{ __('admin.services_subtitle') }}</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('public.services') }}" target="_blank" rel="noopener" class="btn btn-light border"><i class="bi bi-box-arrow-up-right me-1"></i>{{ __('admin.view_on_website') }}</a>
        @if($canManage)
            <a href="{{ route('admin.services.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> {{ __('admin.add_service') }}
            </a>
        @endif
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-6 col-md-3"><div class="kpi-card"><div class="kpi-label">{{ __('admin.services_total') }}</div><div class="kpi-value">{{ $stats['total'] }}</div><span class="kpi-icon"><i class="bi bi-briefcase"></i></span></div></div>
    <div class="col-6 col-md-3"><div class="kpi-card kpi-success"><div class="kpi-label">{{ __('admin.services_live') }}</div><div class="kpi-value text-success">{{ $stats['active'] }}</div></div></div>
    <div class="col-6 col-md-3"><div class="kpi-card kpi-info"><div class="kpi-label">{{ __('admin.quote_requests_month') }}</div><div class="kpi-value">{{ $stats['requests_month'] }}</div></div></div>
    <div class="col-6 col-md-3"><div class="kpi-card kpi-warning"><div class="kpi-label">{{ __('admin.quote_requests_new') }}</div><div class="kpi-value">{{ $stats['requests_new'] }}</div></div></div>
</div>

<form method="GET" action="{{ route('admin.services.index') }}" class="card p-2 p-md-3 mb-3">
    <div class="filter-bar">
        <div class="flex-grow-1">
            <div class="input-group input-group-sm">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="search" name="q" class="form-control" value="{{ request('q') }}" placeholder="{{ __('admin.search_services') }}" aria-label="{{ __('app.search') }}">
            </div>
        </div>
        <div>
            <select name="category" class="form-select form-select-sm" onchange="this.form.submit()" aria-label="{{ __('admin.field_category') }}">
                <option value="">{{ __('admin.all_categories') }}</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat }}" @selected(request('category') === $cat)>{{ $cat }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()" aria-label="{{ __('app.status') }}">
                <option value="">{{ __('ops.all_statuses') }}</option>
                <option value="active" @selected(request('status') === 'active')>{{ __('admin.active') }}</option>
                <option value="hidden" @selected(request('status') === 'hidden')>{{ __('admin.disabled') }}</option>
            </select>
        </div>
        <div class="filter-actions">
            <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-search"></i></button>
            <a href="{{ route('admin.services.index') }}" class="btn btn-sm btn-light border">{{ __('ops.reset') }}</a>
        </div>
    </div>
</form>

@if($canManage)
<!-- Floating bulk action bar -->
<div class="bulk-bar flex-column d-none" id="bulkServicesBar">
    <div class="bulk-inner">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-primary rounded-pill fs-6" id="bulkServicesCount">0</span>
            <span class="small fw-semibold text-white-50">{{ __('admin.selected') }}</span>
        </div>
        <div class="d-flex align-items-center flex-wrap gap-2">
            <button class="btn btn-sm btn-outline-success rounded-pill bulk-srv-action" data-action="activate" type="button">
                <i class="bi bi-check-circle me-1"></i> {{ __('admin.activate') }}
            </button>
            <button class="btn btn-sm btn-outline-warning rounded-pill bulk-srv-action" data-action="deactivate" type="button">
                <i class="bi bi-pause-circle me-1"></i> {{ __('admin.deactivate') }}
            </button>
            <button class="btn btn-sm btn-danger rounded-pill px-3" id="bulkDeleteServicesBtn" type="button">
                <i class="bi bi-trash me-1"></i> {{ __('app.delete') }}
            </button>
            <button class="btn btn-sm btn-link text-white-50 text-decoration-none touch-target p-0" id="bulkCancelServicesBtn" type="button" title="{{ __('admin.deselect_all') }}" aria-label="{{ __('admin.deselect_all') }}">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    </div>
</div>
@endif

<div class="card">
    @if($services->isEmpty())
        <div class="empty-state">
            <div class="empty-icon"><i class="bi bi-briefcase"></i></div>
            <h5>{{ __('admin.no_services_title') }}</h5>
            <p class="mb-3">{{ __('admin.no_services_text') }}</p>
            @if($canManage)<a href="{{ route('admin.services.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> {{ __('admin.add_service') }}</a>@endif
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover table-stack align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        @if($canManage)
                            <th class="text-center"><input type="checkbox" class="form-check-input" id="selectAllSrvCheckboxes" aria-label="{{ __('admin.select_all') }}"></th>
                        @endif
                        <th>{{ __('admin.service') }}</th>
                        <th>{{ __('admin.order') }}</th>
                        <th class="text-center">{{ __('admin.quote_requests') }}</th>
                        <th class="text-center">{{ __('admin.nav_projects_short') }}</th>
                        <th>{{ __('app.status') }}</th>
                        <th class="text-end">{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($services as $srv)
                        <tr id="srv-row-{{ $srv->id }}" data-id="{{ $srv->id }}">
                            @if($canManage)
                                <td class="stack-check text-center">
                                    <input type="checkbox" class="form-check-input srv-checkbox" value="{{ $srv->id }}" aria-label="{{ $srv->title }}">
                                </td>
                            @endif
                            <td class="stack-primary">
                                <div class="d-flex align-items-center gap-2">
                                    @if($srv->thumbnail_url)
                                        <img src="{{ $srv->thumbnail_url }}" alt="" class="rounded-2 object-fit-cover flex-shrink-0" style="width: 2.75rem; height: 2.75rem;">
                                    @else
                                        <span class="btn-icon bg-primary-subtle text-primary fs-5 flex-shrink-0"><i class="bi {{ $srv->icon }}"></i></span>
                                    @endif
                                    <div class="min-w-0">
                                        <a href="{{ route('admin.services.show', $srv) }}" class="fw-bold text-dark text-decoration-none d-block line-clamp-2">{{ $srv->title }}</a>
                                        <span class="badge bg-light text-secondary border">{{ $srv->category }}</span>
                                        <span class="text-muted small d-none d-xl-inline ms-1 line-clamp-1">{{ $srv->short_description }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="text-nowrap">
                                @if($canManage && request()->query() === [])
                                    <div class="d-inline-flex align-items-center gap-1">
                                        <form action="{{ route('admin.services.move', $srv) }}" method="POST" class="m-0" data-no-loading>@csrf<input type="hidden" name="direction" value="up">
                                            <button class="btn btn-sm btn-light border btn-icon" @disabled($loop->first) title="{{ __('admin.move_up') }}" aria-label="{{ __('admin.move_up') }}"><i class="bi bi-arrow-up"></i></button>
                                        </form>
                                        <form action="{{ route('admin.services.move', $srv) }}" method="POST" class="m-0" data-no-loading>@csrf<input type="hidden" name="direction" value="down">
                                            <button class="btn btn-sm btn-light border btn-icon" @disabled($loop->last) title="{{ __('admin.move_down') }}" aria-label="{{ __('admin.move_down') }}"><i class="bi bi-arrow-down"></i></button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-muted">#{{ $loop->iteration }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="fw-bold">{{ $srv->requests_count }}</span>
                                @if($srv->new_requests_count)<span class="badge bg-warning text-dark ms-1" title="{{ __('admin.quote_requests_new') }}">{{ $srv->new_requests_count }} {{ __('ops.request_status_new') }}</span>@endif
                            </td>
                            <td class="text-center">{{ $srv->projects_count }}</td>
                            <td>
                                @if($canManage)
                                    <form action="{{ route('admin.services.toggle', $srv) }}" method="POST" class="m-0" data-no-loading>
                                        @csrf
                                        <div class="form-check form-switch m-0">
                                            <input class="form-check-input" type="checkbox" role="switch" onchange="this.form.submit()" @checked($srv->is_active) aria-label="{{ __('admin.active') }}" id="srvActive{{ $srv->id }}">
                                            <label class="form-check-label small srv-status-badge" for="srvActive{{ $srv->id }}">{{ $srv->is_active ? __('admin.active') : __('admin.disabled') }}</label>
                                        </div>
                                    </form>
                                @else
                                    <span class="badge {{ $srv->is_active ? 'bg-success' : 'bg-secondary' }} srv-status-badge">{{ $srv->is_active ? __('admin.active') : __('admin.disabled') }}</span>
                                @endif
                            </td>
                            <td class="stack-actions text-end">
                                <div class="d-inline-flex gap-1 align-items-center">
                                    <a href="{{ route('admin.services.show', $srv) }}" class="btn btn-light border btn-icon" title="{{ __('app.view') }}" aria-label="{{ __('app.view') }}"><i class="bi bi-eye"></i></a>
                                    @if($canManage)
                                        <a href="{{ route('admin.services.edit', $srv) }}" class="btn btn-light border btn-icon" title="{{ __('app.edit') }}" aria-label="{{ __('app.edit') }}"><i class="bi bi-pencil text-primary"></i></a>
                                        <form action="{{ route('admin.services.duplicate', $srv) }}" method="POST" class="m-0">@csrf
                                            <button type="submit" class="btn btn-light border btn-icon" title="{{ __('admin.duplicate') }}" aria-label="{{ __('admin.duplicate') }}"><i class="bi bi-copy"></i></button>
                                        </form>
                                        <form action="{{ route('admin.services.destroy', $srv) }}" method="POST" class="d-inline m-0" onsubmit="return confirm(@json(__('admin.confirm_delete')))">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-light border btn-icon text-danger" title="{{ __('app.delete') }}" aria-label="{{ __('app.delete') }}"><i class="bi bi-trash"></i></button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    const activeLabel = @json(__('admin.active'));
    const disabledLabel = @json(__('admin.disabled'));

    function updateBulkServicesBar() {
        const count = $('.srv-checkbox:checked').length;
        $('#bulkServicesCount').text(count);

        if (count > 0) {
            $('#bulkServicesBar').removeClass('d-none').addClass('d-flex');
        } else {
            $('#bulkServicesBar').addClass('d-none').removeClass('d-flex');
            $('#selectAllSrvCheckboxes').prop('checked', false);
        }
    }

    $('#selectAllSrvCheckboxes').on('change', function() {
        $('.srv-checkbox').prop('checked', $(this).is(':checked'));
        updateBulkServicesBar();
    });

    $(document).on('change', '.srv-checkbox', function() {
        updateBulkServicesBar();
    });

    $('#bulkCancelServicesBtn').on('click', function() {
        $('.srv-checkbox, #selectAllSrvCheckboxes').prop('checked', false);
        updateBulkServicesBar();
    });

    function showToast(message, icon = 'success') {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: icon,
            title: message,
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
    }

    // Bulk Activate/Deactivate
    $('.bulk-srv-action').on('click', function(e) {
        e.preventDefault();
        const action = $(this).data('action');
        const selectedIds = $('.srv-checkbox:checked').map(function() { return $(this).val(); }).get();

        if (selectedIds.length === 0) return;

        const btn = $(this);
        const originalHtml = btn.html();
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>');

        $.ajax({
            url: "{{ route('admin.services.bulk') }}",
            type: 'POST',
            data: {
                _token: csrfToken,
                action: action,
                ids: selectedIds
            },
            success: function(res) {
                btn.prop('disabled', false).html(originalHtml);
                if (res.success) {
                    selectedIds.forEach(id => {
                        const badge = $(`#srv-row-${id} .srv-status-badge`);
                        if (action === 'activate') {
                            badge.attr('class', 'badge bg-success srv-status-badge').text(activeLabel);
                        } else {
                            badge.attr('class', 'badge bg-secondary srv-status-badge').text(disabledLabel);
                        }
                    });
                    $('.srv-checkbox, #selectAllSrvCheckboxes').prop('checked', false);
                    updateBulkServicesBar();
                    showToast(res.message);
                }
            },
            error: function() {
                btn.prop('disabled', false).html(originalHtml);
                showToast('Action failed', 'error');
            }
        });
    });

    // Bulk Delete Services
    $('#bulkDeleteServicesBtn').on('click', function() {
        const selectedIds = $('.srv-checkbox:checked').map(function() { return $(this).val(); }).get();
        if (selectedIds.length === 0) return;

        Swal.fire({
            title: 'Delete Selected Services?',
            text: `Are you sure you want to delete ${selectedIds.length} services?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            confirmButtonText: 'Yes, Delete'
        }).then((res) => {
            if (res.isConfirmed) {
                const btn = $('#bulkDeleteServicesBtn');
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Deleting...');

                $.ajax({
                    url: "{{ route('admin.services.bulk') }}",
                    type: 'POST',
                    data: {
                        _token: csrfToken,
                        action: 'delete',
                        ids: selectedIds
                    },
                    success: function(r) {
                        btn.prop('disabled', false).html('<i class="bi bi-trash me-1"></i> {{ __('app.delete') }}');
                        if (r.success) {
                            selectedIds.forEach(id => {
                                $(`#srv-row-${id}`).fadeOut(300, function() { $(this).remove(); });
                            });
                            $('.srv-checkbox, #selectAllSrvCheckboxes').prop('checked', false);
                            updateBulkServicesBar();
                            showToast(r.message);
                        }
                    }
                });
            }
        });
    });
});
</script>
@endsection
