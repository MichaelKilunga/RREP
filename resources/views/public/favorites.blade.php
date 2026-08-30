@extends('layouts.public')

@section('title', 'My Saved Properties')

@section('content')
<div class="bg-dark text-white py-4 mb-4">
    <div class="container">
        <h2 class="brand-font text-white mb-1"><i class="bi bi-heart-fill text-danger me-2"></i> My Saved Properties</h2>
        <p class="text-white-50 small mb-0">Manage your shortlisted real estate listings and land parcels</p>
    </div>
</div>

<div class="container pb-5">
    <div id="favoritesLoading" class="text-center py-5 d-none">
        <div class="spinner-border text-primary" role="status"></div>
        <div class="text-muted small mt-2">Loading your saved properties...</div>
    </div>

    <div id="favoritesContent">
        @if($properties->count() > 0)
            <div class="d-flex justify-content-between align-items-center mb-4">
                <span class="text-muted small">Showing <strong>{{ $properties->count() }}</strong> saved properties</span>
                <button type="button" class="btn btn-outline-danger btn-sm rounded-pill" id="clearAllFavoritesBtn">
                    <i class="bi bi-trash me-1"></i> Clear All Saved
                </button>
            </div>

            <div class="row g-4">
                @foreach($properties as $p)
                    <div class="col-md-6 col-lg-4">
                        @include('public.partials.property-card', ['p' => $p])
                    </div>
                @endforeach
            </div>
        @else
            <div class="card p-5 text-center border rounded-4 bg-white shadow-sm max-w-500 mx-auto" id="noFavoritesCard">
                <div class="rounded-circle bg-light text-danger p-3 mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 70px; height: 70px; font-size: 2rem;">
                    <i class="bi bi-heart"></i>
                </div>
                <h4 class="brand-font mb-2">You Haven't Saved Any Properties Yet</h4>
                <p class="text-muted small mb-4">Click the heart icon on any property listing across the marketplace to add it to your personal shortlist.</p>
                <a href="{{ route('public.properties') }}" class="btn btn-primary btn-sm rounded-pill px-4 fw-bold mx-auto">
                    Explore Properties
                </a>
            </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const favs = REMS.getFavorites();
    const urlParams = new URLSearchParams(window.location.search);
    const idsParam = urlParams.get('ids');

    const favsJoined = favs.join(',');
    if (favs.length > 0 && (!idsParam || idsParam !== favsJoined)) {
        $('#favoritesLoading').removeClass('d-none');
        $('#favoritesContent').addClass('d-none');
        window.location.href = '{{ route("public.favorites") }}?ids=' + favsJoined;
    }

    $('#clearAllFavoritesBtn').on('click', function() {
        localStorage.removeItem('rems_favorites');
        REMS.updateCounters();
        window.location.href = '{{ route("public.favorites") }}';
    });
});
</script>
@endsection
