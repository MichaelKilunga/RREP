<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Property;
use App\Services\CRM\CRMService;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function __construct(protected CRMService $crmService) {}

    public function index()
    {
        $appointments = Appointment::with(['property', 'customer', 'agent.user'])->latest('scheduled_at')->paginate(15);
        $properties = Property::all();
        $customers = Customer::all();
        $agents = Agent::with('user')->get();

        return view('appointments.index', compact('appointments', 'properties', 'customers', 'agents'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'property_id' => 'required|exists:properties,id',
            'customer_id' => 'required|exists:customers,id',
            'agent_id' => 'nullable|exists:agents,id',
            'scheduled_at' => 'required|date',
            'duration_minutes' => 'required|integer',
            'meeting_type' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $this->crmService->createAppointment($data);

        return back()->with('success', 'Viewing appointment scheduled!');
    }

    public function updateStatus(Request $request, Appointment $appointment)
    {
        $request->validate(['status' => 'required|string']);
        $appointment->update([
            'status' => $request->status,
            'feedback_score' => $request->input('feedback_score'),
            'feedback_notes' => $request->input('feedback_notes'),
        ]);

        return back()->with('success', 'Appointment status updated!');
    }
}
