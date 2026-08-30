@extends('layouts.app')

@section('title', 'Survey ' . $survey->project_code)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <h3 class="brand-font mb-0">{{ $survey->project_name }}</h3>
            <span class="badge bg-warning-subtle text-warning-emphasis">{{ $survey->status }}</span>
        </div>
        <p class="text-muted small mb-0">Project Code: <strong>{{ $survey->project_code }}</strong> &bull; Location: {{ $survey->location_name }} &bull; Area: {{ $survey->total_area }} {{ $survey->area_unit }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('survey.index') }}" class="btn btn-light border btn-sm"><i class="bi bi-arrow-left me-1"></i> Back</a>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addBeaconModal">
            <i class="bi bi-geo-fill me-1"></i> Add Beacon Coordinate
        </button>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <!-- Interactive Cadastral Leaflet Map -->
        <div class="card mb-4">
            <div class="card-header brand-font d-flex justify-content-between align-items-center">
                <span><i class="bi bi-map-fill text-primary me-2"></i>Cadastral Boundary Polygon & Beacons</span>
                <span class="badge bg-light text-dark border">{{ $survey->beacons->count() }} Points</span>
            </div>
            <div class="card-body p-0">
                <div id="surveyMap" style="height: 380px; width: 100%;"></div>
            </div>
        </div>

        <!-- Beacons Coordinates Table -->
        <div class="card">
            <div class="card-header brand-font">Cadastral Coordinate Registry</div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                    <thead class="table-light">
                        <tr>
                            <th>Beacon #</th>
                            <th>Latitude</th>
                            <th>Longitude</th>
                            <th>Northing</th>
                            <th>Easting</th>
                            <th>Type</th>
                            <th>Condition</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($survey->beacons as $b)
                            <tr>
                                <td class="fw-bold text-primary">{{ $b->beacon_number }}</td>
                                <td>{{ $b->latitude }}</td>
                                <td>{{ $b->longitude }}</td>
                                <td>{{ $b->northing ?? '-' }}</td>
                                <td>{{ $b->easting ?? '-' }}</td>
                                <td><span class="badge bg-light text-dark border">{{ $b->beacon_type }}</span></td>
                                <td><span class="badge bg-success-subtle text-success">{{ $b->condition }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-3">No beacons recorded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <!-- Milestones -->
        <div class="card mb-4">
            <div class="card-header brand-font">Survey Milestones</div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($survey->milestones as $m)
                        <li class="list-group-item p-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-bold small">{{ $m->sequence }}. {{ $m->milestone_name }}</span>
                                <span class="badge bg-{{ $m->status == 'Completed' ? 'success' : 'warning' }}-subtle text-{{ $m->status == 'Completed' ? 'success' : 'warning' }}">{{ $m->status }}</span>
                            </div>
                            <p class="text-muted small mb-0">{{ $m->remarks }}</p>
                        </li>
                    @empty
                        <li class="list-group-item text-muted small py-3">No milestones added.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <!-- Surveyor Information -->
        <div class="card">
            <div class="card-header brand-font">Lead Surveyor Details</div>
            <div class="card-body">
                <div class="fw-bold fs-6">{{ $survey->leadSurveyor?->name }}</div>
                <div class="text-muted small mb-2">{{ $survey->leadSurveyor?->job_title ?? 'Cadastral Surveyor' }}</div>
                <div class="small mb-1"><i class="bi bi-card-text text-primary me-2"></i>License: <strong>{{ $survey->surveyor_license_number ?? 'NCLS-TZ' }}</strong></div>
                <div class="small"><i class="bi bi-telephone text-primary me-2"></i>{{ $survey->leadSurveyor?->phone }}</div>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="addBeaconModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title brand-font">Add Beacon Coordinate</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('survey.beacons.add', $survey) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Beacon Identification # *</label>
                        <input type="text" name="beacon_number" class="form-control" placeholder="e.g. BM-05, TP-2" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Latitude *</label>
                            <input type="number" step="0.00000001" name="latitude" class="form-control" placeholder="-3.32800000" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Longitude *</label>
                            <input type="number" step="0.00000001" name="longitude" class="form-control" placeholder="36.65400000" required>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Northing (UTM)</label>
                            <input type="number" step="0.01" name="northing" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Easting (UTM)</label>
                            <input type="number" step="0.01" name="easting" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Monument Type</label>
                        <select name="beacon_type" class="form-select">
                            <option value="Concrete Pillar">Concrete Pillar</option>
                            <option value="Iron Pin">Iron Pin</option>
                            <option value="Stone Monument">Stone Monument</option>
                            <option value="GPS Control Point">GPS Control Point</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Beacon</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const geoData = @json($geoJson);
    let center = [-3.3284, 36.6543];
    
    @if($survey->beacons->first())
        center = [{{ $survey->beacons->first()->latitude }}, {{ $survey->beacons->first()->longitude }}];
    @endif

    const map = L.map('surveyMap').setView(center, 15);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    if (geoData && geoData.features && geoData.features.length > 0) {
        const geoLayer = L.geoJSON(geoData, {
            style: {
                color: '#0f52ba',
                weight: 3,
                fillColor: '#0f52ba',
                fillOpacity: 0.2
            }
        }).addTo(map);
        map.fitBounds(geoLayer.getBounds());
    }

    @foreach($survey->beacons as $b)
        L.marker([{{ $b->latitude }}, {{ $b->longitude }}]).addTo(map)
            .bindPopup("<strong>Beacon {{ $b->beacon_number }}</strong><br>{{ $b->beacon_type }}");
    @endforeach
});
</script>
@endsection
