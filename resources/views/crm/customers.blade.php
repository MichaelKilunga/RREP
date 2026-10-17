@extends('layouts.app')

@section('title', __('app.customers'))

@section('content')
<div class="page-header">
    <div class="min-w-0">
        <h1 class="brand-font mb-1">{{ __('ops.customers_title') }}</h1>
        <p class="text-muted small mb-0">{{ __('ops.customers_subtitle', ['count' => $customers->total()]) }}</p>
    </div>
    <div class="page-actions">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newCustomerModal">
            <i class="bi bi-person-plus-fill me-1"></i> {{ __('ops.add_customer') }}
        </button>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 table-stack small">
            <thead class="table-light">
                <tr>
                    <th>{{ __('ops.customer') }}</th>
                    <th>{{ __('ops.type') }}</th>
                    <th>{{ __('ops.phone_email') }}</th>
                    <th>{{ __('ops.nida_tin') }}</th>
                    <th>{{ __('ops.region_address') }}</th>
                    <th>{{ __('ops.kyc_status') }}</th>
                    <th>{{ __('ops.deals_invoices') }}</th>
                    <th class="text-end">{{ __('app.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $c)
                    @php
                        $waDigits = preg_replace('/\D+/', '', (string) $c->phone);
                        if (str_starts_with($waDigits, '0')) {
                            $waDigits = '255'.substr($waDigits, 1);
                        }
                    @endphp
                    <tr>
                        <td class="stack-primary">
                            <div class="fw-bold text-dark">{{ $c->full_name }}</div>
                            @if($c->company_name)
                                <small class="text-muted d-block">{{ $c->company_name }}</small>
                            @endif
                        </td>
                        <td><span class="badge bg-light text-dark border">{{ $c->customer_type === 'Corporate' ? __('ops.corporate') : __('ops.individual') }}</span></td>
                        <td>
                            <div><a href="tel:{{ $c->phone }}" class="text-decoration-none"><i class="bi bi-telephone text-primary me-1"></i>{{ format_phone_tz($c->phone) }}</a></div>
                            @if($c->email)<small class="text-muted text-break">{{ $c->email }}</small>@endif
                        </td>
                        <td>
                            <div class="small"><span class="text-muted">NIDA:</span> {{ $c->national_id_passport ?: '-' }}</div>
                            <div class="small"><span class="text-muted">TIN:</span> {{ $c->tax_number ?: '-' }}</div>
                        </td>
                        <td>
                            <div>{{ $c->city ?: '-' }}</div>
                            @if($c->address)<small class="text-muted">{{ $c->address }}</small>@endif
                        </td>
                        <td><span class="badge badge-status-{{ strtolower($c->kyc_status) }}"><i class="bi bi-shield-check me-1"></i>{{ __('ops.kyc_'.strtolower($c->kyc_status)) }}</span></td>
                        <td>
                            <span class="badge bg-primary-subtle text-primary me-1">{{ $c->sales_deals_count }} {{ __('ops.deals_short') }}</span>
                            <span class="badge bg-secondary-subtle text-secondary">{{ $c->invoices_count }} {{ __('ops.invoices_short') }}</span>
                        </td>
                        <td class="stack-actions text-end">
                            <div class="d-inline-flex gap-1">
                                @if($waDigits)
                                    <a href="https://wa.me/{{ $waDigits }}" target="_blank" rel="noopener" class="btn btn-whatsapp btn-sm" title="WhatsApp"><i class="bi bi-whatsapp"></i><span class="d-md-none ms-1">WhatsApp</span></a>
                                @endif
                                <a href="tel:{{ $c->phone }}" class="btn btn-light border btn-sm" title="{{ __('ops.call') }}"><i class="bi bi-telephone"></i><span class="d-md-none ms-1">{{ __('ops.call') }}</span></a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="stack-no-label">
                            <div class="empty-state">
                                <div class="empty-icon"><i class="bi bi-people"></i></div>
                                <h6>{{ __('ops.no_customers') }}</h6>
                                <p class="small mb-3">{{ __('ops.no_customers_hint') }}</p>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newCustomerModal"><i class="bi bi-person-plus-fill me-1"></i> {{ __('ops.add_customer') }}</button>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($customers->hasPages())
        <div class="p-3 border-top d-flex justify-content-center justify-content-md-end">
            {{ $customers->links() }}
        </div>
    @endif
</div>

<!-- New Customer Modal -->
<div class="modal fade" id="newCustomerModal" tabindex="-1" aria-labelledby="newCustomerModalLabel">
    <div class="modal-dialog modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title brand-font" id="newCustomerModalLabel">{{ __('ops.register_customer') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('app.cancel') }}"></button>
            </div>
            <form action="{{ route('crm.customers.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row g-2 mb-3">
                        <div class="col-12 col-sm-6">
                            <label for="custFirstName" class="form-label small fw-semibold">{{ __('ops.first_name') }} *</label>
                            <input type="text" id="custFirstName" name="first_name" class="form-control" autocomplete="given-name" required>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label for="custLastName" class="form-label small fw-semibold">{{ __('ops.last_name') }} *</label>
                            <input type="text" id="custLastName" name="last_name" class="form-control" autocomplete="family-name" required>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-12 col-sm-6">
                            <label for="custType" class="form-label small fw-semibold">{{ __('ops.customer_type') }}</label>
                            <select id="custType" name="customer_type" class="form-select">
                                <option value="Individual">{{ __('ops.individual') }}</option>
                                <option value="Corporate">{{ __('ops.corporate') }}</option>
                            </select>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label for="custCompany" class="form-label small fw-semibold">{{ __('ops.company_name') }}</label>
                            <input type="text" id="custCompany" name="company_name" class="form-control" autocomplete="organization">
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-12 col-sm-6">
                            <label for="custPhone" class="form-label small fw-semibold">{{ __('ops.phone_number') }} *</label>
                            <input type="tel" id="custPhone" name="phone" class="form-control" data-tz-phone placeholder="0784 100 200" autocomplete="tel" required>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label for="custEmail" class="form-label small fw-semibold">{{ __('ops.email_address') }}</label>
                            <input type="email" id="custEmail" name="email" class="form-control" autocomplete="email" inputmode="email">
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-12 col-sm-6">
                            <label for="custNida" class="form-label small fw-semibold">{{ __('ops.nida_number') }}</label>
                            <input type="text" id="custNida" name="national_id_passport" class="form-control" placeholder="19900101-12345-00001-23" inputmode="numeric">
                        </div>
                        <div class="col-12 col-sm-6">
                            <label for="custTin" class="form-label small fw-semibold">{{ __('ops.tin_number') }}</label>
                            <input type="text" id="custTin" name="tax_number" class="form-control" placeholder="123-456-789" inputmode="numeric">
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-12 col-sm-6">
                            <label for="custRegion" class="form-label small fw-semibold">{{ __('ops.region') }}</label>
                            <select id="custRegion" name="city" class="form-select">
                                <option value="">{{ __('ops.select_region') }}</option>
                                @foreach(tz_regions() as $region)
                                    <option value="{{ $region }}" @selected($region === 'Dar es Salaam')>{{ $region }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label for="custAddress" class="form-label small fw-semibold">{{ __('ops.street_ward_district') }}</label>
                            <input type="text" id="custAddress" name="address" class="form-control" placeholder="{{ __('ops.address_placeholder') }}" autocomplete="street-address">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">{{ __('app.cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('ops.save_customer') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
