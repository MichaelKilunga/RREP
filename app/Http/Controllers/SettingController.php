<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\BrandingConfig;
use App\Models\LicensedModule;
use App\Models\SystemSetting;
use App\Services\SmsService;
use Illuminate\Http\Request;

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

        return view('settings.index', compact(
            'org',
            'branding',
            'modules',
            'branches',
            'auditLogs',
            'smsBalance',
            'featureToggles'
        ));
    }

    public function updateBranding(Request $request)
    {
        $org = current_organization();
        $branding = $org?->branding ?: new BrandingConfig(['organization_id' => $org?->id]);

        $branding->primary_color = $request->input('primary_color', '#0f52ba');
        $branding->secondary_color = $request->input('secondary_color', '#495057');
        $branding->accent_color = $request->input('accent_color', '#00a86b');
        $branding->sidebar_theme = $request->input('sidebar_theme', 'dark');
        $branding->company_tagline = $request->input('company_tagline');
        $branding->save();

        if ($request->filled('company_name')) {
            SystemSetting::setVal('company_name', $request->company_name, 'branding', $org?->id);
        }

        return back()->with('success', 'Tenant branding customization saved!');
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
            $isEnabled = $request->boolean($toggleKey);
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
}
