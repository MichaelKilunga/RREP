@extends('layouts.app')

@section('title', __('app.leads'))

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h3 class="brand-font mb-1">{{ __('app.leads') }} & Pipeline</h3>
        <p class="text-muted small mb-0">Track prospect lifecycles from inquiry to won sales contracts</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-primary btn-sm d-flex align-items-center gap-1 shadow-sm" data-bs-toggle="modal" data-bs-target="#newLeadModal">
            <i class="bi bi-plus-lg"></i> Create New Lead
        </button>
    </div>
</div>

<!-- Kanban Columns -->
<div class="row g-3 flex-nowrap overflow-auto pb-4" style="min-height: 70vh;">
    @foreach($stages as $stage)
        @php
            $stageLeads = $leads->where('stage', $stage);
        @endphp
        <div class="col-12 col-md-4 col-xl-3" style="min-width: 280px;">
            <div class="card bg-light border-0 shadow-none h-100 p-2">
                <div class="d-flex justify-content-between align-items-center p-2 mb-2">
                    <span class="fw-bold brand-font" style="font-size: 0.9rem;">{{ $stage }}</span>
                    <span class="badge bg-white text-dark border">{{ $stageLeads->count() }}</span>
                </div>

                <div class="d-flex flex-column gap-2 kanban-column" data-stage="{{ $stage }}">
                    @forelse($stageLeads as $lead)
                        <div class="card p-3 shadow-sm border bg-white lead-card" style="cursor: pointer;">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge bg-primary-subtle text-primary" style="font-size: 0.7rem;">{{ $lead->source }}</span>
                                <span class="badge bg-{{ $lead->priority == 'High' ? 'danger' : 'secondary' }}-subtle text-{{ $lead->priority == 'High' ? 'danger' : 'secondary' }}" style="font-size: 0.7rem;">{{ $lead->priority }}</span>
                            </div>
                            <h6 class="fw-bold mb-1" style="font-size: 0.875rem;">{{ $lead->customer?->full_name }}</h6>
                            <p class="text-muted small mb-2" style="font-size: 0.8rem;">{{ Str::limit($lead->title, 40) }}</p>
                            
                            @if($lead->estimated_value)
                                <div class="fw-bold text-success small mb-2">{{ format_currency($lead->estimated_value) }}</div>
                            @endif

                            <div class="border-top pt-2 d-flex justify-content-between align-items-center" style="font-size: 0.75rem;">
                                <span class="text-muted"><i class="bi bi-person me-1"></i>{{ $lead->agent?->user?->name ?? 'Unassigned' }}</span>
                                <button class="btn btn-sm btn-link p-0 text-decoration-none" data-bs-toggle="modal" data-bs-target="#activityModal{{ $lead->id }}">
                                    <i class="bi bi-chat-left-text text-primary"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Activity Modal -->
                        <div class="modal fade" id="activityModal{{ $lead->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h6 class="modal-title fw-bold">Log Activity: {{ $lead->customer?->full_name }}</h6>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form action="{{ route('crm.leads.log_activity', $lead) }}" method="POST">
                                        @csrf
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold">Activity Type</label>
                                                <select name="activity_type" class="form-select form-select-sm" required>
                                                    <option value="Call">Phone Call</option>
                                                    <option value="Site Visit">Site Inspection / Visit</option>
                                                    <option value="Meeting">Office Meeting</option>
                                                    <option value="WhatsApp">WhatsApp / Chat</option>
                                                    <option value="Email">Email Communication</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold">Summary / Outcome</label>
                                                <input type="text" name="summary" class="form-control form-control-sm" placeholder="e.g. Client requested contract review on Masaki Villa" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold">Detailed Notes</label>
                                                <textarea name="details" rows="3" class="form-control form-control-sm"></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-sm btn-primary">Save Activity</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted small py-4 border rounded border-dashed">No leads in {{ $stage }}</div>
                    @endforelse
                </div>
            </div>
        </div>
    @endforeach
</div>

<!-- New Lead Modal -->
<div class="modal fade" id="newLeadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title brand-font">Create New Lead Prospect</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('crm.leads.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Customer / Client *</label>
                        <select name="customer_id" class="form-select select2" required style="width: 100%;">
                            <option value="">Select Existing Customer</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->full_name }} ({{ $c->phone }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Lead Title / Inquiry *</label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Inquiring for 3-Bedroom Villa in Masaki" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Source</label>
                            <select name="source" class="form-select">
                                <option value="Website">Website Marketplace</option>
                                <option value="Walk-in">Walk-in / Office</option>
                                <option value="Referral">Referral</option>
                                <option value="Instagram">Instagram</option>
                                <option value="Facebook">Facebook</option>
                                <option value="Billboard">Billboard / Signage</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Priority</label>
                            <select name="priority" class="form-select">
                                <option value="Low">Low</option>
                                <option value="Medium" selected>Medium</option>
                                <option value="High">High</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Pipeline Stage</label>
                            <select name="stage" class="form-select">
                                @foreach($stages as $st)
                                    <option value="{{ $st }}">{{ $st }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Estimated Value ({{ current_currency() }})</label>
                            <input type="number" step="0.01" name="estimated_value" class="form-control" placeholder="500000000">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Assign Sales Agent</label>
                        <select name="assigned_agent_id" class="form-select">
                            <option value="">Unassigned</option>
                            @foreach($agents as $ag)
                                <option value="{{ $ag->id }}">{{ $ag->user?->name }} ({{ $ag->designation }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Property of Interest</label>
                        <select name="property_interest_id" class="form-select">
                            <option value="">General Inquiry / Any</option>
                            @foreach($properties as $pr)
                                <option value="{{ $pr->id }}">{{ $pr->title }} ({{ $pr->formatted_price }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Lead</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
