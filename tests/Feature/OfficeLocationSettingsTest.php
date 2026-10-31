<?php

namespace Tests\Feature;

use App\Models\SystemSetting;
use App\Models\User;
use Tests\TestCase;

/**
 * Owners pin the office on a map in Settings -> Contact; the Contact page
 * map, Directions link and structured data follow that pin.
 */
class OfficeLocationSettingsTest extends TestCase
{
    private function admin(): User
    {
        return User::where('email', 'admin@rehospace.com')->firstOrFail();
    }

    /**
     * @return array<string, string>
     */
    private function contactPayload(array $overrides = []): array
    {
        return array_merge([
            'contact_phone' => '+255 784 100 200',
            'contact_whatsapp' => '255784100200',
            'contact_email' => 'info@avenix.co.tz',
            'contact_address' => 'Chihoni, Nala, Dodoma',
        ], $overrides);
    }

    public function test_settings_contact_tab_offers_the_office_location_picker(): void
    {
        $this->actingAs($this->admin())->get(route('settings.index'))
            ->assertOk()
            ->assertSee('Office Location (Map Pin)')
            ->assertSee('id="officeLocationMap"', false)
            ->assertSee('name="contact_latitude"', false)
            ->assertSee('name="contact_longitude"', false);
    }

    public function test_saved_office_location_drives_the_contact_page_map_and_directions(): void
    {
        $this->actingAs($this->admin())
            ->post(route('settings.social'), $this->contactPayload([
                'contact_latitude' => '-6.163012',
                'contact_longitude' => '35.751634',
            ]))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame('-6.163012', SystemSetting::getVal('contact_latitude'));
        $this->assertSame('35.751634', SystemSetting::getVal('contact_longitude'));

        $this->get(route('public.contact'))
            ->assertOk()
            ->assertSee('id="contactOfficeMap"', false)
            ->assertSee('destination=-6.163012,35.751634', false)
            ->assertSee('"GeoCoordinates"', false)
            ->assertSee('"latitude": -6.163012', false);
    }

    public function test_save_redirects_back_to_the_contact_tab(): void
    {
        $response = $this->actingAs($this->admin())
            ->from(route('settings.index'))
            ->post(route('settings.social'), $this->contactPayload([
                'contact_latitude' => '-6.163',
                'contact_longitude' => '35.7516',
            ]));

        $response->assertRedirect(route('settings.index').'#social');
    }

    public function test_without_a_location_the_map_is_hidden_and_maps_searches_the_address(): void
    {
        $this->actingAs($this->admin())
            ->post(route('settings.social'), $this->contactPayload([
                'contact_latitude' => '',
                'contact_longitude' => '',
            ]))
            ->assertSessionHasNoErrors();

        $this->get(route('public.contact'))
            ->assertOk()
            ->assertDontSee('id="contactOfficeMap"', false)
            ->assertDontSee('maps/dir/', false)
            ->assertSee('query=Chihoni%2C+Nala%2C+Dodoma', false)
            ->assertDontSee('"GeoCoordinates"', false);
    }

    public function test_incomplete_or_out_of_range_coordinates_are_rejected(): void
    {
        $this->actingAs($this->admin())
            ->from(route('settings.index'))
            ->post(route('settings.social'), $this->contactPayload([
                'contact_latitude' => '-6.163',
                'contact_longitude' => '',
            ]))
            ->assertRedirect(route('settings.index').'#social')
            ->assertSessionHasErrors('contact_longitude');

        $this->actingAs($this->admin())
            ->from(route('settings.index'))
            ->post(route('settings.social'), $this->contactPayload([
                'contact_latitude' => '123',
                'contact_longitude' => '35.75',
            ]))
            ->assertSessionHasErrors('contact_latitude');

        $this->assertNull(SystemSetting::getVal('contact_latitude'));
    }
}
