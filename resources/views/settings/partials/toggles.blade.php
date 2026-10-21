@php
    $toggleGroups = [
        'marketplace' => [
            'title' => __('settings.group_marketplace'),
            'icon' => 'bi-shop',
            'items' => [
                'marketplace_functions' => ['title' => __('settings.toggle_marketplace_functions'), 'desc' => __('settings.toggle_marketplace_functions_desc'), 'icon' => 'bi-shop'],
                'property_owner_submissions' => ['title' => __('settings.toggle_property_owner_submissions'), 'desc' => __('settings.toggle_property_owner_submissions_desc'), 'icon' => 'bi-building-up'],
                'online_reservations' => ['title' => __('settings.toggle_online_reservations'), 'desc' => __('settings.toggle_online_reservations_desc'), 'icon' => 'bi-bookmark-check'],
                'online_bookings' => ['title' => __('settings.toggle_online_bookings'), 'desc' => __('settings.toggle_online_bookings_desc'), 'icon' => 'bi-calendar-event'],
                'crm_lead_tracking' => ['title' => __('settings.toggle_crm_lead_tracking'), 'desc' => __('settings.toggle_crm_lead_tracking_desc'), 'icon' => 'bi-funnel-fill'],
            ],
        ],
        'communication' => [
            'title' => __('settings.group_communication'),
            'icon' => 'bi-chat-dots',
            'items' => [
                'whatsapp_notifications' => ['title' => __('settings.toggle_whatsapp_notifications'), 'desc' => __('settings.toggle_whatsapp_notifications_desc'), 'icon' => 'bi-whatsapp'],
                'sms_notifications' => ['title' => __('settings.toggle_sms_notifications'), 'desc' => __('settings.toggle_sms_notifications_desc'), 'icon' => 'bi-chat-left-dots'],
            ],
        ],
        'finance' => [
            'title' => __('settings.group_finance'),
            'icon' => 'bi-cash-coin',
            'items' => [
                'payment_processing' => ['title' => __('settings.toggle_payment_processing'), 'desc' => __('settings.toggle_payment_processing_desc'), 'icon' => 'bi-credit-card-2-front'],
            ],
        ],
        'admin_panels' => [
            'title' => __('settings.group_admin_panels'),
            'icon' => 'bi-window-sidebar',
            'items' => [
                'properties_module' => ['title' => __('settings.toggle_properties_module'), 'desc' => __('settings.toggle_properties_module_desc'), 'icon' => 'bi-buildings'],
                'land_survey_module' => ['title' => __('settings.toggle_land_survey_module'), 'desc' => __('settings.toggle_land_survey_module_desc'), 'icon' => 'bi-compass'],
            ],
        ],
        'content' => [
            'title' => __('settings.group_content'),
            'icon' => 'bi-newspaper',
            'items' => [
                'blog_module' => ['title' => __('settings.toggle_blog_module'), 'desc' => __('settings.toggle_blog_module_desc'), 'icon' => 'bi-newspaper'],
                'testimonials_module' => ['title' => __('settings.toggle_testimonials_module'), 'desc' => __('settings.toggle_testimonials_module_desc'), 'icon' => 'bi-star-fill'],
            ],
        ],
    ];
    $enabledCount = count(array_filter($featureToggles));
@endphp

<form action="{{ route('settings.toggles') }}" method="POST">
    @csrf
    <div class="card rounded-4 overflow-hidden">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="min-w-0">
                <h5 class="brand-font mb-0"><i class="bi bi-toggles text-primary me-1.5"></i>{{ __('settings.toggles_title') }}</h5>
                <small class="text-muted fw-normal">{{ __('settings.toggles_subtitle') }}</small>
            </div>
            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">{{ __('settings.toggles_enabled_count', ['enabled' => $enabledCount, 'total' => count($featureToggles)]) }}</span>
        </div>
        <div class="card-body p-3 p-md-4">
            @foreach($toggleGroups as $groupKey => $group)
                <div class="{{ $loop->last ? '' : 'mb-4' }}">
                    <h6 class="fw-bold text-uppercase small text-muted tracking-wider mb-2"><i class="bi {{ $group['icon'] }} me-1"></i>{{ $group['title'] }}</h6>
                    <div class="list-group shadow-xs">
                        @foreach($group['items'] as $key => $meta)
                            <div class="list-group-item py-3">
                                <div class="form-check form-switch d-flex align-items-start gap-3 ps-0 m-0">
                                    <span class="btn-icon bg-light text-primary flex-shrink-0" aria-hidden="true"><i class="bi {{ $meta['icon'] }}"></i></span>
                                    <label class="form-check-label flex-grow-1 min-w-0 cursor-pointer" for="toggle_{{ $key }}">
                                        <span class="fw-bold text-dark d-block">{{ $meta['title'] }}</span>
                                        <span class="text-muted small d-block">{{ $meta['desc'] }}</span>
                                    </label>
                                    <input class="form-check-input ms-0 flex-shrink-0 fs-5 m-0" type="checkbox" role="switch" name="{{ $key }}" value="1" id="toggle_{{ $key }}" @checked($featureToggles[$key] ?? false)>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
        <div class="card-footer bg-white border-top-0 pt-0 pb-3 px-3 px-md-4">
            <div class="d-grid d-sm-flex justify-content-sm-end">
                <button type="submit" class="btn btn-primary fw-bold rounded-pill px-4 py-2 shadow-sm">
                    <i class="bi bi-save me-1"></i>{{ __('settings.save_toggles') }}
                </button>
            </div>
        </div>
    </div>
</form>
