@extends('layouts.app')

@section('title', __('ops.kyc_title'))

@section('content')
<div class="page-header">
    <div class="min-w-0">
        <h1 class="brand-font mb-1"><i class="bi bi-shield-check text-success me-2"></i>{{ __('ops.kyc_title') }}</h1>
        <p class="text-muted small mb-0">{{ __('ops.kyc_subtitle') }}</p>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="kpi-card kpi-warning">
            <div class="kpi-label">{{ __('ops.customers_pending_kyc') }}</div>
            <div class="kpi-value">{{ $pendingCustomers->count() }}</div>
            <span class="kpi-icon"><i class="bi bi-person-badge"></i></span>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card kpi-info">
            <div class="kpi-label">{{ __('ops.owners_pending_kyc') }}</div>
            <div class="kpi-value">{{ $pendingOwners->count() }}</div>
            <span class="kpi-icon"><i class="bi bi-house-check"></i></span>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header brand-font d-flex justify-content-between align-items-center gap-2">
        <span class="text-truncate">{{ __('ops.customer_kyc_queue') }}</span>
        <span class="badge badge-status-pending text-nowrap">{{ __('ops.pending_count', ['count' => $pendingCustomers->count()]) }}</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 table-stack small">
            <thead class="table-light">
                <tr>
                    <th>{{ __('ops.customer') }}</th>
                    <th>{{ __('ops.type') }}</th>
                    <th>{{ __('ops.nida_passport') }}</th>
                    <th>{{ __('ops.tin_number') }}</th>
                    <th>{{ __('ops.phone') }}</th>
                    <th>{{ __('ops.kyc_status') }}</th>
                    <th class="text-end">{{ __('app.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pendingCustomers as $cust)
                    <tr>
                        <td class="stack-primary"><span class="fw-bold">{{ $cust->full_name }}</span></td>
                        <td>{{ $cust->customer_type === 'Corporate' ? __('ops.corporate') : __('ops.individual') }}</td>
                        <td><code>{{ $cust->national_id_passport ?: __('ops.not_provided') }}</code></td>
                        <td>{{ $cust->tax_number ?: '-' }}</td>
                        <td><a href="tel:{{ $cust->phone }}" class="text-decoration-none">{{ format_phone_tz($cust->phone) }}</a></td>
                        <td><span class="badge badge-status-{{ strtolower($cust->kyc_status) }}">{{ __('ops.kyc_'.strtolower($cust->kyc_status)) }}</span></td>
                        <td class="stack-actions text-end">
                            <div class="d-inline-flex gap-1">
                                <form action="{{ route('compliance.verify_customer', $cust) }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="status" value="Verified">
                                    <button type="submit" class="btn btn-sm btn-success"><i class="bi bi-check-circle me-1"></i>{{ __('ops.verify') }}</button>
                                </form>
                                <form action="{{ route('compliance.verify_customer', $cust) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('ops.confirm_reject') }}')">
                                    @csrf
                                    <input type="hidden" name="status" value="Rejected">
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-x-circle me-1"></i>{{ __('ops.reject') }}</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="stack-no-label">
                            <div class="empty-state py-4">
                                <div class="empty-icon"><i class="bi bi-shield-check"></i></div>
                                <h6>{{ __('ops.kyc_customers_up_to_date') }}</h6>
                                <p class="small mb-0">{{ __('ops.kyc_queue_empty_hint') }}</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-header brand-font d-flex justify-content-between align-items-center gap-2">
        <span class="text-truncate">{{ __('ops.owner_kyc_queue') }}</span>
        <span class="badge badge-status-pending text-nowrap">{{ __('ops.pending_count', ['count' => $pendingOwners->count()]) }}</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 table-stack small">
            <thead class="table-light">
                <tr>
                    <th>{{ __('ops.owner') }}</th>
                    <th>{{ __('ops.company_name') }}</th>
                    <th>{{ __('ops.nida_number') }}</th>
                    <th>{{ __('ops.bank_account') }}</th>
                    <th>{{ __('ops.kyc_status') }}</th>
                    <th class="text-end">{{ __('app.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pendingOwners as $own)
                    <tr>
                        <td class="stack-primary"><span class="fw-bold">{{ $own->full_name }}</span></td>
                        <td>{{ $own->company_name ?: __('ops.individual') }}</td>
                        <td><code>{{ $own->national_id ?: __('ops.not_provided') }}</code></td>
                        <td>{{ $own->bank_name }}{{ $own->bank_account_number ? ': '.$own->bank_account_number : '' }}</td>
                        <td><span class="badge badge-status-{{ strtolower((string) $own->kyc_status) }}">{{ $own->kyc_status }}</span></td>
                        <td class="stack-actions text-end">
                            <form action="{{ route('compliance.verify_owner', $own) }}" method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" name="status" value="Verified">
                                <button type="submit" class="btn btn-sm btn-success"><i class="bi bi-check-circle me-1"></i>{{ __('ops.verify') }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="stack-no-label">
                            <div class="empty-state py-4">
                                <div class="empty-icon"><i class="bi bi-house-check"></i></div>
                                <h6>{{ __('ops.kyc_owners_up_to_date') }}</h6>
                                <p class="small mb-0">{{ __('ops.kyc_queue_empty_hint') }}</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
