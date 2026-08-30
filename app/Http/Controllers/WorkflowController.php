<?php

namespace App\Http\Controllers;

use App\Models\ApprovalLog;
use App\Models\WorkflowApproval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkflowController extends Controller
{
    public function index()
    {
        $pendingApprovals = WorkflowApproval::with(['requester', 'logs.user'])->latest()->get();

        return view('workflows.index', compact('pendingApprovals'));
    }

    public function approve(Request $request, WorkflowApproval $approval)
    {
        $approval->update(['status' => 'Approved']);

        ApprovalLog::create([
            'workflow_approval_id' => $approval->id,
            'user_id' => Auth::id() ?? 1,
            'action' => 'Approved',
            'comments' => $request->input('comments', 'Approved by authority.'),
        ]);

        return back()->with('success', 'Workflow approval granted successfully.');
    }

    public function reject(Request $request, WorkflowApproval $approval)
    {
        $approval->update(['status' => 'Rejected']);

        ApprovalLog::create([
            'workflow_approval_id' => $approval->id,
            'user_id' => Auth::id() ?? 1,
            'action' => 'Rejected',
            'comments' => $request->input('comments', 'Declined by authority.'),
        ]);

        return back()->with('info', 'Workflow request rejected.');
    }
}
