@extends('layouts.app')

@section('title', __('app.campaigns'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="brand-font mb-1"><i class="bi bi-megaphone-fill text-primary me-2"></i>Marketing Campaigns & Broadcasts (BM-012)</h3>
        <p class="text-muted small mb-0">Plan digital marketing campaigns, monitor lead generation attribution, and send broadcast notifications</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#broadcastModal">
            <i class="bi bi-send-fill me-1"></i> Send Prospect Broadcast
        </button>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#newCampaignModal">
            <i class="bi bi-plus-lg me-1"></i> Launch Campaign
        </button>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
            <thead class="table-light">
                <tr>
                    <th>Code</th>
                    <th>Campaign Name</th>
                    <th>Channel Type</th>
                    <th>Budget</th>
                    <th>Spent</th>
                    <th>Leads Generated</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($campaigns as $cmp)
                    <tr>
                        <td class="fw-bold text-primary">{{ $cmp->campaign_code }}</td>
                        <td class="fw-semibold">{{ $cmp->name }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $cmp->campaign_type }}</span></td>
                        <td class="fw-bold">{{ format_currency($cmp->budget) }}</td>
                        <td class="text-muted">{{ format_currency($cmp->spent_amount) }}</td>
                        <td><span class="badge bg-primary-subtle text-primary">{{ $cmp->leads_generated }} Leads</span></td>
                        <td><span class="badge bg-success-subtle text-success">{{ $cmp->status }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No marketing campaigns active.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Broadcast Modal -->
<div class="modal fade" id="broadcastModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title brand-font">Dispatch Prospect Broadcast</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('marketing.broadcast') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Dispatch Channel *</label>
                        <select name="channel" class="form-select">
                            <option value="SMS">Direct SMS Blast (Tanzania Telcos)</option>
                            <option value="WhatsApp">WhatsApp Business Broadcast</option>
                            <option value="Email">Email Newsletter</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Target Prospect Segment</label>
                        <select name="target_group" class="form-select">
                            <option value="all_leads">All Active Leads</option>
                            <option value="qualified_buyers">High-Net-Worth Qualified Buyers</option>
                            <option value="masaki_prospects">Masaki Villa Inquiries</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Broadcast Message *</label>
                        <textarea name="message_content" rows="4" class="form-control" placeholder="Exclusive preview: Masaki Oceanview Executive Villa price reduction this weekend..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Dispatch Broadcast</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- New Campaign Modal -->
<div class="modal fade" id="newCampaignModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title brand-font">Launch Marketing Campaign</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('marketing.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Campaign Name *</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Q3 Masaki Oceanview Instagram & Google Ads" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Channel Type</label>
                            <select name="campaign_type" class="form-select">
                                <option value="Social Media">Social Media Ads (Meta/Google)</option>
                                <option value="Billboard">Outdoor Billboard Signage</option>
                                <option value="Exhibition">Property Exhibition / Expo</option>
                                <option value="SMS Blast">Direct SMS Campaign</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Budget ({{ current_currency() }}) *</label>
                            <input type="number" step="0.01" name="budget" class="form-control fw-bold" placeholder="5000000" required>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Start Date *</label>
                            <input type="date" name="start_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">End Date</label>
                            <input type="date" name="end_date" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Launch Campaign</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
