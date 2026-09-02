<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\LandParcel;
use App\Models\Lease;
use App\Models\LoyaltyPointTransaction;
use App\Models\LoyaltyReward;
use App\Models\MediaFile;
use App\Models\Organization;
use App\Models\Property;
use App\Models\PropertyMedia;
use App\Models\PropertyOwner;
use App\Models\PropertyType;
use App\Models\Reservation;
use App\Models\SurveyProject;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PortalController extends Controller
{
    public function clientPortal()
    {
        $user = auth()->user();
        $customer = null;

        if ($user) {
            $customer = Customer::where('user_id', $user->id)
                ->orWhere('email', $user->email)
                ->first();
        }

        if (! $customer) {
            $customer = Customer::with(['loyaltyRewards', 'loyaltyTransactions'])->first()
                ?: new Customer([
                    'first_name' => 'Demo',
                    'last_name' => 'Client',
                    'loyalty_points' => 350,
                    'loyalty_tier' => 'Gold Estate Holder',
                ]);
        }

        $savedProperties = Property::where('is_featured', true)->take(4)->get();
        $appointments = Appointment::where('customer_id', $customer->id)->with('property')->latest()->get();
        $reservations = Reservation::where('customer_id', $customer->id)->with('property')->latest()->get();
        $invoices = Invoice::where('customer_id', $customer->id)->with(['property', 'items', 'payments'])->latest()->get();
        $surveyRequests = SurveyProject::where('customer_id', $customer->id)->with(['beacons', 'milestones'])->latest()->get();

        $loyaltyRewards = LoyaltyReward::where('customer_id', $customer->id)->latest()->get();
        $loyaltyTransactions = LoyaltyPointTransaction::where('customer_id', $customer->id)->latest()->take(20)->get();

        // Library files (Title deeds, survey blueprints)
        $libraryFiles = MediaFile::where(function ($q) {
            $q->where('category', 'blueprints')
                ->orWhere('category', 'title_deeds')
                ->orWhere('category', 'surveys')
                ->orWhere('category', 'documents');
        })->latest()->take(10)->get();

        return view('portals.client', compact(
            'customer',
            'savedProperties',
            'appointments',
            'reservations',
            'invoices',
            'surveyRequests',
            'loyaltyRewards',
            'loyaltyTransactions',
            'libraryFiles'
        ));
    }

    public function ownerPortal()
    {
        $user = auth()->user();
        $owner = null;

        if ($user) {
            $owner = PropertyOwner::where('user_id', $user->id)
                ->orWhere('email', $user->email)
                ->first();
        }

        if (! $owner) {
            $owner = PropertyOwner::with('properties.units')->first()
                ?: new PropertyOwner(['first_name' => 'Demo', 'last_name' => 'Owner']);
        }

        $properties = Property::where('property_owner_id', $owner->id)->with(['propertyType', 'landParcel'])->latest()->get();
        $leases = Lease::whereIn('property_id', $properties->pluck('id'))->with(['property', 'tenant.customer'])->get();
        $propertyTypes = PropertyType::where('is_active', true)->get();

        return view('portals.owner', compact('owner', 'properties', 'leases', 'propertyTypes'));
    }

    /**
     * Seller / Owner Plot Submission Workflow
     */
    public function submitOwnerProperty(Request $request)
    {
        if (! is_module_enabled('property_owner_submissions', true)) {
            return back()->with('error', 'Property owner submissions are currently disabled.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'property_type_id' => 'required|exists:property_types,id',
            'listing_type' => 'required|in:Sale,Rent,Lease',
            'price' => 'required|numeric|min:0',
            'area_size' => 'required|numeric|min:0.1',
            'area_unit' => 'required|in:Sqm,Acres,Hectares',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'zoning' => 'nullable|string|max:50',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'deed_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'property_image' => 'nullable|image|max:5120',
            'description' => 'required|string|max:3000',
        ]);

        $user = auth()->user();
        $orgId = current_organization()?->id ?: Organization::first()?->id ?: 1;
        $branchId = current_branch()?->id ?: Branch::first()?->id ?: 1;

        $owner = PropertyOwner::firstOrCreate(
            ['user_id' => $user?->id, 'email' => $user?->email ?: 'owner@avenix.co.tz'],
            [
                'organization_id' => $orgId,
                'first_name' => $user?->first_name ?: 'Land',
                'last_name' => $user?->last_name ?: 'Owner',
                'phone' => $user?->phone ?: '255700000000',
                'kyc_status' => 'Pending',
            ]
        );

        $propertyCode = 'PLT-'.strtoupper(Str::random(6));
        $slug = Str::slug($request->title).'-'.strtolower(Str::random(5));

        // Handle file uploads
        $deedUrl = null;
        if ($request->hasFile('deed_document')) {
            $path = $request->file('deed_document')->store('deed_proofs', 'public');
            $deedUrl = '/storage/'.$path;
        }

        $property = Property::create([
            'organization_id' => $orgId,
            'branch_id' => $branchId,
            'property_type_id' => $request->property_type_id,
            'property_owner_id' => $owner->id,
            'title' => $request->title,
            'slug' => $slug,
            'property_code' => $propertyCode,
            'listing_type' => $request->listing_type,
            'status' => 'Under Review',
            'submission_status' => 'Under Review',
            'price' => $request->price,
            'currency' => 'TZS',
            'address' => $request->address,
            'city' => $request->city,
            'area_size' => $request->area_size,
            'area_unit' => $request->area_unit,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'is_published' => false,
            'description' => $request->description,
            'deed_document_url' => $deedUrl,
            'created_by' => auth()->id(),
        ]);

        // Create Land Parcel record
        LandParcel::create([
            'property_id' => $property->id,
            'parcel_number' => $propertyCode,
            'acreage' => $request->area_unit === 'Acres' ? $request->area_size : ($request->area_size / 4046.86),
            'zoning' => $request->zoning ?: 'Residential',
            'title_deed_type' => 'Right of Occupancy',
        ]);

        if ($request->hasFile('property_image')) {
            $imgPath = $request->file('property_image')->store('properties', 'public');
            $mediaFile = MediaFile::create([
                'disk' => 'public',
                'file_path' => $imgPath,
                'file_name' => $request->file('property_image')->getClientOriginalName(),
                'file_size' => $request->file('property_image')->getSize(),
                'mime_type' => $request->file('property_image')->getMimeType(),
                'collection_name' => 'property_images',
            ]);

            PropertyMedia::create([
                'property_id' => $property->id,
                'media_file_id' => $mediaFile->id,
                'is_primary' => true,
            ]);
        }

        return back()->with('success', "Plot listing #{$propertyCode} submitted successfully! It is now Under Review by administration for deed proof validation.");
    }
}
