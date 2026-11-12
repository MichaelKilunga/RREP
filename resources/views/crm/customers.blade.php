@extends('layouts.app')

@section('title', __('app.customers'))

@section('content')
@php
    $canManage = auth()->user()->can('manage_customers');
    $canDelete = auth()->user()->can('delete_customers');
    $canSms = auth()->user()->can('view_sms_logs') && auth()->user()->can('send_sms');
    $canBulk = $canDelete || $canSms;
@endphp

<div class="page-header">
    <div class="min-w-0">
        <h1 class="brand-font mb-1">{{ __('ops.customers_title') }}</h1>
        <p class="text-muted small mb-0">{{ __('ops.customers_subtitle', ['count' => $customers->total()]) }}</p>
    </div>
    <div class="page-actions">
        @can('export_reports')
            <a href="{{ route('crm.customers.export', request()->query()) }}" class="btn btn-light border"><i class="bi bi-filetype-csv me-1"></i>CSV</a>
        @endcan
        @if($canManage)
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newCustomerModal">
                <i class="bi bi-person-plus-fill me-1"></i> {{ __('ops.add_customer') }}
            </button>
        @endif
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-6 col-md-3"><div class="kpi-card"><div class="kpi-label">{{ __('ops.customers_total') }}</div><div class="kpi-value">{{ number_format($stats['total']) }}</div><span class="kpi-icon"><i class="bi bi-people"></i></span></div></div>
    <div class="col-6 col-md-3"><div class="kpi-card kpi-info"><div class="kpi-label">{{ __('ops.customers_new_month') }}</div><div class="kpi-value">{{ number_format($stats['new_month']) }}</div></div></div>
    <div class="col-6 col-md-3"><div class="kpi-card kpi-success"><div class="kpi-label">{{ __('ops.kyc_verified') }}</div><div class="kpi-value">{{ number_format($stats['verified']) }}</div></div></div>
    <div class="col-6 col-md-3"><div class="kpi-card kpi-warning"><div class="kpi-label">{{ __('ops.customers_with_holds') }}</div><div class="kpi-value">{{ number_format($stats['with_holds']) }}</div></div></div>
</div>

<form method="GET" action="{{ route('crm.customers') }}" class="card p-2 p-md-3 mb-3">
    <div class="filter-bar">
        <div class="flex-grow-1">
            <div class="input-group input-group-sm">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="search" name="q" class="form-control" value="{{ request('q') }}" placeholder="{{ __('ops.customer_search_placeholder') }}" aria-label="{{ __('app.search') }}">
            </div>
        </div>
        <div>
            <select name="type" class="form-select form-select-sm" onchange="this.form.submit()" aria-label="{{ __('ops.customer_type') }}">
                <option value="">{{ __('ops.all_types') }}</option>
                <option value="Individual" @selected(request('type') === 'Individual')>{{ __('ops.individual') }}</option>
                <option value="Corporate" @selected(request('type') === 'Corporate')>{{ __('ops.corporate') }}</option>
            </select>
        </div>
        <div>
            <select name="kyc" class="form-select form-select-sm" onchange="this.form.submit()" aria-label="{{ __('ops.kyc_status') }}">
                <option value="">{{ __('ops.all_kyc') }}</option>
                @foreach(['Pending', 'Verified', 'Rejected'] as $kyc)
                    <option value="{{ $kyc }}" @selected(request('kyc') === $kyc)>{{ __('ops.kyc_'.strtolower($kyc)) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <select name="city" class="form-select form-select-sm" onchange="this.form.submit()" aria-label="{{ __('ops.region') }}">
                <option value="">{{ __('ops.all_regions') }}</option>
                @foreach(tz_regions() as $region)
                    <option value="{{ $region }}" @selected(request('city') === $region)>{{ $region }}</option>
                @endforeach
            </select>
        </div>
        <div class="filter-actions">
            <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-search"></i></button>
            <a href="{{ route('crm.customers') }}" class="btn btn-sm btn-light border">{{ __('ops.reset') }}</a>
        </div>
    </div>
</form>

<div class="card">
    @if($canBulk && $customers->count() > 0)
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 py-2">
            <span class="small text-muted">{{ __('ops.bulk_select_hint') }}</span>
            <button type="button" class="btn btn-sm btn-light border" data-bulk-all="customers" aria-pressed="false" data-label-all="{{ __('admin.select_all') }}" data-label-none="{{ __('admin.deselect_all') }}">
                <i class="bi bi-check2-square me-1"></i><span data-bulk-all-label>{{ __('admin.select_all') }} ({{ $customers->count() }})</span>
            </button>
        </div>
    @endif
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 table-stack small">
            <thead class="table-light">
                <tr>
                    @if($canBulk)<th class="stack-check" style="width: 2.5rem;"><input type="checkbox" class="form-check-input" data-bulk-all="customers" aria-label="{{ __('admin.select_all') }}"></th>@endif
                    <th>{{ __('ops.customer') }}</th>
                    <th>{{ __('ops.phone_email') }}</th>
                    <th>{{ __('ops.region_address') }}</th>
                    <th>{{ __('ops.kyc_status') }}</th>
                    <th>{{ __('ops.engagement') }}</th>
                    <th>{{ __('ops.last_contact') }}</th>
                    <th class="text-end">{{ __('app.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $c)
                    @php
                        $waDigits = preg_replace('/\D+/', '', (string) $c->phone);
                        if (str_starts_with($waDigits, '0')) {
                            $waDigits = '255'.substr($waDigits, 1);
                        }
                    @endphp
                    <tr>
                        @if($canBulk)<td class="stack-check"><input type="checkbox" class="form-check-input" value="{{ $c->id }}" data-bulk-item="customers" data-phone="{{ $c->phone }}" aria-label="{{ $c->full_name }}"></td>@endif
                        <td class="stack-primary">
                            <a href="{{ route('crm.customers.show', $c) }}" class="fw-bold text-dark text-decoration-none">{{ $c->full_name }}</a>
                            <small class="text-muted d-block">{{ $c->company_name ?: ($c->customer_type === 'Corporate' ? __('ops.corporate') : __('ops.individual')) }}</small>
                        </td>
                        <td>
                            <div><a href="tel:{{ $c->phone }}" class="text-decoration-none"><i class="bi bi-telephone text-primary me-1"></i>{{ format_phone_tz($c->phone) }}</a></div>
                            @if($c->email)<small class="text-muted text-break">{{ $c->email }}</small>@endif
                        </td>
                        <td>
                            <div>{{ $c->city ?: '-' }}</div>
                            @if($c->address)<small class="text-muted">{{ $c->address }}</small>@endif
                        </td>
                        <td><span class="badge badge-status-{{ strtolower((string) $c->kyc_status) }}"><i class="bi bi-shield-check me-1"></i>{{ $c->kyc_status ? __('ops.kyc_'.strtolower($c->kyc_status)) : '-' }}</span></td>
                        <td class="text-nowrap">
                            <span class="badge bg-warning-subtle text-warning-emphasis" title="{{ __('ops.requests_title') }}"><i class="bi bi-inboxes me-1"></i>{{ $c->requests_count }}</span>
                            <span class="badge bg-info-subtle text-info" title="{{ __('app.leads') }}"><i class="bi bi-funnel me-1"></i>{{ $c->leads_count }}</span>
                            <span class="badge bg-success-subtle text-success" title="{{ __('app.reservations') }}"><i class="bi bi-bookmark me-1"></i>{{ $c->reservations_count }}</span>
                            <span class="badge bg-secondary-subtle text-secondary" title="{{ __('ops.invoices_short') }}"><i class="bi bi-receipt me-1"></i>{{ $c->invoices_count }}</span>
                        </td>
                        <td class="text-muted text-nowrap">{{ $c->last_interaction_at?->diffForHumans() ?? '—' }}</td>
                        <td class="stack-actions text-end">
                            <div class="d-inline-flex gap-1">
                                <a href="{{ route('crm.customers.show', $c) }}" class="btn btn-light border btn-sm" title="{{ __('ops.open_profile') }}"><i class="bi bi-person-vcard"></i><span class="d-md-none ms-1">{{ __('ops.open_profile') }}</span></a>
                                @if($waDigits)
                                    <a href="https://wa.me/{{ $waDigits }}" target="_blank" rel="noopener" class="btn btn-whatsapp btn-sm" title="WhatsApp"><i class="bi bi-whatsapp"></i></a>
                                @endif
                                <a href="tel:{{ $c->phone }}" class="btn btn-light border btn-sm" title="{{ __('ops.call') }}"><i class="bi bi-telephone"></i></a>
                                @if($canDelete)
                                    <form action="{{ route('crm.customers.destroy', $c) }}" method="POST" class="m-0" onsubmit="return confirm({{ json_encode(__('ops.customer_delete_confirm', ['name' => $c->full_name])) }});">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="{{ __('app.delete') }}" aria-label="{{ __('app.delete') }}"><i class="bi bi-trash"></i></button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $canBulk ? 8 : 7 }}" class="stack-no-label">
                            <div class="empty-state">
                                <div class="empty-icon"><i class="bi bi-people"></i></div>
                                <h6>{{ __('ops.no_customers') }}</h6>
                                <p class="small mb-3">{{ __('ops.no_customers_hint') }}</p>
                                @if($canManage)<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newCustomerModal"><i class="bi bi-person-plus-fill me-1"></i> {{ __('ops.add_customer') }}</button>@endif
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($customers->hasPages())
        <div class="p-3 border-top d-flex justify-content-center justify-content-md-end">
            {{ $customers->links() }}
        </div>
    @endif
</div>

@if($canBulk)
<form action="{{ route('crm.customers.bulk') }}" method="POST" data-bulk-form="customers">
    @csrf
    <div class="bulk-bar flex-column d-none" data-bulk-bar="customers">
        <div class="bulk-inner">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary rounded-pill fs-6" data-bulk-count="customers">0</span>
                <span class="text-white small fw-semibold">{{ __('ops.customers_selected') }}</span>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <button type="button" class="btn btn-sm btn-outline-light rounded-pill px-3" data-bulk-all="customers" data-label-all="{{ __('admin.select_all') }}" data-label-none="{{ __('admin.deselect_all') }}">
                    <i class="bi bi-check2-all me-1"></i><span data-bulk-all-label>{{ __('admin.select_all') }}</span>
                </button>
                @if($canSms)
                    <button type="button" class="btn btn-sm btn-light rounded-pill px-3" id="smsSelectedCustomers" data-url="{{ route('sms.index') }}"><i class="bi bi-chat-dots me-1"></i>{{ __('ops.sms_selected') }}</button>
                @endif
                @if($canDelete)
                    <button type="submit" name="action" value="delete" class="btn btn-sm btn-danger rounded-pill px-3" data-confirm="{{ __('ops.customers_bulk_delete_confirm') }}"><i class="bi bi-trash me-1"></i>{{ __('app.delete') }}</button>
                @endif
                <button type="button" class="btn btn-sm btn-link text-white-50 text-decoration-none touch-target p-0" data-bulk-clear="customers" title="{{ __('admin.deselect_all') }}" aria-label="{{ __('admin.deselect_all') }}"><i class="bi bi-x-lg"></i></button>
            </div>
        </div>
    </div>
</form>
@endif

@if($canManage)
<!-- New Customer Modal -->
<div class="modal fade" id="newCustomerModal" tabindex="-1" aria-labelledby="newCustomerModalLabel">
    <div class="modal-dialog modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title brand-font" id="newCustomerModalLabel">{{ __('ops.register_customer') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('app.cancel') }}"></button>
            </div>
            <form action="{{ route('crm.customers.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    @include('crm.partials.customer-fields', ['customer' => null, 'prefix' => 'newCust'])
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">{{ __('app.cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('ops.save_customer') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const smsButton = document.getElementById('smsSelectedCustomers');
    if (!smsButton) { return; }
    smsButton.addEventListener('click', function () {
        const phones = Array.from(document.querySelectorAll('[data-bulk-item="customers"]:checked'))
            .map(box => box.dataset.phone)
            .filter(Boolean);
        if (!phones.length) { return; }
        window.location.href = smsButton.dataset.url + '?to=' + encodeURIComponent(phones.join(', '));
    });
});
</script>
@endsection
