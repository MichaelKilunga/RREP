{{--
    Shared create/edit branch fields.
    Variables: prefix (id prefix, e.g. createBranch / editBranch), branch (Branch|null), regions, branchStatuses
--}}
@php
    $suffix = $branch?->id ?? '';
    $fieldId = fn (string $name) => $prefix.$name.$suffix;
    $currentCity = $branch?->city;
    $cityOptions = $regions;
    if ($currentCity && ! in_array($currentCity, $cityOptions, true)) {
        array_unshift($cityOptions, $currentCity);
    }
@endphp

<div class="row g-3">
    <div class="col-12">
        <label class="form-label fw-semibold" for="{{ $fieldId('Name') }}">{{ __('settings.branch_name') }} <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control" id="{{ $fieldId('Name') }}" value="{{ $branch?->name }}" placeholder="{{ __('settings.branch_name_placeholder') }}" autocomplete="organization" required>
    </div>
    <div class="col-12 col-md-6">
        <label class="form-label fw-semibold" for="{{ $fieldId('Code') }}">{{ __('settings.branch_code') }} <span class="text-danger">*</span></label>
        <input type="text" name="code" class="form-control text-uppercase font-monospace" id="{{ $fieldId('Code') }}" value="{{ $branch?->code }}" placeholder="BR-DOD" data-uppercase autocomplete="off" required>
        <div class="form-text">{{ __('settings.branch_code_help') }}</div>
    </div>
    <div class="col-12 col-md-6">
        <label class="form-label fw-semibold" for="{{ $fieldId('City') }}">{{ __('settings.region') }}</label>
        <select name="city" class="form-select" id="{{ $fieldId('City') }}" autocomplete="address-level1">
            <option value="">{{ __('settings.select_region') }}</option>
            @foreach($cityOptions as $region)
                <option value="{{ $region }}" @selected($currentCity === $region)>{{ $region }}</option>
            @endforeach
        </select>
        <div class="form-text">{{ __('settings.region_help') }}</div>
    </div>
    <div class="col-12">
        <label class="form-label fw-semibold" for="{{ $fieldId('Address') }}">{{ __('settings.physical_address') }}</label>
        <input type="text" name="address" class="form-control" id="{{ $fieldId('Address') }}" value="{{ $branch?->address }}" placeholder="{{ __('settings.physical_address_placeholder') }}" autocomplete="street-address">
    </div>
    <div class="col-12 col-md-6">
        <label class="form-label fw-semibold" for="{{ $fieldId('Phone') }}">{{ __('settings.phone_number') }}</label>
        <input type="tel" name="phone" class="form-control" id="{{ $fieldId('Phone') }}" value="{{ $branch?->phone }}" placeholder="0784 100 200" data-tz-phone autocomplete="tel">
    </div>
    <div class="col-12 col-md-6">
        <label class="form-label fw-semibold" for="{{ $fieldId('Email') }}">{{ __('settings.official_email') }}</label>
        <input type="email" name="email" class="form-control" id="{{ $fieldId('Email') }}" value="{{ $branch?->email }}" placeholder="dodoma@company.co.tz" inputmode="email" autocomplete="email">
    </div>
    <div class="col-12 col-md-6">
        <label class="form-label fw-semibold" for="{{ $fieldId('Status') }}">{{ __('app.status') }}</label>
        <select name="status" class="form-select" id="{{ $fieldId('Status') }}">
            @foreach($branchStatuses as $value => $label)
                <option value="{{ $value }}" @selected(($branch?->status ?? 'Active') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-12 col-md-6 d-flex align-items-end">
        <div class="form-check form-switch p-2.5 ps-5 bg-light rounded-3 border w-100">
            <input class="form-check-input" type="checkbox" role="switch" name="is_main" value="1" id="{{ $fieldId('IsMain') }}" @checked($branch?->is_main)>
            <label class="form-check-label small fw-semibold" for="{{ $fieldId('IsMain') }}">{{ __('settings.headquarters_hq') }}</label>
            <div class="form-text">{{ __('settings.headquarters_help') }}</div>
        </div>
    </div>
</div>
