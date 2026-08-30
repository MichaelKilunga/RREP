<div class="card property-card h-100 shadow-sm border-0">
    <div class="property-card-img-wrapper">
        <a href="{{ route('public.properties.show', $p) }}">
            <img src="{{ $p->primary_image_url }}" alt="{{ $p->title }}" loading="lazy" onerror="this.src='https://images.unsplash.com/photo-1500382017468-9049fed747ef?w=600&auto=format&fit=crop&q=80'">
        </a>
        <div class="position-absolute top-0 start-0 m-3">
            <span class="badge bg-success text-white px-3 py-2 fw-bold shadow-sm" style="font-size: 0.75rem;">
                <i class="bi bi-map-fill me-1"></i> Land & Plots
            </span>
        </div>
        <div class="position-absolute top-0 end-0 m-3">
            <button type="button" class="favorite-btn" data-property-id="{{ $p->id }}">
                <i class="bi bi-heart"></i>
            </button>
        </div>
        <div class="position-absolute bottom-0 start-0 m-3">
            <span class="badge bg-dark bg-opacity-90 text-white p-2 px-3 fs-6 fw-bold shadow">
                {{ $p->formatted_price }}
            </span>
        </div>
    </div>

    <div class="card-body p-3 p-md-4 d-flex flex-column">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="badge bg-success-subtle text-success border border-success-subtle fw-medium" style="font-size: 0.75rem;">
                {{ $p->landParcel?->title_deed_type ?? 'Surveyed Deed' }}
            </span>
            <span class="badge badge-verified">
                <i class="bi bi-check-circle-fill me-1 text-success"></i> Cadastral Verified
            </span>
        </div>

        <h5 class="brand-font mb-1" style="font-size: 1.05rem; line-height: 1.35; min-height: 2.7rem;">
            <a href="{{ route('public.properties.show', $p) }}" class="text-dark text-decoration-none hover-primary">
                {{ $p->title }}
            </a>
        </h5>

        <p class="text-muted small mb-3">
            <i class="bi bi-geo-alt-fill text-danger me-1"></i> {{ $p->address }}, {{ $p->city }}
        </p>

        <div class="mt-auto border-top pt-3 text-muted small">
            <div class="row g-2 text-center">
                <div class="col-4 border-end">
                    <span class="text-muted d-block" style="font-size: 0.7rem;">Land Area</span>
                    <strong class="text-dark">{{ $p->area_size ? "{$p->area_size} {$p->area_unit}" : 'Titled Plot' }}</strong>
                </div>
                <div class="col-4 border-end">
                    <span class="text-muted d-block" style="font-size: 0.7rem;">Zoning</span>
                    <strong class="text-dark">{{ $p->landParcel?->zoning ?? 'Mixed Use' }}</strong>
                </div>
                <div class="col-4">
                    <span class="text-muted d-block" style="font-size: 0.7rem;">Beacons</span>
                    <strong class="text-success">GPS Fixed</strong>
                </div>
            </div>
        </div>
    </div>

    <div class="card-footer bg-white border-top p-3 d-flex gap-2">
        <a href="{{ route('public.properties.show', $p) }}" class="btn btn-outline-success btn-sm flex-grow-1 fw-bold">
            View Land Details
        </a>
        <a href="{{ route('public.services.land_survey') }}" class="btn btn-light btn-sm border" title="Request Land Survey Consultation">
            <i class="bi bi-compass text-primary"></i>
        </a>
    </div>
</div>
