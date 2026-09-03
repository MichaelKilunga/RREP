@extends('layouts.app')

@section('title', 'Client Portal & Account Hub')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="brand-font mb-1"><i class="bi bi-person-workspace text-primary me-2"></i>Client Self-Service Portal & Account Hub</h3>
        <p class="text-muted small mb-0">Welcome back, <strong>{{ $customer->full_name }}</strong> &bull; Track plot reservations, cadastral surveys, invoices, and loyalty rewards</p>
    </div>
    <div>
        <a href="{{ route('public.land') }}" class="btn btn-primary btn-sm fw-semibold">
            <i class="bi bi-compass me-1"></i> Browse Marketplace Plots
        </a>
    </div>
</div>

<!-- KPI & Loyalty Banner -->
<div class="row g-4 mb-4">
    <!-- Loyalty & Rewards Card -->
    <div class="col-lg-4 col-md-6">
        <div class="card h-100 border-0 shadow-sm bg-gradient text-dark" style="background: linear-gradient(135deg, var(--rrep-primary, #0f52ba) 0%, #1e293b 100%);">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-dark-50 small fw-semibold text-uppercase">Loyalty Tier & Balance</span>
                    <span class="badge bg-warning text-dark fw-bold">{{ $customer->loyalty_tier ?? 'Bronze Member' }}</span>
                </div>
                <div class="h2 fw-bold mb-1">{{ number_format($customer->loyalty_points ?? 0) }} <span class="fs-6 fw-normal">pts</span></div>
                <p class="small text-dark-50 mb-3">Earn points on reservations, purchases, and survey bookings.</p>
                <div class="d-flex gap-2">
                    <a href="#loyaltyTab" class="btn btn-light btn-sm text-primary fw-semibold" onclick="$('#clientTabs #loyalty-tab').tab('show');">
                        <i class="bi bi-ticket-perforated me-1"></i> View My Vouchers ({{ $loyaltyRewards->where('status', 'active')->count() }})
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Reservations Card -->
    <div class="col-lg-4 col-md-6">
        <div class="card p-4 h-100 shadow-sm bg-white border-0">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small text-uppercase fw-bold">Active Plot Holds</span>
                <i class="bi bi-geo-alt-fill text-warning fs-4"></i>
            </div>
            <h3 class="fw-bold mb-1 text-dark">{{ $reservations->where('status', 'Active')->count() }}</h3>
            <small class="text-muted">Guaranteed reservation holds with locked plot pricing</small>
        </div>
    </div>

    <!-- Invoices & Statements Card -->
    <div class="col-lg-4 col-md-6">
        <div class="card p-4 h-100 shadow-sm bg-white border-0">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small text-uppercase fw-bold">Billing Ledger</span>
                <i class="bi bi-receipt text-primary fs-4"></i>
            </div>
            <h3 class="fw-bold mb-1 text-dark">{{ $invoices->count() }}</h3>
            <small class="text-muted">Digital tax invoices, deposit receipts & payment history</small>
        </div>
    </div>
</div>

<!-- Navigation Tabs -->
<ul class="nav nav-tabs mb-4" id="clientTabs" role="tablist">
    <li class="nav-item">
        <button class="nav-link active fw-semibold" id="reservations-tab" data-bs-toggle="tab" data-bs-target="#reservationsTab" type="button">
            <i class="bi bi-house-check me-1"></i> Plot Reservations ({{ $reservations->count() }})
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link fw-semibold" id="surveys-tab" data-bs-toggle="tab" data-bs-target="#surveysTab" type="button">
            <i class="bi bi-compass me-1"></i> Survey Requests ({{ $surveyRequests->count() }})
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link fw-semibold" id="invoices-tab" data-bs-toggle="tab" data-bs-target="#invoicesTab" type="button">
            <i class="bi bi-credit-card me-1"></i> Invoices & Ledger ({{ $invoices->count() }})
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link fw-semibold" id="loyalty-tab" data-bs-toggle="tab" data-bs-target="#loyaltyTab" type="button">
            <i class="bi bi-gift me-1"></i> Loyalty Vouchers & Points
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link fw-semibold" id="vault-tab" data-bs-toggle="tab" data-bs-target="#vaultTab" type="button">
            <i class="bi bi-folder2-open me-1"></i> Document Vault & Blueprints
        </button>
    </li>
</ul>

<div class="tab-content" id="clientTabContent">
    <!-- TAB 1: Reservations -->
    <div class="tab-pane fade show active" id="reservationsTab" role="tabpanel">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="brand-font mb-0">My Land Plot Reservations & Holds</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Hold Ref #</th>
                            <th>Property / Plot</th>
                            <th>Hold Fee</th>
                            <th>Valid Until</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reservations as $res)
                            <tr>
                                <td class="fw-bold text-primary font-monospace">{{ $res->reservation_number }}</td>
                                <td>
                                    <div class="fw-bold">{{ $res->property?->title ?? 'Land Parcel' }}</div>
                                    <small class="text-muted">{{ $res->property?->address }}, {{ $res->property?->city }}</small>
                                </td>
                                <td class="fw-bold text-dark">{{ format_currency($res->reservation_fee) }}</td>
                                <td>
                                    <div>{{ $res->reserved_until }}</div>
                                    <small class="text-muted">From: {{ $res->reserved_from }}</small>
                                </td>
                                <td>
                                    @if($res->status === 'Active')
                                        <span class="badge bg-success">Active Hold</span>
                                    @elseif($res->status === 'Converted')
                                        <span class="badge bg-primary">Purchased</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $res->status }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center py-4 text-muted">No plot reservations placed yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB 2: Land Survey Requests -->
    <div class="tab-pane fade" id="surveysTab" role="tabpanel">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="brand-font mb-0">Cadastral Survey & Topographical Requests</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Project Code</th>
                            <th>Survey Type</th>
                            <th>Location</th>
                            <th>Estimated Fee</th>
                            <th>Milestone Progress</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($surveyRequests as $srv)
                            <tr>
                                <td class="fw-bold text-primary font-monospace">{{ $srv->project_code }}</td>
                                <td>{{ $srv->survey_type ?? $srv->project_name }}</td>
                                <td>{{ $srv->location_name }}</td>
                                <td>{{ format_currency($srv->estimated_cost) }}</td>
                                <td>
                                    <span class="badge bg-info-subtle text-info">{{ $srv->milestones->count() }} Milestones Logged</span>
                                </td>
                                <td>
                                    @if($srv->status === 'Completed')
                                        <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Completed</span>
                                    @elseif($srv->status === 'Fieldwork')
                                        <span class="badge bg-warning text-dark"><i class="bi bi-gear me-1"></i>Fieldwork in Progress</span>
                                    @else
                                        <span class="badge bg-primary">{{ $srv->status }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center py-4 text-muted">No survey requests submitted yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB 3: Invoices & Payment Ledger -->
    <div class="tab-pane fade" id="invoicesTab" role="tabpanel">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="brand-font mb-0">Digital Invoices & Accounting Trail</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Invoice #</th>
                            <th>Issue Date</th>
                            <th>Due Date</th>
                            <th>Total Amount</th>
                            <th>Paid Amount</th>
                            <th>Balance Due</th>
                            <th>Payment State</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $inv)
                            <tr>
                                <td class="fw-bold text-primary font-monospace">{{ $inv->invoice_number }}</td>
                                <td>{{ $inv->issue_date }}</td>
                                <td>{{ $inv->due_date }}</td>
                                <td class="fw-bold">{{ format_currency($inv->total_amount, $inv->currency) }}</td>
                                <td class="text-success">{{ format_currency($inv->paid_amount, $inv->currency) }}</td>
                                <td class="fw-bold text-danger">{{ format_currency($inv->balance_due, $inv->currency) }}</td>
                                <td>
                                    @if($inv->balance_due <= 0 || $inv->status === 'Paid')
                                        <span class="badge bg-success">Fully Paid</span>
                                    @elseif($inv->paid_amount > 0)
                                        <span class="badge bg-warning text-dark">Partial</span>
                                    @else
                                        <span class="badge bg-danger">Unpaid</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center py-4 text-muted">No invoices generated for your account yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB 4: Loyalty Vouchers -->
    <div class="tab-pane fade" id="loyaltyTab" role="tabpanel">
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="brand-font mb-0">My Unlocked Reward Vouchers</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            @forelse($loyaltyRewards as $rew)
                                <div class="list-group-item p-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="badge bg-primary fs-6 font-monospace">{{ $rew->reward_code }}</span>
                                        <span class="badge bg-success-subtle text-success fw-bold">{{ $rew->formatted_discount }}</span>
                                    </div>
                                    <div class="fw-semibold text-dark">{{ $rew->reward_name }}</div>
                                    <small class="text-muted">
                                        Valid until: {{ $rew->expires_at?->format('Y-m-d') ?? 'N/A' }} &bull;
                                        Status: <span class="fw-bold text-capitalize">{{ $rew->status }}</span>
                                    </small>
                                </div>
                            @empty
                                <div class="p-4 text-center text-muted">No vouchers unlocked yet. Complete plot viewings, reservations, or survey bookings to earn rewards!</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="brand-font mb-0">Points Activity Ledger</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Action</th>
                                        <th class="text-end">Points</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($loyaltyTransactions as $lt)
                                        <tr>
                                            <td class="small text-muted">{{ $lt->created_at->format('Y-m-d') }}</td>
                                            <td>{{ $lt->description }}</td>
                                            <td class="text-end fw-bold {{ $lt->points >= 0 ? 'text-success' : 'text-danger' }}">
                                                {{ $lt->points >= 0 ? '+' . $lt->points : $lt->points }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="3" class="text-center text-muted py-3">No activity logged yet.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 5: Document Library Archive -->
    <div class="tab-pane fade" id="vaultTab" role="tabpanel">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="brand-font mb-0">Document Library & Cadastral Blueprints Archive</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>File Name</th>
                                <th>Document Type</th>
                                <th>Size</th>
                                <th>Upload Date</th>
                                <th class="text-end">Download</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($libraryFiles as $doc)
                                <tr>
                                    <td>
                                        <div class="fw-semibold text-dark"><i class="bi bi-file-earmark-pdf text-danger me-2"></i>{{ $doc->file_name }}</div>
                                    </td>
                                    <td><span class="badge bg-light text-dark border">{{ strtoupper($doc->collection_name) }}</span></td>
                                    <td class="small text-muted">{{ round(($doc->file_size ?? 1024) / 1024, 1) }} KB</td>
                                    <td class="small text-muted">{{ $doc->created_at->format('Y-m-d') }}</td>
                                    <td class="text-end">
                                        <a href="{{ $doc->url }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-download"></i> Download
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center py-4 text-muted">No documents attached to your account yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
