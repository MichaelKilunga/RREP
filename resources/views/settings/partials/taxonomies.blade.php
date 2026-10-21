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
                            @forelse($taxonomyTerms[$group] ?? [] as $term)
                                <div class="d-flex align-items-center gap-2">
                                    <form action="{{ route('settings.taxonomies.update', $term) }}" method="POST" class="flex-grow-1 d-flex gap-2 m-0" data-no-loading>
                                        @csrf
                                        @method('PUT')
                                        <input type="text" name="name" value="{{ $term->name }}" class="form-control form-control-sm" required maxlength="120" aria-label="{{ $meta['title'] }}">
                                        <button type="submit" class="btn btn-light border btn-sm btn-icon flex-shrink-0" title="{{ __('settings.taxonomy_rename') }}" aria-label="{{ __('settings.taxonomy_rename') }}"><i class="bi bi-check-lg text-success"></i></button>
                                    </form>
                                    <form action="{{ route('settings.taxonomies.destroy', $term) }}" method="POST" class="m-0" data-no-loading onsubmit="return confirm(@json(__('settings.taxonomy_delete_confirm')));">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-light border btn-sm btn-icon" title="{{ __('app.delete') }}" aria-label="{{ __('app.delete') }}"><i class="bi bi-trash text-danger"></i></button>
                                    </form>
                                </div>
                            @empty
                                <p class="text-muted small mb-0">{{ __('settings.taxonomy_empty') }}</p>
                            @endforelse

                            <form action="{{ route('settings.taxonomies.store') }}" method="POST" class="d-flex gap-2 mt-auto pt-2 border-top m-0" data-no-loading>
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
