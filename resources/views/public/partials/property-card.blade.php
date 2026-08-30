@php
    $image = $p->primary_image_url;
    $isLand = in_array($p->propertyType?->category, ['Land', 'Agricultural']);
@endphp

<div class="card property-card h-100 shadow-sm border-0">
    <!-- Image Wrapper with 16:10 ratio & Badges -->
    <div class="property-card-img-wrapper">
        <a href="{{ route('public.properties.show', $p) }}">
            <img src="{{ $image }}" alt="{{ $p->title }}" loading="lazy" onerror="this.src='https://images.unsplash.com/photo-1580587771525-78b9dba3b914?w=600&auto=format&fit=crop&q=80'">
        </a>

        <!-- Top Badges -->
        <div class="position-absolute top-0 start-0 m-3 d-flex flex-column gap-1">
            <span class="badge {{ $p->listing_type === 'Rent' ? 'bg-warning text-dark' : 'bg-primary text-white' }} px-3 py-2 fw-bold shadow-sm" style="font-size: 0.75rem;">
                For {{ $p->listing_type }}
            </span>
            @if($p->is_featured)
                <span class="badge bg-danger text-white px-2 py-1 shadow-sm" style="font-size: 0.7rem;">
                    <i class="bi bi-star-fill me-1"></i> Featured
                </span>
            @endif
        </div>

        <!-- Top Right Actions -->
        <div class="position-absolute top-0 end-0 m-3 d-flex flex-column gap-2">
            <button type="button" class="favorite-btn" data-property-id="{{ $p->id }}" title="Save to Favorites">
                <i class="bi bi-heart"></i>
            </button>
            <button type="button" class="favorite-btn share-property-btn" data-url="{{ route('public.properties.show', $p) }}" data-title="{{ $p->title }}" title="Share Property">
                <i class="bi bi-share"></i>
            </button>
        </div>

        <!-- Bottom Price Badge -->
        <div class="position-absolute bottom-0 start-0 m-3">
            <span class="badge bg-dark bg-opacity-90 text-white p-2 px-3 fs-6 fw-bold shadow">
                {{ $p->formatted_price }}
            </span>
        </div>
    </div>

    <!-- Card Body -->
    <div class="card-body p-3 p-md-4 d-flex flex-column">
        <!-- Category & Verification -->
        <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="badge bg-light text-secondary border fw-medium" style="font-size: 0.75rem;">
                {{ $p->propertyType?->name ?? 'Real Estate' }}
            </span>
            <span class="badge badge-verified" data-bs-toggle="tooltip" title="Verified Title Deed and Cadastral Survey Status">
                <i class="bi bi-patch-check-fill me-1 text-success"></i> Verified
            </span>
        </div>

        <!-- Title -->
        <h5 class="brand-font mb-1 text-truncate-2" style="font-size: 1.05rem; line-height: 1.35; min-height: 2.7rem;">
            <a href="{{ route('public.properties.show', $p) }}" class="text-dark text-decoration-none hover-primary">
                {{ $p->title }}
            </a>
        </h5>

        <!-- Location -->
        <p class="text-muted small mb-3 text-truncate">
            <i class="bi bi-geo-alt-fill text-danger me-1"></i> {{ $p->address }}, {{ $p->city }}
        </p>

        <!-- Specifications Grid -->
        <div class="mt-auto border-top pt-3 text-muted small">
            <div class="row g-2 text-center">
                @if($isLand || $p->landParcel)
                    <div class="col-4 border-end">
                        <i class="bi bi-aspect-ratio text-primary d-block mb-1"></i>
                        <span class="fw-semibold text-dark">{{ $p->area_size ? $p->area_size . ' ' . $p->area_unit : ($p->landParcel ? $p->landParcel->acreage . ' Acres' : '-') }}</span>
                    </div>
                    <div class="col-4 border-end">
                        <i class="bi bi-compass text-success d-block mb-1"></i>
                        <span class="fw-semibold text-dark">Surveyed</span>
                    </div>
                    <div class="col-4">
                        <i class="bi bi-shield-check text-info d-block mb-1"></i>
                        <span class="fw-semibold text-dark">Titled</span>
                    </div>
                @else
                    <div class="col-4 border-end">
                        <i class="bi bi-door-open text-primary d-block mb-1"></i>
                        <span class="fw-semibold text-dark">{{ $p->bedrooms ? $p->bedrooms . ' Beds' : '-' }}</span>
                    </div>
                    <div class="col-4 border-end">
                        <i class="bi bi-droplet text-info d-block mb-1"></i>
                        <span class="fw-semibold text-dark">{{ $p->bathrooms ? $p->bathrooms . ' Baths' : '-' }}</span>
                    </div>
                    <div class="col-4">
                        <i class="bi bi-arrows-fullscreen text-secondary d-block mb-1"></i>
                        <span class="fw-semibold text-dark">{{ $p->area_size ? $p->area_size . ' ' . $p->area_unit : '-' }}</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Card Footer -->
    <div class="card-footer bg-white border-top p-3 d-flex gap-2">
        <a href="{{ route('public.properties.show', $p) }}" class="btn btn-outline-primary btn-sm flex-grow-1 fw-bold">
            View Property
        </a>
        <a href="https://wa.me/{{ setting('contact_whatsapp', '255784100200') }}?text={{ urlencode('Hello, I am interested in property: ' . $p->title . ' (' . route('public.properties.show', $p) . ')') }}" target="_blank" class="btn btn-success btn-sm px-3" title="Chat on WhatsApp">
            <i class="bi bi-whatsapp"></i>
        </a>
    </div>
</div>
