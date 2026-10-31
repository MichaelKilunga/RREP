@extends('layouts.app')

@section('title', $customer->full_name)

@section('content')
@php
    $waDigits = preg_replace('/\D+/', '', (string) $customer->phone);
    if (str_starts_with($waDigits, '0')) {
        $waDigits = '255'.substr($waDigits, 1);
    }
@endphp

<div class="page-header">
    <div class="min-w-0">
        <a href="{{ route('crm.customers') }}" class="small text-muted text-decoration-none"><i class="bi bi-arrow-left me-1"></i>{{ __('ops.customers_title') }}</a>
        <h1 class="brand-font mb-1 text-truncate">{{ $customer->full_name }}</h1>
        <div class="d-flex flex-wrap gap-1 small">
            <span class="badge bg-light text-dark border">{{ $customer->customer_type === 'Corporate' ? __('ops.corporate') : __('ops.individual') }}</span>
            @if($customer->company_name)<span class="badge bg-light text-dark border">{{ $customer->company_name }}</span>@endif
            @if($customer->kyc_status)<span class="badge badge-status-{{ strtolower($customer->kyc_status) }}"><i class="bi bi-shield-check me-1"></i>{{ __('ops.kyc_'.strtolower($customer->kyc_status)) }}</span>@endif
            @if($customer->source)<span class="badge bg-info-subtle text-info">{{ $customer->source }}</span>@endif
        </div>
    </div>
    <div class="page-actions">
        @if($waDigits)
            <a href="https://wa.me/{{ $waDigits }}" target="_blank" rel="noopener" class="btn btn-whatsapp"><i class="bi bi-whatsapp me-1"></i>WhatsApp</a>
            <a href="tel:{{ $customer->phone }}" class="btn btn-light border"><i class="bi bi-telephone me-1"></i>{{ __('ops.call') }}</a>
        @endif
        @can('send_sms')
            <button type="button" class="btn btn-light border" data-bs-toggle="modal" data-bs-target="#smsCustomerModal"><i class="bi bi-chat-left-dots me-1 text-primary"></i>SMS</button>
        @endcan
        @can('manage_customers')
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editCustomerModal"><i class="bi bi-pencil me-1"></i>{{ __('app.edit') }}</button>
        @endcan
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3"><div class="kpi-card"><div class="kpi-label">{{ __('ops.invoiced_total') }}</div><div class="kpi-value" title="{{ format_currency($summary['invoiced'], null, 0) }}">{{ format_money_short($summary['invoiced']) }}</div><span class="kpi-icon"><i class="bi bi-receipt"></i></span></div></div>
    <div class="col-6 col-md-3"><div class="kpi-card kpi-success"><div class="kpi-label">{{ __('ops.paid_total') }}</div><div class="kpi-value text-success" title="{{ format_currency($summary['paid'], null, 0) }}">{{ format_money_short($summary['paid']) }}</div></div></div>
    <div class="col-6 col-md-3"><div class="kpi-card kpi-danger"><div class="kpi-label">{{ __('ops.balance_due') }}</div><div class="kpi-value text-danger" title="{{ format_currency($summary['balance'], null, 0) }}">{{ format_money_short($summary['balance']) }}</div></div></div>
    <div class="col-6 col-md-3"><div class="kpi-card kpi-warning"><div class="kpi-label">{{ __('ops.active_holds') }}</div><div class="kpi-value">{{ $summary['active_holds'] }}</div></div></div>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-4">
        <div class="card p-3 mb-4">
            <h2 class="h6 brand-font mb-3">{{ __('ops.contact_details') }}</h2>
            <dl class="row small mb-0">
                <dt class="col-5 text-muted">{{ __('ops.phone_number') }}</dt><dd class="col-7"><a href="tel:{{ $customer->phone }}" class="text-decoration-none">{{ format_phone_tz($customer->phone) }}</a></dd>
                @if($customer->alt_phone)<dt class="col-5 text-muted">{{ __('ops.alt_phone') }}</dt><dd class="col-7">{{ format_phone_tz($customer->alt_phone) }}</dd>@endif
                <dt class="col-5 text-muted">{{ __('ops.email_address') }}</dt><dd class="col-7 text-break">{{ $customer->email ?: '—' }}</dd>
                <dt class="col-5 text-muted">NIDA</dt><dd class="col-7">{{ $customer->national_id_passport ?: '—' }}</dd>
                <dt class="col-5 text-muted">TIN</dt><dd class="col-7">{{ $customer->tax_number ?: '—' }}</dd>
                <dt class="col-5 text-muted">{{ __('ops.region') }}</dt><dd class="col-7">{{ $customer->city ?: '—' }}</dd>
                <dt class="col-5 text-muted">{{ __('ops.street_ward_district') }}</dt><dd class="col-7">{{ $customer->address ?: '—' }}</dd>
                <dt class="col-5 text-muted">{{ __('ops.customer_since') }}</dt><dd class="col-7">{{ $customer->created_at?->format('d M Y') }}</dd>
                <dt class="col-5 text-muted">{{ __('ops.last_contact') }}</dt><dd class="col-7">{{ $customer->last_interaction_at?->diffForHumans() ?? '—' }}</dd>
            </dl>
            @if($customer->notes)
                <div class="border-top mt-3 pt-3 small"><strong class="d-block mb-1">{{ __('ops.internal_notes') }}</strong><span class="text-muted" style="white-space: pre-line;">{{ $customer->notes }}</span></div>
            @endif
        </div>

        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="brand-font">{{ __('app.reservations') }}</span>
                @can('create_reservations')<a href="{{ route('reservations.index') }}" class="small">{{ __('ops.new_reservation') }}</a>@endcan
            </div>
            <div class="list-group list-group-flush small">
                @forelse($customer->reservations as $res)
                    <div class="list-group-item d-flex justify-content-between gap-2">
                        <div class="min-w-0">
                            <strong>{{ $res->reservation_number }}</strong>
                            <div class="text-muted text-truncate">
                                {{ $res->landParcel ? __('pages.plot').' '.$res->landParcel->parcel_number.' · '.$res->landParcel->project?->title : $res->property?->title }}
                            </div>
                        </div>
                        <span class="badge bg-light text-dark border align-self-start">{{ __('ops.reservation_status_'.strtolower($res->status)) }}</span>
                    </div>
                @empty
                    <div class="list-group-item text-muted">{{ __('ops.no_reservations') }}</div>
                @endforelse
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="brand-font">{{ __('ops.invoices_short') }}</span>
                @can('create_invoices')<a href="{{ route('finance.invoices', ['customer' => $customer->id]) }}" class="small">{{ __('ops.new_invoice') }}</a>@endcan
            </div>
            <div class="list-group list-group-flush small">
                @forelse($customer->invoices as $inv)
                    <a href="{{ route('finance.invoices.show', $inv) }}" class="list-group-item list-group-item-action d-flex justify-content-between gap-2">
                        <span><strong>{{ $inv->invoice_number }}</strong><span class="text-muted d-block">{{ $inv->issue_date?->format('d M Y') }}</span></span>
                        <span class="text-end"><span class="fw-bold">{{ format_money_short($inv->total_amount) }}</span><span class="badge bg-light text-dark border d-block mt-1">{{ $inv->status }}</span></span>
                    </a>
                @empty
                    <div class="list-group-item text-muted">{{ __('ops.no_invoices') }}</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-8">
        <div class="card">
            <div class="card-header"><span class="brand-font">{{ __('ops.activity_timeline') }}</span></div>
            <div class="card-body">
                @forelse($timeline as $event)
                    <div class="d-flex gap-3 {{ $loop->last ? '' : 'mb-3 pb-3 border-bottom' }}">
                        <span class="btn-icon bg-{{ $event['tone'] }}-subtle text-{{ $event['tone'] }} flex-shrink-0"><i class="bi {{ $event['icon'] }}"></i></span>
                        <div class="min-w-0 flex-grow-1">
                            <div class="d-flex justify-content-between gap-2">
                                <strong class="small">{{ $event['title'] }}</strong>
                                <span class="text-muted small text-nowrap" title="{{ $event['at'] }}">{{ $event['at']->diffForHumans() }}</span>
                            </div>
                            @if($event['detail'])<div class="text-muted small text-break">{{ $event['detail'] }}</div>@endif
                        </div>
                    </div>
                @empty
                    <div class="empty-state py-4"><div class="empty-icon"><i class="bi bi-clock-history"></i></div><h6>{{ __('ops.no_activity_yet') }}</h6></div>
                @endforelse
            </div>
        </div>

        @can('delete_customers')
            <form action="{{ route('crm.customers.destroy', $customer) }}" method="POST" class="text-end mt-3" onsubmit="return confirm(@json(__('ops.customer_delete_confirm')));">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash me-1"></i>{{ __('ops.delete_customer') }}</button>
            </form>
        @endcan
    </div>
</div>

@can('send_sms')
<div class="modal fade" id="smsCustomerModal" tabindex="-1" aria-labelledby="smsCustomerLabel">
    <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
        <form class="modal-content" action="{{ route('crm.customers.sms', $customer) }}" method="POST">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="smsCustomerLabel">SMS: {{ $customer->full_name }} <small class="text-muted">({{ format_phone_tz($customer->phone) }})</small></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('app.cancel') }}"></button>
            </div>
            <div class="modal-body">
                <label for="smsBody" class="form-label small fw-semibold">{{ __('ops.message') }}</label>
                <textarea id="smsBody" name="message" rows="4" class="form-control" maxlength="640" required>{{ __('ops.sms_default_greeting', ['name' => $customer->first_name]) }}</textarea>
                <div class="form-text" id="smsCounter"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">{{ __('app.cancel') }}</button>
                <button type="submit" class="btn btn-primary"><i class="bi bi-send me-1"></i>{{ __('ops.send_sms') }}</button>
            </div>
        </form>
    </div>
</div>
@endcan

@can('manage_customers')
<div class="modal fade" id="editCustomerModal" tabindex="-1" aria-labelledby="editCustomerLabel">
    <div class="modal-dialog modal-dialog-scrollable modal-fullscreen-sm-down">
        <form class="modal-content" action="{{ route('crm.customers.update', $customer) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title" id="editCustomerLabel">{{ __('ops.edit_customer') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('app.cancel') }}"></button>
            </div>
            <div class="modal-body">
                @include('crm.partials.customer-fields', ['customer' => $customer, 'prefix' => 'editCust'])
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">{{ __('app.cancel') }}</button>
                <button type="submit" class="btn btn-primary">{{ __('app.save') }}</button>
            </div>
        </form>
    </div>
</div>
@endcan
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const body = document.getElementById('smsBody');
    const counter = document.getElementById('smsCounter');
    if (!body || !counter) { return; }
    const template = @json(__('ops.sms_counter'));
    const update = () => {
        const len = body.value.length;
        counter.textContent = template.replace(':chars', len).replace(':parts', Math.max(1, Math.ceil(len / 160)));
    };
    body.addEventListener('input', update);
    update();
});
</script>
@endsection
