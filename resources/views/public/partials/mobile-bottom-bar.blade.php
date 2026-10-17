@php
    $barPhone = preg_replace('/\s+/', '', (string) setting('contact_phone', '+255784100200'));
    $barWhatsapp = 'https://wa.me/'.setting('contact_whatsapp', '255784100200').'?text='.urlencode(__('marketplace.whatsapp_interest', ['title' => $property->title, 'url' => url()->current()]));
@endphp
<div class="mobile-action-bar" role="toolbar" aria-label="{{ __('marketplace.inquire') }}">
    <div class="container px-2 d-flex gap-2">
        <a href="tel:{{ $barPhone }}" class="btn btn-outline-dark flex-fill fw-bold d-flex align-items-center justify-content-center gap-1 px-2">
            <i class="bi bi-telephone-fill text-primary"></i> {{ __('marketplace.call') }}
        </a>
        <a href="{{ $barWhatsapp }}" target="_blank" rel="noopener" class="btn btn-whatsapp flex-fill fw-bold d-flex align-items-center justify-content-center gap-1 px-2">
            <i class="bi bi-whatsapp"></i> {{ __('marketplace.whatsapp') }}
        </a>
        <button type="button" class="btn btn-primary flex-fill fw-bold d-flex align-items-center justify-content-center gap-1 px-2" data-bs-toggle="modal" data-bs-target="#inquiryModal">
            <i class="bi bi-calendar-check"></i> {{ __('marketplace.inquire') }}
        </button>
    </div>
</div>
