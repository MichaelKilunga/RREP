@extends('layouts.app')

@section('title', __('ops.loyalty_title'))

@section('content')
@php
    $rewardBadge = ['active' => 'active', 'redeemed' => 'review', 'expired' => 'overdue', 'cancelled' => 'cancelled'];
@endphp

<div class="page-header">
    <div class="min-w-0">
        <h1 class="brand-font mb-1">{{ __('ops.loyalty_title') }}</h1>
        <p class="text-muted small mb-0">{{ __('ops.loyalty_subtitle') }}</p>
    </div>
    <div class="page-actions">
        <form action="{{ route('loyalty.scan_all') }}" method="POST" class="d-flex flex-fill flex-sm-grow-0">
            @csrf
            <button type="submit" class="btn btn-outline-primary">
                <i class="bi bi-arrow-repeat me-1"></i> {{ __('ops.scan_rewards') }}
            </button>
        </form>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newRuleModal">
            <i class="bi bi-plus-lg me-1"></i> {{ __('ops.new_tier_rule') }}
        </button>
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#redeemModal">
            <i class="bi bi-qr-code me-1"></i> {{ __('ops.redeem_voucher') }}
        </button>
    </div>
</div>

<!-- KPI Stats -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-label">{{ __('ops.total_points_issued') }}</div>
            <div class="kpi-value">{{ number_format($stats['total_points_issued']) }} <small class="fs-6 text-muted">pts</small></div>
            <span class="kpi-icon"><i class="bi bi-star-fill"></i></span>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card kpi-info">
            <div class="kpi-label">{{ __('ops.active_members') }}</div>
            <div class="kpi-value">{{ number_format($stats['total_members']) }}</div>
            <span class="kpi-icon"><i class="bi bi-people"></i></span>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card kpi-success">
            <div class="kpi-label">{{ __('ops.active_vouchers') }}</div>
            <div class="kpi-value text-success">{{ number_format($stats['active_rewards']) }}</div>
            <span class="kpi-icon"><i class="bi bi-ticket-perforated"></i></span>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card kpi-warning">
            <div class="kpi-label">{{ __('ops.redeemed_vouchers') }}</div>
            <div class="kpi-value">{{ number_format($stats['redeemed_rewards']) }}</div>
            <span class="kpi-icon"><i class="bi bi-check2-circle"></i></span>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Active Loyalty Tiers / Rules -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center gap-2">
                <h2 class="brand-font h6 mb-0 text-truncate">{{ __('ops.loyalty_tiers') }}</h2>
                <span class="badge bg-primary-subtle text-primary text-nowrap">{{ __('ops.tiers_count', ['count' => $rules->count()]) }}</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 table-stack small">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('ops.tier_name') }}</th>
                                <th>{{ __('ops.min_points_deals') }}</th>
                                <th>{{ __('ops.discount') }}</th>
                                <th>{{ __('ops.prefix') }}</th>
                                <th>{{ __('app.status') }}</th>
                                <th class="text-end">{{ __('app.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rules as $rule)
                                <tr>
                                    <td class="stack-primary">
                                        <div class="fw-bold text-dark">{{ $rule->name }}</div>
                                        <small class="text-muted fw-normal">{{ __('ops.valid_days', ['days' => $rule->validity_days]) }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">{{ $rule->min_points }} pts</span>
                                        @if($rule->min_transactions > 0)
                                            <span class="badge bg-light text-dark border ms-1">{{ $rule->min_transactions }} {{ __('ops.deals_short') }}</span>
                                        @endif
                                    </td>
                                    <td><span class="badge badge-status-paid">{{ $rule->formatted_discount }}</span></td>
                                    <td><code>{{ $rule->code_prefix }}</code></td>
                                    <td>
                                        @if($rule->is_active)
                                            <span class="badge badge-status-active">{{ __('ops.active') }}</span>
                                        @else
                                            <span class="badge badge-status-inactive">{{ __('ops.inactive') }}</span>
                                        @endif
                                    </td>
                                    <td class="stack-actions text-end">
                                        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editRuleModal{{ $rule->id }}" aria-label="{{ __('app.edit') }} {{ $rule->name }}">
                                            <i class="bi bi-pencil"></i><span class="d-md-none ms-1">{{ __('app.edit') }}</span>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="stack-no-label">
                                        <div class="empty-state py-4">
                                            <div class="empty-icon"><i class="bi bi-trophy"></i></div>
                                            <h6>{{ __('ops.no_rules') }}</h6>
                                            <p class="small mb-3">{{ __('ops.no_rules_hint') }}</p>
                                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#newRuleModal">{{ __('ops.new_tier_rule') }}</button>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Points Adjustment -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">
                <h2 class="brand-font h6 mb-0">{{ __('ops.adjust_points_title') }}</h2>
            </div>
            <div class="card-body">
                <form action="{{ route('loyalty.adjust_points') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="adjustCustomer" class="form-label small fw-semibold">{{ __('ops.select_customer') }}</label>
                        <select id="adjustCustomer" name="customer_id" class="form-select" required>
                            <option value="">{{ __('ops.choose_client') }}</option>
                            @foreach(\App\Models\Customer::orderBy('first_name')->take(50)->get() as $cust)
                                <option value="{{ $cust->id }}">
                                    {{ $cust->full_name }} ({{ format_phone_tz($cust->phone) }}) &mdash; {{ $cust->loyalty_points ?? 0 }} pts [{{ $cust->loyalty_tier }}]
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-12 col-sm-6">
                            <label for="adjustPoints" class="form-label small fw-semibold">{{ __('ops.points_delta') }}</label>
                            <input type="number" id="adjustPoints" name="points" class="form-control" placeholder="100 / -50" inputmode="numeric" required>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label for="adjustReason" class="form-label small fw-semibold">{{ __('ops.reason_reference') }}</label>
                            <input type="text" id="adjustReason" name="reason" class="form-control" placeholder="{{ __('ops.reason_placeholder') }}" required>
                        </div>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary fw-semibold">
                            <i class="bi bi-star-fill me-1"></i> {{ __('ops.apply_points') }}
                        </button>
                    </div>
                </form>

                <hr class="my-4">

                <h3 class="fw-bold h6 mb-2">{{ __('ops.automated_rules') }}</h3>
                <div class="list-group list-group-flush small">
                    <div class="list-group-item px-0 py-2 d-flex justify-content-between gap-2">
                        <span><i class="bi bi-house-check text-primary me-2"></i>{{ __('ops.rule_reservation') }}</span>
                        <span class="fw-bold text-success text-nowrap">+100 pts</span>
                    </div>
                    <div class="list-group-item px-0 py-2 d-flex justify-content-between gap-2">
                        <span><i class="bi bi-geo-alt text-primary me-2"></i>{{ __('ops.rule_survey') }}</span>
                        <span class="fw-bold text-success text-nowrap">+150 pts</span>
                    </div>
                    <div class="list-group-item px-0 py-2 d-flex justify-content-between gap-2">
                        <span><i class="bi bi-cash-coin text-primary me-2"></i>{{ __('ops.rule_sale') }}</span>
                        <span class="fw-bold text-success text-nowrap">+500 pts</span>
                    </div>
                    <div class="list-group-item px-0 py-2 d-flex justify-content-between gap-2">
                        <span><i class="bi bi-cake2 text-primary me-2"></i>{{ __('ops.rule_birthday') }}</span>
                        <span class="fw-bold text-success text-nowrap">+100 pts</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Issued Reward Vouchers -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center gap-2">
        <h2 class="brand-font h6 mb-0 text-truncate">{{ __('ops.reward_vouchers') }}</h2>
        <button type="button" class="btn btn-sm btn-outline-success text-nowrap" data-bs-toggle="modal" data-bs-target="#redeemModal">
            <i class="bi bi-check-circle me-1"></i> {{ __('ops.validate_redeem') }}
        </button>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 table-stack small">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('ops.voucher_code') }}</th>
                        <th>{{ __('ops.customer') }}</th>
                        <th>{{ __('ops.reward_name') }}</th>
                        <th>{{ __('ops.discount') }}</th>
                        <th>{{ __('ops.issued_expires') }}</th>
                        <th>{{ __('app.status') }}</th>
                        <th>{{ __('ops.sms_delivered') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rewards as $reward)
                        <tr>
                            <td class="stack-primary"><span class="badge bg-dark-subtle text-dark fs-6 font-monospace">{{ $reward->reward_code }}</span></td>
                            <td>
                                <div class="fw-bold">{{ $reward->customer?->full_name }}</div>
                                @if($reward->customer?->phone)<small class="text-muted">{{ format_phone_tz($reward->customer->phone) }}</small>@endif
                            </td>
                            <td>{{ $reward->reward_name }}</td>
                            <td><span class="badge badge-status-paid">{{ $reward->formatted_discount }}</span></td>
                            <td>
                                <div>{{ $reward->issued_at?->format('d M Y') ?? '—' }}</div>
                                <small class="text-muted">{{ __('ops.expires') }}: {{ $reward->expires_at?->format('d M Y') ?? __('ops.never') }}</small>
                            </td>
                            <td><span class="badge badge-status-{{ $rewardBadge[$reward->status] ?? strtolower((string) $reward->status) }}">{{ __('ops.reward_status_'.strtolower($reward->status)) }}</span></td>
                            <td>
                                @if($reward->sms_sent)
                                    <span class="text-success"><i class="bi bi-check-all"></i> {{ __('ops.sent') }}</span>
                                @else
                                    <span class="text-muted"><i class="bi bi-dash"></i> {{ __('ops.pending_no_phone') }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="stack-no-label">
                                <div class="empty-state">
                                    <div class="empty-icon"><i class="bi bi-ticket-perforated"></i></div>
                                    <h6>{{ __('ops.no_vouchers') }}</h6>
                                    <p class="small mb-0">{{ __('ops.no_vouchers_hint') }}</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($rewards->hasPages())
            <div class="p-3 border-top d-flex justify-content-center justify-content-md-end">
                {{ $rewards->links() }}
            </div>
        @endif
    </div>
</div>

@foreach($rules as $rule)
    <!-- Edit Rule Modal -->
    <div class="modal fade" id="editRuleModal{{ $rule->id }}" tabindex="-1" aria-labelledby="editRuleModalLabel{{ $rule->id }}">
        <div class="modal-dialog modal-dialog-scrollable modal-fullscreen-sm-down">
            <div class="modal-content">
                <form action="{{ route('loyalty.rules.update', $rule->id) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="editRuleModalLabel{{ $rule->id }}">{{ __('ops.edit_tier') }}: {{ $rule->name }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('app.cancel') }}"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="ruleName{{ $rule->id }}" class="form-label small fw-semibold">{{ __('ops.tier_name') }}</label>
                            <input type="text" id="ruleName{{ $rule->id }}" name="name" class="form-control" value="{{ $rule->name }}" required>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label for="ruleMinPoints{{ $rule->id }}" class="form-label small fw-semibold">{{ __('ops.min_points') }}</label>
                                <input type="number" id="ruleMinPoints{{ $rule->id }}" name="min_points" class="form-control" value="{{ $rule->min_points }}" inputmode="numeric" required>
                            </div>
                            <div class="col-6">
                                <label for="ruleMinTx{{ $rule->id }}" class="form-label small fw-semibold">{{ __('ops.min_transactions') }}</label>
                                <input type="number" id="ruleMinTx{{ $rule->id }}" name="min_transactions" class="form-control" value="{{ $rule->min_transactions }}" inputmode="numeric" required>
                            </div>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label for="ruleDiscountType{{ $rule->id }}" class="form-label small fw-semibold">{{ __('ops.discount_type') }}</label>
                                <select id="ruleDiscountType{{ $rule->id }}" name="discount_type" class="form-select">
                                    <option value="percentage" @selected($rule->discount_type === 'percentage')>{{ __('ops.percentage') }}</option>
                                    <option value="fixed" @selected($rule->discount_type === 'fixed')>{{ __('ops.fixed_amount_tzs') }}</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label for="ruleDiscountValue{{ $rule->id }}" class="form-label small fw-semibold">{{ __('ops.discount_value') }}</label>
                                <input type="number" step="0.1" min="0" inputmode="decimal" id="ruleDiscountValue{{ $rule->id }}" name="discount_value" class="form-control" value="{{ $rule->discount_value }}" required>
                            </div>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label for="rulePrefix{{ $rule->id }}" class="form-label small fw-semibold">{{ __('ops.voucher_prefix') }}</label>
                                <input type="text" id="rulePrefix{{ $rule->id }}" name="code_prefix" class="form-control" value="{{ $rule->code_prefix }}" required>
                            </div>
                            <div class="col-6">
                                <label for="ruleValidity{{ $rule->id }}" class="form-label small fw-semibold">{{ __('ops.validity_days') }}</label>
                                <input type="number" id="ruleValidity{{ $rule->id }}" name="validity_days" class="form-control" value="{{ $rule->validity_days }}" inputmode="numeric" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="ruleSms{{ $rule->id }}" class="form-label small fw-semibold">{{ __('ops.sms_template') }}</label>
                            <textarea id="ruleSms{{ $rule->id }}" name="sms_template" class="form-control" rows="3">{{ $rule->sms_template }}</textarea>
                            <div class="form-text">{{ __('ops.sms_placeholders') }}: {customer_name}, {discount}, {reward_code}, {expiry_date}, {company_name}</div>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="activeSwitch{{ $rule->id }}" @checked($rule->is_active)>
                            <label class="form-check-label" for="activeSwitch{{ $rule->id }}">{{ __('ops.tier_active_label') }}</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light border" data-bs-dismiss="modal">{{ __('app.cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('app.save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach

<!-- Modal: New Loyalty Rule -->
<div class="modal fade" id="newRuleModal" tabindex="-1" aria-labelledby="newRuleModalLabel">
    <div class="modal-dialog modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content">
            <form action="{{ route('loyalty.rules.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="newRuleModalLabel">{{ __('ops.new_tier_title') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('app.cancel') }}"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="newRuleName" class="form-label small fw-semibold">{{ __('ops.tier_name') }}</label>
                        <input type="text" id="newRuleName" name="name" class="form-control" placeholder="{{ __('ops.tier_name_placeholder') }}" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label for="newRuleMinPoints" class="form-label small fw-semibold">{{ __('ops.min_points') }}</label>
                            <input type="number" id="newRuleMinPoints" name="min_points" class="form-control" value="200" inputmode="numeric" required>
                        </div>
                        <div class="col-6">
                            <label for="newRuleMinTx" class="form-label small fw-semibold">{{ __('ops.min_transactions') }}</label>
                            <input type="number" id="newRuleMinTx" name="min_transactions" class="form-control" value="2" inputmode="numeric" required>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label for="newRuleDiscountType" class="form-label small fw-semibold">{{ __('ops.discount_type') }}</label>
                            <select id="newRuleDiscountType" name="discount_type" class="form-select">
                                <option value="percentage">{{ __('ops.percentage') }}</option>
                                <option value="fixed">{{ __('ops.fixed_amount_tzs') }}</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label for="newRuleDiscountValue" class="form-label small fw-semibold">{{ __('ops.discount_value') }}</label>
                            <input type="number" step="0.1" min="0" inputmode="decimal" id="newRuleDiscountValue" name="discount_value" class="form-control" value="10" required>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label for="newRulePrefix" class="form-label small fw-semibold">{{ __('ops.voucher_prefix') }}</label>
                            <input type="text" id="newRulePrefix" name="code_prefix" class="form-control" value="{{ strtoupper(substr(preg_replace('/[^A-Za-z]/', '', (string) setting('company_name', 'REHO')), 0, 6)) ?: 'REHO' }}" required>
                        </div>
                        <div class="col-6">
                            <label for="newRuleValidity" class="form-label small fw-semibold">{{ __('ops.validity_days') }}</label>
                            <input type="number" id="newRuleValidity" name="validity_days" class="form-control" value="60" inputmode="numeric" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="newRuleSms" class="form-label small fw-semibold">{{ __('ops.sms_template') }}</label>
                        <textarea id="newRuleSms" name="sms_template" class="form-control" rows="3" placeholder="Hongera {customer_name}! Umepata daraja jipya..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">{{ __('app.cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('ops.create_tier_rule') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Redeem Voucher -->
<div class="modal fade" id="redeemModal" tabindex="-1" aria-labelledby="redeemModalLabel">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <form action="{{ route('loyalty.redeem') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="redeemModalLabel">{{ __('ops.redeem_title') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('app.cancel') }}"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="redeemCode" class="form-label small fw-semibold">{{ __('ops.voucher_code') }}</label>
                        <input type="text" id="redeemCode" name="reward_code" class="form-control form-control-lg text-uppercase font-monospace" placeholder="e.g. REHO-4821-X9" autocomplete="off" autocapitalize="characters" required>
                        <div class="form-text">{{ __('ops.redeem_hint') }}</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">{{ __('app.cancel') }}</button>
                    <button type="submit" class="btn btn-success">{{ __('ops.validate_mark_redeemed') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
