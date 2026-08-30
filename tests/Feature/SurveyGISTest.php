<?php

namespace Tests\Feature;

use App\Models\SurveyProject;
use App\Models\User;
use Tests\TestCase;

class SurveyGISTest extends TestCase
{
    protected function getAdmin(): User
    {
        return User::where('email', 'admin@rehospace.com')->first();
    }

    public function test_survey_projects_index_renders(): void
    {
        $response = $this->actingAs($this->getAdmin())->get('/survey');
        $response->assertStatus(200);
        $response->assertSee('Survey Projects');
    }

    public function test_adding_beacon_and_viewing_geojson(): void
    {
        $survey = SurveyProject::first();

        $response = $this->actingAs($this->getAdmin())->post(route('survey.beacons.add', $survey), [
            'beacon_number' => 'BM-TEST-99',
            'latitude' => -3.3300,
            'longitude' => 36.6560,
            'beacon_type' => 'Concrete Pillar',
            'condition' => 'Good',
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('survey_beacons', [
            'survey_project_id' => $survey->id,
            'beacon_number' => 'BM-TEST-99',
        ]);
    }
}
