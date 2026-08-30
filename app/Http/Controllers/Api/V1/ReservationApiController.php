<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Services\CRM\CRMService;
use Illuminate\Http\Request;

class ReservationApiController extends Controller
{
    public function __construct(protected CRMService $crmService) {}

    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => Reservation::with(['property', 'customer', 'agent.user'])->latest()->paginate(20),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'property_id' => 'required|exists:properties,id',
            'customer_id' => 'required|exists:customers,id',
            'reservation_fee' => 'required|numeric',
            'deposit_paid' => 'required|numeric',
            'reserved_from' => 'required|date',
            'reserved_until' => 'required|date',
        ]);

        $reservation = $this->crmService->createReservation($data);

        return response()->json([
            'success' => true,
            'data' => $reservation,
        ], 201);
    }
}
