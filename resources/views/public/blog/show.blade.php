@extends('layouts.public')

@section('title', $article->title)
@section('meta_description', $article->excerpt)
@section('og_image', $article->featured_image_url)

@section('content')
<div class="bg-white border-bottom py-3 mb-4">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb small mb-0">
                <li class="breadcrumb-item"><a href="{{ route('public.home') }}" class="text-decoration-none">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('public.blog') }}" class="text-decoration-none">Insights</a></li>
                <li class="breadcrumb-item active text-truncate" style="max-width: 350px;">{{ $article->title }}</li>
            </ol>
        </nav>
    </div>
</div>

<div class="container pb-5">
    <div class="row g-5">
        <!-- Article Main Content -->
        <div class="col-lg-8">
            <div class="card p-4 p-md-5 border rounded-4 bg-white shadow-sm mb-4">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <span class="badge bg-primary">{{ $article->category }}</span>
                    <span class="text-muted small"><i class="bi bi-clock me-1"></i> {{ $article->reading_time_minutes }} min read</span>
                    <span class="text-muted small">• Published {{ $article->published_at ? $article->published_at->format('M d, Y') : '' }}</span>
                </div>

                <h1 class="brand-font display-6 fw-bold mb-4 text-dark">{{ $article->title }}</h1>

                <div class="d-flex align-items-center gap-3 border-top border-bottom py-3 mb-4">
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 44px; height: 44px;">
                        {{ substr($article->author_name, 0, 1) }}
                    </div>
                    <div>
                        <h6 class="brand-font mb-0">{{ $article->author_name }}</h6>
                        <small class="text-muted">{{ $article->author_role }}</small>
                    </div>
                </div>

                <!-- Featured Image -->
                <div class="rounded-4 overflow-hidden mb-4" style="max-height: 400px;">
                    <img src="{{ $article->featured_image_url }}" alt="{{ $article->title }}" class="w-100 object-fit-cover" onerror="this.src='https://images.unsplash.com/photo-1560518883-ce09059eeffa?w=1000&auto=format&fit=crop&q=80'">
                </div>

                <!-- Article Body -->
                <div class="article-content text-secondary" style="line-height: 1.9; font-size: 1.05rem;">
                    {!! nl2br(e($article->content)) !!}
                </div>

                <!-- Share Footer -->
                <div class="border-top pt-4 mt-5 d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div>
                        <strong class="text-dark small d-block mb-1">Found this guide helpful?</strong>
                        <span class="text-muted small">Share with other property buyers & investors</span>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="https://wa.me/?text={{ urlencode($article->title . ' ' . url()->current()) }}" target="_blank" class="btn btn-success btn-sm rounded-pill px-3 fw-bold">
                            <i class="bi bi-whatsapp me-1"></i> WhatsApp
                        </a>
                        <a href="https://twitter.com/intent/tweet?text={{ urlencode($article->title) }}&url={{ urlencode(url()->current()) }}" target="_blank" class="btn btn-dark btn-sm rounded-pill px-3 fw-bold">
                            <i class="bi bi-twitter-x me-1"></i> Post
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="sticky-top" style="top: 90px;">
                <!-- Need a Survey Card -->
                <div class="card border-0 rounded-4 p-4 text-white shadow-sm mb-4" style="background: linear-gradient(135deg, #064e3b 0%, #047857 100%);">
                    <i class="bi bi-compass fs-2 mb-2 text-warning"></i>
                    <h5 class="brand-font text-white mb-2">Need Cadastral Due Diligence?</h5>
                    <p class="text-white-50 small mb-3">Our licensed geomatics surveyors conduct boundary beacon relocation and official deed search across Tanzania.</p>
                    <a href="{{ route('public.services.land_survey') }}" class="btn btn-warning btn-sm fw-bold w-100 py-2">
                        Request Land Survey
                    </a>
                </div>

                <!-- Related Articles -->
                @if($relatedArticles->count())
                    <div class="card border rounded-4 p-4 bg-white shadow-sm mb-4">
                        <h5 class="brand-font mb-3">Related Guides</h5>
                        <div class="d-flex flex-column gap-3">
                            @foreach($relatedArticles as $ra)
                                <div class="d-flex gap-3 align-items-center">
                                    <div style="width: 60px; height: 60px; min-width: 60px;" class="rounded-3 overflow-hidden bg-light">
                                        <img src="{{ $ra->featured_image_url }}" alt="{{ $ra->title }}" class="w-100 h-100 object-fit-cover" onerror="this.src='https://images.unsplash.com/photo-1560518883-ce09059eeffa?w=200&auto=format&fit=crop&q=80'">
                                    </div>
                                    <div>
                                        <h6 class="brand-font mb-1" style="font-size: 0.85rem;">
                                            <a href="{{ route('public.blog.show', $ra->slug) }}" class="text-dark text-decoration-none hover-primary">
                                                {{ Str::limit($ra->title, 55) }}
                                            </a>
                                        </h6>
                                        <small class="text-muted">{{ $ra->reading_time_minutes }} min read</small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
