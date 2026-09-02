<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\SurveyProject;
use App\Models\User;
use App\Services\GIS\GISService;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class SurveyGISController extends Controller
{
    public function __construct(protected GISService $gisService) {}

    public function index()
    {
        $projects = SurveyProject::with(['property', 'leadSurveyor', 'beacons', 'milestones'])->latest()->paginate(15);
        $properties = Property::all();
        $surveyors = User::all();

        return view('survey.index', compact('projects', 'properties', 'surveyors'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'project_name' => 'required|string|max:255',
            'property_id' => 'nullable|exists:properties,id',
            'location_name' => 'required|string',
            'total_area' => 'nullable|numeric',
            'area_unit' => 'nullable|string',
            'lead_surveyor_id' => 'nullable|exists:users,id',
            'surveyor_license_number' => 'nullable|string',
            'start_date' => 'nullable|date',
            'expected_completion_date' => 'nullable|date',
            'description' => 'nullable|string',
        ]);

        $project = $this->gisService->createSurveyProject($data);

        return redirect()->route('survey.show', $project)->with('success', 'Cadastral survey project initialized!');
    }

    public function show(SurveyProject $survey)
    {
        $survey->load(['property.landParcel', 'leadSurveyor', 'beacons', 'milestones']);
        $geoJson = $this->gisService->generateGeoJson($survey);

        return view('survey.show', compact('survey', 'geoJson'));
    }

    public function addBeacon(Request $request, SurveyProject $survey)
    {
        $data = $request->validate([
            'beacon_number' => 'required|string|max:50',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'northing' => 'nullable|numeric',
            'easting' => 'nullable|numeric',
            'elevation' => 'nullable|numeric',
            'beacon_type' => 'nullable|string',
            'condition' => 'nullable|string',
        ]);

        $this->gisService->addBeacon($survey, $data);

        return back()->with('success', "Beacon {$data['beacon_number']} added to survey coordinates!");
    }

    public function map()
    {
        $projects = SurveyProject::with(['beacons', 'property'])->get();
        $features = [];

        foreach ($projects as $proj) {
            $geo = $this->gisService->generateGeoJson($proj);
            if (! empty($geo['features'])) {
                $features = array_merge($features, $geo['features']);
            }
        }

        $allGeoJson = [
            'type' => 'FeatureCollection',
            'features' => $features,
        ];

        return view('survey.map', compact('projects', 'allGeoJson'));
    }

    public function updateStatus(Request $request, SurveyProject $survey)
    {
        $request->validate([
            'status' => 'required|string|in:Planning,Fieldwork,Beaconing,Computations,Verification,Approved,Completed',
        ]);

        $oldStatus = $survey->status;
        $newStatus = $request->status;

        $survey->update([
            'status' => $newStatus,
            'actual_completion_date' => $newStatus === 'Completed' ? now()->toDateString() : $survey->actual_completion_date,
        ]);

        // When survey is completed, automatically trigger Event B SMS to customer
        if ($newStatus === 'Completed' && $oldStatus !== 'Completed' && $survey->customer) {
            NotificationService::triggerEventB_SurveyCompletion($survey->customer, $survey->project_code);
        }

        return back()->with('success', "Survey project {$survey->project_code} status updated to {$newStatus}!");
    }
}
