@extends('layouts.public')

@section('title', __('marketplace.saved_properties'))

@section('content')
<section class="page-hero hero-img-plot py-4">
    <div class="container">
        <div class="page-header mb-0">
            <div class="min-w-0">
                <h1 class="brand-font text-white mb-1"><i class="bi bi-heart-fill text-danger me-2"></i>{{ __('marketplace.saved_properties') }}</h1>
                <p class="text-white-50 small mb-0">{{ __('marketplace.saved_subtitle') }}</p>
            </div>
            @if($properties->count() > 0)
                <div class="page-actions">
                    <button type="button" class="btn btn-outline-light rounded-pill" id="clearAllFavoritesBtn">
                        <i class="bi bi-trash me-1"></i>{{ __('marketplace.clear_all_saved') }}
                    </button>
                </div>
            @endif
        </div>
    </div>
</section>

<div class="container py-4">
    <div id="favoritesLoading" class="text-center py-5 d-none" role="status">
        <div class="spinner-border text-primary"></div>
        <div class="text-muted small mt-2">{{ __('marketplace.loading_saved') }}</div>
    </div>

    <div id="favoritesContent">
        @if($properties->count() > 0)
            <p class="text-muted small mb-3">{{ __('marketplace.showing_saved', ['count' => $properties->count()]) }}</p>

            <div class="row g-3 g-md-4">
                @foreach($properties as $p)
                    <div class="col-12 col-sm-6 col-lg-4">
                        @include('public.partials.property-card', ['p' => $p])
                    </div>
                @endforeach
            </div>
        @else
            <div class="card empty-state max-w-xl mx-auto" id="noFavoritesCard">
                <div class="empty-icon"><i class="bi bi-heart"></i></div>
                <h5>{{ __('marketplace.no_saved_title') }}</h5>
                <p class="small mb-3">{{ __('marketplace.no_saved_hint') }}</p>
                <a href="{{ route('public.properties') }}" class="btn btn-primary rounded-pill px-4 fw-bold">{{ __('marketplace.explore_properties') }}</a>
            </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const favs = REMS.getFavorites();
    const urlParams = new URLSearchParams(window.location.search);
    const idsParam = urlParams.get('ids');

    const favsJoined = favs.join(',');
    if (favs.length > 0 && (!idsParam || idsParam !== favsJoined)) {
        $('#favoritesLoading').removeClass('d-none');
        $('#favoritesContent').addClass('d-none');
        window.location.href = '{{ route("public.favorites") }}?ids=' + favsJoined;
    }

    $('#clearAllFavoritesBtn').on('click', function () {
        localStorage.removeItem('rems_favorites');
        REMS.updateCounters();
        window.location.href = '{{ route("public.favorites") }}';
    });
});
</script>
@endsection
