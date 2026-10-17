@extends('layouts.app')

@section('title', __('app.invoices'))

@section('content')
<div class="page-header">
    <div class="min-w-0">
        <h1 class="brand-font mb-1">{{ __('ops.invoices_title') }}</h1>
        <p class="text-muted small mb-0">{{ __('ops.invoices_subtitle') }}</p>
    </div>
    <div class="page-actions">
        <button class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#recordPaymentModal">
            <i class="bi bi-cash-coin me-1"></i> {{ __('ops.record_payment') }}
        </button>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newInvoiceModal">
            <i class="bi bi-plus-lg me-1"></i> {{ __('ops.create_invoice') }}
        </button>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 table-stack small">
            <thead class="table-light">
                <tr>
                    <th>{{ __('ops.invoice_no') }}</th>
                    <th>{{ __('ops.customer') }}</th>
                    <th>{{ __('ops.property_deal') }}</th>
                    <th>{{ __('ops.issue_date') }}</th>
                    <th>{{ __('ops.due_date') }}</th>
                    <th>{{ __('app.total') }}</th>
                    <th>{{ __('ops.balance_due') }}</th>
                    <th>{{ __('app.status') }}</th>
                    <th class="text-end">{{ __('app.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $inv)
                    <tr>
                        <td class="stack-primary"><a href="{{ route('finance.invoices.show', $inv) }}" class="fw-bold text-primary text-decoration-none">{{ $inv->invoice_number }}</a></td>
                        <td>
                            <div class="fw-semibold">{{ $inv->customer?->full_name }}</div>
                            @if($inv->customer?->phone)<small class="text-muted">{{ format_phone_tz($inv->customer->phone) }}</small>@endif
                        </td>
                        <td>{{ $inv->property?->title ?? ($inv->salesDeal ? __('ops.deal').' '.$inv->salesDeal->deal_number : __('ops.general_billing')) }}</td>
                        <td>{{ $inv->issue_date->format('d M Y') }}</td>
                        <td @class(['text-danger fw-semibold' => $inv->balance_due > 0 && $inv->due_date->isPast()])>{{ $inv->due_date->format('d M Y') }}</td>
                        <td class="fw-bold">{{ format_currency($inv->total_amount, $inv->currency) }}</td>
                        <td class="fw-bold text-danger">{{ format_currency($inv->balance_due, $inv->currency) }}</td>
                        <td><span class="badge badge-status-{{ strtolower(str_replace(' ', '', $inv->status)) }}">{{ $inv->status }}</span></td>
                        <td class="stack-actions text-end">
                            <a href="{{ route('finance.invoices.show', $inv) }}" class="btn btn-light border btn-sm" title="{{ __('ops.view_print') }}"><i class="bi bi-printer me-1"></i>{{ __('ops.view_print') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="stack-no-label">
                            <div class="empty-state">
                                <div class="empty-icon"><i class="bi bi-receipt"></i></div>
                                <h6>{{ __('ops.no_invoices') }}</h6>
                                <p class="small mb-3">{{ __('ops.no_invoices_hint') }}</p>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newInvoiceModal"><i class="bi bi-plus-lg me-1"></i> {{ __('ops.create_invoice') }}</button>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($invoices->hasPages())
        <div class="p-3 border-top d-flex justify-content-center justify-content-md-end">
            {{ $invoices->links() }}
        </div>
    @endif
</div>

<!-- New Invoice Modal -->
<div class="modal fade" id="newInvoiceModal" tabindex="-1" aria-labelledby="newInvoiceModalLabel">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title brand-font" id="newInvoiceModalLabel">{{ __('ops.generate_invoice_title') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('app.cancel') }}"></button>
            </div>
            <form action="{{ route('finance.invoices.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row g-2 mb-3">
                        <div class="col-12 col-md-6">
                            <label for="invCustomer" class="form-label small fw-semibold">{{ __('ops.customer') }} *</label>
                            <select id="invCustomer" name="customer_id" class="form-select select2 w-100" required>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}">{{ $c->full_name }} ({{ format_phone_tz($c->phone) }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="invProperty" class="form-label small fw-semibold">{{ __('ops.linked_property') }}</label>
                            <select id="invProperty" name="property_id" class="form-select select2 w-100">
                                <option value="">{{ __('ops.none') }}</option>
                                @foreach($properties as $pr)
                                    <option value="{{ $pr->id }}">{{ $pr->title }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-12 col-sm-6">
                            <label for="invIssueDate" class="form-label small fw-semibold">{{ __('ops.issue_date') }} *</label>
                            <input type="date" id="invIssueDate" name="issue_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label for="invDueDate" class="form-label small fw-semibold">{{ __('ops.due_date') }} *</label>
                            <input type="date" id="invDueDate" name="due_date" class="form-control" value="{{ date('Y-m-d', strtotime('+14 days')) }}" required>
                        </div>
                    </div>

                    <h6 class="brand-font mb-2">{{ __('ops.invoice_line_item') }}</h6>
                    <div class="row g-2 mb-3">
                        <div class="col-12 col-md-6">
                            <label for="invItemDesc" class="form-label small fw-semibold">{{ __('app.description') }} *</label>
                            <input type="text" id="invItemDesc" name="items[0][description]" class="form-control" placeholder="{{ __('ops.item_description_placeholder') }}" required>
                        </div>
                        <div class="col-4 col-md-2">
                            <label for="invItemQty" class="form-label small fw-semibold">{{ __('ops.quantity') }} *</label>
                            <input type="number" id="invItemQty" name="items[0][quantity]" class="form-control" value="1" min="1" inputmode="numeric" required>
                        </div>
                        <div class="col-8 col-md-4">
                            <label for="invItemPrice" class="form-label small fw-semibold">{{ __('ops.unit_price') }} *</label>
                            <div class="input-group">
                                <span class="input-group-text">TSh</span>
                                <input type="number" step="0.01" min="0" inputmode="decimal" id="invItemPrice" name="items[0][unit_price]" class="form-control" placeholder="10000000" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">{{ __('app.cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('ops.generate_invoice') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Record Payment Modal -->
<div class="modal fade" id="recordPaymentModal" tabindex="-1" aria-labelledby="recordPaymentModalLabel">
    <div class="modal-dialog modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title brand-font" id="recordPaymentModalLabel">{{ __('ops.record_payment_title') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('app.cancel') }}"></button>
            </div>
            <form action="{{ route('finance.payments.record') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="payCustomer" class="form-label small fw-semibold">{{ __('ops.customer') }} *</label>
                        <select id="payCustomer" name="customer_id" class="form-select select2 w-100" required>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->full_name }} ({{ format_phone_tz($c->phone) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="payInvoice" class="form-label small fw-semibold">{{ __('ops.invoice_optional') }}</label>
                        <select id="payInvoice" name="invoice_id" class="form-select select2 w-100">
                            <option value="">{{ __('ops.no_specific_invoice') }}</option>
                            @foreach($invoices as $in)
                                <option value="{{ $in->id }}">{{ $in->invoice_number }} &mdash; {{ __('ops.balance') }}: {{ format_currency($in->balance_due) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-12 col-sm-6">
                            <label for="payAmount" class="form-label small fw-semibold">{{ __('ops.amount') }} *</label>
                            <div class="input-group">
                                <span class="input-group-text">TSh</span>
                                <input type="number" step="0.01" min="1" inputmode="decimal" id="payAmount" name="amount" class="form-control fw-bold" required>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label for="payDate" class="form-label small fw-semibold">{{ __('ops.payment_date') }} *</label>
                            <input type="date" id="payDate" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-12 col-sm-6">
                            <label for="payMethod" class="form-label small fw-semibold">{{ __('ops.payment_method') }}</label>
                            <select id="payMethod" name="payment_method" class="form-select">
                                @foreach(tz_payment_methods() as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                                <option value="Mobile Money">{{ __('ops.payment_mobile_money_other') }}</option>
                            </select>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label for="payReference" class="form-label small fw-semibold">{{ __('ops.reference_txn') }}</label>
                            <input type="text" id="payReference" name="reference_number" class="form-control" placeholder="{{ __('ops.reference_placeholder') }}" autocomplete="off">
                            <div class="form-text">{{ __('ops.reference_hint') }}</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">{{ __('app.cancel') }}</button>
                    <button type="submit" class="btn btn-success">{{ __('ops.record_payment') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
