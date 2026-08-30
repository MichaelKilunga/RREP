<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Customer;
use App\Models\Property;
use App\Models\Reservation;
use App\Services\CRM\CRMService;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    public function __construct(protected CRMService $crmService) {}

    public function index()
    {
        $reservations = Reservation::with(['property', 'unit', 'customer', 'agent.user'])->latest()->paginate(15);
        $properties = Property::where('status', 'Available')->get();
        $customers = Customer::all();
        $agents = Agent::with('user')->get();

        return view('reservations.index', compact('reservations', 'properties', 'customers', 'agents'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'property_id' => 'required|exists:properties,id',
            'property_unit_id' => 'nullable|exists:property_units,id',
            'customer_id' => 'required|exists:customers,id',
            'agent_id' => 'nullable|exists:agents,id',
            'reservation_fee' => 'required|numeric|min:0',
            'deposit_paid' => 'required|numeric|min:0',
            'reserved_from' => 'required|date',
            'reserved_until' => 'required|date|after_or_equal:reserved_from',
            'notes' => 'nullable|string',
        ]);

        $reservation = $this->crmService->createReservation($data);

        $reservation->property->update(['status' => 'Reserved']);

        return back()->with('success', 'Property reservation hold active!');
    }

    public function cancel(Request $request, Reservation $reservation)
    {
        $reservation->update([
            'status' => 'Cancelled',
            'cancellation_reason' => $request->input('reason', 'Cancelled by customer/agent'),
        ]);
        $reservation->property->update(['status' => 'Available']);

        return back()->with('info', 'Reservation hold cancelled.');
    }
}
