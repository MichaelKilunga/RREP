<?php

namespace App\Http\Controllers;

use App\Services\Push\WebPushService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PushController extends Controller
{
    public function subscribe(Request $request): JsonResponse
    {
        $data = $request->validate([
            'endpoint' => 'required|url|max:1000',
            'p256dh' => 'required|string|max:255',
            'auth' => 'required|string|max:255',
        ]);

        WebPushService::storeSubscription($request->user()->id, $data['endpoint'], $data['p256dh'], $data['auth']);

        return response()->json(['ok' => true]);
    }

    public function unsubscribe(Request $request): JsonResponse
    {
        $request->validate(['endpoint' => 'required|string|max:1000']);

        WebPushService::removeSubscription($request->input('endpoint'));

        return response()->json(['ok' => true]);
    }
}
