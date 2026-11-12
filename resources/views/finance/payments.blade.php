@extends('layouts.app')

@section('title', __('ops.payments_title'))

@section('content')
@php $canDeletePayments = auth()->user()->can('void_invoices'); @endphp
<div class="page-header">
    <div class="min-w-0">
        <h1 class="brand-font mb-1">{{ __('ops.payments_title') }}</h1>
        <p class="text-muted small mb-0">{{ __('ops.payments_subtitle') }}</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('finance.invoices') }}" class="btn btn-light border"><i class="bi bi-receipt me-1"></i>{{ __('app.invoices') }}</a>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-12 col-lg-4">
        <div class="kpi-card kpi-success h-100">
            <div class="kpi-label">{{ __('ops.collected_in_range') }}</div>
            <div class="kpi-value text-success" title="{{ format_currency($total, null, 0) }}">{{ format_money_short($total) }}</div>
            <small class="text-muted">{{ request('from') || request('to') ? (request('from') ?: '…').' → '.(request('to') ?: '…') : __('ops.all_time') }}</small>
            <span class="kpi-icon"><i class="bi bi-cash-stack"></i></span>
        </div>
    </div>
    <div class="col-12 col-lg-8">
        <div class="card p-3 h-100">
            <h2 class="h6 brand-font mb-2">{{ __('ops.by_payment_method') }}</h2>
            @forelse($byMethod as $row)
                @php $share = $total > 0 ? round($row->total / $total * 100) : 0; @endphp
                <div class="d-flex justify-content-between small mb-1">
                    <span>{{ tz_payment_methods()[$row->payment_method] ?? $row->payment_method }} <span class="text-muted">({{ $row->count }})</span></span>
                    <span class="fw-semibold">{{ format_money_short($row->total) }} · {{ $share }}%</span>
                </div>
                <div class="progress progress-thin rounded-pill mb-2" role="progressbar" aria-valuenow="{{ $share }}" aria-valuemin="0" aria-valuemax="100" aria-label="{{ $row->payment_method }}">
                    <div class="progress-bar rounded-pill" style="width: {{ $share }}%; background: #2a78d6;"></div>
                </div>
            @empty
                <p class="text-muted small mb-0">{{ __('ops.no_payments') }}</p>
            @endforelse
        </div>
    </div>
</div>

<form method="GET" action="{{ route('finance.payments') }}" class="card p-2 p-md-3 mb-3">
    <div class="filter-bar">
        <div class="flex-grow-1">
            <div class="input-group input-group-sm">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="search" name="q" class="form-control" value="{{ request('q') }}" placeholder="{{ __('ops.payment_search_placeholder') }}" aria-label="{{ __('app.search') }}">
            </div>
        </div>
        <div>
            <select name="method" class="form-select form-select-sm" onchange="this.form.submit()" aria-label="{{ __('ops.payment_method') }}">
                <option value="">{{ __('ops.all_methods') }}</option>
                @foreach(tz_payment_methods() as $value => $label)
                    <option value="{{ $value }}" @selected(request('method') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()" aria-label="{{ __('app.status') }}">
                <option value="">{{ __('ops.all_statuses') }}</option>
                <option value="Completed" @selected(request('status') === 'Completed')>{{ __('ops.payment_status_completed') }}</option>
                <option value="Voided" @selected(request('status') === 'Voided')>{{ __('ops.payment_status_voided') }}</option>
            </select>
        </div>
        <div><input type="date" name="from" class="form-control form-control-sm" value="{{ request('from') }}" title="{{ __('ops.date_from') }}" aria-label="{{ __('ops.date_from') }}"></div>
        <div><input type="date" name="to" class="form-control form-control-sm" value="{{ request('to') }}" title="{{ __('ops.date_to') }}" aria-label="{{ __('ops.date_to') }}"></div>
        <div class="filter-actions">
            <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-search"></i></button>
            <a href="{{ route('finance.payments') }}" class="btn btn-sm btn-light border">{{ __('ops.reset') }}</a>
        </div>
    </div>
</form>

<div class="card">
    @if($canDeletePayments && $payments->count() > 0)
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 py-2">
            <span class="small text-muted">{{ __('ops.bulk_select_hint') }}</span>
            <button type="button" class="btn btn-sm btn-light border" data-bulk-all="payments" aria-pressed="false" data-label-all="{{ __('admin.select_all') }}" data-label-none="{{ __('admin.deselect_all') }}">
                <i class="bi bi-check2-square me-1"></i><span data-bulk-all-label>{{ __('admin.select_all') }} ({{ $payments->count() }})</span>
            </button>
        </div>
    @endif
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 table-stack small">
            <thead class="table-light">
                <tr>
                    @if($canDeletePayments)<th class="stack-check" style="width: 2.5rem;"><input type="checkbox" class="form-check-input" data-bulk-all="payments" aria-label="{{ __('admin.select_all') }}"></th>@endif
                    <th>{{ __('ops.payment_no') }}</th>
                    <th>{{ __('ops.customer') }}</th>
                    <th>{{ __('ops.invoice') }}</th>
                    <th>{{ __('ops.payment_date') }}</th>
                    <th>{{ __('ops.payment_method') }}</th>
                    <th class="text-end">{{ __('ops.amount') }}</th>
                    <th class="text-end">{{ __('app.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $pay)
                    <tr class="{{ $pay->status !== 'Completed' ? 'text-muted' : '' }}">
                        @if($canDeletePayments)<td class="stack-check"><input type="checkbox" class="form-check-input" value="{{ $pay->id }}" data-bulk-item="payments" aria-label="{{ $pay->payment_number }}"></td>@endif
                        <td class="stack-primary">
                            <strong>{{ $pay->payment_number }}</strong>
                            @if($pay->status !== 'Completed')<span class="badge bg-secondary ms-1">{{ $pay->status }}</span>@endif
                            @if($pay->reference_number)<div class="text-muted">{{ __('ops.ref') }}: {{ $pay->reference_number }}</div>@endif
                        </td>
                        <td>{{ $pay->customer?->full_name }}</td>
                        <td>@if($pay->invoice)<a href="{{ route('finance.invoices.show', $pay->invoice) }}">{{ $pay->invoice->invoice_number }}</a>@else — @endif</td>
                        <td>{{ $pay->payment_date?->format('d M Y') }}<div class="text-muted">{{ $pay->recorder?->name }}</div></td>
                        <td>{{ tz_payment_methods()[$pay->payment_method] ?? $pay->payment_method }}</td>
                        <td class="text-end fw-bold {{ $pay->status === 'Completed' ? 'text-success' : 'text-decoration-line-through' }}">{{ format_currency($pay->amount, $pay->currency, 0) }}</td>
                        <td class="stack-actions text-end">
                            <div class="d-inline-flex gap-1">
                                <a href="{{ route('finance.payments.receipt', $pay) }}" class="btn btn-light border btn-sm" title="{{ __('ops.receipt') }}"><i class="bi bi-file-earmark-text"></i></a>
                                @if($pay->status === 'Completed')
                                    @can('void_invoices')
                                        <form action="{{ route('finance.payments.void', $pay) }}" method="POST" class="m-0" onsubmit="this.reason.value = prompt(@json(__('ops.void_reason_prompt'))) || ''; return confirm(@json(__('ops.payment_void_confirm')));">
                                            @csrf
                                            <input type="hidden" name="reason">
                                            <button type="submit" class="btn btn-light border btn-sm" title="{{ __('ops.void_payment') }}"><i class="bi bi-x-octagon text-danger"></i></button>
                                        </form>
                                    @endcan
                                @endif
                                @if($canDeletePayments)
                                    <form action="{{ route('finance.payments.destroy', $pay) }}" method="POST" class="m-0" onsubmit="return confirm({{ json_encode(__('ops.payment_delete_confirm', ['number' => $pay->payment_number])) }});">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-light border btn-sm" title="{{ __('app.delete') }}" aria-label="{{ __('app.delete') }}"><i class="bi bi-trash text-danger"></i></button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="{{ $canDeletePayments ? 8 : 7 }}" class="stack-no-label"><div class="empty-state"><div class="empty-icon"><i class="bi bi-cash-stack"></i></div><h6>{{ __('ops.no_payments') }}</h6></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($payments->hasPages())
        <div class="p-3 border-top d-flex justify-content-center justify-content-md-end">{{ $payments->links() }}</div>
    @endif
</div>

@if($canDeletePayments)
<form action="{{ route('finance.payments.bulk') }}" method="POST" data-bulk-form="payments">
    @csrf
    <div class="bulk-bar flex-column d-none" data-bulk-bar="payments">
        <div class="bulk-inner">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary rounded-pill fs-6" data-bulk-count="payments">0</span>
                <span class="text-white small fw-semibold">{{ __('ops.payments_selected') }}</span>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <button type="button" class="btn btn-sm btn-outline-light rounded-pill px-3" data-bulk-all="payments" data-label-all="{{ __('admin.select_all') }}" data-label-none="{{ __('admin.deselect_all') }}">
                    <i class="bi bi-check2-all me-1"></i><span data-bulk-all-label>{{ __('admin.select_all') }}</span>
                </button>
                <button type="submit" name="action" value="delete" class="btn btn-sm btn-danger rounded-pill px-3" data-confirm="{{ __('ops.payments_bulk_delete_confirm') }}"><i class="bi bi-trash me-1"></i>{{ __('app.delete') }}</button>
                <button type="button" class="btn btn-sm btn-link text-white-50 text-decoration-none touch-target p-0" data-bulk-clear="payments" title="{{ __('admin.deselect_all') }}" aria-label="{{ __('admin.deselect_all') }}"><i class="bi bi-x-lg"></i></button>
            </div>
        </div>
    </div>
</form>
@endif
@endsection
