@extends('layouts.app')

@section('title', 'Module Disabled')

@section('content')
<div class="card p-5 text-center mx-auto shadow-sm my-5" style="max-width: 600px;">
    <i class="bi bi-lock-fill text-warning display-3 mb-3"></i>
    <h4 class="brand-font mb-2">Module License Required</h4>
    <p class="text-muted">The module <strong>{{ $module }}</strong> is currently not active for your organization deployment.</p>
    <div>
        <a href="{{ route('dashboard') }}" class="btn btn-primary btn-sm"><i class="bi bi-arrow-left me-1"></i> Return to Dashboard</a>
    </div>
</div>
@endsection
