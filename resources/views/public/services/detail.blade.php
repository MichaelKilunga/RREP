@extends('layouts.public')

@section('title', $title)
@section('meta_description', __('pages.service_detail_meta', ['title' => $title]))

@section('content')
<section class="page-hero hero-img-survey py-4 py-md-5 text-center">
    <div class="container py-2 py-md-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb small justify-content-center mb-2 flex-nowrap text-nowrap overflow-auto">
                <li class="breadcrumb-item"><a href="{{ route('public.home') }}" class="text-white-50 text-decoration-none">{{ __('pages.home') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('public.services') }}" class="text-white-50 text-decoration-none">{{ __('pages.services') }}</a></li>
                <li class="breadcrumb-item active text-white text-truncate" aria-current="page">{{ $title }}</li>
            </ol>
        </nav>
        <h1 class="brand-font fw-bold mb-2">{{ $title }}</h1>
        <p class="lead text-white-50 mx-auto mb-0 max-w-2xl">{{ __('pages.service_detail_lead') }}</p>
    </div>
</section>

<div class="container py-4 py-md-5">
    @if($errors->any())
        <div class="alert alert-danger shadow-sm" role="alert">
            <strong>{{ __('pages.form_errors_title') }}</strong>
            <ul class="mb-0 small">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-12 col-lg-8 min-w-0">
            <div class="card p-3 p-md-4 p-lg-5 rounded-4 bg-white shadow-sm mb-4">
                <h2 class="brand-font fs-4 mb-3">{{ __('pages.about_service', ['title' => $title]) }}</h2>
                <p class="text-secondary leading-relaxed">{{ __('pages.service_detail_body', ['title' => $title]) }}</p>

                <h3 class="brand-font fs-5 mt-4 mb-3">{{ __('pages.key_highlights') }}</h3>
                <ul class="list-unstyled d-flex flex-column gap-2 text-muted mb-0">
                    <li class="d-flex gap-2"><i class="bi bi-check-circle-fill text-success"></i><span>{{ __('pages.highlight_due_diligence') }}</span></li>
                    <li class="d-flex gap-2"><i class="bi bi-check-circle-fill text-success"></i><span>{{ __('pages.highlight_market_analytics') }}</span></li>
                    <li class="d-flex gap-2"><i class="bi bi-check-circle-fill text-success"></i><span>{{ __('pages.highlight_account_manager') }}</span></li>
                    <li class="d-flex gap-2"><i class="bi bi-check-circle-fill text-success"></i><span>{{ __('pages.highlight_marketing') }}</span></li>
                </ul>
            </div>

            <div class="card p-3 p-md-4 rounded-4 bg-light border-0">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div class="min-w-0">
                        <h3 class="brand-font fs-6 mb-1">{{ __('pages.need_a_land_survey') }}</h3>
                        <p class="text-muted small mb-0">{{ __('pages.need_a_land_survey_help') }}</p>
                    </div>
                    <a href="{{ route('public.services.land_survey') }}" class="btn btn-outline-primary rounded-pill px-4 fw-bold flex-grow-1 flex-sm-grow-0">
                        <i class="bi bi-compass me-1"></i> {{ __('app.request_a_land_survey') }}
                    </a>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="sticky-lg-top" style="top: 84px;">
                <div class="card p-3 p-md-4 rounded-4 bg-white shadow-sm mb-3">
                    <h2 class="brand-font fs-6 mb-2">{{ __('pages.request_consultation') }}</h2>
                    <p class="text-muted small mb-3">{{ __('pages.request_consultation_help') }}</p>

                    <form action="{{ route('public.contact.submit') }}" method="POST">
                        @csrf
                        <input type="hidden" name="subject" value="Service Inquiry: {{ $title }}">

                        <div class="mb-3">
                            <label for="consultName" class="form-label small fw-bold text-muted">{{ __('pages.full_name') }} *</label>
                            <input type="text" id="consultName" name="name" class="form-control" required autocomplete="name" placeholder="Juma Rashid">
                        </div>
                        <div class="mb-3">
                            <label for="consultPhone" class="form-label small fw-bold text-muted">{{ __('pages.phone_whatsapp') }} *</label>
                            <input type="tel" id="consultPhone" name="phone" class="form-control" required data-tz-phone autocomplete="tel" placeholder="0784 100 200">
                        </div>
                        <div class="mb-3">
                            <label for="consultEmail" class="form-label small fw-bold text-muted">{{ __('pages.email_address') }} *</label>
                            <input type="email" id="consultEmail" name="email" class="form-control" required autocomplete="email" placeholder="juma@example.com">
                        </div>
                        <div class="mb-3">
                            <label for="consultMessage" class="form-label small fw-bold text-muted">{{ __('pages.service_requirements') }} *</label>
                            <textarea id="consultMessage" name="message" rows="3" class="form-control" required placeholder="{{ __('pages.service_requirements_placeholder') }}"></textarea>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary py-2 fw-bold shadow-sm">{{ __('pages.submit_consultation') }}</button>
                        </div>
                    </form>
                </div>

                <a href="https://wa.me/{{ setting('contact_whatsapp', '255784100200') }}?text={{ urlencode(__('pages.whatsapp_service_message', ['title' => $title])) }}" target="_blank" rel="noopener" class="btn btn-whatsapp w-100 py-3 fw-bold rounded-4 shadow-sm d-flex align-items-center justify-content-center gap-2">
                    <i class="bi bi-whatsapp fs-5"></i> {{ __('pages.chat_on_whatsapp') }}
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
