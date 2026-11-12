@extends('layouts.app')

@section('title', __('app.invoices'))

@section('content')
@php
    $statusTone = ['Paid' => 'success', 'Partially Paid' => 'info', 'Issued' => 'primary', 'Overdue' => 'danger', 'Cancelled' => 'secondary', 'Draft' => 'light'];
    $canVoid = auth()->user()->can('void_invoices');
    $canRemind = auth()->user()->can('send_sms');
    $canBulk = $canVoid || $canRemind;
@endphp

<div class="page-header">
    <div class="min-w-0">
        <h1 class="brand-font mb-1">{{ __('ops.invoices_title') }}</h1>
        <p class="text-muted small mb-0">{{ __('ops.invoices_subtitle') }}</p>
    </div>
    <div class="page-actions">
        @can('view_financial_reports')<a href="{{ route('finance.overview') }}" class="btn btn-light border"><i class="bi bi-graph-up me-1"></i>{{ __('ops.finance_overview') }}</a>@endcan
        <a href="{{ route('finance.payments') }}" class="btn btn-light border"><i class="bi bi-cash-stack me-1"></i>{{ __('ops.payments_title') }}</a>
        @can('record_payments')
            <button class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#recordPaymentModal">
                <i class="bi bi-cash-coin me-1"></i> {{ __('ops.record_payment') }}
            </button>
        @endcan
        @can('create_invoices')
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newInvoiceModal">
                <i class="bi bi-plus-lg me-1"></i> {{ __('ops.create_invoice') }}
            </button>
        @endcan
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-6 col-md-3"><div class="kpi-card"><div class="kpi-label">{{ __('ops.outstanding') }}</div><div class="kpi-value" title="{{ format_currency($stats['outstanding'], null, 0) }}">{{ format_money_short($stats['outstanding']) }}</div><span class="kpi-icon"><i class="bi bi-hourglass-split"></i></span></div></div>
    <div class="col-6 col-md-3"><div class="kpi-card kpi-danger"><div class="kpi-label">{{ __('ops.overdue') }}</div><div class="kpi-value text-danger" title="{{ format_currency($stats['overdue'], null, 0) }}">{{ format_money_short($stats['overdue']) }}</div></div></div>
    <div class="col-6 col-md-3"><div class="kpi-card kpi-success"><div class="kpi-label">{{ __('ops.collected_month') }}</div><div class="kpi-value text-success" title="{{ format_currency($stats['paid_month'], null, 0) }}">{{ format_money_short($stats['paid_month']) }}</div></div></div>
    <div class="col-6 col-md-3"><div class="kpi-card kpi-info"><div class="kpi-label">{{ __('ops.open_invoices') }}</div><div class="kpi-value">{{ $stats['open_count'] }}</div></div></div>
</div>

<form method="GET" action="{{ route('finance.invoices') }}" class="card p-2 p-md-3 mb-3">
    <div class="filter-bar">
        <div class="flex-grow-1">
            <div class="input-group input-group-sm">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="search" name="q" class="form-control" value="{{ request('q') }}" placeholder="{{ __('ops.invoice_search_placeholder') }}" aria-label="{{ __('app.search') }}">
            </div>
        </div>
        <div>
            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()" aria-label="{{ __('app.status') }}">
                <option value="">{{ __('ops.all_statuses') }}</option>
                <option value="open" @selected(request('status') === 'open')>{{ __('ops.status_open') }}</option>
                <option value="overdue" @selected(request('status') === 'overdue')>{{ __('ops.overdue') }}</option>
                @foreach(['Issued', 'Partially Paid', 'Paid', 'Cancelled'] as $st)
                    <option value="{{ $st }}" @selected(request('status') === $st)>{{ __('ops.invoice_status_'.Str::slug($st, '_')) }}</option>
                @endforeach
            </select>
        </div>
        <div><input type="date" name="from" class="form-control form-control-sm" value="{{ request('from') }}" aria-label="{{ __('ops.date_from') }}" title="{{ __('ops.date_from') }}"></div>
        <div><input type="date" name="to" class="form-control form-control-sm" value="{{ request('to') }}" aria-label="{{ __('ops.date_to') }}" title="{{ __('ops.date_to') }}"></div>
        <div class="filter-actions">
            <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-search"></i></button>
            <a href="{{ route('finance.invoices') }}" class="btn btn-sm btn-light border">{{ __('ops.reset') }}</a>
        </div>
    </div>
</form>

<div class="card">
    @if($canBulk && $invoices->count() > 0)
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 py-2">
            <span class="small text-muted">{{ __('ops.bulk_select_hint') }}</span>
            <button type="button" class="btn btn-sm btn-light border" data-bulk-all="invoices" aria-pressed="false" data-label-all="{{ __('admin.select_all') }}" data-label-none="{{ __('admin.deselect_all') }}">
                <i class="bi bi-check2-square me-1"></i><span data-bulk-all-label>{{ __('admin.select_all') }} ({{ $invoices->count() }})</span>
            </button>
        </div>
    @endif
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 table-stack small">
            <thead class="table-light">
                <tr>
                    @if($canBulk)<th class="stack-check" style="width: 2.5rem;"><input type="checkbox" class="form-check-input" data-bulk-all="invoices" aria-label="{{ __('admin.select_all') }}"></th>@endif
                    <th>{{ __('ops.invoice_no') }}</th>
                    <th>{{ __('ops.customer') }}</th>
                    <th>{{ __('ops.issue_date') }}</th>
                    <th>{{ __('ops.due_date') }}</th>
                    <th class="text-end">{{ __('app.total') }}</th>
                    <th class="text-end">{{ __('ops.balance_due') }}</th>
                    <th>{{ __('app.status') }}</th>
                    <th class="text-end">{{ __('app.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $inv)
                    @php $isOverdue = (float) $inv->balance_due > 0 && ! in_array($inv->status, ['Paid', 'Cancelled'], true) && $inv->due_date?->isPast() && ! $inv->due_date?->isToday(); @endphp
                    <tr>
                        @if($canBulk)<td class="stack-check"><input type="checkbox" class="form-check-input" value="{{ $inv->id }}" data-bulk-item="invoices" aria-label="{{ $inv->invoice_number }}"></td>@endif
                        <td class="stack-primary">
                            <a href="{{ route('finance.invoices.show', $inv) }}" class="fw-bold text-primary text-decoration-none">{{ $inv->invoice_number }}</a>
                            <div class="text-muted">{{ $inv->property?->title ?? ($inv->salesDeal ? __('ops.deal').' '.$inv->salesDeal->deal_number : __('ops.general_billing')) }}</div>
                        </td>
                        <td>
                            <div class="fw-semibold">
                                @if($inv->customer && auth()->user()->can('view_customers'))
                                    <a href="{{ route('crm.customers.show', $inv->customer) }}" class="text-dark">{{ $inv->customer->full_name }}</a>
                                @else
                                    {{ $inv->customer?->full_name }}
                                @endif
                            </div>
                            @if($inv->customer?->phone)<small class="text-muted">{{ format_phone_tz($inv->customer->phone) }}</small>@endif
                        </td>
                        <td>{{ $inv->issue_date?->format('d M Y') }}</td>
                        <td @class(['text-danger fw-semibold' => $isOverdue])>{{ $inv->due_date?->format('d M Y') }}</td>
                        <td class="fw-bold text-end">{{ format_currency($inv->total_amount, $inv->currency, 0) }}</td>
                        <td class="fw-bold text-end {{ (float) $inv->balance_due > 0 ? 'text-danger' : 'text-success' }}">{{ format_currency($inv->balance_due, $inv->currency, 0) }}</td>
                        <td>
                            <span class="badge bg-{{ $isOverdue ? 'danger' : ($statusTone[$inv->status] ?? 'secondary') }}">{{ $isOverdue ? __('ops.overdue') : __('ops.invoice_status_'.Str::slug($inv->status, '_')) }}</span>
                        </td>
                        <td class="stack-actions text-end">
                            <div class="d-inline-flex gap-1">
                                <a href="{{ route('finance.invoices.show', $inv) }}" class="btn btn-light border btn-sm" title="{{ __('ops.view_print') }}"><i class="bi bi-eye"></i></a>
                                @if(! in_array($inv->status, ['Paid', 'Cancelled'], true))
                                    @can('create_invoices')<a href="{{ route('finance.invoices.edit', $inv) }}" class="btn btn-light border btn-sm" title="{{ __('app.edit') }}"><i class="bi bi-pencil text-primary"></i></a>@endcan
                                    @can('record_payments')
                                        <button type="button" class="btn btn-light border btn-sm inv-pay-btn" title="{{ __('ops.record_payment') }}" data-customer="{{ $inv->customer_id }}" data-invoice="{{ $inv->id }}" data-balance="{{ (float) $inv->balance_due }}"><i class="bi bi-cash-coin text-success"></i></button>
                                    @endcan
                                    @can('send_sms')
                                        <form action="{{ route('finance.invoices.remind', $inv) }}" method="POST" class="m-0">@csrf
                                            <button type="submit" class="btn btn-light border btn-sm" title="{{ __('ops.send_reminder') }}"><i class="bi bi-bell text-warning"></i></button>
                                        </form>
                                    @endcan
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $canBulk ? 9 : 8 }}" class="stack-no-label">
                            <div class="empty-state">
                                <div class="empty-icon"><i class="bi bi-receipt"></i></div>
                                <h6>{{ __('ops.no_invoices') }}</h6>
                                <p class="small mb-0">{{ __('ops.no_invoices_hint') }}</p>
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

@if($canBulk)
<form action="{{ route('finance.invoices.bulk') }}" method="POST" data-bulk-form="invoices">
    @csrf
    <div class="bulk-bar flex-column d-none" data-bulk-bar="invoices">
        <div class="bulk-inner">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary rounded-pill fs-6" data-bulk-count="invoices">0</span>
                <span class="text-white small fw-semibold">{{ __('ops.invoices_selected') }}</span>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <button type="button" class="btn btn-sm btn-outline-light rounded-pill px-3" data-bulk-all="invoices" data-label-all="{{ __('admin.select_all') }}" data-label-none="{{ __('admin.deselect_all') }}">
                    <i class="bi bi-check2-all me-1"></i><span data-bulk-all-label>{{ __('admin.select_all') }}</span>
                </button>
                @if($canRemind)
                    <button type="submit" name="action" value="remind" class="btn btn-sm btn-light rounded-pill px-3" data-confirm="{{ __('ops.invoices_bulk_remind_confirm') }}"><i class="bi bi-bell me-1"></i>{{ __('ops.send_reminder') }}</button>
                @endif
                @if($canVoid)
                    <button type="submit" name="action" value="cancel" class="btn btn-sm btn-warning rounded-pill px-3" data-confirm="{{ __('ops.invoices_bulk_cancel_confirm') }}"><i class="bi bi-x-circle me-1"></i>{{ __('ops.cancel_invoice') }}</button>
                    <button type="submit" name="action" value="delete" class="btn btn-sm btn-danger rounded-pill px-3" data-confirm="{{ __('ops.invoices_bulk_delete_confirm') }}"><i class="bi bi-trash me-1"></i>{{ __('app.delete') }}</button>
                @endif
                <button type="button" class="btn btn-sm btn-link text-white-50 text-decoration-none touch-target p-0" data-bulk-clear="invoices" title="{{ __('admin.deselect_all') }}" aria-label="{{ __('admin.deselect_all') }}"><i class="bi bi-x-lg"></i></button>
            </div>
        </div>
    </div>
</form>
@endif

@can('create_invoices')
<!-- New Invoice Modal -->
<div class="modal fade" id="newInvoiceModal" tabindex="-1" aria-labelledby="newInvoiceModalLabel">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-fullscreen-sm-down">
        <form class="modal-content inv-form" action="{{ route('finance.invoices.store') }}" method="POST">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title brand-font" id="newInvoiceModalLabel">{{ __('ops.generate_invoice_title') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('app.cancel') }}"></button>
            </div>
            <div class="modal-body">
                @include('finance.partials.invoice-form', ['invoice' => null])
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">{{ __('app.cancel') }}</button>
                <button type="submit" class="btn btn-primary">{{ __('ops.generate_invoice') }}</button>
            </div>
        </form>
    </div>
</div>
@endcan

@can('record_payments')
<!-- Record Payment Modal -->
<div class="modal fade" id="recordPaymentModal" tabindex="-1" aria-labelledby="recordPaymentModalLabel">
    <div class="modal-dialog modal-dialog-scrollable modal-fullscreen-sm-down">
        <form class="modal-content" action="{{ route('finance.payments.record') }}" method="POST" id="recordPaymentForm">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title brand-font" id="recordPaymentModalLabel">{{ __('ops.record_payment_title') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('app.cancel') }}"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="payCustomer" class="form-label small fw-semibold">{{ __('ops.customer') }} *</label>
                    <select id="payCustomer" name="customer_id" class="form-select w-100" required>
                        <option value="">{{ __('ops.select_customer') }}</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}">{{ $c->first_name }} {{ $c->last_name }} ({{ format_phone_tz($c->phone) }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label for="payInvoice" class="form-label small fw-semibold">{{ __('ops.invoice_optional') }}</label>
                    <select id="payInvoice" name="invoice_id" class="form-select w-100">
                        <option value="">{{ __('ops.no_specific_invoice') }}</option>
                    </select>
                    <div class="form-text" id="payInvoiceHint">{{ __('ops.pick_customer_first') }}</div>
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
                        <input type="date" id="payDate" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" required>
                    </div>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-12 col-sm-6">
                        <label for="payMethod" class="form-label small fw-semibold">{{ __('ops.payment_method') }}</label>
                        <select id="payMethod" name="payment_method" class="form-select">
                            @foreach(tz_payment_methods() as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-sm-6">
                        <label for="payReference" class="form-label small fw-semibold">{{ __('ops.reference_txn') }}</label>
                        <input type="text" id="payReference" name="reference_number" class="form-control" placeholder="{{ __('ops.reference_placeholder') }}" autocomplete="off">
                    </div>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="send_receipt" value="1" id="paySendReceipt" checked>
                    <label class="form-check-label small" for="paySendReceipt">{{ __('ops.send_sms_receipt') }}</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">{{ __('app.cancel') }}</button>
                <button type="submit" class="btn btn-success">{{ __('ops.record_payment') }}</button>
            </div>
        </form>
    </div>
</div>
@endcan
@endsection

@section('scripts')
@include('finance.partials.invoice-form-script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const openInvoices = {!! json_encode($openInvoices->map(fn ($i) => ['id' => $i->id, 'customer' => $i->customer_id, 'number' => $i->invoice_number, 'balance' => (float) $i->balance_due])->values()) !!};
    const customer = document.getElementById('payCustomer');
    const invoice = document.getElementById('payInvoice');
    const amount = document.getElementById('payAmount');
    const hint = document.getElementById('payInvoiceHint');
    if (!customer) { return; }
    const labels = {!! json_encode(['none' => __('ops.no_specific_invoice'), 'balance' => __('ops.balance'), 'noneOpen' => __('ops.customer_no_open_invoices')]) !!};

    function fillInvoices(preselect) {
        const list = openInvoices.filter(i => String(i.customer) === String(customer.value));
        invoice.innerHTML = '';
        invoice.add(new Option(labels.none, ''));
        list.forEach(i => invoice.add(new Option(i.number + ' — ' + labels.balance + ': TSh ' + Math.round(i.balance).toLocaleString('en-US'), i.id)));
        hint.textContent = customer.value && !list.length ? labels.noneOpen : '';
        if (preselect) { invoice.value = preselect; }
        syncAmount();
    }
    function syncAmount() {
        const picked = openInvoices.find(i => String(i.id) === String(invoice.value));
        if (picked) { amount.value = picked.balance; amount.max = picked.balance; } else { amount.removeAttribute('max'); }
    }
    customer.addEventListener('change', () => fillInvoices());
    invoice.addEventListener('change', syncAmount);

    document.querySelectorAll('.inv-pay-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            customer.value = btn.dataset.customer;
            fillInvoices(btn.dataset.invoice);
            bootstrap.Modal.getOrCreateInstance(document.getElementById('recordPaymentModal')).show();
        });
    });
});
</script>
@endsection
