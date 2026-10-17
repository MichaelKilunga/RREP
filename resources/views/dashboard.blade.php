@extends('layouts.app')

@section('title', __('app.dashboard'))

@section('content')
@php
    $statusLabels = [
        'Available' => __('admin.status_available'),
        'Reserved' => __('admin.status_reserved'),
        'Under Contract' => __('admin.status_under_contract'),
        'Sold' => __('admin.status_sold'),
        'Leased' => __('admin.status_leased'),
        'Maintenance' => __('admin.status_maintenance'),
        'Off Market' => __('admin.status_off_market'),
    ];
    $kpis = [
        ['label' => __('app.total_properties'), 'value' => $metrics['total_properties'] ?? 0, 'icon' => 'bi-building', 'accent' => 'var(--rrep-primary)'],
        ['label' => __('app.active_listings'), 'value' => $metrics['active_listings'] ?? 0, 'icon' => 'bi-megaphone', 'accent' => 'var(--rrep-success)'],
        ['label' => __('app.total_leads'), 'value' => $metrics['total_leads'] ?? 0, 'icon' => 'bi-people', 'accent' => 'var(--rrep-warning)'],
        ['label' => __('app.pending_reservations'), 'value' => $metrics['pending_reservations'] ?? 0, 'icon' => 'bi-bookmark-check', 'accent' => 'var(--rrep-danger)'],
        ['label' => __('app.active_deals'), 'value' => $metrics['active_deals'] ?? 0, 'icon' => 'bi-briefcase', 'accent' => 'var(--rrep-info)'],
        ['label' => __('app.revenue_this_month'), 'value' => format_currency($metrics['revenue_this_month'] ?? 0, null, 0), 'icon' => 'bi-cash-stack', 'accent' => 'var(--rrep-accent)'],
    ];
    $quickActions = [
        ['route' => 'admin.properties.create', 'icon' => 'bi-plus-circle', 'label' => __('app.add_property')],
        ['route' => 'crm.leads', 'icon' => 'bi-person-plus', 'label' => __('admin.quick_leads')],
        ['route' => 'reservations.index', 'icon' => 'bi-bookmark-check', 'label' => __('admin.quick_reservations')],
        ['route' => 'finance.invoices', 'icon' => 'bi-receipt', 'label' => __('admin.quick_invoices')],
        ['route' => 'documents.index', 'icon' => 'bi-folder-check', 'label' => __('admin.quick_documents')],
        ['route' => 'survey.index', 'icon' => 'bi-compass', 'label' => __('admin.quick_survey')],
    ];
@endphp

<div class="page-header">
    <div class="min-w-0">
        <span class="badge bg-primary-subtle text-primary rounded-pill mb-2"><i class="bi bi-shield-check me-1"></i>{{ __('admin.console_badge') }}</span>
        <h1 class="brand-font">{{ __('admin.dashboard_title') }}</h1>
        <p class="text-muted small mb-0 max-w-2xl">{{ __('admin.dashboard_subtitle') }}</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.properties.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> {{ __('app.add_property') }}
        </a>
        <a href="{{ route('survey.index') }}" class="btn btn-outline-primary">
            <i class="bi bi-compass me-1"></i> {{ __('admin.new_survey') }}
        </a>
    </div>
</div>

<div class="row g-3 mb-4">
    @foreach($kpis as $kpi)
        <div class="col-6 col-md-4 col-xl-2">
            <div class="kpi-card" style="--kpi-accent: {{ $kpi['accent'] }}">
                <span class="kpi-icon"><i class="bi {{ $kpi['icon'] }}"></i></span>
                <div class="kpi-label pe-5">{{ $kpi['label'] }}</div>
                <div class="kpi-value">{{ $kpi['value'] }}</div>
            </div>
        </div>
    @endforeach
</div>

<div class="card mb-4">
    <div class="card-header d-flex align-items-center gap-2">
        <i class="bi bi-lightning-charge-fill text-warning"></i> {{ __('admin.quick_actions') }}
    </div>
    <div class="card-body">
        <div class="row g-2">
            @foreach($quickActions as $action)
                <div class="col-6 col-sm-4 col-lg-2">
                    <a href="{{ route($action['route']) }}" class="btn btn-light border w-100 h-100 d-flex flex-column align-items-center justify-content-center gap-1 py-3 text-dark">
                        <i class="bi {{ $action['icon'] }} fs-4 text-primary"></i>
                        <span class="small fw-semibold text-center">{{ $action['label'] }}</span>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <span class="brand-font">{{ __('app.recent_properties') }}</span>
        <a href="{{ route('admin.properties.index') }}" class="btn btn-sm btn-outline-primary">{{ __('app.view_all') }} <i class="bi bi-arrow-right ms-1"></i></a>
    </div>
    @if($recentProperties->isEmpty())
        <div class="empty-state">
            <div class="empty-icon"><i class="bi bi-house-add"></i></div>
            <h5>{{ __('admin.no_properties_title') }}</h5>
            <p class="mb-3">{{ __('admin.no_properties_text') }}</p>
            <a href="{{ route('admin.properties.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> {{ __('app.add_property') }}</a>
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover table-stack align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('admin.property') }}</th>
                        <th>{{ __('admin.type') }}</th>
                        <th>{{ __('app.location') }}</th>
                        <th>{{ __('app.price') }}</th>
                        <th>{{ __('app.status') }}</th>
                        <th class="text-end">{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentProperties as $p)
                        <tr>
                            <td class="stack-primary">
                                <a href="{{ route('admin.properties.show', $p) }}" class="fw-bold text-dark text-decoration-none d-block text-truncate max-w-500">{{ $p->title }}</a>
                                <div class="text-muted small">{{ $p->property_code }}</div>
                            </td>
                            <td><span class="badge bg-light text-dark border">{{ $p->propertyType?->name ?? __('app.nav_land') }}</span></td>
                            <td>{{ $p->city }}</td>
                            @php
                                $isRental = in_array($p->listing_type, ['Rent', 'Lease'], true);
                                $shortPrice = $isRental ? format_money_short($p->rent_price ?? 0).' / '.strtolower($p->rent_period ?? 'Monthly') : format_money_short($p->price ?? 0);
                            @endphp
                            <td class="fw-bold text-success" title="{{ $p->formatted_price }}">{{ $shortPrice }}</td>
                            <td><span class="badge badge-status-{{ strtolower(str_replace(' ', '', $p->status)) }}">{{ $statusLabels[$p->status] ?? $p->status }}</span></td>
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
    @endif
</div>
@endsection
