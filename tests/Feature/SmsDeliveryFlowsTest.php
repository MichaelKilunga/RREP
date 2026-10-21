<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SmsDeliveryFlowsTest extends TestCase
{
    public function test_quote_sends_sms_to_both_company_and_customer(): void
    {
        Http::fake(['*' => Http::response(['status' => 'ok'], 200)]);
        SystemSetting::setVal('sms_enabled', '1');
        SystemSetting::setVal('pushsms_api_key', 'real_key_for_test');

        $this->post(route('public.inquire'), [
            'name' => 'Neema Mushi',
            'phone' => '0712999888',
            'email' => 'neema@example.com',
            'message' => 'Naomba bei',
            'service_name' => 'Land Surveying',
        ])->assertSessionHas('success');

        $smsTargets = [];
        Http::assertSent(function ($request) use (&$smsTargets) {
            if (str_contains($request->url(), '/api/v1/send')) {
                $smsTargets[] = $request->data()['to'] ?? '?';
            }

            return true;
        });
        $this->assertContains('255712999888', $smsTargets, 'Customer SMS was never initiated');
        $this->assertContains('255784100200', $smsTargets, 'Company SMS was never initiated');
    }

    public function test_reservation_sends_sms_to_both_company_and_customer(): void
    {
        Http::fake(['*' => Http::response(['status' => 'ok'], 200)]);
        SystemSetting::setVal('sms_enabled', '1');
        SystemSetting::setVal('feature_online_reservations_enabled', '1');

        $property = Property::where('is_published', true)->where('status', 'Available')->firstOrFail();

        $this->post(route('public.reservation.reserve'), [
            'property_id' => $property->id,
            'name' => 'Juma Kondo',
            'phone' => '0713555444',
            'email' => 'juma@example.com',
            'deposit_amount' => 500000,
        ])->assertSessionHas('success');

        $smsTargets = [];
        Http::assertSent(function ($request) use (&$smsTargets) {
            if (str_contains($request->url(), '/api/v1/send')) {
                $smsTargets[] = $request->data()['to'] ?? '?';
            }

            return true;
        });
        $this->assertContains('255713555444', $smsTargets, 'Customer reservation SMS was never initiated');
        $this->assertContains('255784100200', $smsTargets, 'Company reservation SMS was never initiated');
    }

    public function test_blank_saved_sms_template_falls_back_to_the_default_text(): void
    {
        Http::fake(['*' => Http::response(['status' => 'ok'], 200)]);
        SystemSetting::setVal('sms_enabled', '1');
        SystemSetting::setVal('feature_online_reservations_enabled', '1');
        SystemSetting::setVal('sms_template_event_a', ''); // saved empty in admin

        $property = Property::where('is_published', true)->where('status', 'Available')->firstOrFail();

        $this->post(route('public.reservation.reserve'), [
            'property_id' => $property->id,
            'name' => 'Asha Hamisi',
            'phone' => '0715666777',
            'email' => 'asha@example.com',
            'deposit_amount' => 300000,
        ])->assertSessionHas('success');

        $customerSms = null;
        Http::assertSent(function ($request) use (&$customerSms) {
            if (str_contains($request->url(), '/api/v1/send') && ($request->data()['to'] ?? null) === '255715666777') {
                $customerSms = $request->data()['message'] ?? '';
            }

            return true;
        });

        $this->assertNotNull($customerSms, 'Customer SMS was never initiated despite the blank template');
        $this->assertNotSame('', trim($customerSms));
    }
}
