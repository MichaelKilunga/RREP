<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\SurveyProject;
use App\Services\GIS\GISService;

class SurveyApiController extends Controller
{
    public function __construct(protected GISService $gisService) {}

    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => SurveyProject::with(['property', 'leadSurveyor', 'beacons', 'milestones'])->latest()->paginate(20),
        ]);
    }

    public function geojson(SurveyProject $survey)
    {
        $geojson = $this->gisService->generateGeoJson($survey);

        return response()->json($geojson);
    }
}
