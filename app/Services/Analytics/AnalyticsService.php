<?php

namespace App\Services\Analytics;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Reservation;
use App\Models\SalesDeal;

class AnalyticsService
{
    public function getDashboardMetrics(): array
    {
        $totalProperties = Property::count();
        $activeListings = Property::where('status', 'Available')->count();
        $totalLeads = Lead::count();
        $pendingReservations = Reservation::where('status', 'Active')->count();
        $activeDeals = SalesDeal::where('status', 'Active')->count();
        $totalCustomers = Customer::count();

        $startOfMonth = now()->startOfMonth()->toDateString();
        $endOfMonth = now()->endOfMonth()->toDateString();

        $revenueThisMonth = Payment::where('status', 'Completed')
            ->whereBetween('payment_date', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        $totalRevenueAllTime = Payment::where('status', 'Completed')->sum('amount');
        $outstandingReceivables = Invoice::whereIn('status', ['Issued', 'Partially Paid', 'Overdue'])->sum('balance_due');

        // Pipeline Distribution
        $pipelineStages = [
            'New' => Lead::where('stage', 'New')->count(),
            'Contacted' => Lead::where('stage', 'Contacted')->count(),
            'Qualified' => Lead::where('stage', 'Qualified')->count(),
            'Viewing' => Lead::where('stage', 'Viewing')->count(),
            'Negotiation' => Lead::where('stage', 'Negotiation')->count(),
            'Won' => Lead::where('stage', 'Won')->count(),
            'Lost' => Lead::where('stage', 'Lost')->count(),
        ];

        return [
            'total_properties' => $totalProperties,
            'active_listings' => $activeListings,
            'total_leads' => $totalLeads,
            'total_customers' => $totalCustomers,
            'pending_reservations' => $pendingReservations,
            'active_deals' => $activeDeals,
            'revenue_this_month' => $revenueThisMonth,
            'total_revenue_all_time' => $totalRevenueAllTime,
            'outstanding_receivables' => $outstandingReceivables,
            'occupancy_rate' => $totalProperties > 0 ? round(($totalProperties - $activeListings) / $totalProperties * 100, 1) : 0,
            'pipeline_stages' => $pipelineStages,
        ];
    }
}
