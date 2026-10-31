<?php

namespace App\Http\Controllers;

use App\Models\CommunicationLog;
use App\Models\Customer;
use App\Services\SmsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SmsCenterController extends Controller
{
    public const MAX_RECIPIENTS = 100;

    public const STATUSES = [SmsService::STATUS_SENT, SmsService::STATUS_FAILED, SmsService::STATUS_SKIPPED];

    public function index(Request $request): View
    {
        $term = trim((string) $request->input('q'));
        $phoneTerm = preg_match('/^[\d\s+()-]{6,}$/', $term) ? SmsService::formatPhoneNumber($term) : null;

        $logs = CommunicationLog::with('customer')
            ->where('channel', 'sms')
            ->when(in_array($request->input('status'), self::STATUSES, true), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('template'), fn ($q) => $request->input('template') === 'manual'
                ? $q->whereNull('template_code')
                : $q->where('template_code', $request->input('template')))
            ->when($term !== '', fn ($q) => $q->where(fn ($w) => $w->where('recipient', 'like', "%{$term}%")
                ->when($phoneTerm, fn ($p) => $p->orWhere('recipient', 'like', "%{$phoneTerm}%"))
                ->orWhere('body', 'like', "%{$term}%")
                ->orWhere('error_message', 'like', "%{$term}%")))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('created_at', '>=', $request->input('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('created_at', '<=', $request->input('to')))
            ->latest()
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        $monthCounts = CommunicationLog::where('channel', 'sms')
            ->where('created_at', '>=', now()->startOfMonth())
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');
        $monthTotal = (int) $monthCounts->sum();
        $month = [
            'sent' => (int) ($monthCounts[SmsService::STATUS_SENT] ?? 0),
            'failed' => (int) ($monthCounts[SmsService::STATUS_FAILED] ?? 0),
            'skipped' => (int) ($monthCounts[SmsService::STATUS_SKIPPED] ?? 0),
            'success_rate' => $monthTotal > 0 ? round(($monthCounts[SmsService::STATUS_SENT] ?? 0) / $monthTotal * 100, 1) : null,
        ];

        $templates = CommunicationLog::where('channel', 'sms')->whereNotNull('template_code')->distinct()->orderBy('template_code')->pluck('template_code');

        $config = SmsService::config();
        $gateway = [
            'enabled' => SmsService::isEnabled(),
            'automatic' => SmsService::automaticEnabled(),
            'has_key' => $config['api_key'] !== '',
            'sender' => $config['sender'],
            'client_app' => $config['client_app'],
        ];

        return view('sms.index', [
            'logs' => $logs,
            'month' => $month,
            'templates' => $templates,
            'gateway' => $gateway,
            'statuses' => self::STATUSES,
            'prefill' => (string) $request->query('to', ''),
        ]);
    }

    public function send(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'recipients' => 'required|string|max:3000',
            'message' => 'required|string|max:640',
        ]);

        [$valid] = SmsService::splitRecipients($data['recipients']);
        if ($valid === []) {
            throw ValidationException::withMessages(['recipients' => __('ops.sms_no_valid_numbers')]);
        }
        if (count($valid) > self::MAX_RECIPIENTS) {
            throw ValidationException::withMessages(['recipients' => __('ops.sms_too_many_recipients', ['max' => self::MAX_RECIPIENTS])]);
        }

        $customerId = count($valid) === 1 ? $this->customerIdForNumber($valid[0]) : null;
        $result = SmsService::deliver($data['recipients'], $data['message'], 'sms_center', $customerId, manual: true);

        if ($result['status'] !== SmsService::STATUS_SENT) {
            return back()->withInput()->with('error', __('ops.sms_not_sent_reason', ['reason' => $result['reason']]));
        }

        $message = trans_choice('ops.sms_center_sent', count($valid), ['count' => count($valid)]);

        return back()->with('success', $result['reason'] ? $message.' '.$result['reason'] : $message);
    }

    public function resend(CommunicationLog $log): RedirectResponse
    {
        abort_unless($log->channel === 'sms', 404);

        $result = SmsService::deliver((string) $log->recipient, (string) $log->body, $log->template_code, $log->customer_id, manual: true);

        return $result['status'] === SmsService::STATUS_SENT
            ? back()->with('success', __('ops.sms_resent'))
            : back()->with('error', __('ops.sms_not_sent_reason', ['reason' => $result['reason']]));
    }

    public function balance(): JsonResponse
    {
        return response()->json(SmsService::getBalance());
    }

    /**
     * Link a one-off message to the customer who owns the number, when exactly one does.
     */
    protected function customerIdForNumber(string $number): ?int
    {
        $matches = Customer::where('phone', 'like', '%'.substr($number, -9))->limit(2)->pluck('id');

        return $matches->count() === 1 ? (int) $matches->first() : null;
    }
}
