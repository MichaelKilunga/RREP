@extends('layouts.app')

@section('title', __('ops.survey').' '.$survey->project_code)

@section('styles')
<style>
    .milestone-timeline { list-style: none; padding-left: 0; margin: 0; }
    .milestone-timeline li { position: relative; padding: 0 0 1rem 2.25rem; }
    .milestone-timeline li::before { content: ""; position: absolute; left: 0.9rem; top: 1.6rem; bottom: -0.2rem; border-left: 2px dashed var(--rrep-card-border); }
    .milestone-timeline li:last-child::before { display: none; }
    .milestone-dot { position: absolute; left: 0; top: 0; width: 1.8rem; height: 1.8rem; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 700; }
</style>
@endsection

@section('content')
@php
    $surveyBadge = ['Planning' => 'pending', 'Fieldwork' => 'review', 'Beaconing' => 'review', 'Computations' => 'review', 'Verification' => 'underreview', 'Approved' => 'active', 'Completed' => 'paid'];
    $surveyStatuses = ['Planning', 'Fieldwork', 'Beaconing', 'Computations', 'Verification', 'Approved', 'Completed'];
    $conditionBadge = ['Good' => 'active', 'Damaged' => 'pending', 'Replaced' => 'review', 'Missing' => 'overdue'];
    $milestonesDone = $survey->milestones->where('status', 'Completed')->count();
    $milestonesTotal = $survey->milestones->count();
    $surveyorPhone = $survey->leadSurveyor?->phone;
@endphp

<div class="page-header">
    <div class="min-w-0">
        <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
            <h1 class="brand-font mb-0">{{ $survey->project_name }}</h1>
            <span class="badge badge-status-{{ $surveyBadge[$survey->status] ?? strtolower($survey->status) }}">{{ __('ops.survey_status_'.strtolower($survey->status)) }}</span>
        </div>
        <p class="text-muted small mb-0">{{ __('ops.project_code') }}: <strong>{{ $survey->project_code }}</strong> &bull; {{ $survey->location_name }} &bull; {{ $survey->total_area ? rtrim(rtrim(number_format($survey->total_area, 4), '0'), '.') : '-' }} {{ $survey->area_unit }}</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('survey.index') }}" class="btn btn-light border"><i class="bi bi-arrow-left me-1"></i> {{ __('ops.back') }}</a>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBeaconModal">
            <i class="bi bi-geo-fill me-1"></i> {{ __('ops.add_beacon') }}
        </button>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-label">{{ __('ops.beacons') }}</div>
            <div class="kpi-value">{{ $survey->beacons->count() }}</div>
            <span class="kpi-icon"><i class="bi bi-geo-fill"></i></span>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card kpi-success">
            <div class="kpi-label">{{ __('ops.milestones') }}</div>
            <div class="kpi-value">{{ $milestonesDone }}/{{ $milestonesTotal }}</div>
            <span class="kpi-icon"><i class="bi bi-list-check"></i></span>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card kpi-info">
            <div class="kpi-label">{{ __('ops.start_date') }}</div>
            <div class="kpi-value fs-6">{{ $survey->start_date?->format('d M Y') ?? '—' }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card kpi-warning">
            <div class="kpi-label">{{ __('ops.expected_completion') }}</div>
            <div class="kpi-value fs-6">{{ $survey->expected_completion_date?->format('d M Y') ?? '—' }}</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <!-- Interactive Cadastral Leaflet Map -->
        <div class="card mb-4">
            <div class="card-header brand-font d-flex justify-content-between align-items-center gap-2">
                <span class="text-truncate"><i class="bi bi-map-fill text-primary me-2"></i>{{ __('ops.boundary_polygon') }}</span>
                <span class="badge bg-light text-dark border text-nowrap">{{ __('ops.points_count', ['count' => $survey->beacons->count()]) }}</span>
            </div>
            <div class="card-body p-0">
                <div id="surveyMap" class="map-h-md w-100" role="region" aria-label="{{ __('ops.boundary_polygon') }}"></div>
            </div>
        </div>

        <!-- Beacons Coordinates Table -->
        <div class="card">
            <div class="card-header brand-font">{{ __('ops.coordinate_registry') }}</div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 table-stack small">
                    <thead class="table-light">
                        <tr>
                            <th>{{ __('ops.beacon_no') }}</th>
                            <th>{{ __('ops.latitude') }}</th>
                            <th>{{ __('ops.longitude') }}</th>
                            <th>{{ __('ops.northing') }}</th>
                            <th>{{ __('ops.easting') }}</th>
                            <th>{{ __('ops.type') }}</th>
                            <th>{{ __('ops.condition') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($survey->beacons as $b)
                            <tr>
                                <td class="stack-primary"><span class="fw-bold text-primary">{{ $b->beacon_number }}</span></td>
                                <td class="font-monospace">{{ $b->latitude }}</td>
                                <td class="font-monospace">{{ $b->longitude }}</td>
                                <td class="font-monospace">{{ $b->northing ?? '-' }}</td>
                                <td class="font-monospace">{{ $b->easting ?? '-' }}</td>
                                <td><span class="badge bg-light text-dark border">{{ $b->beacon_type }}</span></td>
                                <td><span class="badge badge-status-{{ $conditionBadge[$b->condition] ?? strtolower((string) $b->condition) }}">{{ $b->condition }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="stack-no-label">
                                    <div class="empty-state py-4">
                                        <div class="empty-icon"><i class="bi bi-geo"></i></div>
                                        <h6>{{ __('ops.no_beacons') }}</h6>
                                        <p class="small mb-3">{{ __('ops.no_beacons_hint') }}</p>
                                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addBeaconModal"><i class="bi bi-geo-fill me-1"></i> {{ __('ops.add_beacon') }}</button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <!-- Status update -->
        <div class="card mb-4">
            <div class="card-header brand-font">{{ __('ops.update_survey_stage') }}</div>
            <div class="card-body">
                <form action="{{ route('survey.status', $survey) }}" method="POST" class="d-flex flex-column flex-sm-row gap-2">
                    @csrf
                    <label for="surveyStatus" class="visually-hidden">{{ __('app.status') }}</label>
                    <select id="surveyStatus" name="status" class="form-select flex-grow-1">
                        @foreach($surveyStatuses as $st)
                            <option value="{{ $st }}" @selected($st === $survey->status)>{{ __('ops.survey_status_'.strtolower($st)) }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-primary text-nowrap"><i class="bi bi-check2-circle me-1"></i>{{ __('ops.update_status') }}</button>
                </form>
                <div class="form-text mt-2">{{ __('ops.survey_status_hint') }}</div>
            </div>
        </div>

        <!-- Milestones -->
        <div class="card mb-4">
            <div class="card-header brand-font d-flex justify-content-between align-items-center">
                <span>{{ __('ops.survey_milestones') }}</span>
                <span class="badge bg-primary-subtle text-primary">{{ $milestonesDone }}/{{ $milestonesTotal }}</span>
            </div>
            <div class="card-body">
                @if($milestonesTotal > 0)
                    <ol class="milestone-timeline">
                        @foreach($survey->milestones->sortBy('sequence') as $m)
                            @php $done = $m->status === 'Completed'; $inProgress = $m->status === 'In Progress'; @endphp
                            <li>
                                <span class="milestone-dot {{ $done ? 'bg-success text-white' : ($inProgress ? 'bg-primary text-white' : 'bg-light text-muted border') }}">
                                    @if($done)<i class="bi bi-check-lg"></i>@else{{ $m->sequence }}@endif
                                </span>
                                <div class="d-flex justify-content-between align-items-start gap-2">
                                    <span class="fw-bold small">{{ $m->milestone_name }}</span>
                                    <span class="badge badge-status-{{ $done ? 'paid' : ($inProgress ? 'review' : 'pending') }} text-nowrap">{{ $m->status }}</span>
                                </div>
                                @if($m->due_date || $m->completed_at)
                                    <div class="text-muted small">
                                        @if($m->completed_at){{ __('ops.completed_on') }}: {{ $m->completed_at->format('d M Y') }}@elseif($m->due_date){{ __('ops.due_date') }}: {{ $m->due_date->format('d M Y') }}@endif
                                    </div>
                                @endif
                                @if($m->remarks)<p class="text-muted small mb-0">{{ $m->remarks }}</p>@endif
                            </li>
                        @endforeach
                    </ol>
                @else
                    <div class="empty-state py-3">
                        <div class="empty-icon"><i class="bi bi-flag"></i></div>
                        <h6>{{ __('ops.no_milestones') }}</h6>
                        <p class="small mb-0">{{ __('ops.no_milestones_hint') }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Surveyor Information -->
        <div class="card">
            <div class="card-header brand-font">{{ __('ops.lead_surveyor_details') }}</div>
            <div class="card-body">
                <div class="fw-bold">{{ $survey->leadSurveyor?->name ?? __('ops.unassigned') }}</div>
                <div class="text-muted small mb-2">{{ $survey->leadSurveyor?->job_title ?? __('ops.cadastral_surveyor') }}</div>
                <div class="small mb-1"><i class="bi bi-card-text text-primary me-2"></i>{{ __('ops.license') }}: <strong>{{ $survey->surveyor_license_number ?: '—' }}</strong></div>
                @if($surveyorPhone)
                    <div class="small mb-3"><i class="bi bi-telephone text-primary me-2"></i><a href="tel:{{ $surveyorPhone }}" class="text-decoration-none">{{ format_phone_tz($surveyorPhone) }}</a></div>
                    <div class="d-grid d-sm-flex gap-2">
                        <a href="tel:{{ $surveyorPhone }}" class="btn btn-light border btn-sm"><i class="bi bi-telephone me-1"></i>{{ __('ops.call') }}</a>
                        @php $spDigits = preg_replace('/\D+/', '', $surveyorPhone); if (str_starts_with($spDigits, '0')) { $spDigits = '255'.substr($spDigits, 1); } @endphp
                        <a href="https://wa.me/{{ $spDigits }}" target="_blank" rel="noopener" class="btn btn-whatsapp btn-sm"><i class="bi bi-whatsapp me-1"></i>WhatsApp</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="addBeaconModal" tabindex="-1" aria-labelledby="addBeaconModalLabel">
    <div class="modal-dialog modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title brand-font" id="addBeaconModalLabel">{{ __('ops.add_beacon') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('app.cancel') }}"></button>
            </div>
            <form action="{{ route('survey.beacons.add', $survey) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="beaconNumber" class="form-label small fw-semibold">{{ __('ops.beacon_id') }} *</label>
                        <input type="text" id="beaconNumber" name="beacon_number" class="form-control" placeholder="e.g. BM-05, TP-2" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label for="beaconLat" class="form-label small fw-semibold">{{ __('ops.latitude') }} *</label>
                            <input type="number" step="0.00000001" inputmode="decimal" id="beaconLat" name="latitude" class="form-control" placeholder="-6.82350000" required>
                        </div>
                        <div class="col-6">
                            <label for="beaconLng" class="form-label small fw-semibold">{{ __('ops.longitude') }} *</label>
                            <input type="number" step="0.00000001" inputmode="decimal" id="beaconLng" name="longitude" class="form-control" placeholder="39.26950000" required>
                        </div>
                    </div>
                    <div class="d-grid mb-3">
                        <button type="button" class="btn btn-outline-primary btn-sm" id="useGpsBtn"><i class="bi bi-crosshair me-1"></i>{{ __('ops.use_my_gps') }}</button>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label for="beaconNorthing" class="form-label small fw-semibold">{{ __('ops.northing') }} (UTM)</label>
                            <input type="number" step="0.01" inputmode="decimal" id="beaconNorthing" name="northing" class="form-control">
                        </div>
                        <div class="col-6">
                            <label for="beaconEasting" class="form-label small fw-semibold">{{ __('ops.easting') }} (UTM)</label>
                            <input type="number" step="0.01" inputmode="decimal" id="beaconEasting" name="easting" class="form-control">
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-12 col-sm-6">
                            <label for="beaconType" class="form-label small fw-semibold">{{ __('ops.monument_type') }}</label>
                            <select id="beaconType" name="beacon_type" class="form-select">
                                <option value="Concrete Pillar">{{ __('ops.beacon_concrete') }}</option>
                                <option value="Iron Pin">{{ __('ops.beacon_iron_pin') }}</option>
                                <option value="Stone Monument">{{ __('ops.beacon_stone') }}</option>
                                <option value="GPS Control Point">{{ __('ops.beacon_gps') }}</option>
                            </select>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label for="beaconCondition" class="form-label small fw-semibold">{{ __('ops.condition') }}</label>
                            <select id="beaconCondition" name="condition" class="form-select">
                                <option value="Good">{{ __('ops.condition_good') }}</option>
                                <option value="Damaged">{{ __('ops.condition_damaged') }}</option>
                                <option value="Replaced">{{ __('ops.condition_replaced') }}</option>
                                <option value="Missing">{{ __('ops.condition_missing') }}</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">{{ __('app.cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('ops.save_beacon') }}</button>
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
    let center = [-6.7925, 39.2083];

    @if($survey->beacons->first())
        center = [{{ $survey->beacons->first()->latitude }}, {{ $survey->beacons->first()->longitude }}];
    @endif

    const map = L.map('surveyMap', { tap: true }).setView(center, 15);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    const brand = getComputedStyle(document.documentElement).getPropertyValue('--rrep-primary').trim() || '#0f52ba';

    if (geoData && geoData.features && geoData.features.length > 0) {
        const geoLayer = L.geoJSON(geoData, {
            style: { color: brand, weight: 3, fillColor: brand, fillOpacity: 0.2 }
        }).addTo(map);
        map.fitBounds(geoLayer.getBounds(), { padding: [16, 16] });
    }

    @foreach($survey->beacons as $b)
        L.marker([{{ $b->latitude }}, {{ $b->longitude }}]).addTo(map)
            .bindPopup({!! json_encode('<strong>'.__('ops.beacon').' '.e($b->beacon_number).'</strong><br>'.e($b->beacon_type)) !!});
    @endforeach

    const modal = document.getElementById('addBeaconModal');
    if (modal) {
        modal.addEventListener('shown.bs.modal', function () { window.setTimeout(function () { map.invalidateSize(); }, 50); });
    }
    window.addEventListener('resize', function () { map.invalidateSize(); });

    const gpsBtn = document.getElementById('useGpsBtn');
    if (gpsBtn) {
        gpsBtn.addEventListener('click', function () {
            if (!navigator.geolocation) { gpsBtn.disabled = true; return; }
            gpsBtn.classList.add('btn-loading');
            navigator.geolocation.getCurrentPosition(function (pos) {
                document.getElementById('beaconLat').value = pos.coords.latitude.toFixed(8);
                document.getElementById('beaconLng').value = pos.coords.longitude.toFixed(8);
                gpsBtn.classList.remove('btn-loading');
            }, function () {
                gpsBtn.classList.remove('btn-loading');
                if (window.RREP && window.RREP.toast) { window.RREP.toast(@json(__('ops.gps_unavailable')), 'warning'); }
            }, { enableHighAccuracy: true, timeout: 10000 });
        });
    }
});
</script>
@endsection
