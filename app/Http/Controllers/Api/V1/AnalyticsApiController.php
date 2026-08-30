<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Analytics\AnalyticsService;

class AnalyticsApiController extends Controller
{
    public function __construct(protected AnalyticsService $analyticsService) {}

    public function overview()
    {
        return response()->json([
            'success' => true,
            'data' => $this->analyticsService->getDashboardMetrics(),
        ]);
    }
}
