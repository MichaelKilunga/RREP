@extends('layouts.app')

@section('title', 'User Management & Personnel Elevation')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="brand-font mb-1">User Management & Personnel Elevation</h3>
        <p class="text-muted small mb-0">Single master panel to view profiles, ban malicious accounts, or elevate internal personnel roles (Field Surveyor, Admin, Sales Agent)</p>
    </div>
    <button type="button" class="btn btn-primary btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#newUserModal">
        <i class="bi bi-person-plus me-1"></i> Add New Personnel / User
    </button>
</div>

<!-- KPI Stats -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-white">
            <div class="card-body">
                <div class="text-muted small fw-semibold text-uppercase">Total Users</div>
                <div class="h3 fw-bold text-dark mb-0 mt-1">{{ number_format($stats['total_users']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-white">
            <div class="card-body">
                <div class="text-muted small fw-semibold text-uppercase">Active Accounts</div>
                <div class="h3 fw-bold text-success mb-0 mt-1">{{ number_format($stats['active_users']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-white">
            <div class="card-body">
                <div class="text-muted small fw-semibold text-uppercase">Field Surveyors</div>
                <div class="h3 fw-bold text-primary mb-0 mt-1">{{ number_format($stats['field_surveyors']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-white">
            <div class="card-body">
                <div class="text-muted small fw-semibold text-uppercase">Suspended / Banned</div>
                <div class="h3 fw-bold text-danger mb-0 mt-1">{{ number_format($stats['banned_users']) }}</div>
            </div>
        </div>
    </div>
</div>

<!-- Users Table -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <form method="GET" action="{{ route('users.index') }}" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" class="form-control" placeholder="Search by name, email, or phone..." value="{{ request('q') }}">
                </div>
            </div>
            <div class="col-md-3">
                <select name="role" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">-- All Roles --</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->name }}" @selected(request('role') === $role->name)>{{ $role->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">-- All Statuses --</option>
                    <option value="Active" @selected(request('status') === 'Active')>Active</option>
                    <option value="Suspended" @selected(request('status') === 'Suspended')>Suspended</option>
                </select>
            </div>
            <div class="col-md-2 text-end">
                <a href="{{ route('users.index') }}" class="btn btn-sm btn-outline-secondary w-100">Reset Filters</a>
            </div>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>User Profile</th>
                        <th>Contact</th>
                        <th>Assigned Roles</th>
                        <th>Branch</th>
                        <th>Account Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $u)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 38px; height: 38px;">
                                        {{ strtoupper(substr($u->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">{{ $u->name }}</div>
                                        <small class="text-muted">Type: {{ $u->user_type ?? 'User' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div><i class="bi bi-envelope text-muted me-1"></i>{{ $u->email }}</div>
                                @if($u->phone)
                                    <small class="text-muted"><i class="bi bi-phone text-muted me-1"></i>{{ $u->phone }}</small>
                                @endif
                            </td>
                            <td>
                                @forelse($u->roles as $r)
                                    <span class="badge bg-primary-subtle text-primary mb-1">{{ $r->name }}</span>
                                @empty
                                    <span class="badge bg-light text-muted border">No Role Assigned</span>
                                @endforelse
                            </td>
                            <td>{{ $u->branch?->name ?? 'HQ / All' }}</td>
                            <td>
                                @if($u->status === 'Active')
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Suspended</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-1">
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#elevateRoleModal{{ $u->id }}" title="Elevate / Change Roles">
                                        <i class="bi bi-shield-check"></i> Elevate
                                    </button>
                                    <form action="{{ route('users.toggle_status', $u->id) }}" method="POST" onsubmit="return confirm('Change account status for {{ $u->name }}?');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm {{ $u->status === 'Active' ? 'btn-outline-danger' : 'btn-outline-success' }}" title="{{ $u->status === 'Active' ? 'Ban Account' : 'Activate Account' }}">
                                            <i class="bi {{ $u->status === 'Active' ? 'bi-slash-circle' : 'bi-check-circle' }}"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        <!-- Elevate Role Modal -->
                        <div class="modal fade" id="elevateRoleModal{{ $u->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('users.elevate_role', $u->id) }}" method="POST">
                                        @csrf
                                        <div class="modal-header">
                                            <h5 class="modal-title">Elevate Roles: {{ $u->name }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p class="small text-muted mb-3">Select the roles to assign to this personnel member (e.g. promote staff to Field Surveyor or Administrator):</p>
                                            <div class="d-flex flex-column gap-2">
                                                @foreach($roles as $role)
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="role_ids[]" value="{{ $role->id }}" id="role_{{ $u->id }}_{{ $role->id }}" @checked($u->roles->contains('id', $role->id))>
                                                        <label class="form-check-label fw-semibold" for="role_{{ $u->id }}_{{ $role->id }}">
                                                            {{ $role->name }}
                                                            <small class="text-muted d-block fw-normal">{{ $role->description ?? 'Standard platform role permissions' }}</small>
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary">Save Role Elevation</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr><td colspan="6" class="text-center py-4 text-muted">No users found matching query.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">
            {{ $users->links() }}
        </div>
    </div>
</div>

<!-- Modal: New User -->
<div class="modal fade" id="newUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('users.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add New Personnel Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">First Name</label>
                            <input type="text" name="first_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Last Name</label>
                            <input type="text" name="last_name" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Email Address</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Phone Number</label>
                        <input type="text" name="phone" class="form-control" placeholder="07xxxxxxxx">
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Primary Role</label>
                            <select name="role_id" class="form-select" required>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Branch</label>
                            <select name="branch_id" class="form-select">
                                <option value="">HQ / Main Branch</option>
                                @foreach($branches as $br)
                                    <option value="{{ $br->id }}">{{ $br->name }} ({{ $br->city }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Initial Password</label>
                        <input type="password" name="password" class="form-control" required minlength="8">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create User Account</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
