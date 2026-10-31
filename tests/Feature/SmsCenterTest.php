<?php

namespace Tests\Feature;

use App\Models\CommunicationLog;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\SmsService;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Push SMS reliability: every attempt is logged with a reason, both switches are
 * honoured, gateway errors are detected, messages are cleaned for template
 * matching, and the SMS center can send, filter and resend.
 */
class SmsCenterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        SystemSetting::setVal('sms_enabled', '1');
        SystemSetting::setVal('feature_sms_notifications_enabled', '1');
    }

    private function admin(): User
    {
        return User::where('email', 'admin@rehospace.com')->firstOrFail();
    }

    public function test_skipped_messages_are_logged_with_the_reason_and_never_reach_the_gateway(): void
    {
        Http::fake();

        SystemSetting::setVal('sms_enabled', '0');
        $this->assertFalse(SmsService::send('0712345678', 'Habari', 'invoice_issued'));
        $this->assertDatabaseHas('communication_logs', ['recipient' => '255712345678', 'status' => 'Skipped', 'error_message' => 'SMS is switched off in Settings (Push SMS)']);

        SystemSetting::setVal('sms_enabled', '1');
        $this->assertFalse(SmsService::send('07123', 'Habari', 'invoice_issued'));
        $this->assertDatabaseHas('communication_logs', ['status' => 'Skipped', 'error_message' => 'Invalid phone number: 07123']);

        config(['services.pushsms.api_key' => '']);
        $this->assertFalse(SmsService::send('0712345678', 'Habari', 'invoice_issued'));
        $this->assertDatabaseHas('communication_logs', ['status' => 'Skipped', 'error_message' => 'PushSMS API key is not set']);

        Http::assertNothingSent();
    }

    public function test_automatic_toggle_blocks_notifications_but_not_messages_sent_by_staff(): void
    {
        Http::fake(['*' => Http::response(['status' => 'ok', 'id' => 7], 200)]);
        SystemSetting::setVal('feature_sms_notifications_enabled', '0');

        $this->assertFalse(SmsService::send('0712345678', 'Ankara mpya', 'invoice_issued'));
        $this->assertDatabaseHas('communication_logs', ['template_code' => 'invoice_issued', 'status' => 'Skipped']);

        $this->assertTrue(SmsService::send('0712345678', 'Habari, tunakukumbusha', 'invoice_reminder', null, manual: true));
        $this->assertDatabaseHas('communication_logs', ['template_code' => 'invoice_reminder', 'status' => 'Sent']);
        Http::assertSentCount(1);
    }

    public function test_gateway_errors_are_detected_even_with_http_200(): void
    {
        Http::fakeSequence('*')
            ->push(['status' => 'error', 'message' => 'Insufficient balance'], 200)
            ->push(['message' => 'Unauthorized'], 401);

        $first = SmsService::deliver('0712345678', 'Habari', 'test_sms', manual: true);
        $this->assertSame('Failed', $first['status']);
        $this->assertStringContainsString('Insufficient balance', $first['reason']);

        $second = SmsService::deliver('0712345678', 'Habari', 'test_sms', manual: true);
        $this->assertSame('API key rejected by the gateway (HTTP 401)', $second['reason']);
    }

    public function test_messages_are_cleaned_for_template_matching_and_unicode_is_flagged(): void
    {
        Http::fake(['*' => Http::response(['status' => 'ok'], 200)]);

        SmsService::send('0712345678, 0754 000 111, 123', "Habari \u{201C}Asha\u{201D} \u{2013} malipo\nyamepokelewa", 'test_sms', manual: true);
        SmsService::send('0712345678', 'Hongera 🎉', 'test_sms', manual: true);

        $payloads = [];
        Http::assertSent(function (HttpRequest $request) use (&$payloads) {
            $payloads[] = $request->data();

            return true;
        });

        $this->assertSame('255712345678,255754000111', $payloads[0]['to']);
        $this->assertSame('Habari "Asha" - malipo yamepokelewa', $payloads[0]['message']);
        $this->assertArrayNotHasKey('language', $payloads[0]);
        $this->assertSame('Unicode', $payloads[1]['language']);
        $this->assertDatabaseHas('communication_logs', ['recipient' => '255712345678,255754000111', 'status' => 'Sent', 'error_message' => 'Not sent to invalid numbers: 123']);
    }

    public function test_sms_center_sends_filters_and_resends(): void
    {
        Http::fake(['*' => Http::response(['status' => 'ok'], 200)]);
        $admin = $this->admin();

        $this->actingAs($admin)->get(route('sms.index'))
            ->assertOk()
            ->assertSee('Can the system send SMS?')
            ->assertSee(route('sms.send'), false);

        $this->actingAs($admin)->post(route('sms.send'), ['recipients' => 'abc, 12', 'message' => 'Habari'])
            ->assertSessionHasErrors('recipients');
        $this->actingAs($admin)->post(route('sms.send'), ['recipients' => "0712345678\n0754000111", 'message' => 'Mkutano kesho saa 4'])
            ->assertSessionHas('success');
        $this->assertDatabaseHas('communication_logs', ['template_code' => 'sms_center', 'recipient' => '255712345678,255754000111', 'status' => 'Sent']);

        $failed = CommunicationLog::create(['recipient' => '255715111222', 'channel' => 'sms', 'template_code' => 'invoice_issued', 'body' => 'Ankara mpya ya kurudia', 'status' => 'Failed', 'error_message' => 'HTTP 500: Gateway down']);

        $this->actingAs($admin)->get(route('sms.index', ['status' => 'Failed']))
            ->assertOk()
            ->assertSee('Gateway down')
            ->assertDontSee('Mkutano kesho saa 4');

        $this->actingAs($admin)->post(route('sms.resend', $failed))->assertSessionHas('success');
        $this->assertSame(2, CommunicationLog::where('body', 'Ankara mpya ya kurudia')->count());
        $this->assertDatabaseHas('communication_logs', ['body' => 'Ankara mpya ya kurudia', 'status' => 'Sent']);
    }

    public function test_sms_center_is_limited_to_roles_that_can_see_sms_logs(): void
    {
        $agent = User::where('email', 'agent@rehospace.com')->firstOrFail();

        $this->actingAs($agent)->get(route('sms.index'))->assertForbidden();
        $this->actingAs($agent)->post(route('sms.send'), ['recipients' => '0712345678', 'message' => 'Hi'])->assertForbidden();
    }

    public function test_settings_page_does_not_call_the_gateway_or_print_the_api_key(): void
    {
        Http::fake();
        SystemSetting::setVal('pushsms_api_key', 'secret_live_key_9f3c');

        $this->actingAs($this->admin())->get(route('settings.index'))
            ->assertOk()
            ->assertDontSee('secret_live_key_9f3c')
            ->assertSee('9f3c');

        Http::assertNothingSent();
    }
}
