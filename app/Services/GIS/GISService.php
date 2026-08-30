<?php

namespace App\Services\GIS;

use App\Models\SurveyBeacon;
use App\Models\SurveyProject;
use Illuminate\Support\Str;

class GISService
{
    public function createSurveyProject(array $data): SurveyProject
    {
        $data['project_code'] = 'SURV-'.date('Y').'-'.strtoupper(Str::random(5));

        return SurveyProject::create($data);
    }

    public function addBeacon(SurveyProject $project, array $beaconData): SurveyBeacon
    {
        return $project->beacons()->create($beaconData);
    }

    /**
     * Convert survey beacons into standard GeoJSON polygon.
     */
    public function generateGeoJson(SurveyProject $project): array
    {
        $beacons = $project->beacons()->whereNotNull('latitude')->whereNotNull('longitude')->get();
        if ($beacons->count() < 3) {
            return [
                'type' => 'FeatureCollection',
                'features' => [],
            ];
        }

        $coordinates = [];
        foreach ($beacons as $b) {
            $coordinates[] = [(float) $b->longitude, (float) $b->latitude];
        }
        // Close the polygon loop
        $coordinates[] = $coordinates[0];

        return [
            'type' => 'FeatureCollection',
            'features' => [
                [
                    'type' => 'Feature',
                    'geometry' => [
                        'type' => 'Polygon',
                        'coordinates' => [$coordinates],
                    ],
                    'properties' => [
                        'project_code' => $project->project_code,
                        'project_name' => $project->project_name,
                        'total_area' => "{$project->total_area} {$project->area_unit}",
                        'status' => $project->status,
                    ],
                ],
            ],
        ];
    }
}
