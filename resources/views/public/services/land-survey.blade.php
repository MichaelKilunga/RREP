@extends('layouts.public')

@section('title', __('app.professional_land_survey_and_cadastral_mapping'))
@section('meta_description', __('app.certified_geomatics_land_survey_services_in'))

@section('content')
<!-- Hero -->
<section class="page-hero hero-img-survey py-4 py-md-5">
    <div class="container py-2 py-md-4">
        <div class="row align-items-center g-4">
            <div class="col-12 col-lg-7">
                <span class="badge bg-success px-3 py-2 rounded-pill mb-2">
                    <i class="bi bi-compass me-1"></i> {{ __('app.certified_geomatics_and_cadastral_surveyors') }}
                </span>
                <h1 class="brand-font fw-bold mb-3">{{ __('app.professional_land_survey_and_gis_mapping') }}</h1>
                <p class="lead text-white-50 mb-4 leading-relaxed">{{ __('app.protect_your_property_investments_with_high') }}</p>
                <div class="d-grid d-sm-flex flex-wrap gap-2 gap-sm-3">
                    <a href="#requestSurveyForm" class="btn btn-warning btn-lg rounded-pill px-4 fw-bold shadow">
                        <i class="bi bi-pencil-square me-1"></i> {{ __('app.request_a_land_survey') }}
                    </a>
                    <a href="https://wa.me/{{ setting('contact_whatsapp', '255784100200') }}?text={{ urlencode(__('app.hello_i_would_like_to_consult')) }}" target="_blank" rel="noopener" class="btn btn-whatsapp btn-lg rounded-pill px-4 fw-semibold">
                        <i class="bi bi-whatsapp me-1"></i> {{ __('app.consult_on_whatsapp') }}
                    </a>
                </div>
            </div>
            <div class="col-12 col-lg-5">
                <div class="card p-4 rounded-4 bg-white bg-opacity-10 border border-light border-opacity-25 text-white shadow-lg text-center">
                    <i class="bi bi-compass text-warning display-5 mb-2"></i>
                    <h2 class="brand-font text-white fs-4 mb-1">{{ $recentSurveysCount }}+ {{ __('app.survey_projects_2') }}</h2>
                    <p class="small text-white-50 mb-0">{{ __('app.delivered_across_dar_es_salaam_morogoro') }}</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Survey services -->
<section class="py-4 py-md-5 bg-white border-bottom">
    <div class="container">
        <div class="text-center max-w-700 mx-auto mb-4 mb-md-5">
            <span class="section-tag">{{ __('app.survey_capabilities') }}</span>
            <h2 class="brand-font mb-2">{{ __('app.our_land_survey_and_geospatial_services') }}</h2>
            <p class="text-muted mb-0">{{ __('app.conducted_in_strict_compliance_with_the') }}</p>
        </div>

        @php
            $surveyServices = [
                ['icon' => 'bi-bounding-box-circles text-primary', 'title' => __('app.boundary_survey_and_beaconing'), 'text' => __('app.accurate_beacon_relocation_monument_fixation_boundary')],
                ['icon' => 'bi-map text-success', 'title' => __('app.cadastral_plot_surveys'), 'text' => __('app.full_cadastral_surveys_for_title_deed')],
                ['icon' => 'bi-diagram-3 text-warning', 'title' => __('app.land_subdivision_and_estate_master_plans'), 'text' => __('app.subdivision_of_large_agricultural_farms_and')],
                ['icon' => 'bi-layers text-info', 'title' => __('app.topographical_and_contour_surveys'), 'text' => __('app.detailed_elevation_models_contour_drawings_and')],
                ['icon' => 'bi-building-up text-danger', 'title' => __('app.engineering_and_construction_setting_out'), 'text' => __('app.setting_out_building_foundations_road_alignment')],
                ['icon' => 'bi-globe-americas text-dark', 'title' => __('app.gis_mapping_and_geospatial_analytics'), 'text' => __('app.interactive_web_gis_mapping_spatial_database')],
            ];
        @endphp
        <div class="row g-3 g-md-4">
            @foreach($surveyServices as $svc)
                <div class="col-12 col-sm-6 col-lg-4">
                    <div class="card h-100 p-4 rounded-4 shadow-sm hover-shadow transition">
                        <i class="bi {{ $svc['icon'] }} fs-2 mb-3"></i>
                        <h3 class="brand-font fs-6 mb-2">{{ $svc['title'] }}</h3>
                        <p class="text-muted small mb-0">{{ $svc['text'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Workflow -->
<section class="py-4 py-md-5 bg-light border-bottom">
    <div class="container">
        <div class="text-center max-w-700 mx-auto mb-4 mb-md-5">
            <span class="section-tag">{{ __('app.rigorous_workflow') }}</span>
            <h2 class="brand-font mb-2">{{ __('app.how_our_land_survey_process_works') }}</h2>
            <p class="text-muted mb-0">{{ __('app.four_transparent_stages_from_initial_coordinates') }}</p>
        </div>

        @php
            $steps = [
                ['title' => __('app.request_and_due_diligence'), 'text' => __('app.submit_your_plot_location_survey_type')],
                ['title' => __('app.fieldwork_and_beaconing'), 'text' => __('app.licensed_surveyors_arrive_with_gnss_rtk')],
                ['title' => __('app.computations_and_gis'), 'text' => __('app.rigorous_closure_computations_utm_coordinates_and')],
                ['title' => __('app.certified_delivery'), 'text' => __('app.official_survey_plan_documents_delivered_ready')],
            ];
        @endphp
        <div class="row g-3 g-md-4 text-center">
            @foreach($steps as $step)
                <div class="col-6 col-md-3">
                    <div class="card p-3 p-md-4 border-0 rounded-4 shadow-sm h-100 bg-white">
                        <span class="touch-target rounded-circle {{ $loop->last ? 'bg-success' : 'bg-primary' }} text-white mx-auto mb-3 fw-bold fs-5">{{ $loop->iteration }}</span>
                        <h3 class="brand-font fs-6 mb-2">{{ $step['title'] }}</h3>
                        <p class="text-muted small mb-0">{{ $step['text'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Request form -->
<section class="py-4 py-md-5" id="requestSurveyForm">
    <div class="container">
        <div class="card p-3 p-md-4 p-lg-5 rounded-4 shadow-lg mx-auto bg-white max-w-850">
            <div class="text-center mb-4">
                <span class="badge bg-primary px-3 py-2 rounded-pill mb-2">{{ __('app.direct_survey_request') }}</span>
                <h2 class="brand-font fs-4 mb-1">{{ __('app.request_a_land_survey_or_cadastral') }}</h2>
                <p class="text-muted small mb-0">{{ __('app.fill_out_the_form_below_our') }}</p>
            </div>

            @if($errors->any())
                <div class="alert alert-danger" role="alert">
                    <strong>{{ __('pages.form_errors_title') }}</strong>
                    <ul class="mb-0 small">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('public.survey.request') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label for="surveyName" class="form-label small fw-bold text-muted">{{ __('app.your_full_name') }}</label>
                        <input type="text" id="surveyName" name="name" class="form-control" placeholder="Juma Rashid" autocomplete="name" value="{{ old('name') }}" required>
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="surveyPhone" class="form-label small fw-bold text-muted">{{ __('app.phone_number_whatsapp') }}</label>
                        <input type="tel" id="surveyPhone" name="phone" class="form-control" placeholder="0784 100 200" data-tz-phone autocomplete="tel" value="{{ old('phone') }}" required>
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="surveyEmail" class="form-label small fw-bold text-muted">{{ __('app.email_address') }}</label>
                        <input type="email" id="surveyEmail" name="email" class="form-control" placeholder="juma@example.com" autocomplete="email" value="{{ old('email') }}">
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="surveyLocation" class="form-label small fw-bold text-muted">{{ __('app.property_location_region') }}</label>
                        <input type="text" id="surveyLocation" name="location" class="form-control" list="tzRegionList" placeholder="{{ __('app.e_g_kihonda_morogoro_or_gezaulole') }}" value="{{ old('location') }}" required>
                        <datalist id="tzRegionList">
                            @foreach(tz_regions() as $region)
                                <option value="{{ $region }}"></option>
                            @endforeach
                        </datalist>
                        <div class="form-text">{{ __('pages.location_hint') }}</div>
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="surveyType" class="form-label small fw-bold text-muted">{{ __('app.type_of_survey_needed') }}</label>
                        <select id="surveyType" name="survey_type" class="form-select" required>
                            <option value="Boundary Beacon Relocation" @selected(old('survey_type') === 'Boundary Beacon Relocation')>{{ __('app.boundary_beacon_relocation_and_fixation') }}</option>
                            <option value="Cadastral Title Survey" @selected(old('survey_type') === 'Cadastral Title Survey')>{{ __('app.cadastral_title_survey_right_of_occupancy') }}</option>
                            <option value="Land Subdivision" @selected(old('survey_type') === 'Land Subdivision')>{{ __('app.land_subdivision_and_plot_splitting') }}</option>
                            <option value="Topographical Survey" @selected(old('survey_type') === 'Topographical Survey')>{{ __('app.topographical_and_contour_survey') }}</option>
                            <option value="Construction Setting-Out" @selected(old('survey_type') === 'Construction Setting-Out')>{{ __('app.construction_setting_out') }}</option>
                            <option value="Deed Due Diligence" @selected(old('survey_type') === 'Deed Due Diligence')>{{ __('app.deed_due_diligence_and_coordinate_check') }}</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="surveyLandSize" class="form-label small fw-bold text-muted">{{ __('app.approximate_land_size') }}</label>
                        <input type="text" id="surveyLandSize" name="approx_land_size" class="form-control" inputmode="decimal" placeholder="{{ __('app.e_g_600_sqm_2_acres') }}" value="{{ old('approx_land_size') }}">
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="surveyDate" class="form-label small fw-bold text-muted">{{ __('app.preferred_survey_fieldwork_date') }}</label>
                        <input type="date" id="surveyDate" name="preferred_date" class="form-control" min="{{ date('Y-m-d') }}" value="{{ old('preferred_date') }}">
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="surveyDocs" class="form-label small fw-bold text-muted">{{ __('app.existing_documentation_status') }}</label>
                        <select id="surveyDocs" name="documentation_status" class="form-select">
                            <option value="Title Deed">{{ __('app.clean_title_deed_in_hand') }}</option>
                            <option value="TP Drawing">{{ __('app.town_planning_tp_drawing_available') }}</option>
                            <option value="Sales Agreement">{{ __('app.local_government_sales_agreement_only') }}</option>
                            <option value="Customary">{{ __('app.unsurveyed_customary_land') }}</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <span class="form-label small fw-bold text-muted d-block">{{ __('app.pin_your_plot_location_on_digital') }}</span>
                        <div class="ratio ratio-16x9 rounded-3 border overflow-hidden mb-2">
                            <div id="surveyPinMap" role="application" aria-label="{{ __('app.pin_your_plot_location_on_digital') }}"></div>
                        </div>
                        <div class="row g-2">
                            <div class="col-12 col-md-6">
                                <div class="input-group input-group-sm">
                                    <label class="input-group-text" for="pinLatitude">{{ __('pages.latitude') }}</label>
                                    <input type="number" step="0.00000001" name="latitude" id="pinLatitude" class="form-control" inputmode="decimal" placeholder="-6.7924" value="{{ old('latitude') }}">
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="input-group input-group-sm">
                                    <label class="input-group-text" for="pinLongitude">{{ __('pages.longitude') }}</label>
                                    <input type="number" step="0.00000001" name="longitude" id="pinLongitude" class="form-control" inputmode="decimal" placeholder="39.2083" value="{{ old('longitude') }}">
                                </div>
                            </div>
                        </div>
                        <div class="form-text">{{ __('app.click_anywhere_on_the_map_above') }}</div>
                    </div>

                    <div class="col-12">
                        <label for="surveyDescription" class="form-label small fw-bold text-muted">{{ __('app.description_and_special_instructions') }}</label>
                        <textarea id="surveyDescription" name="description" rows="3" class="form-control" placeholder="{{ __('app.please_mention_any_landmark_missing_beacons') }}" required>{{ old('description') }}</textarea>
                    </div>
                    <div class="col-12 mt-4">
                        <div class="d-grid d-sm-flex justify-content-sm-center">
                            <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5 fw-bold shadow">
                                <i class="bi bi-compass me-1"></i> {{ __('app.submit_land_survey_request') }}
                            </button>
                        </div>
                        <p class="text-center text-muted small mt-3 mb-0"><i class="bi bi-phone text-success me-1"></i> {{ __('pages.mobile_money_hint') }}</p>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const latInput = document.getElementById('pinLatitude');
    const lngInput = document.getElementById('pinLongitude');
    const defaultLat = parseFloat(latInput.value) || -6.7924;
    const defaultLng = parseFloat(lngInput.value) || 39.2083;
    const map = L.map('surveyPinMap', { scrollWheelZoom: false }).setView([defaultLat, defaultLng], 12);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    let marker = latInput.value && lngInput.value ? L.marker([defaultLat, defaultLng]).addTo(map) : null;

    map.on('click', function (e) {
        if (marker) {
            marker.setLatLng(e.latlng);
        } else {
            marker = L.marker(e.latlng).addTo(map);
        }
        latInput.value = e.latlng.lat.toFixed(6);
        lngInput.value = e.latlng.lng.toFixed(6);
    });
});
</script>
@endsection
