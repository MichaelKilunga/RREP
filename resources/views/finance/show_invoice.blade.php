@extends('layouts.app')

@section('title', 'Invoice ' . $invoice->invoice_number)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 d-print-none">
    <div>
        <h3 class="brand-font mb-0">Invoice: {{ $invoice->invoice_number }}</h3>
        <span class="badge badge-status-{{ strtolower(str_replace(' ', '', $invoice->status)) }}">{{ $invoice->status }}</span>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('finance.invoices') }}" class="btn btn-light border btn-sm"><i class="bi bi-arrow-left me-1"></i> Back</a>
        <button onclick="window.print()" class="btn btn-dark btn-sm"><i class="bi bi-printer me-1"></i> Print / PDF</button>
    </div>
</div>

<div class="card p-4 p-md-5 bg-white mx-auto shadow-sm" style="max-width: 850px;">
    <!-- Invoice Header -->
    <div class="d-flex justify-content-between align-items-start border-bottom pb-4 mb-4">
        <div>
            <h4 class="brand-font text-primary mb-1">{{ current_organization()?->name ?? 'RehoSpace Real Estate' }}</h4>
            <div class="text-muted small">{{ current_organization()?->address }}</div>
            <div class="text-muted small">{{ current_organization()?->city }}, {{ current_organization()?->country }}</div>
            <div class="text-muted small">TIN: {{ current_organization()?->tax_number ?? 'TIN-100-992-881' }}</div>
        </div>
        <div class="text-end">
            <h3 class="brand-font text-dark mb-1">TAX INVOICE</h3>
            <div class="fw-bold text-primary fs-5">{{ $invoice->invoice_number }}</div>
            <div class="small text-muted">Date: {{ $invoice->issue_date->format('d M Y') }}</div>
            <div class="small text-muted">Due: {{ $invoice->due_date->format('d M Y') }}</div>
        </div>
    </div>

    <!-- Bill To -->
    <div class="row mb-4">
        <div class="col-6">
            <div class="text-muted small text-uppercase fw-bold mb-1">Billed To:</div>
            <div class="fw-bold fs-6">{{ $invoice->customer?->full_name }}</div>
            @if($invoice->customer?->company_name)<div class="small text-muted">{{ $invoice->customer?->company_name }}</div>@endif
            <div class="small text-muted">{{ $invoice->customer?->phone }}</div>
            @if($invoice->customer?->email)<div class="small text-muted">{{ $invoice->customer?->email }}</div>@endif
        </div>
        @if($invoice->property)
            <div class="col-6 text-end">
                <div class="text-muted small text-uppercase fw-bold mb-1">Property Reference:</div>
                <div class="fw-semibold">{{ $invoice->property->title }}</div>
                <div class="small text-muted">Code: {{ $invoice->property->property_code }}</div>
            </div>
        @endif
    </div>

    <!-- Items Table -->
    <div class="table-responsive mb-4">
        <table class="table table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th>Item & Description</th>
                    <th class="text-center" style="width: 80px;">Qty</th>
                    <th class="text-end" style="width: 180px;">Unit Price</th>
                    <th class="text-end" style="width: 180px;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                    <tr>
                        <td>{{ $item->description }}</td>
                        <td class="text-center">{{ (int)$item->quantity }}</td>
                        <td class="text-end">{{ format_currency($item->unit_price, $invoice->currency) }}</td>
                        <td class="text-end fw-bold">{{ format_currency($item->total_amount, $invoice->currency) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="3" class="text-end">Subtotal:</th>
                    <th class="text-end">{{ format_currency($invoice->subtotal, $invoice->currency) }}</th>
                </tr>
                @if($invoice->tax_amount > 0)
                    <tr>
                        <th colspan="3" class="text-end">VAT (18%):</th>
                        <th class="text-end">{{ format_currency($invoice->tax_amount, $invoice->currency) }}</th>
                    </tr>
                @endif
                <tr class="table-light">
                    <th colspan="3" class="text-end fs-5">Total Amount:</th>
                    <th class="text-end fs-5 text-primary">{{ format_currency($invoice->total_amount, $invoice->currency) }}</th>
                </tr>
                <tr>
                    <th colspan="3" class="text-end text-success">Paid Amount:</th>
                    <th class="text-end text-success fw-bold">{{ format_currency($invoice->paid_amount, $invoice->currency) }}</th>
                </tr>
                <tr class="table-danger">
                    <th colspan="3" class="text-end text-danger fs-5">Balance Due:</th>
                    <th class="text-end text-danger fs-5 fw-bold">{{ format_currency($invoice->balance_due, $invoice->currency) }}</th>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Payment Receipts section -->
    @if($invoice->payments->count() > 0)
        <div class="mb-4">
            <h6 class="brand-font mb-2">Recorded Payment Receipts</h6>
            <ul class="list-group list-group-flush border rounded">
                @foreach($invoice->payments as $pay)
                    <li class="list-group-item d-flex justify-content-between align-items-center small">
                        <div>
                            <strong>{{ $pay->payment_number }}</strong> &bull; {{ $pay->payment_date->format('d M Y') }} &bull; {{ $pay->payment_method }}
                            @if($pay->reference_number)<span class="text-muted">({{ $pay->reference_number }})</span>@endif
                        </div>
                        <span class="badge bg-success-subtle text-success">{{ format_currency($pay->amount, $pay->currency) }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="border-top pt-4 text-center text-muted small">
        <p class="mb-1">Thank you for your business with {{ current_organization()?->name }}.</p>
        <p class="mb-0">Bank: CRDB Bank Plc &bull; Account: 0150299887700 &bull; Swift: CORUTZTZ</p>
    </div>
</div>
@endsection
