<?php

namespace Tests\Feature;

use App\Models\PropertyType;
use App\Models\User;
use Tests\TestCase;

class PropertyManagementTest extends TestCase
{
    protected function getAdmin(): User
    {
        return User::where('email', 'admin@rehospace.com')->first();
    }

    public function test_properties_index_can_be_viewed_by_staff(): void
    {
        $response = $this->actingAs($this->getAdmin())->get(route('properties.index'));
        $response->assertStatus(200);
        $response->assertSee('Property Inventory');
    }

    public function test_new_property_can_be_created(): void
    {
        $type = PropertyType::first();

        $response = $this->actingAs($this->getAdmin())->post('/admin/properties', [
            'title' => 'Oysterbay Diplomatic Residence New',
            'property_type_id' => $type->id,
            'listing_type' => 'Sale',
            'status' => 'Available',
            'price' => 850000000.00,
            'address' => 'Haile Selassie Road',
            'city' => 'Dar es Salaam',
            'area_size' => 800.00,
            'area_unit' => 'Sqm',
            'bedrooms' => 4,
            'bathrooms' => 4,
            'description' => 'Luxury property in Oysterbay.',
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('properties', [
            'title' => 'Oysterbay Diplomatic Residence New',
        ]);
    }

    public function test_public_marketplace_renders_properties(): void
    {
        $response = $this->get('/marketplace');
        $response->assertStatus(200);
        $response->assertSee('Marketplace');
    }
}
