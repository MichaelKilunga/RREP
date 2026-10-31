@extends('layouts.app')

@section('title', __('ops.users_title'))

@section('content')
@php
    $canManageUsers = auth()->user()->can('manage_users');
    $canManageRoles = auth()->user()->can('manage_roles');
@endphp

<div class="page-header">
    <div class="min-w-0">
        <h1 class="brand-font mb-1">{{ __('ops.users_title') }}</h1>
        <p class="text-muted small mb-0">{{ __('ops.users_subtitle') }}</p>
    </div>
    <div class="page-actions">
        @if($canManageRoles)
            <a href="{{ route('settings.rbac') }}" class="btn btn-light border"><i class="bi bi-shield-lock me-1"></i>{{ __('ops.rbac_title') }}</a>
        @endif
        @if($canManageUsers)
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newUserModal">
                <i class="bi bi-person-plus me-1"></i> {{ __('ops.add_user') }}
            </button>
        @endif
    </div>
</div>

<!-- KPI Stats -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-label">{{ __('ops.total_users') }}</div>
            <div class="kpi-value">{{ number_format($stats['total_users']) }}</div>
            <span class="kpi-icon"><i class="bi bi-people"></i></span>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card kpi-success">
            <div class="kpi-label">{{ __('ops.active_accounts') }}</div>
            <div class="kpi-value text-success">{{ number_format($stats['active_users']) }}</div>
            <span class="kpi-icon"><i class="bi bi-person-check"></i></span>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card kpi-info">
            <div class="kpi-label">{{ __('ops.active_today') }}</div>
            <div class="kpi-value">{{ number_format($stats['active_today']) }}</div>
            <span class="kpi-icon"><i class="bi bi-activity"></i></span>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card kpi-danger">
            <div class="kpi-label">{{ __('ops.suspended_banned') }}</div>
            <div class="kpi-value text-danger">{{ number_format($stats['banned_users']) }}</div>
            <span class="kpi-icon"><i class="bi bi-slash-circle"></i></span>
        </div>
    </div>
</div>

<!-- Users Table -->
<div class="card mb-4">
    <div class="card-header">
        <button class="btn btn-outline-secondary btn-sm d-md-none w-100 mb-2" type="button" data-bs-toggle="collapse" data-bs-target="#userFilters" aria-expanded="false" aria-controls="userFilters"><i class="bi bi-funnel me-1"></i>{{ __('ops.filters') }}</button>
        <form method="GET" action="{{ route('users.index') }}" id="userFilters" class="collapse d-md-block">
            <div class="filter-bar">
                <div class="flex-grow-1">
                    <label for="userSearch" class="visually-hidden">{{ __('app.search') }}</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="search" id="userSearch" name="q" class="form-control" placeholder="{{ __('ops.user_search_placeholder') }}" value="{{ request('q') }}">
                    </div>
                </div>
                <div>
                    <label for="userRole" class="visually-hidden">{{ __('ops.role') }}</label>
                    <select id="userRole" name="role" class="form-select" onchange="this.form.submit()">
                        <option value="">{{ __('ops.all_roles') }}</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}" @selected(request('role') === $role->name)>{{ $role->display_name ?? $role->name }} ({{ $role->users_count }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="userBranch" class="visually-hidden">{{ __('app.branch') }}</label>
                    <select id="userBranch" name="branch" class="form-select" onchange="this.form.submit()">
                        <option value="">{{ __('app.all_branches') }}</option>
                        @foreach($branches as $br)
                            <option value="{{ $br->id }}" @selected((string) request('branch') === (string) $br->id)>{{ $br->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="userStatus" class="visually-hidden">{{ __('app.status') }}</label>
                    <select id="userStatus" name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">{{ __('ops.all_statuses') }}</option>
                        <option value="Active" @selected(request('status') === 'Active')>{{ __('ops.active') }}</option>
                        <option value="Suspended" @selected(request('status') === 'Suspended')>{{ __('ops.suspended') }}</option>
                    </select>
                </div>
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-search me-1"></i>{{ __('ops.search') }}</button>
                    <a href="{{ route('users.index') }}" class="btn btn-light border">{{ __('ops.reset') }}</a>
                </div>
            </div>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 table-stack small">
                <thead class="table-light">
                    <tr>
                        @if($canManageUsers)
                            <th class="stack-check" style="width: 2.5rem;"><input type="checkbox" class="form-check-input" id="userSelectAll" aria-label="{{ __('admin.select_all') }}"></th>
                        @endif
                        <th>{{ __('ops.user_profile') }}</th>
                        <th>{{ __('ops.contact') }}</th>
                        <th>{{ __('ops.assigned_roles') }}</th>
                        <th>{{ __('app.branch') }}</th>
                        <th>{{ __('ops.last_login') }}</th>
                        <th>{{ __('ops.account_status') }}</th>
                        <th class="text-end">{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $u)
                        @php $isSelf = $u->is(auth()->user()); @endphp
                        <tr>
                            @if($canManageUsers)
                                <td class="stack-check">
                                    @unless($isSelf)
                                        <input type="checkbox" class="form-check-input user-check" value="{{ $u->id }}" aria-label="{{ $u->name }}">
                                    @endunless
                                </td>
                            @endif
                            <td class="stack-primary">
                                <div class="d-flex align-items-center gap-2 min-w-0">
                                    <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold flex-shrink-0 btn-icon">
                                        {{ strtoupper(substr($u->name, 0, 2)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="fw-bold text-dark text-truncate">{{ $u->name }} @if($isSelf)<span class="badge bg-info-subtle text-info ms-1">{{ __('ops.you') }}</span>@endif</div>
                                        <small class="text-muted fw-normal">{{ $u->job_title ?: ($u->user_type ?? __('ops.user')) }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="text-break"><i class="bi bi-envelope text-muted me-1"></i>{{ $u->email }}</div>
                                @if($u->phone)
                                    <a href="tel:{{ $u->phone }}" class="small text-muted text-decoration-none"><i class="bi bi-phone text-muted me-1"></i>{{ format_phone_tz($u->phone) }}</a>
                                @endif
                            </td>
                            <td>
                                @forelse($u->roles as $r)
                                    <span class="badge bg-primary-subtle text-primary mb-1">{{ $r->display_name ?? $r->name }}</span>
                                @empty
                                    <span class="badge bg-warning-subtle text-warning-emphasis border">{{ __('ops.no_role') }}</span>
                                @endforelse
                            </td>
                            <td>{{ $u->branch?->name ?? __('ops.hq_all') }}</td>
                            <td class="text-muted text-nowrap" title="{{ $u->last_login_at }}">{{ $u->last_login_at ? $u->last_login_at->diffForHumans() : __('ops.never') }}</td>
                            <td>
                                @if($u->status === 'Active')
                                    <span class="badge badge-status-active">{{ __('ops.active') }}</span>
                                @else
                                    <span class="badge badge-status-cancelled">{{ __('ops.suspended') }}</span>
                                @endif
                            </td>
                            <td class="stack-actions text-end">
                                <div class="d-inline-flex flex-wrap justify-content-end gap-1">
                                    @if($canManageUsers)
                                        <button type="button" class="btn btn-sm btn-light border btn-icon user-edit-btn" title="{{ __('app.edit') }}" aria-label="{{ __('app.edit') }}"
                                            data-url="{{ route('users.update', $u) }}" data-first="{{ $u->first_name ?: Str::before($u->name, ' ') }}" data-last="{{ $u->last_name ?: Str::after($u->name, ' ') }}"
                                            data-email="{{ $u->email }}" data-phone="{{ $u->phone }}" data-job="{{ $u->job_title }}" data-branch="{{ $u->branch_id }}">
                                            <i class="bi bi-pencil text-primary"></i>
                                        </button>
                                    @endif
                                    @if($canManageRoles)
                                        <button type="button" class="btn btn-sm btn-light border btn-icon user-roles-btn" title="{{ __('ops.elevate_roles') }}" aria-label="{{ __('ops.elevate_roles') }}"
                                            data-url="{{ route('users.elevate_role', $u) }}" data-name="{{ $u->name }}" data-roles='@json($u->roles->pluck('id'))'>
                                            <i class="bi bi-shield-check text-success"></i>
                                        </button>
                                    @endif
                                    @if($canManageUsers)
                                        <button type="button" class="btn btn-sm btn-light border btn-icon user-password-btn" title="{{ __('ops.reset_password') }}" aria-label="{{ __('ops.reset_password') }}"
                                            data-url="{{ route('users.password', $u) }}" data-name="{{ $u->name }}">
                                            <i class="bi bi-key text-warning"></i>
                                        </button>
                                        @unless($isSelf)
                                            <form action="{{ route('users.toggle_status', $u) }}" method="POST" class="m-0" onsubmit="return confirm(@json(__('ops.confirm_toggle_status')));">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-light border btn-icon" title="{{ $u->status === 'Active' ? __('ops.suspend_account') : __('ops.activate_account') }}" aria-label="{{ $u->status === 'Active' ? __('ops.suspend_account') : __('ops.activate_account') }}">
                                                    <i class="bi {{ $u->status === 'Active' ? 'bi-slash-circle text-danger' : 'bi-check-circle text-success' }}"></i>
                                                </button>
                                            </form>
                                            <form action="{{ route('users.destroy', $u) }}" method="POST" class="m-0" onsubmit="return confirm(@json(__('ops.user_delete_confirm')));">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-light border btn-icon" title="{{ __('app.delete') }}" aria-label="{{ __('app.delete') }}"><i class="bi bi-trash text-danger"></i></button>
                                            </form>
                                        @endunless
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="stack-no-label">
                                <div class="empty-state">
                                    <div class="empty-icon"><i class="bi bi-people"></i></div>
                                    <h6>{{ __('ops.no_users') }}</h6>
                                    <p class="small mb-3">{{ __('ops.no_users_hint') }}</p>
                                    <a href="{{ route('users.index') }}" class="btn btn-light border btn-sm">{{ __('ops.reset') }}</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
            <div class="p-3 border-top d-flex justify-content-center justify-content-md-end">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>

@if($canManageUsers)
<!-- Bulk bar -->
<form action="{{ route('users.bulk') }}" method="POST" id="userBulkForm">
    @csrf
    <div class="bulk-bar flex-column d-none" id="userBulkBar">
        <div class="bulk-inner">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary rounded-pill fs-6" id="userBulkCount">0</span>
                <span class="text-white small fw-semibold">{{ __('ops.users_selected') }}</span>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <button type="submit" name="action" value="activate" class="btn btn-sm btn-outline-success rounded-pill"><i class="bi bi-check-circle me-1"></i>{{ __('ops.activate_account') }}</button>
                <button type="submit" name="action" value="suspend" class="btn btn-sm btn-outline-warning rounded-pill"><i class="bi bi-slash-circle me-1"></i>{{ __('ops.suspend_account') }}</button>
                <button type="submit" name="action" value="delete" class="btn btn-sm btn-danger rounded-pill px-3" data-confirm="{{ __('ops.users_bulk_delete_confirm') }}"><i class="bi bi-trash me-1"></i>{{ __('app.delete') }}</button>
            </div>
        </div>
    </div>
</form>

<!-- Edit user (shared) -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserLabel">
    <div class="modal-dialog modal-dialog-scrollable modal-fullscreen-sm-down">
        <form class="modal-content" method="POST" id="editUserForm">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title" id="editUserLabel">{{ __('ops.edit_user') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('app.cancel') }}"></button>
            </div>
            <div class="modal-body">
                <div class="row g-2 mb-3">
                    <div class="col-12 col-sm-6">
                        <label for="editUserFirst" class="form-label small fw-semibold">{{ __('ops.first_name') }}</label>
                        <input type="text" id="editUserFirst" name="first_name" class="form-control" required>
                    </div>
                    <div class="col-12 col-sm-6">
                        <label for="editUserLast" class="form-label small fw-semibold">{{ __('ops.last_name') }}</label>
                        <input type="text" id="editUserLast" name="last_name" class="form-control" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="editUserEmail" class="form-label small fw-semibold">{{ __('ops.email_address') }}</label>
                    <input type="email" id="editUserEmail" name="email" class="form-control" required>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-12 col-sm-6">
                        <label for="editUserPhone" class="form-label small fw-semibold">{{ __('ops.phone_number') }}</label>
                        <input type="tel" id="editUserPhone" name="phone" class="form-control" data-tz-phone>
                    </div>
                    <div class="col-12 col-sm-6">
                        <label for="editUserJob" class="form-label small fw-semibold">{{ __('ops.job_title') }}</label>
                        <input type="text" id="editUserJob" name="job_title" class="form-control">
                    </div>
                </div>
                <div>
                    <label for="editUserBranch" class="form-label small fw-semibold">{{ __('app.branch') }}</label>
                    <select id="editUserBranch" name="branch_id" class="form-select">
                        <option value="">{{ __('ops.hq_main_branch') }}</option>
                        @foreach($branches as $br)
                            <option value="{{ $br->id }}">{{ $br->name }} ({{ $br->city }})</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">{{ __('app.cancel') }}</button>
                <button type="submit" class="btn btn-primary">{{ __('app.save') }}</button>
            </div>
        </form>
    </div>
</div>

<!-- Reset password (shared) -->
<div class="modal fade" id="passwordUserModal" tabindex="-1" aria-labelledby="passwordUserLabel">
    <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
        <form class="modal-content" method="POST" id="passwordUserForm">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="passwordUserLabel">{{ __('ops.reset_password') }}: <span id="passwordUserName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('app.cancel') }}"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="newPassword" class="form-label small fw-semibold">{{ __('ops.new_password') }}</label>
                    <div class="input-group">
                        <input type="text" id="newPassword" name="password" class="form-control font-monospace" required minlength="8" autocomplete="new-password">
                        <button type="button" class="btn btn-outline-secondary" id="generatePasswordBtn"><i class="bi bi-magic me-1"></i>{{ __('ops.generate') }}</button>
                    </div>
                    <div class="form-text">{{ __('ops.password_share_hint') }}</div>
                </div>
                <div>
                    <label for="newPasswordConfirm" class="form-label small fw-semibold">{{ __('ops.confirm_password') }}</label>
                    <input type="text" id="newPasswordConfirm" name="password_confirmation" class="form-control font-monospace" required minlength="8" autocomplete="new-password">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">{{ __('app.cancel') }}</button>
                <button type="submit" class="btn btn-warning fw-semibold">{{ __('ops.reset_password') }}</button>
            </div>
        </form>
    </div>
</div>
@endif

@if($canManageRoles)
<!-- Roles (shared) -->
<div class="modal fade" id="rolesUserModal" tabindex="-1" aria-labelledby="rolesUserLabel">
    <div class="modal-dialog modal-dialog-scrollable modal-fullscreen-sm-down">
        <form class="modal-content" method="POST" id="rolesUserForm">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="rolesUserLabel">{{ __('ops.elevate_roles') }}: <span id="rolesUserName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('app.cancel') }}"></button>
            </div>
            <div class="modal-body">
                <p class="small text-muted mb-3">{{ __('ops.elevate_hint') }}</p>
                <div class="d-flex flex-column gap-2">
                    @foreach($roles as $role)
                        @continue($role->name === 'super_admin' && ! auth()->user()->isSuperAdmin())
                        <div class="form-check">
                            <input class="form-check-input role-choice" type="checkbox" name="role_ids[]" value="{{ $role->id }}" id="roleChoice{{ $role->id }}">
                            <label class="form-check-label fw-semibold" for="roleChoice{{ $role->id }}">
                                {{ $role->display_name ?? $role->name }}
                                <small class="text-muted d-block fw-normal">{{ $role->description ?? __('ops.standard_role') }}</small>
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">{{ __('app.cancel') }}</button>
                <button type="submit" class="btn btn-primary">{{ __('ops.save_role_elevation') }}</button>
            </div>
        </form>
    </div>
</div>
@endif

@if($canManageUsers)
<!-- Modal: New User -->
<div class="modal fade" id="newUserModal" tabindex="-1" aria-labelledby="newUserModalLabel">
    <div class="modal-dialog modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content">
            <form action="{{ route('users.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="newUserModalLabel">{{ __('ops.add_user_title') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('app.cancel') }}"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-2 mb-3">
                        <div class="col-12 col-sm-6">
                            <label for="newUserFirst" class="form-label small fw-semibold">{{ __('ops.first_name') }}</label>
                            <input type="text" id="newUserFirst" name="first_name" class="form-control" autocomplete="given-name" value="{{ old('first_name') }}" required>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label for="newUserLast" class="form-label small fw-semibold">{{ __('ops.last_name') }}</label>
                            <input type="text" id="newUserLast" name="last_name" class="form-control" autocomplete="family-name" value="{{ old('last_name') }}" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="newUserEmail" class="form-label small fw-semibold">{{ __('ops.email_address') }}</label>
                        <input type="email" id="newUserEmail" name="email" class="form-control" autocomplete="email" inputmode="email" value="{{ old('email') }}" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-12 col-sm-6">
                            <label for="newUserPhone" class="form-label small fw-semibold">{{ __('ops.phone_number') }}</label>
                            <input type="tel" id="newUserPhone" name="phone" class="form-control" data-tz-phone placeholder="0784 100 200" autocomplete="tel" value="{{ old('phone') }}">
                        </div>
                        <div class="col-12 col-sm-6">
                            <label for="newUserJob" class="form-label small fw-semibold">{{ __('ops.job_title') }}</label>
                            <input type="text" id="newUserJob" name="job_title" class="form-control" value="{{ old('job_title') }}">
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-12 col-sm-6">
                            <label for="newUserRole" class="form-label small fw-semibold">{{ __('ops.primary_role') }}</label>
                            <select id="newUserRole" name="role_id" class="form-select" required>
                                @foreach($roles as $role)
                                    @continue($role->name === 'super_admin' && ! auth()->user()->isSuperAdmin())
                                    <option value="{{ $role->id }}" @selected((string) old('role_id') === (string) $role->id)>{{ $role->display_name ?? $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label for="newUserBranch" class="form-label small fw-semibold">{{ __('app.branch') }}</label>
                            <select id="newUserBranch" name="branch_id" class="form-select">
                                <option value="">{{ __('ops.hq_main_branch') }}</option>
                                @foreach($branches as $br)
                                    <option value="{{ $br->id }}">{{ $br->name }} ({{ $br->city }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="newUserPassword" class="form-label small fw-semibold">{{ __('ops.initial_password') }}</label>
                        <input type="password" id="newUserPassword" name="password" class="form-control" required minlength="8" autocomplete="new-password">
                        <div class="form-text">{{ __('ops.password_hint') }}</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">{{ __('app.cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('ops.create_account') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const show = (id) => bootstrap.Modal.getOrCreateInstance(document.getElementById(id)).show();

    document.querySelectorAll('.user-edit-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const form = document.getElementById('editUserForm');
            form.action = btn.dataset.url;
            form.first_name.value = btn.dataset.first || '';
            form.last_name.value = btn.dataset.last || '';
            form.email.value = btn.dataset.email || '';
            form.phone.value = btn.dataset.phone || '';
            form.job_title.value = btn.dataset.job || '';
            form.branch_id.value = btn.dataset.branch || '';
            show('editUserModal');
        });
    });

    document.querySelectorAll('.user-password-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const form = document.getElementById('passwordUserForm');
            form.action = btn.dataset.url;
            form.reset();
            document.getElementById('passwordUserName').textContent = btn.dataset.name;
            show('passwordUserModal');
        });
    });

    const generate = document.getElementById('generatePasswordBtn');
    if (generate) {
        generate.addEventListener('click', function () {
            const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
            const bytes = new Uint32Array(12);
            crypto.getRandomValues(bytes);
            const pwd = Array.from(bytes, b => chars[b % chars.length]).join('');
            document.getElementById('newPassword').value = pwd;
            document.getElementById('newPasswordConfirm').value = pwd;
        });
    }

    document.querySelectorAll('.user-roles-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const form = document.getElementById('rolesUserForm');
            form.action = btn.dataset.url;
            document.getElementById('rolesUserName').textContent = btn.dataset.name;
            const assigned = JSON.parse(btn.dataset.roles || '[]').map(String);
            form.querySelectorAll('.role-choice').forEach(c => { c.checked = assigned.includes(c.value); });
            show('rolesUserModal');
        });
    });

    // Bulk selection
    const bar = document.getElementById('userBulkBar');
    const bulkForm = document.getElementById('userBulkForm');
    const checks = Array.from(document.querySelectorAll('.user-check'));
    const all = document.getElementById('userSelectAll');
    function refresh() {
        const n = checks.filter(c => c.checked).length;
        if (!bar) { return; }
        document.getElementById('userBulkCount').textContent = n;
        bar.classList.toggle('d-none', n === 0);
        bar.classList.toggle('d-flex', n > 0);
    }
    checks.forEach(c => c.addEventListener('change', refresh));
    if (all) { all.addEventListener('change', () => { checks.forEach(c => { c.checked = all.checked; }); refresh(); }); }
    if (bulkForm) {
        bulkForm.addEventListener('submit', function (e) {
            const confirmText = e.submitter && e.submitter.dataset.confirm;
            if (confirmText && !window.confirm(confirmText)) { e.preventDefault(); return; }
            bulkForm.querySelectorAll('input[name="ids[]"]').forEach(i => i.remove());
            checks.filter(c => c.checked).forEach(function (c) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ids[]';
                input.value = c.value;
                bulkForm.appendChild(input);
            });
        });
    }
});
</script>
@endsection
