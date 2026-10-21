@php
    $taxonomyGroups = [
        'project_type' => ['title' => __('settings.taxonomy_project_types'), 'desc' => __('settings.taxonomy_project_types_desc'), 'icon' => 'bi-map'],
        'service_category' => ['title' => __('settings.taxonomy_service_categories'), 'desc' => __('settings.taxonomy_service_categories_desc'), 'icon' => 'bi-briefcase'],
        'document_type' => ['title' => __('settings.taxonomy_document_types'), 'desc' => __('settings.taxonomy_document_types_desc'), 'icon' => 'bi-folder-check'],
    ];
    $taxonomyTerms = \App\Models\TaxonomyTerm::orderBy('display_order')->orderBy('name')->get()->groupBy('group');
@endphp

<div class="card rounded-4 overflow-hidden mb-4">
    <div class="card-header">
        <h5 class="brand-font mb-0"><i class="bi bi-tags-fill text-primary me-1.5"></i>{{ __('settings.taxonomies_title') }}</h5>
        <small class="text-muted fw-normal">{{ __('settings.taxonomies_subtitle') }}</small>
    </div>
    <div class="card-body p-3 p-md-4">
        <div class="row g-4">
            @foreach($taxonomyGroups as $group => $meta)
                <div class="col-12 col-lg-4">
                    <div class="border rounded-4 h-100 d-flex flex-column">
                        <div class="p-3 border-bottom bg-light rounded-top-4">
                            <h6 class="fw-bold mb-0"><i class="bi {{ $meta['icon'] }} text-primary me-1"></i>{{ $meta['title'] }}</h6>
                            <small class="text-muted">{{ $meta['desc'] }}</small>
                        </div>
                        <div class="p-3 flex-grow-1 d-flex flex-column gap-2">
                            <div class="d-flex flex-column gap-2 tax-term-list">
                                @foreach($taxonomyTerms[$group] ?? [] as $term)
                                    <div class="d-flex align-items-center gap-2 tax-term-row">
                                        <form action="{{ route('settings.taxonomies.update', $term) }}" method="POST" class="flex-grow-1 d-flex gap-2 m-0 tax-rename-form" data-no-loading>
                                            @csrf
                                            @method('PUT')
                                            <input type="text" name="name" value="{{ $term->name }}" class="form-control form-control-sm" required maxlength="120" aria-label="{{ $meta['title'] }}">
                                            <button type="submit" class="btn btn-light border btn-sm btn-icon flex-shrink-0" title="{{ __('settings.taxonomy_rename') }}" aria-label="{{ __('settings.taxonomy_rename') }}"><i class="bi bi-check-lg text-success"></i></button>
                                        </form>
                                        <form action="{{ route('settings.taxonomies.destroy', $term) }}" method="POST" class="m-0 tax-delete-form" data-no-loading>
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-light border btn-sm btn-icon" title="{{ __('app.delete') }}" aria-label="{{ __('app.delete') }}"><i class="bi bi-trash text-danger"></i></button>
                                        </form>
                                    </div>
                                @endforeach
                            </div>

                            <form action="{{ route('settings.taxonomies.store') }}" method="POST" class="d-flex gap-2 mt-auto pt-2 border-top m-0 tax-add-form" data-no-loading>
                                @csrf
                                <input type="hidden" name="group" value="{{ $group }}">
                                <input type="text" name="name" class="form-control form-control-sm" placeholder="{{ __('settings.taxonomy_add_placeholder') }}" required maxlength="120" aria-label="{{ $meta['title'] }}">
                                <button type="submit" class="btn btn-primary btn-sm fw-bold flex-shrink-0"><i class="bi bi-plus-lg me-1"></i>{{ __('app.add') }}</button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <p class="text-muted small mb-0 mt-3"><i class="bi bi-info-circle me-1"></i>{{ __('settings.taxonomy_note') }}</p>
    </div>
</div>

{{-- Row blueprint for terms added without a page reload --}}
<template id="taxTermRowTemplate">
    <div class="d-flex align-items-center gap-2 tax-term-row">
        <form action="" method="POST" class="flex-grow-1 d-flex gap-2 m-0 tax-rename-form" data-no-loading>
            <input type="hidden" name="_method" value="PUT">
            <input type="text" name="name" value="" class="form-control form-control-sm" required maxlength="120">
            <button type="submit" class="btn btn-light border btn-sm btn-icon flex-shrink-0" title="{{ __('settings.taxonomy_rename') }}" aria-label="{{ __('settings.taxonomy_rename') }}"><i class="bi bi-check-lg text-success"></i></button>
        </form>
        <form action="" method="POST" class="m-0 tax-delete-form" data-no-loading>
            <input type="hidden" name="_method" value="DELETE">
            <button type="submit" class="btn btn-light border btn-sm btn-icon" title="{{ __('app.delete') }}" aria-label="{{ __('app.delete') }}"><i class="bi bi-trash text-danger"></i></button>
        </form>
    </div>
</template>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const deleteConfirm = @json(__('settings.taxonomy_delete_confirm'));
    const toast = (msg, icon) => { if (window.RREP && window.RREP.toast) { window.RREP.toast(msg, icon || 'success'); } };

    function send(form, extra) {
        const body = new FormData(form);
        (extra || []).forEach(([k, v]) => body.set(k, v));
        return fetch(form.action, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: body,
        }).then(function (res) {
            if (!res.ok) { return res.json().then(j => Promise.reject(j)); }
            return res.json();
        });
    }

    function wireRow(row) {
        row.querySelector('.tax-rename-form').addEventListener('submit', function (e) {
            e.preventDefault();
            send(this).then(data => toast(data.message)).catch(err => toast(err.message || 'Error', 'error'));
        });
        row.querySelector('.tax-delete-form').addEventListener('submit', function (e) {
            e.preventDefault();
            if (!window.confirm(deleteConfirm)) { return; }
            send(this).then(function (data) { row.remove(); toast(data.message); }).catch(err => toast(err.message || 'Error', 'error'));
        });
    }

    document.querySelectorAll('.tax-term-row').forEach(wireRow);

    document.querySelectorAll('.tax-add-form').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const input = form.querySelector('input[name="name"]');
            send(form).then(function (data) {
                const row = document.getElementById('taxTermRowTemplate').content.firstElementChild.cloneNode(true);
                row.querySelector('.tax-rename-form').action = data.update_url;
                row.querySelector('.tax-rename-form input[name="name"]').value = data.name;
                row.querySelector('.tax-delete-form').action = data.destroy_url;
                form.closest('.d-flex.flex-column').querySelector('.tax-term-list').appendChild(row);
                wireRow(row);
                input.value = '';
                input.focus();
                toast(data.message);
            }).catch(err => toast(err.message || 'Error', 'error'));
        });
    });
});
</script>
