<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Property;
use App\Models\SalesDeal;
use App\Models\TaxonomyTerm;
use App\Services\Finance\FinanceService;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class FinanceController extends Controller
{
    public function __construct(protected FinanceService $financeService) {}

    /* --------------------------------------------------------------- Overview */

    public function overview()
    {
        $monthStart = now()->startOfMonth()->toDateString();
        $open = Invoice::whereNotIn('status', ['Paid', 'Cancelled'])->where('balance_due', '>', 0);

        $kpis = [
            'invoiced_month' => (float) Invoice::where('status', '!=', 'Cancelled')->where('issue_date', '>=', $monthStart)->sum('total_amount'),
            'collected_month' => (float) Payment::where('status', 'Completed')->where('payment_date', '>=', $monthStart)->sum('amount'),
            'expenses_month' => (float) Expense::where('expense_date', '>=', $monthStart)->sum('amount'),
            'outstanding' => (float) (clone $open)->sum('balance_due'),
            'overdue' => (float) (clone $open)->whereDate('due_date', '<', now()->toDateString())->sum('balance_due'),
            'overdue_count' => (clone $open)->whereDate('due_date', '<', now()->toDateString())->count(),
        ];
        $kpis['net_month'] = $kpis['collected_month'] - $kpis['expenses_month'];

        // Six-month cash picture: collections vs expenses per month.
        $months = collect(range(5, 0))->map(fn ($i) => now()->startOfMonth()->subMonths($i));
        $series = $months->map(function (Carbon $m) {
            $from = $m->toDateString();
            $to = $m->copy()->endOfMonth()->toDateString();

            return [
                'label' => $m->translatedFormat('M Y'),
                'collected' => (float) Payment::where('status', 'Completed')->whereBetween('payment_date', [$from, $to])->sum('amount'),
                'expenses' => (float) Expense::whereBetween('expense_date', [$from, $to])->sum('amount'),
            ];
        })->values();

        // Receivables aging by days past due.
        $aging = ['current' => 0.0, 'd1_30' => 0.0, 'd31_60' => 0.0, 'd61_90' => 0.0, 'd90' => 0.0];
        foreach ((clone $open)->get(['due_date', 'balance_due']) as $inv) {
            $days = $inv->due_date ? (int) $inv->due_date->diffInDays(now()->startOfDay(), false) : 0;
            $bucket = match (true) {
                $days <= 0 => 'current',
                $days <= 30 => 'd1_30',
                $days <= 60 => 'd31_60',
                $days <= 90 => 'd61_90',
                default => 'd90',
            };
            $aging[$bucket] += (float) $inv->balance_due;
        }

        $openInvoiceScope = fn ($q) => $q->whereNotIn('status', ['Paid', 'Cancelled'])->where('balance_due', '>', 0);
        $topDebtors = Customer::whereHas('invoices', $openInvoiceScope)
            ->withSum(['invoices as balance' => $openInvoiceScope], 'balance_due')
            ->orderByDesc('balance')
            ->take(6)
            ->get();
        $recentPayments = Payment::with(['customer', 'invoice'])->where('status', 'Completed')->latest('payment_date')->latest()->take(8)->get();

        return view('finance.overview', compact('kpis', 'series', 'aging', 'topDebtors', 'recentPayments'));
    }

    /* --------------------------------------------------------------- Invoices */

    public function invoices(Request $request)
    {
        $invoices = Invoice::with(['customer', 'property', 'salesDeal', 'payments'])
            ->when($request->filled('status'), function ($q) use ($request) {
                if ($request->status === 'overdue') {
                    $q->whereNotIn('status', ['Paid', 'Cancelled'])->where('balance_due', '>', 0)->whereDate('due_date', '<', now()->toDateString());
                } elseif ($request->status === 'open') {
                    $q->whereNotIn('status', ['Paid', 'Cancelled'])->where('balance_due', '>', 0);
                } else {
                    $q->where('status', $request->status);
                }
            })
            ->when($request->filled('customer'), fn ($q) => $q->where('customer_id', $request->customer))
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w->where('invoice_number', 'like', "%{$request->q}%")->orWhereHas('customer', fn ($c) => $c->where('first_name', 'like', "%{$request->q}%")->orWhere('last_name', 'like', "%{$request->q}%")->orWhere('phone', 'like', "%{$request->q}%"))))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('issue_date', '>=', $request->from))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('issue_date', '<=', $request->to))
            ->latest('issue_date')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $open = Invoice::whereNotIn('status', ['Paid', 'Cancelled'])->where('balance_due', '>', 0);
        $stats = [
            'outstanding' => (float) (clone $open)->sum('balance_due'),
            'overdue' => (float) (clone $open)->whereDate('due_date', '<', now()->toDateString())->sum('balance_due'),
            'paid_month' => (float) Payment::where('status', 'Completed')->where('payment_date', '>=', now()->startOfMonth()->toDateString())->sum('amount'),
            'open_count' => (clone $open)->count(),
        ];

        $customers = Customer::orderBy('first_name')->get(['id', 'first_name', 'last_name', 'phone']);
        $properties = Property::all();
        $deals = SalesDeal::all();
        $openInvoices = Invoice::whereNotIn('status', ['Paid', 'Cancelled'])->where('balance_due', '>', 0)->get(['id', 'customer_id', 'invoice_number', 'balance_due']);

        return view('finance.invoices', compact('invoices', 'customers', 'properties', 'deals', 'openInvoices', 'stats'));
    }

    public function storeInvoice(Request $request)
    {
        $data = $this->invoiceData($request);
        $invoice = $this->financeService->createInvoice($data, $data['items']);

        return redirect()->route('finance.invoices.show', $invoice)->with('success', __('ops.invoice_created', ['number' => $invoice->invoice_number]));
    }

    public function showInvoice(Invoice $invoice)
    {
        $invoice->load(['customer', 'property', 'salesDeal', 'items', 'payments.recorder', 'organization.branding']);

        return view('finance.show_invoice', compact('invoice'));
    }

    public function editInvoice(Invoice $invoice)
    {
        abort_if(in_array($invoice->status, ['Paid', 'Cancelled'], true), 422, __('ops.invoice_locked'));
        $invoice->load('items');
        $customers = Customer::orderBy('first_name')->get(['id', 'first_name', 'last_name', 'phone']);
        $properties = Property::all();

        return view('finance.edit_invoice', compact('invoice', 'customers', 'properties'));
    }

    public function updateInvoice(Request $request, Invoice $invoice)
    {
        abort_if(in_array($invoice->status, ['Paid', 'Cancelled'], true), 422, __('ops.invoice_locked'));
        $data = $this->invoiceData($request);

        $invoice->update(collect($data)->except('items')->all());
        $invoice->items()->delete();
        foreach ($data['items'] as $item) {
            $invoice->items()->create($item + ['total_amount' => $item['quantity'] * $item['unit_price']]);
        }
        $invoice->refresh()->recalculateTotals();

        return redirect()->route('finance.invoices.show', $invoice)->with('success', __('ops.invoice_updated'));
    }

    public function cancelInvoice(Invoice $invoice)
    {
        if ($invoice->payments()->where('status', 'Completed')->exists()) {
            return back()->with('error', __('ops.invoice_has_payments'));
        }

        $invoice->update(['status' => 'Cancelled', 'balance_due' => 0]);

        return back()->with('success', __('ops.invoice_cancelled'));
    }

    public function destroyInvoice(Invoice $invoice)
    {
        if ($invoice->payments()->where('status', 'Completed')->exists()) {
            return back()->with('error', __('ops.invoice_has_payments'));
        }

        $number = $invoice->invoice_number;
        $invoice->delete();

        return redirect()->route('finance.invoices')->with('success', __('ops.invoice_deleted', ['number' => $number]));
    }

    public function remindInvoice(Invoice $invoice)
    {
        $invoice->load('customer');
        if (! $invoice->customer?->phone || (float) $invoice->balance_due <= 0) {
            return back()->with('error', __('ops.invoice_cannot_remind'));
        }

        $message = __('ops.invoice_reminder_sms', [
            'name' => $invoice->customer->first_name,
            'number' => $invoice->invoice_number,
            'amount' => format_currency($invoice->balance_due, $invoice->currency, 0),
            'date' => $invoice->due_date?->format('d/m/Y'),
            'company' => setting('company_name', 'Avenix'),
        ], 'sw');

        $sent = SmsService::send($invoice->customer->phone, $message, 'invoice_reminder', $invoice->customer_id);

        return back()->with($sent ? 'success' : 'error', $sent ? __('ops.invoice_reminder_sent') : __('ops.sms_failed_check_log'));
    }

    /* --------------------------------------------------------------- Payments */

    public function payments(Request $request)
    {
        $payments = Payment::with(['customer', 'invoice', 'recorder'])
            ->when($request->filled('method'), fn ($q) => $q->where('payment_method', $request->method))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w->where('payment_number', 'like', "%{$request->q}%")->orWhere('reference_number', 'like', "%{$request->q}%")->orWhereHas('customer', fn ($c) => $c->where('first_name', 'like', "%{$request->q}%")->orWhere('last_name', 'like', "%{$request->q}%"))))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('payment_date', '>=', $request->from))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('payment_date', '<=', $request->to))
            ->latest('payment_date')
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $completed = Payment::where('status', 'Completed')
            ->when($request->filled('from'), fn ($q) => $q->whereDate('payment_date', '>=', $request->from))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('payment_date', '<=', $request->to));
        $byMethod = (clone $completed)->selectRaw('payment_method, sum(amount) as total, count(*) as count')->groupBy('payment_method')->orderByDesc('total')->get();
        $total = (float) (clone $completed)->sum('amount');

        return view('finance.payments', compact('payments', 'byMethod', 'total'));
    }

    public function recordPayment(Request $request)
    {
        $data = $request->validate([
            'invoice_id' => 'nullable|exists:invoices,id',
            'customer_id' => 'required|exists:customers,id',
            'amount' => 'required|numeric|min:1',
            'payment_date' => 'required|date|before_or_equal:today',
            'payment_method' => 'required|string',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'send_receipt' => 'nullable|boolean',
        ]);

        if (! empty($data['invoice_id'])) {
            $invoice = Invoice::findOrFail($data['invoice_id']);
            if ((int) $invoice->customer_id !== (int) $data['customer_id']) {
                throw ValidationException::withMessages(['invoice_id' => __('ops.payment_invoice_mismatch')]);
            }
            if ($invoice->status === 'Cancelled') {
                throw ValidationException::withMessages(['invoice_id' => __('ops.payment_invoice_cancelled')]);
            }
            if ((float) $data['amount'] > (float) $invoice->balance_due + 0.009) {
                throw ValidationException::withMessages(['amount' => __('ops.payment_exceeds_balance', ['balance' => format_currency($invoice->balance_due, null, 0)])]);
            }
        }

        $payment = $this->financeService->recordPayment(collect($data)->except('send_receipt')->all() + ['status' => 'Completed']);

        if ($request->boolean('send_receipt')) {
            $customer = Customer::find($data['customer_id']);
            if ($customer?->phone) {
                SmsService::send($customer->phone, __('ops.payment_receipt_sms', [
                    'name' => $customer->first_name,
                    'amount' => format_currency($payment->amount, null, 0),
                    'number' => $payment->payment_number,
                    'company' => setting('company_name', 'Avenix'),
                ], 'sw'), 'payment_receipt', $customer->id);
            }
        }

        return back()->with('success', __('ops.payment_recorded', ['number' => $payment->payment_number]));
    }

    public function voidPayment(Request $request, Payment $payment)
    {
        $request->validate(['reason' => 'nullable|string|max:255']);

        $payment->update([
            'status' => 'Voided',
            'notes' => trim(($payment->notes ? $payment->notes."\n" : '').__('ops.voided_note', ['reason' => $request->input('reason', '-'), 'user' => $request->user()->name])),
        ]);
        $payment->invoice?->recalculateTotals();

        return back()->with('success', __('ops.payment_voided', ['number' => $payment->payment_number]));
    }

    public function receipt(Payment $payment)
    {
        $payment->load(['customer', 'invoice', 'recorder']);

        return view('finance.receipt', compact('payment'));
    }

    /* --------------------------------------------------------------- Expenses */

    public function expenses(Request $request)
    {
        $filtered = Expense::query()
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->category))
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w->where('title', 'like', "%{$request->q}%")->orWhere('payee', 'like', "%{$request->q}%")->orWhere('expense_number', 'like', "%{$request->q}%")))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('expense_date', '>=', $request->from))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('expense_date', '<=', $request->to));

        $expenses = (clone $filtered)->with(['property', 'approver', 'receipt'])->latest('expense_date')->latest()->paginate(20)->withQueryString();
        $total = (float) (clone $filtered)->sum('amount');
        $byCategory = (clone $filtered)->selectRaw('category, sum(amount) as total')->groupBy('category')->orderByDesc('total')->get();
        $monthTotal = (float) Expense::where('expense_date', '>=', now()->startOfMonth()->toDateString())->sum('amount');

        $properties = Property::all();
        $categories = TaxonomyTerm::options('expense_category', TaxonomyTerm::DEFAULTS['expense_category']);

        return view('finance.expenses', compact('expenses', 'properties', 'categories', 'total', 'byCategory', 'monthTotal'));
    }

    public function storeExpense(Request $request)
    {
        $this->financeService->createExpense($this->expenseData($request));

        return back()->with('success', __('ops.expense_recorded'));
    }

    public function updateExpense(Request $request, Expense $expense)
    {
        $expense->update($this->expenseData($request));

        return back()->with('success', __('ops.expense_updated'));
    }

    public function destroyExpense(Expense $expense)
    {
        $expense->delete();

        return back()->with('success', __('ops.expense_deleted'));
    }

    /* ---------------------------------------------------------------- Helpers */

    /**
     * @return array<string, mixed>
     */
    protected function invoiceData(Request $request): array
    {
        $data = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'sales_deal_id' => 'nullable|exists:sales_deals,id',
            'property_id' => 'nullable|exists:properties,id',
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:issue_date',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:2000',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $data['tax_rate'] = $data['tax_rate'] ?? 0;
        $data['discount_amount'] = $data['discount_amount'] ?? 0;
        $data['items'] = array_values(array_map(fn ($i) => [
            'description' => $i['description'],
            'quantity' => (float) $i['quantity'],
            'unit_price' => (float) $i['unit_price'],
        ], $data['items']));

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    protected function expenseData(Request $request): array
    {
        return $request->validate([
            'property_id' => 'nullable|exists:properties,id',
            'category' => 'required|string|max:100',
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:1',
            'expense_date' => 'required|date',
            'payee' => 'nullable|string|max:255',
            'payment_method' => 'required|string|max:50',
        ]);
    }
}
