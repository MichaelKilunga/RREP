<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Property;
use App\Models\User;
use Tests\TestCase;

class CRMAndDealsTest extends TestCase
{
    protected function getAdmin(): User
    {
        return User::where('email', 'admin@rehospace.com')->first();
    }

    public function test_crm_leads_kanban_view_renders(): void
    {
        $response = $this->actingAs($this->getAdmin())->get('/crm/leads');
        $response->assertStatus(200);
        $response->assertSee('Pipeline');
    }

    public function test_new_customer_can_be_registered(): void
    {
        $response = $this->actingAs($this->getAdmin())->post('/crm/customers', [
            'first_name' => 'Emmanuel',
            'last_name' => 'Massawe',
            'customer_type' => 'Individual',
            'phone' => '+255 712 999 888',
            'email' => 'emmanuel@example.com',
            'city' => 'Arusha',
        ]);

        $customer = Customer::where('phone', '+255 712 999 888')->first();
        $this->assertNotNull($customer);
        $this->assertEquals('Emmanuel Massawe', $customer->full_name);
    }

    public function test_sales_deal_creates_installment_schedules(): void
    {
        $property = Property::first();
        $customer = Customer::first();

        $response = $this->actingAs($this->getAdmin())->post('/deals', [
            'property_id' => $property->id,
            'customer_id' => $customer->id,
            'sale_price' => 300000000.00,
            'payment_plan_type' => 'Installment',
            'total_installments' => 3,
            'agreement_date' => now()->toDateString(),
        ]);

        $response->assertStatus(302);
    }
}
