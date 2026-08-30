@extends('layouts.public')

@section('title', $pageTitle)

@section('content')
<!-- Header & Breadcrumb -->
<div class="py-4 mb-4 text-white position-relative overflow-hidden" style="background: linear-gradient(135deg, #091224 0%, #1e3a8a 60%, #0f172a 100%);">
    <div class="position-absolute top-0 start-0 w-100 h-100 opacity-10" style="background: radial-gradient(#60a5fa 1px, transparent 1px); background-size: 24px 24px;"></div>
    <div class="container position-relative">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb small mb-2 text-white-50">
                <li class="breadcrumb-item"><a href="{{ route('public.home') }}" class="text-white-50 text-decoration-none">Home</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">Properties</li>
            </ol>
        </nav>
        <div class="d-flex flex-wrap justify-content-between align-items-center">
            <div>
                <h2 class="brand-font text-white mb-1">{{ $pageTitle }}</h2>
                <p class="text-white-50 small mb-0">Showing {{ $properties->total() }} verified real estate listings</p>
            </div>
            <div class="mt-3 mt-md-0 d-flex gap-2">
                <button type="button" class="btn btn-outline-light btn-sm rounded-pill d-lg-none" data-bs-toggle="offcanvas" data-bs-target="#filterOffcanvas">
                    <i class="bi bi-funnel me-1"></i> Filters ({{ request()->except('page') ? count(request()->except('page')) : 0 }})
                </button>
                <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm" id="toggleMapBtn">
                    <i class="bi bi-map me-1"></i> <span id="mapBtnText">Show Map View</span>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="container pb-5">
    <!-- Collapsible Interactive Leaflet Map for Search Results -->
    <div id="resultsMapContainer" class="mb-4 d-none">
        <div class="card border rounded-4 overflow-hidden shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <strong class="brand-font text-primary"><i class="bi bi-geo-alt-fill text-danger me-1"></i> Interactive Map of Search Results</strong>
                <span class="badge bg-light text-dark border">{{ $properties->count() }} Properties on this page</span>
            </div>
            <div id="propertiesMap" style="height: 380px;" class="w-100"></div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Desktop Filter Sidebar -->
        <div class="col-lg-3 d-none d-lg-block">
            <div class="sticky-top" style="top: 90px;">
                @include('public.partials.filter-sidebar')
            </div>
        </div>

        <!-- Property Results Grid -->
        <div class="col-lg-9">
            <!-- Results Header & Sort Controls -->
            <div class="card p-3 mb-4 border rounded-4 bg-white shadow-sm d-flex flex-wrap flex-row justify-content-between align-items-center gap-3">
                <div class="small text-muted">
                    Showing <strong>{{ $properties->firstItem() ?? 0 }} - {{ $properties->lastItem() ?? 0 }}</strong> of <strong>{{ $properties->total() }}</strong> listings
                </div>

                <div class="d-flex align-items-center gap-2">
                    <label class="small fw-bold text-muted text-nowrap mb-0">Sort By:</label>
                    <form action="{{ url()->current() }}" method="GET" id="sortForm" class="d-inline">
                        @foreach(request()->except('sort', 'page') as $key => $val)
                            @if(is_array($val))
                                @foreach($val as $item)
                                    <input type="hidden" name="{{ $key }}[]" value="{{ $item }}">
                                @endforeach
                            @else
                                <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                            @endif
                        @endforeach
                        <select name="sort" class="form-select form-select-sm" onchange="this.form.submit()" style="min-width: 160px;">
                            <option value="newest" {{ $sort === 'newest' ? 'selected' : '' }}>Newest Listed</option>
                            <option value="price_asc" {{ $sort === 'price_asc' ? 'selected' : '' }}>Price: Low to High</option>
                            <option value="price_desc" {{ $sort === 'price_desc' ? 'selected' : '' }}>Price: High to Low</option>
                            <option value="views" {{ $sort === 'views' ? 'selected' : '' }}>Most Viewed</option>
                            <option value="size_desc" {{ $sort === 'size_desc' ? 'selected' : '' }}>Largest Size</option>
                        </select>
                    </form>
                </div>
            </div>

            <!-- Property Cards Grid -->
            <div class="row g-4">
                @forelse($properties as $p)
                    <div class="col-md-6 col-xl-4">
                        @include('public.partials.property-card', ['p' => $p])
                    </div>
                @empty
                    <div class="col-12 text-center py-5">
                        <div class="card p-5 border rounded-4 bg-white shadow-sm max-w-500 mx-auto">
                            <div class="rounded-circle bg-light text-muted p-3 mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 70px; height: 70px; font-size: 2rem;">
                                <i class="bi bi-search"></i>
                            </div>
                            <h4 class="brand-font mb-2">No Properties Found</h4>
                            <p class="text-muted small mb-4">We couldn't find any properties matching your current filter criteria. Try broadening your location or price range.</p>
                            <a href="{{ route('public.properties') }}" class="btn btn-primary btn-sm rounded-pill px-4 fw-bold mx-auto">
                                Reset All Filters
                            </a>
                        </div>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            <div class="mt-5 d-flex justify-content-center">
                {{ $properties->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

<!-- Mobile Offcanvas Filter Drawer -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="filterOffcanvas">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title brand-font"><i class="bi bi-funnel text-primary me-2"></i> Filter Properties</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-3">
        @include('public.partials.filter-sidebar')
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    let mapInitialized = false;
    let map = null;

    $('#toggleMapBtn').on('click', function() {
        const container = $('#resultsMapContainer');
        if (container.hasClass('d-none')) {
            container.removeClass('d-none');
            $('#mapBtnText').text('Hide Map View');

            if (!mapInitialized) {
                map = L.map('propertiesMap').setView([-6.7924, 39.2083], 7);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors'
                }).addTo(map);

                const markers = [];
                @foreach($properties as $prop)
                    @if($prop->latitude && $prop->longitude)
                        (function() {
                            const marker = L.marker([{{ $prop->latitude }}, {{ $prop->longitude }}]).addTo(map);
                            marker.bindPopup(`
                                <div style="min-width: 180px;">
                                    <strong>{{ addslashes($prop->title) }}</strong><br>
                                    <span class="badge bg-primary mt-1">{{ $prop->listing_type }}</span>
                                    <div class="fw-bold text-success mt-1">{{ $prop->formatted_price }}</div>
                                    <a href="{{ route('public.properties.show', $prop) }}" class="btn btn-sm btn-outline-primary w-100 mt-2 py-0">View</a>
                                </div>
                            `);
                            markers.push([{{ $prop->latitude }}, {{ $prop->longitude }}]);
                        })();
                    @endif
                @endforeach

                if (markers.length > 0) {
                    map.fitBounds(markers, { padding: [40, 40] });
                }
                mapInitialized = true;
            } else {
                map.invalidateSize();
            }
        } else {
            container.addClass('d-none');
            $('#mapBtnText').text('Show Map View');
        }
    });
});
</script>
@endsection
