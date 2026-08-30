<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            OrganizationSeeder::class,
            RolePermissionSeeder::class,
            UserSeeder::class,
            LicensedModuleSeeder::class,
            PropertySeeder::class,
            CrmFinanceSeeder::class,
        ]);
    }
}
