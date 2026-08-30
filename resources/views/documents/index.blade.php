@extends('layouts.app')

@section('title', 'EDMS Document Vault')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="brand-font mb-1"><i class="bi bi-folder-check text-primary me-2"></i>EDMS Document & Digital Records Vault (BM-010)</h3>
        <p class="text-muted small mb-0">Secure electronic records storage for Title Deeds, Survey Cadastral Maps, and Sale Agreements</p>
    </div>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#uploadDocModal">
        <i class="bi bi-cloud-arrow-up-fill me-1"></i> Archive Document
    </button>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
            <thead class="table-light">
                <tr>
                    <th>Document Name</th>
                    <th>Category</th>
                    <th>File Size</th>
                    <th>MIME Type</th>
                    <th>Archived By</th>
                    <th>Uploaded At</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($documents as $doc)
                    <tr>
                        <td>
                            <div class="fw-bold"><i class="bi bi-file-earmark-pdf text-danger me-2"></i>{{ $doc->file_name }}</div>
                            <small class="text-muted">{{ $doc->file_path }}</small>
                        </td>
                        <td><span class="badge bg-light text-dark border">{{ $doc->category ?? 'General Document' }}</span></td>
                        <td>{{ round($doc->file_size / 1024, 1) }} KB</td>
                        <td><code>{{ $doc->mime_type }}</code></td>
                        <td>{{ $doc->user?->name }}</td>
                        <td>{{ $doc->created_at->format('d M Y H:i') }}</td>
                        <td class="text-end">
                            <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" class="btn btn-sm btn-light border"><i class="bi bi-download"></i></a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No documents uploaded to vault yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="uploadDocModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title brand-font">Archive Electronic Document</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Document Title *</label>
                        <input type="text" name="file_name" class="form-control" placeholder="e.g. Masaki Villa Title Deed Plan 2026" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Document Category *</label>
                        <select name="document_type" class="form-select" required>
                            <option value="Title Deed">Title Deed / Certificate of Occupancy</option>
                            <option value="Cadastral Survey Plan">Cadastral Survey Plan</option>
                            <option value="Sale Agreement">Sale & Purchase Agreement</option>
                            <option value="Lease Agreement">Lease Agreement Contract</option>
                            <option value="KYC Document">National ID / Passport / TIN Document</option>
                            <option value="Valuation Certificate">Valuation Certificate</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Attach File (PDF, DOCX, JPG)</label>
                        <input type="file" name="file" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Archive to Vault</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
