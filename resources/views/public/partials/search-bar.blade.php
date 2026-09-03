<div class="card p-3 p-sm-4 shadow-lg border-0 mx-auto text-dark" style="max-width: 1000px; border-radius: 1.25rem; background: #ffffff;">
    <!-- Intent Tabs with Touch Scroll Support -->
    <div class="overflow-x-auto pb-1 mb-3 search-tabs-container" style="-webkit-overflow-scrolling: touch;">
        <ul class="nav nav-pills flex-nowrap gap-2 text-nowrap" id="searchTabs" role="tablist">
            <li class="nav-item">
                <button class="nav-link active rounded-pill px-3 px-sm-4 py-2 fw-bold search-tab-btn" data-type="Sale" data-action="{{ route('public.properties') }}">
                    <i class="bi bi-bag-check me-1"></i> {{ setting('landing_search_tab_buy', 'Buy') }}
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link rounded-pill px-3 px-sm-4 py-2 fw-bold search-tab-btn" data-type="Rent" data-action="{{ route('public.properties') }}">
                    <i class="bi bi-key me-1"></i> {{ setting('landing_search_tab_rent', 'Rent') }}
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link rounded-pill px-3 px-sm-4 py-2 fw-bold search-tab-btn" data-category="Land" data-action="{{ route('public.land') }}">
                    <i class="bi bi-map me-1"></i> {{ setting('landing_search_tab_land', 'Land & Plots') }}
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link rounded-pill px-3 px-sm-4 py-2 fw-bold search-tab-btn" data-action="{{ route('public.projects') }}">
                    <i class="bi bi-diagram-3 me-1"></i> {{ setting('landing_search_tab_developments', 'Developments') }}
                </button>
            </li>
        </ul>
    </div>

    <!-- Search Form -->
    <form action="{{ route('public.properties') }}" method="GET" id="heroSearchForm" class="row g-2 g-sm-3 align-items-center">
        <input type="hidden" name="listing_type" id="heroListingType" value="{{ request('listing_type', 'Sale') }}">

        <!-- Keyword / Area -->
        <div class="col-12 col-lg-4">
            <label class="form-label small fw-bold text-muted mb-1 d-block text-start"><i class="bi bi-search me-1 text-primary"></i> Keyword / Area / ID</label>
            <input type="text" name="q" class="form-control fs-6" placeholder="e.g. Kihonda, Masaki, Villa..." value="{{ request('q') }}" style="height: 48px;">
        </div>

        <!-- Location / City -->
        <div class="col-12 col-sm-6 col-lg-3">
            <label class="form-label small fw-bold text-muted mb-1 d-block text-start"><i class="bi bi-geo-alt me-1 text-danger"></i> Location</label>
            <select name="city" class="form-select fs-6" style="height: 48px;">
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
        <div class="col-12 col-sm-6 col-lg-3">
            <label class="form-label small fw-bold text-muted mb-1 d-block text-start"><i class="bi bi-house me-1 text-info"></i> Property Type</label>
            <select name="type" class="form-select fs-6" style="height: 48px;">
                <option value="">All Types</option>
                @foreach(\App\Models\PropertyType::where('is_active', true)->get() as $t)
                    <option value="{{ $t->id }}" {{ request('type') == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                @endforeach
            </select>
        </div>

        <!-- Search Button -->
        <div class="col-12 col-lg-2 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100 py-2 fs-6 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2" style="height: 48px; margin-top: auto;">
                <i class="bi {{ setting('landing_search_btn_icon', 'bi-search') }}"></i> {{ setting('landing_search_btn_text', 'Search') }}
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
