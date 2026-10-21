@extends('layouts.app')

@section('title', __('admin.services_title'))

@section('content')
@php
    $categoryLabels = [
        'Land' => __('admin.category_land'),
        'Surveying' => __('admin.category_surveying'),
        'Planning' => __('admin.category_planning'),
        'Consultancy' => __('admin.category_consultancy'),
        'Legal' => __('admin.category_legal'),
        'Development' => __('admin.category_development'),
        'Property' => __('admin.category_property'),
    ];
@endphp

<div class="page-header">
    <div class="min-w-0">
        <h1 class="brand-font">{{ __('admin.services_title') }}</h1>
        <p class="text-muted small mb-0">{{ __('admin.services_subtitle') }}</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.services.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> {{ __('admin.add_service') }}
        </a>
    </div>
</div>

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

<div class="card">
    @if($services->isEmpty())
        <div class="empty-state">
            <div class="empty-icon"><i class="bi bi-briefcase"></i></div>
            <h5>{{ __('admin.no_services_title') }}</h5>
            <p class="mb-3">{{ __('admin.no_services_text') }}</p>
            <a href="{{ route('admin.services.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> {{ __('admin.add_service') }}</a>
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover table-stack align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center">
                            <input type="checkbox" class="form-check-input" id="selectAllSrvCheckboxes" aria-label="{{ __('admin.deselect_all') }}">
                        </th>
                        <th>{{ __('admin.service') }}</th>
                        <th>{{ __('admin.order') }}</th>
                        <th>{{ __('admin.short_description') }}</th>
                        <th>{{ __('admin.sub_services') }}</th>
                        <th>{{ __('app.status') }}</th>
                        <th class="text-end">{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($services as $srv)
                        <tr id="srv-row-{{ $srv->id }}" data-id="{{ $srv->id }}">
                            <td class="stack-check text-center">
                                <input type="checkbox" class="form-check-input srv-checkbox" value="{{ $srv->id }}" aria-label="{{ $srv->title }}">
                            </td>
                            <td class="stack-primary">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="btn-icon bg-primary-subtle text-primary fs-5"><i class="bi {{ $srv->icon }}"></i></span>
                                    <div class="min-w-0">
                                        <a href="{{ route('admin.services.edit', $srv) }}" class="fw-bold text-dark text-decoration-none d-block line-clamp-2">{{ $srv->title }}</a>
                                        <span class="badge bg-light text-secondary border">{{ $categoryLabels[$srv->category] ?? $srv->category }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="text-muted">#{{ $srv->display_order }}</td>
                            <td><span class="text-muted small line-clamp-2 max-w-500">{{ $srv->short_description }}</span></td>
                            <td>
                                @if(! empty($srv->sub_services))
                                    <div class="d-flex flex-wrap gap-1 justify-content-end justify-content-md-start">
                                        @foreach($srv->sub_services as $sub)
                                            <span class="badge bg-light text-primary border fw-semibold">{{ $sub }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $srv->is_active ? 'bg-success' : 'bg-secondary' }} srv-status-badge">
                                    {{ $srv->is_active ? __('admin.active') : __('admin.disabled') }}
                                </span>
                            </td>
                            <td class="stack-actions text-end">
                                <div class="d-inline-flex gap-1 align-items-center">
                                    <a href="{{ route('admin.services.edit', $srv) }}" class="btn btn-light border btn-icon" title="{{ __('app.edit') }}" aria-label="{{ __('app.edit') }}"><i class="bi bi-pencil text-primary"></i></a>
                                    <form action="{{ route('admin.services.destroy', $srv) }}" method="POST" class="d-inline" onsubmit="return confirm(@json(__('admin.confirm_delete')))">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-light border btn-icon text-danger" title="{{ __('app.delete') }}" aria-label="{{ __('app.delete') }}"><i class="bi bi-trash"></i></button>
                                    </form>
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
