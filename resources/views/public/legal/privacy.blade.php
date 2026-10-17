@extends('layouts.public')

@section('title', __('pages.privacy_title') . ' - ' . setting('company_name', 'REMS'))
@section('meta_description', __('pages.privacy_subtitle', ['company' => setting('company_name', 'REMS')]))

@php $company = setting('company_name', 'REMS'); @endphp

@section('content')
<section class="page-hero py-4 mb-4">
    <div class="container">
        <h1 class="brand-font fw-bold mb-1">{{ __('pages.privacy_title') }}</h1>
        <p class="text-white-50 small mb-0">{{ __('pages.privacy_subtitle', ['company' => $company]) }}</p>
    </div>
</section>

<div class="container pb-5">
    <article class="card p-3 p-md-4 p-lg-5 rounded-4 bg-white shadow-sm max-w-850 mx-auto text-secondary leading-relaxed">
        <p class="text-muted small mb-4"><i class="bi bi-calendar-check me-1"></i> {{ __('pages.last_updated') }}: {{ setting('legal_last_updated', now()->format('d M Y')) }}</p>

        <h2 class="brand-font fs-5 text-dark mb-3">1. {{ __('pages.privacy_s1_title') }}</h2>
        <p>{{ __('pages.privacy_s1_body', ['company' => $company]) }}</p>

        <h2 class="brand-font fs-5 text-dark mt-4 mb-3">2. {{ __('pages.privacy_s2_title') }}</h2>
        <p>{{ __('pages.privacy_s2_body') }}</p>

        <h2 class="brand-font fs-5 text-dark mt-4 mb-3">3. {{ __('pages.privacy_s3_title') }}</h2>
        <p>{{ __('pages.privacy_s3_body') }}</p>

        <h2 class="brand-font fs-5 text-dark mt-4 mb-3">4. {{ __('pages.privacy_s4_title') }}</h2>
        <p>{{ __('pages.privacy_s4_body') }}</p>

        <h2 class="brand-font fs-5 text-dark mt-4 mb-3">5. {{ __('pages.privacy_s5_title') }}</h2>
        <p class="mb-0">{{ __('pages.privacy_s5_body') }} <a href="mailto:{{ setting('contact_email', 'privacy@rehospace.co.tz') }}" class="text-break">{{ setting('contact_email', 'privacy@rehospace.co.tz') }}</a>.</p>
    </article>

    <nav class="max-w-850 mx-auto mt-3 d-flex flex-wrap gap-3 small" aria-label="{{ __('pages.legal_pages') }}">
        <a href="{{ route('public.terms') }}" class="text-decoration-none">{{ __('app.terms_and_conditions') }}</a>
        <a href="{{ route('public.cookies') }}" class="text-decoration-none">{{ __('pages.cookies_title') }}</a>
        <a href="{{ route('public.contact') }}" class="text-decoration-none">{{ __('pages.contact_us') }}</a>
    </nav>
</div>
@endsection
