@extends('layouts.app')

@section('title', __('app.customers'))

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h3 class="brand-font mb-1">{{ __('app.customers') }} Directory</h3>
        <p class="text-muted small mb-0">Total {{ $customers->total() }} verified clients and corporate buyers</p>
    </div>
    <button class="btn btn-primary btn-sm d-flex align-items-center gap-1 shadow-sm" data-bs-toggle="modal" data-bs-target="#newCustomerModal">
        <i class="bi bi-person-plus-fill"></i> Add New Customer
    </button>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: 0.875rem;">
            <thead class="table-light">
                <tr>
                    <th>Customer Name</th>
                    <th>Type</th>
                    <th>Phone / Email</th>
                    <th>City / Address</th>
                    <th>KYC Status</th>
                    <th>Deals / Invoices</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $c)
                    <tr>
                        <td>
                            <div class="fw-bold text-dark">{{ $c->full_name }}</div>
                            @if($c->company_name)
                                <small class="text-muted">{{ $c->company_name }}</small>
                            @endif
                        </td>
                        <td><span class="badge bg-light text-dark border">{{ $c->customer_type }}</span></td>
                        <td>
                            <div><i class="bi bi-telephone text-primary me-1"></i>{{ $c->phone }}</div>
                            @if($c->email)<small class="text-muted">{{ $c->email }}</small>@endif
                        </td>
                        <td>{{ $c->city ?? '-' }}, {{ $c->address ?? '' }}</td>
                        <td><span class="badge bg-success-subtle text-success"><i class="bi bi-shield-check me-1"></i>{{ $c->kyc_status }}</span></td>
                        <td>
                            <span class="badge bg-primary-subtle text-primary me-1">{{ $c->sales_deals_count }} Deals</span>
                            <span class="badge bg-secondary-subtle text-secondary">{{ $c->invoices_count }} Invoices</span>
                        </td>
                        <td class="text-end">
                            <button class="btn btn-light border btn-sm"><i class="bi bi-eye"></i></button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No customers registered yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3 border-top d-flex justify-content-end">
        {{ $customers->links() }}
    </div>
</div>

<!-- New Customer Modal -->
<div class="modal fade" id="newCustomerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title brand-font">Register Customer Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('crm.customers.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">First Name *</label>
                            <input type="text" name="first_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Last Name *</label>
                            <input type="text" name="last_name" class="form-control" required>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Customer Type</label>
                            <select name="customer_type" class="form-select">
                                <option value="Individual">Individual</option>
                                <option value="Corporate">Corporate / Company</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Company Name</label>
                            <input type="text" name="company_name" class="form-control">
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Phone Number *</label>
                            <input type="text" name="phone" class="form-control" placeholder="+255..." required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Email Address</label>
                            <input type="email" name="email" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">City</label>
                        <input type="text" name="city" class="form-control" placeholder="Dar es Salaam, Arusha...">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Customer</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
