<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\NotificationTemplate;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    /**
     * Dispatch notification across specified channel(s) with template merge tags.
     */
    public static function notify(
        Customer $customer,
        string $templateCode,
        array $mergeData = [],
        array $channels = ['sms', 'email']
    ): array {
        $results = [];

        // Fetch template from database or use defaults
        $template = NotificationTemplate::where('code', $templateCode)->where('is_active', true)->first();

        $companyName = SystemSetting::getVal('company_name', config('app.name', 'Avenix Co Ltd'));
        $portalUrl = url('/login');

        $baseData = [
            '{customer_name}' => $customer->full_name ?: 'Valued Client',
            '{first_name}' => $customer->first_name ?: 'Client',
            '{phone}' => $customer->phone,
            '{company_name}' => $companyName,
            '{portal_url}' => $portalUrl,
            '{date}' => now()->format('Y-m-d'),
        ];

        $merged = array_merge($baseData, $mergeData);

        // 1. Dispatch SMS
        if (in_array('sms', $channels) && ! empty($customer->phone)) {
            $smsBody = self::getSmsMessage($templateCode, $template, $merged);
            if (! empty($smsBody)) {
                $results['sms'] = SmsService::send($customer->phone, $smsBody, $templateCode, $customer->id);
            }
        }

        // 2. Dispatch Email
        if (in_array('email', $channels) && ! empty($customer->email)) {
            $subject = $template?->subject ?: self::getDefaultSubject($templateCode, $merged);
            $emailBody = $template?->body ?: self::getDefaultEmailBody($templateCode, $merged);
            $subject = self::replacePlaceholders($subject, $merged);
            $emailBody = self::replacePlaceholders($emailBody, $merged);

            try {
                Mail::raw($emailBody, function ($m) use ($customer, $subject) {
                    $m->to($customer->email)->subject($subject);
                });
                $results['email'] = true;
            } catch (\Throwable $e) {
                Log::error("NotificationService: email dispatch error - {$e->getMessage()}");
                $results['email'] = false;
            }
        }

        return $results;
    }

    /**
     * Trigger Event A: SMS sent to buyer confirming a land booking or listing submission.
     */
    public static function triggerEventA_BookingConfirmation(Customer $customer, string $plotCode, string $refNumber, ?string $invoiceAmount = null): bool
    {
        $companyName = SystemSetting::getVal('company_name', 'Avenix');
        $template = SystemSetting::getVal(
            'sms_template_event_a',
            'Habari {customer_name}, ombi lako la kiwanja {plot_code} limepokelewa kikamilifu! Ref: {ref_number}. Tafadhali tembelea akaunti yako kuona ankara/maelezo. - {company_name}'
        );

        $data = [
            '{customer_name}' => $customer->first_name ?: 'Mteja',
            '{plot_code}' => $plotCode,
            '{ref_number}' => $refNumber,
            '{amount}' => $invoiceAmount ?: '',
            '{company_name}' => $companyName,
        ];

        $message = self::replacePlaceholders($template, $data);

        return SmsService::send($customer->phone, $message, 'event_a_booking_confirm', $customer->id);
    }

    /**
     * Trigger Event B: Exact SMS per Pilot Client SRS when survey is completed.
     * SRS Text: "Ukaguzi na uchambuzi wa site yako umekamilika, tafadhari angalia kwenye account yako kuona nyaraka zako."
     */
    public static function triggerEventB_SurveyCompletion(Customer $customer, ?string $surveyCode = null): bool
    {
        $defaultMsg = 'Ukaguzi na uchambuzi wa site yako umekamilika, tafadhari angalia kwenye account yako kuona nyaraka zako.';
        $template = SystemSetting::getVal('sms_template_event_b', $defaultMsg);

        $data = [
            '{customer_name}' => $customer->first_name ?: 'Mteja',
            '{survey_code}' => $surveyCode ?: '',
        ];

        $message = self::replacePlaceholders($template, $data);

        return SmsService::send($customer->phone, $message, 'event_b_survey_completed', $customer->id);
    }

    /**
     * Trigger Invoice Issued Notification.
     */
    public static function triggerInvoiceIssued(Customer $customer, string $invoiceNumber, string $totalAmount, string $dueDate): bool
    {
        $companyName = SystemSetting::getVal('company_name', 'Avenix');
        $template = SystemSetting::getVal(
            'sms_template_invoice_issued',
            'Habari {customer_name}, ankara mpya #{invoice_number} ya kiasi cha {total_amount} imeandaliwa. Tarehe ya mwisho: {due_date}. - {company_name}'
        );

        $data = [
            '{customer_name}' => $customer->first_name ?: 'Mteja',
            '{invoice_number}' => $invoiceNumber,
            '{total_amount}' => $totalAmount,
            '{due_date}' => $dueDate,
            '{company_name}' => $companyName,
        ];

        $message = self::replacePlaceholders($template, $data);

        return SmsService::send($customer->phone, $message, 'invoice_issued', $customer->id);
    }

    /**
     * Generate WhatsApp Direct Click-To-Chat URL for a property or booking inquiry.
     */
    public static function getWhatsAppLink(string $phone, string $prefilledText = ''): string
    {
        $normalized = SmsService::formatPhoneNumber($phone);
        $encoded = urlencode($prefilledText);

        return "https://wa.me/{$normalized}?text={$encoded}";
    }

    /**
     * Replace merge tag placeholders.
     */
    public static function replacePlaceholders(string $text, array $data): string
    {
        return str_replace(array_keys($data), array_values($data), $text);
    }

    private static function getSmsMessage(string $templateCode, ?NotificationTemplate $template, array $data): string
    {
        if ($template && ! empty($template->body)) {
            return self::replacePlaceholders($template->body, $data);
        }

        return match ($templateCode) {
            'welcome' => self::replacePlaceholders('Dear {customer_name}, welcome to {company_name}! Access your client portal here: {portal_url}', $data),
            'reservation_confirmed' => self::replacePlaceholders('Dear {customer_name}, your plot reservation has been confirmed. Ref: {ref_number}. - {company_name}', $data),
            'survey_request' => self::replacePlaceholders('Habari {customer_name}, ombi lako la survey limepokelewa. Mtaalamu wetu atawasiliana nawe hivi punde. - {company_name}', $data),
            default => '',
        };
    }

    private static function getDefaultSubject(string $templateCode, array $data): string
    {
        return match ($templateCode) {
            'welcome' => 'Welcome to '.($data['{company_name}'] ?? 'Avenix'),
            'reservation_confirmed' => 'Plot Reservation Confirmed - '.($data['{company_name}'] ?? 'Avenix'),
            'survey_completed' => 'Land Survey & Documentation Completed',
            'invoice_issued' => 'New Invoice from '.($data['{company_name}'] ?? 'Avenix'),
            default => 'Notification from '.($data['{company_name}'] ?? 'Avenix'),
        };
    }

    private static function getDefaultEmailBody(string $templateCode, array $data): string
    {
        return "Dear {customer_name},\n\nThis is a notification regarding your account with {company_name}.\n\nThank you.";
    }
}
