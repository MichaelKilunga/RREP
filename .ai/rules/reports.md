---
paths:
  - 'resources/views/reports/**'
---

# Reports

## Reports share a period picker, chart kit and gated CSV
New reports take their window from ReportController::period($request) (presets + custom from/to) and bucket trends with series(). Views start with @include('reports.partials.header', [title, icon, routeName]) and include reports.partials.chart-kit in @section('scripts'), drawing charts via ReportCharts.bars() (fixed palette order, 24px bars, legend only for 2+ series, no doughnuts/rainbow). CSV goes through exportCsv(), which enforces export_reports. Add every new report route to PageSmokeTest::staffRoutes.
