@extends('layouts.app')

@section('title', __('admin.documents_title'))

@section('content')
@php
    $documentTypes = [
        'Title Deed' => __('admin.doc_title_deed'),
        'Survey Plan' => __('admin.doc_survey_plan'),
        'Sale Agreement' => __('admin.doc_sale_agreement'),
        'Lease Agreement' => __('admin.doc_lease_agreement'),
        'NIDA Copy' => __('admin.doc_nida_copy'),
        'TIN Certificate' => __('admin.doc_tin_certificate'),
        'Passport Copy' => __('admin.doc_passport_copy'),
        'Valuation Report' => __('admin.doc_valuation_report'),
        'TP Drawing' => __('admin.doc_tp_drawing'),
        'Other' => __('admin.doc_other'),
    ];
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
                                @foreach($documentTypes as $value => $label)
                                    <option value="{{ $value }}" @selected(old('document_type', 'Title Deed') == $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="docFile" class="form-label fw-semibold small">{{ __('admin.field_file') }}</label>
                            <input type="file" name="file" id="docFile" class="form-control @error('file') is-invalid @enderror" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,image/*,application/pdf">
                            <div class="form-text">{{ __('admin.file_hint') }}</div>
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
                                    $isImage = str_starts_with((string) $doc->mime_type, 'image/');
                                    $sizeKb = (float) $doc->file_size / 1024;
                                    $sizeLabel = $sizeKb >= 1024 ? number_format($sizeKb / 1024, 1).' MB' : number_format($sizeKb, 0).' KB';
                                @endphp
                                <tr>
                                    <td class="stack-primary">
                                        <div class="d-flex align-items-start gap-2 min-w-0">
                                            <i class="bi {{ $isImage ? 'bi-file-earmark-image text-info' : 'bi-file-earmark-pdf text-danger' }} fs-5 flex-shrink-0"></i>
                                            <div class="min-w-0">
                                                <div class="fw-bold line-clamp-2">{{ $doc->file_name }}</div>
                                                <div class="text-muted small text-truncate max-w-500">{{ $doc->file_path }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="badge bg-light text-dark border">{{ $documentTypes[$doc->category] ?? ($doc->category ?: __('admin.general_document')) }}</span></td>
                                    <td class="text-muted small">{{ $sizeLabel }}</td>
                                    <td class="small">{{ $doc->user?->name ?? '-' }}</td>
                                    <td class="text-muted small">{{ $doc->created_at?->format('d M Y H:i') }}</td>
                                    <td class="stack-actions text-end">
                                        <a href="{{ asset('storage/'.$doc->file_path) }}" target="_blank" rel="noopener" class="btn btn-light border btn-icon" title="{{ __('admin.download') }}" aria-label="{{ __('admin.download') }}"><i class="bi bi-download"></i></a>
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
@endsection
