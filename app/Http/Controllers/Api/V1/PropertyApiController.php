<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Services\Property\PropertyService;
use Illuminate\Http\Request;

class PropertyApiController extends Controller
{
    public function __construct(protected PropertyService $propertyService) {}

    public function index(Request $request)
    {
        $query = Property::with(['propertyType', 'owner', 'branch', 'units', 'landParcel', 'media.mediaFile']);

        if ($request->filled('type_id')) {
            $query->where('property_type_id', $request->type_id);
        }
        if ($request->filled('listing_type')) {
            $query->where('listing_type', $request->listing_type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }

        $properties = $query->paginate($request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $properties,
        ]);
    }

    public function show(Property $property)
    {
        $property->load(['propertyType', 'owner', 'branch', 'units', 'landParcel.surveyProjects', 'amenities', 'media.mediaFile']);

        return response()->json([
            'success' => true,
            'data' => $property,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'property_type_id' => 'required|exists:property_types,id',
            'listing_type' => 'required|in:Sale,Rent,Lease,Joint Venture',
            'status' => 'required',
            'price' => 'nullable|numeric',
            'rent_price' => 'nullable|numeric',
            'address' => 'required|string',
            'city' => 'required|string',
            'bedrooms' => 'nullable|integer',
            'bathrooms' => 'nullable|integer',
            'area_size' => 'nullable|numeric',
            'area_unit' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $property = $this->propertyService->createProperty($data);

        return response()->json([
            'success' => true,
            'message' => 'Property created.',
            'data' => $property,
        ], 201);
    }

    public function update(Request $request, Property $property)
    {
        $data = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'price' => 'nullable|numeric',
            'rent_price' => 'nullable|numeric',
            'status' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $this->propertyService->updateProperty($property, $data);

        return response()->json([
            'success' => true,
            'message' => 'Property updated.',
            'data' => $property,
        ]);
    }

    public function destroy(Property $property)
    {
        $property->delete();

        return response()->json([
            'success' => true,
            'message' => 'Property deleted.',
        ]);
    }
}
