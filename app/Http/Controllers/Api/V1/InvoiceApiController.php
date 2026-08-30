<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\Finance\FinanceService;
use Illuminate\Http\Request;

class InvoiceApiController extends Controller
{
    public function __construct(protected FinanceService $financeService) {}

    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => Invoice::with(['customer', 'property', 'items', 'payments'])->latest()->paginate(20),
        ]);
    }

    public function show(Invoice $invoice)
    {
        return response()->json([
            'success' => true,
            'data' => $invoice->load(['customer', 'property', 'items', 'payments']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'sales_deal_id' => 'nullable|exists:sales_deals,id',
            'property_id' => 'nullable|exists:properties,id',
            'issue_date' => 'required|date',
            'due_date' => 'required|date',
            'items' => 'required|array|min:1',
        ]);

        $invoice = $this->financeService->createInvoice($data, $request->input('items', []));

        return response()->json([
            'success' => true,
            'data' => $invoice,
        ], 201);
    }
}
