@extends('layouts.app')

@section('title', $project->title)

@section('content')
@php
    $isService = $project->isServiceProject();
    $canManage = auth()->user()->can('manage_projects');
    $statusTone = ['Available' => 'success', 'Reserved' => 'warning', 'Sold' => 'danger'];
    $soldPercent = $stats['total'] > 0 ? (int) round($stats['sold'] / $stats['total'] * 100) : 0;
@endphp

<div class="page-header">
    <div class="min-w-0">
        <a href="{{ route('admin.projects.index') }}" class="small text-muted text-decoration-none"><i class="bi bi-arrow-left me-1"></i>{{ __('admin.projects_title') }}</a>
        <h1 class="brand-font mb-1 text-truncate">{{ $project->title }}</h1>
        <div class="d-flex flex-wrap align-items-center gap-1 small">
            <span class="badge {{ $isService ? 'bg-info-subtle text-info' : 'bg-primary-subtle text-primary' }}">{{ $isService ? __('admin.project_cat_service') : __('admin.project_cat_land') }}</span>
            <span class="badge bg-light text-dark border">{{ $project->status_label }}</span>
            <span class="badge {{ $project->is_published ? 'bg-success' : 'bg-secondary' }}">{{ $project->is_published ? __('admin.published') : __('admin.draft') }}</span>
            @if($project->is_pinned)<span class="badge bg-warning text-dark"><i class="bi bi-pin-angle-fill me-1"></i>{{ __('admin.pinned') }}</span>@endif
            <span class="text-muted ms-1"><i class="bi bi-geo-alt me-1"></i>{{ $project->location_label }}</span>
        </div>
    </div>
    <div class="page-actions">
        <a href="{{ route('public.projects.show', $project->slug) }}" target="_blank" rel="noopener" class="btn btn-light border"><i class="bi bi-box-arrow-up-right me-1"></i>{{ __('admin.view_on_website') }}</a>
        @if($canManage)
            <a href="{{ route('admin.projects.edit', $project) }}" class="btn btn-primary"><i class="bi bi-pencil me-1"></i>{{ __('app.edit') }}</a>
            <div class="dropdown">
                <button class="btn btn-light border dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">{{ __('admin.more_actions') }}</button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                    <li>
                        <form action="{{ route('admin.projects.publish', $project) }}" method="POST" class="m-0">@csrf
                            <button class="dropdown-item"><i class="bi {{ $project->is_published ? 'bi-eye-slash' : 'bi-eye' }} me-2"></i>{{ $project->is_published ? __('admin.unpublish') : __('admin.publish') }}</button>
                        </form>
                    </li>
                    <li>
                        <form action="{{ route('admin.projects.pin', $project) }}" method="POST" class="m-0">@csrf
                            <button class="dropdown-item"><i class="bi bi-pin-angle me-2"></i>{{ $project->is_pinned ? __('admin.unpin_project') : __('admin.pin_project') }}</button>
                        </form>
                    </li>
                    <li>
                        <form action="{{ route('admin.projects.duplicate', $project) }}" method="POST" class="m-0">@csrf
                            <button class="dropdown-item"><i class="bi bi-copy me-2"></i>{{ __('admin.duplicate_project') }}</button>
                        </form>
                    </li>
                    @can('delete_projects')
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="{{ route('admin.projects.destroy', $project) }}" method="POST" class="m-0" onsubmit="return confirm(@json(__('admin.confirm_delete')));">@csrf @method('DELETE')
                                <button class="dropdown-item text-danger"><i class="bi bi-trash me-2"></i>{{ __('app.delete') }}</button>
                            </form>
                        </li>
                    @endcan
                </ul>
            </div>
        @endif
    </div>
</div>

@unless($isService)
<!-- KPIs -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl-2">
        <div class="kpi-card"><div class="kpi-label">{{ __('admin.plots_total') }}</div><div class="kpi-value" data-count="total">{{ $stats['total'] }}</div><span class="kpi-icon"><i class="bi bi-grid-3x3"></i></span></div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="kpi-card kpi-success"><div class="kpi-label">{{ __('admin.plot_status_available') }}</div><div class="kpi-value text-success" data-count="Available">{{ $stats['available'] }}</div></div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="kpi-card kpi-warning"><div class="kpi-label">{{ __('admin.plot_status_reserved') }}</div><div class="kpi-value text-warning-emphasis" data-count="Reserved">{{ $stats['reserved'] }}</div></div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="kpi-card kpi-danger"><div class="kpi-label">{{ __('admin.plot_status_sold') }}</div><div class="kpi-value text-danger" data-count="Sold">{{ $stats['sold'] }}</div></div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="kpi-card kpi-info"><div class="kpi-label">{{ __('admin.value_sold') }}</div><div class="kpi-value" title="{{ format_currency($stats['value_sold'], null, 0) }}">{{ format_money_short($stats['value_sold']) }}</div><small class="text-muted">{{ $soldPercent }}% {{ __('admin.of_plots') }}</small></div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="kpi-card"><div class="kpi-label">{{ __('admin.value_for_sale') }}</div><div class="kpi-value" title="{{ format_currency($stats['value_available'], null, 0) }}">{{ format_money_short($stats['value_available']) }}</div></div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Plot map -->
    <div class="col-12 col-lg-8">
        <div class="card h-100 overflow-hidden">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <span class="brand-font">{{ __('admin.plot_map') }}</span>
                <span class="small d-flex gap-2">
                    <span><span class="d-inline-block rounded-1 bg-success" style="width:.75rem;height:.75rem;"></span> {{ __('admin.plot_status_available') }}</span>
                    <span><span class="d-inline-block rounded-1 bg-warning" style="width:.75rem;height:.75rem;"></span> {{ __('admin.plot_status_reserved') }}</span>
                    <span><span class="d-inline-block rounded-1 bg-danger" style="width:.75rem;height:.75rem;"></span> {{ __('admin.plot_status_sold') }}</span>
                </span>
            </div>
            @if(count($plotGeojson['features']))
                <div id="adminPlotMap" class="map-h-sm"></div>
                <div class="card-footer small text-muted">{{ $canManage ? __('admin.plot_map_click_hint') : __('admin.plot_map_hint') }}</div>
            @else
                <div class="empty-state">
                    <div class="empty-icon"><i class="bi bi-map"></i></div>
                    <h6>{{ __('admin.no_plot_map') }}</h6>
                    <p class="small mb-3">{{ __('admin.no_plot_map_hint') }}</p>
                    @if($canManage)<a href="{{ route('admin.projects.edit', $project) }}" class="btn btn-sm btn-primary"><i class="bi bi-upload me-1"></i>{{ __('admin.upload_shapefile') }}</a>@endif
                </div>
            @endif
        </div>
    </div>

    <!-- Pricing & terms -->
    <div class="col-12 col-lg-4">
        <div class="card h-100 p-3">
            <h2 class="h6 brand-font mb-3">{{ __('admin.pricing_and_terms') }}</h2>
            <dl class="row small mb-0">
                <dt class="col-6 text-muted fw-semibold">{{ __('admin.starting_price') }}</dt>
                <dd class="col-6 text-end fw-bold text-primary">{{ $project->starting_price > 0 ? format_currency($project->starting_price, null, 0) : '—' }}</dd>
                <dt class="col-6 text-muted fw-semibold">{{ __('admin.field_price_per_sqm') }}</dt>
                <dd class="col-6 text-end">{{ (float) $project->price_per_sqm > 0 ? format_currency($project->price_per_sqm, null, 0).' / m²' : '—' }}</dd>
                <dt class="col-6 text-muted fw-semibold">{{ __('admin.field_initial_payment') }}</dt>
                <dd class="col-6 text-end">{{ (int) $project->initial_payment_percent > 0 ? (int) $project->initial_payment_percent.'%' : '—' }}</dd>
                <dt class="col-6 text-muted fw-semibold">{{ __('admin.field_application_fee') }}</dt>
                <dd class="col-6 text-end">{{ (float) $project->application_fee > 0 ? format_currency($project->application_fee, null, 0) : '—' }}</dd>
                <dt class="col-6 text-muted fw-semibold">{{ __('admin.total_area') }}</dt>
                <dd class="col-6 text-end">{{ number_format($stats['area_total']) }} m²</dd>
                <dt class="col-6 text-muted fw-semibold">{{ __('app.location') }}</dt>
                <dd class="col-6 text-end">{{ collect([$project->council ?: $project->city, $project->district, $project->region])->filter()->unique()->implode(', ') }}</dd>
            </dl>
            <div class="mt-auto pt-3">
                <div class="d-flex justify-content-between small mb-1"><span>{{ __('admin.sales_progress') }}</span><span class="fw-bold">{{ $soldPercent }}%</span></div>
                <div class="progress progress-thin rounded-pill"><div class="progress-bar bg-danger" style="width: {{ $soldPercent }}%"></div></div>
            </div>
        </div>
    </div>
</div>

<!-- Plot inventory -->
<div class="card mb-4" id="plots">
    <div class="card-header">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
            <h2 class="h6 brand-font mb-0">{{ __('admin.plot_inventory') }} <span class="badge bg-light text-dark border">{{ $stats['total'] }}</span></h2>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('admin.projects.plots.export', $project) }}" class="btn btn-sm btn-light border"><i class="bi bi-filetype-csv me-1"></i>CSV</a>
                @if($canManage)
                    <button type="button" class="btn btn-sm btn-primary" id="addPlotBtn"><i class="bi bi-plus-lg me-1"></i>{{ __('admin.add_plot') }}</button>
                @endif
            </div>
        </div>
        <div class="filter-bar">
            <div class="flex-grow-1">
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="search" id="plotSearch" class="form-control" placeholder="{{ __('admin.search_plot_number') }}" aria-label="{{ __('admin.search_plot_number') }}">
                </div>
            </div>
            @if($blocks->count() > 1)
                <div>
                    <select id="plotBlockFilter" class="form-select form-select-sm" aria-label="{{ __('admin.block') }}">
                        <option value="">{{ __('admin.all_blocks') }}</option>
                        @foreach($blocks as $block)
                            <option value="{{ $block }}">{{ $block }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div class="btn-group btn-group-sm" role="group" id="plotStatusFilter">
                <button type="button" class="btn btn-primary" data-status="">{{ __('admin.cat_all') }}</button>
                @foreach(['Available', 'Reserved', 'Sold'] as $st)
                    <button type="button" class="btn btn-outline-{{ $statusTone[$st] }}" data-status="{{ $st }}">{{ __('admin.plot_status_'.strtolower($st)) }}</button>
                @endforeach
            </div>
        </div>
    </div>
    @if($parcels->isEmpty())
        <div class="empty-state">
            <div class="empty-icon"><i class="bi bi-grid-3x3"></i></div>
            <h6>{{ __('admin.no_plots') }}</h6>
            <p class="small mb-0">{{ __('admin.no_plots_hint') }}</p>
        </div>
    @else
        <div class="table-responsive" style="max-height: 65vh;">
            <table class="table table-hover table-stack align-middle mb-0 small">
                <thead class="table-light" style="position: sticky; top: 0; z-index: 1;">
                    <tr>
                        @if($canManage)<th class="stack-check" style="width:2.5rem;"><input type="checkbox" class="form-check-input" id="plotSelectAll" aria-label="{{ __('admin.select_all') }}"></th>@endif
                        <th>{{ __('admin.plot') }}</th>
                        <th>{{ __('admin.block') }}</th>
                        <th class="text-end">{{ __('admin.area') }}</th>
                        <th class="text-end">{{ __('app.price') }}</th>
                        <th>{{ __('app.status') }}</th>
                        <th>{{ __('admin.land_use') }}</th>
                        @if($canManage)<th class="text-end">{{ __('app.actions') }}</th>@endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($parcels as $parcel)
                        <tr class="plot-row" data-uuid="{{ $parcel->uuid }}" data-status="{{ $parcel->status }}" data-block="{{ $parcel->block_number }}" data-search="{{ strtolower($parcel->parcel_number.' '.$parcel->block_number) }}">
                            @if($canManage)<td class="stack-check"><input type="checkbox" class="form-check-input plot-check" value="{{ $parcel->id }}" aria-label="{{ $parcel->parcel_number }}"></td>@endif
                            <td class="stack-primary fw-bold">{{ __('pages.plot') }} {{ $parcel->parcel_number }}</td>
                            <td>{{ $parcel->block_number ?: '—' }}</td>
                            <td class="text-end">{{ $parcel->area_sqm ? number_format((float) $parcel->area_sqm).' m²' : '—' }}</td>
                            <td class="text-end fw-semibold" title="{{ format_currency($parcel->price, null, 0) }}">{{ (float) $parcel->price > 0 ? format_money_short($parcel->price) : '—' }}</td>
                            <td>
                                @if($canManage)
                                    <select class="form-select form-select-sm w-auto plot-status-select border-{{ $statusTone[$parcel->status] ?? 'secondary' }}" data-url="{{ route('admin.projects.plots.status', [$project, $parcel]) }}" aria-label="{{ __('app.status') }}">
                                        @foreach(['Available', 'Reserved', 'Sold'] as $st)
                                            <option value="{{ $st }}" @selected($parcel->status === $st)>{{ __('admin.plot_status_'.strtolower($st)) }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <span class="badge bg-{{ $statusTone[$parcel->status] ?? 'secondary' }}">{{ __('admin.plot_status_'.strtolower($parcel->status ?? 'available')) }}</span>
                                @endif
                            </td>
                            <td class="text-muted">{{ $parcel->land_use ?: '—' }}</td>
                            @if($canManage)
                                <td class="stack-actions text-end">
                                    <div class="d-inline-flex gap-1">
                                        <button type="button" class="btn btn-light border btn-sm btn-icon plot-edit-btn" title="{{ __('app.edit') }}" aria-label="{{ __('app.edit') }}"
                                            data-url="{{ route('admin.projects.plots.update', [$project, $parcel]) }}"
                                            data-parcel="{{ json_encode(['parcel_number' => $parcel->parcel_number, 'block_number' => $parcel->block_number, 'area_sqm' => $parcel->area_sqm, 'price' => (float) $parcel->price, 'status' => $parcel->status, 'land_use' => $parcel->land_use, 'title_deed_type' => $parcel->title_deed_type]) }}">
                                            <i class="bi bi-pencil text-primary"></i>
                                        </button>
                                        <form action="{{ route('admin.projects.plots.destroy', [$project, $parcel]) }}" method="POST" class="m-0" onsubmit="return confirm(@json(__('admin.plot_delete_confirm')));">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-light border btn-sm btn-icon" title="{{ __('app.delete') }}" aria-label="{{ __('app.delete') }}"><i class="bi bi-trash text-danger"></i></button>
                                        </form>
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                    <tr id="plotNoMatch" class="d-none"><td colspan="8" class="text-center text-muted py-4">{{ __('admin.no_plots_match') }}</td></tr>
                </tbody>
            </table>
        </div>
    @endif
</div>
@else
<!-- Service project -->
<div class="row g-4 mb-4">
    <div class="col-12 col-lg-8">
        <div class="card p-3 h-100">
            <h2 class="h6 brand-font mb-3">{{ __('admin.gallery') }}</h2>
            @if(count($project->gallery))
                <div class="row g-2">
                    @foreach($project->gallery as $image)
                        <div class="col-6 col-md-4"><div class="ratio ratio-4x3 rounded-3 overflow-hidden border"><img src="{{ $image }}" alt="{{ $project->title }}" class="object-fit-cover" loading="lazy"></div></div>
                    @endforeach
                </div>
            @else
                <p class="text-muted small mb-0">{{ __('admin.no_gallery') }}</p>
            @endif
            @if($project->description)
                <p class="text-muted small mt-3 mb-0" style="white-space: pre-line;">{{ $project->description }}</p>
            @endif
        </div>
    </div>
    <div class="col-12 col-lg-4">
        <div class="card p-3 h-100">
            <dl class="row small mb-0">
                <dt class="col-5 text-muted">{{ __('pages.service_label') }}</dt><dd class="col-7">{{ $project->service?->title ?? '—' }}</dd>
                <dt class="col-5 text-muted">{{ __('pages.client_label') }}</dt><dd class="col-7">{{ $project->client_name ?: '—' }}</dd>
                <dt class="col-5 text-muted">{{ __('app.location') }}</dt><dd class="col-7">{{ $project->location_label ?: '—' }}</dd>
            </dl>
        </div>
    </div>
</div>
@endunless

<!-- Requests & reservations for this project -->
<div class="row g-4">
    <div class="col-12 {{ $isService ? '' : 'col-lg-6' }}">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="brand-font">{{ __('admin.project_requests') }}</span>
                @can('view_requests')<a href="{{ route('crm.requests') }}" class="small">{{ __('app.view_all') }}</a>@endcan
            </div>
            <div class="list-group list-group-flush small">
                @forelse($requests as $req)
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between gap-2">
                            <strong class="text-truncate">{{ $req->name }}</strong>
                            <span class="text-muted text-nowrap">{{ $req->created_at?->diffForHumans() }}</span>
                        </div>
                        <div class="text-muted text-truncate">{{ Str::limit($req->message, 110) }}</div>
                        <div class="d-flex gap-2 mt-1">
                            <span class="badge bg-primary-subtle text-primary">{{ $req->inquiry_type }}</span>
                            <a href="tel:{{ preg_replace('/\D+/', '', $req->phone) }}" class="text-decoration-none">{{ format_phone_tz($req->phone) }}</a>
                        </div>
                    </div>
                @empty
                    <div class="list-group-item text-muted">{{ __('admin.no_project_requests') }}</div>
                @endforelse
            </div>
        </div>
    </div>
    @unless($isService)
    <div class="col-12 col-lg-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="brand-font">{{ __('admin.project_reservations') }}</span>
                @can('create_reservations')<a href="{{ route('reservations.index') }}" class="small">{{ __('app.view_all') }}</a>@endcan
            </div>
            <div class="list-group list-group-flush small">
                @forelse($reservations as $res)
                    <div class="list-group-item d-flex justify-content-between gap-2">
                        <div class="min-w-0">
                            <strong>{{ __('pages.plot') }} {{ $res->landParcel?->parcel_number }} ({{ $res->landParcel?->block_number ?: '-' }})</strong>
                            <div class="text-muted text-truncate">{{ $res->customer?->full_name }} · {{ format_currency($res->deposit_paid, null, 0) }}</div>
                        </div>
                        <div class="text-end flex-shrink-0">
                            <span class="badge bg-light text-dark border">{{ __('ops.reservation_status_'.strtolower($res->status)) }}</span>
                            <div class="text-muted">{{ $res->reserved_until?->format('d M') }}</div>
                        </div>
                    </div>
                @empty
                    <div class="list-group-item text-muted">{{ __('admin.no_project_reservations') }}</div>
                @endforelse
            </div>
        </div>
    </div>
    @endunless
</div>

@if($canManage && ! $isService)
<!-- Bulk bar -->
<form action="{{ route('admin.projects.plots.bulk', $project) }}" method="POST" id="plotBulkForm">
    @csrf
    <div class="bulk-bar flex-column d-none" id="plotBulkBar">
        <div class="bulk-inner">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary rounded-pill fs-6" id="plotBulkCount">0</span>
                <span class="text-white small fw-semibold">{{ __('admin.plots_selected') }}</span>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <div class="input-group input-group-sm w-auto">
                    <select name="status" class="form-select form-select-sm" aria-label="{{ __('app.status') }}">
                        @foreach(['Available', 'Reserved', 'Sold'] as $st)
                            <option value="{{ $st }}">{{ __('admin.plot_status_'.strtolower($st)) }}</option>
                        @endforeach
                    </select>
                    <button type="submit" name="action" value="status" class="btn btn-light">{{ __('admin.set_status') }}</button>
                </div>
                <div class="input-group input-group-sm w-auto">
                    <span class="input-group-text">TSh</span>
                    <input type="number" name="price" min="0" step="any" class="form-control" style="max-width: 8rem;" placeholder="{{ __('app.price') }}" aria-label="{{ __('app.price') }}">
                    <button type="submit" name="action" value="price" class="btn btn-light">{{ __('admin.set_price') }}</button>
                </div>
                <div class="input-group input-group-sm w-auto">
                    <input type="number" name="rate" min="0" step="any" class="form-control" style="max-width: 7rem;" placeholder="TSh/m²" aria-label="{{ __('admin.field_price_per_sqm') }}">
                    <button type="submit" name="action" value="rate" class="btn btn-light">{{ __('admin.apply_rate') }}</button>
                </div>
                <button type="submit" name="action" value="delete" class="btn btn-sm btn-danger rounded-pill px-3" data-confirm="{{ __('admin.plots_bulk_delete_confirm') }}"><i class="bi bi-trash me-1"></i>{{ __('app.delete') }}</button>
            </div>
        </div>
    </div>
</form>

<!-- Add / edit plot -->
<div class="modal fade" id="plotModal" tabindex="-1" aria-labelledby="plotModalLabel">
    <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
        <form class="modal-content" method="POST" id="plotForm" action="{{ route('admin.projects.plots.store', $project) }}" data-store-url="{{ route('admin.projects.plots.store', $project) }}">
            @csrf
            <input type="hidden" name="_method" value="POST" id="plotFormMethod">
            <div class="modal-header">
                <h5 class="modal-title" id="plotModalLabel">{{ __('admin.add_plot') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('app.cancel') }}"></button>
            </div>
            <div class="modal-body">
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label small fw-semibold" for="plotNumber">{{ __('admin.plot') }} *</label>
                        <input type="text" id="plotNumber" name="parcel_number" class="form-control" required maxlength="100">
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold" for="plotBlock">{{ __('admin.block') }}</label>
                        <input type="text" id="plotBlock" name="block_number" class="form-control" maxlength="50" list="plotBlocks">
                        <datalist id="plotBlocks">@foreach($blocks as $block)<option value="{{ $block }}">@endforeach</datalist>
                    </div>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label small fw-semibold" for="plotArea">{{ __('admin.area') }} (m²)</label>
                        <input type="number" id="plotArea" name="area_sqm" class="form-control" min="0" step="any">
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold" for="plotPrice">{{ __('app.price') }} (TSh)</label>
                        <input type="number" id="plotPrice" name="price" class="form-control" min="0" step="any">
                        @if((float) $project->price_per_sqm > 0)
                            <div class="form-text">{{ __('admin.plot_price_from_rate', ['rate' => number_format((float) $project->price_per_sqm)]) }}</div>
                        @endif
                    </div>
                </div>
                <div class="row g-2">
                    <div class="col-6">
                        <label class="form-label small fw-semibold" for="plotStatus">{{ __('app.status') }}</label>
                        <select id="plotStatus" name="status" class="form-select">
                            @foreach(['Available', 'Reserved', 'Sold'] as $st)
                                <option value="{{ $st }}">{{ __('admin.plot_status_'.strtolower($st)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold" for="plotLandUse">{{ __('admin.land_use') }}</label>
                        <input type="text" id="plotLandUse" name="land_use" class="form-control" maxlength="100" placeholder="Makazi / Residential">
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold" for="plotTitleType">{{ __('pages.ownership_status') }}</label>
                        <input type="text" id="plotTitleType" name="title_deed_type" class="form-control" maxlength="50" placeholder="Right of Occupancy">
                    </div>
                </div>
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
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const toast = (m, i) => { if (window.RREP && window.RREP.toast) { window.RREP.toast(m, i || 'success'); } };
    const statusColors = { Available: '#16a34a', Reserved: '#f59e0b', Sold: '#dc2626' };
    const plotModalEl = document.getElementById('plotModal');
    const plotForm = document.getElementById('plotForm');
    const labels = {!! json_encode(['add' => __('admin.add_plot'), 'edit' => __('admin.edit_plot')]) !!};

    function openPlotModal(url, data) {
        if (!plotForm) { return; }
        plotForm.reset();
        plotForm.action = url || plotForm.dataset.storeUrl;
        document.getElementById('plotFormMethod').value = url ? 'PUT' : 'POST';
        document.getElementById('plotModalLabel').textContent = url ? labels.edit : labels.add;
        if (data) {
            plotForm.parcel_number.value = data.parcel_number || '';
            plotForm.block_number.value = data.block_number || '';
            plotForm.area_sqm.value = data.area_sqm || '';
            plotForm.price.value = data.price || '';
            plotForm.status.value = data.status || 'Available';
            plotForm.land_use.value = data.land_use || '';
            plotForm.title_deed_type.value = data.title_deed_type || '';
        }
        bootstrap.Modal.getOrCreateInstance(plotModalEl).show();
    }

    const addBtn = document.getElementById('addPlotBtn');
    if (addBtn) { addBtn.addEventListener('click', () => openPlotModal(null, null)); }
    document.querySelectorAll('.plot-edit-btn').forEach(btn => btn.addEventListener('click', () => openPlotModal(btn.dataset.url, JSON.parse(btn.dataset.parcel))));

    // Inline status change without reloading; KPI tiles follow.
    document.querySelectorAll('.plot-status-select').forEach(function (select) {
        let previous = select.value;
        select.addEventListener('change', function () {
            select.disabled = true;
            fetch(select.dataset.url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
                body: JSON.stringify({ status: select.value })
            }).then(r => r.ok ? r.json() : Promise.reject(r)).then(function (data) {
                previous = select.value;
                select.closest('tr').dataset.status = select.value;
                Object.entries(data.counts).forEach(([k, v]) => {
                    const tile = document.querySelector('[data-count="' + k + '"]');
                    if (tile) { tile.textContent = v; }
                });
                const layer = plotLayers[select.closest('tr').dataset.uuid];
                if (layer) { layer.setStyle({ fillColor: statusColors[select.value] }); }
                toast(data.message);
            }).catch(function () {
                select.value = previous;
                toast(@json(__('admin.plot_status_failed')), 'error');
            }).finally(() => { select.disabled = false; });
        });
    });

    // Client-side filters
    const rows = Array.from(document.querySelectorAll('.plot-row'));
    let statusFilter = '';
    function applyFilters() {
        const q = (document.getElementById('plotSearch')?.value || '').trim().toLowerCase();
        const block = document.getElementById('plotBlockFilter')?.value || '';
        let shown = 0;
        rows.forEach(function (row) {
            const visible = (!q || row.dataset.search.includes(q)) && (!block || row.dataset.block === block) && (!statusFilter || row.dataset.status === statusFilter);
            row.classList.toggle('d-none', !visible);
            if (visible) { shown++; }
        });
        document.getElementById('plotNoMatch')?.classList.toggle('d-none', shown > 0 || rows.length === 0);
    }
    document.getElementById('plotSearch')?.addEventListener('input', applyFilters);
    document.getElementById('plotBlockFilter')?.addEventListener('change', applyFilters);
    document.querySelectorAll('#plotStatusFilter button').forEach(function (btn) {
        btn.addEventListener('click', function () {
            statusFilter = btn.dataset.status;
            document.querySelectorAll('#plotStatusFilter button').forEach(b => b.classList.toggle('active', b === btn));
            applyFilters();
        });
    });

    // Bulk selection
    const checks = Array.from(document.querySelectorAll('.plot-check'));
    const bar = document.getElementById('plotBulkBar');
    function refreshBulk() {
        const n = checks.filter(c => c.checked).length;
        if (!bar) { return; }
        document.getElementById('plotBulkCount').textContent = n;
        bar.classList.toggle('d-none', n === 0);
        bar.classList.toggle('d-flex', n > 0);
    }
    checks.forEach(c => c.addEventListener('change', refreshBulk));
    document.getElementById('plotSelectAll')?.addEventListener('change', function () {
        checks.forEach(c => { if (!c.closest('tr').classList.contains('d-none')) { c.checked = this.checked; } });
        refreshBulk();
    });
    document.getElementById('plotBulkForm')?.addEventListener('submit', function (e) {
        const msg = e.submitter && e.submitter.dataset.confirm;
        if (msg && !window.confirm(msg)) { e.preventDefault(); return; }
        this.querySelectorAll('input[name="ids[]"]').forEach(i => i.remove());
        checks.filter(c => c.checked).forEach(c => {
            const input = document.createElement('input');
            input.type = 'hidden'; input.name = 'ids[]'; input.value = c.value;
            this.appendChild(input);
        });
    });

    // Plot map
    const plotLayers = {};
    const mapEl = document.getElementById('adminPlotMap');
    if (mapEl && window.L) {
        const map = L.map(mapEl);
        const streets = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap' }).addTo(map);
        const satellite = L.tileLayer('https://mt1.google.com/vt/lyrs=y&x={x}&y={y}&z={z}', { maxZoom: 20, attribution: '&copy; Google' });
        L.control.layers({ 'Map': streets, 'Satellite': satellite }, null, { position: 'topright' }).addTo(map);
        const layer = L.geoJSON(@json($plotGeojson), {
            style: f => ({ fillColor: statusColors[f.properties.status] || '#64748b', color: '#fff', weight: 1.2, fillOpacity: 0.7 }),
            onEachFeature: function (feature, lyr) {
                const p = feature.properties;
                plotLayers[p.uuid] = lyr;
                lyr.bindTooltip(@json(__('pages.plot')) + ' ' + p.parcel_number + (p.block_number ? ' (' + p.block_number + ')' : ''));
                lyr.on('click', function () {
                    const row = document.querySelector('.plot-row[data-uuid="' + p.uuid + '"]');
                    const edit = row && row.querySelector('.plot-edit-btn');
                    if (edit) { edit.click(); } else if (row) { row.scrollIntoView({ behavior: 'smooth', block: 'center' }); }
                });
            }
        }).addTo(map);
        try { map.fitBounds(layer.getBounds(), { padding: [20, 20] }); } catch (e) { map.setView([-6.17, 35.75], 13); }
    }
});
</script>
@endsection
