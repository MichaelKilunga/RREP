<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
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

        $inbox = AppNotification::forUser(auth()->user())->latest()->paginate(20);

        return view('notifications.index', compact('inbox', 'templates', 'logs'));
    }

    public function open(AppNotification $notification)
    {
        abort_unless($notification->user_id === null || $notification->user_id === auth()->id(), 403);

        if (! $notification->read_at) {
            $notification->forceFill(['read_at' => now()])->save();
        }

        return $notification->action_url
            ? redirect($notification->action_url)
            : redirect()->route('notifications.index');
    }

    public function readAll()
    {
        AppNotification::forUser(auth()->user())->unread()->update(['read_at' => now()]);

        return back()->with('success', __('app.notifications_marked_read'));
    }
}
