<?php

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Database\Migrations\Migration;

/**
 * Installs the full RBAC catalog now that permissions are enforced on every
 * staff route. Runs on deploy (migrate --force), so production gets the new
 * permissions and default grants without a separate seeder step.
 */
return new class extends Migration
{
    public function up(): void
    {
        (new RolePermissionSeeder)->run();

        // Staff accounts that never got a role had full access while RBAC was
        // unenforced; keep them working by making them organization admins.
        $orgAdmin = Role::where('name', 'org_admin')->first();
        if ($orgAdmin) {
            User::query()
                ->whereDoesntHave('roles')
                ->where(fn ($q) => $q->whereNull('user_type')->orWhereNotIn('user_type', ['Customer', 'Client', 'Owner', 'PropertyOwner']))
                ->each(fn (User $user) => $user->roles()->syncWithoutDetaching([$orgAdmin->id => ['branch_id' => $user->branch_id]]));
        }
    }

    public function down(): void
    {
        // Data migration: grants are left in place.
    }
};
