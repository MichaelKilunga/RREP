<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

/**
 * The RBAC catalog: every permission the app enforces (via Gate::before ->
 * User::hasPermission) and the default grants for the built-in roles.
 * Idempotent and additive: grants owners removed in the matrix are only
 * re-added for permissions that did not exist before.
 */
class RolePermissionSeeder extends Seeder
{
    /** @var array<string, array{name: string, desc: string}> */
    public const ROLES = [
        'super_admin' => ['name' => 'Super Administrator', 'desc' => 'Complete full system control across all tenants.'],
        'org_admin' => ['name' => 'Organization Administrator', 'desc' => 'Full administrative access to the organization and all branches.'],
        'branch_manager' => ['name' => 'Branch Manager', 'desc' => 'Runs projects, services, CRM, sales and finance for a branch; cannot change roles or system settings.'],
        'sales_agent' => ['name' => 'Sales Agent / Broker', 'desc' => 'Handles requests, leads, customers, viewings and plot reservations.'],
        'property_manager' => ['name' => 'Property & Rental Manager', 'desc' => 'Oversees rental properties, units, tenants, leases, and maintenance.'],
        'accountant' => ['name' => 'Finance & Accountant', 'desc' => 'Manages invoices, payments, expenses and financial reports.'],
        'surveyor' => ['name' => 'Land Surveyor & GIS Specialist', 'desc' => 'Conducts cadastral surveys, plot layouts and GIS mapping.'],
        'customer' => ['name' => 'Customer / Buyer', 'desc' => 'Client portal access for saved plots, reservations and invoices.'],
        'property_owner' => ['name' => 'Property Owner / Landlord', 'desc' => 'Owner portal access to portfolios, revenue splits, and statements.'],
    ];

    /** @var array<string, array<int, string>> */
    public const PERMISSIONS = [
        'core' => ['view_dashboard', 'manage_settings', 'view_audit_logs', 'manage_branding'],
        'projects' => ['view_projects', 'manage_projects', 'delete_projects'],
        'services' => ['view_services', 'manage_services'],
        'properties' => ['view_properties', 'create_properties', 'edit_properties', 'delete_properties', 'manage_units', 'manage_parcels'],
        'crm' => ['view_requests', 'manage_requests', 'view_leads', 'create_leads', 'edit_leads', 'assign_leads', 'delete_leads', 'view_customers', 'manage_customers', 'delete_customers'],
        'sales' => ['create_reservations', 'manage_reservations', 'manage_appointments', 'create_deals', 'manage_deals'],
        'finance' => ['view_invoices', 'create_invoices', 'record_payments', 'void_invoices', 'view_expenses', 'manage_expenses', 'manage_commissions'],
        'reports' => ['view_reports', 'view_financial_reports', 'export_reports'],
        'users' => ['view_users', 'manage_users', 'manage_roles'],
        'communications' => ['view_sms_logs', 'send_sms', 'manage_sms'],
        'documents' => ['view_documents', 'manage_documents'],
        'rentals' => ['view_leases', 'create_leases', 'manage_rent_schedules', 'manage_maintenance'],
        'survey' => ['view_surveys', 'create_surveys', 'edit_beacons', 'approve_milestones', 'view_gis_map'],
        'marketing' => ['view_campaigns', 'create_campaigns', 'send_broadcasts'],
        'ai' => ['use_ai_assistant', 'generate_ai_descriptions', 'use_smart_search'],
    ];

    /**
     * Default grants: '*' = everything, 'module.*' = a whole module, or 'module.permission'.
     *
     * @var array<string, array<int, string>>
     */
    public const ROLE_GRANTS = [
        'super_admin' => ['*'],
        'org_admin' => ['*'],
        'branch_manager' => [
            'core.view_dashboard', 'projects.*', 'services.*', 'properties.*', 'crm.*', 'sales.*', 'finance.*', 'reports.*',
            'users.view_users', 'communications.view_sms_logs', 'communications.send_sms', 'documents.*',
            'rentals.*', 'survey.*', 'marketing.*', 'ai.*',
        ],
        'sales_agent' => [
            'core.view_dashboard', 'projects.view_projects', 'services.view_services', 'properties.*',
            'crm.view_requests', 'crm.manage_requests', 'crm.view_leads', 'crm.create_leads', 'crm.edit_leads', 'crm.assign_leads',
            'crm.view_customers', 'crm.manage_customers', 'sales.*', 'reports.view_reports', 'communications.send_sms',
            'documents.view_documents', 'ai.*',
        ],
        'property_manager' => [
            'core.view_dashboard', 'properties.*', 'rentals.*', 'finance.*', 'projects.view_projects', 'crm.view_customers',
            'reports.view_reports', 'documents.*',
        ],
        'accountant' => [
            'core.view_dashboard', 'finance.*', 'reports.*', 'crm.view_customers', 'projects.view_projects',
            'communications.send_sms', 'documents.view_documents',
        ],
        'surveyor' => [
            'core.view_dashboard', 'survey.*', 'properties.*', 'projects.view_projects', 'projects.manage_projects', 'documents.*',
        ],
        'customer' => [],
        'property_owner' => [],
    ];

    public function run(): void
    {
        $roleModels = [];
        foreach (self::ROLES as $slug => $info) {
            $roleModels[$slug] = Role::firstOrCreate(['name' => $slug], [
                'display_name' => $info['name'],
                'description' => $info['desc'],
                'is_system' => true,
            ]);
        }

        $permissionIds = [];
        foreach (self::PERMISSIONS as $module => $names) {
            foreach ($names as $name) {
                $permission = Permission::firstOrCreate(['name' => $name], [
                    'module' => $module,
                    'display_name' => ucwords(str_replace('_', ' ', $name)),
                ]);
                if ($permission->module !== $module) {
                    $permission->update(['module' => $module]);
                }
                $permissionIds["{$module}.{$name}"] = $permission->id;
            }
        }

        foreach (self::ROLE_GRANTS as $slug => $grants) {
            $ids = [];
            foreach ($grants as $grant) {
                foreach ($permissionIds as $key => $id) {
                    if ($grant === '*' || $grant === $key || (str_ends_with($grant, '.*') && str_starts_with($key, substr($grant, 0, -1)))) {
                        $ids[] = $id;
                    }
                }
            }
            $roleModels[$slug]->permissions()->syncWithoutDetaching(array_unique($ids));
        }
    }
}
