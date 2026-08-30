<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class DocumentsAndMarketingTest extends TestCase
{
    protected function getAdmin(): User
    {
        return User::where('email', 'admin@rehospace.com')->first();
    }

    public function test_edms_document_vault_renders_and_archives_document(): void
    {
        $response = $this->actingAs($this->getAdmin())->get('/documents');
        $response->assertStatus(200);
        $response->assertSee('EDMS');

        $storeResponse = $this->actingAs($this->getAdmin())->post('/documents', [
            'file_name' => 'Certificate of Occupancy - Plot 889',
            'document_type' => 'Title Deed',
        ]);
        $storeResponse->assertStatus(302);

        $this->assertDatabaseHas('media_files', [
            'file_name' => 'Certificate of Occupancy - Plot 889',
            'category' => 'Title Deed',
        ]);
    }

    public function test_marketing_campaigns_renders_and_dispatches_broadcast(): void
    {
        $response = $this->actingAs($this->getAdmin())->get('/marketing');
        $response->assertStatus(200);
        $response->assertSee('Marketing');

        $campResponse = $this->actingAs($this->getAdmin())->post('/marketing', [
            'name' => 'Masaki Oceanview Q4 Promotion',
            'campaign_type' => 'Social Media',
            'budget' => 3500000.00,
            'start_date' => now()->toDateString(),
        ]);
        $campResponse->assertStatus(302);

        $broadcastResponse = $this->actingAs($this->getAdmin())->post('/marketing/broadcast', [
            'channel' => 'SMS',
            'target_group' => 'all_leads',
            'message_content' => 'Special promo on luxury properties.',
        ]);
        $broadcastResponse->assertStatus(302);
    }
}
