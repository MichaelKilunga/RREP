@extends('layouts.app')

@section('title', 'Customer Loyalty & Retention Engine')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="brand-font mb-1">Customer Loyalty & Retention Engine</h3>
        <p class="text-muted small mb-0">Dynamic reward vouchers, investor tiers, automated birthday campaigns, and PushSMS alerts</p>
    </div>
    <div class="d-flex gap-2">
        <form action="{{ route('loyalty.scan_all') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-outline-primary btn-sm fw-semibold">
                <i class="bi bi-arrow-repeat me-1"></i> Scan & Dispatch Eligible Rewards
            </button>
        </form>
        <button type="button" class="btn btn-primary btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#newRuleModal">
            <i class="bi bi-plus-lg me-1"></i> New Loyalty Tier Rule
        </button>
        <button type="button" class="btn btn-success btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#redeemModal">
            <i class="bi bi-qr-code me-1"></i> Redeem Voucher Code
        </button>
    </div>
</div>

<!-- KPI Stats -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white border-0 shadow-sm">
            <div class="card-body">
                <div class="text-white-50 small fw-semibold text-uppercase">Total Points Issued</div>
                <div class="h3 fw-bold mb-0 mt-1">{{ number_format($stats['total_points_issued']) }} pts</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-white">
            <div class="card-body">
                <div class="text-muted small fw-semibold text-uppercase">Active Members</div>
                <div class="h3 fw-bold text-dark mb-0 mt-1">{{ number_format($stats['total_members']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-white">
            <div class="card-body">
                <div class="text-muted small fw-semibold text-uppercase">Active Vouchers</div>
                <div class="h3 fw-bold text-success mb-0 mt-1">{{ number_format($stats['active_rewards']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-white">
            <div class="card-body">
                <div class="text-muted small fw-semibold text-uppercase">Redeemed Vouchers</div>
                <div class="h3 fw-bold text-info mb-0 mt-1">{{ number_format($stats['redeemed_rewards']) }}</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Active Loyalty Tiers / Rules -->
    <div class="col-lg-6">
        <div class="card h-100 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="brand-font mb-0">Investor & Buyer Loyalty Tiers</h5>
                <span class="badge bg-primary-subtle text-primary">{{ $rules->count() }} Tiers</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Tier Name</th>
                                <th>Min Pts / Deals</th>
                                <th>Discount</th>
                                <th>Prefix</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rules as $rule)
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $rule->name }}</div>
                                        <small class="text-muted">Valid {{ $rule->validity_days }} days</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">{{ $rule->min_points }} pts</span>
                                        @if($rule->min_transactions > 0)
                                            <span class="badge bg-light text-dark border ms-1">{{ $rule->min_transactions }} deals</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-success-subtle text-success fw-bold">{{ $rule->formatted_discount }}</span>
                                    </td>
                                    <td><code>{{ $rule->code_prefix }}</code></td>
                                    <td>
                                        @if($rule->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editRuleModal{{ $rule->id }}">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                    </td>
                                </tr>

                                <!-- Edit Rule Modal -->
                                <div class="modal fade" id="editRuleModal{{ $rule->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="{{ route('loyalty.rules.update', $rule->id) }}" method="POST">
                                                @csrf
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Tier: {{ $rule->name }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold">Tier Name</label>
                                                        <input type="text" name="name" class="form-control" value="{{ $rule->name }}" required>
                                                    </div>
                                                    <div class="row g-2 mb-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label small fw-semibold">Min Points</label>
                                                            <input type="number" name="min_points" class="form-control" value="{{ $rule->min_points }}" required>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label small fw-semibold">Min Transactions</label>
                                                            <input type="number" name="min_transactions" class="form-control" value="{{ $rule->min_transactions }}" required>
                                                        </div>
                                                    </div>
                                                    <div class="row g-2 mb-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label small fw-semibold">Discount Type</label>
                                                            <select name="discount_type" class="form-select">
                                                                <option value="percentage" @selected($rule->discount_type === 'percentage')>Percentage (%)</option>
                                                                <option value="fixed" @selected($rule->discount_type === 'fixed')>Fixed Amount (TZS)</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label small fw-semibold">Discount Value</label>
                                                            <input type="number" step="0.1" name="discount_value" class="form-control" value="{{ $rule->discount_value }}" required>
                                                        </div>
                                                    </div>
                                                    <div class="row g-2 mb-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label small fw-semibold">Voucher Code Prefix</label>
                                                            <input type="text" name="code_prefix" class="form-control" value="{{ $rule->code_prefix }}" required>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label small fw-semibold">Validity (Days)</label>
                                                            <input type="number" name="validity_days" class="form-control" value="{{ $rule->validity_days }}" required>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold">Custom SMS Notification Template</label>
                                                        <textarea name="sms_template" class="form-control" rows="3">{{ $rule->sms_template }}</textarea>
                                                        <small class="text-muted">Placeholders: {customer_name}, {discount}, {reward_code}, {expiry_date}, {company_name}</small>
                                                    </div>
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="activeSwitch{{ $rule->id }}" @checked($rule->is_active)>
                                                        <label class="form-check-label" for="activeSwitch{{ $rule->id }}">Tier Active & Auto-Issuing</label>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary">Save Changes</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <tr><td colspan="6" class="text-center py-3 text-muted">No rules found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Points Adjustment -->
    <div class="col-lg-6">
        <div class="card h-100 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="brand-font mb-0">Award or Adjust Client Loyalty Points</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('loyalty.adjust_points') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Select Customer</label>
                        <select name="customer_id" class="form-select" required>
                            <option value="">-- Choose Client --</option>
                            @foreach(\App\Models\Customer::orderBy('first_name')->take(50)->get() as $cust)
                                <option value="{{ $cust->id }}">
                                    {{ $cust->full_name }} ({{ $cust->phone }}) - Currently: {{ $cust->loyalty_points ?? 0 }} pts [{{ $cust->loyalty_tier }}]
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Points Delta (Use negative to deduct)</label>
                            <input type="number" name="points" class="form-control" placeholder="e.g. 100 or -50" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Reason / Reference</label>
                            <input type="text" name="reason" class="form-control" placeholder="e.g. VIP Plot Purchase Bonus" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 fw-semibold">
                        <i class="bi bi-star-fill me-1"></i> Apply Loyalty Points & Evaluate Tier
                    </button>
                </form>

                <hr class="my-4">

                <h6 class="fw-bold mb-2">Automated Retention Rules</h6>
                <div class="list-group list-group-flush small">
                    <div class="list-group-item px-0 py-2 d-flex justify-content-between">
                        <span><i class="bi bi-house-check text-primary me-2"></i>Plot Reservation Hold</span>
                        <span class="fw-bold text-success">+100 pts</span>
                    </div>
                    <div class="list-group-item px-0 py-2 d-flex justify-content-between">
                        <span><i class="bi bi-geo-alt text-primary me-2"></i>Land Survey Booking</span>
                        <span class="fw-bold text-success">+150 pts</span>
                    </div>
                    <div class="list-group-item px-0 py-2 d-flex justify-content-between">
                        <span><i class="bi bi-cash-coin text-primary me-2"></i>Property Sale Closing</span>
                        <span class="fw-bold text-success">+500 pts</span>
                    </div>
                    <div class="list-group-item px-0 py-2 d-flex justify-content-between">
                        <span><i class="bi bi-cake2 text-primary me-2"></i>Birthday Retention Voucher</span>
                        <span class="fw-bold text-success">+100 pts & Voucher</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Issued Reward Vouchers -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="brand-font mb-0">Reward Vouchers & Discount Codes</h5>
        <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#redeemModal">
            <i class="bi bi-check-circle me-1"></i> Validate / Redeem Code
        </button>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Voucher Code</th>
                        <th>Client</th>
                        <th>Reward Name</th>
                        <th>Discount</th>
                        <th>Issued / Expires</th>
                        <th>Status</th>
                        <th>SMS Delivered</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rewards as $reward)
                        <tr>
                            <td>
                                <span class="badge bg-dark-subtle text-dark fs-6 font-monospace">{{ $reward->reward_code }}</span>
                            </td>
                            <td>
                                <div class="fw-bold">{{ $reward->customer->full_name }}</div>
                                <small class="text-muted">{{ $reward->customer->phone }}</small>
                            </td>
                            <td>{{ $reward->reward_name }}</td>
                            <td>
                                <span class="badge bg-success-subtle text-success fw-bold">{{ $reward->formatted_discount }}</span>
                            </td>
                            <td>
                                <div>{{ $reward->issued_at?->format('Y-m-d') ?? 'N/A' }}</div>
                                <small class="text-muted">Exp: {{ $reward->expires_at?->format('Y-m-d') ?? 'Never' }}</small>
                            </td>
                            <td>
                                @if($reward->status === 'active')
                                    <span class="badge bg-success">Active</span>
                                @elseif($reward->status === 'redeemed')
                                    <span class="badge bg-info">Redeemed</span>
                                @elseif($reward->status === 'expired')
                                    <span class="badge bg-warning">Expired</span>
                                @else
                                    <span class="badge bg-secondary">{{ $reward->status }}</span>
                                @endif
                            </td>
                            <td>
                                @if($reward->sms_sent)
                                    <span class="text-success"><i class="bi bi-check-all"></i> Sent</span>
                                @else
                                    <span class="text-muted"><i class="bi bi-dash"></i> Pending/No Phone</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-4 text-muted">No reward vouchers issued yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">
            {{ $rewards->links() }}
        </div>
    </div>
</div>

<!-- Modal: New Loyalty Rule -->
<div class="modal fade" id="newRuleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('loyalty.rules.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Create New Loyalty Tier Rule</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Tier Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Diamond Investor" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Min Points</label>
                            <input type="number" name="min_points" class="form-control" value="200" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Min Transactions</label>
                            <input type="number" name="min_transactions" class="form-control" value="2" required>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Discount Type</label>
                            <select name="discount_type" class="form-select">
                                <option value="percentage">Percentage (%)</option>
                                <option value="fixed">Fixed Amount (TZS)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Discount Value</label>
                            <input type="number" step="0.1" name="discount_value" class="form-control" value="10" required>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Voucher Code Prefix</label>
                            <input type="text" name="code_prefix" class="form-control" value="AVENIX" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Validity (Days)</label>
                            <input type="number" name="validity_days" class="form-control" value="60" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">SMS Notification Template</label>
                        <textarea name="sms_template" class="form-control" rows="3" placeholder="Hongera {customer_name}! Umepata daraja jipya..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Tier Rule</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Redeem Voucher -->
<div class="modal fade" id="redeemModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('loyalty.redeem') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Redeem Client Reward Voucher</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Voucher Code</label>
                        <input type="text" name="reward_code" class="form-control form-control-lg text-uppercase font-monospace" placeholder="e.g. AVENIX-4821-X9" required>
                        <small class="text-muted">Enter code presented by buyer or survey client.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Validate & Mark Redeemed</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
