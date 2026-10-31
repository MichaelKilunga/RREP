<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Payment;
use App\Models\Property;
use App\Models\PropertyInquiry;
use App\Models\RealEstateProject;
use App\Models\SalesDeal;
use App\Models\User;
use App\Services\Analytics\AnalyticsService;

class DashboardController extends Controller
{
    public function __construct(protected AnalyticsService $analyticsService) {}

    public function index()
    {
        $user = auth()->user();
        if (! $user->can('view_dashboard')) {
            return redirect($this->landingFor($user));
        }

        $metrics = $this->analyticsService->getDashboardMetrics();
        $metrics['total_projects'] = RealEstateProject::count();
        $metrics['plots_available'] = (int) RealEstateProject::where('project_category', 'land')->sum('available_units');
        $metrics['new_requests'] = PropertyInquiry::where('status', 'New')->count();

        $showProperties = is_module_enabled('properties_module', false);
        $recentProperties = $showProperties
            ? Property::with(['propertyType', 'branch', 'media.mediaFile'])->latest()->take(5)->get()
            : collect();
        $recentRequests = PropertyInquiry::with(['property', 'project'])->latest()->take(5)->get();
        $recentLeads = Lead::with(['customer', 'agent'])->latest()->take(5)->get();
        $recentDeals = SalesDeal::with(['property', 'customer'])->latest()->take(5)->get();
        $recentPayments = Payment::with(['customer', 'invoice'])->latest()->take(5)->get();

        return view('dashboard', compact('metrics', 'recentProperties', 'recentRequests', 'recentLeads', 'recentDeals', 'recentPayments'));
    }

    /**
     * The first page a user without dashboard access is allowed to open, so
     * logging in never dead-ends on a 403.
     */
    protected function landingFor(User $user): string
    {
        if ($user->hasRole('property_owner')) {
            return route('portals.owner');
        }

        foreach ([
            'view_requests' => 'crm.requests',
            'view_leads' => 'crm.leads',
            'view_projects' => 'admin.projects.index',
            'view_invoices' => 'finance.invoices',
            'view_reports' => 'reports.index',
            'view_documents' => 'documents.index',
        ] as $permission => $routeName) {
            if ($user->can($permission)) {
                return route($routeName);
            }
        }

        return route('portals.client');
    }
}
