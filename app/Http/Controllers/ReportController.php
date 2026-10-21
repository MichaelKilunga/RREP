<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Lead;
use App\Models\Lease;
use App\Models\Property;
use App\Models\PropertyInquiry;
use App\Models\RealEstateProject;
use App\Models\Reservation;
use App\Models\SalesDeal;
use App\Models\SurveyProject;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }

    public function projectsReport(Request $request)
    {
        $projects = RealEstateProject::with('service')
            ->withMin('parcels', 'price')
            ->withMax('parcels', 'price')
            ->orderBy('title')
            ->get();

        $totals = [
            'projects' => $projects->count(),
            'plots_total' => (int) $projects->sum('total_units'),
            'plots_available' => (int) $projects->sum('available_units'),
            'plots_sold' => (int) $projects->sum('sold_plots'),
            'active_holds' => Reservation::where('status', 'Active')->whereNotNull('land_parcel_id')->count(),
        ];

        if ($request->get('export') === 'csv') {
            return $this->exportCsv('projects_report.csv', [
                'Project', 'Category', 'Status', 'Location', 'Total Plots', 'Available', 'Sold', 'Starting Price', 'Published',
            ], $projects->map(fn ($p) => [
                $p->title, $p->project_category, $p->project_status, trim(($p->location_name ?: '').', '.$p->city, ', '),
                $p->total_units, $p->available_units, $p->sold_plots, $p->starting_price, $p->is_published ? 'Yes' : 'No',
            ]));
        }

        return view('reports.projects_report', compact('projects', 'totals'));
    }

    public function requestsReport(Request $request)
    {
        $requests = PropertyInquiry::with(['property', 'project'])->latest()->get();
        $byType = PropertyInquiry::selectRaw('inquiry_type, count(*) as count')->groupBy('inquiry_type')->orderByDesc('count')->get();
        $byStatus = PropertyInquiry::selectRaw('status, count(*) as count')->groupBy('status')->get();

        if ($request->get('export') === 'csv') {
            return $this->exportCsv('website_requests_report.csv', [
                'Type', 'Name', 'Phone', 'Email', 'Subject', 'Message', 'Status', 'Received',
            ], $requests->map(fn ($r) => [
                $r->inquiry_type, $r->name, $r->phone, $r->email,
                $r->property?->title ?? $r->project?->title ?? 'General',
                $r->message, $r->status, $r->created_at?->format('Y-m-d H:i'),
            ]));
        }

        return view('reports.requests_report', compact('requests', 'byType', 'byStatus'));
    }

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

    public function leadsReport()
    {
        $leads = Lead::with(['customer', 'agent.user', 'property'])->latest()->get();
        $sources = Lead::selectRaw('source, count(*) as count')->groupBy('source')->get();
        $stages = Lead::selectRaw('stage, count(*) as count')->groupBy('stage')->get();

        return view('reports.leads_report', compact('leads', 'sources', 'stages'));
    }

    protected function exportCsv(string $filename, array $headers, $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);
            foreach ($rows as $row) {
                fputcsv($handle, (array) $row);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
