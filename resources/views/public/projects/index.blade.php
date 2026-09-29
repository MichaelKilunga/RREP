@extends('layouts.public')

@section('title', 'Miradi ya Viwanja & Ramani za Shapefile - Land Projects Directory')

@section('content')
<!-- Header Banner (Modern GIS Styled) -->
<section class="py-5 position-relative text-white" style="background: linear-gradient(135deg, #091224 0%, #1e3a8a 100%);">
    <div class="container py-3">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <a href="{{ route('public.home') }}" class="text-white-50 text-decoration-none small d-inline-flex align-items-center mb-2 hover-white">
                    <i class="bi bi-arrow-left me-1"></i> {{ app()->getLocale() === 'sw' ? 'Rudi Nyumbani' : 'Back to Home' }}
                </a>
                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                    <span class="badge bg-primary bg-opacity-25 border border-primary border-opacity-50 text-white px-3 py-1 rounded-pill small">
                        <i class="bi bi-layers-half me-1"></i> Cadastral GIS & Master Plans
                    </span>
                    <span class="badge bg-success bg-opacity-25 border border-success border-opacity-50 text-white px-3 py-1 rounded-pill small">
                        <i class="bi bi-file-earmark-check-fill me-1"></i> Title Deed Verified
                    </span>
                </div>
                <h1 class="fw-extrabold text-white mb-2 display-6 fs-2">
                    {{ app()->getLocale() === 'sw' ? 'Miradi ya Viwanja & Ramani za Mipango Miji' : 'Land Projects & Interactive Plot Maps' }}
                </h1>
                <p class="text-white-50 mb-0 lead fs-6 max-w-2xl" style="max-width: 620px;">
                    {{ app()->getLocale() === 'sw' ? 'Chagua miradi ya viwanja vilivyopimwa, kagua ramani za GIS halisi, vipimo vya umbo (Shapefiles), na hali ya uuzaji.' : 'Explore verified land projects, inspect interactive GIS shapefile boundaries, cadastral layouts, and plot availability.' }}
                </p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <div class="d-inline-flex flex-column align-items-lg-end bg-white bg-opacity-10 backdrop-blur p-3 rounded-4 border border-white border-opacity-10">
                    <span class="text-white-50 small text-uppercase fw-semibold mb-1">{{ app()->getLocale() === 'sw' ? 'Jumla ya Miradi' : 'Active Projects' }}</span>
                    <span class="fs-3 fw-extrabold text-white">{{ $projects->total() }}</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Filter & Search Engine Bar -->
<section class="py-4 bg-light border-bottom">
    <div class="container">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
            <form action="{{ route('public.projects') }}" method="GET" class="row g-3 align-items-center">
                <div class="col-lg-6 col-md-7">
                    <label class="form-label small fw-bold text-muted mb-1">{{ app()->getLocale() === 'sw' ? 'Tafuta Mradi au Halmashauri' : 'Search Project or Council' }}</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 bg-light ps-0" placeholder="{{ app()->getLocale() === 'sw' ? 'Andika jina la mradi, halmashauri au mkoa...' : 'Halmashauri / Region / Project name...' }}" value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-lg-4 col-md-3">
                    <label class="form-label small fw-bold text-muted mb-1">{{ app()->getLocale() === 'sw' ? 'Mkoa / Halmashauri' : 'Region / Council Filter' }}</label>
                    <select name="region" class="form-select bg-light" onchange="this.form.submit()">
                        <option value="">{{ app()->getLocale() === 'sw' ? 'Mikoa na Halmashauri Zote' : 'All Regions & Councils' }}</option>
                        @foreach($regions as $reg)
                            <option value="{{ $reg }}" {{ request('region') == $reg ? 'selected' : '' }}>{{ $reg }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-2 d-flex align-items-end">
                    <div class="w-100 d-flex gap-2">
                        <button type="submit" class="btn btn-primary fw-bold w-100 rounded-3 py-2 shadow-xs">
                            <i class="bi bi-funnel me-1"></i> {{ app()->getLocale() === 'sw' ? 'Chuja' : 'Filter' }}
                        </button>
                        @if(request()->filled('search') || request()->filled('region'))
                            <a href="{{ route('public.projects') }}" class="btn btn-outline-secondary rounded-3 py-2" title="Reset Filters">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>

<!-- Project Cards List Grid -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row g-4">
            @forelse($projects as $project)
                @php
                    $total = max(1, (int)$project->total_units);
                    $sold = (int)$project->sold_plots;
                    $available = (int)$project->available_units;
                    $soldPercent = min(100, max(0, round(($sold / $total) * 100)));
                @endphp
                <div class="col-lg-6">
                    <div class="card border-0 rounded-4 shadow-sm hover-shadow transition-all h-100 overflow-hidden bg-white d-flex flex-column justify-content-between">
                        <!-- Top Image / Map Banner -->
                        <div class="position-relative bg-dark" style="height: 195px; overflow: hidden;">
                            @if($project->hero_image)
                                <img src="{{ $project->hero_image_url }}" alt="{{ $project->title }}" class="w-100 h-100 object-fit-cover opacity-90">
                            @else
                                <div class="w-100 h-100 d-flex align-items-center justify-content-center text-white-50" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);">
                                    <div class="text-center p-3">
                                        <i class="bi bi-map text-primary opacity-50 display-4 d-block mb-1"></i>
                                        <span class="small text-uppercase tracking-wider fw-semibold opacity-75">Cadastral GIS Shapefile Map</span>
                                    </div>
                                </div>
                            @endif

                            <!-- Overlay Gradient & Badges -->
                            <div class="position-absolute top-0 start-0 w-100 h-100 p-3 d-flex flex-column justify-content-between" style="background: linear-gradient(to bottom, rgba(15,23,42,0.7) 0%, rgba(15,23,42,0.2) 50%, rgba(15,23,42,0.85) 100%);">
                                <div class="d-flex align-items-center justify-content-between">
                                    <span class="badge bg-dark bg-opacity-75 text-white border border-white border-opacity-25 px-3 py-1.5 rounded-pill shadow-sm small">
                                        <i class="bi bi-geo-alt-fill text-danger me-1"></i>{{ $project->council ?? $project->district ?? $project->location_name }}
                                    </span>
                                    <span class="badge {{ $project->available_units > 0 ? 'bg-success' : 'bg-danger' }} px-3 py-1.5 rounded-pill fw-bold shadow-sm">
                                        <i class="bi bi-circle-fill me-1 small opacity-75"></i> {{ $project->available_units > 0 ? (app()->getLocale() === 'sw' ? 'Inapatikana' : 'Available') : (app()->getLocale() === 'sw' ? 'Imekwisha' : 'Sold Out') }}
                                    </span>
                                </div>
                                <div class="d-flex align-items-center justify-content-between">
                                    <h5 class="fw-bold text-white mb-0 text-shadow-sm fs-5 me-2 line-clamp-1">{{ $project->title }}</h5>
                                    <span class="badge bg-primary px-2.5 py-1 rounded-2 shadow-xs text-white small flex-shrink-0" style="font-size: 0.72rem;">
                                        <i class="bi bi-layers-half me-1"></i>GIS Ready
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="card-body p-3 p-sm-4 d-flex flex-column justify-content-between">
                            <div>
                                <p class="text-secondary small mb-3 line-clamp-2" style="line-height: 1.55;">
                                    {{ $project->description ?: (app()->getLocale() === 'sw' ? 'Mradi upo eneo zuri la kiuchumi, una huduma za barabara, maji na umeme.' : 'Prime surveyed land project with complete infrastructure and urban planning approval.') }}
                                </p>

                                <!-- Visual Sales Progress Tracker -->
                                <div class="p-3 bg-light rounded-3 border border-light mb-3">
                                    <div class="d-flex align-items-center justify-content-between mb-1.5 small fw-semibold">
                                        <span class="text-dark small">
                                            <i class="bi bi-pie-chart-fill text-primary me-1"></i>{{ app()->getLocale() === 'sw' ? 'Hali ya Uuzaji wa Viwanja' : 'Plot Allocation Progress' }}
                                        </span>
                                        <span class="text-success fw-bold small">{{ $soldPercent }}% {{ app()->getLocale() === 'sw' ? 'Vimeuzwa' : 'Allocated' }}</span>
                                    </div>
                                    <div class="progress rounded-pill bg-secondary bg-opacity-10 mb-3" style="height: 8px;">
                                        <div class="progress-bar bg-success rounded-pill" role="progressbar" style="width: {{ $soldPercent }}%" aria-valuenow="{{ $soldPercent }}" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>

                                    <!-- 4 Stat Pills -->
                                    <div class="row text-center g-2 pt-1">
                                        <div class="col-3">
                                            <div class="bg-white py-2 px-1 rounded-2 border shadow-xs">
                                                <small class="d-block text-muted text-uppercase fw-bold" style="font-size: 0.6rem;">{{ app()->getLocale() === 'sw' ? 'Jumla' : 'Total' }}</small>
                                                <strong class="text-dark fs-6">{{ $project->total_units }}</strong>
                                            </div>
                                        </div>
                                        <div class="col-3">
                                            <div class="bg-white py-2 px-1 rounded-2 border shadow-xs">
                                                <small class="d-block text-muted text-uppercase fw-bold" style="font-size: 0.6rem;">{{ app()->getLocale() === 'sw' ? 'Uuzaji' : 'Sold' }}</small>
                                                <strong class="text-danger fs-6">{{ $project->sold_plots }}</strong>
                                            </div>
                                        </div>
                                        <div class="col-3">
                                            <div class="bg-white py-2 px-1 rounded-2 border shadow-xs">
                                                <small class="d-block text-muted text-uppercase fw-bold" style="font-size: 0.6rem;">{{ app()->getLocale() === 'sw' ? 'Yaliyopo' : 'Available' }}</small>
                                                <strong class="text-success fs-6">{{ $project->available_units }}</strong>
                                            </div>
                                        </div>
                                        <div class="col-3">
                                            <div class="bg-white py-2 px-1 rounded-2 border shadow-xs">
                                                <small class="d-block text-muted text-uppercase fw-bold" style="font-size: 0.6rem;">{{ app()->getLocale() === 'sw' ? 'Tango %' : 'Deposit %' }}</small>
                                                <strong class="text-primary fs-6">{{ (int)$project->initial_payment_percent }}%</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Feature Chips -->
                                <div class="d-flex flex-wrap gap-1.5 mb-3">
                                    <span class="badge bg-white text-secondary border rounded-pill px-2.5 py-1" style="font-weight: 500; font-size: 0.75rem;">
                                        <i class="bi bi-file-earmark-check-fill text-success me-1"></i>{{ app()->getLocale() === 'sw' ? 'Hati Miliki' : 'Title Deed Guaranteed' }}
                                    </span>
                                    <span class="badge bg-white text-secondary border rounded-pill px-2.5 py-1" style="font-weight: 500; font-size: 0.75rem;">
                                        <i class="bi bi-signpost-split-fill text-primary me-1"></i>{{ app()->getLocale() === 'sw' ? 'Barabara Za Mitaa' : 'Access Roads' }}
                                    </span>
                                    <span class="badge bg-white text-secondary border rounded-pill px-2.5 py-1" style="font-weight: 500; font-size: 0.75rem;">
                                        <i class="bi bi-lightning-charge-fill text-warning me-1"></i>{{ app()->getLocale() === 'sw' ? 'Maji & Umeme' : 'Utilities' }}
                                    </span>
                                    @if($project->grace_period_days)
                                        <span class="badge bg-white text-secondary border rounded-pill px-2.5 py-1" style="font-weight: 500; font-size: 0.75rem;">
                                            <i class="bi bi-clock-history text-info me-1"></i>{{ $project->grace_period_days }} {{ app()->getLocale() === 'sw' ? 'Siku za Mkopeshano' : 'Days Grace' }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Footer Price & CTA -->
                            <div class="d-flex flex-wrap align-items-center justify-content-between pt-3 border-top border-light mt-2 gap-2">
                                <div>
                                    <small class="text-muted d-block" style="font-size: 0.72rem; font-weight: 600;">{{ app()->getLocale() === 'sw' ? 'Anzia Bei' : 'Starting Price' }}</small>
                                    <span class="fw-extrabold text-primary fs-5">TZS {{ number_format($project->starting_price) }}</span>
                                </div>
                                <a href="{{ route('public.projects.show', $project->slug) }}" class="btn btn-primary rounded-pill px-4 py-2.5 fw-bold btn-sm shadow-sm d-inline-flex align-items-center">
                                    <span>{{ app()->getLocale() === 'sw' ? 'Tazama Ramani & Shapefile' : 'View Plot Map & Shapes' }}</span>
                                    <i class="bi bi-arrow-right ms-2"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5 bg-white rounded-4 shadow-sm border">
                    <i class="bi bi-map display-4 text-muted d-block mb-3 opacity-50"></i>
                    <h4 class="fw-bold text-dark mb-1">{{ app()->getLocale() === 'sw' ? 'Hakuna Miradi Iliyopatikana' : 'No Projects Found' }}</h4>
                    <p class="text-muted small mb-3">{{ app()->getLocale() === 'sw' ? 'Jaribu kubadilisha maneno ya utafutaji au mkoa uliochaguliwa.' : 'Try adjusting your search keywords or clearing region filter.' }}</p>
                    <a href="{{ route('public.projects') }}" class="btn btn-outline-primary rounded-pill px-4 fw-semibold btn-sm">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> {{ app()->getLocale() === 'sw' ? 'Onyesha Miradi Yote' : 'Show All Projects' }}
                    </a>
                </div>
            @endforelse

            <div class="col-12 d-flex justify-content-center mt-4">
                {{ $projects->links() }}
            </div>
        </div>
    </div>
</section>
@endsection
