@extends('layouts.app')

@section('title', __('admin.documents_title'))

@section('content')
@php
    $documentTypes = \App\Models\TaxonomyTerm::options('document_type', [
        'Title Deed', 'Survey Plan', 'Sale Agreement', 'Lease Agreement', 'NIDA Copy',
        'TIN Certificate', 'Passport Copy', 'Valuation Report', 'TP Drawing', 'Other',
    ]);
    $formOpen = $errors->any();
@endphp

<div class="page-header">
    <div class="min-w-0">
        <h1 class="brand-font"><i class="bi bi-folder-check text-primary me-2"></i>{{ __('admin.documents_title') }}</h1>
        <p class="text-muted small mb-0">{{ __('admin.documents_subtitle') }}</p>
    </div>
    <div class="page-actions">
        <button class="btn btn-primary d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#uploadDocPanel" aria-expanded="{{ $formOpen ? 'true' : 'false' }}" aria-controls="uploadDocPanel">
            <i class="bi bi-cloud-arrow-up me-1"></i> {{ __('admin.upload_document') }}
        </button>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-4 order-lg-2">
        <div class="collapse d-lg-block {{ $formOpen ? 'show' : '' }}" id="uploadDocPanel">
            <div class="card">
                <div class="card-header brand-font"><i class="bi bi-cloud-arrow-up me-2 text-primary"></i>{{ __('admin.upload_document') }}</div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger small py-2" role="alert">
                            @foreach($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif
                    <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data" id="documentUploadForm">
                        @csrf
                        <div class="mb-3">
                            <label for="docTitle" class="form-label fw-semibold small">{{ __('admin.field_document_title') }} *</label>
                            <input type="text" name="file_name" id="docTitle" class="form-control @error('file_name') is-invalid @enderror" value="{{ old('file_name') }}" placeholder="{{ __('admin.field_document_title_placeholder') }}" required autocomplete="off">
                            @error('file_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="docType" class="form-label fw-semibold small">{{ __('admin.field_document_type') }} *</label>
                            <select name="document_type" id="docType" class="form-select" required>
                                @foreach($documentTypes as $type)
                                    <option value="{{ $type }}" @selected(old('document_type', $documentTypes[0] ?? '') == $type)>{{ $type }}</option>
                                @endforeach
                            </select>
                            <div class="form-text"><a href="{{ route('settings.index') }}#taxonomies" class="text-decoration-none">{{ __('admin.manage_types_link') }}</a></div>
                        </div>
                        <div class="mb-3">
                            <label for="docFile" class="form-label fw-semibold small">{{ __('admin.field_file') }}</label>
                            <input type="file" name="file" id="docFile" class="form-control @error('file') is-invalid @enderror" required accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.csv,.txt,.jpg,.jpeg,.png,.webp,.gif,.zip,.kml,.kmz,.dwg,.dxf">
                            <div class="form-text">{{ __('admin.file_hint_wide') }}</div>
                            @error('file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary fw-bold"><i class="bi bi-cloud-arrow-up-fill me-1"></i> {{ __('admin.archive_document') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8 order-lg-1">
        <div class="card">
            @if($documents->isEmpty())
                <div class="empty-state">
                    <div class="empty-icon"><i class="bi bi-folder2-open"></i></div>
                    <h5>{{ __('admin.no_documents_title') }}</h5>
                    <p class="mb-3">{{ __('admin.no_documents_text') }}</p>
                    <button class="btn btn-primary d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#uploadDocPanel" aria-controls="uploadDocPanel"><i class="bi bi-cloud-arrow-up me-1"></i> {{ __('admin.upload_document') }}</button>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover table-stack align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('admin.document') }}</th>
                                <th>{{ __('admin.category') }}</th>
                                <th>{{ __('admin.file_size') }}</th>
                                <th>{{ __('admin.uploaded_by') }}</th>
                                <th>{{ __('admin.uploaded_at') }}</th>
                                <th class="text-end">{{ __('app.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($documents as $doc)
                                @php
                                    $mime = (string) $doc->mime_type;
                                    $isImage = str_starts_with($mime, 'image/');
                                    $isPdf = $mime === 'application/pdf';
                                    $docIcon = match (true) {
                                        $isImage => 'bi-file-earmark-image text-info',
                                        $isPdf => 'bi-file-earmark-pdf text-danger',
                                        str_contains($mime, 'word') => 'bi-file-earmark-word text-primary',
                                        str_contains($mime, 'sheet'), str_contains($mime, 'excel'), str_contains($mime, 'csv') => 'bi-file-earmark-spreadsheet text-success',
                                        str_contains($mime, 'presentation'), str_contains($mime, 'powerpoint') => 'bi-file-earmark-slides text-warning',
                                        str_contains($mime, 'zip'), str_contains($mime, 'compressed') => 'bi-file-earmark-zip text-secondary',
                                        default => 'bi-file-earmark-text text-secondary',
                                    };
                                    $docUrl = asset('storage/'.$doc->file_path);
                                    $sizeKb = (float) $doc->file_size / 1024;
                                    $sizeLabel = $sizeKb >= 1024 ? number_format($sizeKb / 1024, 1).' MB' : number_format($sizeKb, 0).' KB';
                                @endphp
                                <tr>
                                    <td class="stack-primary">
                                        <div class="d-flex align-items-start gap-2 min-w-0">
                                            <i class="bi {{ $docIcon }} fs-5 flex-shrink-0"></i>
                                            <div class="min-w-0">
                                                <div class="fw-bold line-clamp-2">{{ $doc->file_name }}</div>
                                                <div class="text-muted small text-truncate max-w-500">{{ $doc->file_path }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="badge bg-light text-dark border">{{ $doc->category ?: __('admin.general_document') }}</span></td>
                                    <td class="text-muted small">{{ $sizeLabel }}</td>
                                    <td class="small">{{ $doc->user?->name ?? '-' }}</td>
                                    <td class="text-muted small">{{ $doc->created_at?->format('d M Y H:i') }}</td>
                                    <td class="stack-actions text-end">
                                        <div class="d-inline-flex gap-1">
                                            @if($isImage || $isPdf)
                                                <button type="button" class="btn btn-light border btn-icon doc-preview-btn" data-url="{{ $docUrl }}" data-kind="{{ $isImage ? 'image' : 'pdf' }}" data-name="{{ $doc->file_name }}" title="{{ __('admin.preview') }}" aria-label="{{ __('admin.preview') }}"><i class="bi bi-eye"></i></button>
                                            @endif
                                            <a href="{{ $docUrl }}" download class="btn btn-light border btn-icon" title="{{ __('admin.download') }}" aria-label="{{ __('admin.download') }}"><i class="bi bi-download"></i></a>
                                            <form action="{{ route('documents.destroy', $doc) }}" method="POST" class="m-0 d-inline" data-no-loading onsubmit="return confirm(@json(__('admin.document_delete_confirm')));">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-light border btn-icon" title="{{ __('app.delete') }}" aria-label="{{ __('app.delete') }}"><i class="bi bi-trash text-danger"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($documents->hasPages())
                    <div class="p-3 border-top d-flex justify-content-center justify-content-md-end overflow-auto">
                        {{ $documents->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>

<!-- Document preview (images and PDFs open in-page) -->
<div class="modal fade" id="docPreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold text-truncate" id="docPreviewTitle"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('app.cancel') }}"></button>
            </div>
            <div class="modal-body p-0 text-center bg-dark bg-opacity-10">
                <img src="" alt="" id="docPreviewImage" class="img-fluid d-none" style="max-height: 78vh;">
                <iframe src="about:blank" id="docPreviewFrame" class="w-100 d-none border-0" style="height: 78vh;" title="{{ __('admin.preview') }}"></iframe>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var modalEl = document.getElementById('docPreviewModal');
        var img = document.getElementById('docPreviewImage');
        var frame = document.getElementById('docPreviewFrame');
        document.querySelectorAll('.doc-preview-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                document.getElementById('docPreviewTitle').textContent = btn.dataset.name;
                var isImage = btn.dataset.kind === 'image';
                img.classList.toggle('d-none', ! isImage);
                frame.classList.toggle('d-none', isImage);
                img.src = isImage ? btn.dataset.url : '';
                frame.src = isImage ? 'about:blank' : btn.dataset.url;
                bootstrap.Modal.getOrCreateInstance(modalEl).show();
            });
        });
        modalEl.addEventListener('hidden.bs.modal', function () {
            img.src = '';
            frame.src = 'about:blank';
        });
    });
</script>
@endsection
