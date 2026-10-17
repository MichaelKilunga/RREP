@extends('layouts.app')

@section('title', __('portal.client_title'))

@section('content')
@php
    $whatsappNumber = preg_replace('/\D+/', '', (string) setting('contact_whatsapp', '255784100200'));
    $officePhone = setting('contact_phone', '+255 784 100 200');
    $activeReservations = $reservations->where('status', 'Active')->count();
    $unpaidInvoices = $invoices->filter(fn ($inv) => $inv->status !== 'Paid' && (float) $inv->balance_due > 0);
    $outstandingBalance = $unpaidInvoices->sum('balance_due');
    $activeVouchers = $loyaltyRewards->where('status', 'active')->count();
    $paymentMethods = tz_payment_methods();
    $mobileMoneyNames = collect(['M-Pesa', 'Tigo Pesa', 'Airtel Money', 'HaloPesa'])->map(fn ($key) => $paymentMethods[$key] ?? $key);
    $bankName = setting('bank_name') ?: __('portal.placeholder_bank_name');
    $bankAccount = setting('bank_account') ?: __('portal.placeholder_bank_account');
    $mobileMoneyNumber = setting('mobile_money_number');
    $supportMessage = __('portal.send_confirmation').' - '.$customer->full_name;
@endphp

<div class="page-header">
    <div class="min-w-0">
        <h3 class="brand-font"><i class="bi bi-person-workspace text-primary me-2"></i>{{ __('portal.client_title') }}</h3>
        <p class="text-muted small mb-0">{{ __('portal.welcome_back') }}, <strong>{{ $customer->full_name }}</strong>. {{ __('portal.client_subtitle') }}</p>
    </div>
    <div class="page-actions">
        <a href="https://wa.me/{{ $whatsappNumber }}?text={{ urlencode($supportMessage) }}" target="_blank" rel="noopener" class="btn btn-whatsapp">
            <i class="bi bi-whatsapp me-1"></i> {{ __('portal.support_whatsapp') }}
        </a>
        <a href="{{ route('public.land') }}" class="btn btn-primary fw-semibold">
            <i class="bi bi-compass me-1"></i> {{ __('portal.browse_plots') }}
        </a>
    </div>
</div>

<!-- KPI strip -->
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="kpi-card" style="--kpi-accent: #f59e0b;">
            <div class="kpi-label">{{ __('portal.kpi_loyalty_points') }}</div>
            <div class="kpi-value">{{ number_format($customer->loyalty_points ?? 0) }} <span class="fs-6 fw-normal text-muted">{{ __('portal.points_suffix') }}</span></div>
            <div class="small text-muted mt-1 text-truncate">{{ $customer->loyalty_tier ?? 'Bronze Member' }}</div>
            <span class="kpi-icon"><i class="bi bi-star-fill"></i></span>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card" style="--kpi-accent: var(--rrep-primary);">
            <div class="kpi-label">{{ __('portal.kpi_active_reservations') }}</div>
            <div class="kpi-value">{{ $activeReservations }}</div>
            <div class="small text-muted mt-1 text-truncate">{{ __('portal.tab_reservations') }}: {{ $reservations->count() }}</div>
            <span class="kpi-icon"><i class="bi bi-geo-alt-fill"></i></span>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card" style="--kpi-accent: #dc3545;">
            <div class="kpi-label">{{ __('portal.kpi_outstanding_invoices') }}</div>
            <div class="kpi-value">{{ format_money_short($outstandingBalance) }}</div>
            <div class="small text-muted mt-1 text-truncate">{{ trans_choice('portal.kpi_unpaid_count', $unpaidInvoices->count(), ['count' => $unpaidInvoices->count()]) }}</div>
            <span class="kpi-icon"><i class="bi bi-receipt"></i></span>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card" style="--kpi-accent: #198754;">
            <div class="kpi-label">{{ __('portal.tab_loyalty') }}</div>
            <div class="kpi-value">{{ $activeVouchers }}</div>
            <a href="#loyaltyTab" class="small mt-1 d-inline-block text-decoration-none" data-portal-tab="#loyalty-tab">{{ __('portal.view_vouchers') }} <i class="bi bi-arrow-right-short"></i></a>
            <span class="kpi-icon"><i class="bi bi-ticket-perforated"></i></span>
        </div>
    </div>
</div>

<!-- Tabs (horizontally scrollable on phones) -->
<ul class="nav nav-tabs flex-nowrap scroll-x gap-0 mb-4" id="clientTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active fw-semibold text-nowrap" id="reservations-tab" data-bs-toggle="tab" data-bs-target="#reservationsTab" type="button" role="tab">
            <i class="bi bi-house-check me-1"></i> {{ __('portal.tab_reservations') }} ({{ $reservations->count() }})
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-semibold text-nowrap" id="surveys-tab" data-bs-toggle="tab" data-bs-target="#surveysTab" type="button" role="tab">
            <i class="bi bi-compass me-1"></i> {{ __('portal.tab_surveys') }} ({{ $surveyRequests->count() }})
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-semibold text-nowrap" id="invoices-tab" data-bs-toggle="tab" data-bs-target="#invoicesTab" type="button" role="tab">
            <i class="bi bi-credit-card me-1"></i> {{ __('portal.tab_invoices') }} ({{ $invoices->count() }})
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-semibold text-nowrap" id="loyalty-tab" data-bs-toggle="tab" data-bs-target="#loyaltyTab" type="button" role="tab">
            <i class="bi bi-gift me-1"></i> {{ __('portal.tab_loyalty') }}
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-semibold text-nowrap" id="vault-tab" data-bs-toggle="tab" data-bs-target="#vaultTab" type="button" role="tab">
            <i class="bi bi-folder2-open me-1"></i> {{ __('portal.tab_documents') }} ({{ $libraryFiles->count() }})
        </button>
    </li>
</ul>

<div class="tab-content" id="clientTabContent">
    <!-- TAB 1: Reservations -->
    <div class="tab-pane fade show active" id="reservationsTab" role="tabpanel">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="brand-font mb-0">{{ __('portal.reservations_heading') }}</h5>
            </div>
            @if($reservations->isEmpty())
                <div class="empty-state">
                    <div class="empty-icon"><i class="bi bi-house-check"></i></div>
                    <h6>{{ __('portal.no_reservations') }}</h6>
                    <p class="small mb-3">{{ __('portal.no_reservations_hint') }}</p>
                    <a href="{{ route('public.land') }}" class="btn btn-primary"><i class="bi bi-compass me-1"></i> {{ __('portal.browse_plots') }}</a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover table-stack align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('portal.property_plot') }}</th>
                                <th>{{ __('portal.reservation_ref') }}</th>
                                <th>{{ __('portal.reservation_fee') }}</th>
                                <th>{{ __('portal.valid_until') }}</th>
                                <th>{{ __('app.status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reservations as $res)
                                <tr>
                                    <td class="stack-primary">
                                        <div class="fw-bold line-clamp-2">{{ $res->property?->title ?? __('portal.land_parcel') }}</div>
                                        <small class="text-muted">{{ collect([$res->property?->address, $res->property?->city])->filter()->implode(', ') }}</small>
                                    </td>
                                    <td class="fw-semibold text-primary font-monospace">{{ $res->reservation_number }}</td>
                                    <td class="fw-bold text-dark">{{ format_currency($res->reservation_fee) }}</td>
                                    <td>
                                        <div>{{ $res->reserved_until }}</div>
                                        <small class="text-muted">{{ __('portal.from') }}: {{ $res->reserved_from }}</small>
                                    </td>
                                    <td>
                                        @if($res->status === 'Active')
                                            <span class="badge bg-success">{{ __('portal.status_active_hold') }}</span>
                                        @elseif($res->status === 'Converted')
                                            <span class="badge bg-primary">{{ __('portal.status_purchased') }}</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $res->status }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <!-- TAB 2: Land Survey Requests -->
    <div class="tab-pane fade" id="surveysTab" role="tabpanel">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="brand-font mb-0">{{ __('portal.surveys_heading') }}</h5>
            </div>
            @if($surveyRequests->isEmpty())
                <div class="empty-state">
                    <div class="empty-icon"><i class="bi bi-compass"></i></div>
                    <h6>{{ __('portal.no_surveys') }}</h6>
                    <p class="small mb-3">{{ __('portal.no_surveys_hint') }}</p>
                    <a href="{{ route('public.services.land_survey') }}" class="btn btn-primary"><i class="bi bi-rulers me-1"></i> {{ __('portal.request_survey') }}</a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover table-stack align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('portal.survey_type') }}</th>
                                <th>{{ __('portal.project_code') }}</th>
                                <th>{{ __('app.location') }}</th>
                                <th>{{ __('portal.estimated_fee') }}</th>
                                <th>{{ __('portal.milestones') }}</th>
                                <th>{{ __('app.status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($surveyRequests as $srv)
                                <tr>
                                    <td class="stack-primary fw-bold">{{ $srv->survey_type ?? $srv->project_name }}</td>
                                    <td class="fw-semibold text-primary font-monospace">{{ $srv->project_code }}</td>
                                    <td>{{ $srv->location_name }}</td>
                                    <td>{{ format_currency($srv->estimated_cost) }}</td>
                                    <td><span class="badge bg-info-subtle text-info">{{ trans_choice('portal.milestones_logged', $srv->milestones->count(), ['count' => $srv->milestones->count()]) }}</span></td>
                                    <td>
                                        @if($srv->status === 'Completed')
                                            <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>{{ __('portal.status_completed') }}</span>
                                        @elseif($srv->status === 'Fieldwork')
                                            <span class="badge bg-warning text-dark"><i class="bi bi-gear me-1"></i>{{ __('portal.status_fieldwork') }}</span>
                                        @else
                                            <span class="badge bg-primary">{{ $srv->status }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <!-- TAB 3: Invoices & Payment Ledger -->
    <div class="tab-pane fade" id="invoicesTab" role="tabpanel">
        <div class="row g-3">
            <div class="col-12 col-lg-8">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="brand-font mb-0">{{ __('portal.invoices_heading') }}</h5>
                    </div>
                    @if($invoices->isEmpty())
                        <div class="empty-state">
                            <div class="empty-icon"><i class="bi bi-receipt"></i></div>
                            <h6>{{ __('portal.no_invoices') }}</h6>
                            <p class="small mb-0">{{ __('portal.no_invoices_hint') }}</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover table-stack align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ __('portal.invoice_no') }}</th>
                                        <th>{{ __('portal.issue_date') }}</th>
                                        <th>{{ __('portal.due_date') }}</th>
                                        <th>{{ __('portal.total_amount') }}</th>
                                        <th>{{ __('portal.paid_amount') }}</th>
                                        <th>{{ __('portal.balance_due') }}</th>
                                        <th>{{ __('app.status') }}</th>
                                        <th class="text-end">{{ __('app.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($invoices as $inv)
                                        @php
                                            $isPaid = (float) $inv->balance_due <= 0 || $inv->status === 'Paid';
                                            $isOverdue = ! $isPaid && $inv->due_date && \Illuminate\Support\Carbon::parse($inv->due_date)->isPast();
                                        @endphp
                                        <tr>
                                            <td class="stack-primary fw-bold text-primary font-monospace">{{ $inv->invoice_number }}</td>
                                            <td>{{ $inv->issue_date }}</td>
                                            <td>{{ $inv->due_date }}</td>
                                            <td class="fw-bold">{{ format_currency($inv->total_amount, $inv->currency) }}</td>
                                            <td class="text-success">{{ format_currency($inv->paid_amount, $inv->currency) }}</td>
                                            <td class="fw-bold {{ $isPaid ? 'text-success' : 'text-danger' }}">{{ format_currency($inv->balance_due, $inv->currency) }}</td>
                                            <td>
                                                @if($isPaid)
                                                    <span class="badge bg-success">{{ __('portal.status_paid') }}</span>
                                                @elseif($isOverdue)
                                                    <span class="badge bg-danger">{{ __('portal.status_overdue') }}</span>
                                                @elseif($inv->paid_amount > 0)
                                                    <span class="badge bg-warning text-dark">{{ __('portal.status_partial') }}</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ __('portal.status_unpaid') }}</span>
                                                @endif
                                            </td>
                                            <td class="stack-actions text-end">
                                                @unless($isPaid)
                                                    <a href="#howToPay" class="btn btn-sm btn-outline-primary" data-scroll-to="#howToPay">
                                                        <i class="bi bi-phone me-1"></i> {{ __('portal.pay_now') }}
                                                    </a>
                                                @endunless
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <!-- How to pay (mobile money + bank) -->
            <div class="col-12 col-lg-4">
                <div class="card shadow-sm border-0 h-100" id="howToPay">
                    <div class="card-header bg-white py-3">
                        <h5 class="brand-font mb-0"><i class="bi bi-phone text-primary me-2"></i>{{ __('portal.how_to_pay') }}</h5>
                    </div>
                    <div class="card-body">
                        <p class="small text-muted">{{ __('portal.how_to_pay_intro') }}</p>

                        <div class="mb-3">
                            <div class="small fw-bold text-uppercase text-muted mb-1">{{ __('portal.mobile_money') }}</div>
                            <div class="d-flex flex-wrap gap-1 mb-2">
                                @foreach($mobileMoneyNames as $name)
                                    <span class="badge bg-light text-dark border">{{ $name }}</span>
                                @endforeach
                            </div>
                            <div class="d-flex justify-content-between gap-2 small">
                                <span class="text-muted">{{ __('portal.mobile_money_number') }}</span>
                                <span class="fw-bold text-end">{{ $mobileMoneyNumber ? format_phone_tz($mobileMoneyNumber) : __('portal.placeholder_mobile_money') }}</span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="small fw-bold text-uppercase text-muted mb-1">{{ __('portal.bank_transfer') }}</div>
                            <div class="d-flex justify-content-between gap-2 small">
                                <span class="text-muted">{{ __('portal.bank_name') }}</span>
                                <span class="fw-bold text-end">{{ $bankName }}</span>
                            </div>
                            <div class="d-flex justify-content-between gap-2 small">
                                <span class="text-muted">{{ __('portal.bank_account') }}</span>
                                <span class="fw-bold text-end font-monospace">{{ $bankAccount }}</span>
                            </div>
                        </div>

                        <div class="mb-3 small">
                            <span class="text-muted">{{ __('portal.payment_reference') }}:</span>
                            <span class="fw-semibold">{{ __('portal.payment_reference_hint') }}</span>
                        </div>

                        <div class="d-grid">
                            <a href="https://wa.me/{{ $whatsappNumber }}?text={{ urlencode($supportMessage) }}" target="_blank" rel="noopener" class="btn btn-whatsapp">
                                <i class="bi bi-whatsapp me-1"></i> {{ __('portal.send_confirmation') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 4: Loyalty Vouchers -->
    <div class="tab-pane fade" id="loyaltyTab" role="tabpanel">
        <div class="row g-3">
            <div class="col-12 col-lg-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="brand-font mb-0">{{ __('portal.loyalty_heading') }}</h5>
                    </div>
                    <div class="card-body p-0">
                        @if($loyaltyRewards->isEmpty())
                            <div class="empty-state">
                                <div class="empty-icon"><i class="bi bi-gift"></i></div>
                                <h6>{{ __('portal.no_vouchers') }}</h6>
                                <p class="small mb-3">{{ __('portal.no_vouchers_hint') }}</p>
                                <a href="{{ route('public.land') }}" class="btn btn-outline-primary">{{ __('portal.browse_plots') }}</a>
                            </div>
                        @else
                            <div class="list-group list-group-flush">
                                @foreach($loyaltyRewards as $rew)
                                    <div class="list-group-item p-3">
                                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-1">
                                            <span class="badge bg-primary font-monospace">{{ $rew->reward_code }}</span>
                                            <span class="badge bg-success-subtle text-success fw-bold">{{ $rew->formatted_discount }}</span>
                                        </div>
                                        <div class="fw-semibold text-dark">{{ $rew->reward_name }}</div>
                                        <small class="text-muted">
                                            {{ __('portal.voucher_valid_until') }}: {{ $rew->expires_at?->format('d/m/Y') ?? __('portal.na') }} &bull;
                                            {{ __('app.status') }}: <span class="fw-bold text-capitalize">{{ $rew->status }}</span>
                                        </small>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="brand-font mb-0">{{ __('portal.points_ledger') }}</h5>
                    </div>
                    <div class="card-body p-0">
                        @if($loyaltyTransactions->isEmpty())
                            <div class="empty-state">
                                <div class="empty-icon"><i class="bi bi-star"></i></div>
                                <h6>{{ __('portal.no_points_activity') }}</h6>
                                <p class="small mb-0">{{ __('portal.no_vouchers_hint') }}</p>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-sm table-hover table-stack mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>{{ __('portal.activity') }}</th>
                                            <th>{{ __('portal.date') }}</th>
                                            <th class="text-end">{{ __('portal.points') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($loyaltyTransactions as $lt)
                                            <tr>
                                                <td class="stack-primary">{{ $lt->description }}</td>
                                                <td class="small text-muted">{{ $lt->created_at->format('d/m/Y') }}</td>
                                                <td class="text-end fw-bold {{ $lt->points >= 0 ? 'text-success' : 'text-danger' }}">
                                                    {{ $lt->points >= 0 ? '+'.$lt->points : $lt->points }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 5: Document Library -->
    <div class="tab-pane fade" id="vaultTab" role="tabpanel">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="brand-font mb-0">{{ __('portal.documents_heading') }}</h5>
                <small class="text-muted">{{ __('portal.documents_hint') }}</small>
            </div>
            <div class="card-body">
                @if($libraryFiles->isEmpty())
                    <div class="empty-state">
                        <div class="empty-icon"><i class="bi bi-folder2-open"></i></div>
                        <h6>{{ __('portal.no_documents') }}</h6>
                        <p class="small mb-3">{{ __('portal.no_documents_hint') }}</p>
                        <a href="https://wa.me/{{ $whatsappNumber }}" target="_blank" rel="noopener" class="btn btn-whatsapp"><i class="bi bi-whatsapp me-1"></i> {{ __('portal.support_whatsapp') }}</a>
                    </div>
                @else
                    <div class="row g-3">
                        @foreach($libraryFiles as $doc)
                            @php
                                $extension = strtolower(pathinfo((string) $doc->file_name, PATHINFO_EXTENSION));
                                $docIcon = match (true) {
                                    $extension === 'pdf' => 'bi-file-earmark-pdf text-danger',
                                    in_array($extension, ['jpg', 'jpeg', 'png', 'webp']) => 'bi-file-earmark-image text-info',
                                    in_array($extension, ['dwg', 'dxf', 'kml', 'shp']) => 'bi-map text-success',
                                    default => 'bi-file-earmark-text text-primary',
                                };
                            @endphp
                            <div class="col-12 col-sm-6 col-xl-4">
                                <div class="card h-100 border shadow-xs">
                                    <div class="card-body d-flex gap-3 align-items-start">
                                        <div class="touch-target rounded-3 bg-light flex-shrink-0 fs-4"><i class="bi {{ $docIcon }}"></i></div>
                                        <div class="min-w-0 flex-grow-1">
                                            <div class="fw-semibold text-dark text-truncate" title="{{ $doc->file_name }}">{{ $doc->file_name }}</div>
                                            <div class="small text-muted">
                                                <span class="badge bg-light text-dark border">{{ strtoupper(str_replace('_', ' ', (string) ($doc->category ?? $doc->collection_name))) }}</span>
                                                &bull; {{ round(($doc->file_size ?? 1024) / 1024, 1) }} KB
                                            </div>
                                            <div class="small text-muted">{{ __('portal.uploaded_on') }}: {{ $doc->created_at?->format('d/m/Y') }}</div>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-white border-top-0 pt-0">
                                        <a href="{{ $doc->url }}" target="_blank" rel="noopener" class="btn btn-outline-primary w-100" download>
                                            <i class="bi bi-download me-1"></i> {{ __('portal.download') }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Support strip -->
<div class="card border-0 shadow-sm mt-4">
    <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="min-w-0">
            <div class="fw-bold"><i class="bi bi-headset text-primary me-2"></i>{{ __('portal.need_help') }}</div>
            <div class="small text-muted">{{ __('portal.need_help_text') }} {{ __('portal.office_hours') }}</div>
        </div>
        <div class="d-flex flex-wrap gap-2 flex-grow-1 flex-md-grow-0">
            <a href="https://wa.me/{{ $whatsappNumber }}" target="_blank" rel="noopener" class="btn btn-whatsapp flex-grow-1"><i class="bi bi-whatsapp me-1"></i> WhatsApp</a>
            <a href="tel:{{ preg_replace('/\s+/', '', (string) $officePhone) }}" class="btn btn-outline-secondary flex-grow-1"><i class="bi bi-telephone me-1"></i> {{ format_phone_tz($officePhone) }}</a>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-portal-tab]').forEach(function (link) {
        link.addEventListener('click', function (event) {
            event.preventDefault();
            var trigger = document.querySelector(link.getAttribute('data-portal-tab'));
            if (trigger && window.bootstrap) {
                window.bootstrap.Tab.getOrCreateInstance(trigger).show();
            }
        });
    });

    document.querySelectorAll('[data-scroll-to]').forEach(function (link) {
        link.addEventListener('click', function (event) {
            event.preventDefault();
            var target = document.querySelector(link.getAttribute('data-scroll-to'));
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });
});
</script>
@endsection
