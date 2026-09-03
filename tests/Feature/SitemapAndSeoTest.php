<?php

namespace Tests\Feature;

use App\Models\BrandingConfig;
use App\Models\Property;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SitemapAndSeoTest extends TestCase
{
    public function test_xml_sitemap_renders_valid_xml_with_canonical_urls(): void
    {
        $response = $this->get(route('public.sitemap'));

        $response->assertStatus(200);
        $this->assertStringContainsString('xml', $response->headers->get('Content-Type'));

        $content = $response->getContent();
        $this->assertStringContainsString('<?xml version="1.0" encoding="UTF-8"?>', $content);
        $this->assertStringContainsString('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', $content);
        $this->assertStringContainsString(url('/properties'), $content);
        $this->assertStringContainsString(url('/land'), $content);
        $this->assertStringContainsString(url('/services/land-survey'), $content);

        // Check published property in sitemap
        $property = Property::where('is_published', true)->first();
        if ($property) {
            $this->assertStringContainsString(route('public.properties.show', $property->slug), $content);
        }
    }

    public function test_robots_txt_exists_and_references_sitemap(): void
    {
        $robotsPath = public_path('robots.txt');
        $this->assertFileExists($robotsPath);

        $content = file_get_contents($robotsPath);
        $this->assertStringContainsString('User-agent: *', $content);
        $this->assertStringContainsString('Sitemap:', $content);
        $this->assertStringContainsString('/sitemap.xml', $content);
    }

    public function test_homepage_renders_complete_whatsapp_and_social_open_graph_meta_tags(): void
    {
        $response = $this->get(route('public.home'));
        $response->assertStatus(200);

        $content = $response->getContent();

        // WhatsApp & Open Graph Primary Tags
        $this->assertStringContainsString('<meta property="og:site_name"', $content);
        $this->assertStringContainsString('<meta property="og:type" content="website">', $content);
        $this->assertStringContainsString('<meta property="og:title"', $content);
        $this->assertStringContainsString('<meta property="og:description"', $content);
        $this->assertStringContainsString('<meta property="og:image" content="http', $content);
        $this->assertStringContainsString('<meta property="og:image:secure_url" content="http', $content);
        $this->assertStringContainsString('<meta property="og:image:type" content="image/', $content);
        $this->assertStringContainsString('<meta property="og:image:width" content="1200">', $content);
        $this->assertStringContainsString('<meta property="og:image:height" content="630">', $content);

        // Twitter / X Cards
        $this->assertStringContainsString('<meta name="twitter:card" content="summary_large_image">', $content);
        $this->assertStringContainsString('<meta name="twitter:image"', $content);

        // Microdata Fallback for Crawlers
        $this->assertStringContainsString('<link rel="image_src"', $content);
        $this->assertStringContainsString('<meta itemprop="image"', $content);
    }

    public function test_fallback_og_image_is_used_when_branding_logo_is_absent(): void
    {
        $allLogos = BrandingConfig::pluck('header_logo', 'id');
        BrandingConfig::query()->update(['header_logo' => null]);
        SystemSetting::where('key', 'og_default_image')->delete();
        Cache::flush();

        $response = $this->get(route('public.home'));
        $response->assertStatus(200);

        $content = $response->getContent();
        $this->assertStringContainsString('/images/og-default.jpg', $content);
        $this->assertStringContainsString('<meta property="og:image:type" content="image/jpeg">', $content);

        // Restore
        foreach ($allLogos as $id => $logo) {
            BrandingConfig::where('id', $id)->update(['header_logo' => $logo]);
        }
        Cache::flush();
    }

    public function test_default_open_graph_and_placeholder_images_exist_and_meet_whatsapp_constraints(): void
    {
        $ogPath = public_path('images/og-default.jpg');
        $this->assertFileExists($ogPath, 'public/images/og-default.jpg must exist on disk');

        // WhatsApp drops preview images > 300KB
        $fileSizeBytes = filesize($ogPath);
        $this->assertLessThan(300 * 1024, $fileSizeBytes, "og-default.jpg ({$fileSizeBytes} bytes) must be less than 300KB for WhatsApp compatibility");

        // Optimal dimensions: 1200 x 630
        [$width, $height] = getimagesize($ogPath);
        $this->assertEquals(1200, $width, 'og-default.jpg width should be 1200px');
        $this->assertEquals(630, $height, 'og-default.jpg height should be 630px');

        // Verify fallback placeholders exist
        $this->assertFileExists(public_path('images/property-placeholder.jpg'));
        $this->assertFileExists(public_path('images/blog-placeholder.jpg'));
    }

    public function test_custom_social_share_image_setting_overrides_default_og_image(): void
    {
        SystemSetting::setVal('og_default_image', 'https://example.com/custom-og.jpg', 'branding');
        Cache::flush();

        $response = $this->get(route('public.home'));
        $response->assertStatus(200);

        $content = $response->getContent();
        $this->assertStringContainsString('content="https://example.com/custom-og.jpg"', $content);

        // Clean up setting
        SystemSetting::where('key', 'og_default_image')->delete();
        Cache::flush();
    }
}
