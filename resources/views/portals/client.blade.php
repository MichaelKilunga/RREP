@extends('layouts.app')

@section('title', 'Client Self-Service Portal')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="brand-font mb-1"><i class="bi bi-person-workspace text-primary me-2"></i>Client Self-Service Portal (BM-013)</h3>
        <p class="text-muted small mb-0">Welcome, <strong>{{ $customer->full_name }}</strong> &bull; Direct access to holds, appointments, and billing receipts</p>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Active Reservations Card -->
    <div class="col-md-4">
        <div class="card p-3 h-100">
            <div class="text-muted small text-uppercase fw-bold">Active Property Holds</div>
            <h3 class="fw-bold mb-0 text-warning">{{ $reservations->count() }}</h3>
            <small class="text-muted">Guaranteed reservation holds</small>
        </div>
    </div>
    <!-- Invoices Card -->
    <div class="col-md-4">
        <div class="card p-3 h-100">
            <div class="text-muted small text-uppercase fw-bold">Billing Statements</div>
            <h3 class="fw-bold mb-0 text-primary">{{ $invoices->count() }}</h3>
            <small class="text-muted">Tax invoices & payment receipts</small>
        </div>
    </div>
    <!-- Viewing Tours Card -->
    <div class="col-md-4">
        <div class="card p-3 h-100">
            <div class="text-muted small text-uppercase fw-bold">Viewing Tours</div>
            <h3 class="fw-bold mb-0 text-success">{{ $appointments->count() }}</h3>
            <small class="text-muted">Scheduled site visits</small>
        </div>
    </div>
</div>

<!-- Reservations & Invoices lists -->
<div class="row g-4">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header brand-font">My Property Holds</div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                    <thead class="table-light">
                        <tr>
                            <th>Hold #</th>
                            <th>Property</th>
                            <th>Deposit</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reservations as $res)
                            <tr>
                                <td class="fw-bold text-primary">{{ $res->reservation_number }}</td>
                                <td>{{ $res->property?->title }}</td>
                                <td class="fw-bold text-success">{{ format_currency($res->deposit_paid) }}</td>
                                <td><span class="badge badge-status-{{ strtolower($res->status) }}">{{ $res->status }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-3">No active holds.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card">
            <div class="card-header brand-font">Invoices & Receipts</div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                    <thead class="table-light">
                        <tr>
                            <th>Invoice #</th>
                            <th>Total</th>
                            <th>Balance</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $inv)
                            <tr>
                                <td class="fw-bold text-primary">{{ $inv->invoice_number }}</td>
                                <td>{{ format_currency($inv->total_amount) }}</td>
                                <td class="text-danger fw-bold">{{ format_currency($inv->balance_due) }}</td>
                                <td><span class="badge badge-status-{{ strtolower(str_replace(' ', '', $inv->status)) }}">{{ $inv->status }}</span></td>
                                <td><a href="{{ route('finance.invoices.show', $inv) }}" class="btn btn-sm btn-light border"><i class="bi bi-printer"></i></a></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-3">No invoices issued.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
