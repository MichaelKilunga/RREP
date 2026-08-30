@extends('layouts.app')

@section('title', __('app.add_property'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="brand-font mb-1">{{ __('app.add_property') }}</h3>
        <p class="text-muted small mb-0">Add a new property, apartment unit, or surveyed land parcel to the platform inventory</p>
    </div>
    <a href="{{ route('properties.index') }}" class="btn btn-light border btn-sm"><i class="bi bi-arrow-left me-1"></i> Back to Inventory</a>
</div>

<form action="{{ route('properties.store') }}" method="POST" id="propertyForm">
    @csrf
    <div class="row g-4">
        <!-- Main Form Column -->
        <div class="col-lg-8">
            <!-- General Information -->
            <div class="card mb-4">
                <div class="card-header brand-font d-flex justify-content-between align-items-center">
                    <span>1. General Information</span>
                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill" id="btnAiDescription">
                        <i class="bi bi-stars text-warning me-1"></i> AI Generate Description
                    </button>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Property Title *</label>
                        <input type="text" name="title" id="propTitle" class="form-control" placeholder="e.g. Masaki Oceanview 5-Bedroom Executive Villa" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Property Type *</label>
                            <select name="property_type_id" id="propType" class="form-select" required>
                                <option value="">Select Property Type</option>
                                @foreach($types as $t)
                                    <option value="{{ $t->id }}">{{ $t->name }} ({{ $t->category }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Listing Type *</label>
                            <select name="listing_type" id="listingType" class="form-select" required>
                                <option value="Sale">For Sale</option>
                                <option value="Rent">For Rent</option>
                                <option value="Lease">Commercial Lease</option>
                                <option value="Joint Venture">Joint Venture</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Description</label>
                        <textarea name="description" id="propDescription" rows="5" class="form-control" placeholder="Detailed property description, architecture, specifications..."></textarea>
                    </div>
                </div>
            </div>

            <!-- Location & Cadastral Coordinates -->
            <div class="card mb-4">
                <div class="card-header brand-font">2. Location & GIS Coordinates</div>
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">City / Region *</label>
                            <input type="text" name="city" id="propCity" class="form-control" placeholder="Dar es Salaam, Arusha, Dodoma..." required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">State / District</label>
                            <input type="text" name="state" class="form-control" placeholder="Kinondoni, Ilala, Nyamagana...">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Street Address *</label>
                            <input type="text" name="address" id="propAddress" class="form-control" placeholder="Ali Hassan Mwinyi Road, Masaki..." required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Latitude (GIS WGS84)</label>
                            <input type="number" step="0.00000001" name="latitude" class="form-control" placeholder="-6.74950000">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Longitude (GIS WGS84)</label>
                            <input type="number" step="0.00000001" name="longitude" class="form-control" placeholder="39.28180000">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Specifications & Amenities -->
            <div class="card mb-4">
                <div class="card-header brand-font">3. Specifications & Amenities</div>
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Bedrooms</label>
                            <input type="number" name="bedrooms" id="propBeds" class="form-control" placeholder="4">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Bathrooms</label>
                            <input type="number" name="bathrooms" class="form-control" placeholder="3">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Area Size</label>
                            <input type="number" step="0.01" name="area_size" class="form-control" placeholder="450">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Area Unit</label>
                            <select name="area_unit" class="form-select">
                                <option value="Sqm">Square Meters (Sqm)</option>
                                <option value="Acres">Acres</option>
                                <option value="Hectares">Hectares</option>
                            </select>
                        </div>
                    </div>

                    <label class="form-label fw-semibold small mb-2">Amenities & Infrastructure</label>
                    <div class="row g-2">
                        @foreach($amenities as $am)
                            <div class="col-md-4 col-sm-6">
                                <div class="form-check">
                                    <input class="form-check-input amenity-checkbox" type="checkbox" name="amenities[]" value="{{ $am->id }}" id="am_{{ $am->id }}" data-name="{{ $am->name }}">
                                    <label class="form-check-label small" for="am_{{ $am->id }}">{{ $am->name }}</label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Column -->
        <div class="col-lg-4">
            <!-- Pricing & Status -->
            <div class="card mb-4">
                <div class="card-header brand-font">Pricing & Financials</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Sale Price ({{ current_currency() }})</label>
                        <input type="number" step="0.01" name="price" id="propPrice" class="form-control fw-bold" placeholder="500000000">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Rent Price ({{ current_currency() }} / Month)</label>
                        <input type="number" step="0.01" name="rent_price" class="form-control" placeholder="2500000">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Listing Status *</label>
                        <select name="status" class="form-select fw-semibold" required>
                            <option value="Available" selected>Available</option>
                            <option value="Reserved">Reserved</option>
                            <option value="Under Contract">Under Contract</option>
                            <option value="Sold">Sold</option>
                            <option value="Leased">Leased</option>
                            <option value="Off Market">Off Market</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Branch Allocation</label>
                        <select name="branch_id" class="form-select">
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}">{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Property Owner / Landlord</label>
                        <select name="property_owner_id" class="form-select">
                            <option value="">Internal / Company Asset</option>
                            @foreach($owners as $o)
                                <option value="{{ $o->id }}">{{ $o->full_name }} ({{ $o->company_name ?? 'Individual' }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-3 fw-bold shadow-sm">
                <i class="bi bi-check2-circle me-1"></i> Save & Catalog Property
            </button>
        </div>
    </div>
</form>
@endsection

@section('scripts')
<script>
$(document).ready(function () {
    $('#btnAiDescription').on('click', function () {
        const title = $('#propTitle').val();
        const type = $('#propType option:selected').text();
        const location = $('#propAddress').val() + ', ' + $('#propCity').val();
        const price = $('#propPrice').val();
        const bedrooms = $('#propBeds').val();
        
        let amenities = [];
        $('.amenity-checkbox:checked').each(function () {
            amenities.push($(this).data('name'));
        });

        if (!title) {
            Swal.fire('Notice', 'Please fill at least the property title and location before generating AI description.', 'info');
            return;
        }

        const btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> AI Generating...');

        $.ajax({
            url: "{{ route('properties.ai_description') }}",
            method: "POST",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: {
                title: title,
                type: type,
                location: location,
                price: price,
                bedrooms: bedrooms,
                amenities: amenities
            },
            success: function (res) {
                btn.prop('disabled', false).html('<i class="bi bi-stars text-warning me-1"></i> AI Generate Description');
                $('#propDescription').val(res.description);
                Swal.fire('Success', 'AI Property Description generated successfully!', 'success');
            },
            error: function () {
                btn.prop('disabled', false).html('<i class="bi bi-stars text-warning me-1"></i> AI Generate Description');
                Swal.fire('Error', 'Failed to generate AI description.', 'error');
            }
        });
    });
});
</script>
@endsection
