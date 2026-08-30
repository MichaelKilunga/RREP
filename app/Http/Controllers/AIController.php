<?php

namespace App\Http\Controllers;

use App\Models\AiChatSession;
use App\Models\Property;
use App\Services\AI\AIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AIController extends Controller
{
    public function __construct(protected AIService $aiService) {}

    public function chat()
    {
        $sessions = AiChatSession::where('user_id', Auth::id())->with('interactions')->latest()->get();
        $properties = Property::all();

        return view('ai.chat', compact('sessions', 'properties'));
    }

    public function ask(Request $request)
    {
        $request->validate(['prompt' => 'required|string']);

        $result = $this->aiService->ask($request->prompt, $request->session_id);

        return response()->json([
            'success' => true,
            'response' => $result['response'],
            'session_id' => $result['session_id'],
        ]);
    }
}
