<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\CRM\CRMService;
use Illuminate\Http\Request;

class CustomerApiController extends Controller
{
    public function __construct(protected CRMService $crmService) {}

    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => Customer::latest()->paginate(20),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'customer_type' => 'required|in:Individual,Corporate',
            'phone' => 'required|string',
            'email' => 'nullable|email',
            'city' => 'nullable|string',
        ]);

        $customer = $this->crmService->createCustomer($data);

        return response()->json([
            'success' => true,
            'data' => $customer,
        ], 201);
    }

    public function show(Customer $customer)
    {
        return response()->json([
            'success' => true,
            'data' => $customer->load(['leads', 'salesDeals', 'invoices']),
        ]);
    }
}
