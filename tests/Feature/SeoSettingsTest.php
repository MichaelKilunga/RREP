<?php

namespace Tests\Feature;

use App\Models\SystemSetting;
use App\Models\User;
use Tests\TestCase;

class SeoSettingsTest extends TestCase
{
    public function test_homepage_link_preview_uses_editable_defaults(): void
    {
        $content = $this->get(route('public.home'))->getContent();

        $this->assertStringContainsString('<title>Land, Plots &amp; Property Marketplace in Tanzania', $content);
        $this->assertStringContainsString('og:description" content="Discover verified homes, titled surveyed plots', $content);
    }

    public function test_owner_can_override_the_link_preview_texts(): void
    {
        SystemSetting::setVal('seo_site_title_en', 'Dodoma Trusted Land Experts');
        SystemSetting::setVal('seo_meta_description_en', 'Surveyed plots with title deeds in Dodoma growth corridors.');
        SystemSetting::setVal('seo_meta_keywords', 'viwanja, hati miliki');
        SystemSetting::setVal('contact_city', 'Dodoma');

        $content = $this->get(route('public.home'))->getContent();

        $this->assertStringContainsString('<title>Dodoma Trusted Land Experts', $content);
        $this->assertStringContainsString('og:title" content="Dodoma Trusted Land Experts"', $content);
        $this->assertStringContainsString('twitter:title" content="Dodoma Trusted Land Experts"', $content);
        $this->assertStringContainsString('og:description" content="Surveyed plots with title deeds in Dodoma growth corridors."', $content);
        $this->assertStringContainsString('name="keywords" content="viwanja, hati miliki"', $content);
        $this->assertStringContainsString('"addressLocality": "Dodoma"', $content);
        $this->assertStringNotContainsString('REMS Real Estate Marketplace', $content);
    }

    public function test_page_level_titles_still_override_the_site_default(): void
    {
        SystemSetting::setVal('seo_site_title_en', 'Site Wide Title');

        $content = $this->get(route('public.about'))->getContent();

        $this->assertStringContainsString('About Us', $content);
        $this->assertStringNotContainsString('<title>Site Wide Title', $content);
    }

    public function test_settings_expose_the_seo_panel(): void
    {
        $admin = User::where('email', 'admin@rehospace.com')->firstOrFail();

        $this->actingAs($admin)->get(route('settings.index'))
            ->assertSee('seo_site_title_sw', false)
            ->assertSee('seo_site_title_en', false)
            ->assertSee('seo_meta_description_sw', false)
            ->assertSee('seo_meta_keywords', false)
            ->assertSee('name="contact_city"', false);
    }

    public function test_seo_settings_persist_through_the_landing_cms_form(): void
    {
        $admin = User::where('email', 'admin@rehospace.com')->firstOrFail();

        $this->actingAs($admin)->post(route('settings.landing'), [
            'seo_site_title_sw' => 'Wataalamu wa Viwanja Dodoma',
            'seo_site_title_en' => 'Dodoma Plot Experts',
            'seo_meta_description_en' => 'Plots you can trust.',
            'seo_meta_keywords' => 'plots, dodoma',
            'contact_city' => 'Dodoma',
        ])->assertRedirect();

        $this->assertSame('Dodoma Plot Experts', SystemSetting::getVal('seo_site_title_en'));
        $this->assertSame('Wataalamu wa Viwanja Dodoma', SystemSetting::getVal('seo_site_title_sw'));
        $this->assertSame('plots, dodoma', SystemSetting::getVal('seo_meta_keywords'));
        $this->assertSame('Dodoma', SystemSetting::getVal('contact_city'));
    }
}
