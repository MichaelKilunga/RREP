<?php

use App\Core\Licensing\LicenseManager;
use App\Models\Branch;
use App\Models\BrandingConfig;
use App\Models\LicensedModule;
use App\Models\Organization;
use App\Models\SystemSetting;

if (! function_exists('module_enabled')) {
    function module_enabled(string $moduleKey): bool
    {
        return app(LicenseManager::class)->isEnabled($moduleKey);
    }
}

if (! function_exists('is_module_enabled')) {
    function is_module_enabled(string $moduleSlug, bool $default = true): bool
    {
        // 1. Check system settings toggle
        $settingKey = "feature_{$moduleSlug}_enabled";
        $val = SystemSetting::getVal($settingKey, null);
        if ($val !== null) {
            return in_array($val, ['1', 1, true, 'true', 'yes', 'on'], true);
        }

        // 2. Check licensed modules table
        $module = LicensedModule::where('module_slug', $moduleSlug)->first();
        if ($module) {
            return (bool) $module->is_enabled;
        }

        return $default;
    }
}

if (! function_exists('format_currency')) {
    function format_currency($amount, ?string $currency = null, int $decimals = 2): string
    {
        $currency = $currency ?: config('app.default_currency', 'TZS');
        $symbol = config('app.default_currency_symbol', 'TSh');

        $formatted = number_format((float) $amount, $decimals, '.', ',');

        return "{$symbol} {$formatted}";
    }
}

if (! function_exists('setting')) {
    function setting(string $key, $default = null)
    {
        return SystemSetting::getVal($key, $default);
    }
}

if (! function_exists('setting_trans')) {
    function setting_trans(string $key, $defaultSw = null, $defaultEn = null)
    {
        $locale = app()->getLocale();
        $localizedKey = "{$key}_{$locale}";
        // A saved value wins even when it is an empty string: clearing a text
        // in the admin panel is how owners hide optional elements.
        $val = SystemSetting::getVal($localizedKey);

        if ($val !== null) {
            return $val;
        }

        $plainVal = SystemSetting::getVal($key);
        if ($plainVal !== null) {
            return $plainVal;
        }

        return $locale === 'sw' ? $defaultSw : ($defaultEn ?? $defaultSw);
    }
}

if (! function_exists('current_organization')) {
    function current_organization(): ?Organization
    {
        if (session()->has('current_organization_id')) {
            return Organization::find(session('current_organization_id'));
        }
        if (auth()->check() && auth()->user()->organization) {
            return auth()->user()->organization;
        }

        return Organization::first();
    }
}

if (! function_exists('current_branding')) {
    function current_branding(): ?BrandingConfig
    {
        $org = current_organization();

        return $org?->branding ?: BrandingConfig::first();
    }
}

if (! function_exists('current_branch')) {
    function current_branch(): ?Branch
    {
        if (session()->has('current_branch_id') && session('current_branch_id') !== 'all') {
            return Branch::find(session('current_branch_id'));
        }
        if (auth()->check() && auth()->user()->branch) {
            return auth()->user()->branch;
        }

        return Branch::first();
    }
}

if (! function_exists('current_currency')) {
    function current_currency(): string
    {
        return config('app.default_currency', 'TZS');
    }
}

if (! function_exists('current_locale')) {
    function current_locale(): string
    {
        return app()->getLocale();
    }
}

if (! function_exists('current_app_environment')) {
    function current_app_environment(): string
    {
        $settingEnv = setting('app_environment');
        if (! empty($settingEnv)) {
            return (string) $settingEnv;
        }

        return (string) app()->environment();
    }
}

if (! function_exists('is_production_mode')) {
    function is_production_mode(): bool
    {
        return current_app_environment() === 'production';
    }
}

if (! function_exists('is_local_mode')) {
    function is_local_mode(): bool
    {
        return ! is_production_mode();
    }
}

if (! function_exists('brand_rgb')) {
    /**
     * Convert a hex colour (#0f52ba or #fff) into an "r, g, b" triplet for CSS rgba() tokens.
     */
    function brand_rgb(?string $hex, string $fallback = '15, 82, 186'): string
    {
        $hex = ltrim((string) $hex, '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        if (! preg_match('/^[0-9a-fA-F]{6}$/', $hex)) {
            return $fallback;
        }

        return implode(', ', [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))]);
    }
}

if (! function_exists('format_money_short')) {
    /**
     * Compact Tanzanian-market price label, e.g. "TSh 450M", "TSh 1.2B", "TSh 850K".
     */
    function format_money_short($amount, ?string $currency = null): string
    {
        $symbol = $currency ?: config('app.default_currency_symbol', 'TSh');
        $amount = (float) $amount;
        $abs = abs($amount);

        if ($abs >= 1_000_000_000) {
            $value = $abs / 1_000_000_000;
            $suffix = 'B';
        } elseif ($abs >= 1_000_000) {
            $value = $abs / 1_000_000;
            $suffix = 'M';
        } elseif ($abs >= 1_000) {
            $value = $abs / 1_000;
            $suffix = 'K';
        } else {
            return $symbol.' '.number_format($amount, 0, '.', ',');
        }

        $formatted = $value >= 100 ? number_format($value, 0) : rtrim(rtrim(number_format($value, 1), '0'), '.');

        return ($amount < 0 ? '-' : '').$symbol.' '.$formatted.$suffix;
    }
}

if (! function_exists('brand_shade')) {
    /**
     * Lighten (positive) or darken (negative) a hex colour by a percentage, e.g. brand_shade('#0f52ba', -15).
     */
    function brand_shade(?string $hex, float $percent): string
    {
        $rgb = explode(', ', brand_rgb($hex));
        $factor = max(-100, min(100, $percent)) / 100;

        $channels = array_map(function ($channel) use ($factor) {
            $channel = (int) $channel;
            $adjusted = $factor >= 0
                ? $channel + (255 - $channel) * $factor
                : $channel + $channel * $factor;

            return str_pad(dechex((int) round(max(0, min(255, $adjusted)))), 2, '0', STR_PAD_LEFT);
        }, $rgb);

        return '#'.implode('', $channels);
    }
}

if (! function_exists('tz_regions')) {
    /**
     * Tanzania's administrative regions (mainland + Zanzibar), for location pickers and filters.
     *
     * @return array<int, string>
     */
    function tz_regions(): array
    {
        return [
            'Arusha', 'Dar es Salaam', 'Dodoma', 'Geita', 'Iringa', 'Kagera', 'Katavi', 'Kigoma', 'Kilimanjaro',
            'Lindi', 'Manyara', 'Mara', 'Mbeya', 'Morogoro', 'Mtwara', 'Mwanza', 'Njombe', 'Pemba North',
            'Pemba South', 'Pwani', 'Rukwa', 'Ruvuma', 'Shinyanga', 'Simiyu', 'Singida', 'Songwe', 'Tabora',
            'Tanga', 'Zanzibar North', 'Zanzibar South', 'Zanzibar Urban/West',
        ];
    }
}

if (! function_exists('tz_payment_methods')) {
    /**
     * Payment channels commonly used in Tanzanian property transactions, keyed by stored value.
     *
     * @return array<string, string>
     */
    function tz_payment_methods(): array
    {
        return [
            'Bank Transfer' => __('app.payment_bank_transfer'),
            'M-Pesa' => 'M-Pesa (Vodacom)',
            'Tigo Pesa' => 'Mixx by Yas (Tigo Pesa)',
            'Airtel Money' => 'Airtel Money',
            'HaloPesa' => 'HaloPesa',
            'Cash' => __('app.payment_cash'),
            'Cheque' => __('app.payment_cheque'),
            'Card' => __('app.payment_card'),
        ];
    }
}

if (! function_exists('format_phone_tz')) {
    /**
     * Display a Tanzanian phone number as +255 7XX XXX XXX (falls back to the raw value).
     */
    function format_phone_tz(?string $phone): string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone) ?? '';

        if (str_starts_with($digits, '2550')) {
            $digits = '255'.substr($digits, 4);
        } elseif (str_starts_with($digits, '0')) {
            $digits = '255'.substr($digits, 1);
        } elseif (strlen($digits) === 9) {
            $digits = '255'.$digits;
        }

        if (strlen($digits) !== 12 || ! str_starts_with($digits, '255')) {
            return (string) $phone;
        }

        return '+'.substr($digits, 0, 3).' '.substr($digits, 3, 3).' '.substr($digits, 6, 3).' '.substr($digits, 9);
    }
}
