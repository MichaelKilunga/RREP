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
        $admin = User::create([
            'organization_id' => $org->id,
            'branch_id' => $mainBranch->id,
            'name' => 'Michael Kilunga (Admin)',
            'first_name' => 'Michael',
            'last_name' => 'Kilunga',
            'email' => 'admin@rehospace.com',
            'phone' => '+255 754 111 222',
            'user_type' => 'Staff',
            'job_title' => 'Chief Executive & Principal Broker',
            'status' => 'Active',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $admin->roles()->attach(Role::where('name', 'super_admin')->first()->id);

        // 2. Sales Agent
        $agentUser = User::create([
            'organization_id' => $org->id,
            'branch_id' => $mainBranch->id,
            'name' => 'Baraka John (Senior Agent)',
            'first_name' => 'Baraka',
            'last_name' => 'John',
            'email' => 'agent@rehospace.com',
            'phone' => '+255 755 333 444',
            'user_type' => 'Agent',
            'job_title' => 'Senior Real Estate Consultant',
            'status' => 'Active',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $agentUser->roles()->attach(Role::where('name', 'sales_agent')->first()->id);

        Agent::create([
            'user_id' => $agentUser->id,
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
        $surveyorUser = User::create([
            'organization_id' => $org->id,
            'branch_id' => $mainBranch->id,
            'name' => 'Eng. Grace Mwamburi (Surveyor)',
            'first_name' => 'Grace',
            'last_name' => 'Mwamburi',
            'email' => 'surveyor@rehospace.com',
            'phone' => '+255 756 555 666',
            'user_type' => 'Staff',
            'job_title' => 'Chief Geospatial Surveyor',
            'status' => 'Active',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $surveyorUser->roles()->attach(Role::where('name', 'surveyor')->first()->id);

        // 4. Accountant
        $accountant = User::create([
            'organization_id' => $org->id,
            'branch_id' => $mainBranch->id,
            'name' => 'Amani Kweka (Finance)',
            'first_name' => 'Amani',
            'last_name' => 'Kweka',
            'email' => 'finance@rehospace.com',
            'phone' => '+255 757 777 888',
            'user_type' => 'Staff',
            'job_title' => 'Head of Real Estate Finance',
            'status' => 'Active',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $accountant->roles()->attach(Role::where('name', 'accountant')->first()->id);
    }
}
