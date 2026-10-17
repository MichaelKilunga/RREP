@extends('layouts.app')

@section('title', __('ops.users_title'))

@section('content')
<div class="page-header">
    <div class="min-w-0">
        <h1 class="brand-font mb-1">{{ __('ops.users_title') }}</h1>
        <p class="text-muted small mb-0">{{ __('ops.users_subtitle') }}</p>
    </div>
    <div class="page-actions">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newUserModal">
            <i class="bi bi-person-plus me-1"></i> {{ __('ops.add_user') }}
        </button>
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
            <div class="kpi-label">{{ __('ops.field_surveyors') }}</div>
            <div class="kpi-value">{{ number_format($stats['field_surveyors']) }}</div>
            <span class="kpi-icon"><i class="bi bi-geo-alt"></i></span>
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
                            <option value="{{ $role->name }}" @selected(request('role') === $role->name)>{{ $role->display_name ?? $role->name }}</option>
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
                        <th>{{ __('ops.user_profile') }}</th>
                        <th>{{ __('ops.contact') }}</th>
                        <th>{{ __('ops.assigned_roles') }}</th>
                        <th>{{ __('app.branch') }}</th>
                        <th>{{ __('ops.account_status') }}</th>
                        <th class="text-end">{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $u)
                        <tr>
                            <td class="stack-primary">
                                <div class="d-flex align-items-center gap-2 min-w-0">
                                    <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold flex-shrink-0 btn-icon">
                                        {{ strtoupper(substr($u->name, 0, 2)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="fw-bold text-dark text-truncate">{{ $u->name }}</div>
                                        <small class="text-muted fw-normal">{{ $u->user_type ?? __('ops.user') }}</small>
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
                                    <span class="badge bg-light text-muted border">{{ __('ops.no_role') }}</span>
                                @endforelse
                            </td>
                            <td>{{ $u->branch?->name ?? __('ops.hq_all') }}</td>
                            <td>
                                @if($u->status === 'Active')
                                    <span class="badge badge-status-active">{{ __('ops.active') }}</span>
                                @else
                                    <span class="badge badge-status-cancelled">{{ __('ops.suspended') }}</span>
                                @endif
                            </td>
                            <td class="stack-actions text-end">
                                <div class="d-inline-flex gap-1">
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#elevateRoleModal{{ $u->id }}" title="{{ __('ops.elevate_roles') }}">
                                        <i class="bi bi-shield-check me-1"></i>{{ __('ops.elevate') }}
                                    </button>
                                    <form action="{{ route('users.toggle_status', $u->id) }}" method="POST" onsubmit="return confirm('{{ __('ops.confirm_toggle_status') }}');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm {{ $u->status === 'Active' ? 'btn-outline-danger' : 'btn-outline-success' }}" title="{{ $u->status === 'Active' ? __('ops.suspend_account') : __('ops.activate_account') }}" aria-label="{{ $u->status === 'Active' ? __('ops.suspend_account') : __('ops.activate_account') }}">
                                            <i class="bi {{ $u->status === 'Active' ? 'bi-slash-circle' : 'bi-check-circle' }}"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="stack-no-label">
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

@foreach($users as $u)
    <!-- Elevate Role Modal -->
    <div class="modal fade" id="elevateRoleModal{{ $u->id }}" tabindex="-1" aria-labelledby="elevateRoleModalLabel{{ $u->id }}">
        <div class="modal-dialog modal-dialog-scrollable modal-fullscreen-sm-down">
            <div class="modal-content">
                <form action="{{ route('users.elevate_role', $u->id) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="elevateRoleModalLabel{{ $u->id }}">{{ __('ops.elevate_roles') }}: {{ $u->name }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('app.cancel') }}"></button>
                    </div>
                    <div class="modal-body">
                        <p class="small text-muted mb-3">{{ __('ops.elevate_hint') }}</p>
                        <div class="d-flex flex-column gap-2">
                            @foreach($roles as $role)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="role_ids[]" value="{{ $role->id }}" id="role_{{ $u->id }}_{{ $role->id }}" @checked($u->roles->contains('id', $role->id))>
                                    <label class="form-check-label fw-semibold" for="role_{{ $u->id }}_{{ $role->id }}">
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
    </div>
@endforeach

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
                            <input type="text" id="newUserFirst" name="first_name" class="form-control" autocomplete="given-name" required>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label for="newUserLast" class="form-label small fw-semibold">{{ __('ops.last_name') }}</label>
                            <input type="text" id="newUserLast" name="last_name" class="form-control" autocomplete="family-name" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="newUserEmail" class="form-label small fw-semibold">{{ __('ops.email_address') }}</label>
                        <input type="email" id="newUserEmail" name="email" class="form-control" autocomplete="email" inputmode="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="newUserPhone" class="form-label small fw-semibold">{{ __('ops.phone_number') }}</label>
                        <input type="tel" id="newUserPhone" name="phone" class="form-control" data-tz-phone placeholder="0784 100 200" autocomplete="tel">
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-12 col-sm-6">
                            <label for="newUserRole" class="form-label small fw-semibold">{{ __('ops.primary_role') }}</label>
                            <select id="newUserRole" name="role_id" class="form-select" required>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}">{{ $role->display_name ?? $role->name }}</option>
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
@endsection
