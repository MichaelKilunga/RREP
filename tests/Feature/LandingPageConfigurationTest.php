<?php

namespace Tests\Feature;

use App\Models\BrandingConfig;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LandingPageConfigurationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    protected function tearDown(): void
    {
        SystemSetting::where('group', 'landing')->delete();
        SystemSetting::whereIn('key', ['company_name', 'company_subtitle', 'brand_monogram', 'footer_copyright'])->delete();
        Cache::flush();
        parent::tearDown();
    }

    protected function getAdmin(): User
    {
        return User::where('email', 'admin@rehospace.com')->first() ?: User::first() ?: User::factory()->create();
    }

    public function test_admin_can_update_branding_identity_with_monogram_and_subtitle(): void
    {
        $admin = $this->getAdmin();

        $response = $this->actingAs($admin)->post(route('settings.branding'), [
            'company_name' => 'Kilimanjaro Real Estate Corp',
            'company_subtitle' => 'Cadastral Surveys & Prime Land',
            'brand_monogram' => 'K',
            'company_tagline' => 'Tanzania Premier Land Portal',
            'primary_color' => '#1a56db',
            'secondary_color' => '#374151',
            'accent_color' => '#059669',
            'custom_css' => '/* test custom css */ .hero-title { font-weight: 900; }',
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals('Kilimanjaro Real Estate Corp', SystemSetting::getVal('company_name'));
        $this->assertEquals('Cadastral Surveys & Prime Land', SystemSetting::getVal('company_subtitle'));
        $this->assertEquals('K', SystemSetting::getVal('brand_monogram'));

        // Verify public home reflects updated branding
        $homeResponse = $this->get(route('public.home'));
        $homeResponse->assertStatus(200);
        $homeResponse->assertSee('Kilimanjaro Real Estate Corp');
        $homeResponse->assertSee('Cadastral Surveys & Prime Land');
        $homeResponse->assertSee('.hero-title { font-weight: 900; }', false);
    }

    public function test_admin_can_upload_branding_logo_and_favicon(): void
    {
        Storage::fake('public');
        $admin = $this->getAdmin();

        $logo = UploadedFile::fake()->image('custom-brand-logo.png', 250, 60);
        $favicon = UploadedFile::fake()->image('custom-favicon.ico', 32, 32);

        $response = $this->actingAs($admin)->post(route('settings.branding'), [
            'company_name' => 'Apex Land & Realty',
            'header_logo_file' => $logo,
            'favicon_file' => $favicon,
        ]);

        $response->assertSessionHas('success');

        $branding = BrandingConfig::first();
        $this->assertNotNull($branding->header_logo);
        $this->assertNotNull($branding->favicon);
        $this->assertStringContainsString('branding/', $branding->header_logo);

        // Verify public home page links the uploaded logo & favicon
        $homeResponse = $this->get(route('public.home'));
        $homeResponse->assertStatus(200);
        $homeResponse->assertSee($branding->header_logo);
        $homeResponse->assertSee($branding->favicon);
    }

    public function test_admin_can_update_landing_page_configuration_and_texts(): void
    {
        $admin = $this->getAdmin();

        $response = $this->actingAs($admin)->post(route('settings.landing'), [
            'landing_hero_badge_text' => 'East Africa Number 1 Real Estate Network',
            'landing_hero_title' => "Find Your Dream Property in Tanzania\nTrusted & Title Verified",
            'landing_hero_subtitle' => 'The complete digital marketplace for surveyed plots, commercial villas, and residential developments.',
            'landing_categories_title' => 'Browse Our Exclusive Property Categories',
            'landing_service_1_title' => 'Advanced Drone & RTK GPS Surveying',
            'landing_service_1_desc' => 'High precision cadastral setting out and beacon monumentation.',
            'landing_trust_title' => 'Why Thousands Rely on Our Certified Platform',
            'landing_stat_1_label' => 'Certified Title Deeds',
            'landing_stat_1_override' => '1250',
            'landing_whatsapp_message' => 'Hello team, I need information on purchasing residential plots.',
            'footer_copyright' => 'Custom 2026 Copyright Ecosystem',
        ]);

        $response->assertSessionHas('success');

        // Check stored settings
        $this->assertEquals('East Africa Number 1 Real Estate Network', SystemSetting::getVal('landing_hero_badge_text'));
        $this->assertEquals('Advanced Drone & RTK GPS Surveying', SystemSetting::getVal('landing_service_1_title'));
        $this->assertEquals('1250', SystemSetting::getVal('landing_stat_1_override'));

        // Verify public home page renders the updated texts
        $homeResponse = $this->get(route('public.home'));
        $homeResponse->assertStatus(200);
        $homeResponse->assertSee('East Africa Number 1 Real Estate Network');
        $homeResponse->assertSee('Find Your Dream Property in Tanzania');
        $homeResponse->assertSee('Browse Our Exclusive Property Categories');
        $homeResponse->assertSee('Advanced Drone & RTK GPS Surveying', false);
        $homeResponse->assertSee('Why Thousands Rely on Our Certified Platform');
        $homeResponse->assertSee('Certified Title Deeds');
        $homeResponse->assertSee('1,250+');
        $homeResponse->assertSee('Custom 2026 Copyright Ecosystem');
    }

    public function test_admin_can_toggle_landing_page_sections_off(): void
    {
        $admin = $this->getAdmin();

        // 1. Submit with toggles OFF (value '0')
        $response = $this->actingAs($admin)->post(route('settings.landing'), [
            'landing_categories_enabled' => '0',
            'landing_services_enabled' => '0',
            'landing_trust_enabled' => '0',
            'landing_owner_cta_enabled' => '0',
            'landing_whatsapp_enabled' => '0',
        ]);

        $response->assertSessionHas('success');

        $homeResponse = $this->get(route('public.home'));
        $homeResponse->assertStatus(200);
        $homeResponse->assertDontSee('Marketplace Categories');
        $homeResponse->assertDontSee('Comprehensive Property & Survey Services');
        $homeResponse->assertDontSee('Trust & Verification Protocol');
        $homeResponse->assertDontSee('Chat with Real Estate Advisor on WhatsApp');

        // 2. Toggle back ON (value '1')
        $restoreResponse = $this->actingAs($admin)->post(route('settings.landing'), [
            'landing_categories_enabled' => '1',
            'landing_services_enabled' => '1',
            'landing_trust_enabled' => '1',
            'landing_owner_cta_enabled' => '1',
            'landing_whatsapp_enabled' => '1',
        ]);

        $restoreResponse->assertSessionHas('success');

        $restoredHome = $this->get(route('public.home'));
        $restoredHome->assertStatus(200);
        $restoredHome->assertSee('Marketplace Categories');
        $restoredHome->assertSee('Comprehensive Property & Survey Services');
        $restoredHome->assertSee('Trust & Verification Protocol');
        $restoredHome->assertSee('Have a Property to Sell or Rent?');
        $restoredHome->assertSee('Chat with Real Estate Advisor on WhatsApp');
    }

    public function test_internal_portal_and_dashboard_pages_apply_admin_configured_branding(): void
    {
        $admin = $this->getAdmin();

        $this->actingAs($admin)->post(route('settings.branding'), [
            'company_name' => 'Serengeti Estates & Advisory',
            'company_subtitle' => 'Integrated Land & Property Portal',
            'brand_monogram' => 'S',
            'company_tagline' => 'East African Real Estate Vanguard',
            'primary_color' => '#7c3aed',
            'secondary_color' => '#1e293b',
            'accent_color' => '#f59e0b',
            'sidebar_theme' => 'light',
            'custom_css' => '/* portal custom styles */ .portal-kpi-badge { font-weight: 800; }',
        ]);

        // 1. Dashboard internal layout
        $dashResponse = $this->actingAs($admin)->get(route('dashboard'));
        $dashResponse->assertStatus(200);
        $dashResponse->assertSee('Serengeti Estates & Advisory');
        $dashResponse->assertSee('Integrated Land & Property Portal', false);
        $dashResponse->assertSee('--rrep-primary: #7c3aed', false);
        $dashResponse->assertSee('/* portal custom styles */ .portal-kpi-badge { font-weight: 800; }', false);

        // 2. Client Self-Service Portal
        $clientPortal = $this->actingAs($admin)->get(route('portals.client'));
        $clientPortal->assertStatus(200);
        $clientPortal->assertSee('Serengeti Estates & Advisory Client Self-Service Portal', false);
        $clientPortal->assertSee('--rrep-primary: #7c3aed', false);

        // 3. Owner Self-Service Portal
        $ownerPortal = $this->actingAs($admin)->get(route('portals.owner'));
        $ownerPortal->assertStatus(200);
        $ownerPortal->assertSee('Serengeti Estates & Advisory Landlord & Property Owner Portal', false);
        $ownerPortal->assertSee('--rrep-primary: #7c3aed', false);
    }

    public function test_authentication_page_reflects_admin_configured_branding_and_identity(): void
    {
        $admin = $this->getAdmin();

        $this->actingAs($admin)->post(route('settings.branding'), [
            'company_name' => 'Ngorongoro Heritage Realty',
            'company_subtitle' => 'Government Cadastral Verification Hub',
            'brand_monogram' => 'N',
            'primary_color' => '#059669',
            'secondary_color' => '#111827',
            'accent_color' => '#d97706',
            'custom_css' => '/* auth custom css */ .auth-card { border: 2px solid #059669; }',
        ]);

        // Clear header_logo so monogram fallback is exercised
        $branding = BrandingConfig::first();
        if ($branding) {
            $branding->update(['header_logo' => null]);
        }

        auth()->logout();

        $response = $this->get(route('login'));
        $response->assertStatus(200);
        $response->assertSee('Ngorongoro Heritage Realty');
        $response->assertSee('Government Cadastral Verification Hub');
        $response->assertSee('Sign In to Ngorongoro Heritage Realty');
        $response->assertSee('>N<', false);
        $response->assertSee('--rrep-primary: #059669', false);
        $response->assertSee('/* auth custom css */ .auth-card { border: 2px solid #059669; }', false);
    }
}
