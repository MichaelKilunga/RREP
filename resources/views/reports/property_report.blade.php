@extends('layouts.app')

@section('title', __('ops.report_property_short'))

@section('content')
@php
    $byStatus = $properties->groupBy('status')->map->count();
    $available = $properties->where('status', 'Available')->count();
    $sold = $properties->where('status', 'Sold')->count();
    $cities = $properties->pluck('city')->filter()->unique()->sort()->values();
@endphp

<div class="page-header d-print-none">
    <div class="min-w-0">
        <h1 class="brand-font mb-1">{{ __('ops.report_property_heading') }}</h1>
        <p class="text-muted small mb-0">{{ __('ops.report_generated_at', ['date' => now()->format('d M Y, H:i')]) }} EAT</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('reports.index') }}" class="btn btn-light border"><i class="bi bi-arrow-left me-1"></i> {{ __('ops.reports_center') }}</a>
        <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}" class="btn btn-outline-success"><i class="bi bi-file-earmark-excel me-1"></i> {{ __('ops.export_csv') }}</a>
        <button type="button" onclick="window.print()" class="btn btn-dark"><i class="bi bi-printer me-1"></i> {{ __('ops.print') }}</button>
    </div>
</div>

<h2 class="h5 brand-font d-none d-print-block mb-3">Property Inventory &amp; Valuation Report</h2>

<!-- KPI strip -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-label">{{ __('app.total_properties') }}</div>
            <div class="kpi-value">{{ number_format($properties->count()) }}</div>
            <span class="kpi-icon"><i class="bi bi-building"></i></span>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card kpi-success">
            <div class="kpi-label">{{ __('ops.gross_asset_value') }}</div>
            <div class="kpi-value text-success">{{ format_money_short($totalValuation) }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card kpi-info">
            <div class="kpi-label">{{ __('ops.rent_potential') }}</div>
            <div class="kpi-value">{{ format_money_short($totalRentPotential) }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card kpi-warning">
            <div class="kpi-label">{{ __('app.available') }} / {{ __('app.sold') }}</div>
            <div class="kpi-value">{{ $available }} / {{ $sold }}</div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4 d-print-none">
    <div class="card-body">
        <button class="btn btn-outline-secondary btn-sm d-md-none w-100 mb-2" type="button" data-bs-toggle="collapse" data-bs-target="#reportFilters" aria-expanded="false" aria-controls="reportFilters"><i class="bi bi-funnel me-1"></i>{{ __('ops.filters') }}</button>
        <form method="GET" action="{{ route('reports.properties') }}" id="reportFilters" class="collapse d-md-block">
            <div class="filter-bar">
                <div>
                    <label for="filterStatus" class="visually-hidden">{{ __('app.status') }}</label>
                    <select id="filterStatus" name="status" class="form-select">
                        <option value="">{{ __('ops.all_statuses') }}</option>
                        @foreach(['Available', 'Reserved', 'Sold', 'Leased', 'Under Contract', 'Under Review', 'Off Market'] as $st)
                            <option value="{{ $st }}" @selected(request('status') === $st)>{{ $st }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="filterCity" class="visually-hidden">{{ __('ops.region') }}</label>
                    <select id="filterCity" name="city" class="form-select">
                        <option value="">{{ __('ops.all_regions') }}</option>
                        @foreach(tz_regions() as $region)
                            <option value="{{ $region }}" @selected(request('city') === $region)>{{ $region }}</option>
                        @endforeach
                        @foreach($cities->diff(tz_regions()) as $city)
                            <option value="{{ $city }}" @selected(request('city') === $city)>{{ $city }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-funnel me-1"></i>{{ __('app.filter') }}</button>
                    <a href="{{ route('reports.properties') }}" class="btn btn-light border">{{ __('ops.reset') }}</a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-12 col-lg-5">
        <div class="card h-100">
            <div class="card-header brand-font">{{ __('ops.inventory_by_status') }}</div>
            <div class="card-body">
                <div class="ratio ratio-4x3"><canvas id="propertyStatusChart" aria-label="{{ __('ops.inventory_by_status') }}" role="img"></canvas></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-7">
        <div class="card h-100">
            <div class="card-header brand-font">{{ __('ops.value_by_region') }}</div>
            <div class="card-body">
                <div class="ratio ratio-16x9"><canvas id="propertyCityChart" aria-label="{{ __('ops.value_by_region') }}" role="img"></canvas></div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 table-stack small">
            <thead class="table-light">
                <tr>
                    <th>{{ __('ops.code') }}</th>
                    <th>{{ __('app.title') }}</th>
                    <th>{{ __('ops.type') }}</th>
                    <th>{{ __('ops.region') }}</th>
                    <th>{{ __('app.listing_type') }}</th>
                    <th>{{ __('app.status') }}</th>
                    <th>{{ __('ops.price_valuation') }}</th>
                    <th>{{ __('app.rent_price') }}</th>
                    <th>{{ __('ops.owner') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($properties as $p)
                    <tr>
                        <td class="stack-primary"><span class="fw-bold text-primary">{{ $p->property_code }}</span> &bull; {{ $p->title }}</td>
                        <td class="fw-semibold d-none d-md-table-cell">{{ $p->title }}</td>
                        <td>{{ $p->propertyType?->name }}</td>
                        <td>{{ $p->city }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $p->listing_type }}</span></td>
                        <td><span class="badge badge-status-{{ strtolower(str_replace(' ', '', $p->status)) }}">{{ $p->status }}</span></td>
                        <td class="fw-bold text-success">{{ format_currency($p->price) }}</td>
                        <td>{{ $p->rent_price ? format_currency($p->rent_price) : '-' }}</td>
                        <td>{{ $p->owner?->full_name ?? __('ops.company_asset') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="stack-no-label">
                            <div class="empty-state">
                                <div class="empty-icon"><i class="bi bi-building"></i></div>
                                <h6>{{ __('ops.no_report_rows') }}</h6>
                                <p class="small mb-0">{{ __('ops.no_report_rows_hint') }}</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (!window.Chart) { return; }
    const statusData = @json($byStatus);
    const cityData = @json($properties->groupBy('city')->map(fn ($group) => (float) $group->sum('price'))->sortDesc()->take(8));
    const palette = ['#0f52ba', '#10b981', '#d97706', '#dc2626', '#4338ca', '#0284c7', '#7e22ce', '#475569'];
    const statusCanvas = document.getElementById('propertyStatusChart');
    const cityCanvas = document.getElementById('propertyCityChart');

    if (statusCanvas && Object.keys(statusData).length) {
        new Chart(statusCanvas, {
            type: 'doughnut',
            data: { labels: Object.keys(statusData), datasets: [{ data: Object.values(statusData), backgroundColor: palette }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
        });
    }
    if (cityCanvas && Object.keys(cityData).length) {
        new Chart(cityCanvas, {
            type: 'bar',
            data: { labels: Object.keys(cityData), datasets: [{ label: 'TSh', data: Object.values(cityData), backgroundColor: palette[0], borderRadius: 6 }] },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { ticks: { callback: function (v) { return v >= 1e9 ? (v / 1e9).toFixed(1) + 'B' : v >= 1e6 ? (v / 1e6).toFixed(0) + 'M' : v; } } } }
            }
        });
    }
});
</script>
@endsection
