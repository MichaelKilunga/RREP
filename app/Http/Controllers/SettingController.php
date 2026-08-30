<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\BrandingConfig;
use App\Models\LicensedModule;
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

        return view('settings.index', compact('org', 'branding', 'modules', 'branches', 'auditLogs'));
    }

    public function updateBranding(Request $request)
    {
        $org = current_organization();
        $branding = $org->branding ?: new BrandingConfig(['organization_id' => $org->id]);

        $branding->primary_color = $request->input('primary_color', '#0f52ba');
        $branding->secondary_color = $request->input('secondary_color', '#495057');
        $branding->accent_color = $request->input('accent_color', '#00a86b');
        $branding->sidebar_theme = $request->input('sidebar_theme', 'dark');
        $branding->company_tagline = $request->input('company_tagline');
        $branding->save();

        return back()->with('success', 'Tenant branding customization saved!');
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
