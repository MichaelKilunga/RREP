<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Services\Push\WebPushService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * One entry point for "something happened on the public site" events.
 *
 * The in-app notification is written immediately; every outbound channel
 * (device push, email, SMS) is deferred until after the HTTP response has
 * been sent, so a slow or unreachable mail/SMS gateway can never stall or
 * 500 a visitor-facing request.
 */
class NotificationCenter
{
    /**
     * @param  array{sms?: bool}  $options
     */
    public static function announce(string $type, string $title, string $body, ?string $actionUrl = null, ?int $organizationId = null, array $options = []): void
    {
        // 1. In-app notification for every staff member (fast, local insert).
        try {
            AppNotification::create([
                'organization_id' => $organizationId ?? current_organization()?->id,
                'type' => $type,
                'title' => $title,
                'body' => $body,
                'action_url' => $actionUrl,
            ]);
        } catch (\Throwable $e) {
            Log::warning('In-app notification failed: '.$e->getMessage());
        }

        $companyEmail = (string) setting('company_notify_email', setting('contact_email'));
        $companyPhone = (string) setting('company_notify_phone', setting('contact_phone'));
        $smsWanted = ($options['sms'] ?? false) && setting('sms_enabled', '1') === '1';
        $url = $actionUrl ?: url('/notifications');

        self::afterResponse(function () use ($title, $body, $url, $companyEmail, $companyPhone, $smsWanted) {
            // 2. Device push to subscribed staff phones/desktops.
            try {
                WebPushService::broadcast(['title' => $title, 'body' => $body, 'url' => $url]);
            } catch (\Throwable $e) {
                Log::warning('Push broadcast failed: '.$e->getMessage());
            }

            // 3. Company email.
            if ($companyEmail !== '') {
                try {
                    Mail::raw($body."\n\n".$url, function ($message) use ($companyEmail, $title) {
                        $message->to($companyEmail)->subject($title);
                    });
                } catch (\Throwable $e) {
                    Log::warning('Company email notification failed: '.$e->getMessage());
                }
            }

            // 4. Company SMS for events that move money (reservations, surveys, quotes).
            if ($smsWanted && $companyPhone !== '') {
                try {
                    SmsService::send($companyPhone, $title.' - '.mb_substr($body, 0, 120));
                } catch (\Throwable $e) {
                    Log::warning('Company SMS notification failed: '.$e->getMessage());
                }
            }
        });
    }

    /**
     * Plain-text confirmation email to the customer, sent after the response.
     */
    public static function emailCustomer(?string $email, string $subject, string $body): void
    {
        if (! $email) {
            return;
        }

        self::afterResponse(function () use ($email, $subject, $body) {
            try {
                Mail::raw($body, function ($message) use ($email, $subject) {
                    $message->to($email)->subject($subject);
                });
            } catch (\Throwable $e) {
                Log::warning('Customer email failed: '.$e->getMessage());
            }
        });
    }

    /**
     * Confirmation SMS to the customer, sent after the response.
     */
    public static function smsCustomer(?string $phone, string $message, ?string $templateCode = null, ?int $customerId = null): void
    {
        if (! $phone) {
            return;
        }

        self::afterResponse(function () use ($phone, $message, $templateCode, $customerId) {
            try {
                SmsService::send($phone, $message, $templateCode, $customerId);
            } catch (\Throwable $e) {
                Log::warning('Customer SMS failed: '.$e->getMessage());
            }
        });
    }

    /**
     * Run work after the HTTP response has gone out (immediately in CLI/tests
     * where the app terminates right after the request cycle).
     */
    protected static function afterResponse(callable $job): void
    {
        try {
            dispatch($job)->afterResponse();
        } catch (\Throwable $e) {
            // Fall back to running inline rather than losing the notification.
            try {
                $job();
            } catch (\Throwable $inner) {
                Log::warning('Deferred notification failed: '.$inner->getMessage());
            }
        }
    }
}
