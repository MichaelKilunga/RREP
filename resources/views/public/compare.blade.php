@extends('layouts.public')

@section('title', __('marketplace.compare_title'))

@section('styles')
<style>
    .compare-table th, .compare-table td { min-width: 200px; }
    .compare-table th:first-child, .compare-table td:first-child { min-width: 112px; width: 112px; }
    @media (min-width: 768px) { .compare-table th:first-child, .compare-table td:first-child { min-width: 180px; width: 180px; } }
</style>
@endsection

@section('content')
<section class="page-hero hero-img-plot py-4">
    <div class="container">
        <div class="page-header mb-0">
            <div class="min-w-0">
                <h1 class="brand-font text-white mb-1"><i class="bi bi-columns-gap text-warning me-2"></i>{{ __('marketplace.compare_title') }}</h1>
                <p class="text-white-50 small mb-0">{{ __('marketplace.compare_subtitle') }}</p>
            </div>
        </div>
    </div>
</section>

<div class="container py-4">
    @if($properties->count() > 0)
        <p class="text-muted small mb-2 d-md-none"><i class="bi bi-arrows-expand-vertical me-1"></i>{{ __('marketplace.swipe_hint') }}</p>
        <div class="card border rounded-4 overflow-hidden shadow-sm bg-white">
            <div class="table-responsive">
                <table class="table table-bordered align-middle text-center mb-0 compare-table">
                    <thead>
                        <tr>
                            <th class="text-start p-3 table-light position-sticky start-0 z-1 align-top">{{ __('marketplace.feature') }}</th>
                            @foreach($properties as $p)
                                @php
                                    $showUrl = route('public.properties.show', $p);
                                    $isRental = in_array($p->listing_type, ['Rent', 'Lease']);
                                    $shortPrice = $isRental ? format_money_short($p->rent_price ?? 0) : format_money_short($p->price);
                                @endphp
                                <th class="p-3 fw-normal">
                                    <div class="ratio ratio-16x9 rounded-3 overflow-hidden mb-2 bg-light">
                                        <img src="{{ $p->primary_image_url }}" alt="{{ $p->title }}" class="object-fit-cover" loading="lazy" decoding="async" onerror="this.onerror=null;this.src='{{ asset('images/property-placeholder.jpg') }}'">
                                    </div>
                                    <h2 class="h6 brand-font mb-1 line-clamp-2">
                                        <a href="{{ $showUrl }}" class="text-dark text-decoration-none hover-primary">{{ $p->title }}</a>
                                    </h2>
                                    <div class="fw-bold text-success" title="{{ $p->formatted_price }}">{{ $shortPrice }}</div>
                                    <button type="button" class="btn btn-link btn-sm text-danger text-decoration-none p-0 mt-1 compare-remove-btn" data-property-id="{{ $p->id }}">
                                        <i class="bi bi-x-circle me-1"></i>{{ __('app.delete') }}
                                    </button>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <th class="text-start table-light p-3 position-sticky start-0 z-1">{{ __('marketplace.purpose') }}</th>
                            @foreach($properties as $p)
                                @php
                                    $purposeKey = 'marketplace.for_'.strtolower((string) $p->listing_type);
                                    $purposeLabel = \Illuminate\Support\Facades\Lang::has($purposeKey) ? __($purposeKey) : __('marketplace.for_type', ['type' => $p->listing_type]);
                                @endphp
                                <td><span class="badge {{ in_array($p->listing_type, ['Rent', 'Lease']) ? 'bg-warning text-dark' : 'bg-primary' }} rounded-pill">{{ $purposeLabel }}</span></td>
                            @endforeach
                        </tr>
                        <tr>
                            <th class="text-start table-light p-3 position-sticky start-0 z-1">{{ __('app.location') }}</th>
                            @foreach($properties as $p)
                                <td class="small">{{ $p->address }}, {{ $p->city }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <th class="text-start table-light p-3 position-sticky start-0 z-1">{{ __('app.property_type') }}</th>
                            @foreach($properties as $p)
                                <td>{{ $p->propertyType?->name ?? __('marketplace.real_estate') }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <th class="text-start table-light p-3 position-sticky start-0 z-1">{{ __('app.bedrooms') }}</th>
                            @foreach($properties as $p)
                                <td><strong>{{ $p->bedrooms ? __('marketplace.beds', ['count' => $p->bedrooms]) : '-' }}</strong></td>
                            @endforeach
                        </tr>
                        <tr>
                            <th class="text-start table-light p-3 position-sticky start-0 z-1">{{ __('app.bathrooms') }}</th>
                            @foreach($properties as $p)
                                <td><strong>{{ $p->bathrooms ? __('marketplace.baths', ['count' => $p->bathrooms]) : '-' }}</strong></td>
                            @endforeach
                        </tr>
                        <tr>
                            <th class="text-start table-light p-3 position-sticky start-0 z-1">{{ __('marketplace.area_size') }}</th>
                            @foreach($properties as $p)
                                <td><strong>{{ $p->area_size ? rtrim(rtrim(number_format((float) $p->area_size, 2), '0'), '.').' '.$p->area_unit : ($p->landParcel ? rtrim(rtrim(number_format((float) $p->landParcel->acreage, 2), '0'), '.').' '.__('marketplace.acres') : '-') }}</strong></td>
                            @endforeach
                        </tr>
                        <tr>
                            <th class="text-start table-light p-3 position-sticky start-0 z-1">{{ __('marketplace.parking') }}</th>
                            @foreach($properties as $p)
                                <td>{{ $p->parking_spaces ? __('marketplace.vehicles', ['count' => $p->parking_spaces]) : '-' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <th class="text-start table-light p-3 position-sticky start-0 z-1">{{ __('marketplace.title_deed_type') }}</th>
                            @foreach($properties as $p)
                                <td><span class="badge bg-success-subtle text-success">{{ $p->landParcel?->title_deed_type ?? __('marketplace.titled') }}</span></td>
                            @endforeach
                        </tr>
                        <tr>
                            <th class="text-start table-light p-3 position-sticky start-0 z-1">{{ __('marketplace.availability') }}</th>
                            @foreach($properties as $p)
                                @php
                                    $statusKey = 'marketplace.status_'.strtolower(str_replace([' ', '-'], '_', (string) $p->status));
                                @endphp
                                <td><span class="badge-status-{{ strtolower(str_replace([' ', '-'], '', (string) $p->status)) }} small">{{ \Illuminate\Support\Facades\Lang::has($statusKey) ? __($statusKey) : $p->status }}</span></td>
                            @endforeach
                        </tr>
                        <tr>
                            <th class="text-start table-light p-3 position-sticky start-0 z-1">{{ __('app.actions') }}</th>
                            @foreach($properties as $p)
                                @php $showUrl = route('public.properties.show', $p); @endphp
                                <td class="p-3">
                                    <div class="d-grid gap-2">
                                        <a href="{{ $showUrl }}" class="btn btn-primary btn-sm fw-bold">{{ __('marketplace.view_details') }}</a>
                                        <a href="https://wa.me/{{ setting('contact_whatsapp', '255784100200') }}?text={{ urlencode(__('marketplace.whatsapp_compare', ['title' => $p->title, 'url' => $showUrl])) }}" target="_blank" rel="noopener" class="btn btn-whatsapp btn-sm fw-bold"><i class="bi bi-whatsapp me-1"></i>{{ __('marketplace.whatsapp') }}</a>
                                    </div>
                                </td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="card empty-state max-w-xl mx-auto">
            <div class="empty-icon"><i class="bi bi-columns-gap"></i></div>
            <h5>{{ __('marketplace.no_compare_title') }}</h5>
            <p class="small mb-3">{{ __('marketplace.no_compare_hint') }}</p>
            <a href="{{ route('public.properties') }}" class="btn btn-primary rounded-pill px-4 fw-bold">{{ __('marketplace.browse_marketplace') }}</a>
        </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const compareUrl = '{{ route("public.compare") }}';
    const stored = REMS.getCompare();
    const idsParam = new URLSearchParams(window.location.search).get('ids');

    // Keep the page in sync with the browser's stored comparison list.
    if (stored.length > 0 && !idsParam) {
        window.location.href = compareUrl + '?ids=' + stored.join(',');
        return;
    }

    $(document).on('click', '.compare-remove-btn', function () {
        REMS.toggleCompare($(this).data('property-id'));
        const remaining = REMS.getCompare();
        window.location.href = remaining.length ? compareUrl + '?ids=' + remaining.join(',') : compareUrl;
    });
});
</script>
@endsection
