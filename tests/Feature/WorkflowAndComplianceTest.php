<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Tests\TestCase;

class WorkflowAndComplianceTest extends TestCase
{
    protected function getAdmin(): User
    {
        return User::where('email', 'admin@rehospace.com')->first();
    }

    public function test_workflow_approvals_queue_renders(): void
    {
        $response = $this->actingAs($this->getAdmin())->get('/workflows');
        $response->assertStatus(200);
        $response->assertSee('Workflows');
    }

    public function test_kyc_compliance_queue_renders_and_verifies_customer(): void
    {
        $customer = Customer::first();

        $response = $this->actingAs($this->getAdmin())->get('/compliance/kyc');
        $response->assertStatus(200);
        $response->assertSee('KYC');

        $verifyResponse = $this->actingAs($this->getAdmin())->post(route('compliance.verify_customer', $customer), [
            'status' => 'Verified',
        ]);
        $verifyResponse->assertStatus(302);

        $customer->refresh();
        $this->assertEquals('Verified', $customer->kyc_status);
    }
}
