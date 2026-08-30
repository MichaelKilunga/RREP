@extends('layouts.app')

@section('title', __('app.reservations'))

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h3 class="brand-font mb-1">{{ __('app.reservations') }} & Holds</h3>
        <p class="text-muted small mb-0">Manage property reservation fees, holding validity, and conversions</p>
    </div>
    <button class="btn btn-primary btn-sm d-flex align-items-center gap-1 shadow-sm" data-bs-toggle="modal" data-bs-target="#newReservationModal">
        <i class="bi bi-bookmark-plus-fill"></i> New Reservation Hold
    </button>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: 0.875rem;">
            <thead class="table-light">
                <tr>
                    <th>Resv #</th>
                    <th>Property</th>
                    <th>Customer</th>
                    <th>Deposit Paid</th>
                    <th>Validity Period</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reservations as $res)
                    <tr>
                        <td class="fw-bold text-primary">{{ $res->reservation_number }}</td>
                        <td>
                            <div class="fw-semibold">{{ $res->property?->title }}</div>
                            <small class="text-muted">{{ $res->property?->property_code }}</small>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $res->customer?->full_name }}</div>
                            <small class="text-muted">{{ $res->customer?->phone }}</small>
                        </td>
                        <td class="fw-bold text-success">{{ format_currency($res->deposit_paid) }}</td>
                        <td>
                            <div>{{ $res->reserved_from->format('d M Y') }} &rarr; {{ $res->reserved_until->format('d M Y') }}</div>
                            @if($res->status === 'Active')
                                <span class="badge bg-warning-subtle text-warning-emphasis">
                                    {{ max(0, now()->diffInDays($res->reserved_until, false)) }} days remaining
                                </span>
                            @endif
                        </td>
                        <td><span class="badge badge-status-{{ strtolower($res->status) }}">{{ $res->status }}</span></td>
                        <td class="text-end">
                            @if($res->status === 'Active')
                                <form action="{{ route('reservations.cancel', $res) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Cancel this reservation hold?')">Cancel</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No active reservation holds.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3 border-top d-flex justify-content-end">
        {{ $reservations->links() }}
    </div>
</div>

<!-- New Reservation Modal -->
<div class="modal fade" id="newReservationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title brand-font">Place Property Reservation Hold</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('reservations.store') }}" method="POST">
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
                        <label class="form-label small fw-semibold">Customer / Buyer *</label>
                        <select name="customer_id" class="form-select select2" required style="width: 100%;">
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->full_name }} ({{ $c->phone }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Reservation Fee ({{ current_currency() }}) *</label>
                            <input type="number" step="0.01" name="reservation_fee" class="form-control" value="5000000" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Deposit Paid *</label>
                            <input type="number" step="0.01" name="deposit_paid" class="form-control" value="5000000" required>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Hold Start Date *</label>
                            <input type="date" name="reserved_from" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Hold Expiry Date *</label>
                            <input type="date" name="reserved_until" class="form-control" value="{{ date('Y-m-d', strtotime('+14 days')) }}" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Assigned Sales Agent</label>
                        <select name="agent_id" class="form-select">
                            <option value="">Unassigned</option>
                            @foreach($agents as $ag)
                                <option value="{{ $ag->id }}">{{ $ag->user?->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Reservation</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
