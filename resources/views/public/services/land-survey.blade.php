@extends('layouts.public')

@section('title', app()->getLocale() === 'sw' ? 'Huduma za Upimaji Ardhi na Ramani za Cadastral Tanzania' : 'Professional Land Survey & Cadastral Mapping Services in Tanzania')
@section('meta_description', app()->getLocale() === 'sw' ? 'Huduma za wataalamu waliosajiliwa wa upimaji ardhi Tanzania. Uwekaji wa beacons, upimaji wa hati, kupanga viwanja, na ramani za GIS.' : 'Certified geomatics land survey services in Tanzania. Boundary beacon relocation, cadastral surveys, subdivision plans, and GIS mapping.')

@section('content')
<!-- Hero Section -->
<section class="py-5 text-white position-relative" style="background: linear-gradient(135deg, #091224 0%, #064e3b 100%);">
    <div class="container position-relative py-4" style="z-index: 2;">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <span class="badge bg-success px-3 py-2 rounded-pill mb-2">
                    <i class="bi bi-compass me-1"></i> {{ app()->getLocale() === 'sw' ? 'Wapimaji Ardhi Waliosajiliwa' : 'Certified Geomatics & Cadastral Surveyors' }}
                </span>
                <h1 class="brand-font display-5 fw-bold text-white mb-3">
                    {{ app()->getLocale() === 'sw' ? 'Huduma za Wataalamu wa Upimaji Ardhi na Ramani za GIS' : 'Professional Land Survey & GIS Mapping Services' }}
                </h1>
                <p class="lead text-white-50 mb-4" style="line-height: 1.7;">
                    {{ app()->getLocale() === 'sw' ? 'Linda uwekezaji wako wa ardhi na nyumba kwa kutumia teknolojia ya kisasa ya GNSS/RTK GPS. Tunafanya upimaji wa mipaka, kurudisha beacons, kugawanya viwanja, na kuandaa michoro rasmi ya mipango miji kote Tanzania.' : 'Protect your property investments with high-precision GNSS/RTK GPS boundary surveys, beacon relocation, land subdivisions, and certified town planning drawings across Tanzania.' }}
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="#requestSurveyForm" class="btn btn-warning btn-lg rounded-pill px-4 fw-bold shadow">
                        <i class="bi bi-pencil-square me-1"></i> {{ app()->getLocale() === 'sw' ? 'Omba Upimaji wa Ardhi' : 'Request a Land Survey' }}
                    </a>
                    <a href="https://wa.me/{{ setting('contact_whatsapp', '255784100200') }}?text={{ urlencode(app()->getLocale() === 'sw' ? 'Habari, ningependa kupata ushauri kutoka kwa mpimaji ardhi aliyesajiliwa kuhusu kiwanja changu.' : 'Hello, I would like to consult with a licensed land surveyor regarding my plot.') }}" target="_blank" class="btn btn-outline-light btn-lg rounded-pill px-4 fw-semibold">
                        <i class="bi bi-whatsapp me-1"></i> {{ app()->getLocale() === 'sw' ? 'Wasiliana WhatsApp' : 'Consult on WhatsApp' }}
                    </a>
                </div>
            </div>
            <div class="col-lg-5 text-center">
                <div class="card p-4 rounded-4 bg-white bg-opacity-10 border border-light border-opacity-25 text-white shadow-lg">
                    <i class="bi bi-compass text-warning display-4 mb-2"></i>
                    <h4 class="brand-font mb-1">{{ $recentSurveysCount }}+ {{ app()->getLocale() === 'sw' ? 'Miradi ya Upimaji' : 'Survey Projects' }}</h4>
                    <p class="small text-white-50 mb-0">{{ app()->getLocale() === 'sw' ? 'Miradi iliyokamilika kwa mafanikio katika mikoa ya Dar es Salaam, Morogoro, Arusha, Dodoma, na Pwani.' : 'Delivered across Dar es Salaam, Morogoro, Arusha, Dodoma, and Coastal Regions.' }}</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Survey Specializations -->
<section class="py-5 bg-white border-bottom">
    <div class="container">
        <div class="text-center max-w-700 mx-auto mb-5">
            <span class="section-tag">{{ app()->getLocale() === 'sw' ? 'Uwezo Wetu' : 'Survey Capabilities' }}</span>
            <h2 class="brand-font mb-2">{{ app()->getLocale() === 'sw' ? 'Huduma Zetu za Upimaji na GIS' : 'Our Land Survey & Geospatial Services' }}</h2>
            <p class="text-muted">{{ app()->getLocale() === 'sw' ? 'Inafanyika kwa kuzingatia Sheria za Upimaji Ardhi Tanzania na viwango vya Wizara ya Ardhi.' : 'Conducted in strict compliance with the Tanzania Land Survey Ordinance and Ministry of Lands standards.' }}</p>
        </div>

        <div class="row g-4">
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 p-4 border rounded-4 shadow-sm hover-shadow transition">
                    <i class="bi bi-bounding-box-circles text-primary fs-2 mb-3"></i>
                    <h5 class="brand-font mb-2">{{ app()->getLocale() === 'sw' ? 'Upimaji wa Mipaka na Beacons' : 'Boundary Survey & Beaconing' }}</h5>
                    <p class="text-muted small mb-0">{{ app()->getLocale() === 'sw' ? 'Kutafuta na kurudisha beacons zilizopotea, kusuluhisha migogoro ya mipaka, na kuthibitisha eneo ukitumia RTK GPS.' : 'Accurate beacon relocation, monument fixation, boundary conflict resolution, and perimeter verification using high-precision RTK GPS.' }}</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 p-4 border rounded-4 shadow-sm hover-shadow transition">
                    <i class="bi bi-map text-success fs-2 mb-3"></i>
                    <h5 class="brand-font mb-2">{{ app()->getLocale() === 'sw' ? 'Upimaji wa Viwanja na Hati (Cadastral)' : 'Cadastral Plot Surveys' }}</h5>
                    <p class="text-muted small mb-0">{{ app()->getLocale() === 'sw' ? 'Upimaji rasmi kwa ajili ya maombi ya Hati Miliki (Right of Occupancy), maandalizi ya deed plan na kibali cha Wizara.' : 'Full cadastral surveys for title deed applications (Right of Occupancy), deed plan preparation, and official Ministry approvals.' }}</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 p-4 border rounded-4 shadow-sm hover-shadow transition">
                    <i class="bi bi-diagram-3 text-warning fs-2 mb-3"></i>
                    <h5 class="brand-font mb-2">{{ app()->getLocale() === 'sw' ? 'Kugawanya Viwanja & Mipango Miji' : 'Land Subdivision & Estate Master Plans' }}</h5>
                    <p class="text-muted small mb-0">{{ app()->getLocale() === 'sw' ? 'Kugawanya mashamba makubwa au maeneo ya makazi kuwa viwanja vidogo vyenye barabara na huduma za jamii.' : 'Subdivision of large agricultural farms and residential tracts into individual titled building plots with road reserves.' }}</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 p-4 border rounded-4 shadow-sm hover-shadow transition">
                    <i class="bi bi-layers text-info fs-2 mb-3"></i>
                    <h5 class="brand-font mb-2">{{ app()->getLocale() === 'sw' ? 'Upimaji wa Topografia na Miinuko' : 'Topographical & Contour Surveys' }}</h5>
                    <p class="text-muted small mb-0">{{ app()->getLocale() === 'sw' ? 'Ramani za miinuko (contours) na umbo la ardhi kwa ajili ya ramani za majengo na ujenzi wa miundombinu.' : 'Detailed elevation models, contour drawings, and digital terrain mapping for architectural planning and civil engineering.' }}</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 p-4 border rounded-4 shadow-sm hover-shadow transition">
                    <i class="bi bi-building-up text-danger fs-2 mb-3"></i>
                    <h5 class="brand-font mb-2">{{ app()->getLocale() === 'sw' ? 'Upimaji wa Ujenzi na Misingi' : 'Engineering & Construction Setting-Out' }}</h5>
                    <p class="text-muted small mb-0">{{ app()->getLocale() === 'sw' ? 'Kuweka alama za misingi ya majengo, barabara, mifereji, na vipimo vya ujenzi kwa usahihi wa hali ya juu.' : 'Setting out building foundations, road alignment corridors, drainage networks, and structural control points.' }}</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 p-4 border rounded-4 shadow-sm hover-shadow transition">
                    <i class="bi bi-globe-americas text-dark fs-2 mb-3"></i>
                    <h5 class="brand-font mb-2">{{ app()->getLocale() === 'sw' ? 'Ramani za GIS na Ramani za Ndege (Drone)' : 'GIS Mapping & Geospatial Analytics' }}</h5>
                    <p class="text-muted small mb-0">{{ app()->getLocale() === 'sw' ? 'Tathmini ya kijiografia, ramani za kidijitali za GIS, na picha za ndege nyuki (drone photogrammetry).' : 'Interactive web GIS mapping, spatial database development, land use classification, and drone photogrammetry orthomosaics.' }}</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Survey Workflow Protocol -->
<section class="py-5 bg-light border-bottom">
    <div class="container">
        <div class="text-center max-w-700 mx-auto mb-5">
            <span class="section-tag">{{ app()->getLocale() === 'sw' ? 'Hatua za Kazi' : 'Rigorous Workflow' }}</span>
            <h2 class="brand-font mb-2">{{ app()->getLocale() === 'sw' ? 'Jinsi Mchakato wa Upimaji Unavyofanya Kazi' : 'How Our Land Survey Process Works' }}</h2>
            <p class="text-muted">{{ app()->getLocale() === 'sw' ? 'Hatua 4 za wazi kuanzia uhakiki wa kwanza hadi utoaji wa ramani zilizoidhinishwa.' : 'Four transparent stages from initial coordinates search to official deed approval.' }}</p>
        </div>

        <div class="row g-4 text-center">
            <div class="col-md-3">
                <div class="card p-4 border-0 rounded-4 shadow-sm h-100 bg-white">
                    <div class="rounded-circle bg-primary text-white mx-auto mb-3 d-flex align-items-center justify-content-center fw-bold fs-4" style="width: 54px; height: 54px;">1</div>
                    <h6 class="brand-font mb-2">{{ app()->getLocale() === 'sw' ? 'Ombi na Uhakiki' : 'Request & Due Diligence' }}</h6>
                    <p class="text-muted small mb-0">{{ app()->getLocale() === 'sw' ? 'Wasilisha eneo la kiwanja chako, aina ya upimaji, na nyaraka za awali zilizopo.' : 'Submit your plot location, survey type, and existing town planning documents.' }}</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card p-4 border-0 rounded-4 shadow-sm h-100 bg-white">
                    <div class="rounded-circle bg-primary text-white mx-auto mb-3 d-flex align-items-center justify-content-center fw-bold fs-4" style="width: 54px; height: 54px;">2</div>
                    <h6 class="brand-font mb-2">{{ app()->getLocale() === 'sw' ? 'Kazi ya Uwanjani na Beacons' : 'Fieldwork & Beaconing' }}</h6>
                    <p class="text-muted small mb-0">{{ app()->getLocale() === 'sw' ? 'Wapimaji waliosajiliwa wanafika eneo la kiwanja na vifaa vya RTK GPS na kuweka alama za beacons.' : 'Licensed surveyors arrive with GNSS/RTK systems to fix concrete beacon monuments.' }}</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card p-4 border-0 rounded-4 shadow-sm h-100 bg-white">
                    <div class="rounded-circle bg-primary text-white mx-auto mb-3 d-flex align-items-center justify-content-center fw-bold fs-4" style="width: 54px; height: 54px;">3</div>
                    <h6 class="brand-font mb-2">{{ app()->getLocale() === 'sw' ? 'Hesabu na Mchoro wa CAD' : 'Computations & GIS' }}</h6>
                    <p class="text-muted small mb-0">{{ app()->getLocale() === 'sw' ? 'Uchakataji wa majira namba (UTM coordinates), ukokotoaji wa eneo na kuandaa michoro ya Deed Plan.' : 'Rigorous closure computations, UTM coordinates, and CAD deed drawings processed.' }}</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card p-4 border-0 rounded-4 shadow-sm h-100 bg-white">
                    <div class="rounded-circle bg-success text-white mx-auto mb-3 d-flex align-items-center justify-content-center fw-bold fs-4" style="width: 54px; height: 54px;">4</div>
                    <h6 class="brand-font mb-2">{{ app()->getLocale() === 'sw' ? 'Kukabidhi Ramani Zilizoidhinishwa' : 'Certified Delivery' }}</h6>
                    <p class="text-muted small mb-0">{{ app()->getLocale() === 'sw' ? 'Kukabidhiwa nyaraka na ramani zote tayari kwa ajili ya kuanza mchakato wa Hati Miliki.' : 'Official survey plan documents delivered ready for title registration.' }}</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- High-Conversion Request a Land Survey Form -->
<section class="py-5" id="requestSurveyForm">
    <div class="container">
        <div class="card p-4 p-md-5 border rounded-4 shadow-lg mx-auto bg-white" style="max-width: 850px;">
            <div class="text-center mb-4">
                <span class="badge bg-primary px-3 py-2 rounded-pill mb-2">{{ app()->getLocale() === 'sw' ? 'Ombi la Upimaji' : 'Direct Survey Request' }}</span>
                <h3 class="brand-font mb-1">{{ app()->getLocale() === 'sw' ? 'Tuma Ombi au Pata Bei ya Upimaji Ardhi' : 'Request a Land Survey or Cadastral Quote' }}</h3>
                <p class="text-muted small">{{ app()->getLocale() === 'sw' ? 'Jaza fomu hii fupi. Mpimaji wetu mkuu atawasiliana nawe kwa ajili ya kuthibitisha eneo na kutoa nukuu ya bei.' : 'Fill out the form below. Our principal surveyor will contact you to confirm coordinates and pricing.' }}</p>
            </div>

            <form action="{{ route('public.survey.request') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">{{ app()->getLocale() === 'sw' ? 'Majina Yako Bufuli *' : 'Your Full Name *' }}</label>
                        <input type="text" name="name" class="form-control" placeholder="Juma Rashid" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">{{ app()->getLocale() === 'sw' ? 'Namba ya Simu (WhatsApp) *' : 'Phone Number (WhatsApp) *' }}</label>
                        <input type="text" name="phone" class="form-control" placeholder="0784 100 200" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">{{ app()->getLocale() === 'sw' ? 'Barua Pepe' : 'Email Address' }}</label>
                        <input type="email" name="email" class="form-control" placeholder="juma@example.com">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">{{ app()->getLocale() === 'sw' ? 'Eneo la Kiwanja / Mkoa *' : 'Property Location / Region *' }}</label>
                        <input type="text" name="location" class="form-control" placeholder="{{ app()->getLocale() === 'sw' ? 'Mfano: Kihonda, Morogoro au Gezaulole, Kigamboni' : 'e.g. Kihonda, Morogoro or Gezaulole, Kigamboni' }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">{{ app()->getLocale() === 'sw' ? 'Aina ya Upimaji Unaohitajika *' : 'Type of Survey Needed *' }}</label>
                        <select name="survey_type" class="form-select" required>
                            <option value="Boundary Beacon Relocation">{{ app()->getLocale() === 'sw' ? 'Kurudisha Mipaka na Beacons Zilizopotea' : 'Boundary Beacon Relocation & Fixation' }}</option>
                            <option value="Cadastral Title Survey">{{ app()->getLocale() === 'sw' ? 'Upimaji wa Hati Miliki (Cadastral)' : 'Cadastral Title Survey (Right of Occupancy)' }}</option>
                            <option value="Land Subdivision">{{ app()->getLocale() === 'sw' ? 'Kugawanya Shamba / Kiwanja (Subdivision)' : 'Land Subdivision & Plot Splitting' }}</option>
                            <option value="Topographical Survey">{{ app()->getLocale() === 'sw' ? 'Upimaji wa Miinuko na Topografia' : 'Topographical & Contour Survey' }}</option>
                            <option value="Construction Setting-Out">{{ app()->getLocale() === 'sw' ? 'Upimaji wa Misingi ya Ujenzi' : 'Construction Setting-Out' }}</option>
                            <option value="Deed Due Diligence">{{ app()->getLocale() === 'sw' ? 'Uhakiki wa Majira Namba (Coordinates)' : 'Deed Due Diligence & Coordinate Check' }}</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">{{ app()->getLocale() === 'sw' ? 'Ukubwa wa Eneo (Kadirio)' : 'Approximate Land Size' }}</label>
                        <input type="text" name="approx_land_size" class="form-control" placeholder="{{ app()->getLocale() === 'sw' ? 'Mfano: Mita za mraba 600, Ekari 2, Hekta 10' : 'e.g. 600 Sqm, 2 Acres, 10 Hectares' }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">{{ app()->getLocale() === 'sw' ? 'Tarehe Unayopendelea Upimaji Ufanyike' : 'Preferred Survey Fieldwork Date' }}</label>
                        <input type="date" name="preferred_date" class="form-control" min="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">{{ app()->getLocale() === 'sw' ? 'Hali ya Nyaraka Zilizopo' : 'Existing Documentation Status' }}</label>
                        <select class="form-select">
                            <option>{{ app()->getLocale() === 'sw' ? 'Hati Miliki Ipo Mkononi' : 'Clean Title Deed in Hand' }}</option>
                            <option>{{ app()->getLocale() === 'sw' ? 'Mchoro wa Mipango Miji (TP) Upo' : 'Town Planning (TP) Drawing Available' }}</option>
                            <option>{{ app()->getLocale() === 'sw' ? 'Mkataba wa Mauzo wa Serikali ya Mtaa Tu' : 'Local Government Sales Agreement Only' }}</option>
                            <option>{{ app()->getLocale() === 'sw' ? 'Ardhi ya Kiasili Isiyopimwa' : 'Unsurveyed Customary Land' }}</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold text-muted">{{ app()->getLocale() === 'sw' ? 'Weka Alama kwenye Ramani ya Kidijitali (Si lazima)' : 'Pin Your Plot Location on Digital Map (Optional but Recommended)' }}</label>
                        <div id="surveyPinMap" class="rounded-3 border mb-2" style="height: 260px;"></div>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">Latitude</span>
                                    <input type="number" step="0.00000001" name="latitude" id="pinLatitude" class="form-control" placeholder="-6.7924">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">Longitude</span>
                                    <input type="number" step="0.00000001" name="longitude" id="pinLongitude" class="form-control" placeholder="39.2083">
                                </div>
                            </div>
                        </div>
                        <small class="text-muted">{{ app()->getLocale() === 'sw' ? 'Bofya mahali popote kwenye ramani kuweka alama ya eneo la kiwanja chako.' : 'Click anywhere on the map above to place a landmark pin for the field surveyors.' }}</small>
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-bold text-muted">{{ app()->getLocale() === 'sw' ? 'Maelezo ya Ziada & Maelekezo *' : 'Description & Special Instructions *' }}</label>
                        <textarea name="description" rows="3" class="form-control" placeholder="{{ app()->getLocale() === 'sw' ? 'Tafadhali eleza maelekezo yoyote ya ziada au alama za karibu...' : 'Please mention any landmark, missing beacons, neighboring plots, or specific survey deliverables required...' }}" required></textarea>
                    </div>
                    <div class="col-12 text-center mt-4">
                        <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5 fw-bold shadow">
                            <i class="bi bi-compass me-1"></i> {{ app()->getLocale() === 'sw' ? 'Tuma Ombi la Upimaji Ardhi' : 'Submit Land Survey Request' }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const defaultLat = -6.7924;
    const defaultLng = 39.2083;
    const map = L.map('surveyPinMap').setView([defaultLat, defaultLng], 12);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    let marker = null;

    map.on('click', function(e) {
        const lat = e.latlng.lat.toFixed(6);
        const lng = e.latlng.lng.toFixed(6);

        if (marker) {
            marker.setLatLng(e.latlng);
        } else {
            marker = L.marker(e.latlng).addTo(map);
        }

        document.getElementById('pinLatitude').value = lat;
        document.getElementById('pinLongitude').value = lng;
    });
});
</script>
@endsection
