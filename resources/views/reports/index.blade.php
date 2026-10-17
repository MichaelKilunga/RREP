@extends('layouts.app')

@section('title', __('app.reports'))

@section('content')
@php
    $reportCards = [
        ['route' => 'reports.properties', 'icon' => 'bi-building', 'tone' => 'primary', 'title' => __('ops.report_property_title'), 'text' => __('ops.report_property_text')],
        ['route' => 'reports.sales', 'icon' => 'bi-cash-stack', 'tone' => 'success', 'title' => __('ops.report_sales_title'), 'text' => __('ops.report_sales_text')],
        ['route' => 'reports.agents', 'icon' => 'bi-award', 'tone' => 'warning', 'title' => __('ops.report_agents_title'), 'text' => __('ops.report_agents_text')],
        ['route' => 'reports.rent_roll', 'icon' => 'bi-key', 'tone' => 'info', 'title' => __('ops.report_rent_title'), 'text' => __('ops.report_rent_text')],
        ['route' => 'reports.survey', 'icon' => 'bi-geo-alt', 'tone' => 'secondary', 'title' => __('ops.report_survey_title'), 'text' => __('ops.report_survey_text')],
        ['route' => 'reports.leads', 'icon' => 'bi-funnel', 'tone' => 'dark', 'title' => __('ops.report_leads_title'), 'text' => __('ops.report_leads_text')],
    ];
@endphp

<div class="page-header">
    <div class="min-w-0">
        <h1 class="brand-font mb-1"><i class="bi bi-file-earmark-bar-graph text-primary me-2"></i>{{ __('ops.reports_title') }}</h1>
        <p class="text-muted small mb-0">{{ __('ops.reports_subtitle') }}</p>
    </div>
</div>

<div class="row g-3">
    @foreach($reportCards as $card)
        <div class="col-12 col-sm-6 col-xl-4">
            <div class="card h-100 p-3 p-md-4 border shadow-xs hover-shadow transition">
                <div class="touch-target rounded-3 bg-{{ $card['tone'] }}-subtle text-{{ $card['tone'] }} fs-4 mb-3 align-self-start">
                    <i class="bi {{ $card['icon'] }}"></i>
                </div>
                <h2 class="brand-font h5 mb-2">{{ $card['title'] }}</h2>
                <p class="text-muted small flex-grow-1 mb-3">{{ $card['text'] }}</p>
                <a href="{{ route($card['route']) }}" class="btn btn-outline-{{ $card['tone'] }} {{ $card['tone'] === 'warning' ? 'text-dark' : '' }} fw-bold stretched-link">{{ __('ops.generate_report') }} <i class="bi bi-arrow-right ms-1"></i></a>
            </div>
        </div>
    @endforeach
</div>
@endsection
