<?php

namespace Database\Seeders;

use App\Models\Agent;
use App\Models\Appointment;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\Organization;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Reservation;
use App\Models\SalesDeal;
use App\Models\SurveyBeacon;
use App\Models\SurveyMilestone;
use App\Models\SurveyProject;
use App\Models\User;
use Illuminate\Database\Seeder;

class CrmFinanceSeeder extends Seeder
{
    public function run(): void
    {
        $org = Organization::first();
        $darBranch = Branch::where('code', 'DAR-01')->first();
        $admin = User::first();
        $agent = Agent::first();
        $properties = Property::all();

        // 1. Customers
        $c1 = Customer::create([
            'organization_id' => $org->id,
            'branch_id' => $darBranch->id,
            'first_name' => 'Juma',
            'last_name' => 'Rashid',
            'company_name' => 'Serengeti Logistics Ltd',
            'customer_type' => 'Corporate',
            'email' => 'juma@serengetilogistics.tz',
            'phone' => '+255 713 123 456',
            'national_id_passport' => 'TZ-P-992144',
            'tax_number' => 'TIN-445-123-900',
            'address' => 'Posta, City Center',
            'city' => 'Dar es Salaam',
            'kyc_status' => 'Verified',
            'source' => 'Website',
        ]);

        $c2 = Customer::create([
            'organization_id' => $org->id,
            'branch_id' => $darBranch->id,
            'first_name' => 'Sophia',
            'last_name' => 'Makena',
            'customer_type' => 'Individual',
            'email' => 'sophia.makena@gmail.com',
            'phone' => '+255 768 456 789',
            'national_id_passport' => '19920815-33301-00005-18',
            'address' => 'Mikocheni B',
            'city' => 'Dar es Salaam',
            'kyc_status' => 'Verified',
            'source' => 'Referral',
        ]);

        // 2. Leads
        $lead1 = Lead::create([
            'organization_id' => $org->id,
            'branch_id' => $darBranch->id,
            'customer_id' => $c1->id,
            'title' => 'Looking for 5-Bed Villa in Masaki for CEO Residency',
            'source' => 'Website',
            'stage' => 'Negotiation',
            'priority' => 'High',
            'estimated_value' => 1250000000.00,
            'assigned_agent_id' => $agent->id,
            'property_interest_id' => $properties[0]->id,
            'next_followup_at' => now()->addDays(2),
            'created_by' => $admin->id,
        ]);

        LeadActivity::create([
            'lead_id' => $lead1->id,
            'user_id' => $agent->user_id,
            'activity_type' => 'Site Visit',
            'summary' => 'Physical inspection of Masaki Villa with CEO and Legal Counsel',
            'details' => 'Client expressed great satisfaction with the ocean view and construction quality. Requested draft contract terms.',
            'scheduled_at' => now()->subDays(1),
            'completed_at' => now()->subDays(1),
        ]);

        $lead2 = Lead::create([
            'organization_id' => $org->id,
            'branch_id' => $darBranch->id,
            'customer_id' => $c2->id,
            'title' => 'Interested in 3-Bed Mtumba Apartment in Dodoma',
            'source' => 'Instagram',
            'stage' => 'Qualified',
            'priority' => 'Medium',
            'estimated_value' => 180000000.00,
            'assigned_agent_id' => $agent->id,
            'property_interest_id' => $properties[3]->id,
            'next_followup_at' => now()->addDays(4),
            'created_by' => $admin->id,
        ]);

        // 3. Appointments
        Appointment::create([
            'organization_id' => $org->id,
            'branch_id' => $darBranch->id,
            'property_id' => $properties[0]->id,
            'customer_id' => $c1->id,
            'agent_id' => $agent->id,
            'appointment_number' => 'APPT-2026-001',
            'scheduled_at' => now()->addDays(2)->setHour(14)->setMinute(0),
            'duration_minutes' => 60,
            'meeting_type' => 'Site Viewing',
            'status' => 'Confirmed',
            'notes' => 'Contract review meeting on-site.',
        ]);

        // 4. Reservations
        Reservation::create([
            'organization_id' => $org->id,
            'branch_id' => $darBranch->id,
            'property_id' => $properties[3]->id,
            'customer_id' => $c2->id,
            'agent_id' => $agent->id,
            'reservation_number' => 'RESV-2026-001',
            'reservation_fee' => 5000000.00,
            'deposit_paid' => 5000000.00,
            'reserved_from' => now()->toDateString(),
            'reserved_until' => now()->addDays(14)->toDateString(),
            'status' => 'Active',
            'notes' => 'Holding unit for 14 days pending bank loan disbursement.',
            'created_by' => $admin->id,
        ]);

        // 5. Sales Deal & Installment Schedule
        $deal = SalesDeal::create([
            'organization_id' => $org->id,
            'branch_id' => $darBranch->id,
            'deal_number' => 'DEAL-2026-001',
            'property_id' => $properties[0]->id,
            'customer_id' => $c1->id,
            'agent_id' => $agent->id,
            'sale_price' => 1250000000.00,
            'payment_plan_type' => 'Installment',
            'total_installments' => 3,
            'agreement_date' => now()->subDays(10)->toDateString(),
            'closing_date' => now()->addMonths(3)->toDateString(),
            'commission_rate' => 5.00,
            'commission_amount' => 62500000.00, // 62.5M TZS commission
            'status' => 'Active',
            'notes' => '3-part installment schedule agreed: 50% down payment, 30% milestone, 20% final on deed transfer.',
            'created_by' => $admin->id,
        ]);

        // 6. Invoices & Payments
        $inv1 = Invoice::create([
            'organization_id' => $org->id,
            'branch_id' => $darBranch->id,
            'invoice_number' => 'INV-2026-001',
            'customer_id' => $c1->id,
            'sales_deal_id' => $deal->id,
            'property_id' => $properties[0]->id,
            'issue_date' => now()->subDays(10)->toDateString(),
            'due_date' => now()->subDays(3)->toDateString(),
            'subtotal' => 625000000.00, // 50% down payment
            'tax_rate' => 0.00,
            'tax_amount' => 0.00,
            'discount_amount' => 0.00,
            'total_amount' => 625000000.00,
            'paid_amount' => 625000000.00,
            'balance_due' => 0.00,
            'currency' => 'TZS',
            'status' => 'Paid',
            'notes' => 'Down Payment (50%) for Masaki Oceanview Villa purchase.',
            'created_by' => $admin->id,
        ]);

        $inv1->items()->create([
            'description' => 'Down Payment - 50% of Contract Value (Masaki Villa)',
            'quantity' => 1.00,
            'unit_price' => 625000000.00,
            'total_amount' => 625000000.00,
        ]);

        Payment::create([
            'organization_id' => $org->id,
            'branch_id' => $darBranch->id,
            'payment_number' => 'PAY-2026-001',
            'invoice_id' => $inv1->id,
            'customer_id' => $c1->id,
            'amount' => 625000000.00,
            'currency' => 'TZS',
            'payment_date' => now()->subDays(5)->toDateString(),
            'payment_method' => 'Bank Transfer',
            'reference_number' => 'CRDB-EFT-9912004',
            'status' => 'Completed',
            'notes' => 'Direct RTGS transfer from Serengeti Logistics corporate account.',
            'recorded_by' => $admin->id,
        ]);

        // 7. Land Survey & GIS Project (BM-008)
        $surveyor = User::where('email', 'surveyor@rehospace.com')->first();
        $survey = SurveyProject::create([
            'organization_id' => $org->id,
            'branch_id' => $darBranch->id,
            'project_code' => 'SURV-2026-001',
            'project_name' => 'Mount Meru Estate 10-Acre Cadastral Cadastre Boundary Survey',
            'land_parcel_id' => 1,
            'property_id' => $properties[2]->id,
            'location_name' => 'Ngaramtoni, Arusha',
            'total_area' => 10.0000,
            'area_unit' => 'Acres',
            'lead_surveyor_id' => $surveyor->id,
            'surveyor_license_number' => 'NCLS-TZ-0412',
            'status' => 'Beaconing',
            'start_date' => now()->subDays(7)->toDateString(),
            'expected_completion_date' => now()->addDays(14)->toDateString(),
            'description' => 'Comprehensive cadastral perimeter boundary verification, concrete beacon monuments placement, and mutation computation for title deed preparation.',
            'created_by' => $admin->id,
        ]);

        // Add Beacons
        SurveyBeacon::create(['survey_project_id' => $survey->id, 'beacon_number' => 'BM-01', 'latitude' => -3.32800000, 'longitude' => 36.65400000, 'northing' => 9632140.20, 'easting' => 239410.15, 'elevation' => 1420.50, 'beacon_type' => 'Concrete Pillar', 'condition' => 'Good']);
        SurveyBeacon::create(['survey_project_id' => $survey->id, 'beacon_number' => 'BM-02', 'latitude' => -3.32800000, 'longitude' => 36.65800000, 'northing' => 9632140.10, 'easting' => 239854.40, 'elevation' => 1422.10, 'beacon_type' => 'Concrete Pillar', 'condition' => 'Good']);
        SurveyBeacon::create(['survey_project_id' => $survey->id, 'beacon_number' => 'BM-03', 'latitude' => -3.33200000, 'longitude' => 36.65800000, 'northing' => 9631698.80, 'easting' => 239854.30, 'elevation' => 1418.90, 'beacon_type' => 'Concrete Pillar', 'condition' => 'Good']);
        SurveyBeacon::create(['survey_project_id' => $survey->id, 'beacon_number' => 'BM-04', 'latitude' => -3.33200000, 'longitude' => 36.65400000, 'northing' => 9631698.90, 'easting' => 239410.20, 'elevation' => 1419.40, 'beacon_type' => 'Concrete Pillar', 'condition' => 'Good']);

        // Milestones
        SurveyMilestone::create(['survey_project_id' => $survey->id, 'milestone_name' => 'Initial GPS Reconnaissance & Control Point Setup', 'sequence' => 1, 'status' => 'Completed', 'due_date' => now()->subDays(4)->toDateString(), 'completed_at' => now()->subDays(4), 'approved_by' => $admin->id, 'remarks' => 'Control points established with dual-frequency RTK GNSS.']);
        SurveyMilestone::create(['survey_project_id' => $survey->id, 'milestone_name' => 'Permanent Beacon Monuments Installation', 'sequence' => 2, 'status' => 'In Progress', 'due_date' => now()->addDays(3)->toDateString(), 'remarks' => '4 corner concrete pillars installed, currently placing intermediate boundary pins.']);
        SurveyMilestone::create(['survey_project_id' => $survey->id, 'milestone_name' => 'Ministry Cadastral Computation & Deed Plan Approval', 'sequence' => 3, 'status' => 'Pending', 'due_date' => now()->addDays(12)->toDateString(), 'remarks' => 'Submission to Ministry of Lands Zonal Office.']);

        // 8. Expenses
        Expense::create([
            'organization_id' => $org->id,
            'branch_id' => $darBranch->id,
            'property_id' => $properties[0]->id,
            'expense_number' => 'EXP-2026-001',
            'category' => 'Marketing',
            'title' => 'Professional Drone Videography & 3D Virtual Tour Creation',
            'amount' => 1800000.00,
            'currency' => 'TZS',
            'expense_date' => now()->subDays(8)->toDateString(),
            'payee' => 'SkyMedia Visuals Tanzania',
            'status' => 'Paid',
            'approved_by' => $admin->id,
            'recorded_by' => $admin->id,
        ]);
    }
}
