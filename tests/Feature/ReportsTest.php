<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class ReportsTest extends TestCase
{
    protected function getAdmin(): User
    {
        return User::where('email', 'admin@rehospace.com')->first();
    }

    public function test_reports_hub_can_be_rendered(): void
    {
        $response = $this->actingAs($this->getAdmin())->get('/reports');
        $response->assertStatus(200);
        $response->assertSee('Reports');
    }

    public function test_property_report_renders_and_exports_csv(): void
    {
        $response = $this->actingAs($this->getAdmin())->get('/reports/properties');
        $response->assertStatus(200);
        $response->assertSee('Property Inventory');

        $csvResponse = $this->actingAs($this->getAdmin())->get('/reports/properties?export=csv');
        $csvResponse->assertStatus(200);
        $csvResponse->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_sales_report_renders_and_exports_csv(): void
    {
        $response = $this->actingAs($this->getAdmin())->get('/reports/sales');
        $response->assertStatus(200);
        $response->assertSee('Sales Revenue');

        $csvResponse = $this->actingAs($this->getAdmin())->get('/reports/sales?export=csv');
        $csvResponse->assertStatus(200);
        $csvResponse->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_agent_commission_and_rent_roll_reports_render(): void
    {
        $response1 = $this->actingAs($this->getAdmin())->get('/reports/agents');
        $response1->assertStatus(200);

        $response2 = $this->actingAs($this->getAdmin())->get('/reports/rent-roll');
        $response2->assertStatus(200);
    }
}
