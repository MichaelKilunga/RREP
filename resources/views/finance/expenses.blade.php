@extends('layouts.app')

@section('title', __('app.expenses'))

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h3 class="brand-font mb-1">{{ __('app.expenses') }} & Operating Costs</h3>
        <p class="text-muted small mb-0">Record maintenance, marketing campaigns, commissions, and administrative costs</p>
    </div>
    <button class="btn btn-primary btn-sm d-flex align-items-center gap-1 shadow-sm" data-bs-toggle="modal" data-bs-target="#newExpenseModal">
        <i class="bi bi-plus-lg"></i> Record Expense
    </button>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: 0.875rem;">
            <thead class="table-light">
                <tr>
                    <th>Expense #</th>
                    <th>Date</th>
                    <th>Category</th>
                    <th>Title / Description</th>
                    <th>Payee</th>
                    <th>Amount</th>
                    <th>Method</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($expenses as $exp)
                    <tr>
                        <td class="fw-bold text-primary">{{ $exp->expense_number }}</td>
                        <td>{{ $exp->expense_date->format('d M Y') }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $exp->category }}</span></td>
                        <td>
                            <div class="fw-semibold">{{ $exp->title }}</div>
                            @if($exp->property)<small class="text-muted">{{ $exp->property->title }}</small>@endif
                        </td>
                        <td>{{ $exp->payee ?? '-' }}</td>
                        <td class="fw-bold text-danger">{{ format_currency($exp->amount, $exp->currency) }}</td>
                        <td>{{ $exp->payment_method }}</td>
                        <td><span class="badge bg-success-subtle text-success">{{ $exp->status }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">No expenses recorded.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3 border-top d-flex justify-content-end">
        {{ $expenses->links() }}
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="newExpenseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title brand-font">Record Operational Expense</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('finance.expenses.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Category *</label>
                            <select name="category" class="form-select" required>
                                <option value="Marketing">Marketing & Advertising</option>
                                <option value="Maintenance">Property Maintenance</option>
                                <option value="Legal">Legal & Cadastral Fees</option>
                                <option value="Commission">Broker Commission</option>
                                <option value="Administrative">Administrative & Office</option>
                                <option value="Utilities">Utilities & Security</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Amount ({{ current_currency() }}) *</label>
                            <input type="number" step="0.01" name="amount" class="form-control fw-bold" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Expense Title / Description *</label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Drone photography & brochure printing" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Expense Date *</label>
                            <input type="date" name="expense_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Payee</label>
                            <input type="text" name="payee" class="form-control" placeholder="Vendor / Service Provider">
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Payment Method</label>
                            <select name="payment_method" class="form-select">
                                <option value="Bank Transfer">Bank Transfer</option>
                                <option value="Mobile Money">Mobile Money</option>
                                <option value="Cash">Cash</option>
                                <option value="Cheque">Cheque</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Linked Property (Optional)</label>
                            <select name="property_id" class="form-select">
                                <option value="">None / Corporate</option>
                                @foreach($properties as $pr)
                                    <option value="{{ $pr->id }}">{{ $pr->title }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Expense</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
