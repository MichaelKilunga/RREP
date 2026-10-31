<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Roles & permissions are enforced on staff routes and navigation, and the
 * Users / RBAC panels manage accounts and roles with lock-out guards.
 */
class RbacEnforcementTest extends TestCase
{
    private function user(string $email): User
    {
        return User::where('email', $email)->firstOrFail();
    }

    private function admin(): User
    {
        return $this->user('admin@rehospace.com');
    }

    public function test_sales_agent_reaches_crm_but_not_finance_users_or_settings(): void
    {
        $agent = $this->user('agent@rehospace.com');

        $this->actingAs($agent)->get(route('crm.requests'))->assertOk();
        $this->actingAs($agent)->get(route('crm.leads'))->assertOk();
        $this->actingAs($agent)->get(route('admin.projects.index'))->assertOk();

        $this->actingAs($agent)->get(route('finance.invoices'))->assertForbidden();
        $this->actingAs($agent)->get(route('users.index'))->assertForbidden();
        $this->actingAs($agent)->get(route('settings.index'))->assertForbidden();
        $this->actingAs($agent)->get(route('settings.rbac'))->assertForbidden();
        // Viewing projects does not allow editing them.
        $this->actingAs($agent)->get(route('admin.projects.create'))->assertForbidden();
    }

    public function test_navigation_only_shows_permitted_sections(): void
    {
        $html = $this->actingAs($this->user('agent@rehospace.com'))->get(route('crm.leads'))->getContent();

        $this->assertStringContainsString(route('crm.requests'), $html);
        $this->assertStringNotContainsString(route('users.index'), $html);
        $this->assertStringNotContainsString(route('finance.invoices'), $html);
        $this->assertStringNotContainsString(route('settings.rbac'), $html);
    }

    public function test_accountant_reaches_finance_and_reports_but_not_leads(): void
    {
        $accountant = $this->user('finance@rehospace.com');

        $this->actingAs($accountant)->get(route('finance.invoices'))->assertOk();
        $this->actingAs($accountant)->get(route('reports.index'))->assertOk();
        $this->actingAs($accountant)->get(route('crm.leads'))->assertForbidden();
    }

    public function test_forbidden_pages_show_a_friendly_explanation(): void
    {
        $this->actingAs($this->user('agent@rehospace.com'))->get(route('users.index'))
            ->assertForbidden()
            ->assertSee('You do not have access to this page');
    }

    public function test_users_without_dashboard_access_land_on_a_permitted_page(): void
    {
        $customer = User::factory()->create(['status' => 'Active', 'user_type' => 'Customer']);
        $customer->roles()->attach(Role::where('name', 'customer')->value('id'));

        $this->actingAs($customer)->get(route('dashboard'))->assertRedirect(route('portals.client'));
        $this->actingAs($customer)->get(route('crm.leads'))->assertForbidden();
    }

    public function test_suspended_accounts_cannot_sign_in_and_are_signed_out_mid_session(): void
    {
        $user = User::factory()->create(['status' => 'Suspended', 'password' => Hash::make('secret-pass')]);

        $this->post(route('login.post'), ['email' => $user->email, 'password' => 'secret-pass'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();

        $agent = $this->user('agent@rehospace.com');
        $agent->update(['status' => 'Suspended']);
        $this->actingAs($agent)->get(route('crm.leads'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_custom_role_grants_exactly_its_permissions(): void
    {
        $this->actingAs($this->admin())->post(route('settings.rbac.roles.store'), [
            'display_name' => 'Front Desk',
            'description' => 'Handles walk-ins',
        ])->assertRedirect(route('settings.rbac'));

        $role = Role::where('display_name', 'Front Desk')->firstOrFail();
        $this->assertSame('front_desk', $role->name);

        $this->actingAs($this->admin())->post(route('settings.rbac.update', $role), [
            'permissions' => Permission::whereIn('name', ['view_dashboard', 'view_requests'])->pluck('id')->all(),
        ])->assertSessionHas('success');

        $staff = User::factory()->create(['status' => 'Active']);
        $staff->roles()->attach($role->id);

        $this->actingAs($staff)->get(route('crm.requests'))->assertOk();
        $this->actingAs($staff)->get(route('crm.leads'))->assertForbidden();
    }

    public function test_roles_with_users_and_built_in_roles_cannot_be_deleted(): void
    {
        $builtIn = Role::where('name', 'sales_agent')->firstOrFail();
        $this->actingAs($this->admin())->delete(route('settings.rbac.roles.destroy', $builtIn))->assertSessionHas('error');
        $this->assertNotNull($builtIn->fresh());

        $custom = Role::create(['name' => 'temp_role', 'display_name' => 'Temp']);
        User::factory()->create()->roles()->attach($custom->id);
        $this->actingAs($this->admin())->delete(route('settings.rbac.roles.destroy', $custom))->assertSessionHas('error');

        $custom->users()->detach();
        $this->actingAs($this->admin())->delete(route('settings.rbac.roles.destroy', $custom))->assertSessionHas('success');
        $this->assertNull(Role::find($custom->id));
    }

    public function test_admins_cannot_lock_themselves_out_of_role_management(): void
    {
        $orgAdminRole = Role::where('name', 'org_admin')->firstOrFail();
        $manager = User::factory()->create(['status' => 'Active']);
        $manager->roles()->attach($orgAdminRole->id);

        $withoutManageRoles = $orgAdminRole->permissions()->where('name', '!=', 'manage_roles')->pluck('permissions.id')->all();

        $this->actingAs($manager)->post(route('settings.rbac.update', $orgAdminRole), ['permissions' => $withoutManageRoles])
            ->assertSessionHasErrors('permissions');
        $this->assertTrue($orgAdminRole->fresh()->permissions->contains('name', 'manage_roles'));
    }

    public function test_only_super_admins_grant_super_admin_and_self_or_last_admin_are_protected(): void
    {
        $orgAdmin = User::factory()->create(['status' => 'Active']);
        $orgAdmin->roles()->attach(Role::where('name', 'org_admin')->value('id'));
        $target = User::factory()->create(['status' => 'Active']);

        $this->actingAs($orgAdmin)->post(route('users.elevate_role', $target), [
            'role_ids' => [Role::where('name', 'super_admin')->value('id')],
        ])->assertSessionHasErrors('role_ids');

        $admin = $this->admin();
        $this->actingAs($admin)->post(route('users.toggle_status', $admin))->assertSessionHas('error');
        $this->actingAs($admin)->delete(route('users.destroy', $admin))->assertSessionHas('error');

        // The seeded admin is the only super admin: an org admin cannot remove them.
        $this->actingAs($orgAdmin)->delete(route('users.destroy', $admin))->assertSessionHasErrors('user');
        $this->assertNotNull($admin->fresh());
    }

    public function test_users_can_be_edited_reset_and_bulk_managed(): void
    {
        $admin = $this->admin();
        $user = User::factory()->create(['status' => 'Active']);

        $this->actingAs($admin)->put(route('users.update', $user), [
            'first_name' => 'Neema', 'last_name' => 'Mushi', 'email' => 'neema.mushi@example.com',
            'phone' => '0712345678', 'job_title' => 'Sales Officer',
        ])->assertSessionHas('success');
        $this->assertSame('Neema Mushi', $user->fresh()->name);
        $this->assertSame('Sales Officer', $user->fresh()->job_title);

        $this->actingAs($admin)->post(route('users.password', $user), [
            'password' => 'NewPass2026', 'password_confirmation' => 'NewPass2026',
        ])->assertSessionHas('success');
        $this->assertTrue(Hash::check('NewPass2026', $user->fresh()->password));

        $other = User::factory()->create(['status' => 'Active']);
        $this->actingAs($admin)->post(route('users.bulk'), ['action' => 'suspend', 'ids' => [$user->id, $other->id]])
            ->assertSessionHas('success');
        $this->assertSame('Suspended', $user->fresh()->status);
        $this->assertSame('Suspended', $other->fresh()->status);

        $this->actingAs($admin)->post(route('users.bulk'), ['action' => 'delete', 'ids' => [$other->id]]);
        $this->assertSoftDeleted($other);
    }
}
