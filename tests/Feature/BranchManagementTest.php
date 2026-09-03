<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Organization;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class BranchManagementTest extends TestCase
{
    protected User $admin;

    protected Organization $org;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();

        $this->org = Organization::first() ?: Organization::create([
            'name' => 'RehoSpace Real Estate Ltd',
            'code' => 'REHO',
            'status' => 'Active',
        ]);

        $this->admin = User::first() ?: User::create([
            'name' => 'System Admin',
            'email' => 'admin@rehospace.com',
            'password' => bcrypt('password'),
            'role' => 'Super Admin',
            'organization_id' => $this->org->id,
        ]);
    }

    public function test_admin_can_view_branches_list_in_settings(): void
    {
        $response = $this->actingAs($this->admin)->get(route('settings.index'));

        $response->assertStatus(200);
        $response->assertSee('Organization Branches');
        $response->assertSee('Add Branch');
    }

    public function test_admin_can_create_new_branch(): void
    {
        $uniqueCode = 'BR-TST-'.strtoupper(substr(uniqid(), -4));

        $response = $this->actingAs($this->admin)->post(route('settings.branches.store'), [
            'name' => 'Dodoma Capital Branch',
            'code' => $uniqueCode,
            'city' => 'Dodoma',
            'address' => 'Kikuyu North Avenue',
            'phone' => '+255 711 223 344',
            'email' => 'dodoma@rehospace.com',
            'status' => 'Active',
            'is_main' => '0',
        ]);

        $response->assertRedirect(route('settings.index').'#branches');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('branches', [
            'name' => 'Dodoma Capital Branch',
            'code' => $uniqueCode,
            'city' => 'Dodoma',
            'status' => 'Active',
            'is_main' => false,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'branch_created',
        ]);
    }

    public function test_setting_branch_as_hq_unmarks_previous_hq(): void
    {
        // 1. Ensure an initial HQ branch exists
        $initialHq = Branch::create([
            'organization_id' => $this->org->id,
            'name' => 'Initial HQ Branch',
            'code' => 'BR-HQ1-'.substr(uniqid(), -4),
            'city' => 'Dar es Salaam',
            'status' => 'Active',
            'is_main' => true,
        ]);

        $this->assertTrue((bool) $initialHq->fresh()->is_main);

        // 2. Create a new branch designated as HQ
        $newHqCode = 'BR-HQ2-'.substr(uniqid(), -4);
        $response = $this->actingAs($this->admin)->post(route('settings.branches.store'), [
            'name' => 'New Regional HQ',
            'code' => $newHqCode,
            'city' => 'Arusha',
            'status' => 'Active',
            'is_main' => '1',
        ]);

        $response->assertSessionHas('success');

        $this->assertFalse((bool) $initialHq->fresh()->is_main);
        $this->assertTrue((bool) Branch::where('code', strtoupper($newHqCode))->first()->is_main);
    }

    public function test_admin_can_update_existing_branch(): void
    {
        $branch = Branch::create([
            'organization_id' => $this->org->id,
            'name' => 'Zanzibar Desk',
            'code' => 'BR-ZNZ-'.substr(uniqid(), -4),
            'city' => 'Stone Town',
            'status' => 'Active',
            'is_main' => false,
        ]);

        $response = $this->actingAs($this->admin)->put(route('settings.branches.update', $branch), [
            'name' => 'Zanzibar Island Coastal Branch',
            'code' => $branch->code,
            'city' => 'Stone Town & Nungwi',
            'address' => 'Beachfront Suites, Suite 4',
            'phone' => '+255 777 999 888',
            'email' => 'zanzibar@rehospace.com',
            'status' => 'Active',
            'is_main' => '0',
        ]);

        $response->assertRedirect(route('settings.index').'#branches');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('branches', [
            'id' => $branch->id,
            'name' => 'Zanzibar Island Coastal Branch',
            'city' => 'Stone Town & Nungwi',
            'phone' => '+255 777 999 888',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'branch_updated',
            'auditable_id' => $branch->id,
        ]);
    }

    public function test_cannot_delete_hq_branch(): void
    {
        $hqBranch = Branch::create([
            'organization_id' => $this->org->id,
            'name' => 'Permanent HQ Branch',
            'code' => 'BR-PERMHQ-'.substr(uniqid(), -4),
            'status' => 'Active',
            'is_main' => true,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('settings.branches.destroy', $hqBranch));

        $response->assertRedirect(route('settings.index').'#branches');
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('branches', ['id' => $hqBranch->id, 'deleted_at' => null]);
    }

    public function test_cannot_delete_branch_with_assigned_properties(): void
    {
        $branch = Branch::create([
            'organization_id' => $this->org->id,
            'name' => 'Active Portfolio Branch',
            'code' => 'BR-PORT-'.substr(uniqid(), -4),
            'status' => 'Active',
            'is_main' => false,
        ]);

        $type = PropertyType::first() ?: PropertyType::create([
            'name' => 'Apartment',
            'code' => 'APT',
            'slug' => 'apartment',
        ]);

        Property::create([
            'organization_id' => $this->org->id,
            'property_type_id' => $type->id,
            'branch_id' => $branch->id,
            'title' => 'Assigned Property Test',
            'slug' => 'assigned-property-test-'.uniqid(),
            'property_code' => 'PROP-TST-'.uniqid(),
            'price' => 100000000,
            'city' => 'Dar es Salaam',
            'address' => 'Test Street',
            'is_published' => true,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('settings.branches.destroy', $branch));

        $response->assertRedirect(route('settings.index').'#branches');
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('branches', ['id' => $branch->id, 'deleted_at' => null]);
    }

    public function test_admin_can_delete_unassigned_non_hq_branch(): void
    {
        $branch = Branch::create([
            'organization_id' => $this->org->id,
            'name' => 'Temporary Pop-up Branch',
            'code' => 'BR-TEMP-'.substr(uniqid(), -4),
            'status' => 'Inactive',
            'is_main' => false,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('settings.branches.destroy', $branch));

        $response->assertRedirect(route('settings.index').'#branches');
        $response->assertSessionHas('success');

        $this->assertSoftDeleted('branches', [
            'id' => $branch->id,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'branch_deleted',
            'auditable_id' => $branch->id,
        ]);
    }
}
