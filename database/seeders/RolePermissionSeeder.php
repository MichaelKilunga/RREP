<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'super_admin' => ['name' => 'Super Administrator', 'desc' => 'Complete full system control across all tenants.'],
            'org_admin' => ['name' => 'Organization Administrator', 'desc' => 'Full administrative access to the organization and all branches.'],
            'branch_manager' => ['name' => 'Branch Manager', 'desc' => 'Manages operational branch properties, agents, and transactions.'],
            'sales_agent' => ['name' => 'Sales Agent / Broker', 'desc' => 'Handles leads, properties, viewing appointments, and sales deals.'],
            'property_manager' => ['name' => 'Property & Rental Manager', 'desc' => 'Oversees rental properties, units, tenants, leases, and maintenance.'],
            'accountant' => ['name' => 'Finance & Accountant', 'desc' => 'Manages invoices, payments, installments, expenses, and ledgers.'],
            'surveyor' => ['name' => 'Land Surveyor & GIS Specialist', 'desc' => 'Conducts cadastral boundary surveys, beaconing, and GIS mapping.'],
            'customer' => ['name' => 'Customer / Buyer', 'desc' => 'Public buyer/tenant portal access for saved properties and invoices.'],
            'property_owner' => ['name' => 'Property Owner / Landlord', 'desc' => 'Portal access to view property portfolios, revenue splits, and statements.'],
        ];

        $roleModels = [];
        foreach ($roles as $slug => $info) {
            $roleModels[$slug] = Role::firstOrCreate(['name' => $slug], [
                'display_name' => $info['name'],
                'description' => $info['desc'],
                'is_system' => true,
            ]);
        }

        $permissions = [
            'core' => ['view_dashboard', 'manage_settings', 'view_audit_logs', 'manage_branding'],
            'properties' => ['view_properties', 'create_properties', 'edit_properties', 'delete_properties', 'manage_units', 'manage_parcels'],
            'crm' => ['view_leads', 'create_leads', 'edit_leads', 'assign_leads', 'view_customers', 'manage_customers'],
            'sales' => ['create_reservations', 'manage_reservations', 'manage_appointments', 'create_deals', 'manage_deals'],
            'finance' => ['view_invoices', 'create_invoices', 'record_payments', 'view_expenses', 'manage_commissions'],
            'rentals' => ['view_leases', 'create_leases', 'manage_rent_schedules', 'manage_maintenance'],
            'survey' => ['view_surveys', 'create_surveys', 'edit_beacons', 'approve_milestones', 'view_gis_map'],
            'marketing' => ['view_campaigns', 'create_campaigns', 'send_broadcasts'],
            'ai' => ['use_ai_assistant', 'generate_ai_descriptions', 'use_smart_search'],
        ];

        foreach ($permissions as $module => $perms) {
            foreach ($perms as $pName) {
                $p = Permission::firstOrCreate(['module' => $module, 'name' => $pName], [
                    'display_name' => ucwords(str_replace('_', ' ', $pName)),
                ]);

                // Assign to super_admin and org_admin
                $roleModels['super_admin']->permissions()->syncWithoutDetaching([$p->id]);
                $roleModels['org_admin']->permissions()->syncWithoutDetaching([$p->id]);

                // Assign specific permissions to roles
                if (in_array($module, ['properties', 'crm', 'sales', 'ai'])) {
                    $roleModels['sales_agent']->permissions()->syncWithoutDetaching([$p->id]);
                    $roleModels['branch_manager']->permissions()->syncWithoutDetaching([$p->id]);
                }
                if (in_array($module, ['properties', 'rentals', 'finance'])) {
                    $roleModels['property_manager']->permissions()->syncWithoutDetaching([$p->id]);
                }
                if (in_array($module, ['finance'])) {
                    $roleModels['accountant']->permissions()->syncWithoutDetaching([$p->id]);
                }
                if (in_array($module, ['survey', 'properties'])) {
                    $roleModels['surveyor']->permissions()->syncWithoutDetaching([$p->id]);
                }
            }
        }
    }
}
