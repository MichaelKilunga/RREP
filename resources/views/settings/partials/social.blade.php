@php
    $socialLinks = [
        'social_facebook' => ['label' => __('settings.social_facebook'), 'icon' => 'bi-facebook text-primary', 'placeholder' => 'https://facebook.com/yourpage'],
        'social_instagram' => ['label' => __('settings.social_instagram'), 'icon' => 'bi-instagram text-danger', 'placeholder' => 'https://instagram.com/yourhandle'],
        'social_threads' => ['label' => __('settings.social_threads'), 'icon' => 'bi-threads text-dark', 'placeholder' => 'https://threads.net/@yourhandle'],
        'social_pinterest' => ['label' => __('settings.social_pinterest'), 'icon' => 'bi-pinterest text-danger', 'placeholder' => 'https://pinterest.com/yourbusiness'],
        'social_google_business' => ['label' => __('settings.social_google_business'), 'icon' => 'bi-google text-primary', 'placeholder' => 'https://g.page/yourbusiness'],
        'social_tiktok' => ['label' => __('settings.social_tiktok'), 'icon' => 'bi-tiktok text-dark', 'placeholder' => 'https://tiktok.com/@yourhandle'],
    ];
    $officeLocationMessages = [
        'set' => __('settings.office_location_set'),
        'unreadable' => __('settings.office_location_unreadable'),
        'short_link' => __('settings.office_location_short_link'),
        'gps_wait' => __('settings.office_location_gps_wait'),
        'gps_failed' => __('settings.office_location_gps_failed'),
        'cleared' => __('settings.office_location_cleared'),
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

    {{-- Office location: the pin on the contact page map --}}
    <div class="card rounded-4 mb-3">
        <div class="card-header">
            <h5 class="brand-font mb-0"><i class="bi bi-geo-alt-fill text-danger me-1.5"></i>{{ __('settings.office_location_title') }}</h5>
            <small class="text-muted fw-normal">{{ __('settings.office_location_subtitle') }}</small>
        </div>
        <div class="card-body p-3 p-md-4">
            @if($errors->has('contact_latitude') || $errors->has('contact_longitude'))
                <div class="alert alert-danger small py-2" role="alert">{{ $errors->first('contact_latitude') ?: $errors->first('contact_longitude') }}</div>
            @endif
            <div class="row g-3">
                <div class="col-12 col-lg-5">
                    <label class="form-label fw-semibold" for="officeLocationPaste">{{ __('settings.office_location_paste') }}</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="officeLocationPaste" autocomplete="off" placeholder="https://www.google.com/maps/... / -6.1630, 35.7516">
                        <button type="button" class="btn btn-outline-primary fw-semibold" id="officeLocationApply">{{ __('settings.office_location_apply') }}</button>
                    </div>
                    <div class="form-text" id="officeLocationStatus">{{ __('settings.office_location_paste_help') }}</div>

                    <div class="row g-2 mt-2">
                        <div class="col-6">
                            <label class="form-label fw-semibold small" for="contactLatitude">{{ __('settings.latitude') }}</label>
                            <input type="number" step="any" min="-90" max="90" inputmode="decimal" name="contact_latitude" id="contactLatitude" class="form-control @error('contact_latitude') is-invalid @enderror" value="{{ old('contact_latitude', setting('contact_latitude', '')) }}" placeholder="-6.163000">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold small" for="contactLongitude">{{ __('settings.longitude') }}</label>
                            <input type="number" step="any" min="-180" max="180" inputmode="decimal" name="contact_longitude" id="contactLongitude" class="form-control @error('contact_longitude') is-invalid @enderror" value="{{ old('contact_longitude', setting('contact_longitude', '')) }}" placeholder="35.751600">
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2 mt-3">
                        <button type="button" class="btn btn-light border btn-sm fw-semibold" id="officeLocationGps"><i class="bi bi-crosshair me-1 text-primary"></i>{{ __('settings.office_location_gps') }}</button>
                        <button type="button" class="btn btn-light border btn-sm fw-semibold" id="officeLocationClear"><i class="bi bi-x-circle me-1 text-danger"></i>{{ __('settings.office_location_clear') }}</button>
                    </div>
                    <p class="small text-muted mt-3 mb-0"><i class="bi bi-info-circle me-1"></i>{{ __('settings.office_location_note') }}</p>
                </div>
                <div class="col-12 col-lg-7">
                    <div id="officeLocationMap" class="map-h-sm rounded-3 border" role="application" aria-label="{{ __('settings.office_location_title') }}"></div>
                    <div class="form-text">{{ __('settings.office_location_map_help') }}</div>
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    const mapEl = document.getElementById('officeLocationMap');
    if (!mapEl || !window.L) { return; }

    const latInput = document.getElementById('contactLatitude');
    const lngInput = document.getElementById('contactLongitude');
    const pasteInput = document.getElementById('officeLocationPaste');
    const status = document.getElementById('officeLocationStatus');
    const messages = @json($officeLocationMessages);
    const tanzaniaView = { center: [-6.369, 34.889], zoom: 6 };
    let map = null;
    let marker = null;

    function say(text, tone) {
        status.textContent = text;
        status.className = 'form-text' + (tone ? ' text-' + tone : '');
    }

    function currentPoint() {
        const lat = parseFloat(latInput.value);
        const lng = parseFloat(lngInput.value);
        return Number.isFinite(lat) && Number.isFinite(lng) && Math.abs(lat) <= 90 && Math.abs(lng) <= 180 ? [lat, lng] : null;
    }

    function setPoint(lat, lng, recenter) {
        latInput.value = lat.toFixed(6);
        lngInput.value = lng.toFixed(6);
        if (!map) { return; }
        if (marker) {
            marker.setLatLng([lat, lng]);
        } else {
            marker = L.marker([lat, lng], { draggable: true }).addTo(map);
            marker.on('dragend', function (e) {
                const p = e.target.getLatLng();
                setPoint(p.lat, p.lng, false);
                say(messages.set, 'success');
            });
        }
        if (recenter) { map.setView([lat, lng], Math.max(map.getZoom(), 16)); }
    }

    // Google Maps links carry the pin as !3d<lat>!4d<lng> (preferred over the
    // @<lat>,<lng> viewport centre) or as ?q= / ?query= / ?ll= parameters.
    function parseLocation(text) {
        const value = (text || '').trim();
        const patterns = [
            /!3d(-?\d{1,2}\.\d+)!4d(-?\d{1,3}\.\d+)/,
            /[?&](?:q|query|ll|destination|center)=(-?\d{1,2}\.\d+)(?:,|%2C)\s*(-?\d{1,3}\.\d+)/i,
            /@(-?\d{1,2}\.\d+),\s*(-?\d{1,3}\.\d+)/,
            /^(-?\d{1,2}(?:\.\d+)?)\s*[,;\s]\s*(-?\d{1,3}(?:\.\d+)?)$/,
        ];
        for (const re of patterns) {
            const m = value.match(re);
            if (m) {
                const lat = parseFloat(m[1]);
                const lng = parseFloat(m[2]);
                if (Math.abs(lat) <= 90 && Math.abs(lng) <= 180) { return [lat, lng]; }
            }
        }
        return null;
    }

    function applyPaste() {
        const value = pasteInput.value.trim();
        if (!value) { return; }
        const point = parseLocation(value);
        if (point) {
            setPoint(point[0], point[1], true);
            pasteInput.value = '';
            say(messages.set, 'success');
        } else if (/goo\.gl|maps\.app/i.test(value)) {
            say(messages.short_link, 'warning');
        } else {
            say(messages.unreadable, 'danger');
        }
    }

    function initMap() {
        if (map) { map.invalidateSize(); return; }
        const start = currentPoint();
        map = L.map(mapEl).setView(start || tanzaniaView.center, start ? 16 : tanzaniaView.zoom);
        const streets = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap contributors' }).addTo(map);
        const satellite = L.tileLayer('https://mt1.google.com/vt/lyrs=y&x={x}&y={y}&z={z}', { maxZoom: 20, attribution: '&copy; Google' });
        L.control.layers({ 'Map': streets, 'Satellite': satellite }, null, { position: 'topright' }).addTo(map);
        map.on('click', function (e) {
            setPoint(e.latlng.lat, e.latlng.lng, false);
            say(messages.set, 'success');
        });
        if (start) { setPoint(start[0], start[1], false); }
    }

    document.getElementById('officeLocationApply').addEventListener('click', applyPaste);
    pasteInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); applyPaste(); }
    });

    [latInput, lngInput].forEach(function (input) {
        input.addEventListener('change', function () {
            const point = currentPoint();
            if (point) { setPoint(point[0], point[1], true); }
        });
    });

    document.getElementById('officeLocationGps').addEventListener('click', function () {
        if (!navigator.geolocation) { say(messages.gps_failed, 'danger'); return; }
        say(messages.gps_wait);
        navigator.geolocation.getCurrentPosition(function (pos) {
            setPoint(pos.coords.latitude, pos.coords.longitude, true);
            say(messages.set, 'success');
        }, function () {
            say(messages.gps_failed, 'danger');
        }, { enableHighAccuracy: true, timeout: 15000 });
    });

    document.getElementById('officeLocationClear').addEventListener('click', function () {
        latInput.value = '';
        lngInput.value = '';
        if (marker && map) { map.removeLayer(marker); marker = null; }
        if (map) { map.setView(tanzaniaView.center, tanzaniaView.zoom); }
        say(messages.cleared, 'warning');
    });

    // Leaflet cannot measure a hidden tab, so build the map when the tab opens.
    const tabButton = document.getElementById('social-tab');
    if (tabButton) { tabButton.addEventListener('shown.bs.tab', initMap); }
    if (mapEl.offsetParent !== null) { initMap(); }
});
</script>
