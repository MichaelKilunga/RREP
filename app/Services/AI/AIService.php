<?php

namespace App\Services\AI;

use App\Models\AiChatSession;
use App\Models\AiInteraction;
use Illuminate\Support\Facades\Auth;

class AIService
{
    protected AIAdapterInterface $adapter;

    public function __construct()
    {
        $this->adapter = new GeminiAIAdapter;
    }

    public function ask(string $prompt, ?int $sessionId = null): array
    {
        $session = null;
        if ($sessionId) {
            $session = AiChatSession::find($sessionId);
        }

        if (! $session && Auth::check()) {
            $session = AiChatSession::create([
                'user_id' => Auth::id(),
                'organization_id' => Auth::user()->organization_id,
                'session_title' => mb_substr($prompt, 0, 40).'...',
            ]);
        }

        $response = $this->adapter->generateText($prompt);

        $interaction = null;
        if ($session) {
            $interaction = AiInteraction::create([
                'ai_chat_session_id' => $session->id,
                'user_id' => Auth::id(),
                'prompt' => $prompt,
                'response' => $response,
                'model' => env('GEMINI_MODEL', 'gemini-1.5-flash'),
                'feature' => 'Chat',
            ]);
        }

        return [
            'session_id' => $session ? $session->id : null,
            'response' => $response,
            'interaction_id' => $interaction ? $interaction->id : null,
        ];
    }

    public function generateDescription(array $propertyData): string
    {
        return $this->adapter->generatePropertyDescription($propertyData);
    }

    public function estimateValuation(array $propertyData): array
    {
        return $this->adapter->estimatePropertyValuation($propertyData);
    }
}
