@extends('layouts.app')

@section('title', __('app.deals'))

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h3 class="brand-font mb-1">{{ __('app.deals') }} & Contracts</h3>
        <p class="text-muted small mb-0">Sales contracts, payment plans, and installment schedules</p>
    </div>
    <button class="btn btn-primary btn-sm d-flex align-items-center gap-1 shadow-sm" data-bs-toggle="modal" data-bs-target="#newDealModal">
        <i class="bi bi-plus-lg"></i> New Sales Deal
    </button>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: 0.875rem;">
            <thead class="table-light">
                <tr>
                    <th>Deal #</th>
                    <th>Property</th>
                    <th>Buyer</th>
                    <th>Contract Price</th>
                    <th>Payment Plan</th>
                    <th>Commission</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($deals as $d)
                    <tr>
                        <td class="fw-bold text-primary">{{ $d->deal_number }}</td>
                        <td>{{ $d->property?->title }}</td>
                        <td>{{ $d->customer?->full_name }}</td>
                        <td class="fw-bold text-success">{{ format_currency($d->sale_price) }}</td>
                        <td><span class="badge bg-info-subtle text-info">{{ $d->payment_plan_type }} ({{ $d->total_installments }}x)</span></td>
                        <td>{{ format_currency($d->commission_amount) }} ({{ $d->commission_rate }}%)</td>
                        <td><span class="badge badge-status-{{ strtolower($d->status) }}">{{ $d->status }}</span></td>
                        <td class="text-end">
                            <a href="{{ route('deals.show', $d) }}" class="btn btn-light border btn-sm"><i class="bi bi-eye"></i> View Contract</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">No sales deals active.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3 border-top d-flex justify-content-end">
        {{ $deals->links() }}
    </div>
</div>

<!-- New Deal Modal -->
<div class="modal fade" id="newDealModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title brand-font">Execute New Sales Deal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('deals.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Property *</label>
                        <select name="property_id" class="form-select select2" required style="width: 100%;">
                            @foreach($properties as $pr)
                                <option value="{{ $pr->id }}">{{ $pr->title }} ({{ $pr->formatted_price }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Buyer / Customer *</label>
                        <select name="customer_id" class="form-select select2" required style="width: 100%;">
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->full_name }} ({{ $c->phone }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Agreed Sale Price *</label>
                            <input type="number" step="0.01" name="sale_price" class="form-control fw-bold" placeholder="1250000000" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Payment Plan Type *</label>
                            <select name="payment_plan_type" class="form-select">
                                <option value="Outright">Outright 100% Cash</option>
                                <option value="Installment" selected>Installment Schedule</option>
                                <option value="Mortgage">Bank Mortgage</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Number of Installments</label>
                            <input type="number" name="total_installments" class="form-control" value="3" min="1">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Agreement Date *</label>
                            <input type="date" name="agreement_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Closing Agent</label>
                        <select name="agent_id" class="form-select">
                            @foreach($agents as $ag)
                                <option value="{{ $ag->id }}">{{ $ag->user?->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Initiate Sales Contract</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
