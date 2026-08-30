<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Property;
use App\Models\SalesDeal;
use App\Services\Finance\FinanceService;
use Illuminate\Http\Request;

class FinanceController extends Controller
{
    public function __construct(protected FinanceService $financeService) {}

    public function invoices()
    {
        $invoices = Invoice::with(['customer', 'property', 'salesDeal', 'payments'])->latest()->paginate(15);
        $customers = Customer::all();
        $properties = Property::all();
        $deals = SalesDeal::all();

        return view('finance.invoices', compact('invoices', 'customers', 'properties', 'deals'));
    }

    public function storeInvoice(Request $request)
    {
        $data = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'sales_deal_id' => 'nullable|exists:sales_deals,id',
            'property_id' => 'nullable|exists:properties,id',
            'issue_date' => 'required|date',
            'due_date' => 'required|date',
            'tax_rate' => 'nullable|numeric',
            'discount_amount' => 'nullable|numeric',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $this->financeService->createInvoice($data, $request->input('items', []));

        return back()->with('success', 'Invoice generated successfully!');
    }

    public function showInvoice(Invoice $invoice)
    {
        $invoice->load(['customer', 'property', 'salesDeal', 'items', 'payments', 'organization.branding']);

        return view('finance.show_invoice', compact('invoice'));
    }

    public function recordPayment(Request $request)
    {
        $data = $request->validate([
            'invoice_id' => 'nullable|exists:invoices,id',
            'customer_id' => 'required|exists:customers,id',
            'amount' => 'required|numeric|min:1',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string',
            'reference_number' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $this->financeService->recordPayment($data);

        return back()->with('success', 'Payment receipt recorded successfully!');
    }

    public function expenses()
    {
        $expenses = Expense::with(['property', 'approver', 'receipt'])->latest('expense_date')->paginate(15);
        $properties = Property::all();

        return view('finance.expenses', compact('expenses', 'properties'));
    }

    public function storeExpense(Request $request)
    {
        $data = $request->validate([
            'property_id' => 'nullable|exists:properties,id',
            'category' => 'required|string',
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:1',
            'expense_date' => 'required|date',
            'payee' => 'nullable|string',
            'payment_method' => 'required|string',
        ]);

        $this->financeService->createExpense($data);

        return back()->with('success', 'Expense recorded!');
    }
}
