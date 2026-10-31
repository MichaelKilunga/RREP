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

    public function index(Request $request)
    {
        $reservations = Reservation::with(['property', 'unit', 'customer', 'agent.user', 'landParcel.project'])
            ->when($request->filled('status'), function ($q) use ($request) {
                if ($request->status === 'overdue') {
                    $q->where('status', 'Active')->whereDate('reserved_until', '<', now()->toDateString());
                } elseif ($request->status === 'expiring') {
                    $q->where('status', 'Active')->whereBetween('reserved_until', [now()->toDateString(), now()->addDays(3)->toDateString()]);
                } else {
                    $q->where('status', $request->status);
                }
            })
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = $request->q;
                $q->where(fn ($w) => $w->where('reservation_number', 'like', "%{$term}%")
                    ->orWhereHas('customer', fn ($c) => $c->where('first_name', 'like', "%{$term}%")->orWhere('last_name', 'like', "%{$term}%")->orWhere('phone', 'like', "%{$term}%"))
                    ->orWhereHas('landParcel', fn ($p) => $p->where('parcel_number', 'like', "%{$term}%")));
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $active = Reservation::where('status', 'Active');
        $stats = [
            'active' => (clone $active)->count(),
            'expiring' => (clone $active)->whereBetween('reserved_until', [now()->toDateString(), now()->addDays(3)->toDateString()])->count(),
            'overdue' => (clone $active)->whereDate('reserved_until', '<', now()->toDateString())->count(),
            'deposits' => (float) (clone $active)->sum('deposit_paid'),
        ];
        $properties = Property::where('status', 'Available')->get();
        $projectPlots = LandParcel::with('project')
            ->whereNotNull('real_estate_project_id')
            ->where('status', 'Available')
            ->orderBy('real_estate_project_id')
            ->orderBy('parcel_number')
            ->get();
        $customers = Customer::all();
        $agents = Agent::with('user')->get();

        return view('reservations.index', compact('reservations', 'properties', 'projectPlots', 'customers', 'agents', 'stats'));
    }

    public function extend(Request $request, Reservation $reservation)
    {
        $data = $request->validate([
            'reserved_until' => 'required|date|after_or_equal:today',
            'deposit_paid' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        abort_unless($reservation->status === 'Active', 422, __('ops.reservation_not_active'));

        $reservation->update(array_filter([
            'reserved_until' => $data['reserved_until'],
            'deposit_paid' => $data['deposit_paid'] ?? null,
            'notes' => $data['notes'] ?? null,
        ], fn ($v) => $v !== null));

        return back()->with('success', __('ops.reservation_extended', ['date' => $reservation->reserved_until->format('d M Y')]));
    }

    /**
     * The buyer completed: the hold becomes a sale and the plot/property is marked Sold.
     */
    public function convert(Reservation $reservation)
    {
        abort_unless($reservation->status === 'Active', 422, __('ops.reservation_not_active'));

        $reservation->update(['status' => 'Converted']);
        $reservation->property?->update(['status' => 'Sold']);
        if ($reservation->landParcel) {
            $reservation->landParcel->update(['status' => 'Sold']);
            $reservation->landParcel->project?->syncPlotCounts();
        }

        return back()->with('success', __('ops.reservation_converted'));
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
