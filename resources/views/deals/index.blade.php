@extends('layouts.app')

@section('title', __('app.deals'))

@section('content')
@php
    $dealBadge = ['Draft' => 'inactive', 'Pending Approval' => 'pending', 'Active' => 'active', 'Completed' => 'paid', 'Defaulted' => 'overdue', 'Terminated' => 'cancelled'];
@endphp

<div class="page-header">
    <div class="min-w-0">
        <h1 class="brand-font mb-1">{{ __('ops.deals_title') }}</h1>
        <p class="text-muted small mb-0">{{ __('ops.deals_subtitle') }}</p>
    </div>
    <div class="page-actions">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newDealModal">
            <i class="bi bi-plus-lg me-1"></i> {{ __('ops.new_deal') }}
        </button>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 table-stack small">
            <thead class="table-light">
                <tr>
                    <th>{{ __('ops.deal_no') }}</th>
                    <th>{{ __('ops.property') }}</th>
                    <th>{{ __('ops.buyer') }}</th>
                    <th>{{ __('ops.contract_price') }}</th>
                    <th>{{ __('ops.payment_plan') }}</th>
                    <th>{{ __('ops.commission') }}</th>
                    <th>{{ __('app.status') }}</th>
                    <th class="text-end">{{ __('app.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($deals as $d)
                    <tr>
                        <td class="stack-primary"><a href="{{ route('deals.show', $d) }}" class="fw-bold text-primary text-decoration-none">{{ $d->deal_number }}</a></td>
                        <td>{{ $d->property?->title }}</td>
                        <td>{{ $d->customer?->full_name }}</td>
                        <td class="fw-bold text-success">{{ format_currency($d->sale_price) }}</td>
                        <td><span class="badge bg-info-subtle text-info">{{ __('ops.plan_'.strtolower($d->payment_plan_type)) }} ({{ $d->total_installments }}x)</span></td>
                        <td>{{ format_currency($d->commission_amount) }} <small class="text-muted">({{ $d->commission_rate }}%)</small></td>
                        <td><span class="badge badge-status-{{ $dealBadge[$d->status] ?? strtolower(str_replace(' ', '', $d->status)) }}">{{ $d->status }}</span></td>
                        <td class="stack-actions text-end">
                            <a href="{{ route('deals.show', $d) }}" class="btn btn-light border btn-sm"><i class="bi bi-eye me-1"></i>{{ __('ops.view_contract') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="stack-no-label">
                            <div class="empty-state">
                                <div class="empty-icon"><i class="bi bi-file-earmark-text"></i></div>
                                <h6>{{ __('ops.no_deals') }}</h6>
                                <p class="small mb-3">{{ __('ops.no_deals_hint') }}</p>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newDealModal"><i class="bi bi-plus-lg me-1"></i> {{ __('ops.new_deal') }}</button>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($deals->hasPages())
        <div class="p-3 border-top d-flex justify-content-center justify-content-md-end">
            {{ $deals->links() }}
        </div>
    @endif
</div>

<!-- New Deal Modal -->
<div class="modal fade" id="newDealModal" tabindex="-1" aria-labelledby="newDealModalLabel">
    <div class="modal-dialog modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title brand-font" id="newDealModalLabel">{{ __('ops.new_deal_title') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('app.cancel') }}"></button>
            </div>
            <form action="{{ route('deals.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="dealProperty" class="form-label small fw-semibold">{{ __('ops.property') }} *</label>
                        <select id="dealProperty" name="property_id" class="form-select select2 w-100" required>
                            @foreach($properties as $pr)
                                <option value="{{ $pr->id }}">{{ $pr->title }} ({{ format_money_short($pr->price) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="dealCustomer" class="form-label small fw-semibold">{{ __('ops.customer_buyer') }} *</label>
                        <select id="dealCustomer" name="customer_id" class="form-select select2 w-100" required>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->full_name }} ({{ format_phone_tz($c->phone) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-12 col-sm-6">
                            <label for="dealPrice" class="form-label small fw-semibold">{{ __('ops.agreed_sale_price') }} *</label>
                            <div class="input-group">
                                <span class="input-group-text">TSh</span>
                                <input type="number" step="0.01" min="0" inputmode="decimal" id="dealPrice" name="sale_price" class="form-control fw-bold" placeholder="1250000000" required>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label for="dealPlan" class="form-label small fw-semibold">{{ __('ops.payment_plan') }} *</label>
                            <select id="dealPlan" name="payment_plan_type" class="form-select">
                                <option value="Outright">{{ __('ops.plan_outright_full') }}</option>
                                <option value="Installment" selected>{{ __('ops.plan_installment_full') }}</option>
                                <option value="Mortgage">{{ __('ops.plan_mortgage_full') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-12 col-sm-6">
                            <label for="dealInstallments" class="form-label small fw-semibold">{{ __('ops.number_of_installments') }}</label>
                            <input type="number" id="dealInstallments" name="total_installments" class="form-control" value="3" min="1" inputmode="numeric">
                            <div class="form-text">{{ __('ops.installments_hint') }}</div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label for="dealDate" class="form-label small fw-semibold">{{ __('ops.agreement_date') }} *</label>
                            <input type="date" id="dealDate" name="agreement_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="dealAgent" class="form-label small fw-semibold">{{ __('ops.closing_agent') }}</label>
                        <select id="dealAgent" name="agent_id" class="form-select">
                            @foreach($agents as $ag)
                                <option value="{{ $ag->id }}">{{ $ag->user?->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">{{ __('app.cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('ops.initiate_contract') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
