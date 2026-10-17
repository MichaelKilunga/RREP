@extends('layouts.app')

@section('title', __('ops.rbac_title'))

@section('styles')
<style>
    .rbac-matrix th:first-child, .rbac-matrix td:first-child {
        position: sticky; left: 0; z-index: 1; background: var(--rrep-surface); min-width: 180px; max-width: 240px;
        box-shadow: 1px 0 0 var(--rrep-card-border);
    }
    .rbac-matrix thead th { position: sticky; top: 0; z-index: 2; background: var(--rrep-bg-subtle); }
    .rbac-matrix thead th:first-child { z-index: 3; }
    .rbac-matrix .form-check-input { width: 1.25rem; height: 1.25rem; margin: 0; cursor: pointer; }
    .rbac-matrix td.rbac-cell { text-align: center; min-width: 92px; }
    .rbac-module-row td { background: var(--rrep-bg-subtle); font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--rrep-text-muted); }
    .rbac-scroll { max-height: 70vh; overflow: auto; }
</style>
@endsection

@section('content')
<div class="page-header">
    <div class="min-w-0">
        <h1 class="brand-font mb-1"><i class="bi bi-shield-lock text-primary me-2"></i>{{ __('ops.rbac_title') }}</h1>
        <p class="text-muted small mb-0">{{ __('ops.rbac_subtitle', ['roles' => $roles->count()]) }}</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('settings.index') }}" class="btn btn-light border"><i class="bi bi-arrow-left me-1"></i> {{ __('ops.back_to_settings') }}</a>
    </div>
</div>

@foreach($roles as $role)
    <form id="rbacForm{{ $role->id }}" action="{{ route('settings.rbac.update', $role) }}" method="POST">
        @csrf
    </form>
@endforeach

<p class="text-muted small d-md-none mb-2"><i class="bi bi-arrow-left-right me-1"></i>{{ __('ops.swipe_to_see_roles') }}</p>

<div class="card">
    <div class="table-responsive rbac-scroll">
        <table class="table table-bordered align-middle mb-0 small rbac-matrix">
            <thead>
                <tr>
                    <th>{{ __('ops.permission') }}</th>
                    @foreach($roles as $role)
                        <th class="text-center">
                            <div class="fw-bold">{{ $role->display_name }}</div>
                            <code class="small fw-normal">{{ $role->name }}</code>
                            <div class="badge bg-primary-subtle text-primary mt-1">{{ $role->permissions->count() }}</div>
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($permissions as $mod => $permList)
                    <tr class="rbac-module-row">
                        <td colspan="{{ $roles->count() + 1 }}">{{ strtoupper($mod) }} {{ __('ops.module') }}</td>
                    </tr>
                    @foreach($permList as $perm)
                        <tr>
                            <td class="fw-semibold">{{ $perm->display_name }}</td>
                            @foreach($roles as $role)
                                <td class="rbac-cell">
                                    <input class="form-check-input" type="checkbox" form="rbacForm{{ $role->id }}" name="permissions[]" value="{{ $perm->id }}" id="p_{{ $role->id }}_{{ $perm->id }}" {{ $role->permissions->contains('id', $perm->id) ? 'checked' : '' }} aria-label="{{ $perm->display_name }} – {{ $role->display_name }}">
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
                                <button type="submit" form="rbacForm{{ $role->id }}" class="btn btn-primary btn-sm w-100"><i class="bi bi-save me-1"></i>{{ __('ops.save') }}</button>
                            </td>
                        @endforeach
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
</div>

@if($roles->count())
    <div class="card mt-4">
        <div class="card-body">
            <h2 class="h6 brand-font mb-3">{{ __('ops.role_descriptions') }}</h2>
            <div class="row g-2">
                @foreach($roles as $role)
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="border rounded p-2.5 h-100">
                            <div class="fw-bold small">{{ $role->display_name }} <code class="fw-normal">{{ $role->name }}</code></div>
                            <div class="text-muted small">{{ $role->description ?: __('ops.standard_role') }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif
@endsection
