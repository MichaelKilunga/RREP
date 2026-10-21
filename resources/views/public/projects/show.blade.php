@extends('layouts.public')

@section('title', $project->title . ' - ' . __('pages.project_page_title_suffix'))
@section('meta_description', Str::limit(strip_tags($project->description ?: __('app.prime_surveyed_land_project_with_complete')), 160))
@section('body_class', 'has-action-bar')

@section('styles')
<style>
    /* View-scoped: full-height plot map with a side drawer (lg+) that becomes a bottom sheet on phones. */
    .map-interactive-wrapper { min-height: 60vh; }
    #projectPlotMap {
        width: 100%;
        height: calc(100vh - 150px);
        height: calc(100dvh - 150px);
        min-height: 480px;
        z-index: 1;
    }
    .plot-drawer {
        width: 380px;
        max-width: 100%;
        border-left: 1px solid var(--rrep-card-border);
        background: var(--rrep-surface);
        box-shadow: -4px 0 20px rgba(15, 23, 42, 0.08);
        max-height: calc(100dvh - 150px);
        overflow-y: auto;
    }
    .plot-drawer .drawer-handle { display: none; }
    .map-legend {
        position: absolute;
        bottom: 1rem;
        left: 1rem;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(8px);
        padding: 0.5rem 0.85rem;
        border-radius: var(--rrep-radius-md);
        box-shadow: var(--rrep-shadow-md);
        z-index: 1000;
        font-size: 0.78rem;
        font-weight: 600;
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem 0.85rem;
        max-width: calc(100% - 2rem);
    }
    .legend-box {
        display: inline-block;
        width: 14px;
        height: 14px;
        border-radius: 3px;
        margin-right: 6px;
        vertical-align: middle;
    }
    @media (max-width: 991.98px) {
        #projectPlotMap {
            height: 55vh;
            height: 55dvh;
            min-height: 300px;
        }
        .plot-drawer {
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            width: 100%;
            max-height: 72vh;
            max-height: 72dvh;
            border-left: 0;
            border-top: 1px solid var(--rrep-card-border);
            border-radius: var(--rrep-radius-lg) var(--rrep-radius-lg) 0 0;
            box-shadow: 0 -8px 30px rgba(15, 23, 42, 0.18);
            z-index: var(--rrep-z-bottom-nav);
            transform: translateY(calc(100% - 64px - var(--rrep-safe-bottom)));
            transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            padding-bottom: calc(1rem + var(--rrep-safe-bottom));
        }
        .plot-drawer.is-open { transform: translateY(0); }
        .plot-drawer .drawer-handle {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
            width: 100%;
            min-height: 64px;
            background: transparent;
            border: 0;
            text-align: left;
        }
        .plot-drawer .drawer-handle::before {
            content: "";
            position: absolute;
            top: 6px;
            left: 50%;
            width: 44px;
            height: 4px;
            margin-left: -22px;
            border-radius: 999px;
            background: var(--rrep-card-border);
        }
        .map-legend { bottom: 0.75rem; left: 0.75rem; font-size: 0.72rem; }

        /* Attention pulse: the closed bottom sheet glows until the visitor opens it or taps a plot */
        .plot-drawer.attention-pulse:not(.is-open) {
            animation: drawerGlow 1.1s ease-in-out infinite;
        }
        .plot-drawer.attention-pulse:not(.is-open) .drawer-handle .badge {
            animation: drawerBadgePulse 1.1s ease-in-out infinite;
        }
    }
    @keyframes drawerGlow {
        0%, 100% { box-shadow: 0 -8px 30px rgba(15, 23, 42, 0.18), 0 0 0 0 rgba(var(--rrep-primary-rgb), 0.45); }
        50% { box-shadow: 0 -10px 36px rgba(var(--rrep-primary-rgb), 0.95), 0 0 0 6px rgba(var(--rrep-primary-rgb), 0.18); }
    }
    @keyframes drawerBadgePulse {
        0%, 100% { transform: scale(1); background-color: rgba(var(--rrep-primary-rgb), 0.15) !important; color: var(--rrep-primary) !important; box-shadow: 0 0 0 0 rgba(var(--rrep-primary-rgb), 0.55); }
        50% { transform: scale(1.18); background-color: var(--rrep-primary) !important; color: #fff !important; box-shadow: 0 0 0 6px rgba(var(--rrep-primary-rgb), 0); }
    }
    @media (prefers-reduced-motion: reduce) {
        .plot-drawer.attention-pulse:not(.is-open),
        .plot-drawer.attention-pulse:not(.is-open) .drawer-handle .badge { animation: none; }
    }
</style>
@endsection

@section('content')
@unless($project->is_published)
    <div class="alert alert-warning border-0 rounded-0 mb-0 text-center small fw-semibold">
        <i class="bi bi-eye-slash me-1"></i>{{ __('admin.draft_preview_notice') }}
    </div>
@endunless
<!-- Top bar -->
<div class="bg-white border-bottom py-2 px-3 shadow-sm">
    <div class="page-header mb-0">
        <div class="d-flex align-items-center gap-2 min-w-0">
            <a href="{{ route('public.projects') }}" class="btn btn-outline-dark btn-sm rounded-pill px-3 fw-bold flex-shrink-0">
                <i class="bi bi-arrow-left me-1"></i> {{ __('pages.back') }}
            </a>
            <div class="min-w-0">
                <h1 class="fw-bold text-dark mb-0 fs-6 text-truncate">{{ $project->title }}</h1>
                <small class="text-muted d-block text-truncate">
                    <i class="bi bi-geo-alt-fill text-danger me-1"></i>{{ $project->council ?? $project->location_name }}
                    @if($project->developer_name)
                        <span> &middot; <i class="bi bi-building me-1"></i>{{ $project->developer_name }}</span>
                    @endif
                </small>
            </div>
        </div>
        <div class="page-actions align-items-center">
            <span class="badge bg-success px-3 py-2 rounded-pill fw-bold align-self-center">
                {{ $project->available_units }} {{ __('app.plots_available') }}
            </span>
            <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold" data-bs-toggle="modal" data-bs-target="#inquirePlotModal" data-plot-info="{{ $project->title }}">
                <i class="bi bi-chat-left-text me-1"></i> {{ __('app.inquire_project') }}
            </button>
        </div>
    </div>
</div>

@if($project->isServiceProject())
<!-- Service / contract project showcase -->
<section class="py-4 py-md-5 bg-light">
    <div class="container">
        <div class="row g-4">
            <div class="col-12 col-lg-8">
                @if(count($project->gallery))
                    <div class="row g-2 g-md-3">
                        @foreach($project->gallery as $image)
                            <div class="{{ $loop->first ? 'col-12' : 'col-6 col-md-4' }}">
                                <a href="{{ $image }}" class="d-block rounded-4 overflow-hidden border transition-zoom-parent" data-bs-toggle="modal" data-bs-target="#galleryLightbox" data-image="{{ $image }}" aria-label="{{ $project->title }} - {{ $loop->iteration }}">
                                    <div class="{{ $loop->first ? 'ratio ratio-16x9' : 'ratio ratio-4x3' }}">
                                        <img src="{{ $image }}" alt="{{ $project->title }} - {{ $loop->iteration }}" class="object-fit-cover transition-zoom" loading="lazy">
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="ratio ratio-16x9 rounded-4 overflow-hidden border">
                        <img src="{{ $project->hero_image_url }}" alt="{{ $project->title }}" class="object-fit-cover">
                    </div>
                @endif

                @if($project->description)
                    <div class="card rounded-4 p-3 p-md-4 mt-3">
                        <h2 class="h5 brand-font mb-2">{{ __('pages.about_project') }}</h2>
                        <p class="text-muted mb-0" style="white-space: pre-line;">{{ $project->description }}</p>
                    </div>
                @endif
            </div>

            <div class="col-12 col-lg-4">
                <div class="card rounded-4 p-3 p-md-4 sticky-sidebar">
                    @php
                        $phaseMap = [
                            'Upcoming' => ['label' => __('pages.phase_upcoming'), 'class' => 'bg-info-subtle text-info'],
                            'Ongoing' => ['label' => __('pages.phase_ongoing'), 'class' => 'bg-warning-subtle text-warning-emphasis'],
                            'Completed' => ['label' => __('pages.phase_completed'), 'class' => 'bg-success-subtle text-success'],
                        ];
                        $phase = $phaseMap[$project->project_status] ?? ['label' => $project->project_status, 'class' => 'bg-light text-dark border'];
                    @endphp
                    <span class="badge {{ $phase['class'] }} align-self-start rounded-pill px-3 py-2 fw-bold mb-3">{{ $phase['label'] }}</span>
                    <dl class="row small mb-3">
                        @if($project->service)
                            <dt class="col-5 text-muted">{{ __('pages.service_label') }}</dt>
                            <dd class="col-7 fw-semibold">{{ $project->service->title }}</dd>
                        @endif
                        @if($project->client_name)
                            <dt class="col-5 text-muted">{{ __('pages.client_label') }}</dt>
                            <dd class="col-7 fw-semibold">{{ $project->client_name }}</dd>
                        @endif
                        <dt class="col-5 text-muted">{{ __('admin.field_location_name') }}</dt>
                        <dd class="col-7 fw-semibold">{{ $project->location_name }}, {{ $project->city }}</dd>
                        <dt class="col-5 text-muted">{{ __('admin.developer') }}</dt>
                        <dd class="col-7 fw-semibold">{{ $project->developer_name }}</dd>
                    </dl>
                    <button type="button" class="btn btn-primary w-100 fw-bold rounded-pill mb-2" data-bs-toggle="modal" data-bs-target="#inquirePlotModal">
                        <i class="bi bi-chat-left-text me-1"></i> {{ __('app.inquire_project') }}
                    </button>
                    <a href="https://wa.me/{{ setting('contact_whatsapp', '255784100200') }}?text={{ urlencode(__('pages.whatsapp_project_text', ['title' => $project->title]).' '.route('public.projects.show', $project->slug)) }}" target="_blank" rel="noopener" class="btn btn-whatsapp w-100 fw-bold rounded-pill">
                        <i class="bi bi-whatsapp me-1"></i> WhatsApp
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Gallery lightbox: images open in-page, not in a new tab -->
<div class="modal fade" id="galleryLightbox" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content bg-transparent border-0 shadow-none">
            <div class="text-end mb-2">
                <button type="button" class="btn btn-light btn-sm rounded-pill px-3 fw-bold" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-1"></i>{{ __('pages.close') }}
                </button>
            </div>
            <img src="" alt="{{ $project->title }}" id="galleryLightboxImg" class="img-fluid rounded-4 w-100" style="max-height: 82vh; object-fit: contain;">
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var lightboxImg = document.getElementById('galleryLightboxImg');
        document.querySelectorAll('[data-bs-target="#galleryLightbox"]').forEach(function (trigger) {
            trigger.addEventListener('click', function () {
                lightboxImg.src = this.dataset.image;
            });
        });
    });
</script>
@else
<!-- Map + drawer -->
<div class="d-flex flex-column flex-lg-row position-relative overflow-hidden map-interactive-wrapper">
    <div class="flex-grow-1 position-relative min-w-0">
        <div id="projectPlotMap" role="application" aria-label="{{ __('pages.plot_map') }}"></div>

        <div class="map-legend" aria-label="{{ __('pages.legend') }}">
            <span><span class="legend-box bg-danger"></span>{{ __('pages.legend_sold') }}</span>
            <span><span class="legend-box bg-info"></span>{{ __('pages.legend_available') }}</span>
            <span><span class="legend-box bg-warning"></span>{{ __('pages.legend_reserved') }}</span>
        </div>
    </div>

    <!-- Plot details drawer / bottom sheet -->
    <aside class="plot-drawer attention-pulse px-3 pb-3 d-flex flex-column" id="plotDrawer" aria-label="{{ __('pages.project_details') }}">
        <button type="button" class="drawer-handle position-relative pt-3" id="drawerToggle" aria-controls="plotDrawer" aria-expanded="false">
            <span class="fw-bold text-dark" id="drawerTitleMobile">{{ __('pages.project_details') }}</span>
            <span class="d-flex align-items-center gap-2">
                <span class="badge bg-light text-dark fw-bold" id="drawerPlotBadgeMobile">{{ __('pages.select_a_plot') }}</span>
                <i class="bi bi-chevron-up text-muted"></i>
            </span>
        </button>

        <div class="d-none d-lg-flex align-items-center justify-content-between border-bottom pb-2 mb-3 pt-3">
            <h2 class="fw-bold text-dark mb-0 fs-6" id="drawerTitle">{{ __('pages.project_details') }}</h2>
            <span class="badge bg-light text-dark fw-bold" id="drawerPlotBadge">{{ __('pages.select_a_plot') }}</span>
        </div>

        <div id="emptyDrawerState">
            <div class="empty-state py-3">
                <div class="empty-icon"><i class="bi bi-cursor-fill"></i></div>
                <h6>{{ __('pages.tap_any_plot') }}</h6>
                <p class="small mb-0">{{ __('pages.tap_any_plot_help') }}</p>
            </div>

            <!-- Project summary (details kept off the cards live here) -->
            <div class="border-top pt-3">
                <h3 class="fs-6 fw-bold mb-2">{{ __('pages.project_summary') }}</h3>
                <div class="row g-2 text-center mb-3">
                    <div class="col-4">
                        <div class="bg-light rounded-3 py-2">
                            <small class="d-block text-muted text-uppercase fw-bold" style="font-size: 0.62rem;">{{ __('app.total_2') }}</small>
                            <strong class="text-dark">{{ $project->total_units }}</strong>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="bg-light rounded-3 py-2">
                            <small class="d-block text-muted text-uppercase fw-bold" style="font-size: 0.62rem;">{{ __('app.sold') }}</small>
                            <strong class="text-danger">{{ $project->sold_plots }}</strong>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="bg-light rounded-3 py-2">
                            <small class="d-block text-muted text-uppercase fw-bold" style="font-size: 0.62rem;">{{ __('app.available') }}</small>
                            <strong class="text-success">{{ $project->available_units }}</strong>
                        </div>
                    </div>
                </div>
                <dl class="row small mb-2">
                    <dt class="col-6 text-muted fw-semibold">{{ __('app.starting_price') }}</dt>
                    <dd class="col-6 text-end fw-bold text-primary">{{ format_currency($project->starting_price, null, 0) }}</dd>
                    @if((int) $project->initial_payment_percent > 0)
                        <dt class="col-6 text-muted fw-semibold">{{ __('app.deposit') }}</dt>
                        <dd class="col-6 text-end fw-bold">{{ (int) $project->initial_payment_percent }}%</dd>
                    @endif
                </dl>
                @php $projectAmenities = array_filter((array) $project->amenities_json); @endphp
                @if(count($projectAmenities))
                    <div class="d-flex flex-wrap gap-1 mb-1">
                        @foreach($projectAmenities as $amenity)
                            <span class="badge bg-light text-secondary border rounded-pill fw-medium">{{ $amenity }}</span>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div id="plotDetailsContent" class="d-none">
            <div class="table-responsive">
                <table class="table table-sm table-borderless align-middle mb-0 small">
                    <tbody>
                        <tr><td class="text-muted fw-semibold py-1">{{ __('pages.locality') }}</td><td class="fw-bold text-dark py-1 text-end" id="attrLocality">-</td></tr>
                        <tr><td class="text-muted fw-semibold py-1">{{ __('pages.block_number') }}</td><td class="fw-bold text-dark py-1 text-end" id="attrBlock">-</td></tr>
                        <tr><td class="text-muted fw-semibold py-1">{{ __('pages.plot_number') }}</td><td class="fw-bold text-dark py-1 text-end" id="attrLotNo">-</td></tr>
                        <tr><td class="text-muted fw-semibold py-1">{{ __('pages.legal_area_sqm') }}</td><td class="fw-bold text-danger py-1 text-end" id="attrArea">-</td></tr>
                        <tr><td class="text-muted fw-semibold py-1">{{ __('pages.region') }}</td><td class="fw-bold text-dark py-1 text-end" id="attrRegion">-</td></tr>
                        <tr><td class="text-muted fw-semibold py-1">{{ __('pages.district') }}</td><td class="fw-bold text-dark py-1 text-end" id="attrDistrict">-</td></tr>
                        <tr><td class="text-muted fw-semibold py-1">{{ __('pages.council') }}</td><td class="fw-bold text-dark py-1 text-end" id="attrCouncil">-</td></tr>
                        <tr><td class="text-muted fw-semibold py-1">{{ __('pages.plot_status') }}</td><td class="text-end py-1"><span class="badge bg-success" id="attrStatus">{{ __('app.available') }}</span></td></tr>
                        <tr><td class="text-muted fw-semibold py-1">{{ __('pages.land_use') }}</td><td class="fw-bold text-dark py-1 text-end" id="attrLandUse">-</td></tr>
                        <tr><td class="text-muted fw-semibold py-1">{{ __('pages.ownership_status') }}</td><td class="fw-bold text-dark py-1 text-end" id="attrTitleDeed">-</td></tr>
                        <tr><td class="text-muted fw-semibold py-1">{{ __('app.price') }}</td><td class="fw-extrabold text-danger py-1 text-end fs-6" id="attrPrice">-</td></tr>
                        <tr><td class="text-muted fw-semibold py-1">{{ __('pages.application_fee') }}</td><td class="fw-bold text-dark py-1 text-end" id="attrAppFee">{{ format_currency(20000, null, 0) }}</td></tr>
                    </tbody>
                </table>
            </div>

            <div class="d-flex gap-2 mt-3">
                <a href="#" id="googleMapsBtn" target="_blank" rel="noopener" class="btn btn-outline-secondary btn-sm flex-fill fw-bold rounded-pill">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Google Maps
                </a>
            </div>
        </div>

        <div id="plotActionContainer" class="pt-3 mt-auto border-top d-none">
            <button type="button" id="inquirePlotBtn" class="btn btn-primary w-100 btn-lg fw-bold rounded-pill shadow-sm">
                <i class="bi bi-bookmark-check-fill me-1"></i> {{ __('pages.inquire_reserve_plot') }}
            </button>
        </div>
    </aside>
</div>
@endif


<!-- Inquire modal (plot or whole project) -->
<div class="modal fade" id="inquirePlotModal" tabindex="-1" aria-labelledby="inquirePlotModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header border-0 bg-light rounded-top-4">
                <h5 class="modal-title fw-bold text-dark mb-0" id="inquirePlotModalLabel">{{ __('pages.inquire_land_plot') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('app.cancel') }}"></button>
            </div>
            <div class="modal-body p-3 p-md-4">
                <form action="{{ route('public.inquire') }}" method="POST">
                    @csrf
                    <input type="hidden" name="project_id" value="{{ $project->id }}">
                    <div class="mb-3">
                        <label for="modalPlotTitle" class="form-label fw-bold small">{{ __('pages.project_and_plot') }}</label>
                        <input type="text" id="modalPlotTitle" name="plot_info" class="form-control bg-light" value="{{ $project->title }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="plotInquiryName" class="form-label fw-bold small">{{ __('pages.full_name') }} *</label>
                        <input type="text" id="plotInquiryName" name="name" class="form-control" required placeholder="Juma Rashid" autocomplete="name">
                    </div>
                    <div class="mb-3">
                        <label for="plotInquiryPhone" class="form-label fw-bold small">{{ __('pages.phone_whatsapp') }} *</label>
                        <input type="tel" id="plotInquiryPhone" name="phone" class="form-control" required placeholder="0784 100 200" data-tz-phone autocomplete="tel">
                    </div>
                    <div class="mb-3">
                        <label for="plotInquiryChannel" class="form-label fw-bold small">{{ __('pages.preferred_contact_channel') }}</label>
                        <select id="plotInquiryChannel" name="preferred_contact_method" class="form-select">
                            <option value="WhatsApp">WhatsApp</option>
                            <option value="Phone">{{ __('pages.phone_call') }}</option>
                            <option value="Email">{{ __('pages.email') }}</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="plotInquiryMessage" class="form-label fw-bold small">{{ __('pages.your_message') }} *</label>
                        <textarea id="plotInquiryMessage" name="message" rows="3" class="form-control" required>{{ __('pages.project_inquiry_default_message', ['title' => $project->title]) }}</textarea>
                        <div class="form-text">{{ __('pages.mobile_money_hint') }}</div>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary fw-bold py-2 rounded-pill">{{ __('pages.submit_plot_request') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@unless($project->isServiceProject())
<script>
let map, geojsonLayer, selectedFeature = null;
const rawGeoJson = {!! json_encode($geojsonCollection) !!};
const projectLat = {{ $project->latitude ?? -4.2185 }};
const projectLng = {{ $project->longitude ?? 35.7485 }};
const plotLabel = @json(__('pages.plot'));
const plotDetailsLabel = @json(__('pages.plot_details'));
const currencySymbol = @json(config('app.default_currency_symbol', 'TSh'));
const statusLabels = @json(['Available' => __('app.available'), 'Sold' => __('app.sold'), 'Reserved' => __('pages.legend_reserved')]);

function money(value, fallback) {
    const amount = value ? Number(value) : fallback;
    return currencySymbol + ' ' + amount.toLocaleString('en-US');
}

function openDrawer(open) {
    const drawer = document.getElementById('plotDrawer');
    const toggle = document.getElementById('drawerToggle');
    drawer.classList.toggle('is-open', open);
    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    const icon = toggle.querySelector('i');
    if (icon) {
        icon.className = open ? 'bi bi-chevron-down text-muted' : 'bi bi-chevron-up text-muted';
    }
}

document.addEventListener('DOMContentLoaded', function () {
    map = L.map('projectPlotMap').setView([projectLat, projectLng], 15);

    const osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    const satelliteLayer = L.tileLayer('https://mt1.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
        maxZoom: 20,
        attribution: '&copy; Google Maps'
    });

    L.control.layers({ 'OpenStreetMap': osmLayer, 'Satellite': satelliteLayer }, null, { position: 'topright' }).addTo(map);

    if (rawGeoJson && rawGeoJson.features && rawGeoJson.features.length > 0) {
        geojsonLayer = L.geoJSON(rawGeoJson, {
            style: function (feature) {
                const status = feature.properties.status || 'Available';
                let fillColor = '#38bdf8';
                if (status === 'Sold') fillColor = '#ef4444';
                if (status === 'Reserved') fillColor = '#f59e0b';
                return { fillColor: fillColor, weight: 1.5, opacity: 1, color: '#ffffff', fillOpacity: 0.65 };
            },
            onEachFeature: function (feature, layer) {
                layer.on('click', function (e) {
                    highlightFeature(e);
                    displayPlotDetails(feature.properties);
                });
            }
        }).addTo(map);

        try {
            map.fitBounds(geojsonLayer.getBounds(), { padding: [30, 30] });
        } catch (err) {}
    }

    document.getElementById('drawerToggle').addEventListener('click', function () {
        document.getElementById('plotDrawer').classList.remove('attention-pulse');
        openDrawer(!document.getElementById('plotDrawer').classList.contains('is-open'));
    });

    // Pre-fill the modal title when opened from the top bar (whole project inquiry).
    const inquireModal = document.getElementById('inquirePlotModal');
    inquireModal.addEventListener('show.bs.modal', function (event) {
        const trigger = event.relatedTarget;
        if (trigger && trigger.getAttribute('data-plot-info')) {
            document.getElementById('modalPlotTitle').value = trigger.getAttribute('data-plot-info');
        }
    });
});

function highlightFeature(e) {
    if (selectedFeature) {
        geojsonLayer.resetStyle(selectedFeature);
    }
    selectedFeature = e.target;
    selectedFeature.setStyle({ weight: 3, color: '#0f172a', fillOpacity: 0.85 });
    selectedFeature.bringToFront();
}

function displayPlotDetails(props) {
    // A plot is now selected: the drawer stops being a project summary.
    document.getElementById('drawerTitle').textContent = plotDetailsLabel;
    document.getElementById('drawerTitleMobile').textContent = plotDetailsLabel;
    document.getElementById('plotDrawer').classList.remove('attention-pulse');
    document.getElementById('emptyDrawerState').classList.add('d-none');
    document.getElementById('plotDetailsContent').classList.remove('d-none');
    document.getElementById('plotActionContainer').classList.remove('d-none');

    const badgeText = plotLabel + ' ' + (props.parcel_number || '-');
    document.getElementById('drawerPlotBadge').textContent = badgeText;
    document.getElementById('drawerPlotBadgeMobile').textContent = badgeText;
    document.getElementById('attrLocality').textContent = props.locality || @json($project->location_name);
    document.getElementById('attrBlock').textContent = props.block_number || 'Block A';
    document.getElementById('attrLotNo').textContent = props.parcel_number || '1';
    document.getElementById('attrArea').textContent = (props.area_sqm ? Number(props.area_sqm).toLocaleString('en-US') : '600') + ' m²';
    document.getElementById('attrRegion').textContent = props.region || @json($project->region);
    document.getElementById('attrDistrict').textContent = props.district || @json($project->district);
    document.getElementById('attrCouncil').textContent = props.council || @json($project->council);

    const status = props.status || 'Available';
    const statusBadge = document.getElementById('attrStatus');
    statusBadge.textContent = statusLabels[status] || status;
    statusBadge.className = 'badge ' + (status === 'Sold' ? 'bg-danger' : (status === 'Reserved' ? 'bg-warning text-dark' : 'bg-success'));

    document.getElementById('attrLandUse').textContent = props.land_use || 'Makazi / Residential';
    document.getElementById('attrTitleDeed').textContent = props.title_deed_type || 'Right of Occupancy';
    document.getElementById('attrPrice').textContent = money(props.price, 15000000);
    document.getElementById('attrAppFee').textContent = money(props.application_fee, 20000);

    document.getElementById('googleMapsBtn').href = 'https://www.google.com/maps/search/?api=1&query=' + projectLat + ',' + projectLng;

    const inquireBtn = document.getElementById('inquirePlotBtn');
    inquireBtn.onclick = function () {
        document.getElementById('modalPlotTitle').value = @json($project->title) + ' - ' + plotLabel + ' ' + props.parcel_number + ' (' + (props.block_number || 'Block A') + ')';
        const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('inquirePlotModal'));
        modal.show();
    };

    openDrawer(true);
}


</script>
@endunless
@endsection
