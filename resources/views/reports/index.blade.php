@extends('layouts.app')

@section('title', __('app.reports'))

@section('content')
@php
    $propertiesOn = is_module_enabled('properties_module', false);
    $surveyOn = is_module_enabled('land_survey_module', false);
    $dealsOn = is_module_enabled('deals_module', false);
    $user = auth()->user();

    $groups = [
        [
            'title' => __('ops.report_group_sales'),
            'cards' => [
                ['route' => 'reports.projects', 'icon' => 'bi-map', 'tone' => 'primary', 'title' => __('ops.report_projects_title'), 'text' => __('ops.report_projects_text'), 'metric' => number_format($headlines['projects']), 'metric_label' => __('app.plots_available')],
                ['route' => 'reports.plot_sales', 'icon' => 'bi-bookmark-check', 'tone' => 'success', 'title' => __('ops.report_plot_sales_title'), 'text' => __('ops.report_plot_sales_text'), 'metric' => number_format($headlines['plot_sales']), 'metric_label' => __('ops.plots_sold_this_month')],
                $dealsOn ? ['route' => 'reports.sales', 'icon' => 'bi-cash-stack', 'tone' => 'success', 'title' => __('ops.report_sales_title'), 'text' => __('ops.report_sales_text')] : null,
                $dealsOn ? ['route' => 'reports.agents', 'icon' => 'bi-award', 'tone' => 'warning', 'title' => __('ops.report_agents_title'), 'text' => __('ops.report_agents_text')] : null,
            ],
        ],
        [
            'title' => __('ops.report_group_customers'),
            'cards' => [
                ['route' => 'reports.requests', 'icon' => 'bi-inboxes', 'tone' => 'info', 'title' => __('ops.report_requests_title'), 'text' => __('ops.report_requests_text'), 'metric' => number_format($headlines['requests']), 'metric_label' => __('ops.this_month')],
                ['route' => 'reports.leads', 'icon' => 'bi-funnel', 'tone' => 'dark', 'title' => __('ops.report_leads_title'), 'text' => __('ops.report_leads_text'), 'metric' => number_format($headlines['leads']), 'metric_label' => __('ops.new_leads_this_month')],
                ['route' => 'reports.customers', 'icon' => 'bi-people', 'tone' => 'primary', 'title' => __('ops.report_customers_title'), 'text' => __('ops.report_customers_text'), 'metric' => number_format($headlines['customers']), 'metric_label' => __('ops.new_customers_this_month')],
            ],
        ],
        [
            'title' => __('ops.report_group_money'),
            'cards' => [
                $user->can('view_financial_reports') ? ['route' => 'reports.finance', 'icon' => 'bi-cash-coin', 'tone' => 'success', 'title' => __('ops.report_finance_title'), 'text' => __('ops.report_finance_text'), 'metric' => format_money_short($headlines['finance']), 'metric_label' => __('ops.collected_month')] : null,
                $user->can('view_sms_logs') ? ['route' => 'reports.sms', 'icon' => 'bi-chat-dots', 'tone' => 'info', 'title' => __('ops.report_sms_title'), 'text' => __('ops.report_sms_text'), 'metric' => number_format($headlines['sms']), 'metric_label' => __('ops.sms_sent_this_month')] : null,
                $propertiesOn ? ['route' => 'reports.properties', 'icon' => 'bi-building', 'tone' => 'primary', 'title' => __('ops.report_property_title'), 'text' => __('ops.report_property_text')] : null,
                $propertiesOn ? ['route' => 'reports.rent_roll', 'icon' => 'bi-key', 'tone' => 'info', 'title' => __('ops.report_rent_title'), 'text' => __('ops.report_rent_text')] : null,
                $surveyOn ? ['route' => 'reports.survey', 'icon' => 'bi-geo-alt', 'tone' => 'secondary', 'title' => __('ops.report_survey_title'), 'text' => __('ops.report_survey_text')] : null,
            ],
        ],
    ];
@endphp

<div class="page-header">
    <div class="min-w-0">
        <h1 class="brand-font mb-1"><i class="bi bi-file-earmark-bar-graph text-primary me-2"></i>{{ __('ops.reports_title') }}</h1>
        <p class="text-muted small mb-0">{{ __('ops.reports_subtitle_v2') }}</p>
    </div>
</div>

@foreach($groups as $group)
    @php $cards = array_values(array_filter($group['cards'])); @endphp
    @continue(empty($cards))
    <h2 class="h6 text-uppercase text-muted fw-bold small mb-3 {{ $loop->first ? '' : 'mt-4' }}" style="letter-spacing: .06em;">{{ $group['title'] }}</h2>
    <div class="row g-3">
        @foreach($cards as $card)
            <div class="col-12 col-sm-6 col-xl-4">
                <div class="card h-100 p-3 p-md-4 border shadow-xs hover-shadow transition">
                    <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                        <div class="touch-target rounded-3 bg-{{ $card['tone'] }}-subtle text-{{ $card['tone'] }} fs-4">
                            <i class="bi {{ $card['icon'] }}"></i>
                        </div>
                        @isset($card['metric'])
                            <div class="text-end">
                                <div class="brand-font fw-bold fs-4 lh-1" style="font-variant-numeric: tabular-nums;">{{ $card['metric'] }}</div>
                                <small class="text-muted">{{ $card['metric_label'] }}</small>
                            </div>
                        @endisset
                    </div>
                    <h3 class="brand-font h5 mb-2">{{ $card['title'] }}</h3>
                    <p class="text-muted small flex-grow-1 mb-3">{{ $card['text'] }}</p>
                    <a href="{{ route($card['route']) }}" class="btn btn-outline-{{ $card['tone'] }} {{ $card['tone'] === 'warning' ? 'text-dark' : '' }} fw-bold stretched-link">{{ __('ops.open_report') }} <i class="bi bi-arrow-right ms-1"></i></a>
                </div>
            </div>
        @endforeach
    </div>
@endforeach
@endsection
