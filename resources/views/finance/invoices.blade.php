@extends('layouts.app')

@section('title', __('app.invoices'))

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h3 class="brand-font mb-1">{{ __('app.invoices') }} & Receivables</h3>
        <p class="text-muted small mb-0">Manage billing, installments, tax invoices, and payment receipts</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#recordPaymentModal">
            <i class="bi bi-cash-coin me-1"></i> Record Payment
        </button>
        <button class="btn btn-primary btn-sm d-flex align-items-center gap-1 shadow-sm" data-bs-toggle="modal" data-bs-target="#newInvoiceModal">
            <i class="bi bi-plus-lg"></i> Create Invoice
        </button>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: 0.875rem;">
            <thead class="table-light">
                <tr>
                    <th>Invoice #</th>
                    <th>Customer</th>
                    <th>Property / Deal</th>
                    <th>Issue Date</th>
                    <th>Due Date</th>
                    <th>Total</th>
                    <th>Balance Due</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $inv)
                    <tr>
                        <td class="fw-bold text-primary">{{ $inv->invoice_number }}</td>
                        <td>
                            <div class="fw-semibold">{{ $inv->customer?->full_name }}</div>
                            <small class="text-muted">{{ $inv->customer?->phone }}</small>
                        </td>
                        <td>{{ $inv->property?->title ?? ($inv->salesDeal ? 'Deal #' . $inv->salesDeal->deal_number : 'General Billing') }}</td>
                        <td>{{ $inv->issue_date->format('d M Y') }}</td>
                        <td>{{ $inv->due_date->format('d M Y') }}</td>
                        <td class="fw-bold">{{ format_currency($inv->total_amount, $inv->currency) }}</td>
                        <td class="fw-bold text-danger">{{ format_currency($inv->balance_due, $inv->currency) }}</td>
                        <td><span class="badge badge-status-{{ strtolower(str_replace(' ', '', $inv->status)) }}">{{ $inv->status }}</span></td>
                        <td class="text-end">
                            <a href="{{ route('finance.invoices.show', $inv) }}" class="btn btn-light border btn-sm" title="View & Print"><i class="bi bi-printer"></i></a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center text-muted py-4">No invoices generated yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3 border-top d-flex justify-content-end">
        {{ $invoices->links() }}
    </div>
</div>

<!-- New Invoice Modal -->
<div class="modal fade" id="newInvoiceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title brand-font">Generate Tax Invoice</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('finance.invoices.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Customer *</label>
                            <select name="customer_id" class="form-select select2" required style="width: 100%;">
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}">{{ $c->full_name }} ({{ $c->phone }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Linked Property (Optional)</label>
                            <select name="property_id" class="form-select select2" style="width: 100%;">
                                <option value="">None</option>
                                @foreach($properties as $pr)
                                    <option value="{{ $pr->id }}">{{ $pr->title }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Issue Date *</label>
                            <input type="date" name="issue_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Due Date *</label>
                            <input type="date" name="due_date" class="form-control" value="{{ date('Y-m-d', strtotime('+14 days')) }}" required>
                        </div>
                    </div>

                    <h6 class="brand-font mb-2">Invoice Line Item</h6>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <input type="text" name="items[0][description]" class="form-control" placeholder="Description (e.g. 50% Down Payment)" required>
                        </div>
                        <div class="col-md-2">
                            <input type="number" name="items[0][quantity]" class="form-control" value="1" min="1" required>
                        </div>
                        <div class="col-md-4">
                            <input type="number" step="0.01" name="items[0][unit_price]" class="form-control" placeholder="Unit Price" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Generate Invoice</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Record Payment Modal -->
<div class="modal fade" id="recordPaymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title brand-font">Record Payment Receipt</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('finance.payments.record') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Customer *</label>
                        <select name="customer_id" class="form-select select2" required style="width: 100%;">
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->full_name }} ({{ $c->phone }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Invoice (Optional)</label>
                        <select name="invoice_id" class="form-select select2" style="width: 100%;">
                            <option value="">No specific invoice</option>
                            @foreach($invoices as $in)
                                <option value="{{ $in->id }}">{{ $in->invoice_number }} - Balance: {{ format_currency($in->balance_due) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Amount ({{ current_currency() }}) *</label>
                            <input type="number" step="0.01" name="amount" class="form-control fw-bold" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Payment Date *</label>
                            <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Payment Method</label>
                            <select name="payment_method" class="form-select">
                                <option value="Bank Transfer">Bank Transfer (EFT/RTGS)</option>
                                <option value="Mobile Money">Mobile Money (M-Pesa, TigoPesa, Airtel)</option>
                                <option value="Cheque">Cheque</option>
                                <option value="Cash">Cash</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Reference / Txn #</label>
                            <input type="text" name="reference_number" class="form-control" placeholder="CRDB-EFT-9912004">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Record Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
