@extends('layouts.public')

@section('title', 'Real Estate Insights, Buyer Guides & Market Reports')

@section('content')
<div class="bg-dark text-white py-5 text-center" style="background: linear-gradient(135deg, #091224 0%, #1e3a8a 100%);">
    <div class="container py-3">
        <span class="badge bg-primary px-3 py-2 rounded-pill mb-2">Knowledge & Advisory</span>
        <h1 class="brand-font display-5 fw-bold text-white mb-2">Real Estate Insights & Buyer Guides</h1>
        <p class="lead text-white-50 mx-auto mb-0" style="max-width: 650px;">
            Expert articles on property acquisition, cadastral land surveying, title deeds in Tanzania, and real estate market trends.
        </p>
    </div>
</div>

<div class="container py-5">
    <!-- Category Filter Bar -->
    <div class="d-flex flex-wrap gap-2 mb-4">
        <a href="{{ route('public.blog') }}" class="btn btn-sm {{ !request('category') ? 'btn-primary' : 'btn-outline-secondary' }} rounded-pill px-3 fw-semibold">
            All Topics
        </a>
        @foreach($categories as $cat)
            <a href="{{ route('public.blog', ['category' => $cat]) }}" class="btn btn-sm {{ request('category') === $cat ? 'btn-primary' : 'btn-outline-secondary' }} rounded-pill px-3 fw-semibold">
                {{ $cat }}
            </a>
        @endforeach
    </div>

    <!-- Featured Top Article if available and not searching -->
    @if(isset($featuredArticle) && $featuredArticle && !request('category') && !request('q'))
        <div class="card border rounded-4 overflow-hidden shadow-sm mb-5 bg-white">
            <div class="row g-0">
                <div class="col-lg-6" style="min-height: 320px; background: #e2e8f0;">
                    <img src="{{ $featuredArticle->featured_image_url }}" class="w-100 h-100 object-fit-cover" alt="{{ $featuredArticle->title }}" onerror="this.src='https://images.unsplash.com/photo-1560518883-ce09059eeffa?w=800&auto=format&fit=crop&q=80'">
                </div>
                <div class="col-lg-6 p-4 p-md-5 d-flex flex-column justify-content-center">
                    <span class="badge bg-danger align-self-start mb-2"><i class="bi bi-star-fill me-1"></i> Featured Insight</span>
                    <span class="text-primary small fw-bold mb-1">{{ $featuredArticle->category }}</span>
                    <h3 class="brand-font mb-3">
                        <a href="{{ route('public.blog.show', $featuredArticle->slug) }}" class="text-dark text-decoration-none hover-primary">
                            {{ $featuredArticle->title }}
                        </a>
                    </h3>
                    <p class="text-muted small mb-4" style="line-height: 1.7;">
                        {{ $featuredArticle->excerpt }}
                    </p>
                    <div class="d-flex align-items-center justify-content-between text-muted small mt-auto border-top pt-3">
                        <span><i class="bi bi-person me-1"></i> {{ $featuredArticle->author_name }}</span>
                        <span><i class="bi bi-clock me-1"></i> {{ $featuredArticle->reading_time_minutes }} min read</span>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Articles Grid -->
    <div class="row g-4">
        @forelse($articles as $art)
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border rounded-4 overflow-hidden shadow-sm hover-shadow transition bg-white d-flex flex-column">
                    <div style="height: 200px; background: #e2e8f0;" class="position-relative">
                        <img src="{{ $art->featured_image_url }}" alt="{{ $art->title }}" class="w-100 h-100 object-fit-cover" loading="lazy" onerror="this.src='https://images.unsplash.com/photo-1560518883-ce09059eeffa?w=600&auto=format&fit=crop&q=80'">
                        <span class="position-absolute top-0 start-0 m-3 badge bg-primary">{{ $art->category }}</span>
                    </div>
                    <div class="card-body p-4 d-flex flex-column">
                        <small class="text-muted mb-2"><i class="bi bi-clock me-1"></i> {{ $art->reading_time_minutes }} min read • {{ $art->published_at ? $art->published_at->format('M d, Y') : '' }}</small>
                        <h5 class="brand-font mb-2">
                            <a href="{{ route('public.blog.show', $art->slug) }}" class="text-dark text-decoration-none hover-primary">
                                {{ $art->title }}
                            </a>
                        </h5>
                        <p class="text-muted small mb-3 flex-grow-1">
                            {{ Str::limit($art->excerpt, 110) }}
                        </p>
                        <div class="mt-auto border-top pt-3 d-flex justify-content-between align-items-center">
                            <small class="text-muted">{{ $art->author_name }}</small>
                            <a href="{{ route('public.blog.show', $art->slug) }}" class="fw-bold text-primary text-decoration-none small">
                                Read Guide <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center text-muted py-5">
                <h5>No articles found matching this category.</h5>
            </div>
        @endforelse
    </div>

    <div class="mt-5 d-flex justify-content-center">
        {{ $articles->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
