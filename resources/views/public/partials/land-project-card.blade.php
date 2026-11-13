{{--
    Land project card shared by the home page and the projects page. Same image
    height as the service project card, so the two sit evenly in one row; the
    body carries the location and the two most useful facts the project has.
    Params: $project (loaded with RealEstateProject::withCardData()), optional $headingTag.
--}}
@php
    $headingTag = in_array($headingTag ?? 'h3', ['h2', 'h3'], true) ? ($headingTag ?? 'h3') : 'h3';
    $projectUrl = route('public.projects.show', $project->slug);
    $total = max(1, (int) $project->total_units);
    $soldPercent = min(100, max(0, (int) round(((int) $project->sold_plots / $total) * 100)));
    $isAvailable = (int) $project->available_units > 0;
    $priceVaries = (float) ($project->parcels_min_price ?? 0) > 0 && (float) ($project->parcels_max_price ?? 0) > (float) $project->parcels_min_price;
    $place = $project->location_label;
    if ($project->region && ! str_contains(mb_strtolower($place), mb_strtolower($project->region))) {
        $place = ltrim($place.', '.$project->region, ', ');
    }
    // One row of facts keeps the card about as tall as a service project card.
    $facts = $project->cardFacts(2);
@endphp
<div class="card border-0 rounded-4 shadow-sm hover-shadow transition-all h-100 overflow-hidden bg-white d-flex flex-column">
    <a href="{{ $projectUrl }}" class="property-card-img-wrapper d-block text-decoration-none">
        <img src="{{ $project->hero_image_url }}" alt="{{ $project->title }}" loading="lazy" decoding="async" onerror="this.onerror=null;this.src='{{ asset('images/project-placeholder.jpg') }}';">
        <div class="position-absolute top-0 start-0 w-100 h-100 p-3 d-flex flex-column justify-content-between bg-dark bg-opacity-25">
            <div class="d-flex align-items-start justify-content-between gap-2">
                <span class="badge bg-white text-dark rounded-pill px-2.5 py-1.5 fw-bold text-truncate">{{ $project->status_label }}</span>
                <span class="badge {{ $isAvailable ? 'bg-success' : 'bg-danger' }} rounded-pill px-2.5 py-1.5 fw-bold flex-shrink-0">
                    {{ $isAvailable ? __('app.available') : __('app.sold_out') }}
                </span>
            </div>
            <div class="d-flex align-items-end justify-content-between gap-2">
                <{{ $headingTag }} class="h5 fw-bold text-white mb-0 text-shadow-sm line-clamp-2 min-w-0">{{ $project->title }}</{{ $headingTag }}>
                <span class="badge bg-primary rounded-2 shadow-xs flex-shrink-0"><i class="bi bi-layers-half me-1"></i>{{ __('marketplace.gis_ready') }}</span>
            </div>
        </div>
    </a>

    <div class="card-body p-3 d-flex flex-column">
        @if($place !== '')
            <p class="text-muted small mb-2 text-truncate" title="{{ $place }}"><i class="bi bi-geo-alt-fill text-danger me-1"></i>{{ $place }}</p>
        @endif
        <p class="text-secondary small mb-3 line-clamp-2">{{ $project->description ?: __('app.prime_surveyed_land_project_with_complete') }}</p>

        @if(count($facts))
            <div class="row g-2 mb-3 small project-facts">
                @foreach($facts as $fact)
                    <div class="{{ $loop->last && $loop->count % 2 === 1 ? 'col-12' : 'col-6' }}">
                        <div class="d-flex align-items-start gap-2 rounded-3 bg-light px-2 py-1.5 h-100 min-w-0">
                            <i class="bi {{ $fact['icon'] }} text-primary mt-1"></i>
                            <div class="min-w-0">
                                <div class="project-fact-label text-muted">{{ $fact['label'] }}</div>
                                <div class="fw-semibold text-dark text-truncate" title="{{ $fact['hint'] ?? $fact['value'] }}">{{ $fact['value'] }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="mb-3">
            <div class="d-flex align-items-center justify-content-between small mb-1">
                <span class="fw-semibold text-dark">
                    <i class="bi bi-grid-3x3-gap me-1 text-success"></i><span class="text-success fw-bold">{{ $project->available_units }}</span>/{{ $project->total_units }} {{ __('app.plots_available_short') }}
                </span>
                @if((int) $project->initial_payment_percent > 0)
                    <span class="text-muted">{{ __('app.deposit_short', ['percent' => (int) $project->initial_payment_percent]) }}</span>
                @endif
            </div>
            <div class="progress progress-thin rounded-pill" role="progressbar" aria-label="{{ __('app.plot_allocation_progress') }}" aria-valuenow="{{ 100 - $soldPercent }}" aria-valuemin="0" aria-valuemax="100">
                <div class="progress-bar bg-success rounded-pill" style="width: {{ 100 - $soldPercent }}%"></div>
            </div>
        </div>

        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 pt-3 border-top mt-auto">
            <div class="min-w-0">
                <small class="text-muted d-block fw-semibold">{{ $priceVaries ? __('app.price_from') : __('app.starting_price') }}</small>
                <span class="fw-extrabold text-primary fs-5" title="{{ format_currency($project->starting_price, null, 0) }}">{{ (float) $project->starting_price > 0 ? format_money_short($project->starting_price) : '—' }}</span>
            </div>
            <a href="{{ $projectUrl }}" class="btn btn-primary rounded-pill px-3.5 fw-bold btn-sm shadow-sm d-inline-flex align-items-center justify-content-center">
                {{ __('app.view_map') }} <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
    </div>
</div>
