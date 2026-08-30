@extends('layouts.app')

@section('title', 'Deal ' . $deal->deal_number)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <h3 class="brand-font mb-0">Sales Deal: {{ $deal->deal_number }}</h3>
            <span class="badge badge-status-{{ strtolower($deal->status) }}">{{ $deal->status }}</span>
        </div>
        <p class="text-muted small mb-0">Agreement Date: {{ $deal->agreement_date->format('d M Y') }} &bull; Total Value: <strong>{{ format_currency($deal->sale_price) }}</strong></p>
    </div>
    <a href="{{ route('deals.index') }}" class="btn btn-light border btn-sm"><i class="bi bi-arrow-left me-1"></i> Back to Deals</a>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <!-- Installment Milestone Schedule -->
        <div class="card mb-4">
            <div class="card-header brand-font d-flex justify-content-between align-items-center">
                <span>Installment Milestones & Payment Schedule</span>
                <span class="badge bg-primary-subtle text-primary">{{ $deal->installments->count() }} Milestones</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 0.875rem;">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Milestone / Title</th>
                            <th>Due Date</th>
                            <th>Amount Due</th>
                            <th>Paid Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($deal->installments as $ins)
                            <tr>
                                <td class="fw-bold">{{ $ins->installment_number }}</td>
                                <td>{{ $ins->title }}</td>
                                <td>{{ $ins->due_date->format('d M Y') }}</td>
                                <td class="fw-bold">{{ format_currency($ins->amount) }}</td>
                                <td class="text-success fw-bold">{{ format_currency($ins->paid_amount) }}</td>
                                <td><span class="badge badge-status-{{ strtolower($ins->status) }}">{{ $ins->status }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-3">No installments defined (Outright purchase).</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Invoices for this deal -->
        <div class="card">
            <div class="card-header brand-font">Invoices Issued</div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 0.875rem;">
                    <thead class="table-light">
                        <tr>
                            <th>Invoice #</th>
                            <th>Issue Date</th>
                            <th>Total</th>
                            <th>Balance</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($deal->invoices as $inv)
                            <tr>
                                <td class="fw-bold text-primary">{{ $inv->invoice_number }}</td>
                                <td>{{ $inv->issue_date->format('d M Y') }}</td>
                                <td class="fw-bold">{{ format_currency($inv->total_amount) }}</td>
                                <td class="text-danger fw-bold">{{ format_currency($inv->balance_due) }}</td>
                                <td><span class="badge badge-status-{{ strtolower($inv->status) }}">{{ $inv->status }}</span></td>
                                <td class="text-end">
                                    <a href="{{ route('finance.invoices.show', $inv) }}" class="btn btn-sm btn-light border"><i class="bi bi-receipt"></i></a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-3">No invoices generated for this deal.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Property and Customer Info -->
        <div class="card mb-4">
            <div class="card-header brand-font">Deal Details</div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="text-muted small">Property</label>
                    <div class="fw-bold">{{ $deal->property?->title }}</div>
                    <small class="text-muted">{{ $deal->property?->city }}, {{ $deal->property?->property_code }}</small>
                </div>
                <div class="mb-3">
                    <label class="text-muted small">Buyer / Client</label>
                    <div class="fw-bold">{{ $deal->customer?->full_name }}</div>
                    <small class="text-muted">{{ $deal->customer?->phone }}</small>
                </div>
                <div class="mb-3">
                    <label class="text-muted small">Closing Agent</label>
                    <div class="fw-bold">{{ $deal->agent?->user?->name ?? 'Direct' }}</div>
                    <div class="badge bg-light text-dark border">Commission: {{ format_currency($deal->commission_amount) }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
