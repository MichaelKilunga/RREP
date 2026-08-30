<?php

namespace App\Http\Controllers;

use App\Models\Amenity;
use App\Models\Branch;
use App\Models\Property;
use App\Models\PropertyOwner;
use App\Models\PropertyType;
use App\Services\AI\AIService;
use App\Services\Property\PropertyService;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    public function __construct(
        protected PropertyService $propertyService,
        protected AIService $aiService
    ) {}

    public function index(Request $request)
    {
        $query = Property::with(['propertyType', 'owner', 'branch', 'units', 'landParcel']);

        if ($request->filled('type')) {
            $query->where('property_type_id', $request->type);
        }
        if ($request->filled('listing_type')) {
            $query->where('listing_type', $request->listing_type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('city')) {
            $query->where('city', 'like', '%'.$request->city.'%');
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('property_code', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            });
        }

        $properties = $query->latest()->paginate(15);
        $types = PropertyType::where('is_active', true)->get();
        $branches = Branch::all();

        return view('properties.index', compact('properties', 'types', 'branches'));
    }

    public function create()
    {
        $types = PropertyType::where('is_active', true)->get();
        $owners = PropertyOwner::all();
        $amenities = Amenity::all();
        $branches = Branch::all();

        return view('properties.create', compact('types', 'owners', 'amenities', 'branches'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'property_type_id' => 'required|exists:property_types,id',
            'property_owner_id' => 'nullable|exists:property_owners,id',
            'branch_id' => 'nullable|exists:branches,id',
            'listing_type' => 'required|in:Sale,Rent,Lease,Joint Venture',
            'status' => 'required|in:Available,Reserved,Under Contract,Sold,Leased,Maintenance,Off Market',
            'price' => 'nullable|numeric|min:0',
            'rent_price' => 'nullable|numeric|min:0',
            'rent_period' => 'nullable|string',
            'address' => 'required|string',
            'city' => 'required|string',
            'state' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'area_size' => 'nullable|numeric',
            'area_unit' => 'nullable|string',
            'bedrooms' => 'nullable|integer',
            'bathrooms' => 'nullable|integer',
            'floors' => 'nullable|integer',
            'parking_spaces' => 'nullable|integer',
            'year_built' => 'nullable|integer',
            'description' => 'nullable|string',
            'amenities' => 'nullable|array',
            'parcel_number' => 'nullable|string',
            'deed_number' => 'nullable|string',
            'title_deed_type' => 'nullable|string',
            'acreage' => 'nullable|numeric',
        ]);

        $property = $this->propertyService->createProperty($data);

        return redirect()->route('properties.show', $property)->with('success', 'Property successfully cataloged!');
    }

    public function show(Property $property)
    {
        $property->load(['propertyType', 'owner', 'branch', 'units', 'landParcel.surveyProjects', 'amenities', 'media.mediaFile', 'leads', 'reservations', 'salesDeals']);

        $valuation = null;
        if (module_enabled('ai')) {
            $valuation = $this->aiService->estimateValuation([
                'type' => $property->propertyType?->name,
                'area_size' => $property->area_size,
                'area_unit' => $property->area_unit,
                'location' => "{$property->address}, {$property->city}",
            ]);
        }

        return view('properties.show', compact('property', 'valuation'));
    }

    public function edit(Property $property)
    {
        $types = PropertyType::where('is_active', true)->get();
        $owners = PropertyOwner::all();
        $amenities = Amenity::all();
        $branches = Branch::all();

        return view('properties.edit', compact('property', 'types', 'owners', 'amenities', 'branches'));
    }

    public function update(Request $request, Property $property)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'property_type_id' => 'required|exists:property_types,id',
            'property_owner_id' => 'nullable|exists:property_owners,id',
            'branch_id' => 'nullable|exists:branches,id',
            'listing_type' => 'required|in:Sale,Rent,Lease,Joint Venture',
            'status' => 'required|in:Available,Reserved,Under Contract,Sold,Leased,Maintenance,Off Market',
            'price' => 'nullable|numeric|min:0',
            'rent_price' => 'nullable|numeric|min:0',
            'rent_period' => 'nullable|string',
            'address' => 'required|string',
            'city' => 'required|string',
            'state' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'area_size' => 'nullable|numeric',
            'area_unit' => 'nullable|string',
            'bedrooms' => 'nullable|integer',
            'bathrooms' => 'nullable|integer',
            'floors' => 'nullable|integer',
            'parking_spaces' => 'nullable|integer',
            'year_built' => 'nullable|integer',
            'description' => 'nullable|string',
            'amenities' => 'nullable|array',
        ]);

        $this->propertyService->updateProperty($property, $data);

        return redirect()->route('properties.show', $property)->with('success', 'Property successfully updated!');
    }

    public function destroy(Property $property)
    {
        $property->delete();

        return redirect()->route('properties.index')->with('success', 'Property moved to trash.');
    }

    public function generateAiDescription(Request $request)
    {
        $data = $request->only(['title', 'type', 'location', 'price', 'bedrooms', 'amenities']);
        $description = $this->aiService->generateDescription($data);

        return response()->json(['success' => true, 'description' => $description]);
    }
}
