<div class="card border shadow-sm p-4 rounded-4 bg-white">
    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
        <h5 class="brand-font mb-0"><i class="bi bi-funnel text-primary me-2"></i> Filters</h5>
        <a href="{{ route('public.properties') }}" class="btn btn-sm btn-link text-danger text-decoration-none p-0 fw-semibold">Reset All</a>
    </div>

    <form action="{{ route('public.properties') }}" method="GET">
        <!-- Search Keyword -->
        <div class="mb-3">
            <label class="form-label small fw-bold text-muted">Keyword or Property ID</label>
            <div class="input-group">
                <span class="input-group-text bg-light"><i class="bi bi-search text-muted"></i></span>
                <input type="text" name="q" class="form-control form-control-sm" placeholder="e.g. Kihonda, Oceanview..." value="{{ request('q') }}">
            </div>
        </div>

        <!-- Listing Type / Purpose -->
        <div class="mb-3">
            <label class="form-label small fw-bold text-muted">Listing Purpose</label>
            <select name="listing_type" class="form-select form-select-sm">
                <option value="">Any Purpose (Buy or Rent)</option>
                <option value="Sale" {{ request('listing_type') == 'Sale' ? 'selected' : '' }}>Buy (For Sale)</option>
                <option value="Rent" {{ request('listing_type') == 'Rent' ? 'selected' : '' }}>Rent (For Lease)</option>
            </select>
        </div>

        <!-- Location / City -->
        <div class="mb-3">
            <label class="form-label small fw-bold text-muted">City / Region</label>
            <select name="city" class="form-select form-select-sm">
                <option value="">All Regions</option>
                @foreach($cities as $c)
                    <option value="{{ $c }}" {{ request('city') == $c ? 'selected' : '' }}>{{ $c }}</option>
                @endforeach
            </select>
        </div>

        <!-- Property Type -->
        <div class="mb-3">
            <label class="form-label small fw-bold text-muted">Property Type</label>
            <select name="type" class="form-select form-select-sm">
                <option value="">All Property Types</option>
                @foreach($propertyTypes as $pt)
                    <option value="{{ $pt->id }}" {{ request('type') == $pt->id ? 'selected' : '' }}>{{ $pt->name }}</option>
                @endforeach
            </select>
        </div>

        <!-- Price Range (TZS) -->
        <div class="mb-3">
            <label class="form-label small fw-bold text-muted">Price Range (TZS)</label>
            <div class="row g-2">
                <div class="col-6">
                    <input type="number" name="min_price" class="form-control form-control-sm" placeholder="Min TZS" value="{{ request('min_price') }}">
                </div>
                <div class="col-6">
                    <input type="number" name="max_price" class="form-control form-control-sm" placeholder="Max TZS" value="{{ request('max_price') }}">
                </div>
            </div>
        </div>

        <!-- Bedrooms Filter -->
        <div class="mb-3">
            <label class="form-label small fw-bold text-muted">Bedrooms</label>
            <div class="d-flex flex-wrap gap-1">
                @foreach([1, 2, 3, 4, 5] as $bed)
                    <input type="radio" class="btn-check" name="bedrooms" id="bed{{ $bed }}" value="{{ $bed }}" {{ request('bedrooms') == $bed ? 'checked' : '' }}>
                    <label class="btn btn-outline-secondary btn-sm flex-fill" for="bed{{ $bed }}">{{ $bed }}+</label>
                @endforeach
            </div>
        </div>

        <!-- Bathrooms Filter -->
        <div class="mb-3">
            <label class="form-label small fw-bold text-muted">Bathrooms</label>
            <div class="d-flex flex-wrap gap-1">
                @foreach([1, 2, 3, 4] as $bath)
                    <input type="radio" class="btn-check" name="bathrooms" id="bath{{ $bath }}" value="{{ $bath }}" {{ request('bathrooms') == $bath ? 'checked' : '' }}>
                    <label class="btn btn-outline-secondary btn-sm flex-fill" for="bath{{ $bath }}">{{ $bath }}+</label>
                @endforeach
            </div>
        </div>

        <!-- Amenities Checkboxes -->
        @if(isset($amenities) && $amenities->count())
            <div class="mb-3">
                <label class="form-label small fw-bold text-muted d-block">Features & Amenities</label>
                <div class="overflow-auto" style="max-height: 160px;">
                    @foreach($amenities as $am)
                        <div class="form-check small mb-1">
                            <input class="form-check-input" type="checkbox" name="amenities[]" value="{{ $am->id }}" id="am{{ $am->id }}" {{ is_array(request('amenities')) && in_array($am->id, request('amenities')) ? 'checked' : '' }}>
                            <label class="form-check-label" for="am{{ $am->id }}">
                                {{ $am->name }}
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Submit Button -->
        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold shadow-sm">
            <i class="bi bi-search me-1"></i> Apply Filters
        </button>
    </form>
</div>
