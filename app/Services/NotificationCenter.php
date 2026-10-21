<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Services\Push\WebPushService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * One entry point for "something happened on the public site" events:
 * writes the staff in-app notification, pushes it to installed devices,
 * and alerts the company by email (and SMS for money events).
 */
class NotificationCenter
{
    /**
     * @param  array{sms?: bool}  $options
     */
    public static function announce(string $type, string $title, string $body, ?string $actionUrl = null, ?int $organizationId = null, array $options = []): void
    {
        // 1. In-app notification for every staff member.
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

        // 2. Device push to subscribed staff phones/desktops.
        try {
            WebPushService::broadcast([
                'title' => $title,
                'body' => $body,
                'url' => $actionUrl ?: url('/notifications'),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Push broadcast failed: '.$e->getMessage());
        }

        // 3. Company email.
        $companyEmail = setting('company_notify_email', setting('contact_email'));
        if ($companyEmail) {
            try {
                Mail::raw($body."\n\n".($actionUrl ?: url('/dashboard')), function ($message) use ($companyEmail, $title) {
                    $message->to($companyEmail)->subject($title);
                });
            } catch (\Throwable $e) {
                Log::warning('Company email notification failed: '.$e->getMessage());
            }
        }

        // 4. Company SMS for events that move money (reservations, surveys).
        if (($options['sms'] ?? false) && setting('sms_enabled', '1') === '1') {
            $companyPhone = setting('company_notify_phone', setting('contact_phone'));
            if ($companyPhone) {
                try {
                    SmsService::send($companyPhone, $title.' - '.mb_substr($body, 0, 120));
                } catch (\Throwable $e) {
                    Log::warning('Company SMS notification failed: '.$e->getMessage());
                }
            }
        }
    }

    /**
     * Plain-text confirmation email to the customer (SMS is handled by the
     * existing NotificationService templates).
     */
    public static function emailCustomer(?string $email, string $subject, string $body): void
    {
        if (! $email) {
            return;
        }

        try {
            Mail::raw($body, function ($message) use ($email, $subject) {
                $message->to($email)->subject($subject);
            });
        } catch (\Throwable $e) {
            Log::warning('Customer email failed: '.$e->getMessage());
        }
    }
}
