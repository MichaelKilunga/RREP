@extends('layouts.app')

@section('title', __('app.appointments'))

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h3 class="brand-font mb-1">{{ __('app.appointments') }}</h3>
        <p class="text-muted small mb-0">Scheduled site inspections, viewing tours, and consultations</p>
    </div>
    <button class="btn btn-primary btn-sm d-flex align-items-center gap-1 shadow-sm" data-bs-toggle="modal" data-bs-target="#newApptModal">
        <i class="bi bi-calendar-plus"></i> Schedule Viewing
    </button>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: 0.875rem;">
            <thead class="table-light">
                <tr>
                    <th>Appt #</th>
                    <th>Scheduled Date & Time</th>
                    <th>Property</th>
                    <th>Prospect</th>
                    <th>Agent</th>
                    <th>Type</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($appointments as $apt)
                    <tr>
                        <td class="fw-bold text-primary">{{ $apt->appointment_number }}</td>
                        <td>
                            <div class="fw-semibold">{{ $apt->scheduled_at->format('d M Y, h:i A') }}</div>
                            <small class="text-muted">{{ $apt->duration_minutes }} mins</small>
                        </td>
                        <td>{{ $apt->property?->title }}</td>
                        <td>
                            <div class="fw-semibold">{{ $apt->customer?->full_name }}</div>
                            <small class="text-muted">{{ $apt->customer?->phone }}</small>
                        </td>
                        <td>{{ $apt->agent?->user?->name ?? 'Unassigned' }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $apt->meeting_type }}</span></td>
                        <td><span class="badge bg-primary-subtle text-primary">{{ $apt->status }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No appointments scheduled.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="newApptModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title brand-font">Schedule Viewing Appointment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('appointments.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Property *</label>
                        <select name="property_id" class="form-select" required>
                            @foreach($properties as $pr)
                                <option value="{{ $pr->id }}">{{ $pr->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Customer / Buyer *</label>
                        <select name="customer_id" class="form-select" required>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->full_name }} ({{ $c->phone }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Date & Time *</label>
                            <input type="datetime-local" name="scheduled_at" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Duration (Minutes)</label>
                            <input type="number" name="duration_minutes" class="form-control" value="60">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Meeting Type</label>
                        <select name="meeting_type" class="form-select">
                            <option value="Site Viewing">Physical Site Viewing</option>
                            <option value="Office Consultation">Office Consultation</option>
                            <option value="Virtual Tour">Virtual 3D Tour</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Schedule Viewing</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
