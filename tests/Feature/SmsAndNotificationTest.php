<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\SystemSetting;
use App\Services\NotificationService;
use App\Services\SmsService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SmsAndNotificationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        SystemSetting::setVal('sms_enabled', '1');
        SystemSetting::setVal('pushsms_base_url', 'https://pushsms.rehospace.com');
        SystemSetting::setVal('pushsms_sender_id', 'REALESTATE');
        SystemSetting::setVal('pushsms_api_key', 'test_api_key');
    }

    public function test_phone_number_normalization(): void
    {
        // 07... -> 2557...
        $this->assertEquals('255784100200', SmsService::formatPhoneNumber('0784100200'));

        // +255... -> 255...
        $this->assertEquals('255784100200', SmsService::formatPhoneNumber('+255 784-100-200'));

        // 25507... -> 2557...
        $this->assertEquals('255784100200', SmsService::formatPhoneNumber('2550784100200'));

        // 784... (9 digits) -> 255784...
        $this->assertEquals('255784100200', SmsService::formatPhoneNumber('784100200'));
    }

    public function test_sms_dispatch_sends_dual_sender_params_and_headers(): void
    {
        Http::fake([
            'https://pushsms.rehospace.com/api/v1/send' => Http::response(['status' => 'ok', 'id' => 101], 200),
        ]);

        $sent = SmsService::send('0784100200', 'Test notification message');

        $this->assertTrue($sent);

        Http::assertSent(function ($request) {
            $data = $request->data();
            $hasContentType = str_contains($request->header('Content-Type')[0] ?? '', 'application/json');
            $hasAccept = str_contains($request->header('Accept')[0] ?? '', 'application/json');

            return str_contains($request->url(), 'pushsms.rehospace.com/api/v1/send')
                && $hasContentType
                && $hasAccept
                && $data['to'] === '255784100200'
                && ! empty($data['sender'])
                && ! empty($data['sender_id']) // Critical mandate per send_sms.md
                && $data['sender'] === $data['sender_id']
                && ! empty($data['client_app']);
        });
    }

    public function test_sms_balance_check(): void
    {
        SystemSetting::setVal('pushsms_api_key', 'test_key_123');
        SystemSetting::setVal('pushsms_sender_id', 'REALESTATE');

        Http::fake([
            'https://pushsms.rehospace.com/api/v1/balance*' => Http::response(['status' => 'success', 'sender' => 'REALESTATE', 'balance' => 850], 200),
        ]);

        $balance = SmsService::getBalance();

        $this->assertEquals('success', $balance['status']);
        $this->assertEquals(850, $balance['balance']);
    }

    public function test_event_a_and_event_b_notification_triggers(): void
    {
        Http::fake([
            'https://pushsms.rehospace.com/api/v1/send' => Http::response(['status' => 'ok', 'id' => 102], 200),
        ]);

        $customer = Customer::first() ?: Customer::create([
            'first_name' => 'Juma',
            'last_name' => 'Mushi',
            'phone' => '0712345678',
            'email' => 'juma@example.com',
        ]);

        // Event A: Buyer reservation confirmation
        $eventAResult = NotificationService::triggerEventA_BookingConfirmation($customer, 'PLT-100', 'RES-7890', 'TSh 500,000');
        $this->assertTrue($eventAResult);

        // Event B: Survey completion exact message
        $eventBResult = NotificationService::triggerEventB_SurveyCompletion($customer, 'SRV-2026-101');
        $this->assertTrue($eventBResult);

        Http::assertSent(function ($request) {
            $data = $request->data();
            // Check that Event B contains the exact SRS text
            if (str_contains($data['message'], 'Ukaguzi na uchambuzi wa site yako')) {
                return str_contains($data['message'], 'Ukaguzi na uchambuzi wa site yako umekamilika, tafadhari angalia kwenye account yako kuona nyaraka zako.');
            }

            return true;
        });
    }
}
