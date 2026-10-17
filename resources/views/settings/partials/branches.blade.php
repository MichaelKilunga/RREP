{{-- Floating bulk-action bar for branches --}}
<div class="bulk-bar d-none" id="bulkBranchesBar" role="region" aria-live="polite">
    <div class="bulk-inner">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-primary rounded-pill fs-6" id="bulkBranchesCount">0</span>
            <span class="small fw-semibold text-white-50">{{ __('settings.selected_branches') }}</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-sm btn-danger rounded-pill px-3" id="bulkDeleteBranchesBtn">
                <i class="bi bi-trash me-1"></i> {{ __('settings.delete_selected') }}
            </button>
            <button type="button" class="btn btn-sm btn-link text-white-50 p-0 text-decoration-none touch-target" id="bulkCancelBranchesBtn" title="{{ __('settings.deselect_all') }}" aria-label="{{ __('settings.deselect_all') }}">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    </div>
</div>

<div class="row g-3 g-lg-4">
    <div class="col-12">
        <div class="card rounded-4">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="d-flex align-items-center gap-2 min-w-0">
                    <span class="btn-icon bg-primary-subtle text-primary rounded-3"><i class="bi bi-building fs-5"></i></span>
                    <div class="min-w-0">
                        <h5 class="brand-font mb-0">{{ __('settings.branches_title') }} <span class="badge bg-primary rounded-pill ms-1 align-middle">{{ $branches->count() }}</span></h5>
                        <small class="text-muted fw-normal">{{ __('settings.branches_subtitle') }}</small>
                    </div>
                </div>
                <button type="button" class="btn btn-primary rounded-pill px-3 fw-bold shadow-sm w-100 w-sm-auto" data-bs-toggle="modal" data-bs-target="#createBranchModal">
                    <i class="bi bi-plus-circle me-1"></i>{{ __('settings.add_branch') }}
                </button>
            </div>
            <div class="card-body p-0">
                @if($branches->isEmpty())
                    <div class="empty-state">
                        <div class="empty-icon"><i class="bi bi-building"></i></div>
                        <h6>{{ __('settings.no_branches_title') }}</h6>
                        <p class="small mb-3">{{ __('settings.no_branches_help') }}</p>
                        <button type="button" class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#createBranchModal">
                            <i class="bi bi-plus-circle me-1"></i>{{ __('settings.add_branch') }}
                        </button>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover table-stack align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3 text-center" style="width: 40px;">
                                        <input type="checkbox" class="form-check-input" id="selectAllBranchCheckboxes" aria-label="{{ __('settings.select_all') }}">
                                    </th>
                                    <th>{{ __('settings.branch_name') }}</th>
                                    <th>{{ __('settings.branch_code') }}</th>
                                    <th>{{ __('settings.location_contact') }}</th>
                                    <th>{{ __('settings.assigned_records') }}</th>
                                    <th>{{ __('app.status') }}</th>
                                    <th class="text-end pe-3">{{ __('app.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($branches as $br)
                                    <tr id="branch-row-{{ $br->id }}" data-id="{{ $br->id }}">
                                        <td class="ps-3 text-center stack-check">
                                            @if(!$br->is_main)
                                                <input type="checkbox" class="form-check-input branch-checkbox" value="{{ $br->id }}" aria-label="{{ $br->name }}">
                                            @else
                                                <input type="checkbox" class="form-check-input" disabled title="{{ __('settings.hq_no_bulk_delete') }}" aria-label="{{ $br->name }}">
                                            @endif
                                        </td>
                                        <td class="stack-primary">
                                            <div class="fw-bold text-dark d-flex flex-wrap align-items-center gap-2">
                                                <span>{{ $br->name }}</span>
                                                @if($br->is_main)
                                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-semibold">
                                                        <i class="bi bi-star-fill me-1 text-warning"></i>{{ __('settings.headquarters') }}
                                                    </span>
                                                @endif
                                            </div>
                                            @if($br->address)
                                                <small class="text-muted d-block line-clamp-1">{{ $br->address }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border font-monospace px-2 py-1">{{ $br->code }}</span>
                                        </td>
                                        <td>
                                            <div class="small"><i class="bi bi-geo-alt text-muted me-1"></i>{{ $br->city ?: 'Tanzania' }}</div>
                                            @if($br->phone)
                                                <div class="small"><a href="tel:{{ preg_replace('/\D+/', '', $br->phone) }}" class="text-muted text-decoration-none"><i class="bi bi-telephone me-1"></i>{{ format_phone_tz($br->phone) }}</a></div>
                                            @endif
                                            @if($br->email)
                                                <div class="small text-muted text-break"><i class="bi bi-envelope me-1"></i>{{ $br->email }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex flex-wrap gap-1 justify-content-md-start">
                                                <span class="badge bg-light text-dark border" title="{{ __('settings.properties_in_branch') }}">
                                                    <i class="bi bi-houses me-1 text-primary"></i>{{ $br->properties_count ?? 0 }} {{ __('app.properties') }}
                                                </span>
                                                <span class="badge bg-light text-dark border" title="{{ __('settings.staff_in_branch') }}">
                                                    <i class="bi bi-people me-1 text-secondary"></i>{{ $br->users_count ?? 0 }} {{ __('settings.staff') }}
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            @if($br->status === 'Active')
                                                <span class="badge-status-active"><i class="bi bi-check-circle-fill me-1"></i>{{ __('settings.active') }}</span>
                                            @else
                                                <span class="badge-status-inactive"><i class="bi bi-pause-circle me-1"></i>{{ __('settings.inactive') }}</span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-3 stack-actions">
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editBranchModal{{ $br->id }}" title="{{ __('settings.edit_branch') }}">
                                                    <i class="bi bi-pencil-square me-1"></i>{{ __('app.edit') }}
                                                </button>
                                                @if($br->is_main)
                                                    <button type="button" class="btn btn-outline-danger" disabled title="{{ __('settings.hq_cannot_delete') }}" aria-label="{{ __('settings.hq_cannot_delete') }}">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                @else
                                                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteBranchModal{{ $br->id }}" title="{{ __('settings.delete_branch') }}" aria-label="{{ __('settings.delete_branch') }}">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card rounded-4">
            <div class="card-header">
                <h5 class="brand-font mb-0">{{ __('settings.audit_title') }}</h5>
                <small class="text-muted fw-normal">{{ __('settings.audit_subtitle') }}</small>
            </div>
            <div class="card-body p-0">
                @if($auditLogs->isEmpty())
                    <div class="empty-state">
                        <div class="empty-icon"><i class="bi bi-journal-text"></i></div>
                        <h6>{{ __('settings.no_logs_title') }}</h6>
                        <p class="small mb-0">{{ __('settings.no_logs_help') }}</p>
                    </div>
                @else
                    <div class="table-responsive overflow-auto" style="max-height: 420px;">
                        <table class="table table-sm table-hover table-stack mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">{{ __('settings.audit_user') }}</th>
                                    <th>{{ __('settings.audit_event') }}</th>
                                    <th>{{ __('settings.audit_target') }}</th>
                                    <th>{{ __('settings.audit_time') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($auditLogs as $log)
                                    <tr>
                                        <td class="ps-3 stack-primary">{{ $log->user_name ?? ($log->user?->name ?? 'System') }}</td>
                                        <td><span class="badge bg-light text-dark border">{{ $log->event ?: ($log->action ?: 'Activity') }}</span></td>
                                        <td class="small text-muted">{{ class_basename($log->auditable_type ?? '') }} {{ $log->auditable_id ? "#{$log->auditable_id}" : '' }}</td>
                                        <td class="small text-muted">{{ $log->created_at ? $log->created_at->diffForHumans() : __('settings.recently') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
