@extends('layouts.app')

@section('title', __('app.dashboard'))

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h3 class="brand-font mb-1">{{ __('app.dashboard') }} Overview</h3>
        <p class="text-muted small mb-0">{{ setting('company_name', current_organization()?->name ?? 'RehoSpace') }} &bull; Operational Hub ({{ current_currency() }})</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('properties.create') }}" class="btn btn-primary btn-sm d-flex align-items-center gap-1 shadow-sm">
            <i class="bi bi-plus-lg"></i> {{ __('app.add_property') }}
        </a>
        <a href="{{ route('crm.leads') }}" class="btn btn-light border btn-sm d-flex align-items-center gap-1">
            <i class="bi bi-funnel"></i> {{ __('app.leads') }}
        </a>
    </div>
</div>

<!-- KPI Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card p-3 h-100">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small fw-semibold text-uppercase">{{ __('app.total_properties') }}</div>
                    <h3 class="fw-bold mb-0 mt-1">{{ $metrics['total_properties'] }}</h3>
                    <small class="text-success fw-semibold"><i class="bi bi-check-circle me-1"></i>{{ $metrics['active_listings'] }} {{ __('app.active_listings') }}</small>
                </div>
                <div class="rounded-3 bg-primary-subtle text-primary p-3 fs-3">
                    <i class="bi bi-building"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card p-3 h-100">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small fw-semibold text-uppercase">{{ __('app.total_leads') }}</div>
                    <h3 class="fw-bold mb-0 mt-1">{{ $metrics['total_leads'] }}</h3>
                    <small class="text-info fw-semibold"><i class="bi bi-people me-1"></i>{{ $metrics['total_customers'] }} {{ __('app.customers') }}</small>
                </div>
                <div class="rounded-3 bg-info-subtle text-info p-3 fs-3">
                    <i class="bi bi-funnel"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card p-3 h-100">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small fw-semibold text-uppercase">{{ __('app.revenue_this_month') }}</div>
                    <h4 class="fw-bold mb-0 mt-1 text-success">{{ format_currency($metrics['revenue_this_month']) }}</h4>
                    <small class="text-muted">Total: {{ format_currency($metrics['total_revenue_all_time']) }}</small>
                </div>
                <div class="rounded-3 bg-success-subtle text-success p-3 fs-3">
                    <i class="bi bi-cash-stack"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card p-3 h-100">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small fw-semibold text-uppercase">{{ __('app.active_deals') }}</div>
                    <h3 class="fw-bold mb-0 mt-1 text-primary">{{ $metrics['active_deals'] }}</h3>
                    <small class="text-warning fw-semibold"><i class="bi bi-clock-history me-1"></i>{{ $metrics['pending_reservations'] }} {{ __('app.pending_reservations') }}</small>
                </div>
                <div class="rounded-3 bg-warning-subtle text-warning p-3 fs-3">
                    <i class="bi bi-briefcase"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts & Pipeline -->
<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="brand-font"><i class="bi bi-graph-up text-primary me-2"></i>{{ __('app.financial_overview') }} (Cashflow & Collections)</span>
                <span class="badge bg-light text-dark border">{{ current_currency() }}</span>
            </div>
            <div class="card-body">
                <canvas id="revenueChart" height="110"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header brand-font">
                <i class="bi bi-funnel-fill text-info me-2"></i>{{ __('app.sales_pipeline') }}
            </div>
            <div class="card-body">
                @foreach($metrics['pipeline_stages'] as $stage => $count)
                    <div class="mb-3">
                        <div class="d-flex justify-content-between small fw-semibold mb-1">
                            <span>{{ $stage }}</span>
                            <span>{{ $count }} leads</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $metrics['total_leads'] > 0 ? ($count / $metrics['total_leads']) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- Recent Tables -->
<div class="row g-3">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="brand-font"><i class="bi bi-building me-2 text-primary"></i>{{ __('app.recent_properties') }}</span>
                <a href="{{ route('properties.index') }}" class="small text-decoration-none">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                    <thead class="table-light">
                        <tr>
                            <th>Property</th>
                            <th>Type</th>
                            <th>Price</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentProperties as $p)
                            <tr>
                                <td>
                                    <a href="{{ route('properties.show', $p) }}" class="fw-semibold text-dark text-decoration-none">
                                        {{ Str::limit($p->title, 28) }}
                                    </a>
                                    <div class="text-muted" style="font-size: 0.75rem;">{{ $p->city }}, {{ $p->property_code }}</div>
                                </td>
                                <td>{{ $p->propertyType?->name }}</td>
                                <td class="fw-bold text-success">{{ $p->formatted_price }}</td>
                                <td><span class="badge badge-status-{{ strtolower($p->status) }}">{{ $p->status }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-3">No properties listed.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="brand-font"><i class="bi bi-person-lines-fill me-2 text-info"></i>{{ __('app.recent_leads') }}</span>
                <a href="{{ route('crm.leads') }}" class="small text-decoration-none">View Pipeline</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                    <thead class="table-light">
                        <tr>
                            <th>Prospect</th>
                            <th>Interest</th>
                            <th>Stage</th>
                            <th>Agent</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentLeads as $l)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $l->customer?->full_name }}</div>
                                    <small class="text-muted">{{ $l->customer?->phone }}</small>
                                </td>
                                <td>{{ Str::limit($l->title, 25) }}</td>
                                <td><span class="badge bg-primary-subtle text-primary">{{ $l->stage }}</span></td>
                                <td>{{ $l->agent?->user?->name ?? 'Unassigned' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-3">No leads active.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('revenueChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug'],
            datasets: [{
                label: 'Revenue Collections (TZS)',
                data: [120000000, 240000000, 180000000, 310000000, 450000000, 625000000],
                borderColor: '#0f52ba',
                backgroundColor: 'rgba(15, 82, 186, 0.08)',
                fill: true,
                tension: 0.35,
                borderWidth: 2.5
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) { return (value / 1000000) + 'M'; }
                    }
                }
            }
        }
    });
});
</script>
@endsection
