@extends('layouts.app')

@section('title', __('app.notifications'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="brand-font mb-1"><i class="bi bi-bell-fill text-primary me-2"></i>Notification Dispatcher & Communication Logs (FM-008)</h3>
        <p class="text-muted small mb-0">Multi-channel communication templates (Email, SMS, WhatsApp) and delivery logs</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header brand-font">Multi-Channel Templates</div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($templates as $tmpl)
                        <li class="list-group-item p-3">
                            <div class="fw-bold mb-1">{{ $tmpl->name }} (<code>{{ $tmpl->code }}</code>)</div>
                            <span class="badge bg-light text-dark border mb-2">{{ $tmpl->channel }}</span>
                            <div class="p-2 bg-light rounded text-muted small font-monospace">{{ $tmpl->body_template }}</div>
                        </li>
                    @empty
                        <li class="list-group-item text-muted small py-3">No templates registered.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header brand-font">Dispatched Communications Trail</div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                    <thead class="table-light">
                        <tr>
                            <th>Recipient</th>
                            <th>Channel</th>
                            <th>Status</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $lg)
                            <tr>
                                <td>{{ $lg->customer?->full_name ?? $lg->recipient }}</td>
                                <td><span class="badge bg-light text-dark border">{{ $lg->channel }}</span></td>
                                <td><span class="badge bg-success-subtle text-success">{{ $lg->status }}</span></td>
                                <td>{{ $lg->created_at->format('d M Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-3">No dispatched logs recorded.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
