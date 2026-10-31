<?php

namespace Tests\Feature;

use App\Models\CompanyService;
use App\Models\PropertyInquiry;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Services admin: active switch actually saves, stable unique slugs,
 * per-service quote requests, ordering, toggle, duplicate.
 */
class ServicesManagementTest extends TestCase
{
    private function admin(): User
    {
        return User::where('email', 'admin@rehospace.com')->firstOrFail();
    }

    private function service(string $title, int $order = 1, bool $active = true): CompanyService
    {
        return CompanyService::create([
            'uuid' => Str::uuid(), 'title' => $title, 'slug' => Str::slug($title), 'category' => 'Planning',
            'short_description' => 'Short text', 'icon' => 'bi-buildings', 'display_order' => $order, 'is_active' => $active,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(CompanyService $service, array $overrides = []): array
    {
        return array_merge([
            'title' => $service->title, 'category' => $service->category, 'short_description' => $service->short_description,
            'icon' => $service->icon, 'display_order' => $service->display_order,
        ], $overrides);
    }

    public function test_edit_form_active_switch_is_saved(): void
    {
        $service = $this->service('Valuation', 1, true);

        // Unticked switch = not sent = hidden.
        $this->actingAs($this->admin())->put(route('admin.services.update', $service), $this->payload($service))->assertRedirect();
        $this->assertFalse($service->fresh()->is_active);

        $this->actingAs($this->admin())->put(route('admin.services.update', $service), $this->payload($service, ['is_active' => '1']));
        $this->assertTrue($service->fresh()->is_active);
    }

    public function test_slugs_stay_unique_and_stable(): void
    {
        $a = $this->service('Town Planning');
        $b = $this->service('Mapping');

        $this->actingAs($this->admin())->put(route('admin.services.update', $b), $this->payload($b, ['title' => 'Town Planning', 'is_active' => '1']));
        $this->assertSame('town-planning-2', $b->fresh()->slug);
        $this->assertSame('town-planning', $a->fresh()->slug);

        // Saving without renaming keeps the slug (shared links keep working).
        $this->actingAs($this->admin())->put(route('admin.services.update', $a), $this->payload($a, ['short_description' => 'Changed', 'is_active' => '1']));
        $this->assertSame('town-planning', $a->fresh()->slug);
    }

    public function test_quote_requests_are_linked_to_their_service_and_shown(): void
    {
        Http::fake();
        $service = $this->service('Land Surveying');

        $this->post(route('public.inquire'), [
            'name' => 'Juma Rashid', 'phone' => '0712000222', 'message' => 'Naomba bei ya upimaji.',
            'service_name' => 'Land Surveying',
        ])->assertSessionHas('success');

        $request = PropertyInquiry::latest('id')->firstOrFail();
        $this->assertSame($service->id, $request->company_service_id);

        $this->actingAs($this->admin())->get(route('admin.services.show', $service))
            ->assertOk()
            ->assertSee('Juma Rashid')
            ->assertSee('Naomba bei ya upimaji.');

        $this->actingAs($this->admin())->get(route('admin.services.index'))
            ->assertOk()
            ->assertSee(route('admin.services.show', $service), false);
    }

    public function test_services_can_be_reordered_toggled_and_duplicated(): void
    {
        CompanyService::query()->delete();
        $first = $this->service('Alpha', 1);
        $second = $this->service('Beta', 2);

        $this->actingAs($this->admin())->post(route('admin.services.move', $second), ['direction' => 'up']);
        $this->assertLessThan($first->fresh()->display_order, $second->fresh()->display_order);

        $this->actingAs($this->admin())->post(route('admin.services.toggle', $first))->assertSessionHas('success');
        $this->assertFalse($first->fresh()->is_active);

        $this->actingAs($this->admin())->post(route('admin.services.duplicate', $second))->assertRedirect();
        $copy = CompanyService::where('title', 'Beta (copy)')->firstOrFail();
        $this->assertFalse($copy->is_active);
        $this->assertNotSame($second->slug, $copy->slug);
    }

    public function test_thumbnail_can_be_removed(): void
    {
        $service = $this->service('Design');
        $service->update(['thumbnail' => '/storage/services/old.jpg']);

        $this->actingAs($this->admin())->put(route('admin.services.update', $service), $this->payload($service, ['remove_thumbnail' => '1', 'is_active' => '1']));
        $this->assertNull($service->fresh()->thumbnail);
    }
}
