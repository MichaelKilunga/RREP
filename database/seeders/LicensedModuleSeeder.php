<?php

namespace Database\Seeders;

use App\Models\LicensedModule;
use Illuminate\Database\Seeder;

class LicensedModuleSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [
            // Foundation Modules
            ['code' => 'FM-001', 'name' => 'Core Platform & Organizations', 'slug' => 'core', 'cat' => 'Foundation', 'is_core' => true],
            ['code' => 'FM-002', 'name' => 'Multi-Guard Authentication', 'slug' => 'auth', 'cat' => 'Foundation', 'is_core' => true],
            ['code' => 'FM-003', 'name' => 'Role & Permission Management (RBAC)', 'slug' => 'rbac', 'cat' => 'Foundation', 'is_core' => true],
            ['code' => 'FM-004', 'name' => 'System Configuration Engine', 'slug' => 'config', 'cat' => 'Foundation', 'is_core' => true],
            ['code' => 'FM-005', 'name' => 'Branding & White-Label Customization', 'slug' => 'branding', 'cat' => 'Foundation', 'is_core' => true],
            ['code' => 'FM-006', 'name' => 'Security & OWASP Framework', 'slug' => 'security', 'cat' => 'Foundation', 'is_core' => true],
            ['code' => 'FM-007', 'name' => 'Centralized Audit & Activity Logs', 'slug' => 'audit', 'cat' => 'Foundation', 'is_core' => true],
            ['code' => 'FM-008', 'name' => 'Multi-Channel Notification Dispatcher', 'slug' => 'notifications', 'cat' => 'Foundation', 'is_core' => true],
            ['code' => 'FM-009', 'name' => 'Media & EDMS Document Vault', 'slug' => 'media', 'cat' => 'Foundation', 'is_core' => true],

            // Business Modules
            ['code' => 'BM-001', 'name' => 'Property & Asset Management', 'slug' => 'property_management', 'cat' => 'Core Business', 'is_core' => true],
            ['code' => 'BM-002', 'name' => 'Public Marketplace & Search Portal', 'slug' => 'marketplace', 'cat' => 'Growth', 'is_core' => false],
            ['code' => 'BM-003', 'name' => 'Customer Relationship Management (CRM)', 'slug' => 'crm', 'cat' => 'Core Business', 'is_core' => true],
            ['code' => 'BM-004', 'name' => 'Property Owners & Landlord Portfolios', 'slug' => 'property_owners', 'cat' => 'Core Business', 'is_core' => false],
            ['code' => 'BM-005', 'name' => 'Agents & Commission Tracking', 'slug' => 'agents', 'cat' => 'Core Business', 'is_core' => false],
            ['code' => 'BM-006', 'name' => 'Property Hold & Reservation Engine', 'slug' => 'reservations', 'cat' => 'Core Business', 'is_core' => false],
            ['code' => 'BM-007', 'name' => 'Appointment & Viewing Scheduler', 'slug' => 'appointments', 'cat' => 'Core Business', 'is_core' => false],
            ['code' => 'BM-008', 'name' => 'Land Survey & Geospatial GIS Engine', 'slug' => 'survey', 'cat' => 'Core Business', 'is_core' => false],
            ['code' => 'BM-009', 'name' => 'Sales Pipeline & Deal Transactions', 'slug' => 'sales', 'cat' => 'Core Business', 'is_core' => true],
            ['code' => 'BM-010', 'name' => 'EDMS Title Deed & Document Records', 'slug' => 'documents', 'cat' => 'Core Business', 'is_core' => false],
            ['code' => 'BM-011', 'name' => 'Finance, Billing & Installments', 'slug' => 'finance', 'cat' => 'Core Business', 'is_core' => true],
            ['code' => 'BM-012', 'name' => 'Marketing Campaigns & Broadcasts', 'slug' => 'marketing', 'cat' => 'Growth', 'is_core' => false],
            ['code' => 'BM-013', 'name' => 'Self-Service Portals (Client & Owner)', 'slug' => 'portals', 'cat' => 'Growth', 'is_core' => false],
            ['code' => 'BM-014', 'name' => 'Workflow & Multi-Step Approvals', 'slug' => 'workflows', 'cat' => 'Core Business', 'is_core' => false],
            ['code' => 'BM-015', 'name' => 'Business Intelligence & Reporting', 'slug' => 'analytics', 'cat' => 'Core Business', 'is_core' => true],
            ['code' => 'BM-016', 'name' => 'REST API v1 & Integration Gateway', 'slug' => 'api', 'cat' => 'Intelligence', 'is_core' => false],
            ['code' => 'BM-017', 'name' => 'Governance, Compliance & KYC', 'slug' => 'governance', 'cat' => 'Foundation', 'is_core' => false],
            ['code' => 'BM-018', 'name' => 'System Administration & Feature Flags', 'slug' => 'administration', 'cat' => 'Foundation', 'is_core' => true],
            ['code' => 'BM-019', 'name' => 'Regulatory & Transaction Auditing', 'slug' => 'compliance', 'cat' => 'Foundation', 'is_core' => false],
            ['code' => 'BM-020', 'name' => 'Artificial Intelligence & Automation', 'slug' => 'ai', 'cat' => 'Intelligence', 'is_core' => false],
        ];

        foreach ($modules as $m) {
            LicensedModule::firstOrCreate(['module_code' => $m['code']], [
                'module_name' => $m['name'],
                'module_slug' => $m['slug'],
                'category' => $m['cat'],
                'is_enabled' => true,
                'is_core' => $m['is_core'],
                'version' => '1.0.0',
                'license_tier' => 'Enterprise',
            ]);
        }
    }
}
