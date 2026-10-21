<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Customer;
use App\Models\LandParcel;
use App\Models\Property;
use App\Models\Reservation;
use App\Services\CRM\CRMService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ReservationController extends Controller
{
    public function __construct(protected CRMService $crmService) {}

    public function index()
    {
        $reservations = Reservation::with(['property', 'unit', 'customer', 'agent.user', 'landParcel.project'])->latest()->paginate(15);
        $properties = Property::where('status', 'Available')->get();
        $projectPlots = LandParcel::with('project')
            ->whereNotNull('real_estate_project_id')
            ->where('status', 'Available')
            ->orderBy('real_estate_project_id')
            ->orderBy('parcel_number')
            ->get();
        $customers = Customer::all();
        $agents = Agent::with('user')->get();

        return view('reservations.index', compact('reservations', 'properties', 'projectPlots', 'customers', 'agents'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'reservable' => 'required|string',
            'property_unit_id' => 'nullable|exists:property_units,id',
            'customer_id' => 'required|exists:customers,id',
            'agent_id' => 'nullable|exists:agents,id',
            'reservation_fee' => 'required|numeric|min:0',
            'deposit_paid' => 'required|numeric|min:0',
            'reserved_from' => 'required|date',
            'reserved_until' => 'required|date|after_or_equal:reserved_from',
            'notes' => 'nullable|string',
        ]);

        [$kind, $id] = array_pad(explode('-', (string) $request->reservable, 2), 2, null);
        unset($data['reservable']);

        $property = null;
        $parcel = null;
        if ($kind === 'parcel') {
            $parcel = LandParcel::with('project')->where('status', 'Available')->find((int) $id);
            if (! $parcel) {
                throw ValidationException::withMessages(['reservable' => __('ops.plot_no_longer_available')]);
            }
            $data['land_parcel_id'] = $parcel->id;
        } else {
            $property = Property::find((int) $id);
            if (! $property) {
                throw ValidationException::withMessages(['reservable' => __('ops.plot_no_longer_available')]);
            }
            $data['property_id'] = $property->id;
        }

        $reservation = $this->crmService->createReservation($data);

        if ($parcel) {
            $parcel->update(['status' => 'Reserved']);
            $parcel->project?->syncPlotCounts();
            $holdLabel = trim('Kiwanja '.$parcel->parcel_number.' ('.($parcel->block_number ?: '-').')');
        } else {
            $property->update(['status' => 'Reserved']);
            $holdLabel = $property->property_code ?: $property->title;
        }

        // Booking-confirmation SMS to the buyer (Event A template); never block the hold on SMS failure.
        try {
            $customer = Customer::find($request->customer_id);
            if ($customer?->phone) {
                NotificationService::triggerEventA_BookingConfirmation($customer, $holdLabel, $reservation->reservation_number);
            }
        } catch (\Throwable $e) {
            Log::warning('Reservation SMS failed: '.$e->getMessage());
        }

        return back()->with('success', __('ops.reservation_placed'));
    }

    public function cancel(Request $request, Reservation $reservation)
    {
        $reservation->update([
            'status' => 'Cancelled',
            'cancellation_reason' => $request->input('reason', 'Cancelled by customer/agent'),
        ]);

        $reservation->property?->update(['status' => 'Available']);
        if ($reservation->landParcel) {
            $reservation->landParcel->update(['status' => 'Available']);
            $reservation->landParcel->project?->syncPlotCounts();
        }

        return back()->with('info', __('ops.reservation_cancelled'));
    }
}
