@extends('layouts.app')

@section('title', __('app.expenses'))

@section('content')
@php
    $expenseBadge = ['Pending' => 'pending', 'Approved' => 'active', 'Paid' => 'paid', 'Rejected' => 'rejected', 'Cancelled' => 'cancelled'];
    $pageTotal = $expenses->sum('amount');
@endphp

<div class="page-header">
    <div class="min-w-0">
        <h1 class="brand-font mb-1">{{ __('ops.expenses_title') }}</h1>
        <p class="text-muted small mb-0">{{ __('ops.expenses_subtitle') }}</p>
    </div>
    <div class="page-actions">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newExpenseModal">
            <i class="bi bi-plus-lg me-1"></i> {{ __('ops.record_expense') }}
        </button>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl-3">
        <div class="kpi-card kpi-danger">
            <div class="kpi-label">{{ __('ops.expenses_on_page') }}</div>
            <div class="kpi-value text-danger">{{ format_money_short($pageTotal) }}</div>
            <span class="kpi-icon"><i class="bi bi-wallet2"></i></span>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-label">{{ __('ops.records') }}</div>
            <div class="kpi-value">{{ number_format($expenses->total()) }}</div>
            <span class="kpi-icon"><i class="bi bi-list-check"></i></span>
        </div>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 table-stack small">
            <thead class="table-light">
                <tr>
                    <th>{{ __('ops.expense_no') }}</th>
                    <th>{{ __('ops.date') }}</th>
                    <th>{{ __('ops.category') }}</th>
                    <th>{{ __('ops.title_description') }}</th>
                    <th>{{ __('ops.payee') }}</th>
                    <th>{{ __('ops.amount') }}</th>
                    <th>{{ __('ops.payment_method') }}</th>
                    <th>{{ __('app.status') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($expenses as $exp)
                    <tr>
                        <td class="stack-primary"><span class="fw-bold text-primary">{{ $exp->expense_number }}</span></td>
                        <td>{{ $exp->expense_date->format('d M Y') }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $exp->category }}</span></td>
                        <td>
                            <div class="fw-semibold">{{ $exp->title }}</div>
                            @if($exp->property)<small class="text-muted">{{ $exp->property->title }}</small>@endif
                        </td>
                        <td>{{ $exp->payee ?: '-' }}</td>
                        <td class="fw-bold text-danger">{{ format_currency($exp->amount, $exp->currency) }}</td>
                        <td>{{ tz_payment_methods()[$exp->payment_method] ?? $exp->payment_method }}</td>
                        <td><span class="badge badge-status-{{ $expenseBadge[$exp->status] ?? strtolower(str_replace(' ', '', (string) $exp->status)) }}">{{ $exp->status }}</span></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="stack-no-label">
                            <div class="empty-state">
                                <div class="empty-icon"><i class="bi bi-wallet2"></i></div>
                                <h6>{{ __('ops.no_expenses') }}</h6>
                                <p class="small mb-3">{{ __('ops.no_expenses_hint') }}</p>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newExpenseModal"><i class="bi bi-plus-lg me-1"></i> {{ __('ops.record_expense') }}</button>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($expenses->hasPages())
        <div class="p-3 border-top d-flex justify-content-center justify-content-md-end">
            {{ $expenses->links() }}
        </div>
    @endif
</div>

<!-- Modal -->
<div class="modal fade" id="newExpenseModal" tabindex="-1" aria-labelledby="newExpenseModalLabel">
    <div class="modal-dialog modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title brand-font" id="newExpenseModalLabel">{{ __('ops.record_expense_title') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('app.cancel') }}"></button>
            </div>
            <form action="{{ route('finance.expenses.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row g-2 mb-3">
                        <div class="col-12 col-sm-6">
                            <label for="expCategory" class="form-label small fw-semibold">{{ __('ops.category') }} *</label>
                            <select id="expCategory" name="category" class="form-select" required>
                                <option value="Marketing">{{ __('ops.cat_marketing') }}</option>
                                <option value="Maintenance">{{ __('ops.cat_maintenance') }}</option>
                                <option value="Legal">{{ __('ops.cat_legal') }}</option>
                                <option value="Survey">{{ __('ops.cat_survey') }}</option>
                                <option value="Land Rent">{{ __('ops.cat_land_rent') }}</option>
                                <option value="Commission">{{ __('ops.cat_commission') }}</option>
                                <option value="Transport">{{ __('ops.cat_transport') }}</option>
                                <option value="Administrative">{{ __('ops.cat_administrative') }}</option>
                                <option value="Utilities">{{ __('ops.cat_utilities') }}</option>
                            </select>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label for="expAmount" class="form-label small fw-semibold">{{ __('ops.amount') }} *</label>
                            <div class="input-group">
                                <span class="input-group-text">TSh</span>
                                <input type="number" step="0.01" min="1" inputmode="decimal" id="expAmount" name="amount" class="form-control fw-bold" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="expTitle" class="form-label small fw-semibold">{{ __('ops.title_description') }} *</label>
                        <input type="text" id="expTitle" name="title" class="form-control" placeholder="{{ __('ops.expense_title_placeholder') }}" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-12 col-sm-6">
                            <label for="expDate" class="form-label small fw-semibold">{{ __('ops.expense_date') }} *</label>
                            <input type="date" id="expDate" name="expense_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label for="expPayee" class="form-label small fw-semibold">{{ __('ops.payee') }}</label>
                            <input type="text" id="expPayee" name="payee" class="form-control" placeholder="{{ __('ops.payee_placeholder') }}">
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-12 col-sm-6">
                            <label for="expMethod" class="form-label small fw-semibold">{{ __('ops.payment_method') }}</label>
                            <select id="expMethod" name="payment_method" class="form-select">
                                @foreach(tz_payment_methods() as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                                <option value="Mobile Money">{{ __('ops.payment_mobile_money_other') }}</option>
                            </select>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label for="expProperty" class="form-label small fw-semibold">{{ __('ops.linked_property') }}</label>
                            <select id="expProperty" name="property_id" class="form-select">
                                <option value="">{{ __('ops.none_corporate') }}</option>
                                @foreach($properties as $pr)
                                    <option value="{{ $pr->id }}">{{ $pr->title }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">{{ __('app.cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('ops.save_expense') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
