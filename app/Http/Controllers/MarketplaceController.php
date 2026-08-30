<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Lead;
use App\Models\Property;
use App\Models\PropertyType;
use Illuminate\Http\Request;

class MarketplaceController extends Controller
{
    public function index(Request $request)
    {
        $query = Property::with(['propertyType', 'media.mediaFile', 'branch'])->where('is_published', true);

        if ($request->filled('type')) {
            $query->where('property_type_id', $request->type);
        }
        if ($request->filled('listing_type')) {
            $query->where('listing_type', $request->listing_type);
        }
        if ($request->filled('city')) {
            $query->where('city', 'like', '%'.$request->city.'%');
        }
        if ($request->filled('bedrooms')) {
            $query->where('bedrooms', '>=', $request->bedrooms);
        }
        if ($request->filled('max_price')) {
            $query->where(function ($q) use ($request) {
                $q->where('price', '<=', $request->max_price)
                    ->orWhere('rent_price', '<=', $request->max_price);
            });
        }

        $properties = $query->latest()->paginate(12);
        $types = PropertyType::where('is_active', true)->get();
        $featured = Property::with(['propertyType', 'media.mediaFile'])->where('is_featured', true)->take(4)->get();

        return view('marketplace.index', compact('properties', 'types', 'featured'));
    }

    public function show(Property $property)
    {
        $property->increment('views_count');
        $property->load(['propertyType', 'units', 'landParcel', 'amenities', 'media.mediaFile', 'branch']);
        $similar = Property::where('property_type_id', $property->property_type_id)
            ->where('id', '!=', $property->id)
            ->take(3)
            ->get();

        return view('marketplace.show', compact('property', 'similar'));
    }

    public function submitInquiry(Request $request)
    {
        $request->validate([
            'property_id' => 'required|exists:properties,id',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'required|string',
            'message' => 'required|string',
        ]);

        $nameParts = explode(' ', $request->name, 2);
        $customer = Customer::firstOrCreate(
            ['phone' => $request->phone],
            [
                'first_name' => $nameParts[0],
                'last_name' => $nameParts[1] ?? '',
                'email' => $request->email,
                'source' => 'Marketplace',
            ]
        );

        $property = Property::find($request->property_id);

        Lead::create([
            'customer_id' => $customer->id,
            'property_interest_id' => $property->id,
            'organization_id' => $property->organization_id,
            'branch_id' => $property->branch_id,
            'title' => "Marketplace Inquiry for {$property->title}",
            'source' => 'Website Marketplace',
            'stage' => 'New',
            'priority' => 'High',
            'estimated_value' => $property->price ?: $property->rent_price,
            'lost_reason' => $request->message,
        ]);

        return back()->with('success', 'Thank you! Your property inquiry has been received. Our agent will contact you shortly.');
    }
}
