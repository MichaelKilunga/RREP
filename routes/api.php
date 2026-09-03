<?php

use App\Http\Controllers\Api\V1\AnalyticsApiController;
use App\Http\Controllers\Api\V1\AuthApiController;
use App\Http\Controllers\Api\V1\CustomerApiController;
use App\Http\Controllers\Api\V1\InvoiceApiController;
use App\Http\Controllers\Api\V1\LeadApiController;
use App\Http\Controllers\Api\V1\PropertyApiController;
use App\Http\Controllers\Api\V1\ReservationApiController;
use App\Http\Controllers\Api\V1\SurveyApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| RehoSpace Real Estate Platform - REST API V1
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->name('api.v1.')->group(function () {
    // Public Endpoints
    Route::post('auth/login', [AuthApiController::class, 'login']);
    Route::get('marketplace/properties', [PropertyApiController::class, 'index']);
    Route::get('marketplace/properties/{property}', [PropertyApiController::class, 'show']);
    Route::post('marketplace/inquiries', [LeadApiController::class, 'storePublicInquiry']);

    // Protected API Endpoints (Sanctum)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('user', function (Request $request) {
            return response()->json([
                'success' => true,
                'data' => $request->user()->load(['organization', 'branch', 'roles']),
            ]);
        });
        Route::post('auth/logout', [AuthApiController::class, 'logout']);

        // Properties API
        Route::apiResource('properties', PropertyApiController::class)->except(['index', 'show']);

        // CRM & Leads
        Route::apiResource('customers', CustomerApiController::class);
        Route::apiResource('leads', LeadApiController::class);
        Route::apiResource('reservations', ReservationApiController::class);

        // Finance & Invoicing
        Route::apiResource('invoices', InvoiceApiController::class);

        // Land Survey & GIS
        Route::get('surveys', [SurveyApiController::class, 'index']);
        Route::get('surveys/{survey}/geojson', [SurveyApiController::class, 'geojson']);

        // Analytics & BI
        Route::get('analytics/overview', [AnalyticsApiController::class, 'overview']);
    });
});
