<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\Reservation;
use App\Models\SurveyProject;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class ReservationAndSurveyWorkflowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        SystemSetting::setVal('feature_online_reservations_enabled', '1');
        SystemSetting::setVal('feature_online_bookings_enabled', '1');
        SystemSetting::setVal('feature_property_owner_submissions_enabled', '1');
        SystemSetting::setVal('sms_enabled', '1');

        Http::fake([
            'https://pushsms.rehospace.com/api/v1/send' => Http::response(['status' => 'ok', 'id' => 104], 200),
        ]);
    }

    public function test_plot_reservation_auto_invoicing_and_event_a_sms(): void
    {
        $property = Property::where('is_published', true)->firstOrFail();

        $response = $this->from(route('public.properties.show', $property))->post(route('public.reservation.reserve'), [
            'property_id' => $property->id,
            'name' => 'Salim Mwinyi',
            'phone' => '0712345678',
            'email' => 'salim@example.com',
            'deposit_amount' => 500000,
            'notes' => 'Ready for contract signing after site visit',
        ]);

        $response->assertSessionHas('success');

        // Verify customer created and loyalty points awarded
        $customer = Customer::where('phone', '0712345678')->firstOrFail();
        $this->assertGreaterThanOrEqual(100, $customer->loyalty_points);

        // Verify reservation created
        $reservation = Reservation::where('customer_id', $customer->id)
            ->where('property_id', $property->id)
            ->firstOrFail();
        $this->assertEquals('Active', $reservation->status);
        $this->assertEquals(500000, $reservation->reservation_fee);

        // Verify digital invoice generated
        $invoice = Invoice::where('customer_id', $customer->id)
            ->where('property_id', $property->id)
            ->firstOrFail();
        $this->assertEquals(500000, $invoice->total_amount);
        $this->assertEquals('Issued', $invoice->status);
        $this->assertCount(1, $invoice->items);

        // Verify SMS sent
        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'pushsms.rehospace.com/api/v1/send');
        });
    }

    public function test_survey_request_booking_with_gps_coordinates(): void
    {
        $response = $this->from(route('public.services.land_survey'))->post(route('public.survey.request'), [
            'name' => 'Zawadi Bakari',
            'phone' => '0755998877',
            'email' => 'zawadi@example.com',
            'location' => 'Gezaulole, Kigamboni',
            'survey_type' => 'Cadastral Title Survey',
            'approx_land_size' => '2.5',
            'preferred_date' => now()->addDays(3)->toDateString(),
            'latitude' => -6.823500,
            'longitude' => 39.269500,
            'description' => 'Need full cadastral survey for title deed application',
        ]);

        $response->assertSessionHas('success');

        // Verify customer created
        $customer = Customer::where('phone', '0755998877')->firstOrFail();

        // Verify survey project created with GPS coordinates
        $survey = SurveyProject::where('customer_id', $customer->id)->firstOrFail();
        $this->assertEquals('Cadastral Title Survey', $survey->survey_type);
        $this->assertEquals(-6.823500, $survey->latitude);
        $this->assertEquals(39.269500, $survey->longitude);

        // Verify mobilization invoice generated
        $invoice = Invoice::where('customer_id', $customer->id)->firstOrFail();
        $this->assertEquals(150000, $invoice->total_amount);
    }

    public function test_survey_completion_triggers_event_b_sms(): void
    {
        $customer = Customer::create([
            'first_name' => 'Fatma',
            'last_name' => 'Said',
            'phone' => '0777'.rand(100000, 999999),
            'email' => 'fatma.'.uniqid().'@example.com',
        ]);

        $survey = SurveyProject::create([
            'project_code' => 'SRV-TEST-'.strtoupper(Str::random(6)),
            'project_name' => 'Boundary Relocation',
            'customer_id' => $customer->id,
            'location_name' => 'Kihonda, Morogoro',
            'status' => 'Fieldwork',
        ]);

        $admin = User::first() ?: User::create([
            'name' => 'Staff Surveyor',
            'email' => 'surveyor@avenix.co.tz',
            'password' => bcrypt('password'),
            'role' => 'Super Admin',
        ]);

        $response = $this->actingAs($admin)->from(route('survey.show', $survey))->post(route('survey.status', $survey), [
            'status' => 'Completed',
        ]);

        $response->assertSessionHas('success');

        $survey->refresh();
        $this->assertEquals('Completed', $survey->status);

        // Event B SMS verified
        Http::assertSent(function ($request) {
            $data = $request->data();

            return str_contains($data['message'], 'Ukaguzi na uchambuzi wa site yako umekamilika');
        });
    }

    public function test_owner_portal_plot_submission_enters_under_review(): void
    {
        $user = User::first() ?: User::create([
            'name' => 'Owner Salim',
            'email' => 'salim.owner@example.com',
            'password' => bcrypt('password'),
            'role' => 'Customer',
        ]);

        $propertyType = PropertyType::firstOrFail();

        $uniqueTitle = '1.5 Acres Beachfront Land '.Str::random(5);

        $response = $this->actingAs($user)->from(route('portals.owner'))->post(route('portals.owner.submit_property'), [
            'title' => $uniqueTitle,
            'property_type_id' => $propertyType->id,
            'listing_type' => 'Sale',
            'price' => 85000000,
            'area_size' => 1.5,
            'area_unit' => 'Acres',
            'city' => 'Dar es Salaam',
            'address' => 'Cheka Coastal Zone',
            'zoning' => 'Residential',
            'latitude' => -6.84000,
            'longitude' => 39.29000,
            'description' => 'Prime investment plot with electricity and fresh water connection.',
        ]);

        $response->assertSessionHas('success');

        $property = Property::where('title', $uniqueTitle)->firstOrFail();
        $this->assertEquals('Under Review', $property->submission_status);
        $this->assertEquals('Under Review', $property->status);
        $this->assertFalse((bool) $property->is_published);
    }
}
