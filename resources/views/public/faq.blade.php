@extends('layouts.public')

@section('title', __('pages.faq_page_title'))
@section('meta_description', __('pages.faq_hero_lead'))

@section('content')
<section class="page-hero hero-img-plot py-4 py-md-5 text-center">
    <div class="container py-2 py-md-3">
        <span class="badge bg-white bg-opacity-10 border border-white border-opacity-25 text-white px-3 py-2 rounded-pill mb-2">{{ __('pages.help_center') }}</span>
        <h1 class="brand-font fw-bold mb-2">{{ __('pages.faq_title') }}</h1>
        <p class="lead text-white-50 mx-auto mb-0 max-w-2xl">{{ __('pages.faq_hero_lead') }}</p>
    </div>
</section>

<div class="container py-4 py-md-5">
    <div class="max-w-850 mx-auto">
        <!-- Search -->
        <div class="card p-3 rounded-4 bg-white shadow-sm mb-4 mb-md-5">
            <form action="{{ route('public.faq') }}" method="GET" class="filter-bar" role="search">
                <label for="faqSearch" class="visually-hidden">{{ __('pages.search_questions') }}</label>
                <input type="search" id="faqSearch" name="q" class="form-control" placeholder="{{ __('pages.faq_search_placeholder') }}" value="{{ request('q') }}">
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary px-4 fw-bold"><i class="bi bi-search me-1"></i> {{ __('pages.search') }}</button>
                    @if(request('q'))
                        <a href="{{ route('public.faq') }}" class="btn btn-outline-secondary" aria-label="{{ __('pages.reset_filters') }}"><i class="bi bi-x-lg"></i></a>
                    @endif
                </div>
            </form>
        </div>

        @forelse($faqs as $category => $categoryFaqs)
            @php $accordionId = 'accordion' . Str::slug($category ?: 'general'); @endphp
            <section class="mb-4 mb-md-5">
                <h2 class="brand-font fs-5 text-primary mb-3"><i class="bi bi-folder2-open me-2"></i> {{ $category ?: __('pages.general') }}</h2>
                <div class="accordion accordion-flush shadow-sm rounded-4 overflow-hidden border bg-white" id="{{ $accordionId }}">
                    @foreach($categoryFaqs as $index => $faq)
                        <div class="accordion-item">
                            <h3 class="accordion-header" id="heading{{ $faq->id }}">
                                <button class="accordion-button {{ $index !== 0 ? 'collapsed' : '' }} fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $faq->id }}" aria-expanded="{{ $index === 0 ? 'true' : 'false' }}" aria-controls="collapse{{ $faq->id }}">
                                    {{ $faq->question }}
                                </button>
                            </h3>
                            <div id="collapse{{ $faq->id }}" class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}" data-bs-parent="#{{ $accordionId }}">
                                <div class="accordion-body text-secondary leading-relaxed">{{ $faq->answer }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @empty
            <div class="empty-state bg-white rounded-4 border shadow-sm">
                <div class="empty-icon"><i class="bi bi-question-circle"></i></div>
                <h5>{{ __('pages.no_questions_found') }}</h5>
                <p class="small mb-3">{{ __('pages.no_questions_help') }}</p>
                <a href="{{ route('public.faq') }}" class="btn btn-primary rounded-pill px-4">{{ __('pages.view_all_faqs') }}</a>
            </div>
        @endforelse

        <!-- Still need help -->
        <div class="card rounded-4 p-3 p-md-4 bg-dark text-white border-0 mt-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="min-w-0">
                    <h2 class="brand-font fs-5 text-white mb-1">{{ __('pages.still_need_help') }}</h2>
                    <p class="text-white-50 small mb-0">{{ __('pages.still_need_help_text') }}</p>
                </div>
                <div class="d-grid d-sm-flex gap-2 flex-grow-1 flex-sm-grow-0">
                    <a href="https://wa.me/{{ setting('contact_whatsapp', '255784100200') }}?text={{ urlencode(__('pages.whatsapp_contact_message')) }}" target="_blank" rel="noopener" class="btn btn-whatsapp rounded-pill px-4 fw-bold"><i class="bi bi-whatsapp me-1"></i> WhatsApp</a>
                    <a href="{{ route('public.contact') }}" class="btn btn-outline-light rounded-pill px-4 fw-bold">{{ __('pages.contact_us') }}</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
