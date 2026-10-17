@php
    $projectUrl = route('public.projects.show', $proj->slug);
    $shortPrice = format_money_short($proj->starting_price, ($proj->currency ?? 'TZS') === 'TZS' ? null : $proj->currency);
    $available = (int) $proj->available_units;
@endphp

<article class="card property-card h-100 border-0 shadow-sm">
    <div class="property-card-img-wrapper">
        <a href="{{ $projectUrl }}" aria-label="{{ $proj->title }}">
            <img src="{{ $proj->hero_image_url }}" alt="{{ $proj->title }}" loading="lazy" decoding="async" width="600" height="375" onerror="this.onerror=null;this.src='{{ asset('images/property-placeholder.jpg') }}'">
        </a>
        <div class="position-absolute top-0 start-0 m-2 d-flex flex-column align-items-start gap-1">
            <span class="badge bg-dark rounded-pill px-2.5 py-1.5 fw-semibold shadow-sm">{{ $proj->project_status }}</span>
            <span class="badge {{ $available > 0 ? 'bg-success' : 'bg-danger' }} rounded-pill px-2.5 py-1.5 shadow-sm">
                {{ $available > 0 ? __('app.available') : __('app.sold_out') }}
            </span>
        </div>
        <div class="position-absolute bottom-0 start-0 m-2">
            <span class="badge bg-primary text-white rounded-pill px-3 py-2 fs-6 fw-bold shadow" title="{{ $proj->formatted_price }}">
                <small class="fw-normal opacity-75">{{ __('app.starting_price') }}</small> {{ $shortPrice }}
            </span>
        </div>
    </div>

    <div class="card-body p-3 d-flex flex-column">
        <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
            <span class="badge bg-primary-subtle text-primary fw-medium text-truncate">{{ $proj->project_type }}</span>
            <small class="text-muted text-truncate"><i class="bi bi-person-badge me-1"></i>{{ $proj->developer_name }}</small>
        </div>

        <h3 class="h6 brand-font mb-1 line-clamp-2">
            <a href="{{ $projectUrl }}" class="text-dark text-decoration-none hover-primary">{{ $proj->title }}</a>
        </h3>

        <p class="text-muted small mb-2 text-truncate">
            <i class="bi bi-geo-alt-fill text-danger me-1"></i>{{ $proj->location_name }}, {{ $proj->city }}
        </p>

        <p class="text-muted small mb-3 line-clamp-2">{{ $proj->description }}</p>

        <div class="mt-auto border-top pt-2 text-muted small d-flex flex-wrap justify-content-between gap-1">
            <span><i class="bi bi-houses me-1 text-primary"></i>{{ __('marketplace.units_left', ['available' => $available, 'total' => (int) $proj->total_units]) }}</span>
            <span class="text-success fw-semibold"><i class="bi bi-calendar-check me-1"></i>{{ $proj->expected_completion_date ? $proj->expected_completion_date->format('M Y') : __('marketplace.ongoing') }}</span>
        </div>
    </div>

    <div class="card-footer bg-white border-top p-2.5">
        <a href="{{ $projectUrl }}" class="btn btn-primary btn-sm w-100 fw-bold">
            <i class="bi bi-map me-1"></i>{{ __('marketplace.explore_project') }}
        </a>
    </div>
</article>
