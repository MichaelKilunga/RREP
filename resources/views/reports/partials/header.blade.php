{{--
    Shared report header: title, period picker, CSV / print / back actions.
    Expects: $title, $icon, $routeName; optional: $period (from ReportController::period), $csv (bool).
--}}
@php
    $csv = $csv ?? true;
    $periodQuery = isset($period) ? array_filter(['period' => $period['key'], 'from' => request('from'), 'to' => request('to')]) : [];
    $periodOptions = [
        'this_month' => __('ops.period_this_month'),
        'last_month' => __('ops.period_last_month'),
        'last_30' => __('ops.period_last_30'),
        'last_90' => __('ops.period_last_90'),
        'this_year' => __('ops.period_this_year'),
        'last_year' => __('ops.period_last_year'),
        'all' => __('ops.period_all'),
        'custom' => __('ops.period_custom'),
    ];
@endphp

<div class="page-header">
    <div class="min-w-0">
        <h1 class="brand-font mb-1"><i class="bi {{ $icon }} text-primary me-2 d-print-none"></i>{{ $title }}</h1>
        <p class="text-muted small mb-0">
            @isset($period)<span class="fw-semibold text-body"><i class="bi bi-calendar3 me-1"></i>{{ $period['label'] }}</span> · @endisset
            {{ __('ops.report_generated_at', ['date' => now()->format('d M Y, H:i')]) }} EAT
        </p>
    </div>
    <div class="page-actions d-print-none">
        <a href="{{ route('reports.index') }}" class="btn btn-light border"><i class="bi bi-arrow-left me-1"></i>{{ __('ops.reports_center') }}</a>
        @if($csv)
            @can('export_reports')
                <a href="{{ route($routeName, $periodQuery + ['export' => 'csv']) }}" class="btn btn-outline-primary"><i class="bi bi-filetype-csv me-1"></i>CSV</a>
            @endcan
        @endif
        <button type="button" onclick="window.print()" class="btn btn-dark"><i class="bi bi-printer me-1"></i>{{ __('ops.print') }}</button>
    </div>
</div>

@isset($period)
    <form method="GET" action="{{ route($routeName) }}" class="card card-body py-2 px-3 mb-4 d-print-none" data-period-form>
        <div class="d-flex flex-wrap align-items-end gap-2">
            <div>
                <label for="periodSelect" class="form-label small text-muted mb-1">{{ __('ops.period') }}</label>
                <select name="period" id="periodSelect" class="form-select form-select-sm" style="min-width: 190px;" data-period-select>
                    @foreach($periodOptions as $value => $label)
                        <option value="{{ $value }}" @selected($period['key'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="d-flex flex-wrap align-items-end gap-2 {{ $period['key'] === 'custom' ? '' : 'd-none' }}" data-period-custom>
                <div>
                    <label for="periodFrom" class="form-label small text-muted mb-1">{{ __('ops.date_from') }}</label>
                    <input type="date" name="from" id="periodFrom" class="form-control form-control-sm" value="{{ $period['from']->toDateString() }}">
                </div>
                <div>
                    <label for="periodTo" class="form-label small text-muted mb-1">{{ __('ops.date_to') }}</label>
                    <input type="date" name="to" id="periodTo" class="form-control form-control-sm" value="{{ $period['to']->toDateString() }}">
                </div>
                <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-funnel me-1"></i>{{ __('ops.apply') }}</button>
            </div>
            <div class="ms-auto d-none d-md-flex flex-wrap gap-1" role="group" aria-label="{{ __('ops.period') }}">
                @foreach(['this_month', 'last_30', 'this_year', 'all'] as $quick)
                    <a href="{{ route($routeName, ['period' => $quick]) }}" class="btn btn-sm {{ $period['key'] === $quick ? 'btn-primary' : 'btn-light border' }}">{{ $periodOptions[$quick] }}</a>
                @endforeach
            </div>
        </div>
    </form>
@endisset
