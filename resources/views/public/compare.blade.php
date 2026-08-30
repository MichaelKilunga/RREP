@extends('layouts.public')

@section('title', 'Compare Properties Side-by-Side')

@section('content')
<div class="bg-dark text-white py-4 mb-4">
    <div class="container">
        <h2 class="brand-font text-white mb-1"><i class="bi bi-columns-gap text-warning me-2"></i> Compare Properties</h2>
        <p class="text-white-50 small mb-0">Evaluate specifications, prices, locations, and land parcel metrics side by side</p>
    </div>
</div>

<div class="container pb-5">
    @if($properties->count() > 0)
        <div class="card border rounded-4 overflow-hidden shadow-sm bg-white">
            <div class="table-responsive">
                <table class="table table-bordered align-middle text-center mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 180px;" class="text-start p-3">Feature / Spec</th>
                            @foreach($properties as $p)
                                <th style="min-width: 240px;" class="p-3">
                                    <div class="position-relative mb-2" style="height: 140px; border-radius: 0.5rem; overflow: hidden;">
                                        <img src="{{ $p->primary_image_url }}" alt="{{ $p->title }}" class="w-100 h-100 object-fit-cover" onerror="this.src='https://images.unsplash.com/photo-1580587771525-78b9dba3b914?w=400&auto=format&fit=crop&q=80'">
                                    </div>
                                    <h6 class="brand-font mb-1 text-truncate" style="font-size: 0.95rem;">
                                        <a href="{{ route('public.properties.show', $p) }}" class="text-dark text-decoration-none hover-primary">{{ $p->title }}</a>
                                    </h6>
                                    <div class="fw-bold text-success">{{ $p->formatted_price }}</div>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <th class="text-start table-light p-3">Purpose</th>
                            @foreach($properties as $p)
                                <td><span class="badge bg-primary">For {{ $p->listing_type }}</span></td>
                            @endforeach
                        </tr>
                        <tr>
                            <th class="text-start table-light p-3">Location</th>
                            @foreach($properties as $p)
                                <td>{{ $p->address }}, {{ $p->city }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <th class="text-start table-light p-3">Property Type</th>
                            @foreach($properties as $p)
                                <td>{{ $p->propertyType?->name ?? 'Real Estate' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <th class="text-start table-light p-3">Bedrooms</th>
                            @foreach($properties as $p)
                                <td><strong>{{ $p->bedrooms ? $p->bedrooms . ' Beds' : '-' }}</strong></td>
                            @endforeach
                        </tr>
                        <tr>
                            <th class="text-start table-light p-3">Bathrooms</th>
                            @foreach($properties as $p)
                                <td><strong>{{ $p->bathrooms ? $p->bathrooms . ' Baths' : '-' }}</strong></td>
                            @endforeach
                        </tr>
                        <tr>
                            <th class="text-start table-light p-3">Area Size</th>
                            @foreach($properties as $p)
                                <td><strong>{{ $p->area_size ? "{$p->area_size} {$p->area_unit}" : ($p->landParcel ? $p->landParcel->acreage . ' Acres' : '-') }}</strong></td>
                            @endforeach
                        </tr>
                        <tr>
                            <th class="text-start table-light p-3">Parking</th>
                            @foreach($properties as $p)
                                <td>{{ $p->parking_spaces ? $p->parking_spaces . ' Vehicles' : '-' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <th class="text-start table-light p-3">Title Deed Type</th>
                            @foreach($properties as $p)
                                <td><span class="badge bg-success-subtle text-success">{{ $p->landParcel?->title_deed_type ?? 'Surveyed Deed' }}</span></td>
                            @endforeach
                        </tr>
                        <tr>
                            <th class="text-start table-light p-3">Actions</th>
                            @foreach($properties as $p)
                                <td class="p-3">
                                    <div class="d-grid gap-2">
                                        <a href="{{ route('public.properties.show', $p) }}" class="btn btn-primary btn-sm fw-bold">View Full Details</a>
                                        <a href="https://wa.me/{{ setting('contact_whatsapp', '255784100200') }}?text={{ urlencode('Hello, I am comparing property: ' . $p->title . ' (' . route('public.properties.show', $p) . ')') }}" target="_blank" class="btn btn-outline-success btn-sm fw-bold">WhatsApp</a>
                                    </div>
                                </td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="card p-5 text-center border rounded-4 bg-white shadow-sm max-w-500 mx-auto">
            <div class="rounded-circle bg-light text-warning p-3 mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 70px; height: 70px; font-size: 2rem;">
                <i class="bi bi-columns-gap"></i>
            </div>
            <h4 class="brand-font mb-2">No Properties Selected for Comparison</h4>
            <p class="text-muted small mb-4">Click the "Compare" button on any property listing to add up to 4 properties for side-by-side spec comparison.</p>
            <a href="{{ route('public.properties') }}" class="btn btn-primary btn-sm rounded-pill px-4 fw-bold mx-auto">
                Browse Marketplace
            </a>
        </div>
    @endif
</div>
@endsection
