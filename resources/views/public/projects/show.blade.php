@extends('layouts.public')

@section('title', $project->title . ' - New Development')

@section('content')
<div class="bg-dark text-white py-4 mb-4">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb small text-white-50 mb-2">
                <li class="breadcrumb-item"><a href="{{ route('public.home') }}" class="text-white-50 text-decoration-none">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('public.projects') }}" class="text-white-50 text-decoration-none">Projects</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">{{ $project->title }}</li>
            </ol>
        </nav>
        <div class="d-flex flex-wrap justify-content-between align-items-center">
            <div>
                <span class="badge bg-primary mb-2">{{ $project->project_status }}</span>
                <h1 class="brand-font text-white display-6 fw-bold mb-1">{{ $project->title }}</h1>
                <p class="text-white-50 small mb-0"><i class="bi bi-geo-alt-fill text-danger me-1"></i> {{ $project->location_name }}, {{ $project->city }} • Developed by <strong>{{ $project->developer_name }}</strong></p>
            </div>
            <div class="mt-3 mt-lg-0 text-lg-end">
                <span class="text-white-50 small d-block">Starting Price</span>
                <div class="fs-2 fw-bold text-warning brand-font">{{ $project->formatted_price }}</div>
            </div>
        </div>
    </div>
</div>

<div class="container pb-5">
    <div class="row g-4">
        <div class="col-lg-8">
            <!-- Hero Image -->
            <div class="card border rounded-4 overflow-hidden mb-4 shadow-sm">
                <img src="{{ $project->hero_image_url }}" alt="{{ $project->title }}" class="w-100 object-fit-cover" style="max-height: 440px;" onerror="this.src='https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?w=1200&auto=format&fit=crop&q=80'">
            </div>

            <!-- Overview -->
            <div class="card border rounded-4 p-4 mb-4 bg-white shadow-sm">
                <h4 class="brand-font mb-3"><i class="bi bi-info-circle text-primary me-2"></i> Project Overview</h4>
                <div class="text-secondary mb-4" style="line-height: 1.8;">
                    {!! nl2br(e($project->description)) !!}
                </div>

                <div class="row g-3 text-center border-top pt-4">
                    <div class="col-6 col-md-3 border-end">
                        <span class="text-muted small">Total Units / Plots</span>
                        <div class="fw-bold fs-5 text-dark">{{ $project->total_units }}</div>
                    </div>
                    <div class="col-6 col-md-3 border-end">
                        <span class="text-muted small">Available Units</span>
                        <div class="fw-bold fs-5 text-success">{{ $project->available_units }}</div>
                    </div>
                    <div class="col-6 col-md-3 border-end">
                        <span class="text-muted small">Development Status</span>
                        <div class="fw-bold fs-6 text-primary">{{ $project->project_status }}</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <span class="text-muted small">Target Completion</span>
                        <div class="fw-bold fs-6 text-dark">{{ $project->expected_completion_date ? $project->expected_completion_date->format('M Y') : 'Phase 1' }}</div>
                    </div>
                </div>
            </div>

            <!-- Unit Types & Pricing Matrix -->
            @if($project->unit_types_json && is_array($project->unit_types_json))
                <div class="card border rounded-4 p-4 mb-4 bg-white shadow-sm">
                    <h4 class="brand-font mb-3"><i class="bi bi-houses text-success me-2"></i> Unit Types & Master Options</h4>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Unit / Plot Type</th>
                                    <th>Size</th>
                                    <th>Starting Price</th>
                                    <th>Availability</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($project->unit_types_json as $ut)
                                    <tr>
                                        <td><strong>{{ $ut['name'] ?? 'Unit' }}</strong></td>
                                        <td>{{ $ut['size'] ?? '-' }}</td>
                                        <td class="text-success fw-bold">{{ format_currency($ut['price'] ?? 0, $project->currency) }}</td>
                                        <td><span class="badge bg-success-subtle text-success">{{ $ut['available'] ?? 'Available' }} Units</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- Amenities -->
            @if($project->amenities_json && is_array($project->amenities_json))
                <div class="card border rounded-4 p-4 mb-4 bg-white shadow-sm">
                    <h4 class="brand-font mb-3"><i class="bi bi-stars text-warning me-2"></i> Master Plan Amenities</h4>
                    <div class="row g-2">
                        @foreach($project->amenities_json as $am)
                            <div class="col-md-6">
                                <div class="p-2 rounded-3 bg-light border d-flex align-items-center gap-2">
                                    <i class="bi bi-check-circle-fill text-success"></i>
                                    <span class="small fw-medium">{{ $am }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Location Map -->
            @if($project->latitude && $project->longitude)
                <div class="card border rounded-4 p-4 mb-4 bg-white shadow-sm">
                    <h4 class="brand-font mb-3"><i class="bi bi-geo-alt-fill text-danger me-2"></i> Project Master Location</h4>
                    <div id="projectMap" style="height: 350px;" class="rounded-4 border"></div>
                </div>
            @endif
        </div>

        <!-- Sidebar Inquiry -->
        <div class="col-lg-4">
            <div class="sticky-top" style="top: 90px;">
                <div class="card border rounded-4 p-4 shadow-sm bg-white mb-4">
                    <h5 class="brand-font mb-2">Inquire About This Project</h5>
                    <p class="text-muted small mb-3">Get brochures, site plan drawings, and reservation details from the developer.</p>

                    <form action="{{ route('public.inquire') }}" method="POST">
                        @csrf
                        <input type="hidden" name="project_id" value="{{ $project->id }}">

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Full Name *</label>
                            <input type="text" name="name" class="form-control form-control-sm" placeholder="John Doe" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Phone Number (WhatsApp) *</label>
                            <input type="text" name="phone" class="form-control form-control-sm" placeholder="+255 7..." required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Email Address</label>
                            <input type="email" name="email" class="form-control form-control-sm" placeholder="john@example.com">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Message *</label>
                            <textarea name="message" rows="3" class="form-control form-control-sm" required>I would like to receive pricing sheets and book a site visit for {{ $project->title }}.</textarea>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold shadow-sm">
                            <i class="bi bi-send-fill me-1"></i> Request Developer Info Pack
                        </button>
                    </form>
                </div>

                <a href="https://wa.me/{{ setting('contact_whatsapp', '255784100200') }}?text={{ urlencode('Hello, I am interested in development project: ' . $project->title . ' (' . url()->current() . ')') }}" target="_blank" class="btn btn-success w-100 py-3 fw-bold rounded-4 shadow-sm d-flex align-items-center justify-content-center gap-2">
                    <i class="bi bi-whatsapp fs-5"></i> Chat with Project Advisor
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@if($project->latitude && $project->longitude)
<script>
document.addEventListener('DOMContentLoaded', function () {
    const lat = {{ $project->latitude }};
    const lng = {{ $project->longitude }};
    const map = L.map('projectMap').setView([lat, lng], 14);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    L.marker([lat, lng]).addTo(map).bindPopup("<strong>{{ addslashes($project->title) }}</strong>").openPopup();
});
</script>
@endif
@endsection
