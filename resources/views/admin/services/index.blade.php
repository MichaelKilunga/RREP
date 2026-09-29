@extends('layouts.app')

@section('title', 'Manage Services - Admin Panel')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h3 class="fw-bold text-dark mb-1">Company Services</h3>
        <p class="text-muted small mb-0">Control and manage public services displayed on the site.</p>
    </div>
    <a href="{{ route('admin.services.create') }}" class="btn btn-primary btn-sm rounded-pill fw-bold shadow-sm">
        <i class="bi bi-plus-circle me-1"></i> Add Service
    </a>
</div>

<!-- Floating Bulk Action Drawer -->
<div class="card border-0 shadow-lg position-fixed bottom-0 start-50 translate-middle-x mb-4 px-3 py-2 bg-dark text-white rounded-pill z-3 align-items-center d-none" id="bulkServicesBar" style="z-index: 1050; min-width: 420px;">
    <div class="d-flex align-items-center justify-content-between w-100 gap-3">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-primary rounded-pill fs-6" id="bulkServicesCount">0</span>
            <span class="small fw-semibold text-white-50">Selected</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-sm btn-outline-success rounded-pill bulk-srv-action" data-action="activate">
                <i class="bi bi-check-circle me-1"></i> Activate
            </button>
            <button class="btn btn-sm btn-outline-warning rounded-pill bulk-srv-action" data-action="deactivate">
                <i class="bi bi-pause-circle me-1"></i> Deactivate
            </button>
            <button class="btn btn-sm btn-danger rounded-pill px-3" id="bulkDeleteServicesBtn">
                <i class="bi bi-trash me-1"></i> Delete
            </button>
            <button class="btn btn-sm btn-link text-white-50 p-0 text-decoration-none ms-2" id="bulkCancelServicesBtn" title="Deselect All">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 0.875rem;">
                <thead class="bg-light">
                    <tr>
                        <th style="width: 40px;" class="ps-3 text-center">
                            <input type="checkbox" class="form-check-input" id="selectAllSrvCheckboxes">
                        </th>
                        <th>Order</th>
                        <th>Icon</th>
                        <th>Title & Category</th>
                        <th>Short Description</th>
                        <th>Sub-Services</th>
                        <th>Status</th>
                        <th class="pe-4 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($services as $srv)
                        <tr id="srv-row-{{ $srv->id }}" data-id="{{ $srv->id }}">
                            <td class="ps-3 text-center">
                                <input type="checkbox" class="form-check-input srv-checkbox" value="{{ $srv->id }}">
                            </td>
                            <td class="fw-bold text-muted">#{{ $srv->display_order }}</td>
                            <td>
                                <div class="rounded-3 bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; font-size: 1.1rem;">
                                    <i class="bi {{ $srv->icon }}"></i>
                                </div>
                            </td>
                            <td>
                                <strong class="text-dark d-block">{{ $srv->title }}</strong>
                                <span class="badge bg-light text-secondary border">{{ $srv->category }}</span>
                            </td>
                            <td><small class="text-muted line-clamp-2" style="max-width: 260px;">{{ $srv->short_description }}</small></td>
                            <td>
                                @if(!empty($srv->sub_services))
                                    <div class="d-flex flex-wrap gap-1" style="max-width: 200px;">
                                        @foreach($srv->sub_services as $sub)
                                            <span class="badge bg-light text-primary border" style="font-size: 0.65rem;">{{ $sub }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $srv->is_active ? 'bg-success' : 'bg-secondary' }} srv-status-badge">
                                    {{ $srv->is_active ? 'Active' : 'Disabled' }}
                                </span>
                            </td>
                            <td class="pe-4 text-end">
                                <a href="{{ route('admin.services.edit', $srv->id) }}" class="btn btn-sm btn-light border rounded-circle me-1" title="Edit">
                                    <i class="bi bi-pencil text-primary"></i>
                                </a>
                                <form action="{{ route('admin.services.destroy', $srv->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this service?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-light border rounded-circle text-danger" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">No services created yet. Click "Add Service" to create one.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    const csrfToken = $('meta[name="csrf-token"]').attr('content');

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
                            badge.attr('class', 'badge bg-success srv-status-badge').text('Active');
                        } else {
                            badge.attr('class', 'badge bg-secondary srv-status-badge').text('Disabled');
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
                        btn.prop('disabled', false).html('<i class="bi bi-trash me-1"></i> Delete');
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
