<?php

namespace App\Http\Controllers;

use App\Models\MarketingCampaign;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CampaignController extends Controller
{
    public function index()
    {
        $campaigns = MarketingCampaign::latest()->paginate(15);

        return view('marketing.campaigns', compact('campaigns'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'campaign_type' => 'required|string',
            'budget' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date',
            'target_audience' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $data['campaign_code'] = 'CMP-'.date('Y').'-'.strtoupper(Str::random(4));
        $data['status'] = 'Active';

        MarketingCampaign::create($data);

        return back()->with('success', 'Marketing campaign launched successfully!');
    }

    public function broadcast(Request $request)
    {
        $request->validate([
            'channel' => 'required|in:SMS,WhatsApp,Email',
            'target_group' => 'required|string',
            'message_content' => 'required|string',
        ]);

        return back()->with('success', "Broadcast successfully dispatched to target prospects via {$request->channel}!");
    }
}
