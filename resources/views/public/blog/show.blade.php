@extends('layouts.public')

@section('title', $article->title)
@section('meta_description', $article->excerpt)
@section('og_image', $article->featured_image_url)
@section('og_type', 'article')

@section('content')
<div class="bg-white border-bottom py-3 mb-4">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb small mb-0 flex-nowrap text-nowrap overflow-auto">
                <li class="breadcrumb-item"><a href="{{ route('public.home') }}" class="text-decoration-none">{{ __('pages.home') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('public.blog') }}" class="text-decoration-none">{{ __('pages.insights') }}</a></li>
                <li class="breadcrumb-item active text-truncate max-w-500" aria-current="page">{{ $article->title }}</li>
            </ol>
        </nav>
    </div>
</div>

<div class="container pb-5">
    <div class="row g-4">
        <!-- Article -->
        <div class="col-12 col-lg-8 min-w-0">
            <article class="card p-3 p-md-4 p-lg-5 rounded-4 bg-white shadow-sm mb-4">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-3 small text-muted">
                    <span class="badge bg-primary">{{ $article->category }}</span>
                    <span><i class="bi bi-clock me-1"></i> {{ trans_choice('pages.min_read', (int) $article->reading_time_minutes, ['minutes' => $article->reading_time_minutes]) }}</span>
                    @if($article->published_at)
                        <span>&bull; {{ __('pages.published') }} {{ $article->published_at->format('d M Y') }}</span>
                    @endif
                </div>

                <h1 class="brand-font fw-bold mb-4 text-dark fs-2">{{ $article->title }}</h1>

                <div class="d-flex align-items-center gap-3 border-top border-bottom py-3 mb-4">
                    <span class="btn-icon bg-primary text-white fw-bold">{{ strtoupper(substr($article->author_name, 0, 1)) }}</span>
                    <div class="min-w-0">
                        <h2 class="brand-font fs-6 mb-0">{{ $article->author_name }}</h2>
                        <small class="text-muted">{{ $article->author_role ?: __('pages.editorial_team') }}</small>
                    </div>
                </div>

                <div class="ratio ratio-16x9 rounded-4 overflow-hidden mb-4 bg-light">
                    <img src="{{ $article->featured_image_url }}" alt="{{ $article->title }}" class="object-fit-cover" onerror="this.onerror=null;this.src='{{ asset('images/blog-placeholder.jpg') }}'">
                </div>

                <div class="article-content text-secondary leading-relaxed fs-6">
                    {!! nl2br(e($article->content)) !!}
                </div>

                <div class="border-top pt-4 mt-4 mt-md-5 d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div class="min-w-0">
                        <strong class="text-dark small d-block mb-1">{{ __('pages.found_helpful') }}</strong>
                        <span class="text-muted small">{{ __('pages.share_with_buyers') }}</span>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="https://wa.me/?text={{ urlencode($article->title . ' ' . url()->current()) }}" target="_blank" rel="noopener" class="btn btn-whatsapp btn-sm rounded-pill px-3 fw-bold">
                            <i class="bi bi-whatsapp me-1"></i> WhatsApp
                        </a>
                        <a href="https://twitter.com/intent/tweet?text={{ urlencode($article->title) }}&url={{ urlencode(url()->current()) }}" target="_blank" rel="noopener" class="btn btn-dark btn-sm rounded-pill px-3 fw-bold">
                            <i class="bi bi-twitter-x me-1"></i> {{ __('pages.post') }}
                        </a>
                        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-bold share-property-btn" data-url="{{ url()->current() }}" data-title="{{ $article->title }}">
                            <i class="bi bi-share me-1"></i> {{ __('pages.share') }}
                        </button>
                    </div>
                </div>
            </article>
        </div>

        <!-- Sidebar -->
        <div class="col-12 col-lg-4">
            <div class="sticky-lg-top" style="top: 84px;">
                <div class="card border-0 rounded-4 p-3 p-md-4 text-white shadow-sm mb-4 bg-dark">
                    <i class="bi bi-compass fs-2 mb-2 text-warning"></i>
                    <h2 class="brand-font fs-5 text-white mb-2">{{ __('pages.need_due_diligence') }}</h2>
                    <p class="text-white-50 small mb-3">{{ __('pages.need_due_diligence_help') }}</p>
                    <a href="{{ route('public.services.land_survey') }}" class="btn btn-warning fw-bold w-100 py-2">{{ __('app.request_a_land_survey') }}</a>
                </div>

                @if($relatedArticles->count())
                    <div class="card rounded-4 p-3 p-md-4 bg-white shadow-sm mb-4">
                        <h2 class="brand-font fs-6 mb-3">{{ __('pages.related_guides') }}</h2>
                        <div class="d-flex flex-column gap-3">
                            @foreach($relatedArticles as $ra)
                                <a href="{{ route('public.blog.show', $ra->slug) }}" class="d-flex gap-3 align-items-center text-decoration-none">
                                    <span class="rounded-3 overflow-hidden bg-light flex-shrink-0" style="width: 64px; height: 64px;">
                                        <img src="{{ $ra->featured_image_url }}" alt="{{ $ra->title }}" class="w-100 h-100 object-fit-cover" loading="lazy" onerror="this.onerror=null;this.src='{{ asset('images/blog-placeholder.jpg') }}'">
                                    </span>
                                    <span class="min-w-0">
                                        <span class="brand-font d-block text-dark small fw-semibold line-clamp-2">{{ $ra->title }}</span>
                                        <small class="text-muted">{{ trans_choice('pages.min_read', (int) $ra->reading_time_minutes, ['minutes' => $ra->reading_time_minutes]) }}</small>
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
