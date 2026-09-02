@extends('layouts.app')

@section('title', 'Landlord & Property Owner Portal')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="brand-font mb-1"><i class="bi bi-building-check text-primary me-2"></i>Landlord & Property Owner Portal</h3>
        <p class="text-muted small mb-0">Owner: <strong>{{ $owner->full_name }}</strong> ({{ $owner->company_name ?? 'Individual' }}) &bull; Upload land parcels for sale and track review approvals</p>
    </div>
    <div>
        <button type="button" class="btn btn-primary btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#submitPlotModal">
            <i class="bi bi-cloud-arrow-up me-1"></i> Submit New Plot for Sale
        </button>
    </div>
</div>

<!-- Portfolio KPI Summary -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card p-3 shadow-sm bg-white border-0">
            <div class="text-muted small text-uppercase fw-bold">Submitted Properties</div>
            <h3 class="fw-bold mb-0 text-dark">{{ $properties->count() }}</h3>
            <small class="text-muted">Total plots & residential units listed</small>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-3 shadow-sm bg-white border-0">
            <div class="text-muted small text-uppercase fw-bold">Under Review</div>
            <h3 class="fw-bold mb-0 text-warning">{{ $properties->where('submission_status', 'Under Review')->count() }}</h3>
            <small class="text-muted">Pending deed proof validation by admin</small>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-3 shadow-sm bg-white border-0">
            <div class="text-muted small text-uppercase fw-bold">Live Published</div>
            <h3 class="fw-bold mb-0 text-success">{{ $properties->where('is_published', true)->count() }}</h3>
            <small class="text-muted">Active in public marketplace catalog</small>
        </div>
    </div>
</div>

<!-- Submitted Real Estate Assets & Reviews -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="brand-font mb-0">My Real Estate Assets & Submission Status</h5>
        <span class="badge bg-primary">{{ $properties->count() }} Properties</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Code</th>
                    <th>Property Title</th>
                    <th>Type / Category</th>
                    <th>Area Size</th>
                    <th>Price / Valuation</th>
                    <th>Review Stage</th>
                    <th>Marketplace Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($properties as $pr)
                    <tr>
                        <td class="fw-bold text-primary font-monospace">{{ $pr->property_code }}</td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $pr->title }}</div>
                            <small class="text-muted">{{ $pr->address }}, {{ $pr->city }}</small>
                        </td>
                        <td>{{ $pr->propertyType?->name ?? 'Land' }}</td>
                        <td>{{ $pr->area_size }} {{ $pr->area_unit }}</td>
                        <td class="fw-bold text-success">{{ $pr->formatted_price }}</td>
                        <td>
                            @if($pr->submission_status === 'Under Review')
                                <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i>Under Review</span>
                            @elseif($pr->submission_status === 'Approved' || $pr->submission_status === 'Published')
                                <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Approved</span>
                            @elseif($pr->submission_status === 'Rejected')
                                <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Rejected</span>
                            @else
                                <span class="badge bg-secondary">{{ $pr->submission_status }}</span>
                            @endif
                        </td>
                        <td>
                            @if($pr->is_published)
                                <span class="badge bg-success-subtle text-success fw-bold">Live on Marketplace</span>
                            @else
                                <span class="badge bg-secondary-subtle text-muted">Unpublished</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center py-4 text-muted">No plots submitted yet. Click 'Submit New Plot for Sale' above to start!</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Submit Plot for Sale (Owner Workflow) -->
<div class="modal fade" id="submitPlotModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('portals.owner.submit_property') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title brand-font">Submit Plot / Property for Sale</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted mb-3">Provide plot dimensions, price, GPS coordinates, and deed proofs. Your submission will enter an administrative validation review before publishing.</p>

                    <div class="row g-3 mb-3">
                        <div class="col-md-8">
                            <label class="form-label small fw-semibold">Property / Plot Title</label>
                            <input type="text" name="title" class="form-control" placeholder="e.g. 2 Acres Surveyed Beachfront Plot in Kigamboni" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Category Type</label>
                            <select name="property_type_id" class="form-select" required>
                                @foreach($propertyTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Listing Type</label>
                            <select name="listing_type" class="form-select">
                                <option value="Sale">Sale (Outright)</option>
                                <option value="Rent">Rent</option>
                                <option value="Lease">Long-term Lease</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Asking Price (TZS)</label>
                            <input type="number" name="price" class="form-control" placeholder="e.g. 45000000" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Area Size & Unit</label>
                            <div class="input-group">
                                <input type="number" step="0.01" name="area_size" class="form-control" placeholder="e.g. 1.5" required>
                                <select name="area_unit" class="form-select" style="max-width: 100px;">
                                    <option value="Acres">Acres</option>
                                    <option value="Sqm">Sqm</option>
                                    <option value="Hectares">Hectares</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">City / Region</label>
                            <input type="text" name="city" class="form-control" placeholder="e.g. Dar es Salaam" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Physical Address / Landmark</label>
                            <input type="text" name="address" class="form-control" placeholder="e.g. Cheka, Kigamboni Coastal Strip" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Zoning Type</label>
                            <select name="zoning" class="form-select">
                                <option value="Residential">Residential</option>
                                <option value="Commercial">Commercial</option>
                                <option value="Agricultural">Agricultural</option>
                                <option value="Industrial">Industrial</option>
                                <option value="Mixed Use">Mixed Use</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">GPS Latitude (Optional)</label>
                            <input type="number" step="0.00000001" name="latitude" class="form-control" placeholder="e.g. -6.82350">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">GPS Longitude (Optional)</label>
                            <input type="number" step="0.00000001" name="longitude" class="form-control" placeholder="e.g. 39.26950">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Title Deed Proof / Cadastral Plan (PDF/Image)</label>
                            <input type="file" name="deed_document" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                            <small class="text-muted">Official Ministry/Municipal title deed or survey plan for verification</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Primary Plot Photograph</label>
                            <input type="file" name="property_image" class="form-control" accept="image/*">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Detailed Description & Local Utilities (Water, Electricity, Road Access)</label>
                        <textarea name="description" class="form-control" rows="4" placeholder="Describe the terrain, nearby infrastructure, water supply, electricity grid connection, and accessibility..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-semibold">Submit Plot for Verification</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
