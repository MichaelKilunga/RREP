@extends('layouts.app')

@section('title', 'Manage Projects & Shapefiles - Admin Panel')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h3 class="brand-font mb-1">Projects & Shapefile Management</h3>
        <p class="text-muted small mb-0">Create land projects, upload GIS Shapefiles (.zip), and manage plot boundaries.</p>
    </div>
    <a href="{{ route('admin.projects.create') }}" class="btn btn-primary btn-sm rounded-pill fw-bold shadow-sm">
        <i class="bi bi-plus-circle me-1"></i> Add Project / Upload Shapefile
    </a>
</div>

<!-- Floating Bulk Action Drawer -->
<div class="card border-0 shadow-lg position-fixed bottom-0 start-50 translate-middle-x mb-4 px-3 py-2 bg-dark text-white rounded-pill z-3 align-items-center d-none" id="bulkProjectsBar" style="z-index: 1050; min-width: 440px;">
    <div class="d-flex align-items-center justify-content-between w-100 gap-3">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-primary rounded-pill fs-6" id="bulkProjectsCount">0</span>
            <span class="small fw-semibold text-white-50">Selected Projects</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-sm btn-outline-success rounded-pill bulk-proj-action" data-action="publish">
                <i class="bi bi-eye me-1"></i> Publish
            </button>
            <button class="btn btn-sm btn-outline-warning rounded-pill bulk-proj-action" data-action="unpublish">
                <i class="bi bi-eye-slash me-1"></i> Unpublish
            </button>
            <button class="btn btn-sm btn-danger rounded-pill px-3" id="bulkDeleteProjectsBtn">
                <i class="bi bi-trash me-1"></i> Delete
            </button>
            <button class="btn btn-sm btn-link text-white-50 p-0 text-decoration-none ms-2" id="bulkCancelProjectsBtn" title="Deselect All">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: 0.875rem;">
            <thead class="table-light">
                <tr>
                    <th style="width: 40px;" class="ps-3 text-center">
                        <input type="checkbox" class="form-check-input" id="selectAllProjCheckboxes">
                    </th>
                    <th>Project Title</th>
                    <th>Location / Council</th>
                    <th>Plots Stat</th>
                    <th>Starting Price</th>
                    <th>GIS / Shapefile</th>
                    <th>Status</th>
                    <th class="pe-4 text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($projects as $proj)
                    <tr id="proj-row-{{ $proj->id }}" data-id="{{ $proj->id }}">
                        <td class="ps-3 text-center">
                            <input type="checkbox" class="form-check-input proj-checkbox" value="{{ $proj->id }}">
                        </td>
                        <td>
                            <strong class="text-dark d-block">{{ $proj->title }}</strong>
                            <small class="text-muted">{{ $proj->developer_name }}</small>
                        </td>
                        <td>
                            <span class="fw-semibold text-dark">{{ $proj->city }}</span><br>
                            <small class="text-muted">{{ $proj->council ?? $proj->district ?? 'Tanzania' }}</small>
                        </td>
                        <td>
                            <div class="small">
                                <span class="text-dark fw-bold">{{ $proj->total_units }}</span> Total |
                                <span class="text-success fw-bold">{{ $proj->available_units }}</span> Avail |
                                <span class="text-danger fw-bold">{{ $proj->sold_plots }}</span> Sold
                            </div>
                        </td>
                        <td>
                            <span class="fw-bold text-primary">TZS {{ number_format($proj->starting_price) }}</span>
                        </td>
                        <td>
                            @if($proj->geojson_data || $proj->shapefile_path)
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                    <i class="bi bi-geo-alt-fill me-1"></i> Shapefile Loaded
                                </span>
                            @else
                                <span class="badge bg-light text-muted border">No Shapefile</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $proj->is_published ? 'bg-success' : 'bg-secondary' }} proj-pub-badge">
                                {{ $proj->is_published ? 'Published' : 'Draft' }}
                            </span>
                        </td>
                        <td class="pe-4 text-end">
                            <a href="{{ route('public.projects.show', $proj->slug) }}" target="_blank" class="btn btn-sm btn-light border" title="View Map">
                                <i class="bi bi-map text-info"></i>
                            </a>
                            <a href="{{ route('admin.projects.edit', $proj->id) }}" class="btn btn-sm btn-light border" title="Edit">
                                <i class="bi bi-pencil text-primary"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">No projects added yet. Click "Add Project / Upload Shapefile" to get started.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    const csrfToken = $('meta[name="csrf-token"]').attr('content');

    function updateBulkProjectsBar() {
        const count = $('.proj-checkbox:checked').length;
        $('#bulkProjectsCount').text(count);

        if (count > 0) {
            $('#bulkProjectsBar').removeClass('d-none').addClass('d-flex');
        } else {
            $('#bulkProjectsBar').addClass('d-none').removeClass('d-flex');
            $('#selectAllProjCheckboxes').prop('checked', false);
        }
    }

    $('#selectAllProjCheckboxes').on('change', function() {
        $('.proj-checkbox').prop('checked', $(this).is(':checked'));
        updateBulkProjectsBar();
    });

    $(document).on('change', '.proj-checkbox', function() {
        updateBulkProjectsBar();
    });

    $('#bulkCancelProjectsBtn').on('click', function() {
        $('.proj-checkbox, #selectAllProjCheckboxes').prop('checked', false);
        updateBulkProjectsBar();
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

    // Bulk Publish/Unpublish
    $('.bulk-proj-action').on('click', function(e) {
        e.preventDefault();
        const action = $(this).data('action');
        const selectedIds = $('.proj-checkbox:checked').map(function() { return $(this).val(); }).get();

        if (selectedIds.length === 0) return;

        const btn = $(this);
        const originalHtml = btn.html();
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Processing...');

        $.ajax({
            url: "{{ route('admin.projects.bulk') }}",
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
                        const badge = $(`#proj-row-${id} .proj-pub-badge`);
                        if (action === 'publish') {
                            badge.attr('class', 'badge bg-success proj-pub-badge').text('Published');
                        } else {
                            badge.attr('class', 'badge bg-secondary proj-pub-badge').text('Draft');
                        }
                    });
                    $('.proj-checkbox, #selectAllProjCheckboxes').prop('checked', false);
                    updateBulkProjectsBar();
                    showToast(res.message);
                }
            },
            error: function() {
                btn.prop('disabled', false).html(originalHtml);
                showToast('Action failed', 'error');
            }
        });
    });

    // Bulk Delete Projects
    $('#bulkDeleteProjectsBtn').on('click', function() {
        const selectedIds = $('.proj-checkbox:checked').map(function() { return $(this).val(); }).get();
        if (selectedIds.length === 0) return;

        Swal.fire({
            title: 'Delete Selected Projects?',
            text: `Are you sure you want to delete ${selectedIds.length} projects?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            confirmButtonText: 'Yes, Delete'
        }).then((res) => {
            if (res.isConfirmed) {
                const btn = $('#bulkDeleteProjectsBtn');
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Deleting...');

                $.ajax({
                    url: "{{ route('admin.projects.bulk') }}",
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
                                $(`#proj-row-${id}`).fadeOut(300, function() { $(this).remove(); });
                            });
                            $('.proj-checkbox, #selectAllProjCheckboxes').prop('checked', false);
                            updateBulkProjectsBar();
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
