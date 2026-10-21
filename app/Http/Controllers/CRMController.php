<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\Property;
use App\Models\PropertyInquiry;
use App\Services\CRM\CRMService;
use Illuminate\Http\Request;

class CRMController extends Controller
{
    public function __construct(protected CRMService $crmService) {}

    public function leads(Request $request)
    {
        $leads = Lead::with(['customer', 'agent.user', 'property'])->latest()->get();
        $stages = ['New', 'Contacted', 'Qualified', 'Viewing', 'Proposal', 'Negotiation', 'Won', 'Lost'];
        $agents = Agent::with('user')->where('status', 'Active')->get();
        $properties = Property::all();
        $customers = Customer::all();

        return view('crm.leads', compact('leads', 'stages', 'agents', 'properties', 'customers'));
    }

    public function storeLead(Request $request)
    {
        $data = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'title' => 'required|string|max:255',
            'source' => 'required|string',
            'stage' => 'required|string',
            'priority' => 'required|string',
            'estimated_value' => 'nullable|numeric',
            'assigned_agent_id' => 'nullable|exists:agents,id',
            'property_interest_id' => 'nullable|exists:properties,id',
            'next_followup_at' => 'nullable|date',
        ]);

        $this->crmService->createLead($data);

        return back()->with('success', 'Lead created successfully!');
    }

    public function updateLead(Request $request, Lead $lead)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'source' => 'required|string',
            'stage' => 'required|string',
            'priority' => 'required|string',
            'estimated_value' => 'nullable|numeric',
            'assigned_agent_id' => 'nullable|exists:agents,id',
        ]);

        $lead->update($data);

        return back()->with('success', __('ops.lead_updated'));
    }

    public function destroyLead(Lead $lead)
    {
        $lead->delete();

        return back()->with('success', __('ops.lead_deleted'));
    }

    public function bulkLeads(Request $request)
    {
        $data = $request->validate([
            'action' => 'required|in:delete,move',
            'stage' => 'required_if:action,move|nullable|string',
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:leads,id',
        ]);

        $leads = Lead::whereIn('id', $data['ids']);

        if ($data['action'] === 'delete') {
            $count = $leads->count();
            $leads->get()->each->delete();

            return back()->with('success', __('ops.leads_bulk_deleted', ['count' => $count]));
        }

        $leads->update(['stage' => $data['stage']]);

        return back()->with('success', __('ops.leads_bulk_moved', ['count' => count($data['ids'])]));
    }

    public function updateLeadStage(Request $request, Lead $lead)
    {
        $request->validate([
            'stage' => 'required|string',
        ]);

        $lead->update(['stage' => $request->stage]);

        return response()->json(['success' => true, 'message' => 'Lead stage updated!']);
    }

    public function logActivity(Request $request, Lead $lead)
    {
        $data = $request->validate([
            'activity_type' => 'required|string',
            'summary' => 'required|string',
            'details' => 'nullable|string',
        ]);

        $this->crmService->logActivity($lead, $data);

        return back()->with('success', 'Activity logged successfully!');
    }

    public function customers()
    {
        $customers = Customer::withCount(['leads', 'salesDeals', 'invoices'])->latest()->paginate(15);

        return view('crm.customers', compact('customers'));
    }

    public function storeCustomer(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'company_name' => 'nullable|string',
            'customer_type' => 'required|in:Individual,Corporate',
            'email' => 'nullable|email',
            'phone' => 'required|string',
            'alt_phone' => 'nullable|string',
            'national_id_passport' => 'nullable|string',
            'tax_number' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
        ]);

        $this->crmService->createCustomer($data);

        return back()->with('success', 'Customer profile created successfully!');
    }

    public function requests()
    {
        $typeCounts = PropertyInquiry::selectRaw('inquiry_type, count(*) as total')
            ->groupBy('inquiry_type')
            ->orderByDesc('total')
            ->pluck('total', 'inquiry_type');

        $requests = PropertyInquiry::with(['property', 'project'])
            ->when(request('type'), fn ($q, $type) => $q->where('inquiry_type', $type))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('crm.requests', compact('requests', 'typeCounts'));
    }

    public function updateRequestStatus(Request $request, PropertyInquiry $inquiry)
    {
        $request->validate(['status' => 'required|in:New,Contacted,Resolved,Closed']);

        $inquiry->update(['status' => $request->status]);

        return back()->with('success', __('ops.request_status_updated'));
    }
}
