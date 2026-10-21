<?php

namespace App\Services\Push;

use App\Models\PushSubscription;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Dependency-free Web Push (RFC 8030/8291/8292) using PHP's OpenSSL:
 * VAPID ES256 tokens plus aes128gcm payload encryption. Keys are generated
 * once per deployment and stored as system settings.
 */
class WebPushService
{
    private const SPKI_PREFIX = '3059301306072a8648ce3d020106082a8648ce3d030107034200';

    /**
     * The VAPID public key (base64url, uncompressed P-256 point) for browsers.
     */
    public static function publicKey(): string
    {
        $public = setting('pwa_vapid_public');
        if (! $public) {
            [$public] = self::generateKeys();
        }

        return $public;
    }

    /**
     * Create a P-256 key. Windows PHP often lacks a default openssl.cnf, so a
     * set of well-known config locations is tried before giving up.
     */
    public static function createEcKey(): \OpenSSLAsymmetricKey
    {
        $options = ['curve_name' => 'prime256v1', 'private_key_type' => OPENSSL_KEYTYPE_EC];

        $key = @openssl_pkey_new($options);
        if ($key !== false) {
            return $key;
        }

        $iniDir = php_ini_loaded_file() ? dirname(php_ini_loaded_file()) : null;
        $candidates = array_filter([
            getenv('OPENSSL_CONF') ?: null,
            $iniDir ? $iniDir.'/extras/ssl/openssl.cnf' : null,
            'C:\Program Files\Git\usr\ssl\openssl.cnf',
            'C:\Program Files\Git\mingw64\etc\ssl\openssl.cnf',
            'C:\xampp\apache\conf\openssl.cnf',
            'C:\xampp\php\extras\ssl\openssl.cnf',
            '/etc/ssl/openssl.cnf',
            '/usr/lib/ssl/openssl.cnf',
            '/etc/pki/tls/openssl.cnf',
        ], fn ($path) => $path && is_file($path));

        foreach ($candidates as $config) {
            $key = @openssl_pkey_new($options + ['config' => $config]);
            if ($key !== false) {
                return $key;
            }
        }

        throw new RuntimeException('Unable to generate an EC key: no usable OpenSSL configuration found.');
    }

    /**
     * @return array{0: string, 1: string} [publicB64url, privateB64url]
     */
    private static function generateKeys(): array
    {
        $key = self::createEcKey();

        $details = openssl_pkey_get_details($key);
        $d = str_pad($details['ec']['d'], 32, "\0", STR_PAD_LEFT);
        $point = "\x04".str_pad($details['ec']['x'], 32, "\0", STR_PAD_LEFT).str_pad($details['ec']['y'], 32, "\0", STR_PAD_LEFT);

        $public = self::b64url($point);
        $private = self::b64url($d);

        SystemSetting::setVal('pwa_vapid_public', $public, 'pwa');
        SystemSetting::setVal('pwa_vapid_private', $private, 'pwa');

        return [$public, $private];
    }

    public static function storeSubscription(int $userId, string $endpoint, string $p256dh, string $auth): PushSubscription
    {
        return PushSubscription::updateOrCreate(
            ['endpoint_hash' => hash('sha256', $endpoint)],
            ['user_id' => $userId, 'endpoint' => $endpoint, 'p256dh' => $p256dh, 'auth' => $auth]
        );
    }

    public static function removeSubscription(string $endpoint): void
    {
        PushSubscription::where('endpoint_hash', hash('sha256', $endpoint))->delete();
    }

    /**
     * Send a payload to every registered staff device. Dead subscriptions
     * (410/404 from the push service) are pruned.
     *
     * @param  array{title: string, body?: string, url?: string}  $payload
     */
    public static function broadcast(array $payload): int
    {
        $sent = 0;
        foreach (PushSubscription::all() as $subscription) {
            try {
                $status = self::send($subscription, $payload);
                if (in_array($status, [404, 410], true)) {
                    $subscription->delete();
                } elseif ($status >= 200 && $status < 300) {
                    $sent++;
                }
            } catch (\Throwable $e) {
                Log::warning('Web push failed: '.$e->getMessage());
            }
        }

        return $sent;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return int HTTP status returned by the push service
     */
    public static function send(PushSubscription $subscription, array $payload): int
    {
        $body = self::encryptPayload(
            json_encode($payload, JSON_UNESCAPED_SLASHES),
            $subscription->p256dh,
            $subscription->auth
        );

        $endpoint = $subscription->endpoint;
        $parts = parse_url($endpoint);
        $audience = $parts['scheme'].'://'.$parts['host'].(isset($parts['port']) ? ':'.$parts['port'] : '');

        $jwt = self::vapidToken($audience);

        $response = Http::withoutVerifying()
            ->connectTimeout(5)
            ->timeout(10)
            ->withHeaders([
                'Authorization' => 'vapid t='.$jwt.', k='.self::publicKey(),
                'Content-Encoding' => 'aes128gcm',
                'Content-Type' => 'application/octet-stream',
                'TTL' => '86400',
                'Urgency' => 'normal',
            ])
            ->withBody($body, 'application/octet-stream')
            ->post($endpoint);

        return $response->status();
    }

    /**
     * RFC 8291 aes128gcm encryption of the payload for one subscription.
     */
    public static function encryptPayload(string $payload, string $clientPublicB64, string $clientAuthB64): string
    {
        $clientPublic = self::b64urlDecode($clientPublicB64);
        $authSecret = self::b64urlDecode($clientAuthB64);
        if (strlen($clientPublic) !== 65 || strlen($authSecret) !== 16) {
            throw new RuntimeException('Invalid subscription keys.');
        }

        // Ephemeral server key for this message.
        $ephemeral = self::createEcKey();
        $details = openssl_pkey_get_details($ephemeral);
        $serverPublic = "\x04".str_pad($details['ec']['x'], 32, "\0", STR_PAD_LEFT).str_pad($details['ec']['y'], 32, "\0", STR_PAD_LEFT);

        $sharedSecret = openssl_pkey_derive(self::publicPem($clientPublic), $ephemeral, 32);
        if ($sharedSecret === false) {
            throw new RuntimeException('ECDH key agreement failed.');
        }

        $ikm = hash_hkdf('sha256', $sharedSecret, 32, 'WebPush: info'."\0".$clientPublic.$serverPublic, $authSecret);

        $salt = random_bytes(16);
        $cek = hash_hkdf('sha256', $ikm, 16, "Content-Encoding: aes128gcm\0", $salt);
        $nonce = hash_hkdf('sha256', $ikm, 12, "Content-Encoding: nonce\0", $salt);

        // Single record: payload + 0x02 delimiter (last record).
        $tag = '';
        $cipher = openssl_encrypt($payload."\x02", 'aes-128-gcm', $cek, OPENSSL_RAW_DATA, $nonce, $tag);
        if ($cipher === false) {
            throw new RuntimeException('Payload encryption failed.');
        }

        $recordSize = pack('N', 4096);

        return $salt.$recordSize.chr(65).$serverPublic.$cipher.$tag;
    }

    /**
     * Signed VAPID JWT (ES256) for the given push-service origin.
     */
    private static function vapidToken(string $audience): string
    {
        $private = setting('pwa_vapid_private');
        $public = setting('pwa_vapid_public');
        if (! $private || ! $public) {
            [$public] = self::generateKeys();
            $private = setting('pwa_vapid_private');
        }

        $header = self::b64url(json_encode(['typ' => 'JWT', 'alg' => 'ES256']));
        $claims = self::b64url(json_encode([
            'aud' => $audience,
            'exp' => time() + 43200,
            'sub' => 'mailto:'.(setting('contact_email', 'info@rehospace.co.tz')),
        ], JSON_UNESCAPED_SLASHES));

        $signingInput = $header.'.'.$claims;

        $der = '';
        if (! openssl_sign($signingInput, $der, self::privatePem(self::b64urlDecode($private), self::b64urlDecode($public)), OPENSSL_ALGO_SHA256)) {
            throw new RuntimeException('VAPID signing failed.');
        }

        return $signingInput.'.'.self::b64url(self::derToRawSignature($der));
    }

    /**
     * Wrap a raw uncompressed P-256 point in a SubjectPublicKeyInfo PEM.
     */
    private static function publicPem(string $point): string
    {
        $der = hex2bin(self::SPKI_PREFIX).$point;

        return "-----BEGIN PUBLIC KEY-----\n".chunk_split(base64_encode($der), 64, "\n").'-----END PUBLIC KEY-----';
    }

    /**
     * Build an SEC1 ECPrivateKey PEM from the raw scalar + public point.
     */
    private static function privatePem(string $d, string $point): string
    {
        $der = hex2bin('30770201010420').$d.hex2bin('a00a06082a8648ce3d030107a144034200').$point;

        return "-----BEGIN EC PRIVATE KEY-----\n".chunk_split(base64_encode($der), 64, "\n").'-----END EC PRIVATE KEY-----';
    }

    /**
     * Convert a DER ECDSA signature to the raw 64-byte r||s JWS form.
     */
    private static function derToRawSignature(string $der): string
    {
        $offset = 2; // SEQUENCE header
        if (ord($der[1]) & 0x80) {
            $offset += ord($der[1]) & 0x7F;
        }

        $extract = function () use ($der, &$offset): string {
            $offset++; // 0x02 INTEGER
            $len = ord($der[$offset++]);
            $value = substr($der, $offset, $len);
            $offset += $len;

            return str_pad(ltrim($value, "\0"), 32, "\0", STR_PAD_LEFT);
        };

        return $extract().$extract();
    }

    private static function b64url(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function b64urlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/')) ?: '';
    }
}
