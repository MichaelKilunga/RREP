<?php

namespace App\Services;

use App\Models\CommunicationLog;
use App\Models\SystemSetting;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * PushSMS gateway client (see send_sms.md).
 *
 * Every attempt is written to communication_logs as Sent, Failed or Skipped
 * with a human-readable reason, so staff can see why a customer did not get
 * a message. Automatic notifications obey both the Push SMS master switch and
 * the "Push SMS notifications" feature toggle; messages a staff member sends
 * on purpose ($manual) only obey the master switch.
 */
class SmsService
{
    public const STATUS_SENT = 'Sent';

    public const STATUS_FAILED = 'Failed';

    public const STATUS_SKIPPED = 'Skipped';

    public const DEFAULT_BASE_URL = 'https://pushsms.rehospace.com';

    public static function send(string $to, string $message, ?string $templateCode = null, ?int $customerId = null, bool $manual = false): bool
    {
        return self::deliver($to, $message, $templateCode, $customerId, $manual)['status'] === self::STATUS_SENT;
    }

    /**
     * Send and report the outcome.
     *
     * @return array{status: string, reason: ?string, recipients: string, log: ?CommunicationLog}
     */
    public static function deliver(string $to, string $message, ?string $templateCode = null, ?int $customerId = null, bool $manual = false): array
    {
        $message = self::cleanMessage($message);
        [$valid, $invalid] = self::splitRecipients($to);
        $recipients = $valid !== [] ? implode(',', $valid) : trim($to);
        $config = self::config();

        $skipReason = match (true) {
            $message === '' => 'Empty message',
            $valid === [] => 'Invalid phone number'.($invalid !== [] ? ': '.implode(', ', $invalid) : ''),
            ! self::isEnabled() => 'SMS is switched off in Settings (Push SMS)',
            ! $manual && ! self::automaticEnabled() => 'Automatic SMS notifications are switched off in Feature toggles',
            $config['api_key'] === '' => 'PushSMS API key is not set',
            default => null,
        };

        if ($skipReason !== null) {
            Log::info("SmsService: skipped [{$recipients}] - {$skipReason}");

            return self::outcome(self::STATUS_SKIPPED, $skipReason, $recipients, $message, $templateCode, $customerId);
        }

        $reference = 'sms_'.uniqid().'_'.Str::random(4);
        $payload = [
            'to' => $recipients,
            'message' => $message,
            'client_app' => $config['client_app'],
            'reference' => $reference,
        ];
        if ($config['sender'] !== '') {
            $payload['sender'] = $config['sender'];
            $payload['sender_id'] = $config['sender'];
        }
        if (! self::isPlainText($message)) {
            $payload['language'] = 'Unicode';
        }

        try {
            $response = self::http($config)->timeout(15)->post($config['base_url'].'/api/v1/send', $payload);
            $error = self::responseError($response);
        } catch (\Throwable $e) {
            $error = 'Gateway unreachable: '.$e->getMessage();
        }

        if ($error !== null) {
            Log::error("SmsService: failed to [{$recipients}] (ref {$reference}) - {$error}");

            return self::outcome(self::STATUS_FAILED, $error, $recipients, $message, $templateCode, $customerId, $reference);
        }

        Log::info("SmsService: sent to [{$recipients}] (ref {$reference}).");
        $note = $invalid !== [] ? 'Not sent to invalid numbers: '.implode(', ', $invalid) : null;

        return self::outcome(self::STATUS_SENT, $note, $recipients, $message, $templateCode, $customerId, $reference);
    }

    /**
     * Current PushSMS balance for this sender.
     *
     * @return array{status: string, balance?: int|string, sender?: string, message?: string}
     */
    public static function getBalance(): array
    {
        $config = self::config();

        if ($config['api_key'] === '') {
            return [
                'status' => 'error',
                'balance' => 'API Key Not Set',
                'sender' => $config['sender'],
                'message' => 'Please configure PUSHSMS_API_KEY in admin settings or .env file.',
            ];
        }

        try {
            $response = self::http($config)
                ->withHeaders(['X-Client-App' => $config['client_app']])
                ->timeout(10)
                ->get($config['base_url'].'/api/v1/balance', ['sender' => $config['sender'] ?: $config['client_app']]);

            if ($response->successful() && $response->json('status') !== 'error') {
                return [
                    'status' => 'success',
                    'balance' => $response->json('balance') ?? $response->json('units') ?? 0,
                    'sender' => $response->json('sender') ?? $config['sender'],
                ];
            }

            return [
                'status' => 'error',
                'balance' => 'Unavailable',
                'sender' => $config['sender'],
                'message' => $response->json('message') ?? "HTTP {$response->status()}",
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'error',
                'balance' => 'Error',
                'sender' => $config['sender'],
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Gateway settings: an admin-saved value wins, then .env, then the default.
     * Saved empty strings fall through instead of blanking the value.
     *
     * @return array{base_url: string, api_key: string, sender: string, client_app: string}
     */
    public static function config(): array
    {
        return [
            'base_url' => rtrim((string) (SystemSetting::getVal('pushsms_base_url') ?: config('services.pushsms.url') ?: self::DEFAULT_BASE_URL), '/'),
            'api_key' => trim((string) (SystemSetting::getVal('pushsms_api_key') ?: config('services.pushsms.api_key') ?: '')),
            'sender' => strtoupper(trim((string) (SystemSetting::getVal('pushsms_sender_id') ?: config('services.pushsms.sender') ?: ''))),
            'client_app' => trim((string) (SystemSetting::getVal('pushsms_client_app') ?: config('services.pushsms.client_app') ?: 'RREP')),
        ];
    }

    /**
     * Master switch in Settings > Push SMS.
     */
    public static function isEnabled(): bool
    {
        return ! in_array(SystemSetting::getVal('sms_enabled', '1'), ['0', 0, false, 'false', 'off'], true);
    }

    /**
     * "Push SMS notifications" in Feature toggles: automatic customer and staff alerts.
     */
    public static function automaticEnabled(): bool
    {
        return is_module_enabled('sms_notifications', true);
    }

    /**
     * Normalise and validate a comma, semicolon or newline separated list.
     *
     * @return array{0: array<int, string>, 1: array<int, string>} [valid normalised numbers, rejected raw entries]
     */
    public static function splitRecipients(string $to): array
    {
        $valid = [];
        $invalid = [];
        foreach (preg_split('/[,;\r\n]+/', $to) ?: [] as $raw) {
            $raw = trim($raw);
            if ($raw === '') {
                continue;
            }
            $number = self::formatPhoneNumber($raw);
            if (self::isValidNumber($number)) {
                $valid[$number] = $number;
            } else {
                $invalid[] = $raw;
            }
        }

        return [array_values($valid), $invalid];
    }

    /**
     * Tanzanian mobiles must be 255 + 6/7 + 8 digits; other countries are accepted as 10-15 digit E.164.
     */
    public static function isValidNumber(string $number): bool
    {
        if (str_starts_with($number, '255')) {
            return (bool) preg_match('/^255[67]\d{8}$/', $number);
        }

        return (bool) preg_match('/^[1-9]\d{9,14}$/', $number);
    }

    /**
     * Standard 4-step Phone Number Normalizer to E.164 (2557xxxx / 2556xxxx).
     */
    public static function formatPhoneNumber(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', trim($phone));

        if (empty($phone)) {
            return '';
        }

        if (substr($phone, 0, 1) === '0') {
            $phone = '255'.substr($phone, 1);
        }

        if (substr($phone, 0, 4) === '2550') {
            $phone = '255'.substr($phone, 4);
        }

        if (strlen($phone) === 9) {
            $phone = '255'.$phone;
        }

        return $phone;
    }

    /**
     * Replace typographic characters and line breaks that make the gateway
     * reject a message ("Message not match with template").
     */
    public static function cleanMessage(string $message): string
    {
        $message = strtr($message, [
            "\u{2013}" => '-', "\u{2014}" => '-', "\u{2212}" => '-', "\u{00B7}" => '-',
            "\u{2018}" => "'", "\u{2019}" => "'", "\u{201C}" => '"', "\u{201D}" => '"',
            "\u{2026}" => '...', "\u{00A0}" => ' ',
        ]);
        $message = preg_replace('/\s*[\r\n]+\s*/', ' ', $message);

        return trim((string) preg_replace('/[ \t]{2,}/', ' ', $message));
    }

    public static function isPlainText(string $message): bool
    {
        return (bool) preg_match('/^[\x20-\x7E]*$/', $message);
    }

    /**
     * @param  array{base_url: string, api_key: string, sender: string, client_app: string}  $config
     */
    protected static function http(array $config): PendingRequest
    {
        return Http::withoutVerifying()->connectTimeout(5)->withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'X-API-KEY' => $config['api_key'],
        ]);
    }

    /**
     * Null when the gateway accepted the message, otherwise a readable reason.
     */
    protected static function responseError(Response $response): ?string
    {
        $status = strtolower((string) $response->json('status', ''));
        if ($response->successful() && ! in_array($status, ['error', 'failed', 'fail'], true)) {
            return null;
        }

        if (in_array($response->status(), [401, 403], true)) {
            return "API key rejected by the gateway (HTTP {$response->status()})";
        }

        $detail = (string) ($response->json('message') ?: Str::limit(trim(strip_tags($response->body())), 200));
        $fieldErrors = collect((array) $response->json('errors', []))->flatten()->implode(' ');

        return trim("HTTP {$response->status()}: {$detail} {$fieldErrors}");
    }

    /**
     * @return array{status: string, reason: ?string, recipients: string, log: ?CommunicationLog}
     */
    protected static function outcome(string $status, ?string $reason, string $recipients, string $message, ?string $templateCode, ?int $customerId, ?string $reference = null): array
    {
        $log = null;
        try {
            $log = CommunicationLog::create([
                'customer_id' => $customerId,
                'recipient' => Str::limit($recipients, 250, ''),
                'channel' => 'sms',
                'template_code' => $templateCode ? Str::limit($templateCode, 50, '') : null,
                'subject' => $reference ? 'Ref '.$reference : 'SMS',
                'body' => $message,
                'status' => $status,
                'error_message' => $reason,
                'sent_at' => $status === self::STATUS_SENT ? now() : null,
            ]);
        } catch (\Throwable $e) {
            Log::warning('CommunicationLog creation failed: '.$e->getMessage());
        }

        return ['status' => $status, 'reason' => $reason, 'recipients' => $recipients, 'log' => $log];
    }
}
