@php
    $phaseMap = [
        'Upcoming' => ['label' => __('pages.phase_upcoming'), 'class' => 'bg-info-subtle text-info'],
        'Ongoing' => ['label' => __('pages.phase_ongoing'), 'class' => 'bg-warning-subtle text-warning-emphasis'],
        'Completed' => ['label' => __('pages.phase_completed'), 'class' => 'bg-success-subtle text-success'],
    ];
    $phase = $phaseMap[$project->project_status] ?? ['label' => $project->project_status, 'class' => 'bg-light text-dark border'];
    $cover = $project->gallery[0] ?? $project->hero_image_url;
    $thumbSlides = array_slice($project->gallery ?? [], 0, 4);
@endphp
<div class="card border-0 rounded-4 shadow-sm hover-shadow transition-all h-100 overflow-hidden bg-white d-flex flex-column">
    <a href="{{ route('public.projects.show', $project->slug) }}" class="property-card-img-wrapper d-block text-decoration-none" @if(count($thumbSlides) > 1) data-card-slideshow @endif>
        @if(count($thumbSlides) > 1)
            @foreach($thumbSlides as $thumb)
                <img src="{{ $thumb }}" alt="{{ $project->title }} - {{ $loop->iteration }}" loading="lazy" class="card-slide {{ $loop->first ? 'is-active' : '' }}" onerror="this.onerror=null;this.src='{{ asset('images/project-placeholder.jpg') }}';">
            @endforeach
        @else
            <img src="{{ $cover }}" alt="{{ $project->title }}" loading="lazy" onerror="this.onerror=null;this.src='{{ asset('images/project-placeholder.jpg') }}';">
        @endif
        <div class="position-absolute top-0 start-0 m-2 d-flex gap-1 flex-wrap">
            <span class="badge {{ $phase['class'] }} px-2.5 py-1.5 rounded-pill fw-bold">{{ $phase['label'] }}</span>
            @if(count($project->gallery) > 1)
                <span class="badge bg-dark bg-opacity-75 text-white rounded-pill px-2.5 py-1.5"><i class="bi bi-images me-1"></i>{{ count($project->gallery) }}</span>
            @endif
        </div>
    </a>
    <div class="card-body p-3 d-flex flex-column">
        @if($project->service)
            <span class="badge bg-primary-subtle text-primary align-self-start mb-2"><i class="bi bi-briefcase me-1"></i>{{ $project->service->title }}</span>
        @endif
        <h3 class="h6 fw-bold text-dark mb-1 line-clamp-2">{{ $project->title }}</h3>
        @if($project->client_name)
            <p class="text-muted small mb-1"><i class="bi bi-building me-1"></i>{{ __('pages.client_label') }}: {{ $project->client_name }}</p>
        @endif
        <p class="text-muted small mb-2"><i class="bi bi-geo-alt-fill text-danger me-1"></i>{{ $project->location_name }}, {{ $project->city }}</p>
        <p class="text-muted small mb-3 line-clamp-2 flex-grow-1">{{ $project->description }}</p>
        <a href="{{ route('public.projects.show', $project->slug) }}" class="btn btn-outline-primary btn-sm rounded-pill fw-bold mt-auto">
            {{ __('pages.view_project') }} <i class="bi bi-arrow-right ms-1"></i>
        </a>
    </div>
</div>
