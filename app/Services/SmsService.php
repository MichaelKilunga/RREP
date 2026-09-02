<?php

namespace App\Services;

use App\Models\CommunicationLog;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SmsService
{
    /**
     * Send SMS message to one or multiple recipients.
     */
    public static function send(string $to, string $message, ?string $templateCode = null, ?int $customerId = null): bool
    {
        $to = trim($to);

        // 1. Normalize phone number(s) to 2557xxxx format
        $rawNumbers = explode(',', $to);
        $numbers = array_map([self::class, 'formatPhoneNumber'], $rawNumbers);
        $normalizedTo = implode(',', array_filter($numbers));

        if (empty($normalizedTo) || empty($message)) {
            Log::warning('SmsService: skipped - empty recipient or message.');

            return false;
        }

        // Check if SMS notifications are enabled in system settings
        $smsEnabled = SystemSetting::getVal('sms_enabled', '1');
        if ($smsEnabled === '0' || $smsEnabled === false) {
            Log::info("SmsService: SMS is disabled globally in settings. Message to [{$normalizedTo}] skipped.");

            return false;
        }

        $baseUrl = rtrim(SystemSetting::getVal('pushsms_base_url', config('services.pushsms.url', 'https://pushsms.rehospace.com')), '/');
        $apiKey = SystemSetting::getVal('pushsms_api_key', config('services.pushsms.api_key', ''));
        $sender = SystemSetting::getVal('pushsms_sender_id', config('services.pushsms.sender', 'REALESTATE'));
        $clientApp = SystemSetting::getVal('pushsms_client_app', config('services.pushsms.client_app', 'RREP'));

        $reference = 'sms_'.uniqid().'_'.Str::random(4);

        try {
            // 2. Construct payload with both sender and sender_id (MANDATORY per send_sms.md)
            $payload = [
                'to' => $normalizedTo,
                'message' => $message,
                'client_app' => $clientApp,
                'reference' => $reference,
            ];

            if (! empty($sender)) {
                $payload['sender'] = $sender;
                $payload['sender_id'] = $sender; // Required by remote API controller
            }

            // 3. Explicitly enforce application/json headers
            $response = Http::withoutVerifying()->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'X-API-KEY' => $apiKey,
            ])->timeout(15)->post($baseUrl.'/api/v1/send', $payload);

            $isSuccess = $response->successful();
            $responseBody = $response->body();

            // Log communication in database if table exists
            try {
                CommunicationLog::create([
                    'recipient' => $normalizedTo,
                    'channel' => 'sms',
                    'template_code' => $templateCode,
                    'subject' => 'SMS Dispatch',
                    'body' => $message,
                    'status' => $isSuccess ? 'Sent' : 'Failed',
                    'error_message' => $isSuccess ? null : "HTTP {$response->status()}: {$responseBody}",
                    'sent_at' => $isSuccess ? now() : null,
                ]);
            } catch (\Throwable $e) {
                Log::warning('CommunicationLog creation failed: '.$e->getMessage());
            }

            if ($isSuccess) {
                Log::info("SmsService: OK to [{$normalizedTo}], response=".$responseBody);

                return true;
            }

            Log::error("SmsService: failed to [{$normalizedTo}]. HTTP {$response->status()}: {$responseBody}");

            return false;

        } catch (\Throwable $e) {
            Log::error("SmsService: exception - {$e->getMessage()}");

            try {
                CommunicationLog::create([
                    'recipient' => $normalizedTo,
                    'channel' => 'sms',
                    'template_code' => $templateCode,
                    'subject' => 'SMS Exception',
                    'body' => $message,
                    'status' => 'Failed',
                    'error_message' => $e->getMessage(),
                ]);
            } catch (\Throwable $logEx) {
            }

            return false;
        }
    }

    /**
     * Check current SMS Balance from pushsms.rehospace.com.
     *
     * @return array{status: string, balance?: int|string, sender?: string, message?: string}
     */
    public static function getBalance(): array
    {
        $baseUrl = rtrim(SystemSetting::getVal('pushsms_base_url', config('services.pushsms.url', 'https://pushsms.rehospace.com')), '/');
        $apiKey = SystemSetting::getVal('pushsms_api_key', config('services.pushsms.api_key', ''));
        $sender = SystemSetting::getVal('pushsms_sender_id', config('services.pushsms.sender', 'AVENIX LTD'));
        $clientApp = SystemSetting::getVal('pushsms_client_app', config('services.pushsms.client_app', 'AVENIX'));

        if (empty($apiKey)) {
            return [
                'status' => 'error',
                'balance' => 'API Key Not Set',
                'sender' => $sender,
                'message' => 'Please configure PUSHSMS_API_KEY in admin settings or .env file.',
            ];
        }

        try {
            $response = Http::withoutVerifying()->withHeaders([
                'Accept' => 'application/json',
                'X-API-KEY' => $apiKey,
                'X-Client-App' => $clientApp,
            ])->timeout(10)->get($baseUrl.'/api/v1/balance', [
                'sender' => $sender,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'status' => 'success',
                    'balance' => $data['balance'] ?? ($data['units'] ?? 0),
                    'sender' => $data['sender'] ?? $sender,
                ];
            }

            return [
                'status' => 'error',
                'balance' => 'Unavailable',
                'sender' => $sender,
                'message' => $response->json('message') ?? "HTTP {$response->status()}",
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'error',
                'balance' => 'Error',
                'sender' => $sender,
                'message' => $e->getMessage(),
            ];
        }
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
}
