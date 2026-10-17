@php
    $showUrl = route('public.properties.show', $p);
    $shortPrice = format_money_short($p->price, $p->currency === 'TZS' ? null : $p->currency);
    $statusSlug = strtolower(str_replace([' ', '-'], '', (string) $p->status));
    $statusKey = 'marketplace.status_'.strtolower(str_replace([' ', '-'], '_', (string) $p->status));
    $statusLabel = \Illuminate\Support\Facades\Lang::has($statusKey) ? __($statusKey) : $p->status;
    $areaLabel = $p->area_size
        ? rtrim(rtrim(number_format((float) $p->area_size, 2), '0'), '.').' '.$p->area_unit
        : ($p->landParcel ? rtrim(rtrim(number_format((float) $p->landParcel->acreage, 2), '0'), '.').' '.__('marketplace.acres') : __('marketplace.surveyed_plot'));
    $whatsappUrl = 'https://wa.me/'.setting('contact_whatsapp', '255784100200').'?text='.urlencode(__('marketplace.whatsapp_interest', ['title' => $p->title, 'url' => $showUrl]));
@endphp

<article class="card property-card h-100 border-0 shadow-sm">
    <div class="property-card-img-wrapper">
        <a href="{{ $showUrl }}" aria-label="{{ $p->title }}">
            <img src="{{ $p->primary_image_url }}" alt="{{ $p->title }}" loading="lazy" decoding="async" width="600" height="375" onerror="this.onerror=null;this.src='{{ asset('images/property-placeholder.jpg') }}'">
        </a>

        <div class="position-absolute top-0 start-0 m-2 d-flex flex-column align-items-start gap-1">
            <span class="badge bg-success rounded-pill px-2.5 py-1.5 fw-semibold shadow-sm">
                <i class="bi bi-map-fill me-1"></i>{{ __('marketplace.land_plots') }}
            </span>
            @if($p->is_featured)
                <span class="badge bg-danger rounded-pill px-2.5 py-1.5 shadow-sm"><i class="bi bi-star-fill me-1"></i>{{ __('marketplace.featured') }}</span>
            @endif
        </div>

        <div class="position-absolute top-0 end-0 m-2 d-flex flex-column gap-1.5">
            <button type="button" class="favorite-btn" data-property-id="{{ $p->id }}" aria-label="{{ __('marketplace.save_property') }}" title="{{ __('marketplace.save_property') }}">
                <i class="bi bi-heart"></i>
            </button>
            <button type="button" class="btn btn-light btn-icon shadow-sm share-property-btn" data-url="{{ $showUrl }}" data-title="{{ $p->title }}" aria-label="{{ __('marketplace.share_property') }}" title="{{ __('marketplace.share_property') }}">
                <i class="bi bi-share"></i>
            </button>
            <button type="button" class="btn btn-light btn-icon shadow-sm compare-toggle-btn" data-property-id="{{ $p->id }}" aria-label="{{ __('marketplace.compare_property') }}" title="{{ __('marketplace.compare_property') }}">
                <i class="bi bi-columns-gap"></i>
            </button>
        </div>

        <div class="position-absolute bottom-0 start-0 m-2">
            <span class="badge bg-dark bg-opacity-90 text-white rounded-pill px-3 py-2 fs-6 fw-bold shadow" title="{{ $p->formatted_price }}">{{ $shortPrice }}</span>
        </div>
    </div>

    <div class="card-body p-3 d-flex flex-column">
        <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
            <span class="badge bg-success-subtle text-success border border-success-subtle fw-medium text-truncate">
                {{ $p->landParcel?->title_deed_type ?? __('marketplace.titled') }}
            </span>
            <span class="badge badge-verified flex-shrink-0" title="{{ __('marketplace.verified_tooltip') }}">
                <i class="bi bi-check-circle-fill me-1"></i>{{ __('marketplace.cadastral_verified') }}
            </span>
        </div>

        <h3 class="h6 brand-font mb-1 line-clamp-2">
            <a href="{{ $showUrl }}" class="text-dark text-decoration-none hover-primary">{{ $p->title }}</a>
        </h3>

        <p class="text-muted small mb-2 text-truncate">
            <i class="bi bi-geo-alt-fill text-danger me-1"></i>{{ $p->address }}, {{ $p->city }}
        </p>

        <div class="mt-auto border-top pt-2">
            <div class="row g-1 text-center small">
                <div class="col-4 border-end">
                    <small class="text-muted d-block text-uppercase fw-semibold">{{ __('marketplace.plot_area') }}</small>
                    <strong class="text-dark">{{ $areaLabel }}</strong>
                </div>
                <div class="col-4 border-end">
                    <small class="text-muted d-block text-uppercase fw-semibold">{{ __('marketplace.zoning') }}</small>
                    <strong class="text-dark">{{ $p->landParcel?->zoning ?? __('marketplace.mixed_use') }}</strong>
                </div>
                <div class="col-4">
                    <small class="text-muted d-block text-uppercase fw-semibold">{{ __('marketplace.beacons') }}</small>
                    <strong class="text-success">{{ __('marketplace.gps_fixed') }}</strong>
                </div>
            </div>
        </div>

        <div class="d-flex align-items-center justify-content-between gap-2 mt-2 small">
            <span class="badge-status-{{ $statusSlug }}">{{ $statusLabel }}</span>
            <span class="text-muted text-truncate">{{ $p->landParcel?->parcel_number ?? $p->property_code }}</span>
        </div>
    </div>

    <div class="card-footer bg-white border-top p-2.5 d-flex gap-2">
        <a href="{{ $showUrl }}" class="btn btn-outline-success btn-sm flex-grow-1 fw-bold">
            {{ __('marketplace.view_land_details') }}
        </a>
        <a href="{{ route('public.services.land_survey') }}" class="btn btn-light btn-sm border btn-icon" aria-label="{{ __('marketplace.request_survey') }}" title="{{ __('marketplace.request_survey') }}">
            <i class="bi bi-compass text-primary"></i>
        </a>
        <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener" class="btn btn-whatsapp btn-sm px-3" aria-label="{{ __('marketplace.chat_whatsapp') }}" title="{{ __('marketplace.chat_whatsapp') }}">
            <i class="bi bi-whatsapp"></i>
        </a>
    </div>
</article>
