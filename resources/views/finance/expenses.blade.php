@extends('layouts.app')

@section('title', __('app.expenses'))

@section('content')
@php
    $expenseBadge = ['Pending' => 'pending', 'Approved' => 'active', 'Paid' => 'paid', 'Rejected' => 'rejected', 'Cancelled' => 'cancelled'];
    $canManage = auth()->user()->can('manage_expenses');
@endphp

<div class="page-header">
    <div class="min-w-0">
        <h1 class="brand-font mb-1">{{ __('ops.expenses_title') }}</h1>
        <p class="text-muted small mb-0">{{ __('ops.expenses_subtitle') }}</p>
    </div>
    <div class="page-actions">
        @can('manage_settings')<a href="{{ route('settings.index') }}#taxonomies" class="btn btn-light border"><i class="bi bi-tags me-1"></i>{{ __('ops.manage_categories') }}</a>@endcan
        @if($canManage)
            <button class="btn btn-primary" id="newExpenseBtn" type="button">
                <i class="bi bi-plus-lg me-1"></i> {{ __('ops.record_expense') }}
            </button>
        @endif
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-6 col-lg-3">
        <div class="kpi-card kpi-danger h-100">
            <div class="kpi-label">{{ __('ops.expenses_filtered_total') }}</div>
            <div class="kpi-value text-danger" title="{{ format_currency($total, null, 0) }}">{{ format_money_short($total) }}</div>
            <span class="kpi-icon"><i class="bi bi-wallet2"></i></span>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="kpi-card h-100">
            <div class="kpi-label">{{ __('ops.expenses_this_month') }}</div>
            <div class="kpi-value" title="{{ format_currency($monthTotal, null, 0) }}">{{ format_money_short($monthTotal) }}</div>
        </div>
    </div>
    <div class="col-12 col-lg-6">
        <div class="card p-3 h-100">
            <h2 class="h6 brand-font mb-2">{{ __('ops.by_category') }}</h2>
            @forelse($byCategory->take(5) as $row)
                @php $share = $total > 0 ? round($row->total / $total * 100) : 0; @endphp
                <div class="d-flex justify-content-between small mb-1">
                    <a href="{{ route('finance.expenses', array_merge(request()->except('page'), ['category' => $row->category])) }}" class="text-dark">{{ $row->category ?: '—' }}</a>
                    <span class="fw-semibold">{{ format_money_short($row->total) }} · {{ $share }}%</span>
                </div>
                <div class="progress progress-thin rounded-pill mb-2" role="progressbar" aria-valuenow="{{ $share }}" aria-valuemin="0" aria-valuemax="100" aria-label="{{ $row->category }}">
                    <div class="progress-bar rounded-pill" style="width: {{ $share }}%; background: #eb6834;"></div>
                </div>
            @empty
                <p class="text-muted small mb-0">{{ __('ops.no_expenses') }}</p>
            @endforelse
        </div>
    </div>
</div>

<form method="GET" action="{{ route('finance.expenses') }}" class="card p-2 p-md-3 mb-3">
    <div class="filter-bar">
        <div class="flex-grow-1">
            <div class="input-group input-group-sm">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="search" name="q" class="form-control" value="{{ request('q') }}" placeholder="{{ __('ops.expense_search_placeholder') }}" aria-label="{{ __('app.search') }}">
            </div>
        </div>
        <div>
            <select name="category" class="form-select form-select-sm" onchange="this.form.submit()" aria-label="{{ __('ops.category') }}">
                <option value="">{{ __('admin.all_categories') }}</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat }}" @selected(request('category') === $cat)>{{ $cat }}</option>
                @endforeach
            </select>
        </div>
        <div><input type="date" name="from" class="form-control form-control-sm" value="{{ request('from') }}" title="{{ __('ops.date_from') }}" aria-label="{{ __('ops.date_from') }}"></div>
        <div><input type="date" name="to" class="form-control form-control-sm" value="{{ request('to') }}" title="{{ __('ops.date_to') }}" aria-label="{{ __('ops.date_to') }}"></div>
        <div class="filter-actions">
            <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-search"></i></button>
            <a href="{{ route('finance.expenses') }}" class="btn btn-sm btn-light border">{{ __('ops.reset') }}</a>
        </div>
    </div>
</form>

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
                    <th class="text-end">{{ __('ops.amount') }}</th>
                    <th>{{ __('ops.payment_method') }}</th>
                    @if($canManage)<th class="text-end">{{ __('app.actions') }}</th>@endif
                </tr>
            </thead>
            <tbody>
                @forelse($expenses as $exp)
                    <tr>
                        <td class="stack-primary"><span class="fw-bold text-primary">{{ $exp->expense_number }}</span>
                            <span class="badge badge-status-{{ $expenseBadge[$exp->status] ?? strtolower(str_replace(' ', '', (string) $exp->status)) }} ms-1">{{ $exp->status }}</span>
                        </td>
                        <td>{{ $exp->expense_date?->format('d M Y') }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $exp->category }}</span></td>
                        <td>
                            <div class="fw-semibold">{{ $exp->title }}</div>
                            @if($exp->property)<small class="text-muted">{{ $exp->property->title }}</small>@endif
                        </td>
                        <td>{{ $exp->payee ?: '-' }}</td>
                        <td class="fw-bold text-danger text-end">{{ format_currency($exp->amount, $exp->currency, 0) }}</td>
                        <td>{{ tz_payment_methods()[$exp->payment_method] ?? $exp->payment_method }}</td>
                        @if($canManage)
                            <td class="stack-actions text-end">
                                <div class="d-inline-flex gap-1">
                                    <button type="button" class="btn btn-light border btn-sm exp-edit-btn" title="{{ __('app.edit') }}" aria-label="{{ __('app.edit') }}"
                                        data-url="{{ route('finance.expenses.update', $exp) }}"
                                        data-expense="{{ json_encode(['category' => $exp->category, 'title' => $exp->title, 'amount' => (float) $exp->amount, 'expense_date' => $exp->expense_date?->format('Y-m-d'), 'payee' => $exp->payee, 'payment_method' => $exp->payment_method, 'property_id' => $exp->property_id]) }}">
                                        <i class="bi bi-pencil text-primary"></i>
                                    </button>
                                    <form action="{{ route('finance.expenses.destroy', $exp) }}" method="POST" class="m-0" onsubmit="return confirm(@json(__('ops.expense_delete_confirm')));">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-light border btn-sm" title="{{ __('app.delete') }}" aria-label="{{ __('app.delete') }}"><i class="bi bi-trash text-danger"></i></button>
                                    </form>
                                </div>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="stack-no-label">
                            <div class="empty-state">
                                <div class="empty-icon"><i class="bi bi-wallet2"></i></div>
                                <h6>{{ __('ops.no_expenses') }}</h6>
                                <p class="small mb-0">{{ __('ops.no_expenses_hint') }}</p>
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

@if($canManage)
<!-- Add / edit expense (shared) -->
<div class="modal fade" id="expenseModal" tabindex="-1" aria-labelledby="expenseModalLabel">
    <div class="modal-dialog modal-dialog-scrollable modal-fullscreen-sm-down">
        <form class="modal-content" method="POST" id="expenseForm" action="{{ route('finance.expenses.store') }}" data-store-url="{{ route('finance.expenses.store') }}">
            @csrf
            <input type="hidden" name="_method" value="POST" id="expenseFormMethod">
            <div class="modal-header">
                <h5 class="modal-title brand-font" id="expenseModalLabel">{{ __('ops.record_expense_title') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('app.cancel') }}"></button>
            </div>
            <div class="modal-body">
                <div class="row g-2 mb-3">
                    <div class="col-12 col-sm-6">
                        <label for="expCategory" class="form-label small fw-semibold">{{ __('ops.category') }} *</label>
                        <select id="expCategory" name="category" class="form-select" required>
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}">{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-sm-6">
                        <label for="expDate" class="form-label small fw-semibold">{{ __('ops.date') }} *</label>
                        <input type="date" id="expDate" name="expense_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="expTitle" class="form-label small fw-semibold">{{ __('ops.title_description') }} *</label>
                    <input type="text" id="expTitle" name="title" class="form-control" required maxlength="255">
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-12 col-sm-6">
                        <label for="expAmount" class="form-label small fw-semibold">{{ __('ops.amount') }} *</label>
                        <div class="input-group">
                            <span class="input-group-text">TSh</span>
                            <input type="number" id="expAmount" name="amount" class="form-control fw-bold" min="1" step="0.01" inputmode="decimal" required>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6">
                        <label for="expPayee" class="form-label small fw-semibold">{{ __('ops.payee') }}</label>
                        <input type="text" id="expPayee" name="payee" class="form-control" maxlength="255">
                    </div>
                </div>
                <div class="row g-2">
                    <div class="col-12 col-sm-6">
                        <label for="expMethod" class="form-label small fw-semibold">{{ __('ops.payment_method') }}</label>
                        <select id="expMethod" name="payment_method" class="form-select">
                            @foreach(tz_payment_methods() as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
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
                <button type="submit" class="btn btn-primary">{{ __('app.save') }}</button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('expenseForm');
    if (!form) { return; }
    const labels = {!! json_encode(['add' => __('ops.record_expense_title'), 'edit' => __('ops.edit_expense')]) !!};
    const open = function (url, data) {
        form.reset();
        form.action = url || form.dataset.storeUrl;
        document.getElementById('expenseFormMethod').value = url ? 'PUT' : 'POST';
        document.getElementById('expenseModalLabel').textContent = url ? labels.edit : labels.add;
        if (data) {
            Object.entries(data).forEach(([k, v]) => { if (form.elements[k]) { form.elements[k].value = v ?? ''; } });
            if (data.category && ![...form.category.options].some(o => o.value === data.category)) {
                form.category.add(new Option(data.category, data.category, true, true));
            }
        }
        bootstrap.Modal.getOrCreateInstance(document.getElementById('expenseModal')).show();
    };
    document.getElementById('newExpenseBtn')?.addEventListener('click', () => open(null, null));
    document.querySelectorAll('.exp-edit-btn').forEach(btn => btn.addEventListener('click', () => open(btn.dataset.url, JSON.parse(btn.dataset.expense))));
});
</script>
@endsection
