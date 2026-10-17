@extends('layouts.app')

@section('title', __('ops.deal').' '.$deal->deal_number)

@section('content')
@php
    $dealBadge = ['Draft' => 'inactive', 'Pending Approval' => 'pending', 'Active' => 'active', 'Completed' => 'paid', 'Defaulted' => 'overdue', 'Terminated' => 'cancelled'];
    $installmentBadge = ['Pending' => 'pending', 'Paid' => 'paid', 'Partial' => 'partial', 'Partially Paid' => 'partial', 'Overdue' => 'overdue'];
    $totalPaid = $deal->installments->sum('paid_amount');
    $balance = max(0, (float) $deal->sale_price - (float) $totalPaid);
    $progress = $deal->sale_price > 0 ? min(100, ($totalPaid / $deal->sale_price) * 100) : 0;
    $buyerDigits = preg_replace('/\D+/', '', (string) $deal->customer?->phone);
    if (str_starts_with($buyerDigits, '0')) {
        $buyerDigits = '255'.substr($buyerDigits, 1);
    }
@endphp

<div class="page-header">
    <div class="min-w-0">
        <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
            <h1 class="brand-font mb-0">{{ __('ops.deal') }}: {{ $deal->deal_number }}</h1>
            <span class="badge badge-status-{{ $dealBadge[$deal->status] ?? strtolower(str_replace(' ', '', $deal->status)) }}">{{ $deal->status }}</span>
        </div>
        <p class="text-muted small mb-0">{{ __('ops.agreement_date') }}: {{ $deal->agreement_date->format('d M Y') }} &bull; {{ __('ops.plan_'.strtolower($deal->payment_plan_type)) }}</p>
    </div>
    <div class="page-actions">
        @if($buyerDigits)
            <a href="https://wa.me/{{ $buyerDigits }}" target="_blank" rel="noopener" class="btn btn-whatsapp"><i class="bi bi-whatsapp me-1"></i> {{ __('ops.whatsapp_buyer') }}</a>
        @endif
        <a href="{{ route('deals.index') }}" class="btn btn-light border"><i class="bi bi-arrow-left me-1"></i> {{ __('ops.back_to_deals') }}</a>
    </div>
</div>

<!-- KPI strip -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="kpi-card kpi-primary">
            <div class="kpi-label">{{ __('ops.contract_price') }}</div>
            <div class="kpi-value">{{ format_money_short($deal->sale_price) }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card kpi-success">
            <div class="kpi-label">{{ __('ops.total_paid') }}</div>
            <div class="kpi-value text-success">{{ format_money_short($totalPaid) }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card kpi-danger">
            <div class="kpi-label">{{ __('ops.balance_due') }}</div>
            <div class="kpi-value text-danger">{{ format_money_short($balance) }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card kpi-warning">
            <div class="kpi-label">{{ __('ops.commission') }}</div>
            <div class="kpi-value">{{ format_money_short($deal->commission_amount) }}</div>
            <small class="text-muted">{{ $deal->commission_rate }}%</small>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <!-- Installment Milestone Schedule -->
        <div class="card mb-4">
            <div class="card-header brand-font d-flex justify-content-between align-items-center gap-2">
                <span class="text-truncate">{{ __('ops.installment_schedule') }}</span>
                <span class="badge bg-primary-subtle text-primary text-nowrap">{{ __('ops.milestones_count', ['count' => $deal->installments->count()]) }}</span>
            </div>
            <div class="px-3 pt-3">
                <div class="d-flex justify-content-between small text-muted mb-1">
                    <span>{{ __('ops.collection_progress') }}</span>
                    <span>{{ number_format($progress, 0) }}%</span>
                </div>
                <div class="progress progress-thin mb-3" role="progressbar" aria-valuenow="{{ (int) $progress }}" aria-valuemin="0" aria-valuemax="100">
                    <div class="progress-bar bg-success" style="width: {{ $progress }}%"></div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 table-stack small">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>{{ __('ops.milestone') }}</th>
                            <th>{{ __('ops.due_date') }}</th>
                            <th>{{ __('ops.amount_due') }}</th>
                            <th>{{ __('ops.paid_amount') }}</th>
                            <th>{{ __('app.status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($deal->installments as $ins)
                            <tr>
                                <td class="stack-primary">{{ __('ops.installment') }} {{ $ins->installment_number }} &bull; {{ $ins->title }}</td>
                                <td class="d-none d-md-table-cell">{{ $ins->title }}</td>
                                <td>{{ $ins->due_date->format('d M Y') }}</td>
                                <td class="fw-bold">{{ format_currency($ins->amount) }}</td>
                                <td class="text-success fw-bold">{{ format_currency($ins->paid_amount) }}</td>
                                <td><span class="badge badge-status-{{ $installmentBadge[$ins->status] ?? strtolower(str_replace(' ', '', $ins->status)) }}">{{ $ins->status }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="stack-no-label">
                                    <div class="empty-state py-4">
                                        <div class="empty-icon"><i class="bi bi-cash-stack"></i></div>
                                        <h6>{{ __('ops.no_installments') }}</h6>
                                        <p class="small mb-0">{{ __('ops.no_installments_hint') }}</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Invoices for this deal -->
        <div class="card">
            <div class="card-header brand-font">{{ __('ops.invoices_issued') }}</div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 table-stack small">
                    <thead class="table-light">
                        <tr>
                            <th>{{ __('ops.invoice_no') }}</th>
                            <th>{{ __('ops.issue_date') }}</th>
                            <th>{{ __('app.total') }}</th>
                            <th>{{ __('ops.balance') }}</th>
                            <th>{{ __('app.status') }}</th>
                            <th class="text-end">{{ __('app.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($deal->invoices as $inv)
                            <tr>
                                <td class="stack-primary"><a href="{{ route('finance.invoices.show', $inv) }}" class="fw-bold text-primary text-decoration-none">{{ $inv->invoice_number }}</a></td>
                                <td>{{ $inv->issue_date->format('d M Y') }}</td>
                                <td class="fw-bold">{{ format_currency($inv->total_amount) }}</td>
                                <td class="text-danger fw-bold">{{ format_currency($inv->balance_due) }}</td>
                                <td><span class="badge badge-status-{{ strtolower(str_replace(' ', '', $inv->status)) }}">{{ $inv->status }}</span></td>
                                <td class="stack-actions text-end">
                                    <a href="{{ route('finance.invoices.show', $inv) }}" class="btn btn-sm btn-light border"><i class="bi bi-receipt me-1"></i>{{ __('ops.view_invoice') }}</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="stack-no-label">
                                    <div class="empty-state py-4">
                                        <div class="empty-icon"><i class="bi bi-receipt"></i></div>
                                        <h6>{{ __('ops.no_deal_invoices') }}</h6>
                                        <a href="{{ route('finance.invoices') }}" class="btn btn-outline-primary btn-sm mt-2">{{ __('ops.go_to_invoices') }}</a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Property and Customer Info -->
        <div class="card mb-4">
            <div class="card-header brand-font">{{ __('ops.deal_details') }}</div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="text-muted small">{{ __('ops.property') }}</div>
                    <div class="fw-bold">{{ $deal->property?->title }}</div>
                    <small class="text-muted">{{ $deal->property?->city }} &bull; {{ $deal->property?->property_code }}</small>
                </div>
                <div class="mb-3">
                    <div class="text-muted small">{{ __('ops.buyer') }}</div>
                    <div class="fw-bold">{{ $deal->customer?->full_name }}</div>
                    @if($deal->customer?->phone)
                        <a href="tel:{{ $deal->customer->phone }}" class="small text-decoration-none">{{ format_phone_tz($deal->customer->phone) }}</a>
                    @endif
                </div>
                <div class="mb-3">
                    <div class="text-muted small">{{ __('ops.payment_plan') }}</div>
                    <div class="fw-bold">{{ __('ops.plan_'.strtolower($deal->payment_plan_type)) }} ({{ $deal->total_installments }}x)</div>
                </div>
                <div>
                    <div class="text-muted small">{{ __('ops.closing_agent') }}</div>
                    <div class="fw-bold">{{ $deal->agent?->user?->name ?? __('ops.direct_sale') }}</div>
                    <span class="badge bg-light text-dark border">{{ __('ops.commission') }}: {{ format_currency($deal->commission_amount) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
