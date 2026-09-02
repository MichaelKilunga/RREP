<?php

namespace Tests\Feature;

use App\Models\Property;
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
}
