@php
    $searchTypes = \App\Models\PropertyType::where('is_active', true)->orderBy('name')->get(['id', 'name']);
@endphp
<div class="card hero-search p-3 p-sm-4 shadow-lg border-0 rounded-4 bg-white text-dark mx-auto max-w-4xl text-start">
    <!-- Intent tabs: horizontal scroll on phones -->
    <div class="mb-3">
        <ul class="nav nav-pills nav-scroll gap-2 pb-1" id="searchTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button type="button" class="nav-link active rounded-pill px-3 py-2 fw-bold search-tab-btn" data-type="Sale" data-action="{{ route('public.properties') }}">
                    <i class="bi bi-bag-check me-1"></i>{{ setting_trans('landing_search_tab_buy', 'Nunua', 'Buy') }}
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button type="button" class="nav-link rounded-pill px-3 py-2 fw-bold search-tab-btn" data-type="Rent" data-action="{{ route('public.properties') }}">
                    <i class="bi bi-key me-1"></i>{{ setting_trans('landing_search_tab_rent', 'Panga', 'Rent') }}
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button type="button" class="nav-link rounded-pill px-3 py-2 fw-bold search-tab-btn" data-category="Land" data-action="{{ route('public.land') }}">
                    <i class="bi bi-map me-1"></i>{{ setting_trans('landing_search_tab_land', 'Ardhi & Viwanja', 'Land & Plots') }}
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button type="button" class="nav-link rounded-pill px-3 py-2 fw-bold search-tab-btn" data-action="{{ route('public.projects') }}">
                    <i class="bi bi-diagram-3 me-1"></i>{{ setting_trans('landing_search_tab_developments', 'Miradi', 'Developments') }}
                </button>
            </li>
        </ul>
    </div>

    <form action="{{ route('public.properties') }}" method="GET" id="heroSearchForm" class="row g-2 g-sm-3 align-items-end" data-no-loading>
        <input type="hidden" name="listing_type" id="heroListingType" value="{{ request('listing_type', 'Sale') }}">

        <div class="col-12 col-lg-4">
            <label for="heroKeyword" class="form-label small fw-bold text-muted mb-1"><i class="bi bi-search me-1 text-primary"></i>{{ __('app.keyword_area_id') }}</label>
            <input type="search" id="heroKeyword" name="q" class="form-control" placeholder="{{ __('app.e_g_kihonda_masaki_villa') }}" value="{{ request('q') }}" autocomplete="off" enterkeyhint="search">
        </div>

        <div class="col-12 col-sm-6 col-lg-3">
            <label for="heroCity" class="form-label small fw-bold text-muted mb-1"><i class="bi bi-geo-alt me-1 text-danger"></i>{{ __('app.location_region') }}</label>
            <select id="heroCity" name="city" class="form-select">
                <option value="">{{ __('app.all_locations') }}</option>
                @foreach(tz_regions() as $region)
                    <option value="{{ $region }}" @selected(request('city') === $region)>{{ $region }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-12 col-sm-6 col-lg-3">
            <label for="heroType" class="form-label small fw-bold text-muted mb-1"><i class="bi bi-house me-1 text-info"></i>{{ __('app.property_type') }}</label>
            <select id="heroType" name="type" class="form-select">
                <option value="">{{ __('app.all_types') }}</option>
                @foreach($searchTypes as $t)
                    <option value="{{ $t->id }}" @selected((string) request('type') === (string) $t->id)>{{ $t->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-12 col-lg-2 d-grid">
            <button type="submit" class="btn btn-primary btn-search fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2">
                <i class="bi {{ setting('landing_search_btn_icon', 'bi-search') }}"></i>{{ setting_trans('landing_search_btn_text', 'Tafuta', 'Search') }}
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const tabBtns = document.querySelectorAll('.search-tab-btn');
    const form = document.getElementById('heroSearchForm');
    const listingTypeInput = document.getElementById('heroListingType');
    if (!form || !listingTypeInput) {
        return;
    }

    tabBtns.forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            tabBtns.forEach(function (b) { b.classList.remove('active'); });
            this.classList.add('active');

            const action = this.getAttribute('data-action');
            if (action) {
                form.action = action;
            }

            listingTypeInput.value = this.getAttribute('data-type') || '';
            this.scrollIntoView({ block: 'nearest', inline: 'center', behavior: 'smooth' });
        });
    });
});
</script>
