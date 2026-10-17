<?php

namespace Tests\Feature;

use App\Models\LandParcel;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\User;
use Tests\TestCase;

class PortalUiTest extends TestCase
{
    protected function getAdmin(): User
    {
        return User::where('email', 'admin@rehospace.com')->firstOrFail();
    }

    public function test_client_portal_renders_kpi_strip_payment_instructions_and_support_button(): void
    {
        $response = $this->actingAs($this->getAdmin())->get(route('portals.client'));

        $response->assertStatus(200);
        $response->assertSee('Client Self-Service Portal');
        $response->assertSee('kpi-card', false);
        $response->assertSee('Outstanding invoices (TZS)');
        $response->assertSee('How to pay');
        $response->assertSee('M-Pesa (Vodacom)');
        $response->assertSee('Mixx by Yas (Tigo Pesa)');
        $response->assertSee('Airtel Money');
        $response->assertSee('btn-whatsapp', false);
        $response->assertSee('https://wa.me/', false);
    }

    public function test_client_portal_renders_in_swahili(): void
    {
        $response = $this->withSession(['locale' => 'sw'])->actingAs($this->getAdmin())->get(route('portals.client'));

        $response->assertStatus(200);
        $response->assertSee('Akaunti Yangu ya Mteja');
        $response->assertSee('Jinsi ya kulipa');

        $owner = $this->withSession(['locale' => 'sw'])->actingAs($this->getAdmin())->get(route('portals.owner'));
        $owner->assertStatus(200);
        $owner->assertSee('Mapitio yanafanyikaje');
    }

    public function test_owner_portal_form_uses_tanzanian_regions_and_deed_types(): void
    {
        $response = $this->actingAs($this->getAdmin())->get(route('portals.owner'));

        $response->assertStatus(200);
        $response->assertSee('Property Owner Portal');
        $response->assertSee('name="city"', false);
        $response->assertSee('Dar es Salaam');
        $response->assertSee('Customary Right of Occupancy');
        $response->assertSee('NIDA number');
        $response->assertSee('data-tz-phone', false);
        $response->assertSee('How the review works');
        $response->assertSee('modal-fullscreen-sm-down', false);
    }

    public function test_owner_portal_lists_submissions_as_stacked_table_or_empty_state(): void
    {
        $response = $this->actingAs($this->getAdmin())->get(route('portals.owner'));

        $response->assertStatus(200);
        $html = $response->getContent();

        $this->assertTrue(
            str_contains($html, 'table-stack') || str_contains($html, 'empty-state'),
            'Owner submissions must render either a stacked table or an empty state.'
        );
    }

    public function test_owner_submission_stores_selected_title_deed_type(): void
    {
        $type = PropertyType::where('is_active', true)->firstOrFail();

        $response = $this->actingAs($this->getAdmin())->post(route('portals.owner.submit_property'), [
            'title' => 'Kiwanja Kigamboni',
            'property_type_id' => $type->id,
            'listing_type' => 'Sale',
            'price' => 45000000,
            'area_size' => 1200,
            'area_unit' => 'Sqm',
            'city' => 'Dar es Salaam',
            'address' => 'Plot 12 Block B, Kigamboni',
            'zoning' => 'Residential',
            'title_deed_type' => 'Customary Right of Occupancy',
            'description' => 'Surveyed plot with beacons, water and electricity nearby.',
        ]);

        $response->assertSessionHasNoErrors();

        $property = Property::where('title', 'Kiwanja Kigamboni')->firstOrFail();
        $this->assertSame('Dar es Salaam', $property->city);
        $this->assertSame('Customary Right of Occupancy', LandParcel::where('property_id', $property->id)->value('title_deed_type'));
    }

    public function test_login_page_is_bilingual_with_password_toggle_and_language_switch(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertSee('Sign In to RehoSpace');
        $response->assertSee('autocomplete="current-password"', false);
        $response->assertSee('inputmode="email"', false);
        $response->assertSee('id="togglePassword"', false);
        $response->assertSee(route('lang.switch', 'sw'), false);
        $response->assertSee(route('lang.switch', 'en'), false);
        $response->assertSee(route('public.home'), false);

        $swahili = $this->withSession(['locale' => 'sw'])->get(route('login'));
        $swahili->assertStatus(200);
        $swahili->assertSee('Nenosiri');
        $swahili->assertSee('Ingia kwenye RehoSpace');
    }
}
