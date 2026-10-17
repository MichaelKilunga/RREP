@extends('layouts.app')

@section('title', __('app.workflows'))

@section('content')
@php
    $approvalBadge = ['Pending' => 'pending', 'Approved' => 'active', 'Rejected' => 'rejected', 'Completed' => 'paid', 'Cancelled' => 'cancelled'];
    $pendingCount = $pendingApprovals->where('status', 'Pending')->count();
@endphp

<div class="page-header">
    <div class="min-w-0">
        <h1 class="brand-font mb-1"><i class="bi bi-diagram-3 text-primary me-2"></i>{{ __('ops.workflows_title') }}</h1>
        <p class="text-muted small mb-0">{{ __('ops.workflows_subtitle') }}</p>
    </div>
    <div class="page-actions">
        <span class="badge badge-status-pending fs-6 align-self-center">{{ __('ops.pending_count', ['count' => $pendingCount]) }}</span>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 table-stack small">
            <thead class="table-light">
                <tr>
                    <th>{{ __('ops.request_no') }}</th>
                    <th>{{ __('ops.workflow_type') }}</th>
                    <th>{{ __('ops.requested_by') }}</th>
                    <th>{{ __('ops.target_subject') }}</th>
                    <th>{{ __('ops.step') }}</th>
                    <th>{{ __('app.status') }}</th>
                    <th class="text-end">{{ __('app.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pendingApprovals as $appr)
                    <tr>
                        <td class="stack-primary"><span class="fw-bold text-primary">WF-{{ $appr->id }}</span> &bull; {{ $appr->workflow_type }}</td>
                        <td class="d-none d-md-table-cell"><span class="badge bg-light text-dark border">{{ $appr->workflow_type }}</span></td>
                        <td>{{ $appr->requester?->name ?? '-' }}</td>
                        <td>{{ class_basename($appr->approvable_type) }} #{{ $appr->approvable_id }}</td>
                        <td>{{ __('ops.step_of', ['step' => $appr->step_number, 'total' => $appr->total_steps]) }}</td>
                        <td><span class="badge badge-status-{{ $approvalBadge[$appr->status] ?? strtolower((string) $appr->status) }}">{{ $appr->status }}</span></td>
                        <td class="stack-actions text-end">
                            @if($appr->status === 'Pending')
                                <div class="d-inline-flex gap-1">
                                    <form action="{{ route('workflows.approve', $appr) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success"><i class="bi bi-check-lg me-1"></i>{{ __('ops.approve') }}</button>
                                    </form>
                                    <form action="{{ route('workflows.reject', $appr) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('ops.confirm_reject') }}')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-x-lg me-1"></i>{{ __('ops.reject') }}</button>
                                    </form>
                                </div>
                            @else
                                <span class="text-muted small">{{ __('ops.completed') }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="stack-no-label">
                            <div class="empty-state">
                                <div class="empty-icon"><i class="bi bi-check2-all"></i></div>
                                <h6>{{ __('ops.no_approvals') }}</h6>
                                <p class="small mb-0">{{ __('ops.no_approvals_hint') }}</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
