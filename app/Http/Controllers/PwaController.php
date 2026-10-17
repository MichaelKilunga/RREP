<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Str;

class PwaController extends Controller
{
    /**
     * Web app manifest built from the tenant's branding so each white-label
     * deployment installs under its own name and colours.
     */
    public function manifest(): Response
    {
        $branding = current_branding();
        $name = strip_tags((string) setting('company_name', current_organization()?->name ?? 'RehoSpace'));

        $manifest = [
            'name' => $name,
            'short_name' => Str::limit($name, 12, ''),
            'description' => strip_tags((string) setting('company_tagline', 'Verified Real Estate & Land Survey Marketplace')),
            'id' => '/',
            'start_url' => '/',
            'scope' => '/',
            'display' => 'standalone',
            'orientation' => 'portrait',
            'background_color' => '#f8fafc',
            'theme_color' => $branding?->primary_color ?? '#0f52ba',
            'lang' => app()->getLocale(),
            'icons' => [
                ['src' => route('pwa.icon', ['size' => 192]), 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => route('pwa.icon', ['size' => 512]), 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => route('pwa.icon', ['size' => 512, 'maskable' => 1]), 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable'],
            ],
        ];

        return response(json_encode($manifest, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT), 200, [
            'Content-Type' => 'application/manifest+json',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /**
     * Brand monogram icon rendered on the tenant's primary colour.
     */
    public function icon(int $size): Response
    {
        abort_unless(in_array($size, [192, 512], true), 404);

        $branding = current_branding();
        $primary = $branding?->primary_color ?? '#0f52ba';
        [$r, $g, $b] = array_map('intval', explode(', ', brand_rgb($primary)));
        $monogram = strtoupper(substr(trim((string) setting('brand_monogram', 'R')) ?: 'R', 0, 1));
        $maskable = request()->boolean('maskable');

        $img = imagecreatetruecolor($size, $size);
        imagesavealpha($img, true);

        $bg = imagecolorallocate($img, $r, $g, $b);
        $white = imagecolorallocate($img, 255, 255, 255);

        if ($maskable) {
            imagefilledrectangle($img, 0, 0, $size, $size, $bg);
        } else {
            $transparent = imagecolorallocatealpha($img, 0, 0, 0, 127);
            imagefilledrectangle($img, 0, 0, $size, $size, $transparent);
            $radius = (int) round($size * 0.2);
            imagefilledrectangle($img, $radius, 0, $size - $radius, $size, $bg);
            imagefilledrectangle($img, 0, $radius, $size, $size - $radius, $bg);
            foreach ([[$radius, $radius], [$size - $radius, $radius], [$radius, $size - $radius], [$size - $radius, $size - $radius]] as [$cx, $cy]) {
                imagefilledellipse($img, $cx, $cy, $radius * 2, $radius * 2, $bg);
            }
        }

        $font = collect([
            storage_path('fonts/pwa.ttf'),
            'C:\\Windows\\Fonts\\arialbd.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
            '/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf',
        ])->first(fn ($path) => is_file($path));

        if ($font) {
            $fontSize = $size * ($maskable ? 0.38 : 0.45);
            $box = imagettfbbox($fontSize, 0, $font, $monogram);
            $textWidth = abs($box[2] - $box[0]);
            $textHeight = abs($box[7] - $box[1]);
            $x = (int) (($size - $textWidth) / 2 - $box[0]);
            $y = (int) (($size + $textHeight) / 2 - ($box[1] > 0 ? $box[1] : 0));
            imagettftext($img, $fontSize, 0, $x, $y, $white, $font, $monogram);
        } else {
            // Bitmap fallback: scale GD's built-in font onto the canvas.
            $glyph = imagecreatetruecolor(9, 15);
            $black = imagecolorallocate($glyph, 0, 0, 0);
            imagefilledrectangle($glyph, 0, 0, 9, 15, $black);
            imagestring($glyph, 5, 0, 0, $monogram, imagecolorallocate($glyph, 255, 255, 255));
            $target = (int) ($size * 0.5);
            imagecopyresized($img, $glyph, (int) (($size - $target * 0.6) / 2), (int) (($size - $target) / 2), 0, 0, (int) ($target * 0.6), $target, 9, 15);
            imagedestroy($glyph);
        }

        ob_start();
        imagepng($img);
        $png = ob_get_clean();
        imagedestroy($img);

        return response($png, 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
