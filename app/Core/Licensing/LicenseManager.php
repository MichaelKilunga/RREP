<?php

namespace App\Core\Licensing;

use App\Models\LicensedModule;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class LicenseManager
{
    protected const CACHE_KEY = 'rrep_licensed_modules';

    /**
     * Check if a given module code is enabled.
     * e.g. 'BM-001', 'property_management', 'survey', 'ai'
     */
    public function isEnabled(string $moduleKey): bool
    {
        $modules = $this->getActiveModules();
        $normalizedKey = strtolower(trim($moduleKey));

        return isset($modules[$normalizedKey]) && $modules[$normalizedKey] === true;
    }

    /**
     * Retrieve all active modules list as key-value pairs.
     */
    public function getActiveModules(): array
    {
        return Cache::remember(self::CACHE_KEY, 3600, function () {
            try {
                if (! Schema::hasTable('licensed_modules')) {
                    return $this->getDefaultModules();
                }

                $records = LicensedModule::all();
                if ($records->isEmpty()) {
                    return $this->getDefaultModules();
                }

                $map = [];
                foreach ($records as $r) {
                    $map[strtolower($r->module_code)] = (bool) $r->is_enabled;
                    $map[strtolower($r->module_slug)] = (bool) $r->is_enabled;
                }

                return $map;
            } catch (\Throwable $e) {
                return $this->getDefaultModules();
            }
        });
    }

    /**
     * Clear the cached license configuration.
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Default modules dictionary.
     */
    public function getDefaultModules(): array
    {
        return [
            'fm-001' => true, 'core' => true,
            'fm-002' => true, 'auth' => true,
            'fm-003' => true, 'rbac' => true,
            'fm-004' => true, 'config' => true,
            'fm-005' => true, 'branding' => true,
            'fm-006' => true, 'security' => true,
            'fm-007' => true, 'audit' => true,
            'fm-008' => true, 'notifications' => true,
            'fm-009' => true, 'media' => true,
            'bm-001' => true, 'property' => true, 'property_management' => true,
            'bm-002' => true, 'marketplace' => true,
            'bm-003' => true, 'crm' => true, 'leads' => true,
            'bm-004' => true, 'property_owners' => true, 'landlords' => true,
            'bm-005' => true, 'agents' => true,
            'bm-006' => true, 'reservations' => true,
            'bm-007' => true, 'appointments' => true,
            'bm-008' => true, 'survey' => true, 'gis' => true,
            'bm-009' => true, 'sales' => true, 'transactions' => true,
            'bm-010' => true, 'documents' => true, 'edms' => true,
            'bm-011' => true, 'finance' => true, 'billing' => true,
            'bm-012' => true, 'marketing' => true,
            'bm-013' => true, 'portals' => true,
            'bm-014' => true, 'workflows' => true,
            'bm-015' => true, 'analytics' => true, 'reports' => true,
            'bm-016' => true, 'api' => true, 'integrations' => true,
            'bm-017' => true, 'governance' => true,
            'bm-018' => true, 'administration' => true,
            'bm-019' => true, 'compliance' => true,
            'bm-020' => true, 'ai' => true, 'smart_automation' => true,
        ];
    }
}
