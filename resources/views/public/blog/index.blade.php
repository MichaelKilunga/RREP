@extends('layouts.public')

@section('title', __('pages.blog_page_title'))
@section('meta_description', __('pages.blog_hero_lead'))

@section('content')
<section class="page-hero hero-img-plot py-4 py-md-5 text-center">
    <div class="container py-2 py-md-3">
        <span class="badge bg-white bg-opacity-10 border border-white border-opacity-25 text-white px-3 py-2 rounded-pill mb-2">{{ __('pages.knowledge_advisory') }}</span>
        <h1 class="brand-font fw-bold mb-2">{!! __('pages.blog_title') !!}</h1>
        <p class="lead text-white-50 mx-auto mb-0 max-w-2xl">{{ __('pages.blog_hero_lead') }}</p>
    </div>
</section>

<div class="container py-4 py-md-5">
    <!-- Category chips -->
    <div class="scroll-x mb-4" role="navigation" aria-label="{{ __('pages.topics') }}">
        <a href="{{ route('public.blog') }}" class="btn btn-sm {{ !request('category') ? 'btn-primary' : 'btn-outline-secondary' }} rounded-pill px-3 fw-semibold">
            {{ __('pages.all_topics') }}
        </a>
        @foreach($categories as $cat)
            <a href="{{ route('public.blog', ['category' => $cat]) }}" class="btn btn-sm {{ request('category') === $cat ? 'btn-primary' : 'btn-outline-secondary' }} rounded-pill px-3 fw-semibold">
                {{ $cat }}
            </a>
        @endforeach
    </div>

    <!-- Featured article -->
    @if(isset($featuredArticle) && $featuredArticle && !request('category') && !request('q'))
        <div class="card rounded-4 overflow-hidden shadow-sm mb-4 mb-md-5 bg-white">
            <div class="row g-0">
                <div class="col-12 col-lg-6">
                    <a href="{{ route('public.blog.show', $featuredArticle->slug) }}" class="property-card-img-wrapper d-block h-100">
                        <img src="{{ $featuredArticle->featured_image_url }}" alt="{{ $featuredArticle->title }}" loading="lazy" onerror="this.onerror=null;this.src='{{ asset('images/blog-placeholder.jpg') }}'">
                    </a>
                </div>
                <div class="col-12 col-lg-6 p-4 p-md-5 d-flex flex-column justify-content-center">
                    <span class="badge bg-danger align-self-start mb-2"><i class="bi bi-star-fill me-1"></i> {{ __('pages.featured_insight') }}</span>
                    <span class="text-primary small fw-bold mb-1">{{ $featuredArticle->category }}</span>
                    <h2 class="brand-font fs-4 mb-3">
                        <a href="{{ route('public.blog.show', $featuredArticle->slug) }}" class="text-dark text-decoration-none hover-primary">{{ $featuredArticle->title }}</a>
                    </h2>
                    <p class="text-muted small mb-4 leading-relaxed line-clamp-3">{{ $featuredArticle->excerpt }}</p>
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 text-muted small mt-auto border-top pt-3">
                        <span><i class="bi bi-person me-1"></i> {{ $featuredArticle->author_name }}</span>
                        <span><i class="bi bi-clock me-1"></i> {{ trans_choice('pages.min_read', (int) $featuredArticle->reading_time_minutes, ['minutes' => $featuredArticle->reading_time_minutes]) }}</span>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Articles -->
    <div class="row g-3 g-md-4">
        @forelse($articles as $art)
            <div class="col-12 col-sm-6 col-lg-4">
                <article class="card h-100 rounded-4 overflow-hidden shadow-sm hover-shadow transition bg-white d-flex flex-column">
                    <a href="{{ route('public.blog.show', $art->slug) }}" class="property-card-img-wrapper d-block">
                        <img src="{{ $art->featured_image_url }}" alt="{{ $art->title }}" loading="lazy" onerror="this.onerror=null;this.src='{{ asset('images/blog-placeholder.jpg') }}'">
                        <span class="position-absolute top-0 start-0 m-3 badge bg-primary">{{ $art->category }}</span>
                    </a>
                    <div class="card-body p-3 p-md-4 d-flex flex-column">
                        <small class="text-muted mb-2">
                            <i class="bi bi-clock me-1"></i> {{ trans_choice('pages.min_read', (int) $art->reading_time_minutes, ['minutes' => $art->reading_time_minutes]) }}
                            @if($art->published_at) &bull; {{ $art->published_at->format('d M Y') }} @endif
                        </small>
                        <h2 class="brand-font fs-5 mb-2 line-clamp-2">
                            <a href="{{ route('public.blog.show', $art->slug) }}" class="text-dark text-decoration-none hover-primary">{{ $art->title }}</a>
                        </h2>
                        <p class="text-muted small mb-3 flex-grow-1 line-clamp-3">{{ $art->excerpt }}</p>
                        <div class="mt-auto border-top pt-3 d-flex justify-content-between align-items-center gap-2">
                            <small class="text-muted text-truncate">{{ $art->author_name }}</small>
                            <a href="{{ route('public.blog.show', $art->slug) }}" class="fw-bold text-primary text-decoration-none small flex-shrink-0">
                                {{ __('pages.read_guide') }} <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </article>
            </div>
        @empty
            <div class="col-12">
                <div class="empty-state bg-white rounded-4 border shadow-sm">
                    <div class="empty-icon"><i class="bi bi-journal-text"></i></div>
                    <h5>{{ __('pages.no_articles_found') }}</h5>
                    <p class="small mb-3">{{ __('pages.no_articles_help') }}</p>
                    <a href="{{ route('public.blog') }}" class="btn btn-primary rounded-pill px-4">{{ __('pages.all_topics') }}</a>
                </div>
            </div>
        @endforelse
    </div>

    @if($articles->hasPages())
        <div class="mt-4 mt-md-5 d-flex justify-content-center">
            {{ $articles->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
