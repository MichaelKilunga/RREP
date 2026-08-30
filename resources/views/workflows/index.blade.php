@extends('layouts.app')

@section('title', __('app.workflows'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="brand-font mb-1"><i class="bi bi-diagram-3 text-primary me-2"></i>{{ __('app.workflows') }} & Multi-Step Approvals (BM-014)</h3>
        <p class="text-muted small mb-0">Authorize discount authorizations, high-value expense requests, and survey sign-offs</p>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
            <thead class="table-light">
                <tr>
                    <th>Request #</th>
                    <th>Workflow Type</th>
                    <th>Requested By</th>
                    <th>Target Subject</th>
                    <th>Step</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pendingApprovals as $appr)
                    <tr>
                        <td class="fw-bold text-primary">WF-{{ $appr->id }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $appr->workflow_type }}</span></td>
                        <td>{{ $appr->requester?->name }}</td>
                        <td>{{ class_basename($appr->approvable_type) }} #{{ $appr->approvable_id }}</td>
                        <td>Step {{ $appr->step_number }} of {{ $appr->total_steps }}</td>
                        <td><span class="badge bg-warning-subtle text-warning-emphasis">{{ $appr->status }}</span></td>
                        <td class="text-end">
                            @if($appr->status === 'Pending')
                                <form action="{{ route('workflows.approve', $appr) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success"><i class="bi bi-check-lg me-1"></i> Approve</button>
                                </form>
                                <form action="{{ route('workflows.reject', $appr) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-x-lg me-1"></i> Reject</button>
                                </form>
                            @else
                                <span class="text-muted small">Completed</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No pending workflow approval requests in the queue.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
