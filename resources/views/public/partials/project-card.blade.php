<div class="card property-card h-100 shadow-sm border-0">
    <div class="property-card-img-wrapper">
        <a href="{{ route('public.projects.show', $proj->slug) }}">
            <img src="{{ $proj->hero_image_url }}" alt="{{ $proj->title }}" loading="lazy" onerror="this.src='https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?w=800&auto=format&fit=crop&q=80'">
        </a>
        <div class="position-absolute top-0 start-0 m-3">
            <span class="badge bg-dark text-white px-3 py-2 fw-bold shadow-sm" style="font-size: 0.75rem;">
                {{ $proj->project_status }}
            </span>
        </div>
        <div class="position-absolute bottom-0 start-0 m-3">
            <span class="badge bg-primary text-white p-2 px-3 fs-6 fw-bold shadow">
                {{ $proj->formatted_price }}
            </span>
        </div>
    </div>

    <div class="card-body p-3 p-md-4 d-flex flex-column">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="badge bg-primary-subtle text-primary fw-medium" style="font-size: 0.75rem;">
                {{ $proj->project_type }}
            </span>
            <small class="text-muted"><i class="bi bi-person-badge me-1"></i>{{ $proj->developer_name }}</small>
        </div>

        <h5 class="brand-font mb-1" style="font-size: 1.1rem; line-height: 1.35; min-height: 2.7rem;">
            <a href="{{ route('public.projects.show', $proj->slug) }}" class="text-dark text-decoration-none hover-primary">
                {{ $proj->title }}
            </a>
        </h5>

        <p class="text-muted small mb-3">
            <i class="bi bi-geo-alt-fill text-danger me-1"></i> {{ $proj->location_name }}, {{ $proj->city }}
        </p>

        <p class="text-muted small mb-3 text-truncate-2" style="font-size: 0.85rem;">
            {{ Str::limit($proj->description, 110) }}
        </p>

        <div class="mt-auto border-top pt-3 text-muted small d-flex justify-content-between">
            <span><i class="bi bi-houses me-1 text-primary"></i> <strong>{{ $proj->available_units }}</strong> of {{ $proj->total_units }} Units Left</span>
            <span class="text-success fw-semibold"><i class="bi bi-calendar-check me-1"></i> {{ $proj->expected_completion_date ? $proj->expected_completion_date->format('M Y') : 'Ongoing' }}</span>
        </div>
    </div>

    <div class="card-footer bg-white border-top p-3">
        <a href="{{ route('public.projects.show', $proj->slug) }}" class="btn btn-primary btn-sm w-100 fw-bold">
            Explore Project & Available Units
        </a>
    </div>
</div>
