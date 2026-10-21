<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\LandParcel;
use App\Models\RealEstateProject;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Staff reservations can hold plots from land-selling projects, and the
 * taxonomy settings endpoints answer JSON for the no-reload settings UI.
 */
class ReservationPlotsTest extends TestCase
{
    private function admin(): User
    {
        return User::where('email', 'admin@rehospace.com')->firstOrFail();
    }

    private function availablePlot(): LandParcel
    {
        $project = RealEstateProject::where('project_category', 'land')->firstOrFail();

        return LandParcel::create([
            'uuid' => Str::uuid(),
            'real_estate_project_id' => $project->id,
            'parcel_number' => '77',
            'block_number' => 'B',
            'status' => 'Available',
            'acreage' => 0.15,
            'area_sqm' => 600,
            'price' => 9000000,
        ]);
    }

    public function test_staff_can_reserve_a_project_plot_and_cancelling_releases_it(): void
    {
        Http::fake();
        $plot = $this->availablePlot();
        $customer = Customer::firstOrFail();

        // The modal lists the plot under its project.
        $this->actingAs($this->admin())->get(route('reservations.index'))
            ->assertSee('parcel-'.$plot->id, false)
            ->assertSee($plot->project->title);

        $this->actingAs($this->admin())->post(route('reservations.store'), [
            'reservable' => 'parcel-'.$plot->id,
            'customer_id' => $customer->id,
            'reservation_fee' => 2000000,
            'deposit_paid' => 2000000,
            'reserved_from' => now()->toDateString(),
            'reserved_until' => now()->addDays(14)->toDateString(),
        ])->assertRedirect()->assertSessionHas('success');

        $reservation = Reservation::where('land_parcel_id', $plot->id)->firstOrFail();
        $this->assertSame('Reserved', $plot->fresh()->status);
        $this->assertSame(
            LandParcel::where('real_estate_project_id', $plot->real_estate_project_id)->where('status', 'Available')->count(),
            (int) $plot->project->fresh()->available_units
        );

        // The list shows the plot, not an empty property column.
        $this->actingAs($this->admin())->get(route('reservations.index'))
            ->assertSee('77 (B)')
            ->assertSee($reservation->reservation_number);

        $this->actingAs($this->admin())->post(route('reservations.cancel', $reservation))->assertRedirect();
        $this->assertSame('Available', $plot->fresh()->status);
        $this->assertSame('Cancelled', $reservation->fresh()->status);
    }

    public function test_reserving_an_already_reserved_plot_is_rejected(): void
    {
        $plot = $this->availablePlot();
        $plot->update(['status' => 'Reserved']);

        $this->actingAs($this->admin())->post(route('reservations.store'), [
            'reservable' => 'parcel-'.$plot->id,
            'customer_id' => Customer::firstOrFail()->id,
            'reservation_fee' => 1000000,
            'deposit_paid' => 1000000,
            'reserved_from' => now()->toDateString(),
            'reserved_until' => now()->addDays(14)->toDateString(),
        ])->assertSessionHasErrors('reservable');
    }

    public function test_taxonomy_endpoints_answer_json_for_the_no_reload_settings_ui(): void
    {
        $response = $this->actingAs($this->admin())->postJson(route('settings.taxonomies.store'), [
            'group' => 'document_type',
            'name' => 'Power of Attorney',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['message', 'name', 'update_url', 'destroy_url']);

        $destroyUrl = $response->json('destroy_url');
        $this->actingAs($this->admin())->deleteJson($destroyUrl)->assertOk()->assertJsonStructure(['message']);
    }
}
