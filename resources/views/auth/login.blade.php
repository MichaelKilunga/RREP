@extends('layouts.auth')

@section('content')
<div class="auth-card p-4 p-md-5 mx-auto">
    <div class="text-center mb-4">
        <div class="rounded-3 bg-primary text-white d-inline-flex align-items-center justify-content-center mb-3 shadow" style="width: 50px; height: 50px; font-weight: 800; font-size: 1.5rem;">
            R
        </div>
        <h4 class="brand-font text-dark mb-1">{{ current_organization()?->name ?? 'RehoSpace' }}</h4>
        <p class="text-muted small">Real Estate Platform Enterprise Authentication</p>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger p-2 small mb-3">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('login.post') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label small fw-semibold">Email Address</label>
            <div class="input-group">
                <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                <input type="email" name="email" id="emailField" class="form-control" value="admin@rehospace.com" required autofocus>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label small fw-semibold">Password</label>
            <div class="input-group">
                <span class="input-group-text bg-light"><i class="bi bi-lock"></i></span>
                <input type="password" name="password" id="passwordField" class="form-control" value="password" required>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="form-check">
                <input type="checkbox" name="remember" class="form-check-input" id="rememberMe" checked>
                <label class="form-check-label small" for="rememberMe">Remember me</label>
            </div>
            <a href="#" class="small text-decoration-none">Forgot password?</a>
        </div>

        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold mb-3">
            <i class="bi bi-box-arrow-in-right me-1"></i> Sign In to RehoSpace
        </button>
    </form>

    <!-- Quick Demo Logins -->
    <div class="border-top pt-3 text-center">
        <div class="text-muted small fw-semibold mb-2">⚡ Quick 1-Click Role Login:</div>
        <div class="d-flex flex-wrap gap-1 justify-content-center">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="fillLogin('admin@rehospace.com')">Super Admin</button>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="fillLogin('agent@rehospace.com')">Sales Agent</button>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="fillLogin('surveyor@rehospace.com')">GIS Surveyor</button>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="fillLogin('finance@rehospace.com')">Accountant</button>
        </div>
    </div>
</div>

<script>
function fillLogin(email) {
    document.getElementById('emailField').value = email;
    document.getElementById('passwordField').value = 'password';
}
</script>
@endsection
