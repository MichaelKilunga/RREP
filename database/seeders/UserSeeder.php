<?php

namespace Database\Seeders;

use App\Models\Agent;
use App\Models\Branch;
use App\Models\Organization;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $org = Organization::first();
        $mainBranch = Branch::where('is_main', true)->first();
        $arushaBranch = Branch::where('code', 'ARU-02')->first();

        // 1. Super Admin
        $admin = User::firstOrCreate(['email' => 'admin@rehospace.com'], [
            'organization_id' => $org->id,
            'branch_id' => $mainBranch->id,
            'name' => 'Michael Kilunga (Admin)',
            'first_name' => 'Michael',
            'last_name' => 'Kilunga',
            'phone' => '+255 754 111 222',
            'user_type' => 'Staff',
            'job_title' => 'Chief Executive & Principal Broker',
            'status' => 'Active',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $superAdminRole = Role::where('name', 'super_admin')->first();
        if ($superAdminRole) {
            $admin->roles()->syncWithoutDetaching([$superAdminRole->id]);
        }

        // 2. Sales Agent
        $agentUser = User::firstOrCreate(['email' => 'agent@rehospace.com'], [
            'organization_id' => $org->id,
            'branch_id' => $mainBranch->id,
            'name' => 'Baraka John (Senior Agent)',
            'first_name' => 'Baraka',
            'last_name' => 'John',
            'phone' => '+255 755 333 444',
            'user_type' => 'Agent',
            'job_title' => 'Senior Real Estate Consultant',
            'status' => 'Active',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $salesAgentRole = Role::where('name', 'sales_agent')->first();
        if ($salesAgentRole) {
            $agentUser->roles()->syncWithoutDetaching([$salesAgentRole->id]);
        }

        Agent::firstOrCreate(['user_id' => $agentUser->id], [
            'organization_id' => $org->id,
            'branch_id' => $mainBranch->id,
            'license_number' => 'BRELA-AGT-8891',
            'designation' => 'Senior Property Consultant',
            'commission_rate' => 5.00,
            'total_sales_volume' => 380000000.00,
            'active_deals_count' => 3,
            'hire_date' => '2023-01-15',
            'status' => 'Active',
        ]);

        // 3. Land Surveyor
        $surveyorUser = User::firstOrCreate(['email' => 'surveyor@rehospace.com'], [
            'organization_id' => $org->id,
            'branch_id' => $mainBranch->id,
            'name' => 'Eng. Grace Mwamburi (Surveyor)',
            'first_name' => 'Grace',
            'last_name' => 'Mwamburi',
            'phone' => '+255 756 555 666',
            'user_type' => 'Staff',
            'job_title' => 'Chief Geospatial Surveyor',
            'status' => 'Active',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $surveyorRole = Role::where('name', 'surveyor')->first();
        if ($surveyorRole) {
            $surveyorUser->roles()->syncWithoutDetaching([$surveyorRole->id]);
        }

        // 4. Accountant
        $accountant = User::firstOrCreate(['email' => 'finance@rehospace.com'], [
            'organization_id' => $org->id,
            'branch_id' => $mainBranch->id,
            'name' => 'Amani Kweka (Finance)',
            'first_name' => 'Amani',
            'last_name' => 'Kweka',
            'phone' => '+255 757 777 888',
            'user_type' => 'Staff',
            'job_title' => 'Head of Real Estate Finance',
            'status' => 'Active',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $accountantRole = Role::where('name', 'accountant')->first();
        if ($accountantRole) {
            $accountant->roles()->syncWithoutDetaching([$accountantRole->id]);
        }
    }
}
