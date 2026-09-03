<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\BrandingConfig;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\LicensedModule;
use App\Models\Property;
use App\Models\SalesDeal;
use App\Models\SurveyProject;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SettingController extends Controller
{
    public function index()
    {
        $org = current_organization();
        $branding = $org?->branding ?: BrandingConfig::first();
        $modules = LicensedModule::all();
        $branches = Branch::all();
        $auditLogs = AuditLog::latest()->take(50)->get();

        // PushSMS Live Balance
        $smsBalance = SmsService::getBalance();

        // Feature Toggles state
        $featureToggles = [
            'property_owner_submissions' => is_module_enabled('property_owner_submissions', true),
            'marketplace_functions' => is_module_enabled('marketplace_functions', true),
            'online_reservations' => is_module_enabled('online_reservations', true),
            'online_bookings' => is_module_enabled('online_bookings', true),
            'payment_processing' => is_module_enabled('payment_processing', true),
            'whatsapp_notifications' => is_module_enabled('whatsapp_notifications', true),
            'sms_notifications' => is_module_enabled('sms_notifications', true),
            'blog_module' => is_module_enabled('blog_module', true),
            'testimonials_module' => is_module_enabled('testimonials_module', true),
            'crm_lead_tracking' => is_module_enabled('crm_lead_tracking', true),
        ];

        // Demo seed data statistics
        $demoStats = [
            'properties' => Property::count(),
            'customers' => Customer::count(),
            'leads' => Lead::count(),
            'deals' => SalesDeal::count(),
            'invoices' => Invoice::count(),
            'surveys' => SurveyProject::count(),
            'demo_users' => User::whereIn('email', ['agent@rehospace.com', 'surveyor@rehospace.com', 'finance@rehospace.com'])->count(),
        ];
        $demoStats['total'] = array_sum($demoStats);

        $currentEnv = current_app_environment();
        $isDebug = config('app.debug');

        return view('settings.index', compact(
            'org',
            'branding',
            'modules',
            'branches',
            'auditLogs',
            'smsBalance',
            'featureToggles',
            'demoStats',
            'currentEnv',
            'isDebug'
        ));
    }

    public function updateBranding(Request $request)
    {
        $request->validate([
            'header_logo_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'header_logo_url' => 'nullable|string|max:500',
            'favicon_file' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg,webp,ico|max:1024',
            'favicon_url' => 'nullable|string|max:500',
            'social_share_image_file' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'social_share_image_url' => 'nullable|string|max:500',
            'primary_color' => 'nullable|string|max:30',
            'secondary_color' => 'nullable|string|max:30',
            'accent_color' => 'nullable|string|max:30',
            'sidebar_theme' => 'nullable|string|max:30',
            'company_name' => 'nullable|string|max:255',
            'company_tagline' => 'nullable|string|max:255',
            'company_subtitle' => 'nullable|string|max:255',
            'brand_monogram' => 'nullable|string|max:10',
            'custom_css' => 'nullable|string|max:10000',
        ]);

        $org = current_organization();
        $branding = $org?->branding ?: new BrandingConfig(['organization_id' => $org?->id]);

        $branding->primary_color = $request->input('primary_color', '#0f52ba');
        $branding->secondary_color = $request->input('secondary_color', '#495057');
        $branding->accent_color = $request->input('accent_color', '#00a86b');
        $branding->sidebar_theme = $request->input('sidebar_theme', 'dark');
        $branding->company_tagline = $request->input('company_tagline');

        if ($request->hasFile('header_logo_file')) {
            $path = $request->file('header_logo_file')->store('branding', 'public');
            $branding->header_logo = '/storage/'.$path;
        } elseif ($request->filled('header_logo_url')) {
            $branding->header_logo = $request->input('header_logo_url');
        }

        if ($request->hasFile('favicon_file')) {
            $path = $request->file('favicon_file')->store('branding', 'public');
            $branding->favicon = '/storage/'.$path;
        } elseif ($request->filled('favicon_url')) {
            $branding->favicon = $request->input('favicon_url');
        }

        if ($request->has('custom_css')) {
            $branding->custom_css = $request->input('custom_css');
        }

        $branding->save();

        $orgId = $org?->id;
        if ($request->filled('company_name')) {
            SystemSetting::setVal('company_name', $request->company_name, 'branding', $orgId);
            if ($org) {
                $org->update(['name' => $request->company_name]);
            }
        }
        if ($request->has('company_subtitle')) {
            SystemSetting::setVal('company_subtitle', $request->input('company_subtitle', 'Real Estate & Land'), 'branding', $orgId);
        }
        if ($request->has('brand_monogram')) {
            SystemSetting::setVal('brand_monogram', $request->input('brand_monogram', 'R'), 'branding', $orgId);
        }

        if ($request->hasFile('social_share_image_file')) {
            $path = $request->file('social_share_image_file')->store('branding', 'public');
            SystemSetting::setVal('og_default_image', '/storage/'.$path, 'branding', $orgId);
        } elseif ($request->filled('social_share_image_url')) {
            SystemSetting::setVal('og_default_image', $request->input('social_share_image_url'), 'branding', $orgId);
        }

        Cache::flush();

        return back()->with('success', 'Tenant branding and identity configuration saved!');
    }

    public function updateLandingPage(Request $request)
    {
        $orgId = current_organization()?->id;

        // 1. Handle file uploads if present
        if ($request->hasFile('landing_hero_image_file')) {
            $path = $request->file('landing_hero_image_file')->store('landing', 'public');
            SystemSetting::setVal('landing_hero_image', '/storage/'.$path, 'landing', $orgId);
        } elseif ($request->filled('landing_hero_image_url')) {
            SystemSetting::setVal('landing_hero_image', $request->input('landing_hero_image_url'), 'landing', $orgId);
        }

        for ($i = 1; $i <= 5; $i++) {
            if ($request->hasFile("landing_location_{$i}_image_file")) {
                $path = $request->file("landing_location_{$i}_image_file")->store('landing/locations', 'public');
                SystemSetting::setVal("landing_location_{$i}_image", '/storage/'.$path, 'landing', $orgId);
            } elseif ($request->filled("landing_location_{$i}_image_url")) {
                SystemSetting::setVal("landing_location_{$i}_image", $request->input("landing_location_{$i}_image_url"), 'landing', $orgId);
            }
        }

        // 2. Section toggles
        $toggles = [
            'landing_topbar_enabled',
            'landing_nav_favorites_enabled',
            'landing_categories_enabled',
            'landing_featured_enabled',
            'landing_locations_enabled',
            'landing_latest_enabled',
            'landing_projects_enabled',
            'landing_land_enabled',
            'landing_services_enabled',
            'landing_trust_enabled',
            'landing_testimonials_enabled',
            'landing_blog_enabled',
            'landing_owner_cta_enabled',
            'landing_whatsapp_enabled',
        ];

        foreach ($toggles as $toggle) {
            if ($request->has($toggle)) {
                SystemSetting::setVal($toggle, $request->boolean($toggle) ? '1' : '0', 'landing', $orgId);
            }
        }

        // 3. Text and content configuration fields
        $fields = [
            // Topbar
            'landing_topbar_ticker_label',
            'landing_topbar_ticker_text',
            'landing_topbar_survey_text',
            'landing_topbar_survey_icon',
            'landing_topbar_survey_link',
            'landing_topbar_staff_text',
            'landing_topbar_staff_icon',
            // Navigation
            'landing_nav_home_label',
            'landing_nav_properties_label',
            'landing_nav_land_label',
            'landing_nav_developments_label',
            'landing_nav_locations_label',
            'landing_nav_services_label',
            'landing_nav_insights_label',
            'landing_nav_list_btn_text',
            'landing_nav_list_btn_icon',
            'landing_nav_list_btn_url',
            'landing_nav_login_btn_text',
            // Hero
            'landing_hero_badge_icon',
            'landing_hero_badge_text',
            'landing_hero_title',
            'landing_hero_subtitle',
            'landing_search_tab_buy',
            'landing_search_tab_rent',
            'landing_search_tab_land',
            'landing_search_tab_developments',
            'landing_search_btn_text',
            'landing_search_btn_icon',
            // Categories
            'landing_categories_tag',
            'landing_categories_title',
            'landing_categories_cta_text',
            'landing_categories_cta_url',
            // Featured Properties
            'landing_featured_tag',
            'landing_featured_title',
            'landing_featured_cta_text',
            'landing_featured_cta_url',
            // Explore Locations
            'landing_locations_tag',
            'landing_locations_title',
            'landing_locations_cta_text',
            'landing_locations_cta_url',
            // Locations 1 to 5
            'landing_location_1_name',
            'landing_location_1_desc',
            'landing_location_2_name',
            'landing_location_2_desc',
            'landing_location_3_name',
            'landing_location_3_desc',
            'landing_location_4_name',
            'landing_location_4_desc',
            'landing_location_5_name',
            'landing_location_5_desc',
            // Newly Listed
            'landing_latest_tag',
            'landing_latest_title',
            'landing_latest_cta_text',
            'landing_latest_cta_url',
            // Projects
            'landing_projects_tag',
            'landing_projects_title',
            'landing_projects_cta_text',
            'landing_projects_cta_url',
            // Land Marketplace
            'landing_land_tag',
            'landing_land_title',
            'landing_land_cta_text',
            'landing_land_cta_url',
            // Services
            'landing_services_tag',
            'landing_services_title',
            'landing_services_subtitle',
            'landing_service_1_icon',
            'landing_service_1_title',
            'landing_service_1_desc',
            'landing_service_1_btn_text',
            'landing_service_1_btn_url',
            'landing_service_2_icon',
            'landing_service_2_title',
            'landing_service_2_desc',
            'landing_service_2_btn_text',
            'landing_service_2_btn_url',
            'landing_service_3_icon',
            'landing_service_3_title',
            'landing_service_3_desc',
            'landing_service_3_btn_text',
            'landing_service_3_btn_url',
            // Trust Protocol
            'landing_trust_badge',
            'landing_trust_title',
            'landing_trust_desc',
            'landing_trust_1_icon',
            'landing_trust_1_title',
            'landing_trust_1_desc',
            'landing_trust_2_icon',
            'landing_trust_2_title',
            'landing_trust_2_desc',
            'landing_trust_3_icon',
            'landing_trust_3_title',
            'landing_trust_3_desc',
            // Dynamic Stats overrides & labels
            'landing_stat_1_label',
            'landing_stat_1_override',
            'landing_stat_2_label',
            'landing_stat_2_override',
            'landing_stat_3_label',
            'landing_stat_3_override',
            'landing_stat_4_label',
            'landing_stat_4_override',
            // Testimonials
            'landing_testimonials_tag',
            'landing_testimonials_title',
            'landing_testimonials_subtitle',
            // Blog
            'landing_blog_tag',
            'landing_blog_title',
            'landing_blog_cta_text',
            'landing_blog_cta_url',
            // Owner CTA
            'landing_owner_cta_title',
            'landing_owner_cta_desc',
            'landing_owner_cta_1_text',
            'landing_owner_cta_1_icon',
            'landing_owner_cta_1_url',
            'landing_owner_cta_2_text',
            'landing_owner_cta_2_icon',
            'landing_owner_cta_2_url',
            // Footer
            'footer_bio',
            'footer_newsletter_title',
            'footer_newsletter_subtitle',
            'footer_copyright',
            // WhatsApp
            'landing_whatsapp_message',
        ];

        foreach ($fields as $field) {
            if ($request->has($field)) {
                SystemSetting::setVal($field, (string) $request->input($field), 'landing', $orgId);
            }
        }

        Cache::flush();

        return back()->with('success', 'Public landing page & CMS configuration updated successfully!');
    }

    public function updatePushSms(Request $request)
    {
        $request->validate([
            'pushsms_base_url' => 'nullable|url',
            'pushsms_api_key' => 'nullable|string|max:255',
            'pushsms_sender_id' => 'required|string|max:11',
            'pushsms_client_app' => 'required|string|max:50',
        ]);

        $orgId = current_organization()?->id;
        $baseUrl = $request->pushsms_base_url ?: 'https://pushsms.rehospace.com';

        SystemSetting::setVal('pushsms_base_url', rtrim($baseUrl, '/'), 'sms', $orgId);
        if ($request->filled('pushsms_api_key')) {
            SystemSetting::setVal('pushsms_api_key', $request->pushsms_api_key, 'sms', $orgId);
        }
        SystemSetting::setVal('pushsms_sender_id', strtoupper($request->pushsms_sender_id), 'sms', $orgId);
        SystemSetting::setVal('pushsms_client_app', $request->pushsms_client_app, 'sms', $orgId);
        SystemSetting::setVal('sms_enabled', $request->boolean('sms_enabled') ? '1' : '0', 'sms', $orgId);

        // SMS Templates
        if ($request->filled('sms_template_event_a')) {
            SystemSetting::setVal('sms_template_event_a', $request->sms_template_event_a, 'sms_templates', $orgId);
        }
        if ($request->filled('sms_template_event_b')) {
            SystemSetting::setVal('sms_template_event_b', $request->sms_template_event_b, 'sms_templates', $orgId);
        }
        if ($request->filled('sms_template_invoice_issued')) {
            SystemSetting::setVal('sms_template_invoice_issued', $request->sms_template_invoice_issued, 'sms_templates', $orgId);
        }

        return back()->with('success', 'PushSMS gateway configuration & SMS templates updated successfully!');
    }

    public function updateFeatureToggles(Request $request)
    {
        $orgId = current_organization()?->id;

        $toggles = [
            'property_owner_submissions',
            'marketplace_functions',
            'online_reservations',
            'online_bookings',
            'payment_processing',
            'whatsapp_notifications',
            'sms_notifications',
            'blog_module',
            'testimonials_module',
            'crm_lead_tracking',
        ];

        foreach ($toggles as $toggleKey) {
            $isEnabled = $request->boolean($toggleKey) || $request->boolean("feature_{$toggleKey}");
            SystemSetting::setVal("feature_{$toggleKey}_enabled", $isEnabled ? '1' : '0', 'features', $orgId);

            // Also synchronize with LicensedModule table if exists
            $module = LicensedModule::where('module_slug', $toggleKey)->first();
            if ($module) {
                $module->update(['is_enabled' => $isEnabled]);
            }
        }

        return back()->with('success', 'Dynamic module feature toggles updated successfully!');
    }

    public function updateSocial(Request $request)
    {
        $orgId = current_organization()?->id;

        $fields = [
            'contact_phone',
            'contact_whatsapp',
            'contact_email',
            'contact_address',
            'social_facebook',
            'social_instagram',
            'social_threads',
            'social_pinterest',
            'social_google_business',
            'social_tiktok',
        ];

        foreach ($fields as $field) {
            SystemSetting::setVal($field, $request->input($field, ''), 'social', $orgId);
        }

        return back()->with('success', 'Contact info and social media profile hooks saved!');
    }

    public function checkSmsBalance()
    {
        $balance = SmsService::getBalance();

        return response()->json($balance);
    }

    public function toggleModule(Request $request, LicensedModule $module)
    {
        $module->update(['is_enabled' => $request->boolean('is_enabled')]);

        return response()->json(['success' => true, 'message' => "Module {$module->module_name} status updated."]);
    }

    public function switchBranch(Request $request)
    {
        $branchId = $request->input('branch_id');
        if ($branchId === 'all') {
            session(['current_branch_id' => 'all']);
        } else {
            session(['current_branch_id' => (int) $branchId]);
        }

        return back()->with('info', 'Active branch context switched.');
    }

    public function switchEnvironment(Request $request)
    {
        $request->validate([
            'environment' => 'required|in:production,local',
        ]);

        $env = $request->input('environment');
        $isProd = ($env === 'production');

        // 1. Update .env file
        $this->updateEnvironmentFile([
            'APP_ENV' => $env,
            'APP_DEBUG' => $isProd ? 'false' : 'true',
        ]);

        // 2. Update persistent system settings
        SystemSetting::setVal('app_environment', $env, 'system');
        SystemSetting::setVal('app_debug', $isProd ? '0' : '1', 'system');
        config(['app.env' => $env, 'app.debug' => ! $isProd]);

        // 3. Handle data cleanup/seeding
        $purgedCount = 0;
        if ($isProd && $request->boolean('purge_demo_data', true)) {
            $purgedCount = $this->executePurgeDemoData();
        } elseif (! $isProd && $request->boolean('reseed_demo_data', false)) {
            try {
                Artisan::call('db:seed', ['--force' => true]);
            } catch (\Throwable $e) {
                // Seeding fallback
            }
        }

        try {
            Artisan::call('config:clear');
        } catch (\Throwable $e) {
            // Ignore during tests
        }

        Cache::flush();

        AuditLog::create([
            'organization_id' => current_organization()?->id,
            'user_id' => auth()->id(),
            'user_name' => auth()->user()?->name ?? 'System',
            'event' => 'environment_switch',
            'new_values' => ['environment' => $env, 'purged_records' => $purgedCount],
        ]);

        $message = $isProd
            ? 'Platform successfully brought LIVE into PRODUCTION mode! Quick 1-Click Role Logins disabled'.($purgedCount > 0 ? " and {$purgedCount} demo records purged." : '.')
            : 'Platform successfully switched to LOCAL / DEVELOPMENT mode. Quick 1-Click Role Logins enabled.';

        return back()->with('success', $message);
    }

    public function purgeDemoData(Request $request)
    {
        $purgedCount = $this->executePurgeDemoData();

        AuditLog::create([
            'organization_id' => current_organization()?->id,
            'user_id' => auth()->id(),
            'user_name' => auth()->user()?->name ?? 'System',
            'event' => 'demo_data_purge',
            'new_values' => ['purged_records' => $purgedCount],
        ]);

        return back()->with('success', "Successfully removed {$purgedCount} unneeded demo/seeded records from the database!");
    }

    public function seedDemoData(Request $request)
    {
        try {
            Artisan::call('db:seed', ['--force' => true]);
            Cache::flush();

            return back()->with('success', 'Demo data successfully seeded for development/testing.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Failed to seed demo data: '.$e->getMessage());
        }
    }

    public function executePurgeDemoData(): int
    {
        $deletedCount = 0;
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        } elseif ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF;');
        }

        try {
            $tables = [
                'payments',
                'installment_schedules',
                'invoice_items',
                'invoices',
                'sales_deals',
                'reservations',
                'appointments',
                'lead_activities',
                'leads',
                'survey_beacons',
                'survey_milestones',
                'survey_projects',
                'maintenance_requests',
                'rent_schedules',
                'leases',
                'tenants',
                'marketing_campaigns',
                'approval_logs',
                'loyalty_point_transactions',
                'loyalty_rewards',
                'property_inquiries',
                'property_media',
                'property_amenity',
                'property_units',
                'land_parcels',
                'properties',
                'property_owners',
                'real_estate_projects',
                'agent_commissions',
                'agents',
                'expenses',
                'customers',
            ];

            foreach ($tables as $table) {
                if (Schema::hasTable($table)) {
                    $deletedCount += DB::table($table)->delete();
                }
            }

            // Remove demo accounts permanently while protecting super admin and current user
            $currentUserId = auth()->id() ?? 0;
            $demoEmails = ['agent@rehospace.com', 'surveyor@rehospace.com', 'finance@rehospace.com'];
            $demoUserIds = DB::table('users')
                ->whereIn('email', $demoEmails)
                ->where('id', '!=', $currentUserId)
                ->where('email', '!=', 'admin@rehospace.com')
                ->pluck('id');

            if ($demoUserIds->isNotEmpty()) {
                if (Schema::hasTable('role_user')) {
                    DB::table('role_user')->whereIn('user_id', $demoUserIds)->delete();
                }
                if (Schema::hasTable('model_has_roles')) {
                    DB::table('model_has_roles')->whereIn('model_id', $demoUserIds)->delete();
                }
                $deletedCount += DB::table('users')->whereIn('id', $demoUserIds)->delete();
            }
        } finally {
            if ($driver === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            } elseif ($driver === 'sqlite') {
                DB::statement('PRAGMA foreign_keys = ON;');
            }
        }

        Cache::flush();

        return $deletedCount;
    }

    protected function updateEnvironmentFile(array $values): bool
    {
        $envPath = base_path('.env');
        if (! file_exists($envPath) || ! is_writable($envPath)) {
            return false;
        }

        $content = file_get_contents($envPath);

        foreach ($values as $key => $val) {
            $valStr = is_bool($val) ? ($val ? 'true' : 'false') : (string) $val;
            $pattern = "/^{$key}=.*/m";
            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, "{$key}={$valStr}", $content);
            } else {
                $content .= "\n{$key}={$valStr}";
            }
        }

        file_put_contents($envPath, $content);

        return true;
    }
}
