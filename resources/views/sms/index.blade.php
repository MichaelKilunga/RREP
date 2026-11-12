@extends('layouts.app')

@section('title', __('ops.sms_center'))
@section('inline_errors', '1')

@section('content')
@php
    $statusMeta = [
        'Sent' => ['tone' => 'success', 'icon' => 'bi-check-circle'],
        'Failed' => ['tone' => 'danger', 'icon' => 'bi-x-octagon'],
        'Skipped' => ['tone' => 'warning', 'icon' => 'bi-slash-circle'],
    ];
    $checks = [
        ['ok' => $gateway['enabled'], 'label' => __('ops.sms_check_master'), 'fix' => __('ops.sms_check_master_fix'), 'anchor' => '#pushsms'],
        ['ok' => $gateway['automatic'], 'label' => __('ops.sms_check_automatic'), 'fix' => __('ops.sms_check_automatic_fix'), 'anchor' => '#toggles'],
        ['ok' => $gateway['has_key'], 'label' => __('ops.sms_check_key'), 'fix' => __('ops.sms_check_key_fix'), 'anchor' => '#pushsms'],
        ['ok' => $gateway['sender'] !== '', 'label' => __('ops.sms_check_sender', ['sender' => $gateway['sender'] ?: '—']), 'fix' => __('ops.sms_check_sender_fix'), 'anchor' => '#pushsms'],
    ];
    $hasFilters = request()->hasAny(['status', 'template', 'q', 'from', 'to']);
    $canClean = auth()->user()->can('manage_sms');
    $canResend = auth()->user()->can('send_sms');
@endphp

<div class="page-header">
    <div class="min-w-0">
        <h1 class="brand-font mb-1"><i class="bi bi-send-check text-primary me-2"></i>{{ __('ops.sms_center') }}</h1>
        <p class="text-muted small mb-0">{{ __('ops.sms_center_subtitle') }}</p>
    </div>
    <div class="page-actions">
        @can('view_reports')<a href="{{ route('reports.sms') }}" class="btn btn-light border"><i class="bi bi-bar-chart-line me-1"></i>{{ __('ops.report_sms_short') }}</a>@endcan
        @can('manage_settings')<a href="{{ route('settings.index') }}#pushsms" class="btn btn-light border"><i class="bi bi-gear me-1"></i>{{ __('ops.sms_gateway_settings') }}</a>@endcan
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-12 col-lg-5">
        <div class="card h-100 p-3">
            <h2 class="h6 brand-font mb-3">{{ __('ops.sms_gateway_health') }}</h2>
            <ul class="list-unstyled small mb-0 d-flex flex-column gap-2">
                @foreach($checks as $check)
                    <li class="d-flex align-items-start gap-2">
                        <i class="bi {{ $check['ok'] ? 'bi-check-circle-fill text-success' : 'bi-exclamation-triangle-fill text-danger' }} mt-1"></i>
                        <span class="min-w-0">
                            <span class="{{ $check['ok'] ? '' : 'fw-semibold' }}">{{ $check['label'] }}</span>
                            @unless($check['ok'])
                                <span class="d-block text-muted">{{ $check['fix'] }}
                                    @can('manage_settings')<a href="{{ route('settings.index') }}{{ $check['anchor'] }}" class="ms-1">{{ __('ops.fix_now') }}</a>@endcan
                                </span>
                            @endunless
                        </span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="kpi-card kpi-info">
            <div class="kpi-label">{{ __('settings.live_sms_units') }}</div>
            <div class="kpi-value" id="smsBalance" aria-live="polite"><span class="spinner-border spinner-border-sm" role="status"></span></div>
            <small class="text-muted" id="smsBalanceNote">{{ __('ops.sms_sender_label', ['sender' => $gateway['sender'] ?: $gateway['client_app']]) }}</small>
            <button type="button" class="btn btn-sm btn-link px-0 d-block" id="smsBalanceRefresh"><i class="bi bi-arrow-repeat me-1"></i>{{ __('settings.refresh_balance') }}</button>
        </div>
    </div>
    <div class="col-6 col-lg-4">
        <div class="kpi-card">
            <div class="kpi-label">{{ __('ops.this_month') }}</div>
            <div class="kpi-value">{{ $month['success_rate'] === null ? '—' : $month['success_rate'].'%' }} <span class="fs-6 text-muted fw-normal">{{ __('ops.sms_success_rate') }}</span></div>
            <div class="d-flex flex-wrap gap-2 small mt-2">
                @foreach(['sent' => 'Sent', 'failed' => 'Failed', 'skipped' => 'Skipped'] as $key => $status)
                    <a href="{{ route('sms.index', ['status' => $status, 'from' => now()->startOfMonth()->toDateString()]) }}" class="badge rounded-pill bg-{{ $statusMeta[$status]['tone'] }}-subtle text-{{ $statusMeta[$status]['tone'] }}-emphasis text-decoration-none">
                        <i class="bi {{ $statusMeta[$status]['icon'] }} me-1"></i>{{ __('ops.sms_status_'.$key) }} {{ $month[$key] }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    @can('send_sms')
    <div class="col-12 col-xl-4">
        <div class="card">
            <div class="card-header brand-font"><i class="bi bi-pencil-square me-1"></i>{{ __('ops.sms_compose') }}</div>
            <form method="POST" action="{{ route('sms.send') }}" class="card-body">
                @csrf
                <div class="mb-3">
                    <label for="smsRecipients" class="form-label fw-semibold">{{ __('ops.sms_recipients') }}</label>
                    <textarea name="recipients" id="smsRecipients" rows="3" class="form-control @error('recipients') is-invalid @enderror" placeholder="0712 345 678, 0754 000 111" required>{{ old('recipients', $prefill) }}</textarea>
                    @error('recipients')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">{{ __('ops.sms_recipients_help', ['max' => \App\Http\Controllers\SmsCenterController::MAX_RECIPIENTS]) }}</div>
                </div>
                <div class="mb-3">
                    <label for="smsMessage" class="form-label fw-semibold">{{ __('ops.sms_message') }}</label>
                    <textarea name="message" id="smsMessage" rows="5" maxlength="640" class="form-control @error('message') is-invalid @enderror" required>{{ old('message') }}</textarea>
                    @error('message')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text d-flex justify-content-between gap-2"><span>{{ __('ops.sms_message_help') }}</span><span class="fw-semibold text-nowrap" id="smsMessageCounter" aria-live="polite"></span></div>
                </div>
                <button type="submit" class="btn btn-primary w-100 fw-bold" @disabled(! $gateway['enabled'] || ! $gateway['has_key'])>
                    <i class="bi bi-send me-1"></i>{{ __('ops.sms_send_now') }}
                </button>
                @if(! $gateway['enabled'] || ! $gateway['has_key'])
                    <p class="small text-danger mt-2 mb-0"><i class="bi bi-exclamation-triangle me-1"></i>{{ __('ops.sms_cannot_send_hint') }}</p>
                @endif
            </form>
        </div>
    </div>
    @endcan

    <div class="col-12 {{ auth()->user()->can('send_sms') ? 'col-xl-8' : '' }}">
        <div class="card">
            <div class="card-header brand-font d-flex justify-content-between align-items-center gap-2">
                <span>{{ __('ops.sms_log') }} <span class="badge bg-light text-dark border ms-1">{{ $logs->total() }}</span></span>
                <span class="d-flex align-items-center gap-2">
                    @if($hasFilters)<a href="{{ route('sms.index') }}" class="small">{{ __('ops.clear_filters') }}</a>@endif
                    @if($canClean && $logs->count() > 0)
                        <button type="button" class="btn btn-sm btn-light border" data-bulk-all="smslogs" aria-pressed="false" data-label-all="{{ __('admin.select_all') }}" data-label-none="{{ __('admin.deselect_all') }}">
                            <i class="bi bi-check2-square me-1"></i><span data-bulk-all-label>{{ __('admin.select_all') }} ({{ $logs->count() }})</span>
                        </button>
                    @endif
                </span>
            </div>
            <form method="GET" action="{{ route('sms.index') }}" class="card-body border-bottom py-2">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-md-4">
                        <label for="smsSearch" class="visually-hidden">{{ __('app.search') }}</label>
                        <input type="search" name="q" id="smsSearch" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="{{ __('ops.sms_search_placeholder') }}">
                    </div>
                    <div class="col-6 col-md-2">
                        <label for="smsStatus" class="visually-hidden">{{ __('app.status') }}</label>
                        <select name="status" id="smsStatus" class="form-select form-select-sm">
                            <option value="">{{ __('ops.all_statuses') }}</option>
                            @foreach($statuses as $status)
                                <option value="{{ $status }}" @selected(request('status') === $status)>{{ __('ops.sms_status_'.strtolower($status)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label for="smsTemplate" class="visually-hidden">{{ __('ops.sms_template') }}</label>
                        <select name="template" id="smsTemplate" class="form-select form-select-sm">
                            <option value="">{{ __('ops.all_types') }}</option>
                            <option value="manual" @selected(request('template') === 'manual')>manual</option>
                            @foreach($templates as $template)
                                <option value="{{ $template }}" @selected(request('template') === $template)>{{ $template }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label for="smsFrom" class="visually-hidden">{{ __('ops.date_from') }}</label>
                        <input type="date" name="from" id="smsFrom" value="{{ request('from') }}" class="form-control form-control-sm" title="{{ __('ops.date_from') }}">
                    </div>
                    <div class="col-6 col-md-2 d-flex gap-1">
                        <label for="smsTo" class="visually-hidden">{{ __('ops.date_to') }}</label>
                        <input type="date" name="to" id="smsTo" value="{{ request('to') }}" class="form-control form-control-sm" title="{{ __('ops.date_to') }}">
                        <button type="submit" class="btn btn-sm btn-primary" aria-label="{{ __('ops.apply') }}"><i class="bi bi-funnel"></i></button>
                    </div>
                </div>
            </form>
            <div class="table-responsive">
                <table class="table table-hover table-stack align-middle mb-0 small">
                    <thead class="table-light">
                        <tr>
                            @if($canClean)<th class="stack-check" style="width: 2.5rem;"><input type="checkbox" class="form-check-input" data-bulk-all="smslogs" aria-label="{{ __('admin.select_all') }}"></th>@endif
                            <th>{{ __('ops.date') }}</th>
                            <th>{{ __('ops.sms_recipient') }}</th>
                            <th>{{ __('ops.sms_message') }}</th>
                            <th>{{ __('app.status') }}</th>
                            @if($canResend || $canClean)<th class="text-end"><span class="visually-hidden">{{ __('app.actions') }}</span></th>@endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            @php $meta = $statusMeta[$log->status] ?? ['tone' => 'secondary', 'icon' => 'bi-question-circle']; @endphp
                            <tr>
                                @if($canClean)<td class="stack-check"><input type="checkbox" class="form-check-input" value="{{ $log->id }}" data-bulk-item="smslogs" aria-label="{{ $log->recipient }}"></td>@endif
                                <td class="stack-primary text-nowrap">
                                    {{ $log->created_at?->format('d M Y, H:i') }}
                                    <small class="text-muted d-block"><code>{{ $log->template_code ?: 'manual' }}</code></small>
                                </td>
                                <td class="text-nowrap">
                                    {{ str_contains((string) $log->recipient, ',') ? trans_choice('ops.sms_n_recipients', substr_count($log->recipient, ',') + 1, ['count' => substr_count($log->recipient, ',') + 1]) : format_phone_tz($log->recipient) }}
                                    @if($log->customer)
                                        @can('view_customers')<a href="{{ route('crm.customers.show', $log->customer) }}" class="d-block">{{ $log->customer->full_name }}</a>@else<span class="d-block text-muted">{{ $log->customer->full_name }}</span>@endcan
                                    @endif
                                </td>
                                <td>
                                    <div class="text-truncate" style="max-width: 420px;" title="{{ $log->body }}">{{ $log->body }}</div>
                                    @if($log->error_message)
                                        <small class="{{ $log->status === 'Sent' ? 'text-muted' : 'text-danger' }} d-block text-truncate" style="max-width: 420px;" title="{{ $log->error_message }}"><i class="bi bi-info-circle me-1"></i>{{ $log->error_message }}</small>
                                    @endif
                                </td>
                                <td><span class="badge bg-{{ $meta['tone'] }}-subtle text-{{ $meta['tone'] }}-emphasis"><i class="bi {{ $meta['icon'] }} me-1"></i>{{ __('ops.sms_status_'.strtolower((string) $log->status)) }}</span></td>
                                @if($canResend || $canClean)
                                    <td class="stack-actions text-end">
                                        <div class="d-inline-flex gap-1">
                                            @if($canResend && $log->status !== 'Sent' && $log->body)
                                                <form method="POST" action="{{ route('sms.resend', $log) }}" class="m-0">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-primary text-nowrap" @disabled(! $gateway['enabled'] || ! $gateway['has_key'])><i class="bi bi-arrow-clockwise me-1"></i>{{ __('ops.sms_resend') }}</button>
                                                </form>
                                            @endif
                                            @if($canClean)
                                                <form method="POST" action="{{ route('sms.destroy', $log) }}" class="m-0" onsubmit="return confirm({{ json_encode(__('ops.sms_log_delete_confirm')) }});">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-light border" title="{{ __('app.delete') }}" aria-label="{{ __('app.delete') }}"><i class="bi bi-trash text-danger"></i></button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr><td colspan="{{ 4 + ($canClean ? 1 : 0) + ($canResend || $canClean ? 1 : 0) }}" class="stack-no-label"><div class="empty-state"><div class="empty-icon"><i class="bi bi-chat-dots"></i></div><h6>{{ $hasFilters ? __('ops.no_report_rows') : __('ops.sms_log_empty') }}</h6></div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($logs->hasPages())
                <div class="card-footer">{{ $logs->links() }}</div>
            @endif
        </div>
    </div>
</div>

@if($canClean)
<form action="{{ route('sms.bulk') }}" method="POST" data-bulk-form="smslogs">
    @csrf
    <div class="bulk-bar flex-column d-none" data-bulk-bar="smslogs">
        <div class="bulk-inner">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary rounded-pill fs-6" data-bulk-count="smslogs">0</span>
                <span class="text-white small fw-semibold">{{ __('ops.sms_logs_selected') }}</span>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <button type="button" class="btn btn-sm btn-outline-light rounded-pill px-3" data-bulk-all="smslogs" data-label-all="{{ __('admin.select_all') }}" data-label-none="{{ __('admin.deselect_all') }}">
                    <i class="bi bi-check2-all me-1"></i><span data-bulk-all-label>{{ __('admin.select_all') }}</span>
                </button>
                <button type="submit" name="action" value="delete" class="btn btn-sm btn-danger rounded-pill px-3" data-confirm="{{ __('ops.sms_logs_bulk_delete_confirm') }}"><i class="bi bi-trash me-1"></i>{{ __('app.delete') }}</button>
                <button type="button" class="btn btn-sm btn-link text-white-50 text-decoration-none touch-target p-0" data-bulk-clear="smslogs" title="{{ __('admin.deselect_all') }}" aria-label="{{ __('admin.deselect_all') }}"><i class="bi bi-x-lg"></i></button>
            </div>
        </div>
    </div>
</form>
@endif
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const balanceEl = document.getElementById('smsBalance');
    const noteEl = document.getElementById('smsBalanceNote');
    const units = {!! json_encode(__('settings.sms_units')) !!};
    const loadBalance = function () {
        balanceEl.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span>';
        fetch({!! json_encode(route('sms.balance')) !!}, { headers: { 'Accept': 'application/json' } })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success' && !isNaN(data.balance)) {
                    balanceEl.textContent = Number(data.balance).toLocaleString('en-US');
                    noteEl.textContent = units + ' · ' + (data.sender || '');
                    balanceEl.classList.toggle('text-danger', Number(data.balance) < 50);
                } else {
                    balanceEl.textContent = '—';
                    noteEl.textContent = data.message || data.balance || '';
                }
            })
            .catch(() => { balanceEl.textContent = '—'; });
    };
    document.getElementById('smsBalanceRefresh').addEventListener('click', loadBalance);
    loadBalance();

    const area = document.getElementById('smsMessage');
    const out = document.getElementById('smsMessageCounter');
    if (area && out) {
        const plain = /^[\x20-\x7E\r\n]*$/;
        const update = () => {
            const text = area.value || '';
            const isPlain = plain.test(text);
            const perSegment = isPlain ? 160 : 70;
            const segments = text.length === 0 ? 0 : Math.ceil(text.length / perSegment);
            out.textContent = text.length + '/640 · ' + segments + ' SMS';
            out.classList.toggle('text-danger', segments > 1);
        };
        area.addEventListener('input', update);
        update();
    }
});
</script>
@endsection
