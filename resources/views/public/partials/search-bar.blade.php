<div class="card p-3 p-md-4 shadow-lg border-0 mx-auto text-dark" style="max-width: 1000px; border-radius: 1.25rem; background: #ffffff;">
    <!-- Intent Tabs -->
    <ul class="nav nav-pills mb-3 gap-2" id="searchTabs" role="tablist">
        <li class="nav-item">
            <button class="nav-link active rounded-pill px-4 fw-bold search-tab-btn" data-type="Sale" data-action="{{ route('public.properties') }}">
                <i class="bi bi-bag-check me-1"></i> Buy
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link rounded-pill px-4 fw-bold search-tab-btn" data-type="Rent" data-action="{{ route('public.properties') }}">
                <i class="bi bi-key me-1"></i> Rent
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link rounded-pill px-4 fw-bold search-tab-btn" data-category="Land" data-action="{{ route('public.land') }}">
                <i class="bi bi-map me-1"></i> Land & Plots
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link rounded-pill px-4 fw-bold search-tab-btn" data-action="{{ route('public.projects') }}">
                <i class="bi bi-diagram-3 me-1"></i> Developments
            </button>
        </li>
    </ul>

    <!-- Search Form -->
    <form action="{{ route('public.properties') }}" method="GET" id="heroSearchForm" class="row g-2 align-items-center">
        <input type="hidden" name="listing_type" id="heroListingType" value="{{ request('listing_type', 'Sale') }}">

        <!-- Keyword / Area -->
        <div class="col-12 col-md-4">
            <label class="form-label small fw-bold text-muted mb-1 d-block text-start"><i class="bi bi-search me-1 text-primary"></i> Keyword / Area / ID</label>
            <input type="text" name="q" class="form-control form-control-lg fs-6" placeholder="e.g. Kihonda, Masaki, Villa..." value="{{ request('q') }}">
        </div>

        <!-- Location / City -->
        <div class="col-6 col-md-3">
            <label class="form-label small fw-bold text-muted mb-1 d-block text-start"><i class="bi bi-geo-alt me-1 text-danger"></i> Location</label>
            <select name="city" class="form-select form-select-lg fs-6">
                <option value="">All Locations</option>
                <option value="Dar es Salaam" {{ request('city') == 'Dar es Salaam' ? 'selected' : '' }}>Dar es Salaam</option>
                <option value="Morogoro" {{ request('city') == 'Morogoro' ? 'selected' : '' }}>Morogoro</option>
                <option value="Dodoma" {{ request('city') == 'Dodoma' ? 'selected' : '' }}>Dodoma</option>
                <option value="Arusha" {{ request('city') == 'Arusha' ? 'selected' : '' }}>Arusha</option>
                <option value="Mwanza" {{ request('city') == 'Mwanza' ? 'selected' : '' }}>Mwanza</option>
                <option value="Zanzibar" {{ request('city') == 'Zanzibar' ? 'selected' : '' }}>Zanzibar</option>
            </select>
        </div>

        <!-- Property Type -->
        <div class="col-6 col-md-3">
            <label class="form-label small fw-bold text-muted mb-1 d-block text-start"><i class="bi bi-house me-1 text-info"></i> Property Type</label>
            <select name="type" class="form-select form-select-lg fs-6">
                <option value="">All Types</option>
                @foreach(\App\Models\PropertyType::where('is_active', true)->get() as $t)
                    <option value="{{ $t->id }}" {{ request('type') == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                @endforeach
            </select>
        </div>

        <!-- Search Button -->
        <div class="col-12 col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-primary btn-lg w-100 py-2 fs-6 fw-bold shadow-sm" style="height: 48px; margin-top: auto;">
                <i class="bi bi-search me-1"></i> Search
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const tabBtns = document.querySelectorAll('.search-tab-btn');
    const form = document.getElementById('heroSearchForm');
    const listingTypeInput = document.getElementById('heroListingType');

    tabBtns.forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            tabBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            const action = this.getAttribute('data-action');
            if (action) form.action = action;

            const type = this.getAttribute('data-type');
            if (type) {
                listingTypeInput.value = type;
            } else {
                listingTypeInput.value = '';
            }
        });
    });
});
</script>
