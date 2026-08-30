@extends('layouts.app')

@section('title', __('app.settings'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="brand-font mb-1">{{ __('app.settings') }} & Administration</h3>
        <p class="text-muted small mb-0">Tenant branding tokens, licensed module feature flags, and system audit logs</p>
    </div>
</div>

<div class="row g-4">
    <!-- Branding & White-Label (FM-005) -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header brand-font">Tenant White-Label Branding (FM-005)</div>
            <div class="card-body">
                <form action="{{ route('settings.branding') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Company Tagline</label>
                        <input type="text" name="company_tagline" class="form-control" value="{{ $branding?->company_tagline }}">
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Primary Color</label>
                            <input type="color" name="primary_color" class="form-control form-control-color w-100" value="{{ $branding?->primary_color ?? '#0f52ba' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Secondary Color</label>
                            <input type="color" name="secondary_color" class="form-control form-control-color w-100" value="{{ $branding?->secondary_color ?? '#495057' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Accent Color</label>
                            <input type="color" name="accent_color" class="form-control form-control-color w-100" value="{{ $branding?->accent_color ?? '#00a86b' }}">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm fw-semibold">Save Branding Tokens</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Branches (FM-001) -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header brand-font">Organization Branches (FM-001)</div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @foreach($branches as $br)
                        <li class="list-group-item d-flex justify-content-between align-items-center p-3">
                            <div>
                                <div class="fw-bold">{{ $br->name }} @if($br->is_main)<span class="badge bg-primary ms-1">HQ</span>@endif</div>
                                <small class="text-muted">{{ $br->city }} &bull; Code: {{ $br->code }}</small>
                            </div>
                            <span class="badge bg-success-subtle text-success">{{ $br->status }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    <!-- Licensed Modules Switchboard (BM-018) -->
    <div class="col-12">
        <div class="card">
            <div class="card-header brand-font d-flex justify-content-between align-items-center">
                <span>Licensed Modules & Feature Flags (BM-018)</span>
                <span class="badge bg-primary">{{ $modules->count() }} Modules Registered</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                    <thead class="table-light">
                        <tr>
                            <th>Code</th>
                            <th>Module Name</th>
                            <th>Category</th>
                            <th>Tier</th>
                            <th>State</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($modules as $mod)
                            <tr>
                                <td class="fw-bold text-primary">{{ $mod->module_code }}</td>
                                <td>{{ $mod->module_name }}</td>
                                <td><span class="badge bg-light text-dark border">{{ $mod->category }}</span></td>
                                <td><span class="badge bg-info-subtle text-info">{{ $mod->license_tier }}</span></td>
                                <td>
                                    <span class="badge bg-success-subtle text-success"><i class="bi bi-check-circle me-1"></i>Active / Enabled</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Audit Logs (FM-007) -->
    <div class="col-12">
        <div class="card">
            <div class="card-header brand-font">Security & Activity Audit Trail (FM-007)</div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                    <thead class="table-light">
                        <tr>
                            <th>Timestamp</th>
                            <th>User</th>
                            <th>Event</th>
                            <th>Target Model</th>
                            <th>IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($auditLogs as $log)
                            <tr>
                                <td>{{ $log->created_at->format('d M Y H:i:s') }}</td>
                                <td class="fw-semibold">{{ $log->user_name }}</td>
                                <td><span class="badge bg-primary-subtle text-primary">{{ strtoupper($log->event) }}</span></td>
                                <td>{{ class_basename($log->auditable_type) }}</td>
                                <td>{{ $log->ip_address ?? '127.0.0.1' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-3">No audit entries recorded.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
