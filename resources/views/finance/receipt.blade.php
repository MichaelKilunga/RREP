@extends('layouts.app')

@section('title', __('ops.receipt').' '.$payment->payment_number)

@section('content')
@php
    $companyName = setting('company_name', 'Avenix');
    $isVoid = $payment->status !== 'Completed';
@endphp

<div class="page-header d-print-none">
    <div class="min-w-0">
        <h1 class="brand-font mb-1">{{ __('ops.receipt') }} {{ $payment->payment_number }}</h1>
        @if($isVoid)<span class="badge bg-danger">{{ $payment->status }}</span>@endif
    </div>
    <div class="page-actions">
        <a href="{{ url()->previous() }}" class="btn btn-light border"><i class="bi bi-arrow-left me-1"></i>{{ __('ops.back') }}</a>
        <button type="button" onclick="window.print()" class="btn btn-dark"><i class="bi bi-printer me-1"></i>{{ __('ops.print_pdf') }}</button>
    </div>
</div>

<div class="card p-4 p-md-5 bg-white mx-auto shadow-sm" style="max-width: 640px;">
    <div class="d-flex justify-content-between align-items-start border-bottom pb-3 mb-3">
        <div>
            <h2 class="h4 brand-font mb-0">{{ $companyName }}</h2>
            <div class="text-muted small">{{ setting('contact_address') }}</div>
            <div class="text-muted small">{{ setting('contact_phone') }} · {{ setting('contact_email') }}</div>
        </div>
        <div class="text-end">
            <div class="text-uppercase fw-bold small text-muted">{{ __('ops.payment_receipt') }}</div>
            <div class="fw-bold">{{ $payment->payment_number }}</div>
            <div class="small text-muted">{{ $payment->payment_date?->format('d M Y') }}</div>
        </div>
    </div>

    @if($isVoid)
        <div class="alert alert-danger py-2 small text-center fw-bold">{{ __('ops.receipt_voided') }}</div>
    @endif

    <dl class="row mb-3">
        <dt class="col-5 text-muted fw-normal">{{ __('ops.received_from') }}</dt>
        <dd class="col-7 fw-semibold">{{ $payment->customer?->full_name }}<div class="small text-muted fw-normal">{{ format_phone_tz($payment->customer?->phone) }}</div></dd>
        <dt class="col-5 text-muted fw-normal">{{ __('ops.payment_method') }}</dt>
        <dd class="col-7">{{ tz_payment_methods()[$payment->payment_method] ?? $payment->payment_method }}</dd>
        @if($payment->reference_number)
            <dt class="col-5 text-muted fw-normal">{{ __('ops.reference_txn') }}</dt>
            <dd class="col-7">{{ $payment->reference_number }}</dd>
        @endif
        @if($payment->invoice)
            <dt class="col-5 text-muted fw-normal">{{ __('ops.for_invoice') }}</dt>
            <dd class="col-7">{{ $payment->invoice->invoice_number }}<div class="small text-muted">{{ __('ops.balance_due') }}: {{ format_currency($payment->invoice->balance_due, null, 0) }}</div></dd>
        @endif
        <dt class="col-5 text-muted fw-normal">{{ __('ops.received_by') }}</dt>
        <dd class="col-7">{{ $payment->recorder?->name ?? '—' }}</dd>
    </dl>

    <div class="bg-light rounded-3 p-3 d-flex justify-content-between align-items-center">
        <span class="fw-semibold">{{ __('ops.amount_received') }}</span>
        <span class="fs-4 fw-bold {{ $isVoid ? 'text-decoration-line-through text-muted' : 'text-success' }}">{{ format_currency($payment->amount, $payment->currency, 0) }}</span>
    </div>

    <p class="text-center text-muted small mt-4 mb-0">{{ __('ops.invoice_thanks', ['company' => $companyName]) }}</p>
</div>
@endsection
