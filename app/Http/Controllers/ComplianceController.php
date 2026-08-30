<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\PropertyOwner;
use Illuminate\Http\Request;

class ComplianceController extends Controller
{
    public function kycQueue()
    {
        $pendingCustomers = Customer::where('kyc_status', 'Pending')->get();
        $verifiedCustomers = Customer::where('kyc_status', 'Verified')->take(10)->get();
        $pendingOwners = PropertyOwner::where('kyc_status', 'Pending')->get();
        $verifiedOwners = PropertyOwner::where('kyc_status', 'Verified')->take(10)->get();

        return view('compliance.kyc', compact('pendingCustomers', 'verifiedCustomers', 'pendingOwners', 'verifiedOwners'));
    }

    public function verifyCustomer(Request $request, Customer $customer)
    {
        $status = $request->input('status', 'Verified');
        $customer->update(['kyc_status' => $status]);

        return back()->with('success', "Customer KYC status updated to {$status}.");
    }

    public function verifyOwner(Request $request, PropertyOwner $owner)
    {
        $status = $request->input('status', 'Verified');
        $owner->update(['kyc_status' => $status]);

        return back()->with('success', "Property Owner KYC status updated to {$status}.");
    }
}
