<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Payment;
use App\Models\Property;
use App\Models\PropertyInquiry;
use App\Models\RealEstateProject;
use App\Models\SalesDeal;
use App\Services\Analytics\AnalyticsService;

class DashboardController extends Controller
{
    public function __construct(protected AnalyticsService $analyticsService) {}

    public function index()
    {
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
}
