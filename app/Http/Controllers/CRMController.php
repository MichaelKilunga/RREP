<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\Property;
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
            'address' => 'nullable|string',
            'city' => 'nullable|string',
        ]);

        $this->crmService->createCustomer($data);

        return back()->with('success', 'Customer profile created successfully!');
    }
}
