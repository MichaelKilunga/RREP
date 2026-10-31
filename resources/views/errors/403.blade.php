@extends(auth()->check() ? 'layouts.app' : 'layouts.auth')

@section('title', __('app.no_access_title'))

@section('content')
<div class="card mx-auto my-4 my-md-5 max-w-xl">
    <div class="empty-state">
        <div class="empty-icon bg-danger-subtle text-danger"><i class="bi bi-shield-lock-fill"></i></div>
        <h4 class="brand-font">{{ __('app.no_access_title') }}</h4>
        <p class="mb-1">{{ __('app.no_access_text') }}</p>
        <p class="small mb-4">{{ __('app.no_access_hint') }}</p>
        <div class="d-grid d-sm-flex gap-2 justify-content-center">
            @auth
                <a href="{{ route('dashboard') }}" class="btn btn-primary"><i class="bi bi-house me-1"></i> {{ __('admin.return_dashboard') }}</a>
            @endauth
            <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('public.home') }}" class="btn btn-light border"><i class="bi bi-arrow-left me-1"></i> {{ __('pages.back') }}</a>
        </div>
    </div>
</div>
@endsection
