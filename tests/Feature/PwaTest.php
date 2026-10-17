<?php

namespace Tests\Feature;

use Tests\TestCase;

class PwaTest extends TestCase
{
    public function test_manifest_is_served_with_tenant_branding(): void
    {
        $response = $this->get(route('pwa.manifest'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/manifest+json');

        $manifest = json_decode($response->getContent(), true);
        $this->assertSame('standalone', $manifest['display']);
        $this->assertSame('/', $manifest['start_url']);
        $this->assertNotEmpty($manifest['name']);
        $this->assertNotEmpty($manifest['theme_color']);
        $this->assertCount(3, $manifest['icons']);
    }

    public function test_brand_icons_render_as_png(): void
    {
        foreach ([192, 512] as $size) {
            $response = $this->get(route('pwa.icon', ['size' => $size]));

            $response->assertStatus(200);
            $response->assertHeader('Content-Type', 'image/png');
            $this->assertStringStartsWith("\x89PNG", $response->getContent());
        }

        $this->get('/pwa/icon-300.png')->assertStatus(404);
    }

    public function test_layouts_link_the_manifest_and_service_worker_file_exists(): void
    {
        $this->get(route('public.home'))
            ->assertSee('manifest.webmanifest', false)
            ->assertSee('theme-color', false);

        $this->assertFileExists(public_path('sw.js'));
    }

    public function test_public_bottom_nav_has_exactly_home_survey_projects_and_menu(): void
    {
        $html = $this->get(route('public.home'))->getContent();

        $navStart = strpos($html, 'public-bottom-nav');
        $navEnd = strpos($html, '</nav>', $navStart);
        $this->assertNotFalse($navStart);
        $nav = substr($html, $navStart, $navEnd - $navStart);

        $this->assertStringContainsString(route('public.home'), $nav);
        $this->assertStringContainsString(route('public.services.land_survey'), $nav);
        $this->assertStringContainsString(route('public.projects'), $nav);
        $this->assertStringContainsString('mobileOffcanvas', $nav);

        $this->assertStringNotContainsString(route('public.properties'), $nav);
        $this->assertStringNotContainsString(route('public.land'), $nav);
        $this->assertSame(3, substr_count($nav, '<a href='), 'Bottom nav must hold exactly three links plus the menu button.');
    }
}
