<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class PortalsTest extends TestCase
{
    protected function getAdmin(): User
    {
        return User::where('email', 'admin@rehospace.com')->first();
    }

    public function test_client_self_service_portal_renders(): void
    {
        $response = $this->actingAs($this->getAdmin())->get('/portals/client');
        $response->assertStatus(200);
        $response->assertSee('Client Self-Service Portal');
    }

    public function test_landlord_owner_portal_renders(): void
    {
        $response = $this->actingAs($this->getAdmin())->get('/portals/owner');
        $response->assertStatus(200);
        $response->assertSee('Property Owner Portal');
    }
}
