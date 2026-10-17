@extends('layouts.auth')

@section('title', __('portal.login_title'))

@section('content')
@php
    $org = current_organization();
    $branding = $org?->branding ?: \App\Models\BrandingConfig::first();
    $companyName = setting('company_name', $org?->name ?? 'RehoSpace');
    $companySubtitle = setting('company_subtitle', $branding?->company_tagline ?? 'Real Estate Platform Enterprise Authentication');
    $brandMonogram = setting('brand_monogram', 'R');
    $currentLocale = app()->getLocale();
@endphp

<div class="auth-card p-4 p-md-5 mx-auto">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="{{ route('public.home') }}" class="small text-decoration-none text-muted touch-target justify-content-start">
            <i class="bi bi-arrow-left me-1"></i> {{ __('portal.back_to_website') }}
        </a>
        <div class="btn-group btn-group-sm" role="group" aria-label="{{ __('app.switch_language') }}">
            <a href="{{ route('lang.switch', 'sw') }}" class="btn btn-outline-secondary {{ $currentLocale === 'sw' ? 'active' : '' }}" @if($currentLocale === 'sw') aria-current="true" @endif>SW</a>
            <a href="{{ route('lang.switch', 'en') }}" class="btn btn-outline-secondary {{ $currentLocale === 'en' ? 'active' : '' }}" @if($currentLocale === 'en') aria-current="true" @endif>EN</a>
        </div>
    </div>

    <div class="text-center mb-4">
        @if(!empty($branding?->header_logo))
            <div class="mb-3">
                <img src="{{ $branding->header_logo }}" alt="{{ $companyName }}" class="img-fluid object-fit-contain" height="56" style="max-height: 56px;">
            </div>
        @else
            <div class="touch-target rounded-3 text-white bg-primary fw-extrabold fs-3 mb-3 shadow px-3">{{ $brandMonogram }}</div>
        @endif
        <h4 class="brand-font text-dark mb-1">{!! $companyName !!}</h4>
        <p class="text-muted small mb-0">{!! $companySubtitle !!}</p>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger p-2 small mb-3" role="alert">
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
            <label for="emailField" class="form-label small fw-semibold">{{ __('portal.email') }}</label>
            <div class="input-group">
                <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                <input type="email" name="email" id="emailField" class="form-control @error('email') is-invalid @enderror" inputmode="email" autocomplete="username" autocapitalize="none" spellcheck="false" placeholder="{{ __('portal.email_placeholder') }}" value="{{ is_production_mode() ? old('email') : old('email', 'admin@rehospace.com') }}" required autofocus>
            </div>
        </div>

        <div class="mb-3">
            <label for="passwordField" class="form-label small fw-semibold">{{ __('portal.password') }}</label>
            <div class="input-group">
                <span class="input-group-text bg-light"><i class="bi bi-lock"></i></span>
                <input type="password" name="password" id="passwordField" class="form-control @error('password') is-invalid @enderror" autocomplete="current-password" value="{{ is_production_mode() ? '' : 'password' }}" required>
                <button type="button" class="btn btn-outline-secondary" id="togglePassword" aria-controls="passwordField" aria-pressed="false" aria-label="{{ __('portal.show_password') }}" title="{{ __('portal.show_password') }}" data-label-show="{{ __('portal.show_password') }}" data-label-hide="{{ __('portal.hide_password') }}">
                    <i class="bi bi-eye"></i>
                </button>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <div class="form-check">
                <input type="checkbox" name="remember" class="form-check-input" id="rememberMe" checked>
                <label class="form-check-label small" for="rememberMe">{{ __('portal.remember_me') }}</label>
            </div>
            <a href="#" class="small text-decoration-none">{{ __('portal.forgot_password') }}</a>
        </div>

        <button type="submit" class="btn btn-primary w-100 touch-target fw-semibold mb-3">
            <i class="bi bi-box-arrow-in-right me-1"></i> {{ __('portal.sign_in_to') }} {{ $companyName }}
        </button>
    </form>

    <p class="text-center small text-muted mb-0">{{ __('portal.need_account') }}</p>

    @if (! is_production_mode())
    <!-- Quick Demo Logins -->
    <div class="border-top pt-3 mt-3 text-center">
        <div class="text-muted small fw-semibold mb-2"><i class="bi bi-lightning-charge me-1"></i>{{ __('portal.quick_login') }}</div>
        <div class="d-flex flex-wrap gap-1 justify-content-center">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="fillLogin('admin@rehospace.com')">Super Admin</button>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="fillLogin('agent@rehospace.com')">Sales Agent</button>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="fillLogin('surveyor@rehospace.com')">GIS Surveyor</button>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="fillLogin('finance@rehospace.com')">Accountant</button>
        </div>
    </div>
    @endif
</div>

@if (! is_production_mode())
<script>
function fillLogin(email) {
    document.getElementById('emailField').value = email;
    document.getElementById('passwordField').value = 'password';
}
</script>
@endif
<script>
document.addEventListener('DOMContentLoaded', function () {
    var toggle = document.getElementById('togglePassword');
    var field = document.getElementById('passwordField');
    if (!toggle || !field) {
        return;
    }
    toggle.addEventListener('click', function () {
        var reveal = field.type === 'password';
        field.type = reveal ? 'text' : 'password';
        toggle.setAttribute('aria-pressed', reveal ? 'true' : 'false');
        var label = reveal ? toggle.getAttribute('data-label-hide') : toggle.getAttribute('data-label-show');
        toggle.setAttribute('aria-label', label);
        toggle.setAttribute('title', label);
        toggle.querySelector('i').className = reveal ? 'bi bi-eye-slash' : 'bi bi-eye';
    });
});
</script>
@endsection
