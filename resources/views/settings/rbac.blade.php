@extends('layouts.app')

@section('title', 'Role & Permission Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="brand-font mb-1"><i class="bi bi-shield-lock text-primary me-2"></i>Role & Permission Management Matrix (FM-003)</h3>
        <p class="text-muted small mb-0">Manage granular permissions assigned across all 9 enterprise system roles</p>
    </div>
    <a href="{{ route('settings.index') }}" class="btn btn-light border btn-sm"><i class="bi bi-arrow-left me-1"></i> Back to Settings</a>
</div>

<div class="row g-4">
    @foreach($roles as $role)
        <div class="col-12">
            <div class="card">
                <div class="card-header brand-font d-flex justify-content-between align-items-center bg-light">
                    <div>
                        <strong>{{ $role->display_name }}</strong> (<code>{{ $role->name }}</code>)
                        <div class="text-muted small fw-normal">{{ $role->description }}</div>
                    </div>
                    <span class="badge bg-primary">{{ $role->permissions->count() }} Permissions Active</span>
                </div>
                <div class="card-body">
                    <form action="{{ route('settings.rbac.update', $role) }}" method="POST">
                        @csrf
                        @foreach($permissions as $mod => $permList)
                            <div class="mb-3">
                                <div class="text-uppercase fw-bold text-muted small mb-2 border-bottom pb-1">{{ strtoupper($mod) }} Module</div>
                                <div class="row g-2">
                                    @foreach($permList as $perm)
                                        <div class="col-sm-6 col-md-4 col-lg-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $perm->id }}" id="p_{{ $role->id }}_{{ $perm->id }}" {{ $role->permissions->contains('id', $perm->id) ? 'checked' : '' }}>
                                                <label class="form-check-label small" for="p_{{ $role->id }}_{{ $perm->id }}">
                                                    {{ $perm->display_name }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                        <button type="submit" class="btn btn-primary btn-sm mt-2">Save Permissions for {{ $role->display_name }}</button>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection
