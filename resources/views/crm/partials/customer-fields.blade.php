@php $c = $customer ?? null; $p = $prefix ?? 'cust'; @endphp
<div class="row g-2 mb-3">
    <div class="col-12 col-sm-6">
        <label for="{{ $p }}FirstName" class="form-label small fw-semibold">{{ __('ops.first_name') }} *</label>
        <input type="text" id="{{ $p }}FirstName" name="first_name" class="form-control" autocomplete="given-name" value="{{ old('first_name', $c?->first_name) }}" required>
    </div>
    <div class="col-12 col-sm-6">
        <label for="{{ $p }}LastName" class="form-label small fw-semibold">{{ __('ops.last_name') }} *</label>
        <input type="text" id="{{ $p }}LastName" name="last_name" class="form-control" autocomplete="family-name" value="{{ old('last_name', $c?->last_name) }}" required>
    </div>
</div>
<div class="row g-2 mb-3">
    <div class="col-12 col-sm-6">
        <label for="{{ $p }}Type" class="form-label small fw-semibold">{{ __('ops.customer_type') }}</label>
        <select id="{{ $p }}Type" name="customer_type" class="form-select">
            <option value="Individual" @selected(old('customer_type', $c?->customer_type) !== 'Corporate')>{{ __('ops.individual') }}</option>
            <option value="Corporate" @selected(old('customer_type', $c?->customer_type) === 'Corporate')>{{ __('ops.corporate') }}</option>
        </select>
    </div>
    <div class="col-12 col-sm-6">
        <label for="{{ $p }}Company" class="form-label small fw-semibold">{{ __('ops.company_name') }}</label>
        <input type="text" id="{{ $p }}Company" name="company_name" class="form-control" autocomplete="organization" value="{{ old('company_name', $c?->company_name) }}">
    </div>
</div>
<div class="row g-2 mb-3">
    <div class="col-12 col-sm-6">
        <label for="{{ $p }}Phone" class="form-label small fw-semibold">{{ __('ops.phone_number') }} *</label>
        <input type="tel" id="{{ $p }}Phone" name="phone" class="form-control" data-tz-phone placeholder="0784 100 200" autocomplete="tel" value="{{ old('phone', $c?->phone) }}" required>
    </div>
    <div class="col-12 col-sm-6">
        <label for="{{ $p }}AltPhone" class="form-label small fw-semibold">{{ __('ops.alt_phone') }}</label>
        <input type="tel" id="{{ $p }}AltPhone" name="alt_phone" class="form-control" data-tz-phone value="{{ old('alt_phone', $c?->alt_phone) }}">
    </div>
</div>
<div class="mb-3">
    <label for="{{ $p }}Email" class="form-label small fw-semibold">{{ __('ops.email_address') }}</label>
    <input type="email" id="{{ $p }}Email" name="email" class="form-control" autocomplete="email" inputmode="email" value="{{ old('email', $c?->email) }}">
</div>
<div class="row g-2 mb-3">
    <div class="col-12 col-sm-6">
        <label for="{{ $p }}Nida" class="form-label small fw-semibold">{{ __('ops.nida_number') }}</label>
        <input type="text" id="{{ $p }}Nida" name="national_id_passport" class="form-control" placeholder="19900101-12345-00001-23" value="{{ old('national_id_passport', $c?->national_id_passport) }}">
    </div>
    <div class="col-12 col-sm-6">
        <label for="{{ $p }}Tin" class="form-label small fw-semibold">{{ __('ops.tin_number') }}</label>
        <input type="text" id="{{ $p }}Tin" name="tax_number" class="form-control" placeholder="123-456-789" value="{{ old('tax_number', $c?->tax_number) }}">
    </div>
</div>
<div class="row g-2 mb-3">
    <div class="col-12 col-sm-6">
        <label for="{{ $p }}Region" class="form-label small fw-semibold">{{ __('ops.region') }}</label>
        <select id="{{ $p }}Region" name="city" class="form-select">
            <option value="">{{ __('ops.select_region') }}</option>
            @foreach(tz_regions() as $region)
                <option value="{{ $region }}" @selected(old('city', $c?->city) === $region)>{{ $region }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-12 col-sm-6">
        <label for="{{ $p }}Address" class="form-label small fw-semibold">{{ __('ops.street_ward_district') }}</label>
        <input type="text" id="{{ $p }}Address" name="address" class="form-control" placeholder="{{ __('ops.address_placeholder') }}" autocomplete="street-address" value="{{ old('address', $c?->address) }}">
    </div>
</div>
<div>
    <label for="{{ $p }}Notes" class="form-label small fw-semibold">{{ __('ops.internal_notes') }}</label>
    <textarea id="{{ $p }}Notes" name="notes" rows="2" class="form-control">{{ old('notes', $c?->notes) }}</textarea>
</div>
