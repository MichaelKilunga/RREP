<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\CommunicationLog;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Lease;
use App\Models\Payment;
use App\Models\Property;
use App\Models\PropertyInquiry;
use App\Models\RealEstateProject;
use App\Models\Reservation;
use App\Models\SalesDeal;
use App\Models\SurveyProject;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public const PERIODS = ['this_month', 'last_month', 'last_30', 'last_90', 'this_year', 'last_year', 'all', 'custom'];

    public function index()
    {
        $month = [now()->startOfMonth(), now()->endOfMonth()];

        $headlines = [
            'projects' => RealEstateProject::where('project_category', 'land')->sum('available_units'),
            'plot_sales' => Reservation::where('status', 'Converted')->whereBetween('updated_at', $month)->count(),
            'requests' => PropertyInquiry::whereBetween('created_at', $month)->count(),
            'leads' => Lead::whereBetween('created_at', $month)->count(),
            'customers' => Customer::whereBetween('created_at', $month)->count(),
            'finance' => (float) Payment::where('status', 'Completed')->whereBetween('payment_date', [$month[0]->toDateString(), $month[1]->toDateString()])->sum('amount'),
            'sms' => CommunicationLog::where('channel', 'sms')->where('status', 'Sent')->whereBetween('created_at', $month)->count(),
        ];

        return view('reports.index', compact('headlines'));
    }

    /* ----------------------------------------------------- Projects & plots */

    public function projectsReport(Request $request)
    {
        $projects = RealEstateProject::with('service')
            ->withCount([
                'parcels as plots_available' => fn ($q) => $q->where('status', 'Available'),
                'parcels as plots_reserved' => fn ($q) => $q->where('status', 'Reserved'),
                'parcels as plots_sold' => fn ($q) => $q->where('status', 'Sold'),
            ])
            ->withSum(['parcels as value_sold' => fn ($q) => $q->where('status', 'Sold')], 'price')
            ->withSum(['parcels as value_available' => fn ($q) => $q->where('status', 'Available')], 'price')
            ->orderBy('title')
            ->get();

        $land = $projects->where('project_category', '!=', 'service');
        $totals = [
            'projects' => $projects->count(),
            'plots_total' => (int) $land->sum(fn ($p) => $p->plots_available + $p->plots_reserved + $p->plots_sold),
            'plots_available' => (int) $land->sum('plots_available'),
            'plots_sold' => (int) $land->sum('plots_sold'),
            'active_holds' => Reservation::where('status', 'Active')->whereNotNull('land_parcel_id')->count(),
            'value_sold' => (float) $land->sum('value_sold'),
            'value_available' => (float) $land->sum('value_available'),
        ];

        if ($request->get('export') === 'csv') {
            return $this->exportCsv('projects_report.csv', [
                'Project', 'Category', 'Status', 'Location', 'Available', 'Reserved', 'Sold', 'Value sold (TZS)', 'Value for sale (TZS)', 'Starting Price', 'Published',
            ], $projects->map(fn ($p) => [
                $p->title, $p->project_category, $p->project_status, trim(($p->location_name ?: '').', '.$p->city, ', '),
                $p->plots_available, $p->plots_reserved, $p->plots_sold, (float) $p->value_sold, (float) $p->value_available, $p->starting_price, $p->is_published ? 'Yes' : 'No',
            ]));
        }

        return view('reports.projects_report', compact('projects', 'totals', 'land'));
    }

    /* -------------------------------------------- Plot sales & reservations */

    public function plotSalesReport(Request $request)
    {
        $period = $this->period($request);
        $holds = Reservation::with(['customer', 'landParcel.project', 'property'])
            ->whereBetween('created_at', [$period['from'], $period['to']])
            ->latest()
            ->get();
        $sales = Reservation::with(['customer', 'landParcel.project', 'property'])
            ->where('status', 'Converted')
            ->whereBetween('updated_at', [$period['from'], $period['to']])
            ->get();

        $stats = [
            'holds' => $holds->count(),
            'sales' => $sales->count(),
            'expired' => $holds->where('status', 'Expired')->count(),
            'cancelled' => $holds->where('status', 'Cancelled')->count(),
            'deposits' => (float) $holds->sum('deposit_paid'),
            'sales_value' => (float) $sales->sum(fn ($r) => $r->landParcel?->price ?? $r->property?->price ?? 0),
        ];
        $stats['conversion'] = $stats['holds'] > 0 ? round($holds->where('status', 'Converted')->count() / $stats['holds'] * 100, 1) : 0;

        $byProject = $holds->groupBy(fn ($r) => $r->landParcel?->project?->title ?? $r->property?->title ?? '—')
            ->map(fn (Collection $g, $name) => [
                'name' => $name,
                'holds' => $g->count(),
                'sold' => $g->where('status', 'Converted')->count(),
                'deposits' => (float) $g->sum('deposit_paid'),
            ])->sortByDesc('holds')->values();

        $series = $this->series($period, [
            'holds' => $holds->pluck('created_at'),
            'sales' => $sales->pluck('updated_at'),
        ]);

        if ($request->get('export') === 'csv') {
            return $this->exportCsv('plot_sales_report.csv', ['Reservation', 'Customer', 'Phone', 'Plot / Property', 'Project', 'Deposit (TZS)', 'Status', 'Placed', 'Until'],
                $holds->map(fn ($r) => [
                    $r->reservation_number, $r->customer?->full_name, $r->customer?->phone,
                    $r->landParcel ? 'Plot '.$r->landParcel->parcel_number.' ('.$r->landParcel->block_number.')' : $r->property?->title,
                    $r->landParcel?->project?->title, (float) $r->deposit_paid, $r->status, $r->created_at?->format('Y-m-d'), $r->reserved_until?->format('Y-m-d'),
                ]));
        }

        return view('reports.plot_sales_report', compact('period', 'holds', 'stats', 'byProject', 'series'));
    }

    /* ------------------------------------------------------ Website requests */

    public function requestsReport(Request $request)
    {
        $period = $this->period($request);
        $requests = PropertyInquiry::with(['property', 'project', 'service', 'assignee'])
            ->whereBetween('created_at', [$period['from'], $period['to']])
            ->latest()
            ->get();

        $byType = $requests->groupBy('inquiry_type')->map->count()->sortDesc();
        $byStatus = $requests->groupBy('status')->map->count();
        $handled = $requests->filter(fn ($r) => $r->handled_at);
        $avgResponseHours = $handled->isNotEmpty()
            ? round($handled->avg(fn ($r) => $r->created_at->diffInMinutes($r->handled_at)) / 60, 1)
            : null;

        $stats = [
            'total' => $requests->count(),
            'new' => $byStatus['New'] ?? 0,
            'resolved' => ($byStatus['Resolved'] ?? 0) + ($byStatus['Closed'] ?? 0),
            'converted' => $requests->whereNotNull('lead_id')->count(),
            'avg_response_hours' => $avgResponseHours,
        ];
        $stats['resolution_rate'] = $stats['total'] > 0 ? round($stats['resolved'] / $stats['total'] * 100, 1) : 0;

        $series = $this->series($period, ['requests' => $requests->pluck('created_at')]);

        if ($request->get('export') === 'csv') {
            return $this->exportCsv('website_requests_report.csv', [
                'Type', 'Name', 'Phone', 'Email', 'Subject', 'Message', 'Status', 'Assigned to', 'Received', 'Handled',
            ], $requests->map(fn ($r) => [
                $r->inquiry_type, $r->name, $r->phone, $r->email,
                $r->property?->title ?? $r->project?->title ?? $r->service?->title ?? 'General',
                $r->message, $r->status, $r->assignee?->name, $r->created_at?->format('Y-m-d H:i'), $r->handled_at?->format('Y-m-d H:i'),
            ]));
        }

        return view('reports.requests_report', compact('period', 'requests', 'byType', 'byStatus', 'stats', 'series'));
    }

    /* -------------------------------------------------------- Leads pipeline */

    public function leadsReport(Request $request)
    {
        $period = $this->period($request);
        $leads = Lead::with(['customer', 'agent.user', 'property'])
            ->whereBetween('created_at', [$period['from'], $period['to']])
            ->latest()
            ->get();

        $stageOrder = CRMController::LEAD_STAGES;
        $stages = collect($stageOrder)->mapWithKeys(fn ($s) => [$s => $leads->where('stage', $s)->count()]);
        $sources = $leads->groupBy(fn ($l) => $l->source ?: '—')->map->count()->sortDesc();
        $byAgent = $leads->groupBy(fn ($l) => $l->agent?->user?->name ?? __('ops.unassigned'))
            ->map(fn (Collection $g) => ['total' => $g->count(), 'won' => $g->where('stage', 'Won')->count()])
            ->sortByDesc('total');

        $won = $stages['Won'] ?? 0;
        $stats = [
            'total' => $leads->count(),
            'won' => $won,
            'lost' => $stages['Lost'] ?? 0,
            'conversion' => $leads->count() > 0 ? round($won / $leads->count() * 100, 1) : 0,
            'pipeline_value' => (float) $leads->whereNotIn('stage', ['Won', 'Lost'])->sum('estimated_value'),
            'won_value' => (float) $leads->where('stage', 'Won')->sum('estimated_value'),
        ];

        if ($request->get('export') === 'csv') {
            return $this->exportCsv('leads_report.csv', ['Customer', 'Phone', 'Lead', 'Source', 'Stage', 'Priority', 'Agent', 'Estimated value', 'Created', 'Next follow-up'],
                $leads->map(fn ($l) => [
                    $l->customer?->full_name, $l->customer?->phone, $l->title, $l->source, $l->stage, $l->priority,
                    $l->agent?->user?->name, (float) $l->estimated_value, $l->created_at?->format('Y-m-d'), $l->next_followup_at?->format('Y-m-d H:i'),
                ]));
        }

        return view('reports.leads_report', compact('period', 'leads', 'stages', 'sources', 'byAgent', 'stats'));
    }

    /* -------------------------------------------------------------- Customers */

    public function customersReport(Request $request)
    {
        $period = $this->period($request);
        $customers = Customer::withCount(['requests', 'leads', 'reservations'])
            ->withSum(['invoices as paid_total' => fn ($q) => $q->where('status', '!=', 'Cancelled')], 'paid_amount')
            ->whereBetween('created_at', [$period['from'], $period['to']])
            ->latest()
            ->get();

        $byRegion = $customers->groupBy(fn ($c) => $c->city ?: '—')->map->count()->sortDesc();
        $bySource = $customers->groupBy(fn ($c) => $c->source ?: '—')->map->count()->sortDesc();
        $byKyc = $customers->groupBy(fn ($c) => $c->kyc_status ?: '—')->map->count();
        $topPayers = Customer::withSum(['payments as paid' => fn ($q) => $q->where('status', 'Completed')->whereBetween('payment_date', [$period['from']->toDateString(), $period['to']->toDateString()])], 'amount')
            ->orderByDesc('paid')
            ->take(8)
            ->get()
            ->filter(fn ($c) => (float) $c->paid > 0);

        $stats = [
            'new' => $customers->count(),
            'total' => Customer::count(),
            'with_holds' => $customers->where('reservations_count', '>', 0)->count(),
            'corporate' => $customers->where('customer_type', 'Corporate')->count(),
        ];

        $series = $this->series($period, ['customers' => $customers->pluck('created_at')]);

        if ($request->get('export') === 'csv') {
            return $this->exportCsv('customers_report.csv', ['Name', 'Type', 'Phone', 'Email', 'Region', 'Source', 'KYC', 'Requests', 'Leads', 'Reservations', 'Paid (TZS)', 'Joined'],
                $customers->map(fn ($c) => [
                    $c->full_name, $c->customer_type, $c->phone, $c->email, $c->city, $c->source, $c->kyc_status,
                    $c->requests_count, $c->leads_count, $c->reservations_count, (float) $c->paid_total, $c->created_at?->format('Y-m-d'),
                ]));
        }

        return view('reports.customers_report', compact('period', 'customers', 'byRegion', 'bySource', 'byKyc', 'topPayers', 'stats', 'series'));
    }

    /* ------------------------------------------------------------- Financial */

    public function financeReport(Request $request)
    {
        $period = $this->period($request);
        [$from, $to] = [$period['from']->toDateString(), $period['to']->toDateString()];

        $payments = Payment::with(['customer', 'invoice'])->where('status', 'Completed')->whereBetween('payment_date', [$from, $to])->get();
        $expenses = Expense::whereBetween('expense_date', [$from, $to])->get();
        $invoiced = (float) Invoice::where('status', '!=', 'Cancelled')->whereBetween('issue_date', [$from, $to])->sum('total_amount');

        $collected = (float) $payments->sum('amount');
        $spent = (float) $expenses->sum('amount');
        $stats = [
            'invoiced' => $invoiced,
            'collected' => $collected,
            'expenses' => $spent,
            'net' => $collected - $spent,
            'collection_rate' => $invoiced > 0 ? round($collected / $invoiced * 100, 1) : null,
            'outstanding' => (float) Invoice::whereNotIn('status', ['Paid', 'Cancelled'])->where('balance_due', '>', 0)->sum('balance_due'),
        ];

        $expensesByCategory = $expenses->groupBy(fn ($e) => $e->category ?: '—')->map(fn ($g) => (float) $g->sum('amount'))->sortDesc();
        $paymentsByMethod = $payments->groupBy(fn ($p) => tz_payment_methods()[$p->payment_method] ?? $p->payment_method)->map(fn ($g) => (float) $g->sum('amount'))->sortDesc();

        $collectedByMonth = $payments->groupBy(fn ($p) => $p->payment_date->format('Y-m'))->map(fn ($g) => (float) $g->sum('amount'));
        $spentByMonth = $expenses->groupBy(fn ($e) => $e->expense_date->format('Y-m'))->map(fn ($g) => (float) $g->sum('amount'));

        // The monthly statement is capped at the last 24 months so "All time" stays readable.
        $months = collect();
        $cursor = $period['from']->copy()->startOfMonth()->max(now()->subMonths(23)->startOfMonth());
        for (; $cursor->lte($period['to']); $cursor->addMonth()) {
            $key = $cursor->format('Y-m');
            $months->push([
                'label' => $cursor->translatedFormat('M Y'),
                'collected' => $collectedByMonth[$key] ?? 0.0,
                'expenses' => $spentByMonth[$key] ?? 0.0,
            ]);
        }

        if ($request->get('export') === 'csv') {
            return $this->exportCsv('financial_report.csv', ['Month', 'Collected (TZS)', 'Expenses (TZS)', 'Net (TZS)'],
                $months->map(fn ($m) => [$m['label'], $m['collected'], $m['expenses'], $m['collected'] - $m['expenses']])
                    ->push(['TOTAL', $collected, $spent, $collected - $spent]));
        }

        return view('reports.finance_report', compact('period', 'stats', 'expensesByCategory', 'paymentsByMethod', 'months', 'payments'));
    }

    /* ------------------------------------------------------- SMS & delivery */

    public function smsReport(Request $request)
    {
        $period = $this->period($request);
        $logs = CommunicationLog::where('channel', 'sms')->whereBetween('created_at', [$period['from'], $period['to']])->latest()->get();

        $byStatus = $logs->groupBy('status')->map->count();
        $sent = $byStatus['Sent'] ?? 0;
        $stats = [
            'total' => $logs->count(),
            'sent' => $sent,
            'failed' => $byStatus['Failed'] ?? 0,
            'skipped' => $byStatus['Skipped'] ?? 0,
            'success_rate' => $logs->count() > 0 ? round($sent / $logs->count() * 100, 1) : null,
        ];
        $byTemplate = $logs->groupBy(fn ($l) => $l->template_code ?: 'manual')
            ->map(fn (Collection $g) => ['total' => $g->count(), 'sent' => $g->where('status', 'Sent')->count()])
            ->sortByDesc('total');
        $failureReasons = $logs->whereIn('status', ['Failed', 'Skipped'])
            ->groupBy(fn ($l) => Str::limit((string) ($l->error_message ?: 'Unknown'), 90))
            ->map->count()->sortDesc()->take(8);

        $series = $this->series($period, [
            'sent' => $logs->where('status', 'Sent')->pluck('created_at'),
            'failed' => $logs->whereIn('status', ['Failed', 'Skipped'])->pluck('created_at'),
        ]);

        if ($request->get('export') === 'csv') {
            return $this->exportCsv('sms_report.csv', ['Date', 'Recipient', 'Template', 'Status', 'Message', 'Error'],
                $logs->map(fn ($l) => [$l->created_at?->format('Y-m-d H:i'), $l->recipient, $l->template_code, $l->status, $l->body, $l->error_message]));
        }

        return view('reports.sms_report', compact('period', 'logs', 'stats', 'byTemplate', 'failureReasons', 'series'));
    }

    /* ------------------------------------------------------- Legacy modules */

    public function propertyReport(Request $request)
    {
        $query = Property::with(['propertyType', 'owner', 'branch']);
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }

        $properties = $query->get();
        $totalValuation = $properties->sum('price');
        $totalRentPotential = $properties->sum('rent_price');

        if ($request->get('export') === 'csv') {
            return $this->exportCsv('property_inventory_report.csv', [
                'Property Code', 'Title', 'Type', 'City', 'Listing Type', 'Status', 'Price', 'Rent Price', 'Owner',
            ], $properties->map(fn ($p) => [
                $p->property_code, $p->title, $p->propertyType?->name, $p->city, $p->listing_type, $p->status, $p->price, $p->rent_price, $p->owner?->full_name,
            ]));
        }

        return view('reports.property_report', compact('properties', 'totalValuation', 'totalRentPotential'));
    }

    public function salesReport(Request $request)
    {
        $deals = SalesDeal::with(['property', 'customer', 'agent.user', 'invoices.payments'])->latest()->get();
        $totalSalesValue = $deals->sum('sale_price');
        $totalCommissions = $deals->sum('commission_amount');

        if ($request->get('export') === 'csv') {
            return $this->exportCsv('sales_revenue_report.csv', [
                'Deal #', 'Property', 'Buyer', 'Agent', 'Sale Price', 'Plan', 'Commission', 'Status', 'Date',
            ], $deals->map(fn ($d) => [
                $d->deal_number, $d->property?->title, $d->customer?->full_name, $d->agent?->user?->name, $d->sale_price, $d->payment_plan_type, $d->commission_amount, $d->status, $d->agreement_date->format('Y-m-d'),
            ]));
        }

        return view('reports.sales_report', compact('deals', 'totalSalesValue', 'totalCommissions'));
    }

    public function agentCommissionReport()
    {
        $agents = Agent::with(['user', 'salesDeals', 'commissions'])->get();

        return view('reports.agent_commission_report', compact('agents'));
    }

    public function rentRollReport()
    {
        $leases = Lease::with(['property', 'tenant.customer', 'rentSchedules'])->get();
        $totalMonthlyRent = $leases->where('status', 'Active')->sum('rent_amount');

        return view('reports.rent_roll_report', compact('leases', 'totalMonthlyRent'));
    }

    public function surveyReport()
    {
        $surveys = SurveyProject::with(['property', 'leadSurveyor', 'beacons', 'milestones'])->get();

        return view('reports.survey_report', compact('surveys'));
    }

    /* ---------------------------------------------------------------- Helpers */

    /**
     * The reporting window chosen with the shared period picker.
     *
     * @return array{key: string, from: Carbon, to: Carbon, label: string}
     */
    protected function period(Request $request): array
    {
        $key = in_array($request->input('period'), self::PERIODS, true) ? $request->input('period') : 'this_month';

        [$from, $to] = match ($key) {
            'last_month' => [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()],
            'last_30' => [now()->subDays(29)->startOfDay(), now()->endOfDay()],
            'last_90' => [now()->subDays(89)->startOfDay(), now()->endOfDay()],
            'this_year' => [now()->startOfYear(), now()->endOfDay()],
            'last_year' => [now()->subYear()->startOfYear(), now()->subYear()->endOfYear()],
            'all' => [Carbon::create(2000, 1, 1), now()->endOfDay()],
            'custom' => [
                $request->filled('from') ? rescue(fn () => Carbon::parse($request->input('from'))->startOfDay(), now()->startOfMonth(), false) : now()->startOfMonth(),
                $request->filled('to') ? rescue(fn () => Carbon::parse($request->input('to'))->endOfDay(), now()->endOfDay(), false) : now()->endOfDay(),
            ],
            default => [now()->startOfMonth(), now()->endOfMonth()],
        };

        if ($from->gt($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        $label = $key === 'all' ? __('ops.period_all') : $from->format('d M Y').' – '.$to->format('d M Y');

        return ['key' => $key, 'from' => $from, 'to' => $to, 'label' => $label];
    }

    /**
     * Bucket event timestamps per day (short windows) or per month, filling gaps with zero.
     *
     * @param  array{key: string, from: Carbon, to: Carbon, label: string}  $period
     * @param  array<string, Collection<int, mixed>>  $streams
     * @return array{labels: array<int, string>, series: array<string, array<int, int>>}
     */
    protected function series(array $period, array $streams): array
    {
        $from = $period['key'] === 'all'
            ? collect($streams)->flatten()->filter()->min() ?? now()->subMonths(5)
            : $period['from'];
        $from = Carbon::parse($from);
        $daily = $from->diffInDays($period['to']) <= 62;

        $keys = [];
        $labels = [];
        $cursor = $daily ? $from->copy()->startOfDay() : $from->copy()->startOfMonth();
        while ($cursor->lte($period['to'])) {
            $keys[] = $cursor->format($daily ? 'Y-m-d' : 'Y-m');
            $labels[] = $cursor->translatedFormat($daily ? 'd M' : 'M Y');
            $daily ? $cursor->addDay() : $cursor->addMonth();
        }

        $series = [];
        foreach ($streams as $name => $dates) {
            $counts = array_fill_keys($keys, 0);
            foreach ($dates as $date) {
                $bucket = Carbon::parse($date)->format($daily ? 'Y-m-d' : 'Y-m');
                if (isset($counts[$bucket])) {
                    $counts[$bucket]++;
                }
            }
            $series[$name] = array_values($counts);
        }

        return ['labels' => $labels, 'series' => $series];
    }

    protected function exportCsv(string $filename, array $headers, $rows): StreamedResponse
    {
        abort_unless(auth()->user()?->can('export_reports'), 403);

        return response()->streamDownload(function () use ($headers, $rows) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel shows Swahili characters correctly
            fputcsv($handle, $headers);
            foreach ($rows as $row) {
                fputcsv($handle, (array) $row);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
