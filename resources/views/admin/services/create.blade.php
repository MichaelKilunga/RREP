@extends('layouts.app')

@section('title', __('admin.create_service'))

@section('content')
@php
    $categories = \App\Models\TaxonomyTerm::options('service_category', ['Land', 'Surveying', 'Planning', 'Consultancy', 'Legal', 'Development', 'Property']);
@endphp

<div class="page-header">
    <div class="min-w-0">
        <h1 class="brand-font">{{ __('admin.create_service') }}</h1>
        <p class="text-muted small mb-0">{{ __('admin.create_service_subtitle') }}</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.services.index') }}" class="btn btn-light border"><i class="bi bi-arrow-left me-1"></i> {{ __('admin.back_to_list') }}</a>
        <button type="submit" form="serviceForm" class="btn btn-primary"><i class="bi bi-check2-circle me-1"></i> {{ __('admin.create_service') }}</button>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger d-flex align-items-start gap-2" role="alert">
        <i class="bi bi-exclamation-triangle-fill fs-5"></i>
        <div class="small">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    </div>
@endif

<form action="{{ route('admin.services.store') }}" method="POST" enctype="multipart/form-data" id="serviceForm">
    @csrf

    <div class="row g-4">
        <div class="col-12 col-xl-8">
    <div class="card mb-4">
        <div class="card-header brand-font"><i class="bi bi-briefcase me-2 text-primary"></i>{{ __('admin.section_basics') }}</div>
        <div class="card-body">
            <p class="text-muted small">{{ __('admin.required_hint') }}</p>
            <div class="row g-3">
                <div class="col-12 col-md-7">
                    <label for="serviceTitle" class="form-label fw-semibold small">{{ __('admin.field_service_title') }} *</label>
                    <input type="text" name="title" id="serviceTitle" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" required placeholder="{{ __('admin.field_service_title_placeholder') }}" autocomplete="off">
                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12 col-md-5">
                    <label for="serviceCategory" class="form-label fw-semibold small">{{ __('admin.field_category') }} *</label>
                    <select name="category" id="serviceCategory" class="form-select" required>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}" @selected(old('category', $categories[0] ?? '') == $cat)>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12">
                    <label for="shortDescription" class="form-label fw-semibold small">{{ __('admin.field_short_description') }} *</label>
                    <textarea name="short_description" id="shortDescription" class="form-control @error('short_description') is-invalid @enderror" rows="3" required placeholder="{{ __('admin.field_short_description_placeholder') }}">{{ old('short_description') }}</textarea>
                    @error('short_description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label for="subServices" class="form-label fw-semibold small">{{ __('admin.field_sub_services') }}</label>
                    <input type="text" name="sub_services" id="subServices" class="form-control" value="{{ old('sub_services') }}" placeholder="{{ __('admin.field_sub_services_placeholder') }}">
                    <div class="form-text">{{ __('admin.field_sub_services_hint') }}</div>
                </div>
                <div class="col-12 col-md-6">
                    <label for="ctaText" class="form-label fw-semibold small">{{ __('admin.service_cta_text') }}</label>
                    <input type="text" name="cta_text" id="ctaText" class="form-control" maxlength="60" value="{{ old('cta_text') }}" placeholder="Omba Bei">
                    <div class="form-text">{{ __('admin.service_cta_text_hint') }}</div>
                </div>
                <div class="col-12 col-md-6">
                    <label for="ctaUrl" class="form-label fw-semibold small">{{ __('admin.service_cta_url') }}</label>
                    <input type="text" name="cta_url" id="ctaUrl" class="form-control" value="{{ old('cta_url') }}" placeholder="/contact">
                    <div class="form-text">{{ __('admin.service_cta_url_hint') }}</div>
                </div>
            </div>
        </div>
    </div>

        </div>

        <div class="col-12 col-xl-4">
            <!-- Publishing -->
            <div class="card mb-4">
                <div class="card-header brand-font"><i class="bi bi-rocket-takeoff me-2 text-primary"></i>{{ __('admin.section_publishing') }}</div>
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-12">
                            <label for="serviceThumb" class="form-label fw-semibold small">{{ __('admin.service_thumbnail') }}</label>
                            <input type="file" name="thumbnail_file" id="serviceThumb" class="form-control @error('thumbnail_file') is-invalid @enderror" accept="image/*">
                            @error('thumbnail_file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text">{{ __('admin.service_thumbnail_hint') }}</div>
                        </div>
                <div class="col-12">
                    <label for="serviceIcon" class="form-label fw-semibold small">{{ __('admin.field_icon') }} *</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-geo-alt-fill" id="serviceIconPreview"></i></span>
                        <input type="text" name="icon" id="serviceIcon" class="form-control font-monospace" value="{{ old('icon', 'bi-geo-alt-fill') }}" required placeholder="bi-rulers" autocomplete="off">
                    </div>
                    <div class="form-text">{{ __('admin.field_icon_hint') }}</div>
                </div>
                <div class="col-12">
                    <label for="displayOrder" class="form-label fw-semibold small">{{ __('admin.field_display_order') }}</label>
                    <input type="number" name="display_order" id="displayOrder" class="form-control" value="{{ old('display_order', 1) }}" min="0">
                </div>
                <div class="col-12">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" id="isActive" value="1" @checked(old('is_active', true))>
                        <label class="form-check-label fw-semibold" for="isActive">{{ __('admin.service_active') }}</label>
                    </div>
                </div>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary fw-bold"><i class="bi bi-check2-circle me-1"></i> {{ __('admin.create_service') }}</button>
                        <a href="{{ route('admin.services.index') }}" class="btn btn-light border">{{ __('app.cancel') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('serviceIcon');
    const preview = document.getElementById('serviceIconPreview');
    if (input && preview) {
        input.addEventListener('input', function () {
            preview.className = 'bi ' + input.value.trim();
        });
    }
});
</script>
@endsection
