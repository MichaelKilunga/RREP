@php
    $socialLinks = [
        'social_facebook' => ['label' => __('settings.social_facebook'), 'icon' => 'bi-facebook text-primary', 'placeholder' => 'https://facebook.com/yourpage'],
        'social_instagram' => ['label' => __('settings.social_instagram'), 'icon' => 'bi-instagram text-danger', 'placeholder' => 'https://instagram.com/yourhandle'],
        'social_threads' => ['label' => __('settings.social_threads'), 'icon' => 'bi-threads text-dark', 'placeholder' => 'https://threads.net/@yourhandle'],
        'social_pinterest' => ['label' => __('settings.social_pinterest'), 'icon' => 'bi-pinterest text-danger', 'placeholder' => 'https://pinterest.com/yourbusiness'],
        'social_google_business' => ['label' => __('settings.social_google_business'), 'icon' => 'bi-google text-primary', 'placeholder' => 'https://g.page/yourbusiness'],
        'social_tiktok' => ['label' => __('settings.social_tiktok'), 'icon' => 'bi-tiktok text-dark', 'placeholder' => 'https://tiktok.com/@yourhandle'],
    ];
@endphp

<form action="{{ route('settings.social') }}" method="POST">
    @csrf

    {{-- Corporate contact --}}
    <div class="card rounded-4 mb-3">
        <div class="card-header">
            <h5 class="brand-font mb-0"><i class="bi bi-building text-primary me-1.5"></i>{{ __('settings.contact_title') }}</h5>
            <small class="text-muted fw-normal">{{ __('settings.contact_subtitle') }}</small>
        </div>
        <div class="card-body p-3 p-md-4">
            <div class="row g-3">
                <div class="col-12 col-md-6 col-xl-4">
                    <label class="form-label fw-semibold" for="contactPhone">{{ __('settings.contact_phone') }}</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-telephone-fill text-primary"></i></span>
                        <input type="tel" name="contact_phone" class="form-control" id="contactPhone" data-tz-phone autocomplete="tel" placeholder="0784 100 200" value="{{ setting('contact_phone', '+255 784 100 200') }}">
                    </div>
                    <div class="form-text">{{ __('settings.contact_phone_help') }}</div>
                </div>
                <div class="col-12 col-md-6 col-xl-4">
                    <label class="form-label fw-semibold" for="contactWhatsapp">{{ __('settings.contact_whatsapp') }}</label>
                    <div class="input-group">
                        <span class="input-group-text text-success"><i class="bi bi-whatsapp"></i></span>
                        <input type="tel" name="contact_whatsapp" class="form-control" id="contactWhatsapp" data-tz-phone="e164" inputmode="numeric" placeholder="255784100200" value="{{ setting('contact_whatsapp', '255784100200') }}">
                    </div>
                    <div class="form-text">{{ __('settings.contact_whatsapp_help') }}</div>
                </div>
                <div class="col-12 col-md-6 col-xl-4">
                    <label class="form-label fw-semibold" for="contactEmail">{{ __('settings.contact_email') }}</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope-fill text-info"></i></span>
                        <input type="email" name="contact_email" class="form-control" id="contactEmail" autocomplete="email" inputmode="email" placeholder="info@company.co.tz" value="{{ setting('contact_email', 'info@avenix.co.tz') }}">
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold" for="contactAddress">{{ __('settings.contact_address') }}</label>
                    <input type="text" name="contact_address" class="form-control" id="contactAddress" autocomplete="street-address" placeholder="{{ __('settings.contact_address_placeholder') }}" value="{{ setting('contact_address', 'Plot 42, Victoria Business Tower, New Bagamoyo Road, Dar es Salaam') }}">
                    <div class="form-text">{{ __('settings.contact_address_help') }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Social profiles --}}
    <div class="card rounded-4 mb-3">
        <div class="card-header">
            <h5 class="brand-font mb-0"><i class="bi bi-diagram-3 text-warning me-1.5"></i>{{ __('settings.social_title') }}</h5>
            <small class="text-muted fw-normal">{{ __('settings.social_subtitle') }}</small>
        </div>
        <div class="card-body p-3 p-md-4">
            <div class="row g-3">
                @foreach($socialLinks as $name => $link)
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold" for="{{ Str::camel($name) }}">{{ $link['label'] }}</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi {{ $link['icon'] }}"></i></span>
                            <input type="url" name="{{ $name }}" class="form-control" id="{{ Str::camel($name) }}" inputmode="url" placeholder="{{ $link['placeholder'] }}" value="{{ setting($name, '') }}">
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Verification --}}
    <div class="card rounded-4 mb-3">
        <div class="card-header">
            <h5 class="brand-font mb-0"><i class="bi bi-patch-check-fill text-danger me-1.5"></i>{{ __('settings.verification_title') }}</h5>
        </div>
        <div class="card-body p-3 p-md-4">
            <label class="form-label fw-semibold" for="pinterestVerify">{{ __('settings.pinterest_verify') }}</label>
            <input type="text" name="pinterest_domain_verify" class="form-control font-monospace" id="pinterestVerify" autocomplete="off" placeholder="430d9ec3abe981ee91c5e53589d57da1" value="{{ setting('pinterest_domain_verify', '') }}">
            <div class="form-text">{{ __('settings.pinterest_verify_help') }}</div>
        </div>
        <div class="card-footer bg-white border-top-0 pt-0 pb-3 px-3 px-md-4">
            <div class="d-grid d-sm-flex justify-content-sm-end">
                <button type="submit" class="btn btn-primary fw-bold rounded-pill px-4 py-2 shadow-sm">
                    <i class="bi bi-save me-1"></i>{{ __('settings.save_social') }}
                </button>
            </div>
        </div>
    </div>
</form>
