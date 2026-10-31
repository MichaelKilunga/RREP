@extends('layouts.app')

@section('title', __('ops.edit_invoice').' '.$invoice->invoice_number)

@section('content')
<div class="page-header">
    <div class="min-w-0">
        <a href="{{ route('finance.invoices.show', $invoice) }}" class="small text-muted text-decoration-none"><i class="bi bi-arrow-left me-1"></i>{{ $invoice->invoice_number }}</a>
        <h1 class="brand-font mb-1">{{ __('ops.edit_invoice') }} {{ $invoice->invoice_number }}</h1>
        <p class="text-muted small mb-0">{{ __('ops.edit_invoice_hint') }}</p>
    </div>
</div>

<form class="card p-3 p-md-4 inv-form" action="{{ route('finance.invoices.update', $invoice) }}" method="POST">
    @csrf
    @method('PUT')
    @include('finance.partials.invoice-form', ['invoice' => $invoice])
    <div class="d-flex justify-content-end gap-2 mt-4">
        <a href="{{ route('finance.invoices.show', $invoice) }}" class="btn btn-light border">{{ __('app.cancel') }}</a>
        <button type="submit" class="btn btn-primary"><i class="bi bi-check2-circle me-1"></i>{{ __('app.save') }}</button>
    </div>
</form>
@endsection

@section('scripts')
@include('finance.partials.invoice-form-script')
@endsection
