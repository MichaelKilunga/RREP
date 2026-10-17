@extends('layouts.app')

@section('title', __('app.campaigns'))

@section('content')
@php
    $campaignBadge = ['Active' => 'active', 'Planned' => 'pending', 'Draft' => 'inactive', 'Paused' => 'review', 'Completed' => 'paid', 'Cancelled' => 'cancelled'];
    $pageBudget = $campaigns->sum('budget');
    $pageSpent = $campaigns->sum('spent_amount');
    $pageLeads = $campaigns->sum('leads_generated');
@endphp

<div class="page-header">
    <div class="min-w-0">
        <h1 class="brand-font mb-1"><i class="bi bi-megaphone-fill text-primary me-2"></i>{{ __('ops.marketing_title') }}</h1>
        <p class="text-muted small mb-0">{{ __('ops.marketing_subtitle') }}</p>
    </div>
    <div class="page-actions">
        <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#broadcastModal">
            <i class="bi bi-send-fill me-1"></i> {{ __('ops.send_broadcast') }}
        </button>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newCampaignModal">
            <i class="bi bi-plus-lg me-1"></i> {{ __('ops.launch_campaign') }}
        </button>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-4">
        <div class="kpi-card">
            <div class="kpi-label">{{ __('ops.total_budget') }}</div>
            <div class="kpi-value">{{ format_money_short($pageBudget) }}</div>
            <span class="kpi-icon"><i class="bi bi-wallet2"></i></span>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="kpi-card kpi-danger">
            <div class="kpi-label">{{ __('ops.spent') }}</div>
            <div class="kpi-value text-danger">{{ format_money_short($pageSpent) }}</div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="kpi-card kpi-success">
            <div class="kpi-label">{{ __('ops.leads_generated') }}</div>
            <div class="kpi-value text-success">{{ number_format($pageLeads) }}</div>
            <span class="kpi-icon"><i class="bi bi-funnel"></i></span>
        </div>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 table-stack small">
            <thead class="table-light">
                <tr>
                    <th>{{ __('ops.code') }}</th>
                    <th>{{ __('ops.campaign_name') }}</th>
                    <th>{{ __('ops.channel') }}</th>
                    <th>{{ __('ops.budget') }}</th>
                    <th>{{ __('ops.spent') }}</th>
                    <th>{{ __('ops.leads_generated') }}</th>
                    <th>{{ __('app.status') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($campaigns as $cmp)
                    <tr>
                        <td class="stack-primary"><span class="fw-bold text-primary">{{ $cmp->campaign_code }}</span> &bull; {{ $cmp->name }}</td>
                        <td class="fw-semibold d-none d-md-table-cell">{{ $cmp->name }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $cmp->campaign_type }}</span></td>
                        <td class="fw-bold">{{ format_currency($cmp->budget) }}</td>
                        <td class="text-muted">{{ format_currency($cmp->spent_amount) }}</td>
                        <td><span class="badge bg-primary-subtle text-primary">{{ __('ops.leads_count', ['count' => $cmp->leads_generated]) }}</span></td>
                        <td><span class="badge badge-status-{{ $campaignBadge[$cmp->status] ?? strtolower((string) $cmp->status) }}">{{ $cmp->status }}</span></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="stack-no-label">
                            <div class="empty-state">
                                <div class="empty-icon"><i class="bi bi-megaphone"></i></div>
                                <h6>{{ __('ops.no_campaigns') }}</h6>
                                <p class="small mb-3">{{ __('ops.no_campaigns_hint') }}</p>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newCampaignModal"><i class="bi bi-plus-lg me-1"></i> {{ __('ops.launch_campaign') }}</button>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Broadcast Modal -->
<div class="modal fade" id="broadcastModal" tabindex="-1" aria-labelledby="broadcastModalLabel">
    <div class="modal-dialog modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title brand-font" id="broadcastModalLabel">{{ __('ops.broadcast_title') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('app.cancel') }}"></button>
            </div>
            <form action="{{ route('marketing.broadcast') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="bcChannel" class="form-label small fw-semibold">{{ __('ops.channel') }} *</label>
                        <select id="bcChannel" name="channel" class="form-select">
                            <option value="SMS">{{ __('ops.channel_sms') }}</option>
                            <option value="WhatsApp">{{ __('ops.channel_whatsapp') }}</option>
                            <option value="Email">{{ __('ops.channel_email') }}</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="bcTarget" class="form-label small fw-semibold">{{ __('ops.target_segment') }}</label>
                        <select id="bcTarget" name="target_group" class="form-select">
                            <option value="all_leads">{{ __('ops.segment_all_leads') }}</option>
                            <option value="qualified_buyers">{{ __('ops.segment_qualified') }}</option>
                            <option value="masaki_prospects">{{ __('ops.segment_masaki') }}</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="bcMessage" class="form-label small fw-semibold">{{ __('ops.broadcast_message') }} *</label>
                        <textarea id="bcMessage" name="message_content" rows="4" class="form-control" maxlength="480" placeholder="{{ __('ops.broadcast_placeholder') }}" required></textarea>
                        <div class="form-text"><span id="bcCount">0</span>/480 &bull; {{ __('ops.sms_segments_hint') }}</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">{{ __('app.cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('ops.dispatch_broadcast') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- New Campaign Modal -->
<div class="modal fade" id="newCampaignModal" tabindex="-1" aria-labelledby="newCampaignModalLabel">
    <div class="modal-dialog modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title brand-font" id="newCampaignModalLabel">{{ __('ops.launch_campaign_title') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('app.cancel') }}"></button>
            </div>
            <form action="{{ route('marketing.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="cmpName" class="form-label small fw-semibold">{{ __('ops.campaign_name') }} *</label>
                        <input type="text" id="cmpName" name="name" class="form-control" placeholder="{{ __('ops.campaign_name_placeholder') }}" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-12 col-sm-6">
                            <label for="cmpType" class="form-label small fw-semibold">{{ __('ops.channel') }}</label>
                            <select id="cmpType" name="campaign_type" class="form-select">
                                <option value="Social Media">{{ __('ops.campaign_social') }}</option>
                                <option value="WhatsApp">{{ __('ops.campaign_whatsapp') }}</option>
                                <option value="SMS Blast">{{ __('ops.campaign_sms') }}</option>
                                <option value="Radio">{{ __('ops.campaign_radio') }}</option>
                                <option value="Billboard">{{ __('ops.campaign_billboard') }}</option>
                                <option value="Exhibition">{{ __('ops.campaign_exhibition') }}</option>
                            </select>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label for="cmpBudget" class="form-label small fw-semibold">{{ __('ops.budget') }} *</label>
                            <div class="input-group">
                                <span class="input-group-text">TSh</span>
                                <input type="number" step="0.01" min="0" inputmode="decimal" id="cmpBudget" name="budget" class="form-control fw-bold" placeholder="5000000" required>
                            </div>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label for="cmpStart" class="form-label small fw-semibold">{{ __('ops.start_date') }} *</label>
                            <input type="date" id="cmpStart" name="start_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-6">
                            <label for="cmpEnd" class="form-label small fw-semibold">{{ __('ops.end_date') }}</label>
                            <input type="date" id="cmpEnd" name="end_date" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">{{ __('app.cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('ops.launch_campaign') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const message = document.getElementById('bcMessage');
    const counter = document.getElementById('bcCount');
    if (message && counter) {
        message.addEventListener('input', function () { counter.textContent = message.value.length; });
    }
});
</script>
@endsection
