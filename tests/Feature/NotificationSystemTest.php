<?php

namespace Tests\Feature;

use App\Models\AppNotification;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Property;
use App\Models\Reservation;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\Push\WebPushService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class NotificationSystemTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Http::fake(['*' => Http::response(['ok' => true], 201)]);
        SystemSetting::setVal('feature_online_reservations_enabled', '1');
    }

    private function admin(): User
    {
        return User::where('email', 'admin@rehospace.com')->firstOrFail();
    }

    public function test_public_reservation_creates_an_in_app_notification(): void
    {
        $property = Property::where('is_published', true)->where('status', 'Available')->firstOrFail();

        $this->post(route('public.reservation.reserve'), [
            'property_id' => $property->id,
            'name' => 'Salma Juma',
            'phone' => '0784111222',
            'email' => 'salma@example.com',
            'deposit_amount' => 500000,
        ])->assertSessionHas('success');

        $note = AppNotification::where('type', 'reservation')->latest('id')->first();
        $this->assertNotNull($note, 'Reservation must create a staff in-app notification.');
        $this->assertStringContainsString('Salma', $note->body);
        $this->assertSame(route('reservations.index'), $note->action_url);
    }

    public function test_reservation_without_branch_is_visible_while_a_branch_filter_is_active(): void
    {
        $branch = Branch::firstOrFail();
        $property = Property::firstOrFail();
        $customer = Customer::firstOrFail();

        $reservation = Reservation::create([
            'organization_id' => 1,
            'branch_id' => null,
            'property_id' => $property->id,
            'customer_id' => $customer->id,
            'reservation_number' => 'RES-BRANCHLESS1',
            'reservation_fee' => 100000,
            'deposit_paid' => 0,
            'reserved_from' => now()->toDateString(),
            'reserved_until' => now()->addDays(14)->toDateString(),
            'status' => 'Active',
        ]);

        $this->withSession(['current_branch_id' => $branch->id])
            ->actingAs($this->admin())
            ->get(route('reservations.index'))
            ->assertStatus(200)
            ->assertSee('RES-BRANCHLESS1');
    }

    public function test_bell_shows_unread_and_opening_marks_read(): void
    {
        $note = AppNotification::create([
            'type' => 'inquiry',
            'title' => 'Bell Smoke Notification',
            'body' => 'Someone asked about Kigamboni plots',
            'action_url' => route('crm.leads'),
        ]);

        $this->actingAs($this->admin())->get(route('dashboard'))
            ->assertSee('Bell Smoke Notification');

        $this->actingAs($this->admin())->get(route('notifications.open', $note))
            ->assertRedirect(route('crm.leads'));
        $this->assertNotNull($note->fresh()->read_at);

        AppNotification::create(['type' => 'system', 'title' => 'Another one', 'body' => '']);
        $this->actingAs($this->admin())->post(route('notifications.read_all'))->assertRedirect();
        $this->assertSame(0, AppNotification::unread()->count());
    }

    public function test_staff_can_register_a_push_subscription(): void
    {
        $this->actingAs($this->admin())->postJson(route('push.subscribe'), [
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/'.Str::random(24),
            'p256dh' => rtrim(strtr(base64_encode(random_bytes(65)), '+/', '-_'), '='),
            'auth' => rtrim(strtr(base64_encode(random_bytes(16)), '+/', '-_'), '='),
        ])->assertOk()->assertJson(['ok' => true]);

        $this->assertDatabaseCount('push_subscriptions', 1);
    }

    public function test_web_push_payload_encrypts_per_rfc8291_and_browser_can_decrypt(): void
    {
        $b64url = fn (string $raw) => rtrim(strtr(base64_encode($raw), '+/', '-_'), '=');
        $b64urlDecode = fn (string $data) => base64_decode(strtr($data, '-_', '+/'));

        // Simulated browser keypair + auth secret.
        $browser = WebPushService::createEcKey();
        $details = openssl_pkey_get_details($browser);
        $browserPublic = "\x04".str_pad($details['ec']['x'], 32, "\0", STR_PAD_LEFT).str_pad($details['ec']['y'], 32, "\0", STR_PAD_LEFT);
        $authSecret = random_bytes(16);

        $subscription = WebPushService::storeSubscription(
            $this->admin()->id,
            'https://updates.push.services.mozilla.com/wpush/v2/'.Str::random(20),
            $b64url($browserPublic),
            $b64url($authSecret)
        );

        $status = WebPushService::send($subscription, ['title' => 'Jaribio', 'body' => 'Uhifadhi mpya', 'url' => '/notifications']);
        $this->assertSame(201, $status);

        Http::assertSent(function ($request) use ($browser, $browserPublic, $authSecret) {
            if ($request->header('Content-Encoding')[0] !== 'aes128gcm') {
                return false;
            }
            if (! str_starts_with($request->header('Authorization')[0], 'vapid t=')) {
                return false;
            }

            // Decrypt exactly as a browser would (RFC 8291).
            $body = $request->body();
            $salt = substr($body, 0, 16);
            $serverPublic = substr($body, 21, 65);
            $cipherAndTag = substr($body, 86);

            $spkiPrefix = hex2bin('3059301306072a8648ce3d020106082a8648ce3d030107034200');
            $serverPem = "-----BEGIN PUBLIC KEY-----\n".chunk_split(base64_encode($spkiPrefix.$serverPublic), 64, "\n").'-----END PUBLIC KEY-----';
            $shared = openssl_pkey_derive($serverPem, $browser, 32);

            $ikm = hash_hkdf('sha256', $shared, 32, "WebPush: info\0".$browserPublic.$serverPublic, $authSecret);
            $cek = hash_hkdf('sha256', $ikm, 16, "Content-Encoding: aes128gcm\0", $salt);
            $nonce = hash_hkdf('sha256', $ikm, 12, "Content-Encoding: nonce\0", $salt);

            $tag = substr($cipherAndTag, -16);
            $plain = openssl_decrypt(substr($cipherAndTag, 0, -16), 'aes-128-gcm', $cek, OPENSSL_RAW_DATA, $nonce, $tag);
            if ($plain === false) {
                return false;
            }

            $payload = json_decode(rtrim($plain, "\x02"), true);

            return ($payload['title'] ?? null) === 'Jaribio' && ($payload['url'] ?? null) === '/notifications';
        });
    }

    public function test_vapid_public_key_is_a_valid_p256_point(): void
    {
        $key = WebPushService::publicKey();
        $raw = base64_decode(strtr($key, '-_', '+/'));

        $this->assertSame(65, strlen($raw));
        $this->assertSame("\x04", $raw[0]);
        $this->assertSame($key, setting('pwa_vapid_public'));
    }
}
