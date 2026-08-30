@extends('layouts.app')

@section('title', __('app.gis_map_viewer'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h3 class="brand-font mb-0">{{ __('app.gis_map_viewer') }}</h3>
        <p class="text-muted small mb-0">Cadastral parcel spatial visualizer across all administrative branches</p>
    </div>
    <a href="{{ route('survey.index') }}" class="btn btn-light border btn-sm"><i class="bi bi-list me-1"></i> Survey Projects</a>
</div>

<div class="card overflow-hidden shadow-sm" style="height: calc(100vh - 200px);">
    <div id="fullGisMap" style="height: 100%; width: 100%;"></div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const geoData = @json($allGeoJson);
    const map = L.map('fullGisMap').setView([-6.7725, 39.2486], 7);

    // Streets layer
    const streets = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    if (geoData && geoData.features && geoData.features.length > 0) {
        const geoLayer = L.geoJSON(geoData, {
            style: {
                color: '#00a86b',
                weight: 3,
                fillColor: '#00a86b',
                fillOpacity: 0.25
            },
            onEachFeature: function (feature, layer) {
                if (feature.properties) {
                    layer.bindPopup(`
                        <strong>${feature.properties.project_name}</strong><br>
                        Code: ${feature.properties.project_code}<br>
                        Area: ${feature.properties.total_area}<br>
                        Status: <span class="badge bg-success">${feature.properties.status}</span>
                    `);
                }
            }
        }).addTo(map);
        map.fitBounds(geoLayer.getBounds());
    }
});
</script>
@endsection
