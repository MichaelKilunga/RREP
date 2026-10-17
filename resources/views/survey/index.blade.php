@extends('layouts.app')

@section('title', __('app.survey_projects'))

@section('content')
@php
    $surveyBadge = ['Planning' => 'pending', 'Fieldwork' => 'review', 'Beaconing' => 'review', 'Computations' => 'review', 'Verification' => 'underreview', 'Approved' => 'active', 'Completed' => 'paid'];
@endphp

<div class="page-header">
    <div class="min-w-0">
        <h1 class="brand-font mb-1">{{ __('ops.survey_title') }}</h1>
        <p class="text-muted small mb-0">{{ __('ops.survey_subtitle') }}</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('survey.map') }}" class="btn btn-outline-primary"><i class="bi bi-map me-1"></i> {{ __('ops.gis_map') }}</a>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newSurveyModal">
            <i class="bi bi-plus-lg me-1"></i> {{ __('ops.new_survey_project') }}
        </button>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 table-stack small">
            <thead class="table-light">
                <tr>
                    <th>{{ __('ops.project_code') }}</th>
                    <th>{{ __('ops.project_name') }}</th>
                    <th>{{ __('ops.location') }}</th>
                    <th>{{ __('ops.area_size') }}</th>
                    <th>{{ __('ops.lead_surveyor') }}</th>
                    <th>{{ __('ops.beacons') }}</th>
                    <th>{{ __('app.status') }}</th>
                    <th class="text-end">{{ __('app.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($projects as $p)
                    <tr>
                        <td class="stack-primary"><a href="{{ route('survey.show', $p) }}" class="fw-bold text-primary text-decoration-none">{{ $p->project_code }}</a></td>
                        <td>
                            <div class="fw-bold">{{ $p->project_name }}</div>
                            @if($p->property)<small class="text-muted">{{ __('ops.property') }}: {{ $p->property->title }}</small>@endif
                        </td>
                        <td>{{ $p->location_name }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $p->total_area ? rtrim(rtrim(number_format($p->total_area, 4), '0'), '.') : '-' }} {{ $p->area_unit }}</span></td>
                        <td>{{ $p->leadSurveyor?->name ?? __('ops.unassigned') }}</td>
                        <td><span class="badge bg-primary-subtle text-primary">{{ __('ops.beacons_count', ['count' => $p->beacons->count()]) }}</span></td>
                        <td><span class="badge badge-status-{{ $surveyBadge[$p->status] ?? strtolower($p->status) }}">{{ __('ops.survey_status_'.strtolower($p->status)) }}</span></td>
                        <td class="stack-actions text-end">
                            <a href="{{ route('survey.show', $p) }}" class="btn btn-light border btn-sm"><i class="bi bi-eye me-1"></i>{{ __('ops.view_gis_beacons') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="stack-no-label">
                            <div class="empty-state">
                                <div class="empty-icon"><i class="bi bi-geo-alt"></i></div>
                                <h6>{{ __('ops.no_surveys') }}</h6>
                                <p class="small mb-3">{{ __('ops.no_surveys_hint') }}</p>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newSurveyModal"><i class="bi bi-plus-lg me-1"></i> {{ __('ops.new_survey_project') }}</button>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($projects->hasPages())
        <div class="p-3 border-top d-flex justify-content-center justify-content-md-end">
            {{ $projects->links() }}
        </div>
    @endif
</div>

<!-- Modal -->
<div class="modal fade" id="newSurveyModal" tabindex="-1" aria-labelledby="newSurveyModalLabel">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title brand-font" id="newSurveyModalLabel">{{ __('ops.new_survey_title') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('app.cancel') }}"></button>
            </div>
            <form action="{{ route('survey.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="svProjectName" class="form-label small fw-semibold">{{ __('ops.project_name') }} *</label>
                        <input type="text" id="svProjectName" name="project_name" class="form-control" placeholder="{{ __('ops.project_name_placeholder') }}" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-12 col-md-6">
                            <label for="svLocation" class="form-label small fw-semibold">{{ __('ops.location_ward_district') }} *</label>
                            <input type="text" id="svLocation" name="location_name" class="form-control" placeholder="{{ __('ops.survey_location_placeholder') }}" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="svProperty" class="form-label small fw-semibold">{{ __('ops.linked_property') }}</label>
                            <select id="svProperty" name="property_id" class="form-select">
                                <option value="">{{ __('ops.none') }}</option>
                                @foreach($properties as $pr)
                                    <option value="{{ $pr->id }}">{{ $pr->title }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label for="svArea" class="form-label small fw-semibold">{{ __('ops.total_area') }}</label>
                            <input type="number" step="0.0001" min="0" inputmode="decimal" id="svArea" name="total_area" class="form-control" placeholder="10.00">
                        </div>
                        <div class="col-6">
                            <label for="svAreaUnit" class="form-label small fw-semibold">{{ __('ops.area_unit') }}</label>
                            <select id="svAreaUnit" name="area_unit" class="form-select">
                                <option value="Acres">{{ __('app.acres') }}</option>
                                <option value="Hectares">{{ __('ops.hectares') }}</option>
                                <option value="Sqm">{{ __('app.sqm') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-12 col-md-6">
                            <label for="svSurveyor" class="form-label small fw-semibold">{{ __('ops.lead_surveyor') }}</label>
                            <select id="svSurveyor" name="lead_surveyor_id" class="form-select">
                                @foreach($surveyors as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="svLicense" class="form-label small fw-semibold">{{ __('ops.surveyor_license') }}</label>
                            <input type="text" id="svLicense" name="surveyor_license_number" class="form-control" placeholder="NCLS-TZ-0412">
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label for="svStart" class="form-label small fw-semibold">{{ __('ops.start_date') }}</label>
                            <input type="date" id="svStart" name="start_date" class="form-control">
                        </div>
                        <div class="col-6">
                            <label for="svExpected" class="form-label small fw-semibold">{{ __('ops.expected_completion') }}</label>
                            <input type="date" id="svExpected" name="expected_completion_date" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="svDescription" class="form-label small fw-semibold">{{ __('ops.project_scope') }}</label>
                        <textarea id="svDescription" name="description" rows="3" class="form-control" placeholder="{{ __('ops.project_scope_placeholder') }}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">{{ __('app.cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('ops.initialize_survey') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
