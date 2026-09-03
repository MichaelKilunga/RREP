<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\PropertyType;
use App\Models\Role;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Tests\TestCase;

class EnvironmentLifecycleTest extends TestCase
{
    protected function getAdmin(): User
    {
        $admin = User::where('email', 'admin@rehospace.com')->first();
        if (! $admin) {
            $admin = User::create([
                'name' => 'Michael Kilunga (Admin)',
                'first_name' => 'Michael',
                'last_name' => 'Kilunga',
                'email' => 'admin@rehospace.com',
                'phone' => '+255 754 111 222',
                'user_type' => 'Staff',
                'job_title' => 'Chief Executive & Principal Broker',
                'status' => 'Active',
                'password' => bcrypt('password'),
            ]);
            $role = Role::firstOrCreate(['name' => 'super_admin', 'display_name' => 'Super Administrator']);
            $admin->roles()->syncWithoutDetaching([$role->id]);
        }

        return $admin;
    }

    public function test_login_screen_renders_quick_logins_in_local_mode(): void
    {
        SystemSetting::setVal('app_environment', 'local');

        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Quick 1-Click Role Login:');
        $response->assertSee('Super Admin');
        $response->assertSee('Sales Agent');
        $response->assertSee('GIS Surveyor');
        $response->assertSee('Accountant');
        $response->assertSee('value="password"', false);
    }

    public function test_login_screen_hides_quick_logins_in_production_mode(): void
    {
        SystemSetting::setVal('app_environment', 'production');

        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertDontSee('Quick 1-Click Role Login:');
        $response->assertDontSee('onclick="fillLogin(', false);
        $response->assertDontSee('value="password"', false);

        // Reset back to local for subsequent tests
        SystemSetting::setVal('app_environment', 'local');
    }

    public function test_admin_can_view_environment_settings_and_stats(): void
    {
        $admin = $this->getAdmin();

        $response = $this->actingAs($admin)->get(route('settings.index'));

        $response->assertStatus(200);
        $response->assertSee('Environment & Lifecycle', false);
        $response->assertSee('Production Environment');
        $response->assertSee('Development Environment');
        $response->assertSee('Seeded Demo Data Status & Controls', false);
    }

    public function test_admin_can_switch_environment_to_production_and_purge_demo_data(): void
    {
        $admin = $this->getAdmin();

        // Create temporary demo records to verify purging
        $type = PropertyType::first() ?: PropertyType::create([
            'name' => 'Residential Apartment',
            'slug' => 'residential-apartment-temp',
            'category' => 'Residential',
        ]);

        Property::create([
            'uuid' => (string) Str::uuid(),
            'title' => 'Temporary Demo Villa Test',
            'slug' => 'temporary-demo-villa-test-'.uniqid(),
            'property_code' => 'PROP-TEST-'.uniqid(),
            'property_type_id' => $type->id,
            'listing_type' => 'Sale',
            'status' => 'Available',
            'price' => 250000000.00,
            'address' => 'Mikocheni B',
            'city' => 'Dar es Salaam',
        ]);

        $response = $this->actingAs($admin)->post(route('settings.environment'), [
            'environment' => 'production',
            'purge_demo_data' => 1,
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals('production', SystemSetting::getVal('app_environment'));
        $this->assertTrue(is_production_mode());

        // Admin must be preserved
        $this->assertDatabaseHas('users', [
            'email' => 'admin@rehospace.com',
        ]);

        // Demo login accounts must be purged
        $this->assertDatabaseMissing('users', [
            'email' => 'agent@rehospace.com',
        ]);
        $this->assertDatabaseMissing('users', [
            'email' => 'surveyor@rehospace.com',
        ]);
        $this->assertDatabaseMissing('users', [
            'email' => 'finance@rehospace.com',
        ]);

        // Demo property must be purged
        $this->assertDatabaseMissing('properties', [
            'title' => 'Temporary Demo Villa Test',
        ]);
    }

    public function test_admin_can_switch_environment_back_to_local(): void
    {
        $admin = $this->getAdmin();

        $response = $this->actingAs($admin)->post(route('settings.environment'), [
            'environment' => 'local',
            'reseed_demo_data' => 0,
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals('local', SystemSetting::getVal('app_environment'));
        $this->assertTrue(is_local_mode());
    }

    public function test_admin_can_purge_demo_data_endpoint(): void
    {
        $admin = $this->getAdmin();

        $response = $this->actingAs($admin)->post(route('settings.purge_demo_data'));

        $response->assertSessionHas('success');

        // Restore seeds for subsequent test suites
        Artisan::call('db:seed', ['--force' => true]);
    }
}
