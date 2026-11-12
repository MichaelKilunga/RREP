<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\Property;
use App\Models\PropertyInquiry;
use App\Models\User;
use App\Services\CRM\CRMService;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CRMController extends Controller
{
    public const LEAD_STAGES = ['New', 'Contacted', 'Qualified', 'Viewing', 'Proposal', 'Negotiation', 'Won', 'Lost'];

    public const REQUEST_STATUSES = ['New', 'Contacted', 'Resolved', 'Closed'];

    public function __construct(protected CRMService $crmService) {}

    /* ------------------------------------------------------------------ Leads */

    public function leads(Request $request)
    {
        $leads = Lead::with(['customer', 'agent.user', 'property', 'activities' => fn ($q) => $q->latest()->with('user')])
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = $request->q;
                $q->where(fn ($w) => $w->where('title', 'like', "%{$term}%")
                    ->orWhereHas('customer', fn ($c) => $c->where('first_name', 'like', "%{$term}%")->orWhere('last_name', 'like', "%{$term}%")->orWhere('phone', 'like', "%{$term}%")));
            })
            ->when($request->filled('agent'), fn ($q) => $q->where('assigned_agent_id', $request->agent))
            ->when($request->boolean('due'), fn ($q) => $q->whereNotNull('next_followup_at')->where('next_followup_at', '<=', now()->endOfDay())->whereNotIn('stage', ['Won', 'Lost']))
            ->latest()
            ->get();
        $stages = self::LEAD_STAGES;
        $agents = Agent::with('user')->where('status', 'Active')->get();
        $properties = Property::all();
        $customers = Customer::orderBy('first_name')->get();
        $dueCount = Lead::whereNotNull('next_followup_at')->where('next_followup_at', '<=', now()->endOfDay())->whereNotIn('stage', ['Won', 'Lost'])->count();

        return view('crm.leads', compact('leads', 'stages', 'agents', 'properties', 'customers', 'dueCount'));
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

        return back()->with('success', __('ops.lead_created'));
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
            'next_followup_at' => 'nullable|date',
            'lost_reason' => 'nullable|string|max:255',
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
            abort_unless($request->user()->can('delete_leads'), 403);
            $count = $leads->count();
            $leads->get()->each->delete();

            return back()->with('success', __('ops.leads_bulk_deleted', ['count' => $count]));
        }

        $leads->update(['stage' => $data['stage']]);

        return back()->with('success', __('ops.leads_bulk_moved', ['count' => count($data['ids'])]));
    }

    public function updateLeadStage(Request $request, Lead $lead)
    {
        $request->validate(['stage' => 'required|string']);

        $lead->update(['stage' => $request->stage]);

        return response()->json(['success' => true, 'message' => __('ops.lead_moved')]);
    }

    public function logActivity(Request $request, Lead $lead)
    {
        $data = $request->validate([
            'activity_type' => 'required|string',
            'summary' => 'required|string',
            'details' => 'nullable|string',
            'next_followup_at' => 'nullable|date',
        ]);

        $this->crmService->logActivity($lead, collect($data)->except('next_followup_at')->all() + ['completed_at' => now()]);

        if (array_key_exists('next_followup_at', $data)) {
            $lead->update(['next_followup_at' => $data['next_followup_at']]);
        }
        $lead->customer?->update(['last_interaction_at' => now()]);

        return back()->with('success', __('ops.activity_logged'));
    }

    /* -------------------------------------------------------------- Customers */

    public function customers(Request $request)
    {
        $customers = $this->customerQuery($request)
            ->withCount(['leads', 'requests', 'reservations', 'invoices'])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total' => Customer::count(),
            'new_month' => Customer::where('created_at', '>=', now()->startOfMonth())->count(),
            'verified' => Customer::where('kyc_status', 'Verified')->count(),
            'with_holds' => Customer::whereHas('reservations', fn ($q) => $q->where('status', 'Active'))->count(),
        ];

        return view('crm.customers', compact('customers', 'stats'));
    }

    public function showCustomer(Customer $customer)
    {
        $customer->load([
            'leads.activities.user', 'leads.agent.user',
            'reservations.landParcel.project', 'reservations.property',
            'invoices.payments',
            'requests.project', 'requests.service',
            'appointments',
        ]);
        $smsLogs = $customer->smsLogs()->latest()->take(30)->get();

        $timeline = $this->customerTimeline($customer, $smsLogs);
        $summary = [
            'invoiced' => (float) $customer->invoices->sum('total_amount'),
            'paid' => (float) $customer->invoices->sum('paid_amount'),
            'balance' => (float) $customer->invoices->sum('balance_due'),
            'active_holds' => $customer->reservations->where('status', 'Active')->count(),
        ];

        return view('crm.customer_show', compact('customer', 'timeline', 'summary', 'smsLogs'));
    }

    public function storeCustomer(Request $request)
    {
        $customer = $this->crmService->createCustomer($this->customerData($request));

        return back()->with('success', __('ops.customer_created', ['name' => $customer->full_name]));
    }

    public function updateCustomer(Request $request, Customer $customer)
    {
        $customer->update($this->customerData($request));

        return back()->with('success', __('ops.customer_updated', ['name' => $customer->full_name]));
    }

    public function destroyCustomer(Customer $customer)
    {
        if ($this->hasOpenBusiness($customer)) {
            return back()->with('error', __('ops.customer_has_open_business'));
        }

        $name = $customer->full_name;
        $customer->delete();

        return redirect()->route('crm.customers')->with('success', __('ops.customer_deleted', ['name' => $name]));
    }

    /**
     * Delete the selected customers, keeping any who still hold a plot or owe money.
     */
    public function bulkCustomers(Request $request)
    {
        $data = $request->validate([
            'action' => 'required|in:delete',
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:customers,id',
        ]);

        $deleted = 0;
        $kept = [];
        foreach (Customer::whereIn('id', $data['ids'])->get() as $customer) {
            if ($this->hasOpenBusiness($customer)) {
                $kept[] = $customer->full_name;

                continue;
            }
            $customer->delete();
            $deleted++;
        }

        if ($kept !== []) {
            return back()->with('error', __('ops.customers_bulk_partial', [
                'count' => $deleted,
                'kept' => count($kept),
                'names' => implode(', ', array_slice($kept, 0, 5)).(count($kept) > 5 ? '...' : ''),
            ]));
        }

        return back()->with('success', __('ops.customers_bulk_deleted', ['count' => $deleted]));
    }

    /**
     * An active plot hold or an unpaid invoice: the record must stay.
     */
    protected function hasOpenBusiness(Customer $customer): bool
    {
        return $customer->reservations()->where('status', 'Active')->exists()
            || $customer->invoices()->where('status', '!=', 'Cancelled')->where('balance_due', '>', 0)->exists();
    }

    public function smsCustomer(Request $request, Customer $customer)
    {
        $data = $request->validate(['message' => 'required|string|max:640']);

        if (! $customer->phone) {
            return back()->with('error', __('ops.customer_no_phone'));
        }

        $result = SmsService::deliver($customer->phone, $data['message'], 'manual_customer_sms', $customer->id, manual: true);
        $customer->update(['last_interaction_at' => now()]);

        return $result['status'] === SmsService::STATUS_SENT
            ? back()->with('success', __('ops.sms_sent_to', ['name' => $customer->full_name]))
            : back()->with('error', __('ops.sms_not_sent_reason', ['reason' => $result['reason']]));
    }

    public function exportCustomers(Request $request): StreamedResponse
    {
        $customers = $this->customerQuery($request)->withCount(['leads', 'reservations', 'invoices'])->latest()->get();

        return response()->streamDownload(function () use ($customers) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Name', 'Type', 'Phone', 'Email', 'NIDA/Passport', 'TIN', 'City', 'KYC', 'Leads', 'Reservations', 'Invoices', 'Created']);
            foreach ($customers as $c) {
                fputcsv($out, [
                    $c->full_name, $c->customer_type, $c->phone, $c->email, $c->national_id_passport, $c->tax_number,
                    $c->city, $c->kyc_status, $c->leads_count, $c->reservations_count, $c->invoices_count, $c->created_at?->format('Y-m-d'),
                ]);
            }
            fclose($out);
        }, 'customers.csv', ['Content-Type' => 'text/csv']);
    }

    /* --------------------------------------------------------------- Requests */

    public function requests(Request $request)
    {
        $typeCounts = PropertyInquiry::selectRaw('inquiry_type, count(*) as total')
            ->groupBy('inquiry_type')
            ->orderByDesc('total')
            ->pluck('total', 'inquiry_type');
        $statusCounts = PropertyInquiry::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        $requests = PropertyInquiry::with(['property', 'project', 'service', 'assignee', 'customer'])
            ->when($request->filled('type'), fn ($q) => $q->where('inquiry_type', $request->type))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->boolean('mine'), fn ($q) => $q->where('assigned_to', $request->user()->id))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = $request->q;
                $q->where(fn ($w) => $w->where('name', 'like', "%{$term}%")->orWhere('phone', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%")->orWhere('message', 'like', "%{$term}%"));
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $staff = User::where('status', 'Active')->whereHas('roles', fn ($q) => $q->whereNotIn('name', ['customer', 'property_owner']))->orderBy('name')->get(['id', 'name']);

        return view('crm.requests', compact('requests', 'typeCounts', 'statusCounts', 'staff'));
    }

    public function updateRequestStatus(Request $request, PropertyInquiry $inquiry)
    {
        $request->validate(['status' => 'required|in:'.implode(',', self::REQUEST_STATUSES)]);

        $inquiry->update(['status' => $request->status, 'handled_at' => $request->status === 'New' ? null : ($inquiry->handled_at ?? now())]);

        return back()->with('success', __('ops.request_status_updated'));
    }

    public function updateRequest(Request $request, PropertyInquiry $inquiry)
    {
        $data = $request->validate([
            'status' => 'required|in:'.implode(',', self::REQUEST_STATUSES),
            'assigned_to' => 'nullable|exists:users,id',
            'staff_notes' => 'nullable|string|max:2000',
        ]);

        $data['handled_at'] = $data['status'] === 'New' ? null : ($inquiry->handled_at ?? now());
        $inquiry->update($data);

        return back()->with('success', __('ops.request_saved'));
    }

    /**
     * Turn a website request into a pipeline lead (creating the customer if needed).
     */
    public function convertRequest(PropertyInquiry $inquiry)
    {
        if ($inquiry->lead_id && Lead::find($inquiry->lead_id)) {
            return redirect()->route('crm.leads', ['q' => $inquiry->name])->with('info', __('ops.request_already_lead'));
        }

        $customer = $inquiry->customer ?: Customer::firstOrCreate(
            ['phone' => $inquiry->phone],
            [
                'first_name' => trim(strtok((string) $inquiry->name, ' ') ?: $inquiry->name),
                'last_name' => trim((string) substr((string) $inquiry->name, strlen((string) strtok((string) $inquiry->name, ' ')))),
                'email' => $inquiry->email,
                'customer_type' => 'Individual',
                'source' => $inquiry->source ?: 'Website',
            ]
        );

        $subject = $inquiry->project?->title ?? $inquiry->service?->title ?? $inquiry->property?->title ?? $inquiry->inquiry_type;
        $lead = $this->crmService->createLead([
            'customer_id' => $customer->id,
            'title' => "{$inquiry->inquiry_type}: {$subject}",
            'source' => $inquiry->source ?: 'Website',
            'stage' => 'Contacted',
            'priority' => in_array($inquiry->inquiry_type, ['Plot Reservation Request', 'Service Quote'], true) ? 'High' : 'Medium',
            'property_interest_id' => $inquiry->property_id,
            'next_followup_at' => now()->addDay(),
        ]);

        $inquiry->update([
            'customer_id' => $customer->id,
            'lead_id' => $lead->id,
            'status' => $inquiry->status === 'New' ? 'Contacted' : $inquiry->status,
            'handled_at' => $inquiry->handled_at ?? now(),
        ]);

        return redirect()->route('crm.leads', ['q' => $customer->first_name])->with('success', __('ops.request_converted'));
    }

    public function destroyRequest(PropertyInquiry $inquiry)
    {
        $inquiry->delete();

        return back()->with('success', __('ops.request_deleted'));
    }

    public function bulkRequests(Request $request)
    {
        $data = $request->validate([
            'action' => 'required|in:status,delete',
            'status' => 'required_if:action,status|nullable|in:'.implode(',', self::REQUEST_STATUSES),
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:property_inquiries,id',
        ]);

        $query = PropertyInquiry::whereIn('id', $data['ids']);
        $count = $query->count();

        if ($data['action'] === 'delete') {
            $query->delete();
        } else {
            $query->update(['status' => $data['status'], 'handled_at' => $data['status'] === 'New' ? null : now()]);
        }

        return back()->with('success', __('ops.requests_bulk_done', ['count' => $count]));
    }

    /* ---------------------------------------------------------------- Helpers */

    protected function customerQuery(Request $request)
    {
        return Customer::query()
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = $request->q;
                $q->where(fn ($w) => $w->where('first_name', 'like', "%{$term}%")->orWhere('last_name', 'like', "%{$term}%")
                    ->orWhere('company_name', 'like', "%{$term}%")->orWhere('phone', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")->orWhere('national_id_passport', 'like', "%{$term}%"));
            })
            ->when($request->filled('type'), fn ($q) => $q->where('customer_type', $request->type))
            ->when($request->filled('kyc'), fn ($q) => $q->where('kyc_status', $request->kyc))
            ->when($request->filled('city'), fn ($q) => $q->where('city', $request->city));
    }

    /**
     * @return array<string, mixed>
     */
    protected function customerData(Request $request): array
    {
        return $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'company_name' => 'nullable|string|max:255',
            'customer_type' => 'required|in:Individual,Corporate',
            'email' => 'nullable|email',
            'phone' => 'required|string|max:50',
            'alt_phone' => 'nullable|string|max:50',
            'national_id_passport' => 'nullable|string|max:100',
            'tax_number' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:2000',
        ]);
    }

    /**
     * One chronological feed of everything that happened with a customer.
     *
     * @return Collection<int, array{at: mixed, icon: string, tone: string, title: string, detail: ?string}>
     */
    protected function customerTimeline(Customer $customer, Collection $smsLogs): Collection
    {
        $events = collect();

        foreach ($customer->requests as $r) {
            $events->push(['at' => $r->created_at, 'icon' => 'bi-inboxes', 'tone' => 'warning', 'title' => __('ops.tl_request', ['type' => $r->inquiry_type]), 'detail' => Str::limit((string) $r->message, 140)]);
        }
        foreach ($customer->leads as $lead) {
            $events->push(['at' => $lead->created_at, 'icon' => 'bi-funnel', 'tone' => 'info', 'title' => __('ops.tl_lead', ['title' => $lead->title]), 'detail' => __('ops.stage_'.strtolower($lead->stage))]);
            foreach ($lead->activities as $a) {
                $events->push(['at' => $a->created_at, 'icon' => 'bi-journal-text', 'tone' => 'primary', 'title' => $a->activity_type.': '.$a->summary, 'detail' => trim(($a->user?->name ? $a->user->name.' · ' : '').(string) $a->details, ' ·')]);
            }
        }
        foreach ($customer->reservations as $res) {
            $what = $res->landParcel ? __('pages.plot').' '.$res->landParcel->parcel_number.' · '.$res->landParcel->project?->title : $res->property?->title;
            $events->push(['at' => $res->created_at, 'icon' => 'bi-bookmark-check', 'tone' => 'success', 'title' => __('ops.tl_reservation', ['number' => $res->reservation_number]), 'detail' => $what.' · '.__('ops.reservation_status_'.strtolower($res->status))]);
        }
        foreach ($customer->invoices as $inv) {
            $events->push(['at' => $inv->created_at, 'icon' => 'bi-receipt', 'tone' => 'secondary', 'title' => __('ops.tl_invoice', ['number' => $inv->invoice_number]), 'detail' => format_currency($inv->total_amount, null, 0).' · '.$inv->status]);
            foreach ($inv->payments as $p) {
                $events->push(['at' => $p->created_at, 'icon' => 'bi-cash-coin', 'tone' => 'success', 'title' => __('ops.tl_payment', ['amount' => format_currency($p->amount, null, 0)]), 'detail' => $p->payment_method]);
            }
        }
        foreach ($smsLogs as $sms) {
            $events->push(['at' => $sms->created_at, 'icon' => 'bi-chat-left-dots', 'tone' => $sms->status === 'Sent' ? 'success' : 'danger', 'title' => __('ops.tl_sms', ['status' => $sms->status]), 'detail' => Str::limit((string) $sms->body, 140)]);
        }

        return $events->filter(fn ($e) => $e['at'])->sortByDesc('at')->values();
    }
}
