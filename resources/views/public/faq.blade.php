@extends('layouts.public')

@section('title', 'Frequently Asked Questions (FAQ)')

@section('content')
<div class="bg-dark text-white py-5 text-center" style="background: linear-gradient(135deg, #091224 0%, #1e3a8a 100%);">
    <div class="container py-3">
        <span class="badge bg-primary px-3 py-2 rounded-pill mb-2">Help Center</span>
        <h1 class="brand-font display-5 fw-bold text-white mb-2">Frequently Asked Questions</h1>
        <p class="lead text-white-50 mx-auto mb-0" style="max-width: 650px;">
            Clear answers to common questions about buying property, land surveying, title deeds, renting, and advertising.
        </p>
    </div>
</div>

<div class="container py-5">
    <div class="max-w-850 mx-auto">
        <!-- Search FAQs -->
        <div class="card p-3 border rounded-4 bg-white shadow-sm mb-5">
            <form action="{{ route('public.faq') }}" method="GET" class="d-flex gap-2">
                <input type="text" name="q" class="form-control" placeholder="Search questions (e.g. title deeds, beacons, payment, viewing)..." value="{{ request('q') }}">
                <button type="submit" class="btn btn-primary px-4 fw-bold"><i class="bi bi-search me-1"></i> Search</button>
            </form>
        </div>

        @forelse($faqs as $category => $categoryFaqs)
            <div class="mb-5">
                <h4 class="brand-font text-primary mb-3"><i class="bi bi-folder2-open me-2"></i> {{ $category }}</h4>
                <div class="accordion shadow-sm rounded-4 overflow-hidden" id="accordion{{ Str::slug($category) }}">
                    @foreach($categoryFaqs as $index => $faq)
                        <div class="accordion-item border-0 border-bottom">
                            <h2 class="accordion-header" id="heading{{ $faq->id }}">
                                <button class="accordion-button {{ $index !== 0 ? 'collapsed' : '' }} fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $faq->id }}">
                                    {{ $faq->question }}
                                </button>
                            </h2>
                            <div id="collapse{{ $faq->id }}" class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}" data-bs-parent="#accordion{{ Str::slug($category) }}">
                                <div class="accordion-body text-secondary" style="line-height: 1.8;">
                                    {{ $faq->answer }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="text-center py-5 text-muted">
                <h5>No questions found matching your search.</h5>
                <a href="{{ route('public.faq') }}" class="btn btn-primary btn-sm mt-2">View All FAQs</a>
            </div>
        @endforelse
    </div>
</div>
@endsection
