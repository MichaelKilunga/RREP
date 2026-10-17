@php
    $regions = tz_regions();
    $branchStatuses = ['Active' => __('settings.active'), 'Inactive' => __('settings.inactive')];
@endphp

{{-- Modal: create branch --}}
<div class="modal fade" id="createBranchModal" tabindex="-1" aria-labelledby="createBranchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content">
            <form action="{{ route('settings.branches.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title brand-font text-white mb-0" id="createBranchModalLabel">
                        <i class="bi bi-building-add me-2"></i>{{ __('settings.add_branch_title') }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="{{ __('app.cancel') }}"></button>
                </div>
                <div class="modal-body p-3 p-md-4">
                    @include('settings.partials.branch-form-fields', ['prefix' => 'createBranch', 'branch' => null])
                </div>
                <div class="modal-footer bg-light border-0 d-grid d-sm-flex gap-2">
                    <button type="button" class="btn btn-light border rounded-pill px-4" data-bs-dismiss="modal">{{ __('app.cancel') }}</button>
                    <button type="submit" class="btn btn-primary rounded-pill fw-bold px-4 shadow-sm">
                        <i class="bi bi-check2-circle me-1"></i>{{ __('settings.save_branch') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@foreach($branches as $br)
    @php $hasLinkedRecords = ($br->properties_count ?? 0) > 0 || ($br->users_count ?? 0) > 0; @endphp

    {{-- Modal: edit branch --}}
    <div class="modal fade" id="editBranchModal{{ $br->id }}" tabindex="-1" aria-labelledby="editBranchModalLabel{{ $br->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down">
            <div class="modal-content">
                <form action="{{ route('settings.branches.update', $br) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title brand-font text-white mb-0 text-truncate" id="editBranchModalLabel{{ $br->id }}">
                            <i class="bi bi-pencil-square me-2"></i>{{ __('settings.edit_branch') }}: {{ $br->name }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="{{ __('app.cancel') }}"></button>
                    </div>
                    <div class="modal-body p-3 p-md-4">
                        @include('settings.partials.branch-form-fields', ['prefix' => 'editBranch', 'branch' => $br])
                        @if($hasLinkedRecords)
                            <div class="alert alert-info py-2 px-3 small mb-0 mt-3 d-flex align-items-center gap-2 rounded-3 border-0">
                                <i class="bi bi-info-circle-fill text-info"></i>
                                <span>{{ __('settings.branch_linked_records', ['properties' => $br->properties_count ?? 0, 'staff' => $br->users_count ?? 0]) }}</span>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer bg-light border-0 d-grid d-sm-flex gap-2">
                        <button type="button" class="btn btn-light border rounded-pill px-4" data-bs-dismiss="modal">{{ __('app.cancel') }}</button>
                        <button type="submit" class="btn btn-primary rounded-pill fw-bold px-4 shadow-sm">
                            <i class="bi bi-check2-circle me-1"></i>{{ __('settings.update_branch') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if(!$br->is_main)
        {{-- Modal: delete branch --}}
        <div class="modal fade" id="deleteBranchModal{{ $br->id }}" tabindex="-1" aria-labelledby="deleteBranchModalLabel{{ $br->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <form action="{{ route('settings.branches.destroy', $br) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title brand-font text-white" id="deleteBranchModalLabel{{ $br->id }}">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ __('settings.delete_branch') }}
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="{{ __('app.cancel') }}"></button>
                        </div>
                        <div class="modal-body p-3 p-md-4">
                            <p class="mb-3">{{ __('settings.delete_branch_question', ['name' => $br->name, 'code' => $br->code]) }}</p>
                            @if($hasLinkedRecords)
                                <div class="alert alert-danger small mb-0">
                                    <i class="bi bi-shield-x me-1"></i>
                                    <strong>{{ __('settings.deletion_blocked') }}:</strong>
                                    {{ __('settings.deletion_blocked_help', ['properties' => $br->properties_count ?? 0, 'staff' => $br->users_count ?? 0]) }}
                                </div>
                            @else
                                <div class="alert alert-warning small mb-0">
                                    <i class="bi bi-info-circle me-1"></i>{{ __('settings.soft_delete_notice') }}
                                </div>
                            @endif
                        </div>
                        <div class="modal-footer bg-light d-grid d-sm-flex gap-2">
                            <button type="button" class="btn btn-light border rounded-pill px-4" data-bs-dismiss="modal">{{ __('app.cancel') }}</button>
                            @if(!$hasLinkedRecords)
                                <button type="submit" class="btn btn-danger fw-bold rounded-pill px-4">
                                    <i class="bi bi-trash me-1"></i>{{ __('settings.confirm_delete') }}
                                </button>
                            @else
                                <button type="button" class="btn btn-danger fw-bold rounded-pill px-4" disabled>{{ __('settings.cannot_delete_branch') }}</button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endforeach
