@extends('layouts.app')

@section('title', __('app.survey_projects'))

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h3 class="brand-font mb-1">{{ __('app.survey_projects') }} & GIS</h3>
        <p class="text-muted small mb-0">Cadastral boundary surveys, beacon coordinates, deed mutation, and GIS spatial mapping</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('survey.map') }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-map me-1"></i> Interactive GIS Map</a>
        <button class="btn btn-primary btn-sm d-flex align-items-center gap-1 shadow-sm" data-bs-toggle="modal" data-bs-target="#newSurveyModal">
            <i class="bi bi-plus-lg"></i> New Survey Project
        </button>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: 0.875rem;">
            <thead class="table-light">
                <tr>
                    <th>Project Code</th>
                    <th>Project Name</th>
                    <th>Location</th>
                    <th>Area Size</th>
                    <th>Lead Surveyor</th>
                    <th>Beacons</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($projects as $p)
                    <tr>
                        <td class="fw-bold text-primary">{{ $p->project_code }}</td>
                        <td>
                            <div class="fw-bold">{{ $p->project_name }}</div>
                            @if($p->property)<small class="text-muted">Property: {{ $p->property->title }}</small>@endif
                        </td>
                        <td>{{ $p->location_name }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $p->total_area }} {{ $p->area_unit }}</span></td>
                        <td>{{ $p->leadSurveyor?->name ?? 'Unassigned' }}</td>
                        <td><span class="badge bg-primary-subtle text-primary">{{ $p->beacons->count() }} Beacons</span></td>
                        <td><span class="badge bg-warning-subtle text-warning-emphasis">{{ $p->status }}</span></td>
                        <td class="text-end">
                            <a href="{{ route('survey.show', $p) }}" class="btn btn-light border btn-sm"><i class="bi bi-eye"></i> View GIS & Beacons</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">No land survey projects registered.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3 border-top d-flex justify-content-end">
        {{ $projects->links() }}
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="newSurveyModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title brand-font">Initialize Cadastral Survey Project</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('survey.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Project Name *</label>
                        <input type="text" name="project_name" class="form-control" placeholder="e.g. Mount Meru 10-Acre Cadastral Boundary Survey" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Location / District *</label>
                            <input type="text" name="location_name" class="form-control" placeholder="Ngaramtoni, Arusha" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Linked Property (Optional)</label>
                            <select name="property_id" class="form-select">
                                <option value="">None</option>
                                @foreach($properties as $pr)
                                    <option value="{{ $pr->id }}">{{ $pr->title }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Total Area</label>
                            <input type="number" step="0.0001" name="total_area" class="form-control" placeholder="10.00">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Area Unit</label>
                            <select name="area_unit" class="form-select">
                                <option value="Acres">Acres</option>
                                <option value="Hectares">Hectares</option>
                                <option value="Sqm">Square Meters</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Lead Surveyor</label>
                            <select name="lead_surveyor_id" class="form-select">
                                @foreach($surveyors as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Surveyor License #</label>
                            <input type="text" name="surveyor_license_number" class="form-control" placeholder="NCLS-TZ-0412">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Project Scope / Remarks</label>
                        <textarea name="description" rows="3" class="form-control" placeholder="Boundary verification, beacon monuments, title deed mutation..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Initialize Survey</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
