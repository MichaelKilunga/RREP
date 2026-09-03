<?php

use App\Core\Licensing\LicenseManager;
use App\Models\Branch;
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
