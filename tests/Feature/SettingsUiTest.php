<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Organization;
use App\Models\User;
use Tests\TestCase;

class SettingsUiTest extends TestCase
{
    protected function getAdmin(): User
    {
        return User::where('email', 'admin@rehospace.com')->firstOrFail();
    }

    public function test_settings_page_renders_responsive_tabs_cards_and_stacked_tables(): void
    {
        $response = $this->actingAs($this->getAdmin())->get(route('settings.index'));

        $response->assertStatus(200);
        $response->assertSee('id="settingsTabs"', false);
        $response->assertSee('flex-nowrap overflow-auto scroll-x', false);
        $response->assertSee('data-bs-target="#branches"', false);
        $response->assertSee('table-stack', false);
        $response->assertSee('stack-primary', false);
        $response->assertSee('id="landingAccordion"', false);
        $response->assertSee('id="brandPreviewCard"', false);
        $response->assertSee('data-sms-counter', false);
        $response->assertSee('modal-fullscreen-sm-down', false);
        $response->assertSee('Organization Branches');
        $response->assertSee('Add Branch');
        $response->assertSee('Marketplace & Sales');
        $response->assertSee('0784 100 200');
    }

    public function test_branch_modals_use_tanzanian_region_select_and_keep_unknown_city(): void
    {
        $org = Organization::first() ?: Organization::create(['name' => 'RehoSpace Real Estate Ltd', 'code' => 'REHO', 'status' => 'Active']);

        Branch::create([
            'organization_id' => $org->id,
            'name' => 'Stone Town Desk',
            'code' => 'BR-STZ-'.substr(uniqid(), -4),
            'city' => 'Stone Town',
            'phone' => '0777 999 888',
            'status' => 'Active',
            'is_main' => false,
        ]);

        $response = $this->actingAs($this->getAdmin())->get(route('settings.index'));

        $response->assertStatus(200);
        $response->assertSee('<select name="city"', false);
        $response->assertSee('<option value="Dar es Salaam"', false);
        $response->assertSee('<option value="Stone Town" selected>', false);
        $response->assertSee('name="phone"', false);
        $response->assertSee('data-tz-phone', false);
        $response->assertSee('+255 777 999 888');
    }

    public function test_settings_page_shows_branch_empty_state_when_no_branches_exist(): void
    {
        Branch::query()->delete();

        $response = $this->actingAs($this->getAdmin())->get(route('settings.index'));

        $response->assertStatus(200);
        $response->assertSee('empty-state', false);
        $response->assertSee('No branches yet');
        $response->assertSee('Add Branch');
    }

    public function test_settings_page_renders_in_swahili(): void
    {
        $response = $this->withSession(['locale' => 'sw'])->actingAs($this->getAdmin())->get(route('settings.index'));

        $response->assertStatus(200);
        $response->assertSee('<html lang="sw"', false);
        $response->assertSee('Mazingira na Uendeshaji');
        $response->assertSee('Matawi ya Taasisi');
        $response->assertSee('Ongeza Tawi');
        $response->assertSee('Hifadhi mipangilio ya SMS');
    }
}
