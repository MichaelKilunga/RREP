<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();

        $this->admin = User::first() ?: User::create([
            'name' => 'System Administrator',
            'email' => 'admin@rehospace.com',
            'password' => bcrypt('password'),
            'role' => 'Super Admin',
        ]);
    }

    public function test_admin_can_access_users_index_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('users.index'));

        $response->assertStatus(200);
        $response->assertSee('User Management &amp; Personnel Elevation', false);
        $response->assertSee('Total Users');
        $response->assertSee('Field Surveyors');
    }

    public function test_admin_can_filter_users_by_role_and_status(): void
    {
        $role = Role::first() ?: Role::create([
            'name' => 'surveyor',
            'display_name' => 'GIS Surveyor',
        ]);

        $response = $this->actingAs($this->admin)->get(route('users.index', [
            'role' => $role->name,
            'status' => 'Active',
        ]));

        $response->assertStatus(200);
    }

    public function test_admin_can_create_new_user(): void
    {
        $role = Role::first() ?: Role::create([
            'name' => 'agent',
            'display_name' => 'Sales Agent',
        ]);

        $branch = Branch::first();

        $email = 'newuser_'.uniqid().'@rehospace.com';

        $response = $this->actingAs($this->admin)->post(route('users.store'), [
            'first_name' => 'Daudi',
            'last_name' => 'Kassim',
            'email' => $email,
            'phone' => '+255 788 123 456',
            'password' => 'SecurePass123!',
            'role_id' => $role->id,
            'branch_id' => $branch?->id,
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'email' => $email,
            'first_name' => 'Daudi',
            'last_name' => 'Kassim',
        ]);
    }

    public function test_admin_can_toggle_user_status(): void
    {
        $user = User::factory()->create([
            'status' => 'Active',
        ]);

        $response = $this->actingAs($this->admin)->post(route('users.toggle_status', $user));

        $response->assertSessionHas('info');
        $this->assertEquals('Suspended', $user->fresh()->status);

        // Toggle back
        $this->actingAs($this->admin)->post(route('users.toggle_status', $user));
        $this->assertEquals('Active', $user->fresh()->status);
    }
}
