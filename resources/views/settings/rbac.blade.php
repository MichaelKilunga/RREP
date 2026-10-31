@extends('layouts.app')

@section('title', __('ops.rbac_title'))

@section('styles')
<style>
    .rbac-matrix th:first-child, .rbac-matrix td:first-child {
        position: sticky; left: 0; z-index: 1; background: var(--rrep-surface); min-width: 200px; max-width: 280px;
        box-shadow: 1px 0 0 var(--rrep-card-border);
    }
    .rbac-matrix thead th { position: sticky; top: 0; z-index: 2; background: var(--rrep-bg-subtle); }
    .rbac-matrix thead th:first-child { z-index: 3; }
    .rbac-matrix .form-check-input { width: 1.25rem; height: 1.25rem; margin: 0; cursor: pointer; }
    .rbac-matrix td.rbac-cell { text-align: center; min-width: 104px; }
    .rbac-module-row td { background: var(--rrep-bg-subtle); font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: var(--rrep-text-muted); }
    .rbac-scroll { max-height: 72vh; overflow: auto; }
</style>
@endsection

@section('content')
@php
    $permLabel = function ($perm) {
        $key = 'ops.perm_'.$perm->name;
        return __($key) !== $key ? __($key) : $perm->display_name;
    };
    $moduleLabel = function ($module) {
        $key = 'ops.perm_module_'.$module;
        return __($key) !== $key ? __($key) : ucfirst($module);
    };
@endphp

<div class="page-header">
    <div class="min-w-0">
        <h1 class="brand-font mb-1"><i class="bi bi-shield-lock text-primary me-2"></i>{{ __('ops.rbac_title') }}</h1>
        <p class="text-muted small mb-0">{{ __('ops.rbac_lead') }}</p>
    </div>
    <div class="page-actions">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newRoleModal"><i class="bi bi-plus-lg me-1"></i>{{ __('ops.new_role') }}</button>
        @can('view_users')
            <a href="{{ route('users.index') }}" class="btn btn-light border"><i class="bi bi-people me-1"></i>{{ __('ops.users_title') }}</a>
        @endcan
    </div>
</div>

<!-- Roles -->
<div class="row g-3 mb-4">
    @foreach($roles as $role)
        <div class="col-12 col-md-6 col-xl-4">
            <div class="card h-100 p-3">
                <div class="d-flex align-items-start justify-content-between gap-2 mb-1">
                    <div class="min-w-0">
                        <div class="fw-bold text-truncate">{{ $role->display_name }}</div>
                        <code class="small">{{ $role->name }}</code>
                    </div>
                    <div class="d-flex gap-1 flex-shrink-0">
                        @if($role->is_system)
                            <span class="badge bg-secondary-subtle text-secondary align-self-start" title="{{ __('ops.role_system_hint') }}"><i class="bi bi-lock-fill me-1"></i>{{ __('ops.role_system') }}</span>
                        @endif
                    </div>
                </div>
                <p class="text-muted small mb-2 line-clamp-2">{{ $role->description ?: __('ops.standard_role') }}</p>
                <div class="d-flex flex-wrap gap-2 small mb-3">
                    <span class="badge bg-primary-subtle text-primary"><i class="bi bi-people me-1"></i>{{ trans_choice('ops.role_users_count', $role->users_count, ['count' => $role->users_count]) }}</span>
                    <span class="badge bg-success-subtle text-success"><i class="bi bi-key me-1"></i>{{ $role->name === 'super_admin' ? __('ops.all_permissions') : __('ops.permissions_count', ['count' => $role->permissions->count()]) }}</span>
                </div>
                <div class="d-flex flex-wrap gap-1 mt-auto">
                    <button type="button" class="btn btn-light border btn-sm" data-bs-toggle="modal" data-bs-target="#editRoleModal{{ $role->id }}"><i class="bi bi-pencil text-primary me-1"></i>{{ __('app.edit') }}</button>
                    <button type="button" class="btn btn-light border btn-sm clone-role-btn" data-role-id="{{ $role->id }}" data-role-name="{{ $role->display_name }}"><i class="bi bi-copy me-1"></i>{{ __('ops.clone_role') }}</button>
                    @unless($role->is_system)
                        <form action="{{ route('settings.rbac.roles.destroy', $role) }}" method="POST" class="m-0" onsubmit="return confirm(@json(__('ops.role_delete_confirm')));">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-light border btn-sm" @disabled($role->users_count > 0) title="{{ $role->users_count > 0 ? __('ops.role_has_users', ['count' => $role->users_count]) : __('app.delete') }}"><i class="bi bi-trash text-danger me-1"></i>{{ __('app.delete') }}</button>
                        </form>
                    @endunless
                </div>
            </div>
        </div>

        <!-- Edit role -->
        <div class="modal fade" id="editRoleModal{{ $role->id }}" tabindex="-1" aria-labelledby="editRoleLabel{{ $role->id }}">
            <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
                <form class="modal-content" action="{{ route('settings.rbac.roles.update', $role) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title" id="editRoleLabel{{ $role->id }}">{{ __('ops.edit_role') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('app.cancel') }}"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold" for="roleName{{ $role->id }}">{{ __('ops.role_name') }}</label>
                            <input type="text" id="roleName{{ $role->id }}" name="display_name" class="form-control" value="{{ $role->display_name }}" required maxlength="100">
                        </div>
                        <div>
                            <label class="form-label small fw-semibold" for="roleDesc{{ $role->id }}">{{ __('ops.role_description') }}</label>
                            <textarea id="roleDesc{{ $role->id }}" name="description" rows="3" class="form-control" maxlength="255">{{ $role->description }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light border" data-bs-dismiss="modal">{{ __('app.cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('app.save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach
</div>

<!-- Permission matrix -->
@foreach($roles as $role)
    <form id="rbacForm{{ $role->id }}" action="{{ route('settings.rbac.update', $role) }}" method="POST">
        @csrf
    </form>
@endforeach

<div class="card">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="min-w-0">
            <h2 class="h6 brand-font mb-0">{{ __('ops.permission_matrix') }}</h2>
            <small class="text-muted">{{ __('ops.permission_matrix_hint') }}</small>
        </div>
        <div class="input-group input-group-sm" style="max-width: 260px;">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="search" id="rbacFilter" class="form-control" placeholder="{{ __('ops.filter_permissions') }}" aria-label="{{ __('ops.filter_permissions') }}">
        </div>
    </div>
    <p class="text-muted small d-md-none mb-0 px-3 pt-2"><i class="bi bi-arrow-left-right me-1"></i>{{ __('ops.swipe_to_see_roles') }}</p>
    <div class="table-responsive rbac-scroll">
        <table class="table table-bordered align-middle mb-0 small rbac-matrix">
            <thead>
                <tr>
                    <th>{{ __('ops.permission') }}</th>
                    @foreach($roles as $role)
                        <th class="text-center">
                            <div class="fw-bold">{{ $role->display_name }}</div>
                            <div class="badge bg-primary-subtle text-primary mt-1">{{ $role->name === 'super_admin' ? __('ops.all_permissions') : $role->permissions->count() }}</div>
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($permissions as $mod => $permList)
                    <tr class="rbac-module-row" data-module="{{ $mod }}">
                        <td>{{ $moduleLabel($mod) }}</td>
                        @foreach($roles as $role)
                            <td class="rbac-cell">
                                @if($role->name !== 'super_admin')
                                    <input class="form-check-input rbac-module-toggle" type="checkbox" data-role="{{ $role->id }}" data-module="{{ $mod }}" title="{{ __('ops.tick_whole_module') }}" aria-label="{{ __('ops.tick_whole_module') }}: {{ $moduleLabel($mod) }} – {{ $role->display_name }}">
                                @endif
                            </td>
                        @endforeach
                    </tr>
                    @foreach($permList as $perm)
                        <tr class="rbac-perm-row" data-module="{{ $mod }}" data-search="{{ strtolower($permLabel($perm).' '.$perm->name.' '.$mod) }}">
                            <td>
                                <div class="fw-semibold">{{ $permLabel($perm) }}</div>
                                <code class="text-muted" style="font-size: 0.68rem;">{{ $perm->name }}</code>
                            </td>
                            @foreach($roles as $role)
                                <td class="rbac-cell">
                                    @if($role->name === 'super_admin')
                                        <i class="bi bi-check-circle-fill text-success" title="{{ __('ops.super_admin_always_full') }}"></i>
                                    @else
                                        <input class="form-check-input rbac-perm" type="checkbox" form="rbacForm{{ $role->id }}" name="permissions[]" value="{{ $perm->id }}" data-role="{{ $role->id }}" data-module="{{ $mod }}" id="p_{{ $role->id }}_{{ $perm->id }}" @checked($role->permissions->contains('id', $perm->id)) aria-label="{{ $permLabel($perm) }} – {{ $role->display_name }}">
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                @empty
                    <tr>
                        <td colspan="{{ $roles->count() + 1 }}">
                            <div class="empty-state py-4">
                                <div class="empty-icon"><i class="bi bi-shield-lock"></i></div>
                                <h6>{{ __('ops.no_permissions') }}</h6>
                                <p class="small mb-0">{{ __('ops.no_permissions_hint') }}</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if($roles->count())
                <tfoot>
                    <tr>
                        <td class="fw-bold">{{ __('ops.save_role') }}</td>
                        @foreach($roles as $role)
                            <td class="rbac-cell">
                                @if($role->name !== 'super_admin')
                                    <button type="submit" form="rbacForm{{ $role->id }}" class="btn btn-primary btn-sm w-100"><i class="bi bi-save me-1"></i>{{ __('ops.save') }}</button>
                                @else
                                    <span class="text-muted small"><i class="bi bi-lock-fill"></i></span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
</div>

<!-- New role -->
<div class="modal fade" id="newRoleModal" tabindex="-1" aria-labelledby="newRoleLabel">
    <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
        <form class="modal-content" action="{{ route('settings.rbac.roles.store') }}" method="POST">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="newRoleLabel">{{ __('ops.new_role') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('app.cancel') }}"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label small fw-semibold" for="newRoleName">{{ __('ops.role_name') }} *</label>
                    <input type="text" id="newRoleName" name="display_name" class="form-control" required maxlength="100" placeholder="{{ __('ops.role_name_placeholder') }}">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold" for="newRoleDesc">{{ __('ops.role_description') }}</label>
                    <textarea id="newRoleDesc" name="description" rows="2" class="form-control" maxlength="255"></textarea>
                </div>
                <div>
                    <label class="form-label small fw-semibold" for="newRoleCopy">{{ __('ops.copy_permissions_from') }}</label>
                    <select id="newRoleCopy" name="copy_from" class="form-select">
                        <option value="">{{ __('ops.start_empty') }}</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}">{{ $role->display_name }}</option>
                        @endforeach
                    </select>
                    <div class="form-text">{{ __('ops.copy_permissions_hint') }}</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">{{ __('app.cancel') }}</button>
                <button type="submit" class="btn btn-primary">{{ __('ops.create_role') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const perms = Array.from(document.querySelectorAll('.rbac-perm'));

    function syncModuleToggles() {
        document.querySelectorAll('.rbac-module-toggle').forEach(function (toggle) {
            const boxes = perms.filter(p => p.dataset.role === toggle.dataset.role && p.dataset.module === toggle.dataset.module);
            const checked = boxes.filter(p => p.checked).length;
            toggle.checked = boxes.length > 0 && checked === boxes.length;
            toggle.indeterminate = checked > 0 && checked < boxes.length;
        });
    }

    document.querySelectorAll('.rbac-module-toggle').forEach(function (toggle) {
        toggle.addEventListener('change', function () {
            perms.filter(p => p.dataset.role === toggle.dataset.role && p.dataset.module === toggle.dataset.module)
                .forEach(p => { p.checked = toggle.checked; });
            syncModuleToggles();
        });
    });
    perms.forEach(p => p.addEventListener('change', syncModuleToggles));
    syncModuleToggles();

    // Filter permission rows; hide module headers with no visible rows.
    document.getElementById('rbacFilter').addEventListener('input', function () {
        const q = this.value.trim().toLowerCase();
        document.querySelectorAll('.rbac-perm-row').forEach(function (row) {
            row.classList.toggle('d-none', q !== '' && !row.dataset.search.includes(q));
        });
        document.querySelectorAll('.rbac-module-row').forEach(function (header) {
            const anyVisible = document.querySelector('.rbac-perm-row[data-module="' + header.dataset.module + '"]:not(.d-none)');
            header.classList.toggle('d-none', !anyVisible);
        });
    });

    // "Clone" opens the new-role dialog pre-set to copy that role.
    document.querySelectorAll('.clone-role-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('newRoleCopy').value = btn.dataset.roleId;
            document.getElementById('newRoleName').value = btn.dataset.roleName + ' (copy)';
            bootstrap.Modal.getOrCreateInstance(document.getElementById('newRoleModal')).show();
        });
    });
});
</script>
@endsection
