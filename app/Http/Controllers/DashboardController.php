<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Payment;
use App\Models\Property;
use App\Models\SalesDeal;
use App\Services\Analytics\AnalyticsService;

class DashboardController extends Controller
{
    public function __construct(protected AnalyticsService $analyticsService) {}

    public function index()
    {
        $metrics = $this->analyticsService->getDashboardMetrics();
        $recentProperties = Property::with(['propertyType', 'branch'])->latest()->take(5)->get();
        $recentLeads = Lead::with(['customer', 'agent'])->latest()->take(5)->get();
        $recentDeals = SalesDeal::with(['property', 'customer'])->latest()->take(5)->get();
        $recentPayments = Payment::with(['customer', 'invoice'])->latest()->take(5)->get();

        return view('dashboard', compact('metrics', 'recentProperties', 'recentLeads', 'recentDeals', 'recentPayments'));
    }
}
