@extends('layouts.public')

@section('title', __('pages.contact_page_title'))
@section('meta_description', __('pages.contact_hero_lead'))

@php
    $contactPhone = setting('contact_phone', '+255 784 100 200');
    $whatsappNumber = setting('contact_whatsapp', '255784100200');
    $contactEmail = setting('contact_email', 'info@rehospace.co.tz');
    $contactAddress = setting('contact_address', 'Plot 42, Victoria Business Tower, New Bagamoyo Road, Dar es Salaam, Tanzania');
    $officeLat = (float) setting('contact_latitude', -6.7924);
    $officeLng = (float) setting('contact_longitude', 39.2083);
@endphp

@section('content')
<section class="page-hero hero-img-survey py-4 py-md-5 text-center">
    <div class="container py-2 py-md-3">
        <span class="badge bg-white bg-opacity-10 border border-white border-opacity-25 text-white px-3 py-2 rounded-pill mb-2">{{ __('pages.get_in_touch') }}</span>
        <h1 class="brand-font fw-bold mb-2">{{ __('pages.contact_title') }}</h1>
        <p class="lead text-white-50 mx-auto mb-0 max-w-2xl">{{ __('pages.contact_hero_lead') }}</p>
    </div>
</section>

<!-- Quick actions -->
<div class="container mt-4">
    <div class="row g-2 g-md-3">
        <div class="col-12 col-sm-4">
            <a href="tel:{{ preg_replace('/\s+/', '', $contactPhone) }}" class="card rounded-4 p-3 shadow-sm text-decoration-none h-100 d-flex flex-row align-items-center gap-3 hover-shadow transition">
                <span class="btn-icon bg-primary-subtle text-primary fs-5"><i class="bi bi-telephone-fill"></i></span>
                <span class="min-w-0">
                    <span class="d-block small text-muted">{{ __('pages.call_us') }}</span>
                    <strong class="text-dark">{{ format_phone_tz($contactPhone) }}</strong>
                </span>
            </a>
        </div>
        <div class="col-12 col-sm-4">
            <a href="https://wa.me/{{ $whatsappNumber }}?text={{ urlencode(__('pages.whatsapp_contact_message')) }}" target="_blank" rel="noopener" class="card rounded-4 p-3 shadow-sm text-decoration-none h-100 d-flex flex-row align-items-center gap-3 hover-shadow transition">
                <span class="btn-icon bg-success-subtle text-success fs-5"><i class="bi bi-whatsapp"></i></span>
                <span class="min-w-0">
                    <span class="d-block small text-muted">{{ __('pages.chat_on_whatsapp') }}</span>
                    <strong class="text-dark">{{ format_phone_tz($whatsappNumber) }}</strong>
                </span>
            </a>
        </div>
        <div class="col-12 col-sm-4">
            <a href="mailto:{{ $contactEmail }}" class="card rounded-4 p-3 shadow-sm text-decoration-none h-100 d-flex flex-row align-items-center gap-3 hover-shadow transition">
                <span class="btn-icon bg-info-subtle text-info fs-5"><i class="bi bi-envelope-fill"></i></span>
                <span class="min-w-0">
                    <span class="d-block small text-muted">{{ __('pages.email_us') }}</span>
                    <strong class="text-dark text-truncate d-block">{{ $contactEmail }}</strong>
                </span>
            </a>
        </div>
    </div>
</div>

<div class="container py-4 py-md-5">
    <div class="row g-4">
        <!-- Form -->
        <div class="col-12 col-lg-7 min-w-0">
            <div class="card p-3 p-md-4 p-lg-5 rounded-4 bg-white shadow-sm">
                <h2 class="brand-font fs-4 mb-2">{{ __('pages.send_direct_message') }}</h2>
                <p class="text-muted small mb-4">{{ __('pages.send_direct_message_help') }}</p>

                @if($errors->any())
                    <div class="alert alert-danger" role="alert">
                        <strong>{{ __('pages.form_errors_title') }}</strong>
                        <ul class="mb-0 small">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('public.contact.submit') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label for="contactName" class="form-label small fw-bold text-muted">{{ __('pages.full_name') }} *</label>
                            <input type="text" id="contactName" name="name" class="form-control" placeholder="Juma Rashid" autocomplete="name" value="{{ old('name') }}" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="contactPhone" class="form-label small fw-bold text-muted">{{ __('pages.phone_whatsapp') }} *</label>
                            <input type="tel" id="contactPhone" name="phone" class="form-control" placeholder="0784 100 200" data-tz-phone autocomplete="tel" value="{{ old('phone') }}" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="contactEmail" class="form-label small fw-bold text-muted">{{ __('pages.email_address') }} *</label>
                            <input type="email" id="contactEmail" name="email" class="form-control" placeholder="juma@example.com" autocomplete="email" value="{{ old('email') }}" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="contactRegion" class="form-label small fw-bold text-muted">{{ __('pages.region') }}</label>
                            <select id="contactRegion" name="region" class="form-select">
                                <option value="">{{ __('pages.select_region') }}</option>
                                @foreach(tz_regions() as $region)
                                    <option value="{{ $region }}" @selected(old('region') === $region)>{{ $region }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="contactSubject" class="form-label small fw-bold text-muted">{{ __('pages.subject_department') }} *</label>
                            <select id="contactSubject" name="subject" class="form-select" required>
                                <option value="General Inquiry" @selected(old('subject') === 'General Inquiry')>{{ __('pages.subject_general') }}</option>
                                <option value="Land Survey Support" @selected(old('subject') === 'Land Survey Support')>{{ __('pages.subject_survey') }}</option>
                                <option value="Property Listing Help" @selected(old('subject') === 'Property Listing Help')>{{ __('pages.subject_listing') }}</option>
                                <option value="Commercial Investment" @selected(old('subject') === 'Commercial Investment')>{{ __('pages.subject_investment') }}</option>
                                <option value="Technical Support" @selected(old('subject') === 'Technical Support')>{{ __('pages.subject_technical') }}</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="contactMessage" class="form-label small fw-bold text-muted">{{ __('pages.message_details') }} *</label>
                            <textarea id="contactMessage" name="message" rows="4" class="form-control" placeholder="{{ __('pages.message_placeholder') }}" required>{{ old('message') }}</textarea>
                        </div>
                        <div class="col-12 mt-4">
                            <div class="d-grid d-sm-block">
                                <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5 fw-bold shadow">
                                    <i class="bi bi-send-fill me-1"></i> {{ __('pages.send_message') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Office details -->
        <div class="col-12 col-lg-5">
            <div class="card p-3 p-md-4 rounded-4 bg-white shadow-sm mb-4">
                <h2 class="brand-font fs-5 mb-3">{{ __('pages.headquarters_contact') }}</h2>
                <div class="d-flex flex-column gap-3 text-secondary small">
                    <div class="d-flex align-items-start gap-3">
                        <i class="bi bi-geo-alt-fill text-danger fs-5"></i>
                        <div>
                            <strong class="d-block text-dark">{{ __('pages.physical_address') }}</strong>
                            {{ $contactAddress }}
                        </div>
                    </div>
                    <div class="d-flex align-items-start gap-3">
                        <i class="bi bi-telephone-fill text-success fs-5"></i>
                        <div>
                            <strong class="d-block text-dark">{{ __('pages.phone_help_desk') }}</strong>
                            <a href="tel:{{ preg_replace('/\s+/', '', $contactPhone) }}" class="text-decoration-none">{{ format_phone_tz($contactPhone) }}</a>
                        </div>
                    </div>
                    <div class="d-flex align-items-start gap-3">
                        <i class="bi bi-whatsapp text-success fs-5"></i>
                        <div>
                            <strong class="d-block text-dark">{{ __('pages.whatsapp_hotline') }}</strong>
                            <a href="https://wa.me/{{ $whatsappNumber }}" target="_blank" rel="noopener" class="text-decoration-none">{{ format_phone_tz($whatsappNumber) }}</a>
                        </div>
                    </div>
                    <div class="d-flex align-items-start gap-3">
                        <i class="bi bi-envelope-fill text-primary fs-5"></i>
                        <div class="min-w-0">
                            <strong class="d-block text-dark">{{ __('pages.email') }}</strong>
                            <a href="mailto:{{ $contactEmail }}" class="text-decoration-none text-break">{{ $contactEmail }}</a>
                        </div>
                    </div>
                    <div class="d-flex align-items-start gap-3">
                        <i class="bi bi-clock-fill text-muted fs-5"></i>
                        <div>
                            <strong class="d-block text-dark">{{ __('pages.office_hours') }} <span class="text-muted fw-normal">(EAT, UTC+3)</span></strong>
                            {{ setting_trans('contact_hours_weekdays', __('pages.hours_weekdays'), __('pages.hours_weekdays')) }}<br>
                            {{ setting_trans('contact_hours_saturday', __('pages.hours_saturday'), __('pages.hours_saturday')) }}<br>
                            <span class="text-muted">{{ __('pages.hours_sunday') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Office map -->
            <div class="card rounded-4 overflow-hidden shadow-sm mb-4">
                <div class="ratio ratio-16x9 bg-light">
                    <div id="contactOfficeMap" role="application" aria-label="{{ __('pages.office_map') }}"></div>
                </div>
                <div class="card-body p-3 d-flex flex-wrap align-items-center justify-content-between gap-2 small">
                    <span class="text-muted"><i class="bi bi-geo-alt me-1"></i> {{ __('pages.find_our_office') }}</span>
                    <a href="https://www.google.com/maps/search/?api=1&query={{ $officeLat }},{{ $officeLng }}" target="_blank" rel="noopener" class="fw-semibold text-decoration-none">
                        {{ __('pages.open_in_google_maps') }} <i class="bi bi-box-arrow-up-right ms-1"></i>
                    </a>
                </div>
            </div>

            <!-- Branches -->
            @if(isset($branches) && $branches->count())
                <div class="card p-3 p-md-4 rounded-4 bg-white shadow-sm">
                    <h2 class="brand-font fs-5 mb-3">{{ __('pages.regional_hubs') }}</h2>
                    <div class="d-flex flex-column gap-2">
                        @foreach($branches as $b)
                            <div class="p-2.5 rounded-3 bg-light border small">
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                                    <strong class="text-dark">{{ $b->name }}</strong>
                                    @if($b->city)<span class="badge bg-white text-secondary border">{{ $b->city }}</span>@endif
                                </div>
                                @if($b->address)
                                    <div class="text-muted"><i class="bi bi-geo-alt me-1"></i>{{ $b->address }}</div>
                                @endif
                                @if($b->phone)
                                    <a href="tel:{{ preg_replace('/\s+/', '', $b->phone) }}" class="text-decoration-none d-inline-block mt-1"><i class="bi bi-telephone me-1"></i>{{ format_phone_tz($b->phone) }}</a>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const lat = {{ $officeLat }};
    const lng = {{ $officeLng }};
    const map = L.map('contactOfficeMap', { scrollWheelZoom: false }).setView([lat, lng], 14);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);
    L.marker([lat, lng]).addTo(map).bindPopup(@json(setting('company_name', 'RehoSpace')) + '<br>' + @json($contactAddress));
});
</script>
@endsection
