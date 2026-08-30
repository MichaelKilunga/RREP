<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\Property;
use App\Services\CRM\CRMService;
use Illuminate\Http\Request;

class LeadApiController extends Controller
{
    public function __construct(protected CRMService $crmService) {}

    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => Lead::with(['customer', 'agent.user', 'property'])->latest()->paginate(20),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'title' => 'required|string',
            'source' => 'required|string',
            'stage' => 'required|string',
            'priority' => 'required|string',
            'estimated_value' => 'nullable|numeric',
        ]);

        $lead = $this->crmService->createLead($data);

        return response()->json([
            'success' => true,
            'data' => $lead,
        ], 201);
    }

    public function storePublicInquiry(Request $request)
    {
        $request->validate([
            'property_id' => 'required|exists:properties,id',
            'name' => 'required|string',
            'phone' => 'required|string',
            'email' => 'nullable|email',
            'message' => 'required|string',
        ]);

        $nameParts = explode(' ', $request->name, 2);
        $customer = Customer::firstOrCreate(
            ['phone' => $request->phone],
            [
                'first_name' => $nameParts[0],
                'last_name' => $nameParts[1] ?? '',
                'email' => $request->email,
                'source' => 'API Marketplace',
            ]
        );

        $property = Property::find($request->property_id);

        $lead = Lead::create([
            'customer_id' => $customer->id,
            'property_interest_id' => $property->id,
            'organization_id' => $property->organization_id,
            'branch_id' => $property->branch_id,
            'title' => "API Inquiry for {$property->title}",
            'source' => 'REST API',
            'stage' => 'New',
            'priority' => 'High',
            'estimated_value' => $property->price ?: $property->rent_price,
            'lost_reason' => $request->message,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Inquiry registered successfully.',
            'data' => $lead,
        ], 201);
    }
}
