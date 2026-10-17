@php
    $smsTemplates = [
        [
            'name' => 'sms_template_event_a',
            'id' => 'smsEventA',
            'label' => __('settings.sms_event_a_label'),
            'help' => __('settings.sms_event_a_help'),
            'placeholders' => ['{customer_name}', '{plot_code}', '{ref_number}', '{amount}', '{company_name}'],
            'default' => 'Habari {customer_name}, ombi lako la kiwanja {plot_code} limepokelewa kikamilifu! Ref: {ref_number}. Tafadhali tembelea akaunti yako kuona ankara/maelezo. - {company_name}',
        ],
        [
            'name' => 'sms_template_event_b',
            'id' => 'smsEventB',
            'label' => __('settings.sms_event_b_label'),
            'help' => __('settings.sms_event_b_help'),
            'placeholders' => ['{customer_name}', '{survey_code}'],
            'default' => 'Ukaguzi na uchambuzi wa site yako umekamilika, tafadhari angalia kwenye account yako kuona nyaraka zako.',
        ],
        [
            'name' => 'sms_template_invoice_issued',
            'id' => 'smsInvoice',
            'label' => __('settings.sms_invoice_label'),
            'help' => __('settings.sms_invoice_help'),
            'placeholders' => ['{customer_name}', '{invoice_number}', '{total_amount}', '{due_date}'],
            'default' => 'Habari {customer_name}, ankara mpya #{invoice_number} ya kiasi cha {total_amount} imeandaliwa. Tarehe ya mwisho: {due_date}. - {company_name}',
        ],
    ];
    $balanceOk = isset($smsBalance['status']) && $smsBalance['status'] === 'success' && is_numeric($smsBalance['balance'] ?? null);
@endphp

<div class="row g-3 g-lg-4">
    <div class="col-12 col-lg-8">
        <form action="{{ route('settings.pushsms') }}" method="POST">
            @csrf

            {{-- Gateway credentials --}}
            <div class="card rounded-4 mb-3">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div class="min-w-0">
                        <h5 class="brand-font mb-0">{{ __('settings.pushsms_title') }}</h5>
                        <small class="text-muted fw-normal">{{ __('settings.pushsms_subtitle') }}</small>
                    </div>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill">send_sms.md</span>
                </div>
                <div class="card-body p-3 p-md-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold" for="pushsmsBaseUrl">{{ __('settings.api_base_url') }}</label>
                            <input type="url" name="pushsms_base_url" class="form-control" id="pushsmsBaseUrl" inputmode="url" autocomplete="url" value="{{ setting('pushsms_base_url', config('services.pushsms.url', 'https://pushsms.rehospace.com')) }}" placeholder="https://pushsms.rehospace.com" required>
                            <div class="form-text">{{ __('settings.api_base_url_help') }}</div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold" for="pushsmsApiKey">{{ __('settings.api_key') }}</label>
                            <input type="password" name="pushsms_api_key" class="form-control" id="pushsmsApiKey" autocomplete="off" placeholder="••••••••••••••••" value="{{ setting('pushsms_api_key', config('services.pushsms.api_key')) }}">
                            <div class="form-text">{{ __('settings.api_key_help') }}</div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold" for="pushsmsSenderId">{{ __('settings.sender_id') }}</label>
                            <input type="text" name="pushsms_sender_id" class="form-control text-uppercase" id="pushsmsSenderId" maxlength="11" data-uppercase autocomplete="off" value="{{ setting('pushsms_sender_id', config('services.pushsms.sender', 'REALESTATE')) }}" placeholder="AVENIX" required>
                            <div class="form-text">{{ __('settings.sender_id_help') }}</div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold" for="pushsmsClientApp">{{ __('settings.client_app') }}</label>
                            <input type="text" name="pushsms_client_app" class="form-control" id="pushsmsClientApp" autocomplete="off" value="{{ setting('pushsms_client_app', config('services.pushsms.client_app', 'RREP')) }}" placeholder="RREP" required>
                            <div class="form-text">{{ __('settings.client_app_help') }}</div>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch p-3 ps-5 bg-light rounded-3 border">
                                <input class="form-check-input" type="checkbox" name="sms_enabled" value="1" id="smsEnabledSwitch" @checked(setting('sms_enabled', '1') === '1')>
                                <label class="form-check-label fw-bold text-dark" for="smsEnabledSwitch">{{ __('settings.sms_enabled_label') }}</label>
                                <div class="form-text">{{ __('settings.sms_enabled_help') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- SMS templates --}}
            <div class="card rounded-4 mb-3">
                <div class="card-header">
                    <h5 class="brand-font mb-0">{{ __('settings.sms_templates_title') }}</h5>
                    <small class="text-muted fw-normal">{{ __('settings.sms_templates_subtitle') }}</small>
                </div>
                <div class="card-body p-3 p-md-4">
                    @foreach($smsTemplates as $template)
                        <div class="{{ $loop->last ? '' : 'mb-4' }}">
                            <label class="form-label fw-semibold" for="{{ $template['id'] }}">{{ $template['label'] }}</label>
                            <textarea name="{{ $template['name'] }}" class="form-control" id="{{ $template['id'] }}" rows="3" data-sms-counter="#{{ $template['id'] }}Counter">{{ setting($template['name'], $template['default']) }}</textarea>
                            <div class="d-flex flex-wrap justify-content-between gap-2 form-text">
                                <span>{{ $template['help'] }}</span>
                                <span class="fw-semibold text-nowrap" id="{{ $template['id'] }}Counter" aria-live="polite"></span>
                            </div>
                            <div class="d-flex flex-wrap gap-1 mt-1">
                                @foreach($template['placeholders'] as $placeholder)
                                    <code class="small bg-light border rounded px-1.5">{{ $placeholder }}</code>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="card-footer bg-white border-top-0 pt-0 pb-3 px-3 px-md-4">
                    <div class="d-grid d-sm-flex justify-content-sm-end">
                        <button type="submit" class="btn btn-primary fw-bold rounded-pill px-4 py-2 shadow-sm">
                            <i class="bi bi-save me-1"></i>{{ __('settings.save_pushsms') }}
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="col-12 col-lg-4">
        {{-- Live balance --}}
        <div class="card rounded-4 mb-3 border-0 bg-primary text-white">
            <div class="card-body p-3 p-md-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-white-50 small fw-semibold text-uppercase">{{ __('settings.live_sms_units') }}</span>
                    <span class="badge bg-white text-primary">{{ __('settings.prepaid_pool') }}</span>
                </div>
                <div class="h2 fw-bold mb-1" id="liveBalanceText" aria-live="polite">
                    @if($balanceOk)
                        {{ number_format((float) $smsBalance['balance']) }} <span class="fs-6 fw-normal">{{ __('settings.sms_units') }}</span>
                    @else
                        {{ $smsBalance['balance'] ?? __('settings.checking') }}
                    @endif
                </div>
                <div class="small text-white-50">{{ __('settings.sender_id') }}: <strong>{{ setting('pushsms_sender_id', config('services.pushsms.sender', 'REALESTATE')) }}</strong></div>

                <hr class="border-white-50 my-3">

                <button type="button" class="btn btn-light w-100 fw-semibold" onclick="refreshSmsBalance()">
                    <i class="bi bi-arrow-repeat me-1"></i>{{ __('settings.refresh_balance') }}
                </button>
            </div>
        </div>

        {{-- Integration notes --}}
        <div class="card rounded-4">
            <div class="card-header">
                <h6 class="brand-font mb-0">{{ __('settings.integration_specs') }}</h6>
            </div>
            <div class="card-body small">
                <ul class="list-unstyled mb-0 d-flex flex-column gap-2">
                    <li><i class="bi bi-check-circle-fill text-success me-2"></i><strong>{{ __('settings.spec_endpoint') }}:</strong> <code>/api/v1/send</code> (POST)</li>
                    <li><i class="bi bi-check-circle-fill text-success me-2"></i><strong>{{ __('settings.spec_headers') }}:</strong> <code>application/json</code>, <code>X-API-KEY</code></li>
                    <li><i class="bi bi-check-circle-fill text-success me-2"></i><strong>{{ __('settings.spec_dual_params') }}:</strong> <code>sender</code> + <code>sender_id</code></li>
                    <li><i class="bi bi-check-circle-fill text-success me-2"></i><strong>{{ __('settings.spec_normalisation') }}:</strong> <code>0784 100 200</code> → <code>255784100200</code></li>
                    <li><i class="bi bi-info-circle-fill text-primary me-2"></i>{{ __('settings.sms_length_hint') }}</li>
                </ul>
            </div>
        </div>
    </div>
</div>
