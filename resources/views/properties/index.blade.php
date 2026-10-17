@extends('layouts.app')

@section('title', __('app.property_list'))

@section('content')
@php
    $statusOptions = [
        'Available' => __('admin.status_available'),
        'Reserved' => __('admin.status_reserved'),
        'Under Contract' => __('admin.status_under_contract'),
        'Sold' => __('admin.status_sold'),
        'Leased' => __('admin.status_leased'),
        'Maintenance' => __('admin.status_maintenance'),
        'Off Market' => __('admin.status_off_market'),
    ];
    $listingOptions = [
        'Sale' => __('admin.listing_sale'),
        'Rent' => __('admin.listing_rent'),
        'Lease' => __('admin.listing_lease'),
        'Joint Venture' => __('admin.listing_joint_venture'),
    ];
    $quickStatuses = ['Available', 'Reserved', 'Under Contract', 'Sold', 'Off Market'];
    $hasFilters = request()->hasAny(['search', 'type', 'listing_type', 'status', 'city']);
@endphp

<div class="page-header">
    <div class="min-w-0">
        <h1 class="brand-font">{{ __('app.property_list') }}</h1>
        <p class="text-muted small mb-0"><span id="totalPropertiesCount" class="fw-semibold">{{ $properties->total() }}</span> {{ __('admin.properties_registered') }}</p>
    </div>
    <div class="page-actions">
        <button class="btn btn-outline-secondary d-md-none" type="button" data-bs-toggle="collapse" data-bs-target="#propertyFilters" aria-expanded="false" aria-controls="propertyFilters">
            <i class="bi bi-funnel me-1"></i> {{ __('admin.filters') }}
        </button>
        <a href="{{ route('admin.properties.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> {{ __('app.add_property') }}
        </a>
    </div>
</div>

<div class="card mb-4">
    <div class="collapse d-md-block {{ $hasFilters ? 'show' : '' }}" id="propertyFilters">
        <form action="{{ route('admin.properties.index') }}" method="GET" class="filter-bar p-3" data-no-loading>
            <div>
                <label for="filterSearch" class="visually-hidden">{{ __('app.search') }}</label>
                <input type="search" name="search" id="filterSearch" class="form-control" placeholder="{{ __('admin.search_properties_placeholder') }}" value="{{ request('search') }}" autocomplete="off">
            </div>
            <div>
                <label for="filterType" class="visually-hidden">{{ __('app.property_type') }}</label>
                <select name="type" id="filterType" class="form-select">
                    <option value="">{{ __('admin.all_types') }}</option>
                    @foreach($types as $t)
                        <option value="{{ $t->id }}" @selected(request('type') == $t->id)>{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="filterListing" class="visually-hidden">{{ __('app.listing_type') }}</label>
                <select name="listing_type" id="filterListing" class="form-select">
                    <option value="">{{ __('admin.all_listings') }}</option>
                    @foreach($listingOptions as $value => $label)
                        <option value="{{ $value }}" @selected(request('listing_type') == $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="filterStatus" class="visually-hidden">{{ __('app.status') }}</label>
                <select name="status" id="filterStatus" class="form-select">
                    <option value="">{{ __('admin.all_statuses') }}</option>
                    @foreach(['Available', 'Reserved', 'Under Contract', 'Sold', 'Leased', 'Off Market'] as $value)
                        <option value="{{ $value }}" @selected(request('status') == $value)>{{ $statusOptions[$value] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="filterCity" class="visually-hidden">{{ __('admin.field_region') }}</label>
                <select name="city" id="filterCity" class="form-select">
                    <option value="">{{ __('admin.all_regions') }}</option>
                    @foreach(tz_regions() as $region)
                        <option value="{{ $region }}" @selected(request('city') == $region)>{{ $region }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-actions">
                <button type="submit" class="btn btn-dark"><i class="bi bi-funnel me-1"></i> {{ __('app.filter') }}</button>
                <a href="{{ route('admin.properties.index') }}" class="btn btn-light border">{{ __('admin.reset') }}</a>
            </div>
        </form>
    </div>
</div>

<!-- Floating bulk action bar -->
<div class="bulk-bar flex-column d-none" id="bulkActionsBar">
    <div class="bulk-inner">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-primary rounded-pill fs-6" id="bulkSelectedCount">0</span>
            <span class="small fw-semibold text-white-50">{{ __('admin.selected_properties') }}</span>
        </div>
        <div class="d-flex align-items-center flex-wrap gap-2">
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-light dropdown-toggle rounded-pill" type="button" id="bulkStatusBtn" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-tag me-1"></i> {{ __('admin.change_status') }}
                </button>
                <ul class="dropdown-menu shadow">
                    @foreach($quickStatuses as $value)
                        <li><a class="dropdown-item bulk-status-option {{ $value === 'Off Market' ? 'text-danger' : '' }}" href="#" data-status="{{ $value }}">{{ $statusOptions[$value] }}</a></li>
                    @endforeach
                </ul>
            </div>
            <button class="btn btn-sm btn-danger rounded-pill px-3" id="bulkDeleteBtn" type="button">
                <i class="bi bi-trash me-1"></i> {{ __('app.delete') }}
            </button>
            <button class="btn btn-sm btn-link text-white-50 text-decoration-none touch-target p-0" id="bulkCancelBtn" type="button" title="{{ __('admin.deselect_all') }}" aria-label="{{ __('admin.deselect_all') }}">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    </div>
</div>

<div class="card">
    @if($properties->isEmpty())
        <div class="empty-state">
            <div class="empty-icon"><i class="bi bi-house-add"></i></div>
            <h5>{{ $hasFilters ? __('admin.no_properties_match') : __('admin.no_properties_title') }}</h5>
            <p class="mb-3">{{ $hasFilters ? __('admin.try_clearing_filters') : __('admin.no_properties_text') }}</p>
            @if($hasFilters)
                <a href="{{ route('admin.properties.index') }}" class="btn btn-outline-primary me-2">{{ __('admin.reset') }}</a>
            @endif
            <a href="{{ route('admin.properties.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> {{ __('app.add_property') }}</a>
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover table-stack align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center">
                            <input type="checkbox" class="form-check-input" id="selectAllCheckboxes" aria-label="{{ __('admin.deselect_all') }}">
                        </th>
                        <th>{{ __('admin.property') }}</th>
                        <th>{{ __('admin.type') }}</th>
                        <th>{{ __('admin.listing') }}</th>
                        <th>{{ __('app.price') }}</th>
                        <th>{{ __('app.location') }}</th>
                        <th>{{ __('admin.size_units') }}</th>
                        <th>{{ __('app.status') }}</th>
                        <th class="text-end">{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($properties as $p)
                        @php
                            $isRental = in_array($p->listing_type, ['Rent', 'Lease'], true);
                            $shortPrice = $isRental ? format_money_short($p->rent_price ?? 0).' / '.strtolower($p->rent_period ?? 'Monthly') : format_money_short($p->price ?? 0);
                            $primaryMedia = $p->relationLoaded('media') ? ($p->media->firstWhere('is_primary', true) ?? $p->media->first()) : null;
                            $thumb = $primaryMedia?->mediaFile?->url;
                            $isLand = $p->landParcel || ($p->propertyType && stripos($p->propertyType->category ?? '', 'land') !== false);
                        @endphp
                        <tr id="property-row-{{ $p->id }}" data-id="{{ $p->id }}">
                            <td class="stack-check text-center">
                                <input type="checkbox" class="form-check-input row-checkbox" value="{{ $p->id }}" aria-label="{{ $p->title }}">
                            </td>
                            <td class="stack-primary">
                                <div class="d-flex align-items-center gap-2">
                                    @if($thumb)
                                        <img src="{{ $thumb }}" alt="{{ $p->title }}" loading="lazy" width="48" height="48" class="rounded-3 object-fit-cover border flex-shrink-0">
                                    @else
                                        <div class="rounded-3 bg-primary-subtle text-primary d-flex align-items-center justify-content-center flex-shrink-0 touch-target">
                                            <i class="bi {{ $isLand ? 'bi-map' : 'bi-building' }} fs-5"></i>
                                        </div>
                                    @endif
                                    <div class="min-w-0">
                                        <a href="{{ route('admin.properties.show', $p) }}" class="fw-bold text-dark text-decoration-none d-block line-clamp-2">{{ $p->title }}</a>
                                        <div class="text-muted small">{{ $p->property_code }} &bull; {{ $p->branch?->name ?? __('admin.headquarters') }}</div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge bg-light text-dark border">{{ $p->propertyType?->name ?? '-' }}</span></td>
                            <td><span class="badge bg-secondary-subtle text-secondary">{{ $listingOptions[$p->listing_type] ?? $p->listing_type }}</span></td>
                            <td class="fw-bold text-success" title="{{ $p->formatted_price }}">{{ $shortPrice }}</td>
                            <td><span class="d-block line-clamp-2">{{ $p->address }}, {{ $p->city }}</span></td>
                            <td>
                                @if($p->landParcel)
                                    <span class="badge bg-info-subtle text-info"><i class="bi bi-map me-1"></i>{{ __('admin.acres_count', ['count' => rtrim(rtrim(number_format((float) $p->landParcel->acreage, 2), '0'), '.')]) }}</span>
                                @elseif($p->units->count() > 0)
                                    <span class="badge bg-primary-subtle text-primary">{{ __('admin.units_count', ['count' => $p->units->count()]) }}</span>
                                @else
                                    <span class="text-muted">{{ $p->area_size ? rtrim(rtrim(number_format((float) $p->area_size, 2), '0'), '.').' '.$p->area_unit : '-' }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-link text-decoration-none p-0 dropdown-toggle status-badge-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <span class="badge badge-status-{{ strtolower(str_replace(' ', '', $p->status)) }} status-badge-label">{{ $statusOptions[$p->status] ?? $p->status }}</span>
                                    </button>
                                    <ul class="dropdown-menu shadow-sm small">
                                        @foreach($quickStatuses as $value)
                                            <li><a class="dropdown-item quick-status-item {{ $value === 'Off Market' ? 'text-danger' : '' }}" href="#" data-id="{{ $p->id }}" data-status="{{ $value }}">{{ $statusOptions[$value] }}</a></li>
                                        @endforeach
                                    </ul>
                                </div>
                            </td>
                            <td class="stack-actions text-end">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('admin.properties.show', $p) }}" class="btn btn-light border btn-icon" title="{{ __('admin.view') }}" aria-label="{{ __('admin.view') }}"><i class="bi bi-eye"></i></a>
                                    <a href="{{ route('admin.properties.edit', $p) }}" class="btn btn-light border btn-icon text-primary" title="{{ __('app.edit') }}" aria-label="{{ __('app.edit') }}"><i class="bi bi-pencil"></i></a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($properties->hasPages())
            <div class="p-3 border-top d-flex justify-content-center justify-content-md-end overflow-auto">
                {{ $properties->withQueryString()->links('pagination::bootstrap-5') }}
            </div>
        @endif
    @endif
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
                        btn.prop('disabled', false).html('<i class="bi bi-trash me-1"></i> {{ __('app.delete') }}');
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
                        btn.prop('disabled', false).html('<i class="bi bi-trash me-1"></i> {{ __('app.delete') }}');
                        showNotification('Error', 'Bulk delete failed.', 'error');
                    }
                });
            }
        });
    });
});
</script>
@endsection
