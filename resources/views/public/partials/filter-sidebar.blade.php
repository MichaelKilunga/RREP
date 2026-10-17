@php
    $idPrefix = $idPrefix ?? 'flt';
    $asCard = $asCard ?? true;
    $regionOptions = collect(tz_regions())->merge(collect($cities ?? []))->filter()->unique()->sort()->values();
    $selectedAmenities = array_map('intval', (array) request('amenities', []));
@endphp
<div class="{{ $asCard ? 'card border shadow-sm p-3 p-xl-4 rounded-4 bg-white' : '' }} w-100">
    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
        <h2 class="h5 brand-font mb-0"><i class="bi bi-funnel text-primary me-2"></i>{{ __('marketplace.filters') }}</h2>
        <a href="{{ route('public.properties') }}" class="btn btn-sm btn-link text-danger text-decoration-none p-0 fw-semibold">{{ __('marketplace.reset_all') }}</a>
    </div>

    <form action="{{ route('public.properties') }}" method="GET" data-no-loading>
        <div class="mb-3">
            <label for="{{ $idPrefix }}-q" class="form-label small fw-bold text-muted">{{ __('marketplace.keyword_or_id') }}</label>
            <div class="input-group">
                <span class="input-group-text bg-light"><i class="bi bi-search text-muted"></i></span>
                <input type="search" id="{{ $idPrefix }}-q" name="q" class="form-control" placeholder="{{ __('marketplace.keyword_placeholder') }}" value="{{ request('q') }}" autocomplete="off">
            </div>
        </div>

        <div class="mb-3">
            <label for="{{ $idPrefix }}-listing-type" class="form-label small fw-bold text-muted">{{ __('marketplace.listing_purpose') }}</label>
            <select id="{{ $idPrefix }}-listing-type" name="listing_type" class="form-select">
                <option value="">{{ __('marketplace.any_purpose') }}</option>
                <option value="Sale" @selected(request('listing_type') === 'Sale')>{{ __('marketplace.buy') }}</option>
                <option value="Rent" @selected(request('listing_type') === 'Rent')>{{ __('marketplace.rent') }}</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="{{ $idPrefix }}-city" class="form-label small fw-bold text-muted">{{ __('marketplace.region') }}</label>
            <select id="{{ $idPrefix }}-city" name="city" class="form-select">
                <option value="">{{ __('marketplace.all_regions') }}</option>
                @foreach($regionOptions as $region)
                    <option value="{{ $region }}" @selected(request('city') === $region)>{{ $region }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="{{ $idPrefix }}-type" class="form-label small fw-bold text-muted">{{ __('app.property_type') }}</label>
            <select id="{{ $idPrefix }}-type" name="type" class="form-select">
                <option value="">{{ __('marketplace.all_property_types') }}</option>
                @foreach($propertyTypes ?? [] as $pt)
                    <option value="{{ $pt->id }}" @selected((string) request('type') === (string) $pt->id)>{{ $pt->name }}</option>
                @endforeach
            </select>
        </div>

        <fieldset class="mb-3">
            <legend class="form-label small fw-bold text-muted fs-6">{{ __('marketplace.price_range') }}</legend>
            <div class="row g-2">
                <div class="col-6">
                    <label for="{{ $idPrefix }}-min-price" class="visually-hidden">{{ __('marketplace.min') }}</label>
                    <div class="input-group">
                        <span class="input-group-text px-2 small">TSh</span>
                        <input type="number" id="{{ $idPrefix }}-min-price" name="min_price" class="form-control" inputmode="decimal" min="0" placeholder="{{ __('marketplace.min') }}" value="{{ request('min_price') }}">
                    </div>
                </div>
                <div class="col-6">
                    <label for="{{ $idPrefix }}-max-price" class="visually-hidden">{{ __('marketplace.max') }}</label>
                    <div class="input-group">
                        <span class="input-group-text px-2 small">TSh</span>
                        <input type="number" id="{{ $idPrefix }}-max-price" name="max_price" class="form-control" inputmode="decimal" min="0" placeholder="{{ __('marketplace.max') }}" value="{{ request('max_price') }}">
                    </div>
                </div>
            </div>
        </fieldset>

        <fieldset class="mb-3">
            <legend class="form-label small fw-bold text-muted fs-6">{{ __('app.bedrooms') }}</legend>
            <div class="d-flex flex-wrap gap-1">
                <input type="radio" class="btn-check" name="bedrooms" id="{{ $idPrefix }}-bed-any" value="" @checked(! request()->filled('bedrooms'))>
                <label class="btn btn-outline-secondary btn-sm flex-fill" for="{{ $idPrefix }}-bed-any">{{ __('marketplace.any') }}</label>
                @foreach([1, 2, 3, 4, 5] as $bed)
                    <input type="radio" class="btn-check" name="bedrooms" id="{{ $idPrefix }}-bed-{{ $bed }}" value="{{ $bed }}" @checked((string) request('bedrooms') === (string) $bed)>
                    <label class="btn btn-outline-secondary btn-sm flex-fill" for="{{ $idPrefix }}-bed-{{ $bed }}">{{ $bed }}+</label>
                @endforeach
            </div>
        </fieldset>

        <fieldset class="mb-3">
            <legend class="form-label small fw-bold text-muted fs-6">{{ __('app.bathrooms') }}</legend>
            <div class="d-flex flex-wrap gap-1">
                <input type="radio" class="btn-check" name="bathrooms" id="{{ $idPrefix }}-bath-any" value="" @checked(! request()->filled('bathrooms'))>
                <label class="btn btn-outline-secondary btn-sm flex-fill" for="{{ $idPrefix }}-bath-any">{{ __('marketplace.any') }}</label>
                @foreach([1, 2, 3, 4] as $bath)
                    <input type="radio" class="btn-check" name="bathrooms" id="{{ $idPrefix }}-bath-{{ $bath }}" value="{{ $bath }}" @checked((string) request('bathrooms') === (string) $bath)>
                    <label class="btn btn-outline-secondary btn-sm flex-fill" for="{{ $idPrefix }}-bath-{{ $bath }}">{{ $bath }}+</label>
                @endforeach
            </div>
        </fieldset>

        @if(isset($amenities) && $amenities->count())
            <fieldset class="mb-3">
                <legend class="form-label small fw-bold text-muted fs-6">{{ __('marketplace.amenities_features') }}</legend>
                <div class="overflow-auto" style="max-height: 180px;">
                    @foreach($amenities as $am)
                        <div class="form-check small mb-1">
                            <input class="form-check-input" type="checkbox" name="amenities[]" value="{{ $am->id }}" id="{{ $idPrefix }}-am-{{ $am->id }}" @checked(in_array($am->id, $selectedAmenities, true))>
                            <label class="form-check-label" for="{{ $idPrefix }}-am-{{ $am->id }}">{{ $am->name }}</label>
                        </div>
                    @endforeach
                </div>
            </fieldset>
        @endif

        <button type="submit" class="btn btn-primary w-100 fw-bold shadow-sm">
            <i class="bi bi-search me-1"></i>{{ __('marketplace.apply_filters') }}
        </button>
    </form>
</div>
