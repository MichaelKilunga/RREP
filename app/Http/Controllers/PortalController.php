<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Lease;
use App\Models\Property;
use App\Models\PropertyOwner;
use App\Models\Reservation;

class PortalController extends Controller
{
    public function clientPortal()
    {
        $customer = Customer::first() ?? new Customer(['first_name' => 'Demo', 'last_name' => 'Client']);
        $savedProperties = Property::where('is_featured', true)->take(4)->get();
        $appointments = Appointment::where('customer_id', $customer->id)->with('property')->get();
        $reservations = Reservation::where('customer_id', $customer->id)->with('property')->get();
        $invoices = Invoice::where('customer_id', $customer->id)->with(['property', 'payments'])->get();

        return view('portals.client', compact('customer', 'savedProperties', 'appointments', 'reservations', 'invoices'));
    }

    public function ownerPortal()
    {
        $owner = PropertyOwner::with('properties.units')->first() ?? new PropertyOwner(['first_name' => 'Demo', 'last_name' => 'Owner']);
        $properties = $owner->properties ?? collect();
        $leases = Lease::whereIn('property_id', $properties->pluck('id'))->with(['property', 'tenant.customer'])->get();

        return view('portals.owner', compact('owner', 'properties', 'leases'));
    }
}
