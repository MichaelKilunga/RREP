@extends('layouts.public')

@section('title', $property->title)
@section('meta_description', Str::limit(strip_tags($property->description), 160))
@section('og_image', $property->primary_image_url)

@section('content')
<div class="bg-white border-bottom py-3 mb-4">
    <div class="container">
        <!-- Breadcrumbs -->
        <nav aria-label="breadcrumb" class="mb-2">
            <ol class="breadcrumb small mb-0">
                <li class="breadcrumb-item"><a href="{{ route('public.home') }}" class="text-decoration-none">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('public.properties') }}" class="text-decoration-none">Properties</a></li>
                <li class="breadcrumb-item"><a href="{{ route('public.locations.show', Str::slug($property->city)) }}" class="text-decoration-none">{{ $property->city }}</a></li>
                <li class="breadcrumb-item active text-truncate" style="max-width: 300px;">{{ $property->title }}</li>
            </ol>
        </nav>

        <!-- Title & Action Bar -->
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                    <span class="badge {{ $property->listing_type === 'Rent' ? 'bg-warning text-dark' : 'bg-primary text-white' }} px-3 py-1 fw-bold fs-6">
                        For {{ $property->listing_type }}
                    </span>
                    <span class="badge bg-light text-dark border fs-6">{{ $property->propertyType?->name }}</span>
                    <span class="badge badge-verified" data-bs-toggle="tooltip" title="Verified Title Deed and Cadastral Survey Status">
                        <i class="bi bi-patch-check-fill text-success me-1"></i> Verified Listing
                    </span>
                    <span class="badge bg-secondary-subtle text-secondary">Code: {{ $property->property_code }}</span>
                </div>
                <h1 class="brand-font display-6 fw-bold mb-1">{{ $property->title }}</h1>
                <p class="text-muted mb-0">
                    <i class="bi bi-geo-alt-fill text-danger me-1"></i> {{ $property->address }}, {{ $property->city }}, {{ $property->country }}
                </p>
            </div>

            <!-- Price & Quick Actions -->
            <div class="text-lg-end mt-2 mt-lg-0">
                <div class="fs-2 fw-extrabold text-success brand-font mb-1">{{ $property->formatted_price }}</div>
                @if($property->deposit_amount)
                    <div class="small text-muted mb-2">Deposit: {{ format_currency($property->deposit_amount, $property->currency) }}</div>
                @endif
                <div class="d-flex align-items-center gap-2 justify-content-lg-end">
                    <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3 favorite-btn" data-property-id="{{ $property->id }}">
                        <i class="bi bi-heart me-1"></i> Save
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 compare-toggle-btn" data-property-id="{{ $property->id }}">
                        <i class="bi bi-columns-gap me-1"></i> Compare
                    </button>
                    <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 share-property-btn" data-url="{{ url()->current() }}" data-title="{{ $property->title }}">
                        <i class="bi bi-share me-1"></i> Share
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container pb-5">
    <div class="row g-4">
        <!-- Main Column -->
        <div class="col-lg-8">
            <!-- 1. Media Gallery -->
            <div class="card border rounded-4 overflow-hidden mb-4 shadow-sm">
                <div class="position-relative bg-dark" style="height: 440px;">
                    <img id="mainGalleryImg" src="{{ $property->primary_image_url }}" alt="{{ $property->title }}" class="w-100 h-100 object-fit-cover" onerror="this.src='https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=1200&auto=format&fit=crop&q=80'">
                    <span class="position-absolute bottom-0 end-0 m-3 badge bg-dark bg-opacity-75 text-white p-2 px-3">
                        <i class="bi bi-camera me-1"></i> {{ $property->media->count() ?: 1 }} Photos
                    </span>
                </div>
                @if($property->media->count() > 1)
                    <div class="p-3 bg-light d-flex gap-2 overflow-auto">
                        @foreach($property->media as $med)
                            <img src="{{ $med->mediaFile?->url }}" class="rounded-3 border cursor-pointer thumbnail-img" style="width: 80px; height: 60px; object-fit: cover;" onclick="document.getElementById('mainGalleryImg').src = this.src">
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- 2. Property Specifications Summary Bar -->
            <div class="card border rounded-4 p-3 p-md-4 mb-4 bg-white shadow-sm">
                <div class="row g-3 text-center">
                    @if($property->bedrooms)
                        <div class="col-6 col-md-3 border-end">
                            <i class="bi bi-door-open text-primary fs-3 d-block mb-1"></i>
                            <span class="text-muted small">Bedrooms</span>
                            <div class="fw-bold fs-5 text-dark">{{ $property->bedrooms }} Beds</div>
                        </div>
                    @endif
                    @if($property->bathrooms)
                        <div class="col-6 col-md-3 border-end">
                            <i class="bi bi-droplet text-info fs-3 d-block mb-1"></i>
                            <span class="text-muted small">Bathrooms</span>
                            <div class="fw-bold fs-5 text-dark">{{ $property->bathrooms }} Baths</div>
                        </div>
                    @endif
                    <div class="col-6 col-md-3 border-end">
                        <i class="bi bi-arrows-fullscreen text-success fs-3 d-block mb-1"></i>
                        <span class="text-muted small">Area Size</span>
                        <div class="fw-bold fs-5 text-dark">{{ $property->area_size ? "{$property->area_size} {$property->area_unit}" : ($property->landParcel ? $property->landParcel->acreage . ' Acres' : 'Titled') }}</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <i class="bi bi-car-front text-secondary fs-3 d-block mb-1"></i>
                        <span class="text-muted small">Parking Spaces</span>
                        <div class="fw-bold fs-5 text-dark">{{ $property->parking_spaces ?? '1+' }} Cars</div>
                    </div>
                </div>
            </div>

            <!-- 3. Description -->
            <div class="card border rounded-4 p-4 mb-4 bg-white shadow-sm">
                <h4 class="brand-font mb-3"><i class="bi bi-card-text text-primary me-2"></i> Property Description</h4>
                <div class="text-secondary" style="line-height: 1.8; font-size: 0.95rem;">
                    {!! nl2br(e($property->description)) !!}
                </div>
            </div>

            <!-- 4. Features & Amenities -->
            <div class="card border rounded-4 p-4 mb-4 bg-white shadow-sm">
                <h4 class="brand-font mb-3"><i class="bi bi-stars text-warning me-2"></i> Features & Amenities</h4>
                <div class="row g-3">
                    @forelse($property->amenities as $am)
                        <div class="col-6 col-md-4">
                            <div class="d-flex align-items-center gap-2 p-2 rounded-3 bg-light border">
                                <i class="bi bi-check-circle-fill text-success fs-5"></i>
                                <span class="fw-medium text-dark small">{{ $am->name }}</span>
                            </div>
                        </div>
                    @empty
                        @if($property->features_json && is_array($property->features_json))
                            @foreach($property->features_json as $f)
                                <div class="col-6 col-md-4">
                                    <div class="d-flex align-items-center gap-2 p-2 rounded-3 bg-light border">
                                        <i class="bi bi-check-circle-fill text-success fs-5"></i>
                                        <span class="fw-medium text-dark small">{{ $f }}</span>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="col-12 text-muted small">Standard verified amenities.</div>
                        @endif
                    @endforelse
                </div>
            </div>

            <!-- 5. Land & Cadastral Survey Information (If Land Parcel exists) -->
            @if($property->landParcel)
                <div class="card border border-success rounded-4 p-4 mb-4 shadow-sm" style="background: #f0fdf4;">
                    <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                        <h4 class="brand-font text-success mb-0"><i class="bi bi-compass text-success me-2"></i> Cadastral Survey & Title Deed Data</h4>
                        <span class="badge bg-success">Registry Verified</span>
                    </div>

                    <div class="row g-3 small">
                        <div class="col-6 col-md-4">
                            <span class="text-muted d-block">Parcel Number</span>
                            <strong class="text-dark">{{ $property->landParcel->parcel_number }}</strong>
                        </div>
                        <div class="col-6 col-md-4">
                            <span class="text-muted d-block">Title Deed Number</span>
                            <strong class="text-dark">{{ $property->landParcel->deed_number ?? 'In Processing' }}</strong>
                        </div>
                        <div class="col-6 col-md-4">
                            <span class="text-muted d-block">Survey Plan (TP Drawing)</span>
                            <strong class="text-dark">{{ $property->landParcel->survey_plan_number ?? 'Available' }}</strong>
                        </div>
                        <div class="col-6 col-md-4">
                            <span class="text-muted d-block">Title Deed Type</span>
                            <strong class="text-dark">{{ $property->landParcel->title_deed_type }}</strong>
                        </div>
                        <div class="col-6 col-md-4">
                            <span class="text-muted d-block">Acreage</span>
                            <strong class="text-dark">{{ $property->landParcel->acreage }} Acres</strong>
                        </div>
                        <div class="col-6 col-md-4">
                            <span class="text-muted d-block">Tenure Remaining</span>
                            <strong class="text-dark">{{ $property->landParcel->tenure_years_remaining ?? 99 }} Years</strong>
                        </div>
                        <div class="col-6 col-md-4">
                            <span class="text-muted d-block">Zoning</span>
                            <strong class="text-dark">{{ $property->landParcel->zoning ?? 'Residential' }}</strong>
                        </div>
                        <div class="col-6 col-md-4">
                            <span class="text-muted d-block">Topography</span>
                            <strong class="text-dark">{{ $property->landParcel->topography ?? 'Flat Land' }}</strong>
                        </div>
                        <div class="col-6 col-md-4">
                            <span class="text-muted d-block">Soil Type</span>
                            <strong class="text-dark">{{ $property->landParcel->soil_type ?? 'Loam' }}</strong>
                        </div>
                    </div>
                </div>
            @endif

            <!-- 6. Interactive GIS Map -->
            @if($property->latitude && $property->longitude)
                <div class="card border rounded-4 p-4 mb-4 bg-white shadow-sm">
                    <h4 class="brand-font mb-3"><i class="bi bi-geo-alt-fill text-danger me-2"></i> Location & Geospatial Map</h4>
                    <div id="singlePropertyMap" style="height: 350px;" class="rounded-4 border"></div>
                    <div class="text-muted small mt-2 d-flex align-items-center gap-1">
                        <i class="bi bi-info-circle"></i> Coordinates: {{ $property->latitude }}, {{ $property->longitude }} • Verified by REMS Geomatics Engine
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar Column (Inquiry Form, Viewing Booking, Agent Card) -->
        <div class="col-lg-4">
            <div class="sticky-top" style="top: 90px;">
                <!-- Fast WhatsApp Direct Card -->
                <div class="card border-0 shadow-sm p-4 rounded-4 text-white mb-4" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="rounded-circle bg-white text-success d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; font-size: 1.5rem;">
                            <i class="bi bi-whatsapp"></i>
                        </div>
                        <div>
                            <h5 class="brand-font text-white mb-0">Chat on WhatsApp</h5>
                            <small class="text-white-50">Instant response from listing agent</small>
                        </div>
                    </div>
                    <a href="https://wa.me/{{ setting('contact_whatsapp', '255784100200') }}?text={{ urlencode('Hello, I am interested in property: ' . $property->title . ' (' . url()->current() . '). Please provide more details.') }}" target="_blank" class="btn btn-light w-100 fw-bold py-2 shadow-sm text-success">
                        <i class="bi bi-whatsapp me-1"></i> Start WhatsApp Chat
                    </a>
                </div>

                <!-- Schedule Viewing & Inquiry Form -->
                <div class="card border rounded-4 p-4 shadow-sm bg-white mb-4">
                    <ul class="nav nav-tabs nav-fill mb-3" id="inquiryTabs">
                        <li class="nav-item">
                            <button class="nav-link active fw-bold small py-2" data-bs-toggle="tab" data-bs-target="#inquiryTabPane">
                                <i class="bi bi-envelope me-1"></i> Inquire
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link fw-bold small py-2" data-bs-toggle="tab" data-bs-target="#viewingTabPane">
                                <i class="bi bi-calendar-event me-1"></i> Book Viewing
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <!-- Tab 1: General Inquiry Form -->
                        <div class="tab-pane fade show active" id="inquiryTabPane">
                            <form action="{{ route('public.inquire') }}" method="POST">
                                @csrf
                                <input type="hidden" name="property_id" value="{{ $property->id }}">

                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-muted">Your Full Name *</label>
                                    <input type="text" name="name" class="form-control form-control-sm" placeholder="John Doe" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-muted">Phone Number (WhatsApp) *</label>
                                    <input type="text" name="phone" class="form-control form-control-sm" placeholder="+255 7..." required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-muted">Email Address</label>
                                    <input type="email" name="email" class="form-control form-control-sm" placeholder="john@example.com">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-muted">Preferred Contact Channel</label>
                                    <select name="preferred_contact_method" class="form-select form-select-sm">
                                        <option value="WhatsApp">WhatsApp</option>
                                        <option value="Phone">Phone Call</option>
                                        <option value="Email">Email</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-muted">Your Message *</label>
                                    <textarea name="message" rows="3" class="form-control form-control-sm" required placeholder="I am interested in this property and would like to confirm availability and title status...">I am interested in "{{ $property->title }}" (Ref: {{ $property->property_code }}). Please contact me.</textarea>
                                </div>

                                <button type="submit" class="btn btn-primary w-100 py-2 fw-bold shadow-sm">
                                    <i class="bi bi-send-fill me-1"></i> Send Property Inquiry
                                </button>
                            </form>
                        </div>

                        <!-- Tab 2: Book Site Viewing -->
                        <div class="tab-pane fade" id="viewingTabPane">
                            <form action="{{ route('public.viewing.book') }}" method="POST">
                                @csrf
                                <input type="hidden" name="property_id" value="{{ $property->id }}">

                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-muted">Your Full Name *</label>
                                    <input type="text" name="name" class="form-control form-control-sm" placeholder="John Doe" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-muted">Phone Number *</label>
                                    <input type="text" name="phone" class="form-control form-control-sm" placeholder="+255 7..." required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-muted">Email Address</label>
                                    <input type="email" name="email" class="form-control form-control-sm" placeholder="john@example.com">
                                </div>

                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <label class="form-label small fw-bold text-muted">Date *</label>
                                        <input type="date" name="preferred_date" class="form-control form-control-sm" min="{{ date('Y-m-d') }}" value="{{ date('Y-m-d', strtotime('+1 day')) }}" required>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small fw-bold text-muted">Time *</label>
                                        <select name="preferred_time" class="form-select form-select-sm" required>
                                            <option value="09:00 AM">09:00 AM</option>
                                            <option value="11:00 AM">11:00 AM</option>
                                            <option value="02:00 PM">02:00 PM</option>
                                            <option value="04:00 PM">04:00 PM</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-muted">Special Notes / Attendees</label>
                                    <textarea name="message" rows="2" class="form-control form-control-sm" placeholder="Will be attending with my spouse..."></textarea>
                                </div>

                                <button type="submit" class="btn btn-success w-100 py-2 fw-bold shadow-sm">
                                    <i class="bi bi-calendar-check me-1"></i> Confirm Viewing Request
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="text-center text-muted small mt-3">
                        <i class="bi bi-shield-lock text-success me-1"></i> Data protected by {{ current_organization()?->name }}
                    </div>
                </div>

                <!-- Assigned Agent / Listing Desk Card -->
                <div class="card border rounded-4 p-4 shadow-sm bg-white">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 50px; height: 50px; font-size: 1.2rem;">
                            {{ substr($property->owner?->first_name ?? 'R', 0, 1) }}
                        </div>
                        <div>
                            <h6 class="brand-font mb-0">{{ $property->owner?->company_name ?? 'REMS Verified Listing Desk' }}</h6>
                            <small class="text-success fw-semibold"><i class="bi bi-patch-check-fill me-1"></i> Authorized Representative</small>
                        </div>
                    </div>
                    <div class="d-grid gap-2">
                        <a href="tel:{{ setting('contact_phone', '+255784100200') }}" class="btn btn-outline-dark btn-sm fw-bold">
                            <i class="bi bi-telephone-fill text-primary me-1"></i> {{ setting('contact_phone', '+255 784 100 200') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Similar / Recommended Properties -->
    @if($similarProperties->count())
        <div class="border-top pt-5 mt-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <span class="section-tag">Recommendations</span>
                    <h3 class="brand-font mb-0">Similar Properties in {{ $property->city }}</h3>
                </div>
            </div>
            <div class="row g-4">
                @foreach($similarProperties as $sp)
                    <div class="col-md-4">
                        @include('public.partials.property-card', ['p' => $sp])
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

<!-- Mobile Bottom Action Bar -->
@include('public.partials.mobile-bottom-bar', ['property' => $property])

@endsection

@section('scripts')
@if($property->latitude && $property->longitude)
<script>
document.addEventListener('DOMContentLoaded', function () {
    const lat = {{ $property->latitude }};
    const lng = {{ $property->longitude }};
    const map = L.map('singlePropertyMap').setView([lat, lng], 15);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    const marker = L.marker([lat, lng]).addTo(map);
    marker.bindPopup(`<strong>{{ addslashes($property->title) }}</strong><br>{{ addslashes($property->address) }}`).openPopup();

    @if($property->landParcel && $property->landParcel->boundary_coordinates_json)
        const polyCoords = [
            @foreach($property->landParcel->boundary_coordinates_json as $pt)
                [{{ $pt['lat'] }}, {{ $pt['lng'] }}],
            @endforeach
        ];
        if (polyCoords.length >= 3) {
            const polygon = L.polygon(polyCoords, { color: '#10b981', fillColor: '#10b981', fillOpacity: 0.25 }).addTo(map);
            map.fitBounds(polygon.getBounds(), { padding: [30, 30] });
        }
    @endif
});
</script>
@endif
@endsection
