<?php

namespace Tests\Feature;

use App\Models\SystemSetting;
use App\Models\User;
use Tests\TestCase;

class FeatureToggleAndSettingsTest extends TestCase
{
    public function test_feature_toggle_helper_and_settings_matrix(): void
    {
        // By default should be true
        $this->assertTrue(is_module_enabled('online_reservations'));

        // Toggle to false
        SystemSetting::setVal('feature_online_reservations_enabled', 'false');
        $this->assertFalse(is_module_enabled('online_reservations'));

        // Toggle back to true
        SystemSetting::setVal('feature_online_reservations_enabled', 'true');
        $this->assertTrue(is_module_enabled('online_reservations'));
    }

    public function test_admin_can_update_pushsms_settings(): void
    {
        $admin = User::first() ?: User::create([
            'name' => 'Admin User',
            'email' => 'admin.settings@avenix.co.tz',
            'password' => bcrypt('password'),
            'role' => 'Super Admin',
        ]);

        $response = $this->actingAs($admin)->post(route('settings.pushsms'), [
            'pushsms_api_key' => 'reho_live_test_api_key_12345',
            'pushsms_sender_id' => 'AVENIX',
            'pushsms_client_app' => 'RREP_AVENIX',
        ]);

        $response->assertSessionHas('success');

        $this->assertEquals('reho_live_test_api_key_12345', SystemSetting::getVal('pushsms_api_key'));
        $this->assertEquals('AVENIX', SystemSetting::getVal('pushsms_sender_id'));
    }

    public function test_admin_can_update_feature_toggles_via_controller(): void
    {
        $admin = User::first() ?: User::create([
            'name' => 'Admin User',
            'email' => 'admin.toggles@avenix.co.tz',
            'password' => bcrypt('password'),
            'role' => 'Super Admin',
        ]);

        $response = $this->actingAs($admin)->post(route('settings.toggles'), [
            'feature_online_reservations' => '1',
            'feature_property_owner_submissions' => '1',
            'feature_crm_lead_tracking' => '1',
        ]);

        $response->assertSessionHas('success');
        $this->assertTrue(is_module_enabled('online_reservations'));
    }
}
