@extends('layouts.public')

@section('title', 'Professional Land Survey & Cadastral Mapping Services in Tanzania')
@section('meta_description', 'Certified geomatics land survey services in Tanzania. Boundary beacon relocation, cadastral surveys, subdivision plans, and GIS mapping.')

@section('content')
<!-- Hero Section -->
<section class="py-5 text-white position-relative" style="background: linear-gradient(135deg, #091224 0%, #064e3b 100%);">
    <div class="container position-relative py-4" style="z-index: 2;">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <span class="badge bg-success px-3 py-2 rounded-pill mb-2">
                    <i class="bi bi-compass me-1"></i> Certified Geomatics & Cadastral Surveyors
                </span>
                <h1 class="brand-font display-5 fw-bold text-white mb-3">Professional Land Survey & GIS Mapping Services</h1>
                <p class="lead text-white-50 mb-4" style="line-height: 1.7;">
                    Protect your property investments with high-precision GNSS/RTK GPS boundary surveys, beacon relocation, land subdivisions, and certified town planning drawings across Tanzania.
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="#requestSurveyForm" class="btn btn-warning btn-lg rounded-pill px-4 fw-bold shadow">
                        <i class="bi bi-pencil-square me-1"></i> Request a Land Survey
                    </a>
                    <a href="https://wa.me/{{ setting('contact_whatsapp', '255784100200') }}?text={{ urlencode('Hello, I would like to consult with a licensed land surveyor regarding my plot.') }}" target="_blank" class="btn btn-outline-light btn-lg rounded-pill px-4 fw-semibold">
                        <i class="bi bi-whatsapp me-1"></i> Consult on WhatsApp
                    </a>
                </div>
            </div>
            <div class="col-lg-5 text-center">
                <div class="card p-4 rounded-4 bg-white bg-opacity-10 border border-light border-opacity-25 text-white shadow-lg">
                    <i class="bi bi-compass text-warning display-4 mb-2"></i>
                    <h4 class="brand-font mb-1">{{ $recentSurveysCount }}+ Survey Projects</h4>
                    <p class="small text-white-50 mb-0">Delivered across Dar es Salaam, Morogoro, Arusha, Dodoma, and Coastal Regions.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Survey Specializations -->
<section class="py-5 bg-white border-bottom">
    <div class="container">
        <div class="text-center max-w-700 mx-auto mb-5">
            <span class="section-tag">Survey Capabilities</span>
            <h2 class="brand-font mb-2">Our Land Survey & Geospatial Services</h2>
            <p class="text-muted">Conducted in strict compliance with the Tanzania Land Survey Ordinance and Ministry of Lands standards.</p>
        </div>

        <div class="row g-4">
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 p-4 border rounded-4 shadow-sm hover-shadow transition">
                    <i class="bi bi-bounding-box-circles text-primary fs-2 mb-3"></i>
                    <h5 class="brand-font mb-2">Boundary Survey & Beaconing</h5>
                    <p class="text-muted small mb-0">Accurate beacon relocation, monument fixation, boundary conflict resolution, and perimeter verification using high-precision RTK GPS.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 p-4 border rounded-4 shadow-sm hover-shadow transition">
                    <i class="bi bi-map text-success fs-2 mb-3"></i>
                    <h5 class="brand-font mb-2">Cadastral Plot Surveys</h5>
                    <p class="text-muted small mb-0">Full cadastral surveys for title deed applications (Right of Occupancy), deed plan preparation, and official Ministry approvals.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 p-4 border rounded-4 shadow-sm hover-shadow transition">
                    <i class="bi bi-diagram-3 text-warning fs-2 mb-3"></i>
                    <h5 class="brand-font mb-2">Land Subdivision & Estate Master Plans</h5>
                    <p class="text-muted small mb-0">Subdivision of large agricultural farms and residential tracts into individual titled building plots with road reserves.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 p-4 border rounded-4 shadow-sm hover-shadow transition">
                    <i class="bi bi-layers text-info fs-2 mb-3"></i>
                    <h5 class="brand-font mb-2">Topographical & Contour Surveys</h5>
                    <p class="text-muted small mb-0">Detailed elevation models, contour drawings, and digital terrain mapping for architectural planning and civil engineering.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 p-4 border rounded-4 shadow-sm hover-shadow transition">
                    <i class="bi bi-building-up text-danger fs-2 mb-3"></i>
                    <h5 class="brand-font mb-2">Engineering & Construction Setting-Out</h5>
                    <p class="text-muted small mb-0">Setting out building foundations, road alignment corridors, drainage networks, and structural control points.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 p-4 border rounded-4 shadow-sm hover-shadow transition">
                    <i class="bi bi-globe-americas text-dark fs-2 mb-3"></i>
                    <h5 class="brand-font mb-2">GIS Mapping & Geospatial Analytics</h5>
                    <p class="text-muted small mb-0">Interactive web GIS mapping, spatial database development, land use classification, and drone photogrammetry orthomosaics.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Survey Workflow Protocol -->
<section class="py-5 bg-light border-bottom">
    <div class="container">
        <div class="text-center max-w-700 mx-auto mb-5">
            <span class="section-tag">Rigorous Workflow</span>
            <h2 class="brand-font mb-2">How Our Land Survey Process Works</h2>
            <p class="text-muted">Four transparent stages from initial coordinates search to official deed approval.</p>
        </div>

        <div class="row g-4 text-center">
            <div class="col-md-3">
                <div class="card p-4 border-0 rounded-4 shadow-sm h-100 bg-white">
                    <div class="rounded-circle bg-primary text-white mx-auto mb-3 d-flex align-items-center justify-content-center fw-bold fs-4" style="width: 54px; height: 54px;">1</div>
                    <h6 class="brand-font mb-2">Request & Due Diligence</h6>
                    <p class="text-muted small mb-0">Submit your plot location, survey type, and existing town planning documents.</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card p-4 border-0 rounded-4 shadow-sm h-100 bg-white">
                    <div class="rounded-circle bg-primary text-white mx-auto mb-3 d-flex align-items-center justify-content-center fw-bold fs-4" style="width: 54px; height: 54px;">2</div>
                    <h6 class="brand-font mb-2">Fieldwork & Beaconing</h6>
                    <p class="text-muted small mb-0">Licensed surveyors arrive with GNSS/RTK systems to fix concrete beacon monuments.</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card p-4 border-0 rounded-4 shadow-sm h-100 bg-white">
                    <div class="rounded-circle bg-primary text-white mx-auto mb-3 d-flex align-items-center justify-content-center fw-bold fs-4" style="width: 54px; height: 54px;">3</div>
                    <h6 class="brand-font mb-2">Computations & GIS</h6>
                    <p class="text-muted small mb-0">Rigorous closure computations, UTM coordinates, and CAD deed drawings processed.</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card p-4 border-0 rounded-4 shadow-sm h-100 bg-white">
                    <div class="rounded-circle bg-success text-white mx-auto mb-3 d-flex align-items-center justify-content-center fw-bold fs-4" style="width: 54px; height: 54px;">4</div>
                    <h6 class="brand-font mb-2">Certified Delivery</h6>
                    <p class="text-muted small mb-0">Official survey plan documents delivered ready for title registration.</p>
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
                <span class="badge bg-primary px-3 py-2 rounded-pill mb-2">Direct Survey Request</span>
                <h3 class="brand-font mb-1">Request a Land Survey or Cadastral Quote</h3>
                <p class="text-muted small">Fill out the form below. Our principal surveyor will contact you to confirm coordinates and pricing.</p>
            </div>

            <form action="{{ route('public.survey.request') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">Your Full Name *</label>
                        <input type="text" name="name" class="form-control" placeholder="Rashid Mwinyi" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">Phone Number (WhatsApp) *</label>
                        <input type="text" name="phone" class="form-control" placeholder="+255 7..." required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">Email Address</label>
                        <input type="email" name="email" class="form-control" placeholder="rashid@example.com">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">Property Location / Region *</label>
                        <input type="text" name="location" class="form-control" placeholder="e.g. Kihonda, Morogoro or Gezaulole, Kigamboni" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">Type of Survey Needed *</label>
                        <select name="survey_type" class="form-select" required>
                            <option value="Boundary Beacon Relocation">Boundary Beacon Relocation & Fixation</option>
                            <option value="Cadastral Title Survey">Cadastral Title Survey (Right of Occupancy)</option>
                            <option value="Land Subdivision">Land Subdivision & Plot Splitting</option>
                            <option value="Topographical Survey">Topographical & Contour Survey</option>
                            <option value="Construction Setting-Out">Construction Setting-Out</option>
                            <option value="Deed Due Diligence">Deed Due Diligence & Coordinate Check</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">Approximate Land Size</label>
                        <input type="text" name="approx_land_size" class="form-control" placeholder="e.g. 600 Sqm, 2 Acres, 10 Hectares">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">Preferred Survey Fieldwork Date</label>
                        <input type="date" name="preferred_date" class="form-control" min="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">Existing Documentation Status</label>
                        <select class="form-select">
                            <option>Clean Title Deed in Hand</option>
                            <option>Town Planning (TP) Drawing Available</option>
                            <option>Local Government Sales Agreement Only</option>
                            <option>Unsurveyed Customary Land</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold text-muted">Description & Special Instructions *</label>
                        <textarea name="description" rows="3" class="form-control" placeholder="Please mention any landmark, missing beacons, neighboring plots, or specific survey deliverables required..." required></textarea>
                    </div>
                    <div class="col-12 text-center mt-4">
                        <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5 fw-bold shadow">
                            <i class="bi bi-compass me-1"></i> Submit Land Survey Request
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection
