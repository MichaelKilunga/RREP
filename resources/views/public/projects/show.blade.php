@extends('layouts.public')

@section('title', $project->title . ' - Interactive Plot Map & Shapefile Details')

@section('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #projectPlotMap {
        width: 100%;
        height: calc(100vh - 120px);
        min-height: 550px;
        z-index: 1;
        border-radius: 0;
    }
    .plot-drawer {
        width: 380px;
        max-width: 90vw;
        border-left: 1px solid #e2e8f0;
        background: #ffffff;
        box-shadow: -4px 0 20px rgba(0,0,0,0.08);
    }
    .map-legend {
        position: absolute;
        bottom: 24px;
        left: 24px;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(8px);
        padding: 10px 16px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.12);
        z-index: 1000;
        font-size: 0.8rem;
        font-weight: 600;
    }
    .legend-box {
        display: inline-block;
        width: 14px;
        height: 14px;
        border-radius: 3px;
        margin-right: 6px;
        vertical-align: middle;
    }
</style>
@endsection

@section('content')
<!-- Top Action Bar (TAUSI Image 2 Header) -->
<div class="bg-white border-bottom py-2 px-3 d-flex align-items-center justify-content-between shadow-sm sticky-top" style="z-index: 1020;">
    <div class="d-flex align-items-center gap-3">
        <a href="{{ route('public.projects') }}" class="btn btn-outline-dark btn-sm rounded-pill px-3 fw-bold">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
        <div>
            <h5 class="fw-bold text-dark mb-0 fs-6">{{ $project->title }}</h5>
            <small class="text-muted"><i class="bi bi-geo-alt-fill text-danger me-1"></i>{{ $project->council ?? $project->location_name }}</small>
        </div>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span class="badge bg-success px-3 py-2 rounded-pill fw-bold">
            {{ $project->available_units }} Plots Available
        </span>
        <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold d-none d-md-inline-block" data-bs-toggle="modal" data-bs-target="#inquireProjectModal">
            <i class="bi bi-chat-left-text me-1"></i> Inquire Project
        </button>
    </div>
</div>

<!-- Interactive Map Container with Side Drawer (Exact TAUSI Image 2 Layout) -->
<div class="d-flex position-relative overflow-hidden">
    <!-- Map Container -->
    <div class="flex-grow-1 position-relative">
        <div id="projectPlotMap"></div>

        <!-- Map Legend (TAUSI Image 2 Bottom Left) -->
        <div class="map-legend">
            <div class="mb-1"><span class="legend-box" style="background: #ef4444;"></span> Sold</div>
            <div class="mb-1"><span class="legend-box" style="background: #38bdf8;"></span> Available</div>
            <div><span class="legend-box" style="background: #f59e0b;"></span> On Preview / Reserved</div>
        </div>
    </div>

    <!-- Right-Side Drawable Offcanvas Panel (Exact TAUSI Image 2 Plot Details Drawer) -->
    <div class="plot-drawer p-3 d-flex flex-column justify-content-between" id="plotDrawer">
        <div>
            <!-- Drawer Header -->
            <div class="d-flex align-items-center justify-content-between border-bottom pb-2 mb-3">
                <h5 class="fw-bold text-dark mb-0">Plot Details</h5>
                <span class="badge bg-light text-dark fw-bold" id="drawerPlotBadge">Select a plot</span>
            </div>

            <!-- Empty State -->
            <div id="emptyDrawerState" class="text-center py-5">
                <i class="bi bi-cursor-fill fs-1 text-primary opacity-50 d-block mb-2"></i>
                <h6 class="fw-bold text-dark">Click Any Plot Shape</h6>
                <p class="text-muted small">Click any plot polygon on the map to inspect location, price, lease details, and reserve directly.</p>
            </div>

            <!-- Plot Attributes List (TAUSI Image 2 exact keys) -->
            <div id="plotDetailsContent" class="d-none">
                <div class="table-responsive">
                    <table class="table table-sm table-borderless align-middle mb-0" style="font-size: 0.85rem;">
                        <tbody>
                            <tr>
                                <td class="text-muted fw-semibold py-1">Locality</td>
                                <td class="fw-bold text-dark py-1 text-end" id="attrLocality">-</td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-semibold py-1">Block</td>
                                <td class="fw-bold text-dark py-1 text-end" id="attrBlock">-</td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-semibold py-1">Lot Number</td>
                                <td class="fw-bold text-dark py-1 text-end" id="attrLotNo">-</td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-semibold py-1">Legal Area</td>
                                <td class="fw-bold text-danger py-1 text-end" id="attrArea">-</td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-semibold py-1">Unit Of Measure</td>
                                <td class="fw-bold text-dark py-1 text-end">Sqm</td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-semibold py-1">Lot Type</td>
                                <td class="fw-bold text-dark py-1 text-end">Plot</td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-semibold py-1">Region</td>
                                <td class="fw-bold text-dark py-1 text-end" id="attrRegion">-</td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-semibold py-1">District</td>
                                <td class="fw-bold text-dark py-1 text-end" id="attrDistrict">-</td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-semibold py-1">Council</td>
                                <td class="fw-bold text-dark py-1 text-end" id="attrCouncil">-</td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-semibold py-1">Plot Status</td>
                                <td class="text-end py-1"><span class="badge bg-success" id="attrStatus">Available</span></td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-semibold py-1">Lot Use</td>
                                <td class="fw-bold text-dark py-1 text-end" id="attrLandUse">-</td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-semibold py-1">Price</td>
                                <td class="fw-extrabold text-danger py-1 text-end fs-6" id="attrPrice">-</td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-semibold py-1">Application Fee</td>
                                <td class="fw-bold text-dark py-1 text-end" id="attrAppFee">20,000.00 TZS</td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-semibold py-1">First Installment</td>
                                <td class="fw-bold text-dark py-1 text-end" id="attrInstallment">-</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex gap-2 mt-3">
                    <a href="#" id="googleMapsBtn" target="_blank" class="btn btn-outline-secondary btn-sm flex-fill fw-bold rounded-pill">
                        <i class="bi bi-box-arrow-up-right me-1"></i> Google Maps
                    </a>
                    <button type="button" onclick="copyPlotGeoJson()" class="btn btn-outline-dark btn-sm flex-fill fw-bold rounded-pill">
                        <i class="bi bi-clipboard me-1"></i> Copy GeoJSON
                    </button>
                </div>
            </div>
        </div>

        <!-- Action CTA (TAUSI Image 2 Login to Buy button) -->
        <div id="plotActionContainer" class="pt-3 border-top d-none">
            <button type="button" id="inquirePlotBtn" class="btn btn-primary w-100 btn-lg fw-bold rounded-pill shadow-sm">
                Inquire / Reserve Plot
            </button>
        </div>
    </div>
</div>

<!-- Inquire Plot Modal -->
<div class="modal fade" id="inquirePlotModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header border-0 bg-light rounded-top-4">
                <h5 class="modal-header-title fw-bold text-dark mb-0">Inquire Land Plot</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form action="{{ route('public.inquire') }}" method="POST">
                    @csrf
                    <input type="hidden" name="project_id" value="{{ $project->id }}">
                    <div class="mb-2">
                        <label class="form-label fw-bold small">Project & Plot</label>
                        <input type="text" id="modalPlotTitle" name="plot_info" class="form-control bg-light" readonly>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-bold small">Your Full Name</label>
                        <input type="text" name="name" class="form-control" required placeholder="Enter full name">
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-bold small">Phone / WhatsApp</label>
                        <input type="tel" name="phone" class="form-control" required placeholder="e.g. 0784 100 200">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Payment Option</label>
                        <select name="preferred_contact_method" class="form-select">
                            <option value="WhatsApp">WhatsApp Contact</option>
                            <option value="Phone">Full Payment In TZS</option>
                            <option value="Email">50% Initial Installment</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 fw-bold py-2 rounded-pill">Submit Plot Reservation Request</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
let map, geojsonLayer, selectedFeature = null;
const rawGeoJson = {!! json_encode($geojsonCollection) !!};
const projectLat = {{ $project->latitude ?? -4.2185 }};
const projectLng = {{ $project->longitude ?? 35.7485 }};

document.addEventListener('DOMContentLoaded', function () {
    // Initialize Leaflet Map
    map = L.map('projectPlotMap').setView([projectLat, projectLng], 15);

    // OpenStreetMap Base Tile Layer
    const osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    // Google Satellite Tile Layer
    const satelliteLayer = L.tileLayer('https://mt1.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
        maxZoom: 20,
        attribution: '&copy; Google Maps'
    });

    // Layer Switcher Control (TAUSI Image 2 Top Right)
    L.control.layers({
        "OpenStreetMap": osmLayer,
        "Google Satellite": satelliteLayer
    }, null, { position: 'topright' }).addTo(map);

    // Render Plot Polygons from GeoJSON
    if (rawGeoJson && rawGeoJson.features && rawGeoJson.features.length > 0) {
        geojsonLayer = L.geoJSON(rawGeoJson, {
            style: function (feature) {
                const status = feature.properties.status || 'Available';
                let fillColor = '#38bdf8'; // Available Cyan
                if (status === 'Sold') fillColor = '#ef4444'; // Sold Red
                if (status === 'Reserved') fillColor = '#f59e0b'; // Reserved Yellow

                return {
                    fillColor: fillColor,
                    weight: 1.5,
                    opacity: 1,
                    color: '#ffffff',
                    fillOpacity: 0.65
                };
            },
            onEachFeature: function (feature, layer) {
                layer.on('click', function (e) {
                    highlightFeature(e);
                    displayPlotDetails(feature.properties);
                });
            }
        }).addTo(map);

        // Fit Bounds to Plot Polygons
        try {
            map.fitBounds(geojsonLayer.getBounds(), { padding: [30, 30] });
        } catch (err) {}
    }
});

function highlightFeature(e) {
    if (selectedFeature) {
        geojsonLayer.resetStyle(selectedFeature);
    }
    selectedFeature = e.target;
    selectedFeature.setStyle({
        weight: 3,
        color: '#0f172a',
        fillOpacity: 0.85
    });
    selectedFeature.bringToFront();
}

function displayPlotDetails(props) {
    document.getElementById('emptyDrawerState').classList.add('d-none');
    document.getElementById('plotDetailsContent').classList.remove('d-none');
    document.getElementById('plotActionContainer').classList.remove('d-none');

    document.getElementById('drawerPlotBadge').textContent = 'Plot ' + props.parcel_number;
    document.getElementById('attrLocality').textContent = props.locality || '{{ $project->location_name }}';
    document.getElementById('attrBlock').textContent = props.block_number || 'Block A';
    document.getElementById('attrLotNo').textContent = props.parcel_number || '1';
    document.getElementById('attrArea').textContent = (props.area_sqm ? Number(props.area_sqm).toLocaleString('en-US') : '600.00');
    document.getElementById('attrRegion').textContent = props.region || '{{ $project->region }}';
    document.getElementById('attrDistrict').textContent = props.district || '{{ $project->district }}';
    document.getElementById('attrCouncil').textContent = props.council || '{{ $project->council }}';

    const statusBadge = document.getElementById('attrStatus');
    statusBadge.textContent = props.status || 'Available';
    statusBadge.className = 'badge ' + (props.status === 'Sold' ? 'bg-danger' : (props.status === 'Reserved' ? 'bg-warning' : 'bg-success'));

    document.getElementById('attrLandUse').textContent = props.land_use || 'Makazi / Residential';
    document.getElementById('attrPrice').textContent = (props.price ? Number(props.price).toLocaleString('en-US') : '15,000,000') + ' TZS';
    document.getElementById('attrInstallment').textContent = (props.first_installment ? Number(props.first_installment).toLocaleString('en-US') : '7,500,000') + ' TZS';

    document.getElementById('googleMapsBtn').href = 'https://www.google.com/maps/search/?api=1&query=' + projectLat + ',' + projectLng;

    const inquireBtn = document.getElementById('inquirePlotBtn');
    inquireBtn.onclick = function () {
        document.getElementById('modalPlotTitle').value = '{{ $project->title }} - Plot ' + props.parcel_number + ' (' + props.block_number + ')';
        const modal = new bootstrap.Modal(document.getElementById('inquirePlotModal'));
        modal.show();
    };
}

function copyPlotGeoJson() {
    if (!selectedFeature) {
        alert('Please click a plot on the map first.');
        return;
    }
    const str = JSON.stringify(selectedFeature.feature);
    navigator.clipboard.writeText(str).then(function() {
        alert('Plot GeoJSON copied to clipboard!');
    });
}
</script>
@endsection
