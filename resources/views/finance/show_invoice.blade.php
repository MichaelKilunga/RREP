@extends('layouts.app')

@section('title', __('ops.invoice').' '.$invoice->invoice_number)

@section('styles')
<style>
    .invoice-sheet { max-width: 210mm; }
    .invoice-sheet .table > :not(caption) > * > * { padding: 0.5rem 0.6rem; }
    @media print {
        @page { size: A4; margin: 12mm; }
        .invoice-sheet { max-width: 100%; border: 0 !important; padding: 0 !important; }
        .invoice-sheet .table-light th { background: #f1f5f9 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        a[href]::after { content: ""; }
    }
</style>
@endsection

@section('content')
@php
    $invOrg = $invoice->organization ?? current_organization();
    $invBranding = $invOrg?->branding ?: \App\Models\BrandingConfig::first();
    $invCompanyName = setting('company_name', $invOrg?->name ?? 'RehoSpace Real Estate');
    $invPhone = setting('contact_phone', $invOrg?->phone);
    $invEmail = setting('contact_email', $invOrg?->email);
    $invAddress = setting('contact_address', trim(($invOrg?->address ? $invOrg->address.', ' : '').($invOrg?->city ?? '').', '.($invOrg?->country ?? 'Tanzania'), ', '));
    $invTin = setting('company_tin', $invOrg?->tax_number);
    $bankName = setting('payment_bank_name');
    $bankAccount = setting('payment_bank_account');
    $bankAccountName = setting('payment_bank_account_name', $invCompanyName);
    $mobileMoneyName = setting('payment_mobile_money_name');
    $mobileMoneyNumber = setting('payment_mobile_money_number');
    $customerDigits = preg_replace('/\D+/', '', (string) $invoice->customer?->phone);
    if (str_starts_with($customerDigits, '0')) {
        $customerDigits = '255'.substr($customerDigits, 1);
    }
    $waText = __('ops.invoice_whatsapp_text', [
        'name' => $invoice->customer?->first_name ?? '',
        'number' => $invoice->invoice_number,
        'amount' => format_currency($invoice->balance_due, $invoice->currency),
        'due' => $invoice->due_date->format('d M Y'),
        'company' => $invCompanyName,
    ]);
    $statusSlug = strtolower(str_replace(' ', '', $invoice->status));
@endphp

<div class="page-header d-print-none">
    <div class="min-w-0">
        <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
            <h1 class="brand-font mb-0">{{ __('ops.invoice') }}: {{ $invoice->invoice_number }}</h1>
            <span class="badge badge-status-{{ $statusSlug }}">{{ $invoice->status }}</span>
        </div>
        <p class="text-muted small mb-0">{{ __('ops.balance_due') }}: <strong class="text-danger">{{ format_currency($invoice->balance_due, $invoice->currency) }}</strong> &bull; {{ __('ops.due_date') }}: {{ $invoice->due_date->format('d M Y') }}</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('finance.invoices') }}" class="btn btn-light border"><i class="bi bi-arrow-left me-1"></i> {{ __('ops.back') }}</a>
        @if($customerDigits)
            <a href="https://wa.me/{{ $customerDigits }}?text={{ rawurlencode($waText) }}" target="_blank" rel="noopener" class="btn btn-whatsapp"><i class="bi bi-whatsapp me-1"></i> {{ __('ops.send_via_whatsapp') }}</a>
        @endif
        <button type="button" onclick="window.print()" class="btn btn-dark"><i class="bi bi-printer me-1"></i> {{ __('ops.print_pdf') }}</button>
    </div>
</div>

<div class="card p-3 p-sm-4 p-md-5 bg-white mx-auto shadow-sm invoice-sheet">
    <!-- Invoice Header -->
    <div class="row g-3 border-bottom pb-3 pb-md-4 mb-3 mb-md-4">
        <div class="col-12 col-sm-7">
            @if(!empty($invBranding?->header_logo))
                <div class="mb-2">
                    <img src="{{ $invBranding->header_logo }}" alt="{{ $invCompanyName }}" class="object-fit-contain" style="max-height: 48px; max-width: 180px;" loading="lazy">
                </div>
            @endif
            <h2 class="brand-font text-primary h4 mb-1">{{ $invCompanyName }}</h2>
            @if($invAddress)<div class="text-muted small">{{ $invAddress }}</div>@endif
            @if($invPhone)<div class="text-muted small">{{ __('ops.phone') }}: {{ format_phone_tz($invPhone) }}</div>@endif
            @if($invEmail)<div class="text-muted small">{{ $invEmail }}</div>@endif
            <div class="text-muted small">TIN: {{ $invTin ?: '—' }}</div>
        </div>
        <div class="col-12 col-sm-5 text-sm-end">
            <h3 class="brand-font text-dark mb-1">{{ __('ops.tax_invoice') }}</h3>
            <div class="fw-bold text-primary fs-5">{{ $invoice->invoice_number }}</div>
            <div class="small text-muted">{{ __('ops.issue_date') }}: {{ $invoice->issue_date->format('d M Y') }}</div>
            <div class="small text-muted">{{ __('ops.due_date') }}: <strong>{{ $invoice->due_date->format('d M Y') }}</strong></div>
            <div class="small text-muted">{{ __('app.currency') }}: {{ $invoice->currency ?: 'TZS' }}</div>
        </div>
    </div>

    <!-- Bill To -->
    <div class="row g-3 mb-4">
        <div class="col-7">
            <div class="text-muted small text-uppercase fw-bold mb-1">{{ __('ops.billed_to') }}</div>
            <div class="fw-bold">{{ $invoice->customer?->full_name }}</div>
            @if($invoice->customer?->company_name)<div class="small text-muted">{{ $invoice->customer->company_name }}</div>@endif
            @if($invoice->customer?->phone)<div class="small text-muted">{{ format_phone_tz($invoice->customer->phone) }}</div>@endif
            @if($invoice->customer?->email)<div class="small text-muted text-break">{{ $invoice->customer->email }}</div>@endif
            @if($invoice->customer?->tax_number)<div class="small text-muted">TIN: {{ $invoice->customer->tax_number }}</div>@endif
            @if($invoice->customer?->national_id_passport)<div class="small text-muted">NIDA: {{ $invoice->customer->national_id_passport }}</div>@endif
        </div>
        @if($invoice->property)
            <div class="col-5 text-end">
                <div class="text-muted small text-uppercase fw-bold mb-1">{{ __('ops.property_reference') }}</div>
                <div class="fw-semibold">{{ $invoice->property->title }}</div>
                <div class="small text-muted">{{ __('ops.code') }}: {{ $invoice->property->property_code }}</div>
                @if($invoice->property->city)<div class="small text-muted">{{ $invoice->property->city }}</div>@endif
            </div>
        @endif
    </div>

    <!-- Items Table -->
    <div class="table-responsive mb-4">
        <table class="table table-bordered align-middle small mb-0">
            <thead class="table-light">
                <tr>
                    <th>{{ __('ops.item_description') }}</th>
                    <th class="text-center">{{ __('ops.qty') }}</th>
                    <th class="text-end">{{ __('ops.unit_price') }}</th>
                    <th class="text-end">{{ __('app.total') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoice->items as $item)
                    <tr>
                        <td>{{ $item->description }}</td>
                        <td class="text-center">{{ (int) $item->quantity }}</td>
                        <td class="text-end text-nowrap">{{ format_currency($item->unit_price, $invoice->currency) }}</td>
                        <td class="text-end fw-bold text-nowrap">{{ format_currency($item->total_amount, $invoice->currency) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-3">{{ __('ops.no_line_items') }}</td></tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="3" class="text-end">{{ __('ops.subtotal') }}</th>
                    <th class="text-end text-nowrap">{{ format_currency($invoice->subtotal, $invoice->currency) }}</th>
                </tr>
                @if($invoice->discount_amount > 0)
                    <tr>
                        <th colspan="3" class="text-end">{{ __('ops.discount') }}</th>
                        <th class="text-end text-nowrap">- {{ format_currency($invoice->discount_amount, $invoice->currency) }}</th>
                    </tr>
                @endif
                @if($invoice->tax_amount > 0)
                    <tr>
                        <th colspan="3" class="text-end">VAT ({{ rtrim(rtrim(number_format($invoice->tax_rate ?: 18, 2), '0'), '.') }}%)</th>
                        <th class="text-end text-nowrap">{{ format_currency($invoice->tax_amount, $invoice->currency) }}</th>
                    </tr>
                @endif
                <tr class="table-light">
                    <th colspan="3" class="text-end fs-6">{{ __('ops.total_amount') }}</th>
                    <th class="text-end fs-6 text-primary text-nowrap">{{ format_currency($invoice->total_amount, $invoice->currency) }}</th>
                </tr>
                <tr>
                    <th colspan="3" class="text-end text-success">{{ __('ops.paid_amount') }}</th>
                    <th class="text-end text-success fw-bold text-nowrap">{{ format_currency($invoice->paid_amount, $invoice->currency) }}</th>
                </tr>
                <tr class="table-danger">
                    <th colspan="3" class="text-end text-danger fs-6">{{ __('ops.balance_due') }}</th>
                    <th class="text-end text-danger fs-6 fw-bold text-nowrap">{{ format_currency($invoice->balance_due, $invoice->currency) }}</th>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Payment instructions -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-6">
            <div class="border rounded p-3 h-100">
                <div class="fw-bold small text-uppercase mb-2"><i class="bi bi-bank me-1 text-primary"></i>{{ __('ops.pay_by_bank') }}</div>
                <div class="small">{{ __('ops.bank_name') }}: <strong>{{ $bankName ?: __('ops.bank_placeholder') }}</strong></div>
                <div class="small">{{ __('ops.account_name') }}: <strong>{{ $bankAccountName }}</strong></div>
                <div class="small">{{ __('ops.account_number') }}: <strong>{{ $bankAccount ?: '0000 0000 0000' }}</strong></div>
                <div class="small text-muted mt-1">{{ __('ops.bank_reference_hint', ['number' => $invoice->invoice_number]) }}</div>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="border rounded p-3 h-100">
                <div class="fw-bold small text-uppercase mb-2"><i class="bi bi-phone me-1 text-success"></i>{{ __('ops.pay_by_mobile_money') }}</div>
                <div class="small">{{ __('ops.mobile_money_network') }}: <strong>{{ $mobileMoneyName ?: __('ops.mobile_money_placeholder') }}</strong></div>
                <div class="small">{{ __('ops.lipa_namba') }}: <strong>{{ $mobileMoneyNumber ?: '000000' }}</strong></div>
                <div class="small">{{ __('ops.account_name') }}: <strong>{{ $bankAccountName }}</strong></div>
                <div class="small text-muted mt-1">{{ __('ops.mobile_money_hint') }}</div>
            </div>
        </div>
    </div>

    <!-- Payment Receipts section -->
    @if($invoice->payments->count() > 0)
        <div class="mb-4">
            <h6 class="brand-font mb-2">{{ __('ops.recorded_payments') }}</h6>
            <ul class="list-group list-group-flush border rounded">
                @foreach($invoice->payments as $pay)
                    <li class="list-group-item d-flex justify-content-between align-items-center gap-2 small">
                        <div class="min-w-0">
                            <strong>{{ $pay->payment_number }}</strong> &bull; {{ $pay->payment_date->format('d M Y') }} &bull; {{ tz_payment_methods()[$pay->payment_method] ?? $pay->payment_method }}
                            @if($pay->reference_number)<span class="text-muted d-block d-sm-inline">({{ __('ops.ref') }}: {{ $pay->reference_number }})</span>@endif
                        </div>
                        <span class="badge badge-status-paid text-nowrap">{{ format_currency($pay->amount, $pay->currency) }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($invoice->notes)
        <div class="small text-muted mb-3"><strong>{{ __('ops.notes') }}:</strong> {{ $invoice->notes }}</div>
    @endif

    <div class="border-top pt-3 text-center text-muted small">
        <p class="mb-1">{{ __('ops.invoice_thanks', ['company' => $invCompanyName]) }}</p>
        <p class="mb-0">{{ __('ops.invoice_footer_note') }}</p>
    </div>
</div>
@endsection
