@extends('layouts.app')

@section('title', __('admin.projects_title'))

@section('content')
@php
    $projectStatuses = [
        'Selling' => __('admin.status_selling'),
        'Upcoming' => __('admin.status_upcoming'),
        'Pre-Launch' => __('admin.status_pre_launch'),
        'Completed' => __('admin.status_completed'),
        'Sold Out' => __('admin.status_sold_out'),
    ];
@endphp

<div class="page-header">
    <div class="min-w-0">
        <h1 class="brand-font">{{ __('admin.projects_title') }}</h1>
        <p class="text-muted small mb-0">{{ __('admin.projects_subtitle') }}</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.projects.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> {{ __('admin.add_project') }}
        </a>
    </div>
</div>

<div class="card p-2 p-md-3 mb-3">
    <div class="d-flex flex-wrap align-items-center gap-2 category-pills">
        @php $activeCat = $category ?? request('category'); @endphp
        <div class="btn-group btn-group-sm" role="group">
            <a href="{{ route('admin.projects.index', array_filter(['q' => request('q')])) }}" class="btn {{ ! in_array($activeCat, ['land', 'service']) ? 'btn-primary' : 'btn-outline-primary' }}">{{ __('admin.cat_all') }}</a>
            <a href="{{ route('admin.projects.index', array_filter(['category' => 'land', 'q' => request('q')])) }}" class="btn {{ $activeCat === 'land' ? 'btn-primary' : 'btn-outline-primary' }}">{{ __('admin.project_cat_land') }}</a>
            <a href="{{ route('admin.projects.index', array_filter(['category' => 'service', 'q' => request('q')])) }}" class="btn {{ $activeCat === 'service' ? 'btn-primary' : 'btn-outline-primary' }}">{{ __('admin.project_cat_service') }}</a>
        </div>
        <form action="{{ route('admin.projects.index') }}" method="GET" class="d-flex gap-2 flex-grow-1" style="max-width: 420px;">
            @if(in_array($activeCat, ['land', 'service']))<input type="hidden" name="category" value="{{ $activeCat }}">@endif
            <input type="search" name="q" class="form-control form-control-sm" value="{{ request('q') }}" placeholder="{{ __('admin.search_projects_placeholder') }}" aria-label="{{ __('app.search') }}">
            <button type="submit" class="btn btn-sm btn-dark flex-shrink-0"><i class="bi bi-search"></i></button>
        </form>
    </div>
</div>

<!-- Floating bulk action bar -->
<div class="bulk-bar flex-column d-none" id="bulkProjectsBar">
    <div class="bulk-inner">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-primary rounded-pill fs-6" id="bulkProjectsCount">0</span>
            <span class="small fw-semibold text-white-50">{{ __('admin.selected_projects') }}</span>
        </div>
        <div class="d-flex align-items-center flex-wrap gap-2">
            <button class="btn btn-sm btn-outline-success rounded-pill bulk-proj-action" data-action="publish" type="button">
                <i class="bi bi-eye me-1"></i> {{ __('admin.publish') }}
            </button>
            <button class="btn btn-sm btn-outline-warning rounded-pill bulk-proj-action" data-action="unpublish" type="button">
                <i class="bi bi-eye-slash me-1"></i> {{ __('admin.unpublish') }}
            </button>
            <button class="btn btn-sm btn-danger rounded-pill px-3" id="bulkDeleteProjectsBtn" type="button">
                <i class="bi bi-trash me-1"></i> {{ __('app.delete') }}
            </button>
            <button class="btn btn-sm btn-link text-white-50 text-decoration-none touch-target p-0" id="bulkCancelProjectsBtn" type="button" title="{{ __('admin.deselect_all') }}" aria-label="{{ __('admin.deselect_all') }}">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    </div>
</div>

<div class="card">
    @if($projects->isEmpty())
        <div class="empty-state">
            <div class="empty-icon"><i class="bi bi-map"></i></div>
            <h5>{{ __('admin.no_projects_title') }}</h5>
            <p class="mb-3">{{ __('admin.no_projects_text') }}</p>
            <a href="{{ route('admin.projects.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> {{ __('admin.add_project') }}</a>
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover table-stack align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center">
                            <input type="checkbox" class="form-check-input" id="selectAllProjCheckboxes" aria-label="{{ __('admin.deselect_all') }}">
                        </th>
                        <th>{{ __('admin.project') }}</th>
                        <th>{{ __('app.location') }}</th>
                        <th>{{ __('admin.plots') }}</th>
                        <th>{{ __('admin.starting_price') }}</th>
                        <th>{{ __('admin.gis') }}</th>
                        <th>{{ __('app.status') }}</th>
                        <th class="text-end">{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($projects as $proj)
                        @php
                            $total = max((int) $proj->total_units, 0);
                            $sold = max((int) $proj->sold_plots, 0);
                            $available = max((int) $proj->available_units, 0);
                            $soldPercent = $total > 0 ? min(100, (int) round($sold / $total * 100)) : 0;
                        @endphp
                        <tr id="proj-row-{{ $proj->id }}" data-id="{{ $proj->id }}">
                            <td class="stack-check text-center">
                                <input type="checkbox" class="form-check-input proj-checkbox" value="{{ $proj->id }}" aria-label="{{ $proj->title }}">
                            </td>
                            <td class="stack-primary">
                                <a href="{{ route('admin.projects.edit', $proj) }}" class="fw-bold text-dark text-decoration-none d-block line-clamp-2">{{ $proj->title }}</a>
                                <div class="text-muted small">{{ $proj->developer_name }} &bull; {{ $projectStatuses[$proj->project_status] ?? $proj->project_status }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $proj->city }}{{ $proj->region && $proj->region !== $proj->city ? ', '.$proj->region : '' }}</div>
                                <div class="text-muted small">{{ $proj->council ?? $proj->district ?? __('admin.tanzania') }}</div>
                            </td>
                            <td>
                                <div class="small mb-1">
                                    <span class="fw-bold text-danger">{{ $sold }}</span> {{ __('admin.plots_sold') }} &bull;
                                    <span class="fw-bold text-success">{{ $available }}</span> {{ __('admin.plots_available') }} &bull;
                                    <span class="fw-bold">{{ $total }}</span> {{ __('admin.plots_total') }}
                                </div>
                                <div class="progress" role="progressbar" aria-label="{{ __('app.plot_allocation_progress') }}" aria-valuenow="{{ $soldPercent }}" aria-valuemin="0" aria-valuemax="100" style="height: 6px;">
                                    <div class="progress-bar bg-danger" style="width: {{ $soldPercent }}%"></div>
                                </div>
                            </td>
                            <td class="fw-bold text-primary" title="{{ format_currency($proj->starting_price) }}">{{ format_money_short($proj->starting_price) }}</td>
                            <td>
                                @if($proj->geojson_data || $proj->shapefile_path)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle"><i class="bi bi-geo-alt-fill me-1"></i>{{ __('admin.shapefile_loaded') }}</span>
                                @else
                                    <span class="badge bg-light text-muted border">{{ __('admin.no_shapefile') }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ ($proj->project_category ?? 'land') === 'service' ? 'bg-info-subtle text-info' : 'bg-primary-subtle text-primary' }} me-1">{{ ($proj->project_category ?? 'land') === 'service' ? __('admin.project_cat_service') : __('admin.project_cat_land') }}</span>
                                <span class="badge {{ $proj->is_published ? 'bg-success' : 'bg-secondary' }} proj-pub-badge">
                                    {{ $proj->is_published ? __('admin.published') : __('admin.draft') }}
                                </span>
                            </td>
                            <td class="stack-actions text-end">
                                <div class="d-inline-flex gap-1">
                                    <button type="button" class="btn btn-light border btn-icon project-preview-btn" title="{{ __('app.view') }}" aria-label="{{ __('app.view') }}"
                                        data-title="{{ $proj->title }}"
                                        data-type="{{ $proj->project_type }}"
                                        data-status="{{ $proj->project_status }}"
                                        data-location="{{ $proj->location_name }}, {{ $proj->city }}"
                                        data-price="{{ format_currency($proj->starting_price, null, 0) }}"
                                        data-plots="{{ $proj->available_units }}/{{ $proj->total_units }}"
                                        data-published="{{ $proj->is_published ? 1 : 0 }}"
                                        data-image="{{ $proj->hero_image_url }}"
                                        data-public-url="{{ route('public.projects.show', $proj->slug) }}"
                                        data-edit-url="{{ route('admin.projects.edit', $proj) }}"
                                        data-description="{{ Str::limit(strip_tags((string) $proj->description), 220) }}">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <a href="{{ route('admin.projects.edit', $proj) }}" class="btn btn-light border btn-icon" title="{{ __('app.edit') }}" aria-label="{{ __('app.edit') }}"><i class="bi bi-pencil text-primary"></i></a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($projects->hasPages())
            <div class="p-3 border-top d-flex justify-content-center justify-content-md-end overflow-auto">
                {{ $projects->links('pagination::bootstrap-5') }}
            </div>
        @endif
    @endif
</div>

{{-- Quick preview modal (filled from the clicked row's data attributes) --}}
<div class="modal fade" id="projectPreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title brand-font" id="ppvTitle"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('app.cancel') }}"></button>
            </div>
            <div class="modal-body p-0">
                <div class="ratio ratio-21x9 bg-dark"><img id="ppvImage" src="" alt="" class="object-fit-cover"></div>
                <div class="p-3 p-md-4">
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <span class="badge bg-primary-subtle text-primary" id="ppvType"></span>
                        <span class="badge bg-light text-dark border" id="ppvStatus"></span>
                        <span class="badge" id="ppvPublished"></span>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6 col-md-4"><div class="small text-muted">{{ __('admin.field_location_name') }}</div><div class="fw-semibold" id="ppvLocation"></div></div>
                        <div class="col-6 col-md-4"><div class="small text-muted">{{ __('admin.starting_price') }}</div><div class="fw-semibold" id="ppvPrice"></div></div>
                        <div class="col-6 col-md-4"><div class="small text-muted">{{ __('admin.field_available_plots') }}</div><div class="fw-semibold" id="ppvPlots"></div></div>
                    </div>
                    <p class="text-muted small mb-0" id="ppvDescription"></p>
                </div>
            </div>
            <div class="modal-footer flex-wrap">
                <a href="#" class="btn btn-light border" id="ppvEdit"><i class="bi bi-pencil me-1"></i>{{ __('app.edit') }}</a>
                <a href="#" class="btn btn-primary" id="ppvOpen"><i class="bi bi-globe2 me-1"></i>{{ __('admin.open_public_page') }}</a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    const publishedLabel = @json(__('admin.published'));
    const draftLabel = @json(__('admin.draft'));

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
                            badge.attr('class', 'badge bg-success proj-pub-badge').text(publishedLabel);
                        } else {
                            badge.attr('class', 'badge bg-secondary proj-pub-badge').text(draftLabel);
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
                        btn.prop('disabled', false).html('<i class="bi bi-trash me-1"></i> {{ __('app.delete') }}');
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

    // Project quick preview modal
    document.querySelectorAll('.project-preview-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var d = btn.dataset;
            document.getElementById('ppvTitle').textContent = d.title;
            document.getElementById('ppvType').textContent = d.type;
            document.getElementById('ppvStatus').textContent = d.status;
            var pub = document.getElementById('ppvPublished');
            pub.textContent = d.published === '1' ? @json(__('admin.published')) : @json(__('admin.draft'));
            pub.className = 'badge ' + (d.published === '1' ? 'bg-success' : 'bg-warning text-dark');
            document.getElementById('ppvLocation').textContent = d.location;
            document.getElementById('ppvPrice').textContent = d.price;
            document.getElementById('ppvPlots').textContent = d.plots;
            document.getElementById('ppvDescription').textContent = d.description;
            var img = document.getElementById('ppvImage');
            img.src = d.image; img.alt = d.title;
            document.getElementById('ppvEdit').href = d.editUrl;
            document.getElementById('ppvOpen').href = d.publicUrl;
            new bootstrap.Modal(document.getElementById('projectPreviewModal')).show();
        });
    });
});
</script>
@endsection
