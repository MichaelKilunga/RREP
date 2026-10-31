<?php

namespace Tests\Feature;

use App\Models\CommunicationLog;
use App\Models\Customer;
use App\Models\LandParcel;
use App\Models\Lead;
use App\Models\PropertyInquiry;
use App\Models\RealEstateProject;
use App\Models\Reservation;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * CRM: customer profiles & management, request handling and conversion,
 * lead follow-ups and history, reservation extend/convert/expire.
 */
class CrmModuleTest extends TestCase
{
    private function admin(): User
    {
        return User::where('email', 'admin@rehospace.com')->firstOrFail();
    }

    private function customer(array $overrides = []): Customer
    {
        return Customer::create(array_merge([
            'first_name' => 'Asha', 'last_name' => 'Mwinyi', 'customer_type' => 'Individual',
            'phone' => '0715111222', 'email' => 'asha@example.com', 'city' => 'Dodoma',
        ], $overrides));
    }

    private function plot(string $status = 'Reserved'): LandParcel
    {
        return LandParcel::create([
            'uuid' => Str::uuid(), 'real_estate_project_id' => RealEstateProject::where('project_category', 'land')->value('id'),
            'parcel_number' => (string) random_int(1000, 9999), 'block_number' => 'Q', 'status' => $status, 'acreage' => 0.1, 'area_sqm' => 400, 'price' => 4000000,
        ]);
    }

    private function hold(Customer $customer, LandParcel $plot, string $until): Reservation
    {
        return Reservation::create([
            'land_parcel_id' => $plot->id, 'customer_id' => $customer->id, 'reservation_number' => 'RESV-'.Str::random(6),
            'reservation_fee' => 500000, 'deposit_paid' => 500000, 'reserved_from' => now()->subDays(10), 'reserved_until' => $until, 'status' => 'Active',
        ]);
    }

    public function test_customer_profile_shows_a_unified_timeline(): void
    {
        $customer = $this->customer();
        PropertyInquiry::create(['customer_id' => $customer->id, 'inquiry_type' => 'Service Quote', 'name' => 'Asha Mwinyi', 'phone' => '0715111222', 'message' => 'Naomba bei ya ramani', 'status' => 'New']);
        CommunicationLog::create(['customer_id' => $customer->id, 'recipient' => '255715111222', 'channel' => 'sms', 'body' => 'Karibu Avenix', 'status' => 'Sent']);

        $this->actingAs($this->admin())->get(route('crm.customers.show', $customer))
            ->assertOk()
            ->assertSee('Asha Mwinyi')
            ->assertSee('Naomba bei ya ramani')
            ->assertSee('Karibu Avenix');

        $this->actingAs($this->admin())->get(route('crm.customers', ['q' => 'Mwinyi']))
            ->assertOk()
            ->assertSee(route('crm.customers.show', $customer), false);
    }

    public function test_customers_can_be_edited_messaged_exported_and_deleted_safely(): void
    {
        Http::fake(['*' => Http::response(['status' => 'ok'], 200)]);
        SystemSetting::setVal('pushsms_api_key', 'real_key_for_test');
        $customer = $this->customer();

        $this->actingAs($this->admin())->put(route('crm.customers.update', $customer), [
            'first_name' => 'Asha', 'last_name' => 'Mwinyi-Juma', 'customer_type' => 'Individual', 'phone' => '0715111222', 'notes' => 'Prefers WhatsApp',
        ])->assertSessionHas('success');
        $this->assertSame('Mwinyi-Juma', $customer->fresh()->last_name);

        $this->actingAs($this->admin())->post(route('crm.customers.sms', $customer), ['message' => 'Habari Asha'])->assertSessionHas('success');
        $this->assertDatabaseHas('communication_logs', ['customer_id' => $customer->id, 'body' => 'Habari Asha', 'status' => 'Sent']);

        $csv = $this->actingAs($this->admin())->get(route('crm.customers.export', ['q' => 'Mwinyi']));
        $csv->assertOk();
        $this->assertStringContainsString('Mwinyi-Juma', $csv->streamedContent());

        // An active hold blocks deletion; once released the customer can be removed.
        $hold = $this->hold($customer, $this->plot(), now()->addDays(5)->toDateString());
        $this->actingAs($this->admin())->delete(route('crm.customers.destroy', $customer))->assertSessionHas('error');
        $hold->update(['status' => 'Cancelled']);
        $this->actingAs($this->admin())->delete(route('crm.customers.destroy', $customer))->assertRedirect(route('crm.customers'));
        $this->assertSoftDeleted($customer);
    }

    public function test_agents_cannot_delete_customers(): void
    {
        $agent = User::where('email', 'agent@rehospace.com')->firstOrFail();
        $this->actingAs($agent)->delete(route('crm.customers.destroy', $this->customer()))->assertForbidden();
    }

    public function test_requests_are_handled_converted_to_leads_and_bulk_managed(): void
    {
        $request = PropertyInquiry::create(['inquiry_type' => 'Plot Reservation Request', 'name' => 'Baraka Said', 'phone' => '0754333444', 'message' => '[MINARA - Plot 4] Nataka kiwanja', 'source' => 'Project Plot Map', 'status' => 'New']);
        $admin = $this->admin();

        $this->actingAs($admin)->put(route('crm.requests.update', $request), [
            'status' => 'Contacted', 'assigned_to' => $admin->id, 'staff_notes' => 'Called, visiting Saturday',
        ])->assertSessionHas('success');
        $request->refresh();
        $this->assertSame('Contacted', $request->status);
        $this->assertSame($admin->id, $request->assigned_to);
        $this->assertNotNull($request->handled_at);

        $this->actingAs($admin)->get(route('crm.requests', ['mine' => 1]))->assertSee('Called, visiting Saturday');

        $this->actingAs($admin)->post(route('crm.requests.convert', $request))->assertRedirect();
        $request->refresh();
        $lead = Lead::findOrFail($request->lead_id);
        $this->assertSame('High', $lead->priority);
        $this->assertSame('0754333444', $lead->customer->phone);
        $this->assertNotNull($lead->next_followup_at);

        $other = PropertyInquiry::create(['inquiry_type' => 'Property Inquiry', 'name' => 'X', 'phone' => '0700000000', 'message' => 'Hi', 'status' => 'New']);
        $this->actingAs($admin)->post(route('crm.requests.bulk'), ['action' => 'status', 'status' => 'Closed', 'ids' => [$other->id]]);
        $this->assertSame('Closed', $other->fresh()->status);
        $this->actingAs($admin)->delete(route('crm.requests.destroy', $other));
        $this->assertNull(PropertyInquiry::find($other->id));
    }

    public function test_activities_set_follow_ups_and_show_in_history(): void
    {
        $lead = Lead::firstOrFail();
        $when = now()->subDay()->format('Y-m-d H:i:s');

        $this->actingAs($this->admin())->post(route('crm.leads.log_activity', $lead), [
            'activity_type' => 'Call', 'summary' => 'Client asked for site visit', 'details' => 'Saturday morning', 'next_followup_at' => $when,
        ])->assertSessionHas('success');

        $this->assertNotNull($lead->fresh()->next_followup_at);
        $page = $this->actingAs($this->admin())->get(route('crm.leads', ['due' => 1, 'stage' => $lead->stage]));
        $page->assertOk()->assertSee('Client asked for site visit');
    }

    public function test_reservations_extend_convert_and_expire(): void
    {
        $customer = $this->customer();

        $plotA = $this->plot();
        $holdA = $this->hold($customer, $plotA, now()->addDays(2)->toDateString());
        $this->actingAs($this->admin())->post(route('reservations.extend', $holdA), [
            'reserved_until' => now()->addDays(20)->toDateString(), 'deposit_paid' => 900000,
        ])->assertSessionHas('success');
        $this->assertSame(now()->addDays(20)->toDateString(), $holdA->fresh()->reserved_until->toDateString());
        $this->assertSame(900000.0, (float) $holdA->fresh()->deposit_paid);

        $this->actingAs($this->admin())->post(route('reservations.convert', $holdA))->assertSessionHas('success');
        $this->assertSame('Converted', $holdA->fresh()->status);
        $this->assertSame('Sold', $plotA->fresh()->status);

        $plotB = $this->plot();
        $holdB = $this->hold($customer, $plotB, now()->subDays(3)->toDateString());
        $this->actingAs($this->admin())->get(route('reservations.index', ['status' => 'overdue']))->assertSee($holdB->reservation_number);

        $this->artisan('reservations:expire')->assertSuccessful();
        $this->assertSame('Expired', $holdB->fresh()->status);
        $this->assertSame('Available', $plotB->fresh()->status);
    }
}
