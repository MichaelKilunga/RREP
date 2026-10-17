@php
    $isProduction = is_production_mode();
    $demoCards = [
        ['label' => __('settings.demo_properties'), 'value' => $demoStats['properties'], 'icon' => 'bi-houses', 'accent' => 'var(--rrep-primary)'],
        ['label' => __('settings.demo_customers'), 'value' => $demoStats['customers'], 'icon' => 'bi-people', 'accent' => 'var(--rrep-info)'],
        ['label' => __('settings.demo_leads'), 'value' => $demoStats['leads'], 'icon' => 'bi-funnel', 'accent' => 'var(--rrep-warning)'],
        ['label' => __('settings.demo_deals'), 'value' => $demoStats['deals'], 'icon' => 'bi-briefcase', 'accent' => 'var(--rrep-success)'],
        ['label' => __('settings.demo_invoices'), 'value' => $demoStats['invoices'], 'icon' => 'bi-receipt', 'accent' => 'var(--rrep-secondary)'],
        ['label' => __('settings.demo_logins'), 'value' => $demoStats['demo_users'], 'icon' => 'bi-person-badge', 'accent' => 'var(--rrep-danger)'],
    ];
@endphp

<div class="row g-3 g-lg-4 mb-4">
    {{-- Production (live) --}}
    <div class="col-12 col-lg-6">
        <div class="card h-100 rounded-4 {{ $isProduction ? 'border-success border-2' : '' }}">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="d-flex align-items-center gap-2 min-w-0">
                    <span class="badge bg-success rounded-pill">{{ __('settings.live') }}</span>
                    <h5 class="brand-font mb-0 text-success">{{ __('settings.production_environment') }}</h5>
                </div>
                @if($isProduction)
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill"><i class="bi bi-check2-circle me-1"></i>{{ __('settings.active_mode') }}</span>
                @endif
            </div>
            <div class="card-body p-3 p-md-4 d-flex flex-column">
                <p class="text-muted small mb-3">{{ __('settings.production_intro') }}</p>

                <div class="bg-light rounded-3 p-3 mb-3 small border">
                    <h6 class="fw-bold small text-dark mb-2"><i class="bi bi-shield-shaded text-success me-1"></i>{{ __('settings.production_enforcements') }}</h6>
                    <ul class="list-unstyled mb-0 d-flex flex-column gap-2 text-muted">
                        <li class="d-flex"><i class="bi bi-check-circle-fill text-success me-2"></i><span><strong>{{ __('settings.quick_login') }}:</strong> {{ __('settings.enforce_quick_login') }}</span></li>
                        <li class="d-flex"><i class="bi bi-check-circle-fill text-success me-2"></i><span><strong>{{ __('settings.debug') }}:</strong> {{ __('settings.enforce_debug') }} (<code>APP_DEBUG=false</code>)</span></li>
                        <li class="d-flex"><i class="bi bi-check-circle-fill text-success me-2"></i><span><strong>{{ __('settings.demo_data') }}:</strong> {{ __('settings.enforce_demo_data') }}</span></li>
                        <li class="d-flex"><i class="bi bi-check-circle-fill text-success me-2"></i><span><strong>{{ __('settings.core_safeguards') }}:</strong> {{ __('settings.enforce_safeguards') }}</span></li>
                    </ul>
                </div>

                <form action="{{ route('settings.environment') }}" method="POST" class="mt-auto" data-confirm="{{ __('settings.confirm_go_live') }}" data-confirm-title="{{ __('settings.go_live_title') }}" data-confirm-button="{{ __('settings.yes_go_live') }}" data-confirm-color="#16a34a">
                    @csrf
                    <input type="hidden" name="environment" value="production">

                    <div class="form-check form-switch mb-3 p-2.5 ps-5 bg-light rounded-3 border">
                        <input class="form-check-input" type="checkbox" name="purge_demo_data" value="1" id="purgeDemoCheck" checked>
                        <label class="form-check-label small fw-semibold" for="purgeDemoCheck">{{ __('settings.purge_demo_on_go_live') }}</label>
                        <div class="form-text">{{ __('settings.purge_demo_on_go_live_help') }}</div>
                    </div>

                    <div class="d-grid">
                        @if($isProduction)
                            <button type="submit" class="btn btn-outline-success fw-semibold rounded-pill py-2.5">
                                <i class="bi bi-arrow-repeat me-1"></i>{{ __('settings.reapply_production') }}
                            </button>
                        @else
                            <button type="submit" class="btn btn-success fw-bold rounded-pill py-2.5 shadow-sm">
                                <i class="bi bi-cloud-arrow-up-fill me-1"></i>{{ __('settings.go_live') }}
                            </button>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Local (development) --}}
    <div class="col-12 col-lg-6">
        <div class="card h-100 rounded-4 {{ $isProduction ? '' : 'border-warning border-2' }}">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="d-flex align-items-center gap-2 min-w-0">
                    <span class="badge bg-warning text-dark rounded-pill">{{ __('settings.local') }}</span>
                    <h5 class="brand-font mb-0 text-warning-emphasis">{{ __('settings.development_environment') }}</h5>
                </div>
                @unless($isProduction)
                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill"><i class="bi bi-check2-circle me-1"></i>{{ __('settings.active_mode') }}</span>
                @endunless
            </div>
            <div class="card-body p-3 p-md-4 d-flex flex-column">
                <p class="text-muted small mb-3">{{ __('settings.local_intro') }}</p>

                <div class="bg-light rounded-3 p-3 mb-3 small border">
                    <h6 class="fw-bold small text-dark mb-2"><i class="bi bi-code-slash text-warning me-1"></i>{{ __('settings.development_capabilities') }}</h6>
                    <ul class="list-unstyled mb-0 d-flex flex-column gap-2 text-muted">
                        <li class="d-flex"><i class="bi bi-check-circle-fill text-warning me-2"></i><span><strong>{{ __('settings.quick_login') }}:</strong> {{ __('settings.capability_quick_login') }} <code>/login</code></span></li>
                        <li class="d-flex"><i class="bi bi-check-circle-fill text-warning me-2"></i><span><strong>{{ __('settings.debug') }}:</strong> {{ __('settings.capability_debug') }} (<code>APP_DEBUG=true</code>)</span></li>
                        <li class="d-flex"><i class="bi bi-check-circle-fill text-warning me-2"></i><span><strong>{{ __('settings.demo_data') }}:</strong> {{ __('settings.capability_demo_data') }}</span></li>
                    </ul>
                </div>

                <form action="{{ route('settings.environment') }}" method="POST" class="mt-auto" data-confirm="{{ __('settings.confirm_switch_local') }}" data-confirm-title="{{ __('settings.switch_local_title') }}" data-confirm-button="{{ __('settings.yes_switch_local') }}" data-confirm-color="#d97706">
                    @csrf
                    <input type="hidden" name="environment" value="local">

                    <div class="form-check form-switch mb-3 p-2.5 ps-5 bg-light rounded-3 border">
                        <input class="form-check-input" type="checkbox" name="reseed_demo_data" value="1" id="reseedDemoCheck">
                        <label class="form-check-label small fw-semibold" for="reseedDemoCheck">{{ __('settings.reseed_on_switch') }}</label>
                        <div class="form-text">{{ __('settings.reseed_on_switch_help') }}</div>
                    </div>

                    <div class="d-grid">
                        @if($isProduction)
                            <button type="submit" class="btn btn-warning text-dark fw-bold rounded-pill py-2.5 shadow-sm">
                                <i class="bi bi-laptop me-1"></i>{{ __('settings.switch_to_local') }}
                            </button>
                        @else
                            <button type="submit" class="btn btn-outline-warning text-dark fw-semibold rounded-pill py-2.5">
                                <i class="bi bi-arrow-repeat me-1"></i>{{ __('settings.reapply_local') }}
                            </button>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Seeded demo data --}}
<div class="card rounded-4 overflow-hidden mb-4">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div class="min-w-0">
            <h5 class="brand-font mb-0 text-dark">{{ __('settings.demo_data_title') }}</h5>
            <small class="text-muted fw-normal">{{ __('settings.demo_data_subtitle') }}</small>
        </div>
        <span class="badge {{ $demoStats['total'] > 0 ? 'bg-danger-subtle text-danger border border-danger-subtle' : 'bg-success-subtle text-success border border-success-subtle' }} rounded-pill fw-semibold">
            <i class="bi bi-database me-1"></i>{{ trans_choice('settings.demo_items_count', $demoStats['total'], ['count' => $demoStats['total']]) }}
        </span>
    </div>
    <div class="card-body p-3 p-md-4">
        <div class="row g-3 mb-4">
            @foreach($demoCards as $card)
                <div class="col-6 col-md-4 col-xl-2">
                    <div class="kpi-card" style="--kpi-accent: {{ $card['accent'] }};">
                        <div class="kpi-label">{{ $card['label'] }}</div>
                        <div class="kpi-value">{{ $card['value'] }}</div>
                        <span class="kpi-icon"><i class="bi {{ $card['icon'] }}"></i></span>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center pt-3 border-top gap-3">
            <p class="small text-muted mb-0">
                <i class="bi bi-shield-check text-success me-1"></i><strong>{{ __('settings.safety_lock') }}:</strong>
                {{ __('settings.safety_lock_help', ['email' => auth()->user()?->email ?? 'admin']) }}
            </p>
            <div class="d-flex flex-column flex-sm-row gap-2 flex-shrink-0">
                @if($demoStats['total'] > 0)
                    <form action="{{ route('settings.purge_demo_data') }}" method="POST" class="d-grid" data-confirm="{{ __('settings.confirm_purge') }}" data-confirm-title="{{ __('settings.purge_title') }}" data-confirm-button="{{ __('settings.yes_purge') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger rounded-pill fw-semibold px-3">
                            <i class="bi bi-trash3-fill me-1"></i>{{ __('settings.purge_now') }}
                        </button>
                    </form>
                @endif
                @unless($isProduction)
                    <form action="{{ route('settings.seed_demo_data') }}" method="POST" class="d-grid" data-confirm="{{ __('settings.confirm_seed') }}" data-confirm-title="{{ __('settings.seed_title') }}" data-confirm-button="{{ __('settings.yes_seed') }}" data-confirm-icon="info" data-confirm-color="#0f52ba">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary rounded-pill fw-semibold px-3">
                            <i class="bi bi-database-fill-add me-1"></i>{{ __('settings.reseed_now') }}
                        </button>
                    </form>
                @endunless
            </div>
        </div>
    </div>
</div>
