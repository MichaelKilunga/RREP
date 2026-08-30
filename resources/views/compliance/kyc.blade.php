@extends('layouts.app')

@section('title', 'KYC & Compliance')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="brand-font mb-1"><i class="bi bi-shield-check text-success me-2"></i>KYC Verification & Regulatory Compliance (BM-019)</h3>
        <p class="text-muted small mb-0">Verify prospective buyer identity documents, national IDs, and landlord bank accounts</p>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header brand-font d-flex justify-content-between align-items-center">
        <span>Customer / Buyer KYC Queue</span>
        <span class="badge bg-warning text-dark">{{ $pendingCustomers->count() }} Pending</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
            <thead class="table-light">
                <tr>
                    <th>Customer</th>
                    <th>Type</th>
                    <th>National ID / Passport</th>
                    <th>TIN Number</th>
                    <th>Phone</th>
                    <th>KYC Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pendingCustomers as $cust)
                    <tr>
                        <td class="fw-bold">{{ $cust->full_name }}</td>
                        <td>{{ $cust->customer_type }}</td>
                        <td><code>{{ $cust->national_id_passport ?? 'Pending' }}</code></td>
                        <td>{{ $cust->tax_number ?? '-' }}</td>
                        <td>{{ $cust->phone }}</td>
                        <td><span class="badge bg-warning-subtle text-warning-emphasis">{{ $cust->kyc_status }}</span></td>
                        <td class="text-end">
                            <form action="{{ route('compliance.verify_customer', $cust) }}" method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" name="status" value="Verified">
                                <button type="submit" class="btn btn-sm btn-success"><i class="bi bi-check-circle me-1"></i> Verify</button>
                            </form>
                            <form action="{{ route('compliance.verify_customer', $cust) }}" method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" name="status" value="Rejected">
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-x-circle me-1"></i> Reject</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-3">All customer KYC records are up to date.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-header brand-font d-flex justify-content-between align-items-center">
        <span>Property Owner / Landlord KYC Queue</span>
        <span class="badge bg-warning text-dark">{{ $pendingOwners->count() }} Pending</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
            <thead class="table-light">
                <tr>
                    <th>Owner Name</th>
                    <th>Company</th>
                    <th>National ID</th>
                    <th>Bank Account</th>
                    <th>KYC Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pendingOwners as $own)
                    <tr>
                        <td class="fw-bold">{{ $own->full_name }}</td>
                        <td>{{ $own->company_name ?? 'Individual' }}</td>
                        <td><code>{{ $own->national_id ?? 'Pending' }}</code></td>
                        <td>{{ $own->bank_name }}: {{ $own->bank_account_number }}</td>
                        <td><span class="badge bg-warning-subtle text-warning-emphasis">{{ $own->kyc_status }}</span></td>
                        <td class="text-end">
                            <form action="{{ route('compliance.verify_owner', $own) }}" method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" name="status" value="Verified">
                                <button type="submit" class="btn btn-sm btn-success"><i class="bi bi-check-circle me-1"></i> Verify</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-3">All landlord KYC records are verified.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
