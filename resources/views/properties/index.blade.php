@extends('layouts.app')

@section('title', __('app.property_list'))

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h3 class="brand-font mb-1">{{ __('app.property_list') }}</h3>
        <p class="text-muted small mb-0">Total <span id="totalPropertiesCount">{{ $properties->total() }}</span> real estate assets registered</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.properties.create') }}" class="btn btn-primary btn-sm d-flex align-items-center gap-1 shadow-sm">
            <i class="bi bi-plus-lg"></i> {{ __('app.add_property') }}
        </a>
    </div>
</div>

<!-- Filters Card -->
<div class="card mb-4 p-3 bg-white">
    <form action="{{ route('admin.properties.index') }}" method="GET" class="row g-2 align-items-center">
        <div class="col-md-3">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search title, code, address..." value="{{ request('search') }}">
        </div>
        <div class="col-md-2">
            <select name="type" class="form-select form-select-sm">
                <option value="">All Types</option>
                @foreach($types as $t)
                    <option value="{{ $t->id }}" {{ request('type') == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select name="listing_type" class="form-select form-select-sm">
                <option value="">All Listings</option>
                <option value="Sale" {{ request('listing_type') == 'Sale' ? 'selected' : '' }}>Sale</option>
                <option value="Rent" {{ request('listing_type') == 'Rent' ? 'selected' : '' }}>Rent</option>
                <option value="Lease" {{ request('listing_type') == 'Lease' ? 'selected' : '' }}>Lease</option>
            </select>
        </div>
        <div class="col-md-2">
            <select name="status" class="form-select form-select-sm">
                <option value="">All Statuses</option>
                <option value="Available" {{ request('status') == 'Available' ? 'selected' : '' }}>Available</option>
                <option value="Reserved" {{ request('status') == 'Reserved' ? 'selected' : '' }}>Reserved</option>
                <option value="Under Contract" {{ request('status') == 'Under Contract' ? 'selected' : '' }}>Under Contract</option>
                <option value="Sold" {{ request('status') == 'Sold' ? 'selected' : '' }}>Sold</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-dark btn-sm w-100"><i class="bi bi-funnel me-1"></i> Filter</button>
        </div>
        <div class="col-md-1">
            <a href="{{ route('admin.properties.index') }}" class="btn btn-light border btn-sm w-100">Reset</a>
        </div>
    </form>
</div>

<!-- Floating Bulk Action Floating Drawer -->
<div class="card border-0 shadow-lg position-fixed bottom-0 start-50 translate-middle-x mb-4 px-3 py-2 bg-dark text-white rounded-pill z-3 align-items-center d-none" id="bulkActionsBar" style="z-index: 1050; min-width: 480px;">
    <div class="d-flex align-items-center justify-content-between w-100 gap-3">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-primary rounded-pill fs-6" id="bulkSelectedCount">0</span>
            <span class="small fw-semibold text-white-50">Selected Properties</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <!-- Bulk Status Dropdown -->
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-light dropdown-toggle rounded-pill" type="button" id="bulkStatusBtn" data-bs-toggle="dropdown">
                    <i class="bi bi-tag me-1"></i> Change Status
                </button>
                <ul class="dropdown-menu shadow">
                    <li><a class="dropdown-item bulk-status-option" href="#" data-status="Available">Available</a></li>
                    <li><a class="dropdown-item bulk-status-option" href="#" data-status="Reserved">Reserved</a></li>
                    <li><a class="dropdown-item bulk-status-option" href="#" data-status="Under Contract">Under Contract</a></li>
                    <li><a class="dropdown-item bulk-status-option" href="#" data-status="Sold">Sold</a></li>
                    <li><a class="dropdown-item bulk-status-option text-danger" href="#" data-status="Off Market">Off Market</a></li>
                </ul>
            </div>

            <!-- Bulk Delete -->
            <button class="btn btn-sm btn-danger rounded-pill px-3" id="bulkDeleteBtn">
                <i class="bi bi-trash me-1"></i> Delete
            </button>

            <!-- Deselect All -->
            <button class="btn btn-sm btn-link text-white-50 p-0 text-decoration-none ms-2" id="bulkCancelBtn" title="Deselect All">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    </div>
</div>

<!-- Properties Table Card -->
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: 0.875rem;">
            <thead class="table-light">
                <tr>
                    <th style="width: 40px;" class="text-center">
                        <input type="checkbox" class="form-check-input" id="selectAllCheckboxes">
                    </th>
                    <th style="width: 300px;">Property</th>
                    <th>Type</th>
                    <th>Listing</th>
                    <th>Price / Rate</th>
                    <th>Location</th>
                    <th>Units/Acreage</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($properties as $p)
                    <tr id="property-row-{{ $p->id }}" data-id="{{ $p->id }}">
                        <td class="text-center">
                            <input type="checkbox" class="form-check-input row-checkbox" value="{{ $p->id }}">
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-3 bg-light border d-flex align-items-center justify-content-center text-secondary flex-shrink-0" style="width: 44px; height: 44px;">
                                    <i class="bi bi-building fs-5 text-primary"></i>
                                </div>
                                <div class="min-w-0">
                                    <a href="{{ route('admin.properties.show', $p) }}" class="fw-bold text-dark text-decoration-none text-truncate d-block" style="max-width: 220px;">
                                        {{ $p->title }}
                                    </a>
                                    <div class="text-muted small">{{ $p->property_code }} &bull; {{ $p->branch?->name ?? 'Headquarters' }}</div>
                                </div>
                            </div>
                        </td>
                        <td><span class="badge bg-light text-dark border">{{ $p->propertyType?->name ?? 'Asset' }}</span></td>
                        <td><span class="badge bg-secondary-subtle text-secondary">{{ $p->listing_type }}</span></td>
                        <td class="fw-bold text-success">{{ $p->formatted_price }}</td>
                        <td>{{ $p->city }}, {{ $p->address }}</td>
                        <td>
                            @if($p->landParcel)
                                <span class="badge bg-info-subtle text-info"><i class="bi bi-map me-1"></i>{{ $p->landParcel->acreage }} Acres</span>
                            @elseif($p->units->count() > 0)
                                <span class="badge bg-primary-subtle text-primary">{{ $p->units->count() }} Units</span>
                            @else
                                <span class="text-muted">{{ $p->area_size ? "{$p->area_size} {$p->area_unit}" : '-' }}</span>
                            @endif
                        </td>
                        <td>
                            <!-- Dynamic Quick Status Dropdown -->
                            <div class="dropdown">
                                <button class="btn btn-sm btn-link text-decoration-none p-0 dropdown-toggle status-badge-btn" type="button" data-bs-toggle="dropdown">
                                    <span class="badge badge-status-{{ strtolower(str_replace(' ', '', $p->status)) }} status-badge-label">{{ $p->status }}</span>
                                </button>
                                <ul class="dropdown-menu shadow-sm small">
                                    <li><a class="dropdown-item quick-status-item" href="#" data-id="{{ $p->id }}" data-status="Available">Available</a></li>
                                    <li><a class="dropdown-item quick-status-item" href="#" data-id="{{ $p->id }}" data-status="Reserved">Reserved</a></li>
                                    <li><a class="dropdown-item quick-status-item" href="#" data-id="{{ $p->id }}" data-status="Under Contract">Under Contract</a></li>
                                    <li><a class="dropdown-item quick-status-item" href="#" data-id="{{ $p->id }}" data-status="Sold">Sold</a></li>
                                    <li><a class="dropdown-item quick-status-item text-danger" href="#" data-id="{{ $p->id }}" data-status="Off Market">Off Market</a></li>
                                </ul>
                            </div>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.properties.show', $p) }}" class="btn btn-light border" title="View"><i class="bi bi-eye"></i></a>
                                <a href="{{ route('admin.properties.edit', $p) }}" class="btn btn-light border" title="Edit"><i class="bi bi-pencil"></i></a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">No properties matched your search query.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3 border-top d-flex justify-content-end">
        {{ $properties->links() }}
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    const csrfToken = $('meta[name="csrf-token"]').attr('content');

    function updateBulkBar() {
        const checked = $('.row-checkbox:checked');
        const count = checked.length;
        $('#bulkSelectedCount').text(count);

        if (count > 0) {
            $('#bulkActionsBar').removeClass('d-none').addClass('d-flex');
        } else {
            $('#bulkActionsBar').addClass('d-none').removeClass('d-flex');
            $('#selectAllCheckboxes').prop('checked', false);
        }
    }

    // Select All
    $('#selectAllCheckboxes').on('change', function() {
        $('.row-checkbox').prop('checked', $(this).is(':checked'));
        updateBulkBar();
    });

    // Individual Checkbox
    $(document).on('change', '.row-checkbox', function() {
        updateBulkBar();
    });

    // Deselect All
    $('#bulkCancelBtn').on('click', function() {
        $('.row-checkbox, #selectAllCheckboxes').prop('checked', false);
        updateBulkBar();
    });

    // Show Toast Notification
    function showNotification(title, message, icon = 'success') {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: icon,
            title: message || title,
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
    }

    // Quick Single Status Update (AJAX Without Page Reload)
    $(document).on('click', '.quick-status-item', function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        const newStatus = $(this).data('status');
        const row = $(`#property-row-${id}`);
        const badgeLabel = row.find('.status-badge-label');

        // Visual loading indicator on badge
        badgeLabel.html('<span class="spinner-border spinner-border-sm" style="width: 0.75rem; height: 0.75rem;"></span>');

        $.ajax({
            url: `/admin/properties/${id}/quick-status`,
            type: 'POST',
            data: {
                _token: csrfToken,
                status: newStatus
            },
            success: function(res) {
                if (res.success) {
                    badgeLabel.attr('class', `badge badge-status-${newStatus.toLowerCase().replace(/\s+/g, '')} status-badge-label`).text(newStatus);
                    showNotification('Updated', res.message, 'success');
                }
            },
            error: function() {
                badgeLabel.text('Error');
                showNotification('Error', 'Failed to update property status.', 'error');
            }
        });
    });

    // Bulk Status Action
    $('.bulk-status-option').on('click', function(e) {
        e.preventDefault();
        const status = $(this).data('status');
        const selectedIds = $('.row-checkbox:checked').map(function() { return $(this).val(); }).get();

        if (selectedIds.length === 0) return;

        const btn = $('#bulkStatusBtn');
        const originalHtml = btn.html();
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Updating...');

        $.ajax({
            url: "{{ route('admin.properties.bulk') }}",
            type: 'POST',
            data: {
                _token: csrfToken,
                action: 'change_status',
                status: status,
                ids: selectedIds
            },
            success: function(res) {
                btn.prop('disabled', false).html(originalHtml);
                if (res.success) {
                    selectedIds.forEach(id => {
                        $(`#property-row-${id} .status-badge-label`)
                            .attr('class', `badge badge-status-${status.toLowerCase().replace(/\s+/g, '')} status-badge-label`)
                            .text(status);
                    });
                    $('.row-checkbox, #selectAllCheckboxes').prop('checked', false);
                    updateBulkBar();
                    showNotification('Bulk Action', res.message, 'success');
                }
            },
            error: function() {
                btn.prop('disabled', false).html(originalHtml);
                showNotification('Error', 'Bulk status update failed.', 'error');
            }
        });
    });

    // Bulk Delete Action
    $('#bulkDeleteBtn').on('click', function() {
        const selectedIds = $('.row-checkbox:checked').map(function() { return $(this).val(); }).get();
        if (selectedIds.length === 0) return;

        Swal.fire({
            title: 'Delete Selected Properties?',
            text: `Are you sure you want to delete ${selectedIds.length} selected property listings?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, Delete All'
        }).then((result) => {
            if (result.isConfirmed) {
                const btn = $('#bulkDeleteBtn');
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Deleting...');

                $.ajax({
                    url: "{{ route('admin.properties.bulk') }}",
                    type: 'POST',
                    data: {
                        _token: csrfToken,
                        action: 'delete',
                        ids: selectedIds
                    },
                    success: function(res) {
                        btn.prop('disabled', false).html('<i class="bi bi-trash me-1"></i> Delete');
                        if (res.success) {
                            selectedIds.forEach(id => {
                                $(`#property-row-${id}`).fadeOut(300, function() { $(this).remove(); });
                            });
                            $('.row-checkbox, #selectAllCheckboxes').prop('checked', false);
                            updateBulkBar();
                            showNotification('Deleted', res.message, 'success');
                        }
                    },
                    error: function() {
                        btn.prop('disabled', false).html('<i class="bi bi-trash me-1"></i> Delete');
                        showNotification('Error', 'Bulk delete failed.', 'error');
                    }
                });
            }
        });
    });
});
</script>
@endsection

