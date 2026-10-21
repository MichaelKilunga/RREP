@extends('layouts.public')

@section('title', __('pages.services_page_title'))
@section('meta_description', __('pages.services_hero_lead'))

@section('content')
<!-- Hero -->
<section class="page-hero hero-img-survey py-4 py-md-5">
    <div class="container text-center py-2 py-md-4">
        <span class="badge bg-white bg-opacity-10 border border-white border-opacity-25 text-white px-3 py-2 rounded-pill mb-2">
            <i class="bi bi-grid me-1"></i> {{ __('pages.what_we_do') }}
        </span>
        <h1 class="fw-extrabold mb-2">{{ __('pages.our_services') }}</h1>
        <p class="lead text-white-50 max-w-xl mx-auto mb-0">{{ __('pages.services_hero_lead') }}</p>
    </div>
</section>

<!-- Services grid -->
<section class="py-4 py-md-5 bg-white">
    <div class="container py-md-3">
        <div class="row g-3 g-md-4">
            @forelse($services as $index => $service)
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="card h-100 rounded-4 shadow-sm hover-shadow transition-all bg-white d-flex flex-column overflow-hidden">
                        @if($service->thumbnail_url)
                            <div class="ratio ratio-16x9 transition-zoom-parent">
                                <img src="{{ $service->thumbnail_url }}" alt="{{ $service->title }}" class="object-fit-cover transition-zoom" loading="lazy">
                            </div>
                        @endif
                        <div class="p-4 d-flex flex-column flex-grow-1 w-100">
                        <div class="flex-grow-1">
                            @unless($service->thumbnail_url)
                            <span class="touch-target rounded-3 bg-primary-subtle text-primary fs-4 mb-3">
                                <i class="bi {{ $service->icon }}"></i>
                            </span>
                            @endunless
                            <h2 class="fw-bold text-dark fs-6 mb-2">{{ $service->title }}</h2>
                            <p class="text-muted small mb-3 line-clamp-3">{{ $service->short_description }}</p>

                            @if(!empty($service->sub_services))
                                <div class="d-flex flex-wrap gap-1 mb-3">
                                    @foreach($service->sub_services as $sub)
                                        <span class="badge bg-light text-primary border border-primary-subtle fw-medium">{{ $sub }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <div class="pt-3 border-top d-flex align-items-center justify-content-between">
                            <span class="text-muted small fw-semibold">{{ __('pages.service_number', ['number' => str_pad($index + 1, 2, '0', STR_PAD_LEFT)]) }}</span>
@if($service->cta_url)
                                <a href="{{ $service->cta_url }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-semibold">{{ $service->cta_label }} <i class="bi bi-arrow-right ms-1"></i></a>
                            @else
                                <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-semibold" data-bs-toggle="modal" data-bs-target="#serviceModal" data-service="{{ $service->title }}">
                                    {{ $service->cta_label }} <i class="bi bi-arrow-right ms-1"></i>
                                </button>
                            @endif
                        </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="empty-state bg-light rounded-4 border">
                        <div class="empty-icon"><i class="bi bi-grid"></i></div>
                        <h5>{{ __('pages.no_services_yet') }}</h5>
                        <p class="small mb-3">{{ __('pages.no_services_help') }}</p>
                        <a href="{{ route('public.contact') }}" class="btn btn-primary rounded-pill px-4">{{ __('pages.contact_us') }}</a>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</section>

<!-- CTA -->
<section class="py-4 py-md-5 bg-dark text-white text-center bg-scene hero-img-survey">
    <div class="container py-md-3">
        <h2 class="fw-extrabold text-white mb-2">{{ __('pages.ready_to_work_with_us') }}</h2>
        <p class="lead text-white-50 mb-4 max-w-2xl mx-auto">{{ __('pages.ready_to_work_lead') }}</p>
        <div class="d-grid d-sm-flex justify-content-sm-center gap-2 gap-sm-3">
            <a href="{{ route('public.contact') }}" class="btn btn-primary btn-lg px-4 fw-bold rounded-pill">
                {{ __('pages.get_a_quote') }} <i class="bi bi-arrow-up-right-circle ms-1"></i>
            </a>
            <a href="https://wa.me/{{ setting('contact_whatsapp', '255784100200') }}?text={{ urlencode(__('pages.whatsapp_services_message')) }}" target="_blank" rel="noopener" class="btn btn-whatsapp btn-lg px-4 fw-bold rounded-pill">
                <i class="bi bi-whatsapp me-1"></i> {{ __('pages.whatsapp_us') }}
            </a>
        </div>
    </div>
</section>

<!-- Quote modal -->
<div class="modal fade" id="serviceModal" tabindex="-1" aria-labelledby="serviceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header border-0 bg-light rounded-top-4">
                <h5 class="modal-title fw-bold text-dark mb-0" id="serviceModalLabel">{{ __('pages.request_service_quote') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('app.cancel') }}"></button>
            </div>
            <div class="modal-body p-3 p-md-4">
                <form action="{{ route('public.inquire') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="serviceModalTitle" class="form-label fw-bold small">{{ __('pages.service_requested') }}</label>
                        <input type="text" id="serviceModalTitle" name="service_name" class="form-control bg-light" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="serviceName" class="form-label fw-bold small">{{ __('pages.full_name') }} *</label>
                        <input type="text" id="serviceName" name="name" class="form-control" required placeholder="Juma Rashid" autocomplete="name">
                    </div>
                    <div class="mb-3">
                        <label for="servicePhone" class="form-label fw-bold small">{{ __('pages.phone_whatsapp') }} *</label>
                        <input type="tel" id="servicePhone" name="phone" class="form-control" required placeholder="0784 100 200" data-tz-phone autocomplete="tel">
                    </div>
                    <div class="mb-3">
                        <label for="serviceMessage" class="form-label fw-bold small">{{ __('pages.specific_requirements') }} *</label>
                        <textarea id="serviceMessage" name="message" class="form-control" rows="3" required placeholder="{{ __('pages.service_requirements_placeholder') }}"></textarea>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary fw-bold py-2 rounded-pill">{{ __('pages.submit_quote_request') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const serviceModal = document.getElementById('serviceModal');
    if (serviceModal) {
        serviceModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const service = (button && button.getAttribute('data-service')) || @json(__('pages.general_service_request'));
            document.getElementById('serviceModalTitle').value = service;
        });
    }
});
</script>
@endsection
