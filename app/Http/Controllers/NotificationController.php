<?php

namespace App\Http\Controllers;

use App\Models\CommunicationLog;
use App\Models\NotificationTemplate;

class NotificationController extends Controller
{
    public function index()
    {
        if (NotificationTemplate::count() === 0) {
            $defaults = [
                [
                    'code' => 'welcome',
                    'name' => 'Client Welcome & Onboarding',
                    'channel' => 'sms',
                    'subject' => 'Welcome to Platform',
                    'body' => 'Dear {customer_name}, welcome to {company_name}! Access your client portal here: {portal_url}',
                    'variables_json' => ['{customer_name}', '{company_name}', '{portal_url}'],
                    'is_active' => true,
                ],
                [
                    'code' => 'reservation_confirmed',
                    'name' => 'Plot Reservation Confirmation',
                    'channel' => 'sms',
                    'subject' => 'Reservation Confirmed',
                    'body' => 'Dear {customer_name}, your plot reservation has been confirmed. Ref: {ref_number}. - {company_name}',
                    'variables_json' => ['{customer_name}', '{ref_number}', '{company_name}'],
                    'is_active' => true,
                ],
                [
                    'code' => 'survey_request',
                    'name' => 'Cadastral Survey Request Acknowledgement',
                    'channel' => 'sms',
                    'subject' => 'Survey Request Received',
                    'body' => 'Habari {customer_name}, ombi lako la survey limepokelewa. Mtaalamu wetu atawasiliana nawe hivi punde. - {company_name}',
                    'variables_json' => ['{customer_name}', '{company_name}'],
                    'is_active' => true,
                ],
                [
                    'code' => 'invoice_issued',
                    'name' => 'Billing Invoice Dispatched',
                    'channel' => 'sms',
                    'subject' => 'New Invoice Available',
                    'body' => 'Habari {customer_name}, ankara mpya #{invoice_number} ya kiasi cha {total_amount} imeandaliwa. Tarehe ya mwisho: {due_date}. - {company_name}',
                    'variables_json' => ['{customer_name}', '{invoice_number}', '{total_amount}', '{due_date}', '{company_name}'],
                    'is_active' => true,
                ],
            ];

            foreach ($defaults as $tmpl) {
                NotificationTemplate::create($tmpl);
            }
        }

        $templates = NotificationTemplate::all();
        $logs = CommunicationLog::latest()->take(50)->get();

        return view('notifications.index', compact('templates', 'logs'));
    }
}
