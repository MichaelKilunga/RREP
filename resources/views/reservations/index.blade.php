@extends('layouts.app')

@section('title', __('app.reservations'))

@section('content')
@php
    $reservationBadge = ['Active' => 'active', 'Expired' => 'overdue', 'Converted' => 'paid', 'Cancelled' => 'cancelled'];
@endphp

<div class="page-header">
    <div class="min-w-0">
        <h1 class="brand-font mb-1">{{ __('ops.reservations_title') }}</h1>
        <p class="text-muted small mb-0">{{ __('ops.reservations_subtitle') }}</p>
    </div>
    <div class="page-actions">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newReservationModal">
            <i class="bi bi-bookmark-plus-fill me-1"></i> {{ __('ops.new_reservation') }}
        </button>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 table-stack small">
            <thead class="table-light">
                <tr>
                    <th>{{ __('ops.reservation_no') }}</th>
                    <th>{{ __('ops.property') }}</th>
                    <th>{{ __('ops.customer') }}</th>
                    <th>{{ __('ops.deposit_paid') }}</th>
                    <th>{{ __('ops.validity_period') }}</th>
                    <th>{{ __('app.status') }}</th>
                    <th class="text-end">{{ __('app.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reservations as $res)
                    <tr>
                        <td class="stack-primary">
                            <span class="fw-bold text-primary">{{ $res->reservation_number }}</span>
                            <span class="badge badge-status-{{ $reservationBadge[$res->status] ?? strtolower($res->status) }} ms-2 d-md-none">{{ __('ops.reservation_status_'.strtolower($res->status)) }}</span>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $res->property?->title }}</div>
                            <small class="text-muted">{{ $res->property?->property_code }}</small>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $res->customer?->full_name }}</div>
                            @if($res->customer?->phone)
                                <a href="tel:{{ $res->customer->phone }}" class="small text-muted text-decoration-none">{{ format_phone_tz($res->customer->phone) }}</a>
                            @endif
                        </td>
                        <td class="fw-bold text-success">
                            {{ format_currency($res->deposit_paid) }}
                            @if($res->property?->price > 0)
                                <small class="text-muted fw-normal d-block">{{ __('ops.deposit_of_price', ['percent' => number_format(($res->deposit_paid / $res->property->price) * 100, 1)]) }}</small>
                            @endif
                        </td>
                        <td>
                            <div>{{ $res->reserved_from->format('d M Y') }} &rarr; {{ $res->reserved_until->format('d M Y') }}</div>
                            @if($res->status === 'Active')
                                <span class="badge bg-warning-subtle text-warning-emphasis">{{ __('ops.days_remaining', ['days' => max(0, (int) now()->diffInDays($res->reserved_until, false))]) }}</span>
                            @endif
                        </td>
                        <td class="d-none d-md-table-cell"><span class="badge badge-status-{{ $reservationBadge[$res->status] ?? strtolower($res->status) }}">{{ __('ops.reservation_status_'.strtolower($res->status)) }}</span></td>
                        <td class="stack-actions text-end">
                            @if($res->status === 'Active')
                                <form action="{{ route('reservations.cancel', $res) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('ops.confirm_cancel_reservation') }}')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-x-circle me-1"></i>{{ __('ops.cancel_hold') }}</button>
                                </form>
                            @else
                                <span class="text-muted small">&mdash;</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="stack-no-label">
                            <div class="empty-state">
                                <div class="empty-icon"><i class="bi bi-bookmark"></i></div>
                                <h6>{{ __('ops.no_reservations') }}</h6>
                                <p class="small mb-3">{{ __('ops.no_reservations_hint') }}</p>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newReservationModal"><i class="bi bi-bookmark-plus-fill me-1"></i> {{ __('ops.new_reservation') }}</button>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($reservations->hasPages())
        <div class="p-3 border-top d-flex justify-content-center justify-content-md-end">
            {{ $reservations->links() }}
        </div>
    @endif
</div>

<!-- New Reservation Modal -->
<div class="modal fade" id="newReservationModal" tabindex="-1" aria-labelledby="newReservationModalLabel">
    <div class="modal-dialog modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title brand-font" id="newReservationModalLabel">{{ __('ops.place_reservation') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('app.cancel') }}"></button>
            </div>
            <form action="{{ route('reservations.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="resProperty" class="form-label small fw-semibold">{{ __('ops.property') }} *</label>
                        <select id="resProperty" name="property_id" class="form-select select2 w-100" required>
                            @foreach($properties as $pr)
                                <option value="{{ $pr->id }}" data-price="{{ (float) $pr->price }}">{{ $pr->title }} ({{ format_money_short($pr->price) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="resCustomer" class="form-label small fw-semibold">{{ __('ops.customer_buyer') }} *</label>
                        <select id="resCustomer" name="customer_id" class="form-select select2 w-100" required>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->full_name }} ({{ format_phone_tz($c->phone) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-12 col-sm-6">
                            <label for="resFee" class="form-label small fw-semibold">{{ __('ops.reservation_fee') }} *</label>
                            <div class="input-group">
                                <span class="input-group-text">TSh</span>
                                <input type="number" step="0.01" min="0" inputmode="decimal" id="resFee" name="reservation_fee" class="form-control" value="5000000" required>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label for="resDeposit" class="form-label small fw-semibold">{{ __('ops.deposit_paid') }} *</label>
                            <div class="input-group">
                                <span class="input-group-text">TSh</span>
                                <input type="number" step="0.01" min="0" inputmode="decimal" id="resDeposit" name="deposit_paid" class="form-control" value="5000000" required>
                            </div>
                            <div class="form-text" id="resDepositHint">{{ __('ops.deposit_hint') }}</div>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-12 col-sm-6">
                            <label for="resFrom" class="form-label small fw-semibold">{{ __('ops.hold_start') }} *</label>
                            <input type="date" id="resFrom" name="reserved_from" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label for="resUntil" class="form-label small fw-semibold">{{ __('ops.hold_expiry') }} *</label>
                            <input type="date" id="resUntil" name="reserved_until" class="form-control" value="{{ date('Y-m-d', strtotime('+14 days')) }}" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="resAgent" class="form-label small fw-semibold">{{ __('ops.assigned_agent') }}</label>
                        <select id="resAgent" name="agent_id" class="form-select">
                            <option value="">{{ __('ops.unassigned') }}</option>
                            @foreach($agents as $ag)
                                <option value="{{ $ag->id }}">{{ $ag->user?->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">{{ __('app.cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('ops.submit_reservation') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const property = document.getElementById('resProperty');
    const deposit = document.getElementById('resDeposit');
    const hint = document.getElementById('resDepositHint');
    const template = @json(__('ops.deposit_of_price', ['percent' => ':percent']));
    const fallback = @json(__('ops.deposit_hint'));

    function updateHint() {
        if (!property || !deposit || !hint) { return; }
        const option = property.options[property.selectedIndex];
        const price = option ? parseFloat(option.dataset.price || '0') : 0;
        const amount = parseFloat(deposit.value || '0');
        if (price > 0 && amount > 0) {
            hint.textContent = template.replace(':percent', (amount / price * 100).toFixed(1));
        } else {
            hint.textContent = fallback;
        }
    }

    if (property && deposit) {
        property.addEventListener('change', updateHint);
        deposit.addEventListener('input', updateHint);
        if (window.jQuery) { window.jQuery(property).on('change', updateHint); }
        updateHint();
    }
});
</script>
@endsection
