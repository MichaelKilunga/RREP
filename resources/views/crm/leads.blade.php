@extends('layouts.app')

@section('title', __('app.leads'))

@section('styles')
<style>
    .kanban-board { align-items: flex-start; padding-bottom: 0.75rem; }
    .kanban-col { flex: 0 0 85vw; max-width: 320px; }
    @media (min-width: 576px) { .kanban-col { flex-basis: 300px; } }
</style>
@endsection

@section('content')
@php
    $stageTone = [
        'New' => 'primary', 'Contacted' => 'info', 'Qualified' => 'success', 'Viewing' => 'warning',
        'Proposal' => 'secondary', 'Negotiation' => 'dark', 'Won' => 'success', 'Lost' => 'danger',
    ];
@endphp

<div class="page-header">
    <div class="min-w-0">
        <h1 class="brand-font mb-1">{{ __('ops.leads_title') }}</h1>
        <p class="text-muted small mb-0">{{ __('ops.leads_subtitle') }}</p>
    </div>
    <div class="page-actions">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newLeadModal">
            <i class="bi bi-plus-lg me-1"></i> {{ __('ops.create_lead') }}
        </button>
    </div>
</div>

<p class="text-muted small d-md-none mb-2"><i class="bi bi-arrow-left-right me-1"></i>{{ __('ops.swipe_to_see_stages') }}</p>

<!-- Kanban Columns -->
<div class="scroll-x kanban-board">
    @foreach($stages as $stage)
        @php
            $stageLeads = $leads->where('stage', $stage);
        @endphp
        <div class="kanban-col">
            <div class="card bg-light border-0 shadow-none p-2">
                <div class="d-flex justify-content-between align-items-center p-2 mb-2">
                    <span class="fw-bold brand-font small text-truncate">{{ __('ops.stage_'.strtolower($stage)) }}</span>
                    <span class="badge bg-white text-dark border">{{ $stageLeads->count() }}</span>
                </div>

                <div class="d-flex flex-column gap-2 kanban-column" data-stage="{{ $stage }}">
                    @forelse($stageLeads as $lead)
                        @php
                            $leadPhone = $lead->customer?->phone;
                            $leadDigits = preg_replace('/\D+/', '', (string) $leadPhone);
                            if (str_starts_with($leadDigits, '0')) {
                                $leadDigits = '255'.substr($leadDigits, 1);
                            }
                        @endphp
                        <div class="card p-2.5 shadow-xs border bg-white lead-card" data-lead-id="{{ $lead->id }}">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                <span class="badge bg-primary-subtle text-primary text-truncate">{{ $lead->source }}</span>
                                <span class="badge bg-{{ $lead->priority == 'High' ? 'danger' : 'secondary' }}-subtle text-{{ $lead->priority == 'High' ? 'danger' : 'secondary' }}">{{ __('ops.priority_'.strtolower($lead->priority)) }}</span>
                            </div>
                            <h6 class="fw-bold mb-1 small text-truncate">{{ $lead->customer?->full_name }}</h6>
                            <p class="text-muted small mb-2 line-clamp-2">{{ $lead->title }}</p>
                            <p class="text-muted mb-2" style="font-size: 0.7rem;"><i class="bi bi-clock me-1"></i>{{ $lead->created_at->format('d M Y, H:i') }} ({{ $lead->created_at->diffForHumans() }})</p>

                            @if($lead->estimated_value)
                                <div class="fw-bold text-success small mb-2">{{ format_money_short($lead->estimated_value) }}</div>
                            @endif

                            <div class="d-flex align-items-center gap-1 mb-2">
                                @if($leadDigits)
                                    <a href="https://wa.me/{{ $leadDigits }}" target="_blank" rel="noopener" class="btn btn-whatsapp btn-sm btn-icon" title="WhatsApp" aria-label="WhatsApp {{ $lead->customer?->full_name }}"><i class="bi bi-whatsapp"></i></a>
                                    <a href="tel:{{ $leadPhone }}" class="btn btn-light border btn-sm btn-icon" title="{{ __('ops.call') }}" aria-label="{{ __('ops.call') }}"><i class="bi bi-telephone"></i></a>
                                @endif
                                <button type="button" class="btn btn-light border btn-sm btn-icon" data-bs-toggle="modal" data-bs-target="#activityModal{{ $lead->id }}" title="{{ __('ops.log_activity') }}" aria-label="{{ __('ops.log_activity') }}">
                                    <i class="bi bi-chat-left-text text-primary"></i>
                                </button>
                            </div>

                            <label for="stageSelect{{ $lead->id }}" class="visually-hidden">{{ __('ops.move_to_stage') }}</label>
                            <select id="stageSelect{{ $lead->id }}" class="form-select form-select-sm lead-stage-select" data-url="{{ route('crm.leads.update_stage', $lead) }}" aria-label="{{ __('ops.move_to_stage') }}">
                                @foreach($stages as $st)
                                    <option value="{{ $st }}" @selected($st === $lead->stage)>{{ __('ops.stage_'.strtolower($st)) }}</option>
                                @endforeach
                            </select>

                            <div class="border-top pt-2 mt-2 small text-muted text-truncate">
                                <i class="bi bi-person me-1"></i>{{ $lead->agent?->user?->name ?? __('ops.unassigned') }}
                            </div>
                        </div>

                        <!-- Activity Modal -->
                        <div class="modal fade" id="activityModal{{ $lead->id }}" tabindex="-1" aria-labelledby="activityModalLabel{{ $lead->id }}">
                            <div class="modal-dialog modal-dialog-scrollable modal-fullscreen-sm-down">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h6 class="modal-title fw-bold" id="activityModalLabel{{ $lead->id }}">{{ __('ops.log_activity') }}: {{ $lead->customer?->full_name }}</h6>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('app.cancel') }}"></button>
                                    </div>
                                    <form action="{{ route('crm.leads.log_activity', $lead) }}" method="POST">
                                        @csrf
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label for="activityType{{ $lead->id }}" class="form-label small fw-semibold">{{ __('ops.activity_type') }}</label>
                                                <select id="activityType{{ $lead->id }}" name="activity_type" class="form-select" required>
                                                    <option value="Call">{{ __('ops.activity_call') }}</option>
                                                    <option value="WhatsApp">{{ __('ops.activity_whatsapp') }}</option>
                                                    <option value="Site Visit">{{ __('ops.activity_site_visit') }}</option>
                                                    <option value="Meeting">{{ __('ops.activity_meeting') }}</option>
                                                    <option value="SMS">{{ __('ops.activity_sms') }}</option>
                                                    <option value="Email">{{ __('ops.activity_email') }}</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label for="activitySummary{{ $lead->id }}" class="form-label small fw-semibold">{{ __('ops.summary_outcome') }}</label>
                                                <input type="text" id="activitySummary{{ $lead->id }}" name="summary" class="form-control" placeholder="{{ __('ops.summary_placeholder') }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="activityDetails{{ $lead->id }}" class="form-label small fw-semibold">{{ __('ops.detailed_notes') }}</label>
                                                <textarea id="activityDetails{{ $lead->id }}" name="details" rows="3" class="form-control"></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-light border" data-bs-dismiss="modal">{{ __('app.cancel') }}</button>
                                            <button type="submit" class="btn btn-primary">{{ __('ops.save_activity') }}</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state py-3 border rounded border-dashed bg-white small">
                            <div class="empty-icon" style="width: 40px; height: 40px; font-size: 1rem;"><i class="bi bi-inbox"></i></div>
                            <div>{{ __('ops.no_leads_in_stage') }}</div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    @endforeach
</div>

<!-- New Lead Modal -->
<div class="modal fade" id="newLeadModal" tabindex="-1" aria-labelledby="newLeadModalLabel">
    <div class="modal-dialog modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title brand-font" id="newLeadModalLabel">{{ __('ops.create_lead_title') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('app.cancel') }}"></button>
            </div>
            <form action="{{ route('crm.leads.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="leadCustomer" class="form-label small fw-semibold">{{ __('ops.customer') }} *</label>
                        <select id="leadCustomer" name="customer_id" class="form-select select2 w-100" required>
                            <option value="">{{ __('ops.select_existing_customer') }}</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->full_name }} ({{ format_phone_tz($c->phone) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="leadTitle" class="form-label small fw-semibold">{{ __('ops.lead_title') }} *</label>
                        <input type="text" id="leadTitle" name="title" class="form-control" placeholder="{{ __('ops.lead_title_placeholder') }}" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-12 col-sm-6">
                            <label for="leadSource" class="form-label small fw-semibold">{{ __('ops.source') }}</label>
                            <select id="leadSource" name="source" class="form-select">
                                <option value="WhatsApp">WhatsApp</option>
                                <option value="Website">{{ __('ops.source_website') }}</option>
                                <option value="Facebook">Facebook</option>
                                <option value="Instagram">Instagram</option>
                                <option value="Walk-in">{{ __('ops.source_walk_in') }}</option>
                                <option value="Referral">{{ __('ops.source_referral') }}</option>
                                <option value="Property Portal">{{ __('ops.source_property_portal') }}</option>
                                <option value="Billboard">{{ __('ops.source_billboard') }}</option>
                                <option value="Google">Google</option>
                            </select>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label for="leadPriority" class="form-label small fw-semibold">{{ __('ops.priority') }}</label>
                            <select id="leadPriority" name="priority" class="form-select">
                                <option value="Low">{{ __('ops.priority_low') }}</option>
                                <option value="Medium" selected>{{ __('ops.priority_medium') }}</option>
                                <option value="High">{{ __('ops.priority_high') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-12 col-sm-6">
                            <label for="leadStage" class="form-label small fw-semibold">{{ __('ops.pipeline_stage') }}</label>
                            <select id="leadStage" name="stage" class="form-select">
                                @foreach($stages as $st)
                                    <option value="{{ $st }}">{{ __('ops.stage_'.strtolower($st)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label for="leadValue" class="form-label small fw-semibold">{{ __('ops.estimated_value') }}</label>
                            <div class="input-group">
                                <span class="input-group-text">TSh</span>
                                <input type="number" step="0.01" min="0" inputmode="decimal" id="leadValue" name="estimated_value" class="form-control" placeholder="500000000">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="leadAgent" class="form-label small fw-semibold">{{ __('ops.assign_agent') }}</label>
                        <select id="leadAgent" name="assigned_agent_id" class="form-select">
                            <option value="">{{ __('ops.unassigned') }}</option>
                            @foreach($agents as $ag)
                                <option value="{{ $ag->id }}">{{ $ag->user?->name }} ({{ $ag->designation }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="leadProperty" class="form-label small fw-semibold">{{ __('ops.property_of_interest') }}</label>
                        <select id="leadProperty" name="property_interest_id" class="form-select">
                            <option value="">{{ __('ops.general_inquiry') }}</option>
                            @foreach($properties as $pr)
                                <option value="{{ $pr->id }}">{{ $pr->title }} ({{ format_money_short($pr->price) }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">{{ __('app.cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('ops.create_lead') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const movedText = @json(__('ops.lead_moved'));
    const errorText = @json(__('ops.lead_move_failed'));

    document.querySelectorAll('.lead-stage-select').forEach(function (select) {
        select.addEventListener('change', function () {
            select.disabled = true;
            fetch(select.dataset.url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
                body: JSON.stringify({ stage: select.value })
            })
            .then(function (res) { if (!res.ok) { throw new Error('failed'); } return res.json(); })
            .then(function () {
                if (window.RREP && window.RREP.toast) { window.RREP.toast(movedText); }
                window.setTimeout(function () { window.location.reload(); }, 600);
            })
            .catch(function () {
                select.disabled = false;
                if (window.RREP && window.RREP.toast) { window.RREP.toast(errorText, 'error'); } else { window.alert(errorText); }
            });
        });
    });
});
</script>
@endsection
