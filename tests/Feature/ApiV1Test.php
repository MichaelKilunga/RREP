<?php

namespace Tests\Feature;

use App\Models\Property;
use Tests\TestCase;

class ApiV1Test extends TestCase
{
    public function test_api_login_returns_sanctum_token(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@rehospace.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'token',
            'user' => ['id', 'name', 'email'],
        ]);
    }

    public function test_public_properties_api(): void
    {
        $response = $this->getJson('/api/v1/marketplace/properties');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => ['data'],
        ]);
    }

    public function test_public_marketplace_inquiry_api(): void
    {
        $property = Property::first();

        $response = $this->postJson('/api/v1/marketplace/inquiries', [
            'property_id' => $property->id,
            'name' => 'API Prospect Client',
            'phone' => '+255 799 111 222',
            'email' => 'apiclient@example.com',
            'message' => 'Interested in commercial purchase.',
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
    }
}
