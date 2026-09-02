<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\LoyaltyPointTransaction;
use App\Models\LoyaltyReward;
use App\Models\LoyaltyRule;
use App\Services\LoyaltyService;
use Illuminate\Http\Request;

class LoyaltyController extends Controller
{
    public function index()
    {
        $rules = LoyaltyRule::orderBy('sort_order', 'asc')->get();
        if ($rules->isEmpty()) {
            LoyaltyService::seedDefaultRules();
            $rules = LoyaltyRule::orderBy('sort_order', 'asc')->get();
        }

        $rewards = LoyaltyReward::with(['customer', 'rule', 'redeemer'])
            ->latest()
            ->paginate(20);

        $transactions = LoyaltyPointTransaction::with('customer')
            ->latest()
            ->take(30)
            ->get();

        $stats = [
            'total_points_issued' => LoyaltyPointTransaction::where('points', '>', 0)->sum('points'),
            'total_rewards' => LoyaltyReward::count(),
            'active_rewards' => LoyaltyReward::where('status', 'active')->count(),
            'redeemed_rewards' => LoyaltyReward::where('status', 'redeemed')->count(),
            'total_members' => Customer::where('loyalty_points', '>', 0)->count(),
        ];

        return view('loyalty.index', compact('rules', 'rewards', 'transactions', 'stats'));
    }

    public function storeRule(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code_prefix' => 'required|string|max:20',
            'min_points' => 'required|integer|min:0',
            'min_transactions' => 'required|integer|min:0',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0.1',
            'validity_days' => 'required|integer|min:1',
            'sms_template' => 'nullable|string|max:1000',
        ]);

        LoyaltyRule::create([
            'name' => $request->name,
            'code_prefix' => strtoupper($request->code_prefix),
            'min_points' => $request->min_points,
            'min_transactions' => $request->min_transactions,
            'discount_type' => $request->discount_type,
            'discount_value' => $request->discount_value,
            'validity_days' => $request->validity_days,
            'sms_template' => $request->sms_template,
            'is_active' => true,
            'sort_order' => (LoyaltyRule::max('sort_order') ?? 0) + 1,
        ]);

        return back()->with('success', 'New Loyalty Tier Rule created successfully!');
    }

    public function updateRule(Request $request, LoyaltyRule $rule)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code_prefix' => 'required|string|max:20',
            'min_points' => 'required|integer|min:0',
            'min_transactions' => 'required|integer|min:0',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0.1',
            'validity_days' => 'required|integer|min:1',
            'sms_template' => 'nullable|string|max:1000',
            'is_active' => 'nullable|boolean',
        ]);

        $rule->update([
            'name' => $request->name,
            'code_prefix' => strtoupper($request->code_prefix),
            'min_points' => $request->min_points,
            'min_transactions' => $request->min_transactions,
            'discount_type' => $request->discount_type,
            'discount_value' => $request->discount_value,
            'validity_days' => $request->validity_days,
            'sms_template' => $request->sms_template,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', "Loyalty Rule '{$rule->name}' updated!");
    }

    public function redeemVoucher(Request $request)
    {
        $request->validate([
            'reward_code' => 'required|string|max:50',
        ]);

        $result = LoyaltyService::redeemRewardCode($request->reward_code, auth()->id());

        if ($result['status'] === 'success') {
            return back()->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
    }

    public function adjustPoints(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'points' => 'required|integer',
            'reason' => 'required|string|max:255',
        ]);

        $customer = Customer::findOrFail($request->customer_id);
        $points = (int) $request->points;

        $customer->loyalty_points = max(0, ($customer->loyalty_points ?? 0) + $points);
        if ($points > 0) {
            $customer->lifetime_points = ($customer->lifetime_points ?? 0) + $points;
        }
        $customer->save();

        LoyaltyPointTransaction::create([
            'customer_id' => $customer->id,
            'type' => 'manual_adjustment',
            'points' => $points,
            'description' => "Manual adjustment: {$request->reason}",
            'created_by' => auth()->id(),
        ]);

        LoyaltyService::evaluateAndRewardCustomer($customer);

        return back()->with('success', "Updated points for {$customer->full_name}. New balance: {$customer->loyalty_points} pts.");
    }

    public function scanAll()
    {
        $res = LoyaltyService::scanAndDispatchRewardsAll();

        return back()->with('success', $res['message']);
    }
}
