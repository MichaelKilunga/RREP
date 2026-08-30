<?php

namespace App\Services\CRM;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\Reservation;
use App\Models\SalesDeal;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CRMService
{
    public function createCustomer(array $data): Customer
    {
        return Customer::create($data);
    }

    public function createLead(array $data): Lead
    {
        return Lead::create($data);
    }

    public function logActivity(Lead $lead, array $activityData): LeadActivity
    {
        $activityData['user_id'] = Auth::id() ?? 1;

        return $lead->activities()->create($activityData);
    }

    public function createReservation(array $data): Reservation
    {
        $data['reservation_number'] = 'RESV-'.date('Ymd').'-'.strtoupper(Str::random(4));

        return Reservation::create($data);
    }

    public function createAppointment(array $data): Appointment
    {
        $data['appointment_number'] = 'APPT-'.date('Ymd').'-'.strtoupper(Str::random(4));

        return Appointment::create($data);
    }

    public function createSalesDeal(array $data): SalesDeal
    {
        $data['deal_number'] = 'DEAL-'.date('Ymd').'-'.strtoupper(Str::random(4));

        return SalesDeal::create($data);
    }
}
