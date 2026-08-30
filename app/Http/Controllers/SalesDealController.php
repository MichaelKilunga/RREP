<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Customer;
use App\Models\Property;
use App\Models\SalesDeal;
use App\Services\CRM\CRMService;
use App\Services\Finance\FinanceService;
use Illuminate\Http\Request;

class SalesDealController extends Controller
{
    public function __construct(
        protected CRMService $crmService,
        protected FinanceService $financeService
    ) {}

    public function index()
    {
        $deals = SalesDeal::with(['property', 'customer', 'agent.user', 'invoices'])->latest()->paginate(15);
        $properties = Property::whereIn('status', ['Available', 'Reserved'])->get();
        $customers = Customer::all();
        $agents = Agent::with('user')->get();

        return view('deals.index', compact('deals', 'properties', 'customers', 'agents'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'property_id' => 'required|exists:properties,id',
            'property_unit_id' => 'nullable|exists:property_units,id',
            'customer_id' => 'required|exists:customers,id',
            'agent_id' => 'nullable|exists:agents,id',
            'sale_price' => 'required|numeric|min:1',
            'payment_plan_type' => 'required|in:Outright,Installment,Mortgage',
            'total_installments' => 'required|integer|min:1',
            'agreement_date' => 'required|date',
            'closing_date' => 'nullable|date',
            'commission_rate' => 'nullable|numeric',
            'notes' => 'nullable|string',
        ]);

        $commRate = $data['commission_rate'] ?? 5.00;
        $data['commission_amount'] = ($data['sale_price'] * $commRate) / 100;

        $deal = $this->crmService->createSalesDeal($data);

        $deal->property->update(['status' => 'Under Contract']);

        if ($deal->payment_plan_type === 'Installment' && $deal->total_installments > 1) {
            $this->financeService->generateInstallmentPlan($deal, $deal->total_installments, $deal->sale_price, $deal->agreement_date);
        }

        return redirect()->route('deals.show', $deal)->with('success', 'Sales transaction initiated!');
    }

    public function show(SalesDeal $deal)
    {
        $deal->load(['property', 'unit', 'customer', 'agent.user', 'installments', 'invoices.payments', 'commissions']);

        return view('deals.show', compact('deal'));
    }
}
