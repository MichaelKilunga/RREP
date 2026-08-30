<div class="fixed-bottom bg-white border-top p-2 d-md-none shadow-lg" style="z-index: 1020;">
    <div class="container d-flex gap-2">
        <a href="tel:{{ setting('contact_phone', '+255784100200') }}" class="btn btn-outline-dark flex-fill py-2 fw-bold d-flex align-items-center justify-content-center gap-1">
            <i class="bi bi-telephone-fill text-primary"></i> Call
        </a>
        <a href="https://wa.me/{{ setting('contact_whatsapp', '255784100200') }}?text={{ urlencode('Hello, I am interested in: ' . $property->title . ' (' . url()->current() . ')') }}" target="_blank" class="btn btn-success flex-fill py-2 fw-bold d-flex align-items-center justify-content-center gap-1">
            <i class="bi bi-whatsapp"></i> WhatsApp
        </a>
        <button type="button" class="btn btn-primary flex-fill py-2 fw-bold d-flex align-items-center justify-content-center gap-1" data-bs-toggle="modal" data-bs-target="#inquiryModal">
            <i class="bi bi-calendar-check"></i> Inquire
        </button>
    </div>
</div>
