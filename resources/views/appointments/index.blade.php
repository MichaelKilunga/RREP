@extends('layouts.app')

@section('title', __('app.appointments'))

@section('content')
@php
    $appointmentBadge = ['Pending' => 'pending', 'Confirmed' => 'active', 'Completed' => 'paid', 'Cancelled' => 'cancelled', 'Rescheduled' => 'review', 'No Show' => 'overdue'];
    $appointmentStatuses = ['Pending', 'Confirmed', 'Completed', 'Rescheduled', 'No Show', 'Cancelled'];
@endphp

<div class="page-header">
    <div class="min-w-0">
        <h1 class="brand-font mb-1">{{ __('app.appointments') }}</h1>
        <p class="text-muted small mb-0">{{ __('ops.appointments_subtitle') }}</p>
    </div>
    <div class="page-actions">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newApptModal">
            <i class="bi bi-calendar-plus me-1"></i> {{ __('ops.schedule_viewing') }}
        </button>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 table-stack small">
            <thead class="table-light">
                <tr>
                    <th>{{ __('ops.appointment_no') }}</th>
                    <th>{{ __('ops.scheduled_at') }}</th>
                    <th>{{ __('ops.property') }}</th>
                    <th>{{ __('ops.prospect') }}</th>
                    <th>{{ __('ops.agent') }}</th>
                    <th>{{ __('ops.type') }}</th>
                    <th>{{ __('app.status') }}</th>
                    <th class="text-end">{{ __('app.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($appointments as $apt)
                    <tr>
                        <td class="stack-primary"><span class="fw-bold text-primary">{{ $apt->appointment_number }}</span></td>
                        <td>
                            <div class="fw-semibold">{{ $apt->scheduled_at->format('D d M Y, H:i') }} <small class="text-muted">EAT</small></div>
                            <small class="text-muted">{{ __('ops.minutes', ['count' => $apt->duration_minutes]) }}</small>
                        </td>
                        <td>{{ $apt->property?->title }}</td>
                        <td>
                            <div class="fw-semibold">{{ $apt->customer?->full_name }}</div>
                            @if($apt->customer?->phone)
                                <a href="tel:{{ $apt->customer->phone }}" class="small text-muted text-decoration-none">{{ format_phone_tz($apt->customer->phone) }}</a>
                            @endif
                        </td>
                        <td>{{ $apt->agent?->user?->name ?? __('ops.unassigned') }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $apt->meeting_type }}</span></td>
                        <td><span class="badge badge-status-{{ $appointmentBadge[$apt->status] ?? strtolower(str_replace(' ', '', $apt->status)) }}">{{ $apt->status }}</span></td>
                        <td class="stack-actions text-end">
                            <form action="{{ route('appointments.status', $apt) }}" method="POST" class="d-inline-flex gap-1 align-items-center">
                                @csrf
                                <label for="apptStatus{{ $apt->id }}" class="visually-hidden">{{ __('ops.update_status') }}</label>
                                <select id="apptStatus{{ $apt->id }}" name="status" class="form-select form-select-sm" aria-label="{{ __('ops.update_status') }}">
                                    @foreach($appointmentStatuses as $st)
                                        <option value="{{ $st }}" @selected($st === $apt->status)>{{ $st }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="btn btn-sm btn-outline-primary text-nowrap"><i class="bi bi-check2"></i><span class="d-md-none ms-1">{{ __('ops.update_status') }}</span></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="stack-no-label">
                            <div class="empty-state">
                                <div class="empty-icon"><i class="bi bi-calendar-event"></i></div>
                                <h6>{{ __('ops.no_appointments') }}</h6>
                                <p class="small mb-3">{{ __('ops.no_appointments_hint') }}</p>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newApptModal"><i class="bi bi-calendar-plus me-1"></i> {{ __('ops.schedule_viewing') }}</button>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="newApptModal" tabindex="-1" aria-labelledby="newApptModalLabel">
    <div class="modal-dialog modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title brand-font" id="newApptModalLabel">{{ __('ops.schedule_viewing_title') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('app.cancel') }}"></button>
            </div>
            <form action="{{ route('appointments.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="apptProperty" class="form-label small fw-semibold">{{ __('ops.property') }} *</label>
                        <select id="apptProperty" name="property_id" class="form-select" required>
                            @foreach($properties as $pr)
                                <option value="{{ $pr->id }}">{{ $pr->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="apptCustomer" class="form-label small fw-semibold">{{ __('ops.customer_buyer') }} *</label>
                        <select id="apptCustomer" name="customer_id" class="form-select" required>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->full_name }} ({{ format_phone_tz($c->phone) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-12 col-sm-6">
                            <label for="apptWhen" class="form-label small fw-semibold">{{ __('ops.date_time_eat') }} *</label>
                            <input type="datetime-local" id="apptWhen" name="scheduled_at" class="form-control" required>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label for="apptDuration" class="form-label small fw-semibold">{{ __('ops.duration_minutes') }}</label>
                            <input type="number" id="apptDuration" name="duration_minutes" class="form-control" value="60" min="15" step="15" inputmode="numeric">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="apptType" class="form-label small fw-semibold">{{ __('ops.meeting_type') }}</label>
                        <select id="apptType" name="meeting_type" class="form-select">
                            <option value="Site Viewing">{{ __('ops.meeting_site_viewing') }}</option>
                            <option value="Office Consultation">{{ __('ops.meeting_office') }}</option>
                            <option value="Virtual Tour">{{ __('ops.meeting_virtual') }}</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">{{ __('app.cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('ops.schedule_viewing') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
