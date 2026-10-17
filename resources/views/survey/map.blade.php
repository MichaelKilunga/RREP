@extends('layouts.app')

@section('title', __('app.gis_map_viewer'))

@section('styles')
<style>
    #fullGisMap { width: 100%; height: calc(100vh - 220px); min-height: 320px; }
    @media (max-width: 767.98px) {
        #fullGisMap { height: calc(100dvh - var(--rrep-topbar-height) - var(--rrep-bottom-nav-height) - var(--rrep-safe-bottom) - 7.5rem); }
    }
</style>
@endsection

@section('content')
<div class="page-header mb-3">
    <div class="min-w-0">
        <h1 class="brand-font mb-0">{{ __('app.gis_map_viewer') }}</h1>
        <p class="text-muted small mb-0">{{ __('ops.gis_map_subtitle', ['count' => $projects->count()]) }}</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('survey.index') }}" class="btn btn-light border"><i class="bi bi-list me-1"></i> {{ __('app.survey_projects') }}</a>
    </div>
</div>

<div class="card overflow-hidden shadow-sm">
    @if($projects->isEmpty())
        <div class="empty-state">
            <div class="empty-icon"><i class="bi bi-map"></i></div>
            <h6>{{ __('ops.no_surveys') }}</h6>
            <p class="small mb-3">{{ __('ops.no_surveys_map_hint') }}</p>
            <a href="{{ route('survey.index') }}" class="btn btn-primary">{{ __('ops.new_survey_project') }}</a>
        </div>
    @endif
    <div id="fullGisMap" role="region" aria-label="{{ __('app.gis_map_viewer') }}"></div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const geoData = @json($allGeoJson);
    const labels = {
        code: @json(__('ops.code')),
        area: @json(__('ops.area_size')),
        status: @json(__('app.status'))
    };
    const map = L.map('fullGisMap', { tap: true }).setView([-6.7725, 39.2486], 7);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    const accent = getComputedStyle(document.documentElement).getPropertyValue('--rrep-accent').trim() || '#10b981';

    function esc(value) {
        return String(value ?? '').replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    if (geoData && geoData.features && geoData.features.length > 0) {
        const geoLayer = L.geoJSON(geoData, {
            style: { color: accent, weight: 3, fillColor: accent, fillOpacity: 0.25 },
            onEachFeature: function (feature, layer) {
                if (feature.properties) {
                    layer.bindPopup(
                        '<strong>' + esc(feature.properties.project_name) + '</strong><br>' +
                        labels.code + ': ' + esc(feature.properties.project_code) + '<br>' +
                        labels.area + ': ' + esc(feature.properties.total_area) + '<br>' +
                        labels.status + ': <span class="badge bg-success">' + esc(feature.properties.status) + '</span>'
                    );
                }
            }
        }).addTo(map);
        map.fitBounds(geoLayer.getBounds(), { padding: [20, 20] });
    }

    window.addEventListener('resize', function () { map.invalidateSize(); });
});
</script>
@endsection
