@extends('layouts.public')

@section('title', $property->title)
@section('meta_description', Str::limit(strip_tags($property->description), 160))
@section('og_image', $property->primary_image_url)

@section('body_class', 'has-action-bar')

@php
    $isRental = in_array($property->listing_type, ['Rent', 'Lease']);
    $headlinePrice = $isRental ? $property->rent_price : $property->price;
    $parcel = $property->landParcel;
    $isLand = $parcel || in_array($property->propertyType?->category, ['Land', 'Agricultural']);
    $beaconCount = ($parcel && is_array($parcel->boundary_coordinates_json)) ? count($parcel->boundary_coordinates_json) : 0;
    $whatsappNumber = setting('contact_whatsapp', '255784100200');
    $contactPhone = setting('contact_phone', '+255 784 100 200');
    $placeholderImage = asset('images/property-placeholder.jpg');
    $galleryItems = $property->media->filter(fn ($m) => $m->mediaFile?->url);
@endphp

@section('content')
<div class="bg-white border-bottom py-3 mb-4">
    <div class="container">
        <nav aria-label="breadcrumb" class="mb-2">
            <ol class="breadcrumb small mb-0 flex-nowrap text-nowrap overflow-auto">
                <li class="breadcrumb-item"><a href="{{ route('public.home') }}" class="text-decoration-none">{{ __('pages.home') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('public.properties') }}" class="text-decoration-none">{{ __('app.properties') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('public.locations.show', Str::slug($property->city)) }}" class="text-decoration-none">{{ $property->city }}</a></li>
                <li class="breadcrumb-item active text-truncate max-w-500" aria-current="page">{{ $property->title }}</li>
            </ol>
        </nav>

        <div class="page-header mb-0 align-items-start">
            <div class="min-w-0">
                <div class="d-flex flex-wrap align-items-center gap-1.5 mb-2">
                    <span class="badge {{ $isRental ? 'bg-warning text-dark' : 'bg-primary text-white' }} px-2.5 py-1.5">
                        {{ $isRental ? __('pages.for_rent') : __('pages.for_sale') }}
                    </span>
                    @if($property->propertyType)
                        <span class="badge bg-light text-dark border">{{ $property->propertyType->name }}</span>
                    @endif
                    <span class="badge badge-verified" data-bs-toggle="tooltip" title="{{ __('pages.verified_tooltip') }}">
                        <i class="bi bi-patch-check-fill text-success me-1"></i> {{ __('pages.verified_listing') }}
                    </span>
                    <span class="badge bg-secondary-subtle text-secondary">{{ __('pages.ref') }}: {{ $property->property_code }}</span>
                </div>
                <h1 class="brand-font fw-bold mb-1 fs-3">{{ $property->title }}</h1>
                <p class="text-muted mb-0 small">
                    <i class="bi bi-geo-alt-fill text-danger me-1"></i> {{ $property->address }}, {{ $property->city }}, {{ $property->country ?: 'Tanzania' }}
                </p>
            </div>

            <div class="text-lg-end">
                <div class="d-flex flex-wrap align-items-center justify-content-lg-end gap-2 mb-1">
                    <span class="fs-3 fw-extrabold text-success brand-font">{{ $property->formatted_price }}</span>
                    <span class="badge bg-success-subtle text-success border border-success-subtle fs-6">{{ format_money_short($headlinePrice, $property->currency === 'TZS' || ! $property->currency ? null : $property->currency) }}</span>
                </div>
                @if($property->deposit_amount)
                    <div class="small text-muted mb-2">{{ __('pages.deposit') }}: {{ format_currency($property->deposit_amount, $property->currency) }}</div>
                @endif
                <div class="d-flex flex-wrap align-items-center gap-2 justify-content-lg-end">
                    <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3 favorite-btn" data-property-id="{{ $property->id }}">
                        <i class="bi bi-heart me-1"></i> {{ __('pages.save') }}
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 compare-toggle-btn" data-property-id="{{ $property->id }}">
                        <i class="bi bi-columns-gap me-1"></i> {{ __('pages.compare') }}
                    </button>
                    <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 share-property-btn" data-url="{{ url()->current() }}" data-title="{{ $property->title }}">
                        <i class="bi bi-share me-1"></i> {{ __('pages.share') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container pb-5">
    @if($errors->any())
        <div class="alert alert-danger shadow-sm" role="alert">
            <strong>{{ __('pages.form_errors_title') }}</strong>
            <ul class="mb-0 small">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-4">
        <!-- Main column -->
        <div class="col-12 col-lg-8 min-w-0">
            <!-- 1. Gallery -->
            <div class="card rounded-4 overflow-hidden mb-4 shadow-sm">
                <div class="ratio ratio-16x9 bg-dark">
                    <img id="mainGalleryImg" src="{{ $property->primary_image_url }}" alt="{{ $property->title }}" class="object-fit-cover" onerror="this.onerror=null;this.src='{{ $placeholderImage }}'">
                </div>
                <span class="position-absolute top-0 end-0 m-3 badge bg-dark bg-opacity-75 text-white px-2.5 py-1.5">
                    <i class="bi bi-camera me-1"></i> {{ $galleryItems->count() ?: 1 }} {{ __('pages.photos') }}
                </span>
                @if($galleryItems->count() > 1)
                    <div class="p-2 bg-light border-top scroll-x" role="list" aria-label="{{ __('pages.gallery') }}">
                        @foreach($galleryItems as $med)
                            <button type="button" role="listitem" class="btn p-0 border rounded-3 overflow-hidden bg-white gallery-thumb" style="width: 92px; height: 68px;" data-full="{{ $med->mediaFile->url }}" aria-label="{{ __('pages.photo') }} {{ $loop->iteration }}">
                                <img src="{{ $med->mediaFile->url }}" alt="{{ $med->caption ?: $property->title }}" class="w-100 h-100 object-fit-cover" loading="lazy">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- 2. Specs summary bar -->
            <div class="card rounded-4 p-3 mb-4 bg-white shadow-sm">
                <div class="row g-2">
                    @if($property->bedrooms)
                        <div class="col-6 col-sm-4 col-xl-3">
                            <div class="d-flex align-items-center gap-2 p-2.5 rounded-3 bg-light h-100">
                                <i class="bi bi-door-open text-primary fs-4"></i>
                                <div class="min-w-0">
                                    <div class="text-muted small">{{ __('app.bedrooms') }}</div>
                                    <div class="fw-bold text-dark">{{ $property->bedrooms }}</div>
                                </div>
                            </div>
                        </div>
                    @endif
                    @if($property->bathrooms)
                        <div class="col-6 col-sm-4 col-xl-3">
                            <div class="d-flex align-items-center gap-2 p-2.5 rounded-3 bg-light h-100">
                                <i class="bi bi-droplet text-info fs-4"></i>
                                <div class="min-w-0">
                                    <div class="text-muted small">{{ __('app.bathrooms') }}</div>
                                    <div class="fw-bold text-dark">{{ $property->bathrooms }}</div>
                                </div>
                            </div>
                        </div>
                    @endif
                    <div class="col-6 col-sm-4 col-xl-3">
                        <div class="d-flex align-items-center gap-2 p-2.5 rounded-3 bg-light h-100">
                            <i class="bi bi-arrows-fullscreen text-success fs-4"></i>
                            <div class="min-w-0">
                                <div class="text-muted small">{{ __('app.area') }}</div>
                                <div class="fw-bold text-dark">
                                    @if($property->area_size)
                                        {{ $property->area_size }} {{ $property->area_unit }}
                                    @elseif($parcel)
                                        {{ $parcel->acreage }} {{ __('app.acres') }}
                                    @else
                                        —
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @if($parcel)
                        <div class="col-6 col-sm-4 col-xl-3">
                            <div class="d-flex align-items-center gap-2 p-2.5 rounded-3 bg-light h-100">
                                <i class="bi bi-grid-1x2 text-warning fs-4"></i>
                                <div class="min-w-0">
                                    <div class="text-muted small">{{ __('pages.plot_block') }}</div>
                                    <div class="fw-bold text-dark text-truncate">{{ $parcel->parcel_number }}@if($parcel->block_number) / {{ $parcel->block_number }}@endif</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-sm-4 col-xl-3">
                            <div class="d-flex align-items-center gap-2 p-2.5 rounded-3 bg-light h-100">
                                <i class="bi bi-file-earmark-check text-success fs-4"></i>
                                <div class="min-w-0">
                                    <div class="text-muted small">{{ __('pages.title_deed_type') }}</div>
                                    <div class="fw-bold text-dark text-truncate">{{ $parcel->title_deed_type }}</div>
                                </div>
                            </div>
                        </div>
                    @endif
                    @if(! $isLand)
                        <div class="col-6 col-sm-4 col-xl-3">
                            <div class="d-flex align-items-center gap-2 p-2.5 rounded-3 bg-light h-100">
                                <i class="bi bi-car-front text-secondary fs-4"></i>
                                <div class="min-w-0">
                                    <div class="text-muted small">{{ __('pages.parking') }}</div>
                                    <div class="fw-bold text-dark">{{ $property->parking_spaces ?? '1+' }}</div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- 3. Description -->
            <div class="card rounded-4 p-3 p-md-4 mb-4 bg-white shadow-sm">
                <h2 class="brand-font fs-5 mb-3"><i class="bi bi-card-text text-primary me-2"></i> {{ __('pages.property_description') }}</h2>
                <div class="text-secondary leading-relaxed">
                    {!! nl2br(e($property->description)) !!}
                </div>
            </div>

            <!-- 4. Amenities -->
            <div class="card rounded-4 p-3 p-md-4 mb-4 bg-white shadow-sm">
                <h2 class="brand-font fs-5 mb-3"><i class="bi bi-stars text-warning me-2"></i> {{ __('pages.features_amenities') }}</h2>
                @php
                    $amenityNames = $property->amenities->pluck('name');
                    if ($amenityNames->isEmpty() && is_array($property->features_json)) {
                        $amenityNames = collect($property->features_json);
                    }
                @endphp
                @if($amenityNames->count())
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($amenityNames as $amenity)
                            <span class="badge rounded-pill bg-light text-dark border fw-medium px-3 py-2">
                                <i class="bi bi-check-circle-fill text-success me-1"></i> {{ $amenity }}
                            </span>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted small mb-0">{{ __('pages.standard_amenities') }}</p>
                @endif
            </div>

            <!-- 5. Land & cadastral information -->
            @if($parcel)
                <div class="card rounded-4 p-3 p-md-4 mb-4 shadow-sm border-success bg-success-subtle">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3 border-bottom border-success-subtle pb-2">
                        <h2 class="brand-font fs-5 text-success mb-0"><i class="bi bi-compass me-2"></i> {{ __('pages.cadastral_title') }}</h2>
                        <span class="badge bg-success">{{ __('pages.registry_verified') }}</span>
                    </div>

                    <div class="row g-3 small">
                        <div class="col-6 col-md-4">
                            <span class="text-muted d-block">{{ __('pages.plot_number') }}</span>
                            <strong class="text-dark">{{ $parcel->parcel_number }}</strong>
                        </div>
                        <div class="col-6 col-md-4">
                            <span class="text-muted d-block">{{ __('pages.block_number') }}</span>
                            <strong class="text-dark">{{ $parcel->block_number ?: '—' }}</strong>
                        </div>
                        <div class="col-6 col-md-4">
                            <span class="text-muted d-block">{{ __('pages.title_deed_number') }}</span>
                            <strong class="text-dark">{{ $parcel->deed_number ?: __('pages.in_processing') }}</strong>
                        </div>
                        <div class="col-6 col-md-4">
                            <span class="text-muted d-block">{{ __('pages.title_deed_type') }}</span>
                            <strong class="text-dark">{{ $parcel->title_deed_type }}</strong>
                        </div>
                        <div class="col-6 col-md-4">
                            <span class="text-muted d-block">{{ __('pages.surveyed_plan') }}</span>
                            <strong class="text-dark">{{ $parcel->survey_plan_number ?: __('pages.available_on_request') }}</strong>
                        </div>
                        <div class="col-6 col-md-4">
                            <span class="text-muted d-block">{{ __('pages.beacons') }}</span>
                            <strong class="text-dark">{{ $beaconCount ? trans_choice('pages.beacons_count', $beaconCount, ['count' => $beaconCount]) : __('pages.beacons_verified_on_site') }}</strong>
                        </div>
                        <div class="col-6 col-md-4">
                            <span class="text-muted d-block">{{ __('pages.acreage') }}</span>
                            <strong class="text-dark">{{ $parcel->acreage }} {{ __('app.acres') }}</strong>
                        </div>
                        <div class="col-6 col-md-4">
                            <span class="text-muted d-block">{{ __('pages.tenure_remaining') }}</span>
                            <strong class="text-dark">{{ $parcel->tenure_years_remaining ?? 99 }} {{ __('pages.years') }}</strong>
                        </div>
                        <div class="col-6 col-md-4">
                            <span class="text-muted d-block">{{ __('pages.land_use') }}</span>
                            <strong class="text-dark">{{ $parcel->land_use ?: ($parcel->zoning ?: __('pages.residential')) }}</strong>
                        </div>
                        <div class="col-6 col-md-4">
                            <span class="text-muted d-block">{{ __('pages.topography') }}</span>
                            <strong class="text-dark">{{ $parcel->topography ?: __('pages.flat_land') }}</strong>
                        </div>
                        <div class="col-6 col-md-4">
                            <span class="text-muted d-block">{{ __('pages.soil_type') }}</span>
                            <strong class="text-dark">{{ $parcel->soil_type ?: '—' }}</strong>
                        </div>
                        <div class="col-6 col-md-4">
                            <span class="text-muted d-block">{{ __('pages.location_hierarchy') }}</span>
                            <strong class="text-dark">{{ collect([$parcel->council, $parcel->district, $parcel->region ?: $property->city])->filter()->implode(', ') }}</strong>
                        </div>
                    </div>
                    <p class="small text-muted mb-0 mt-3"><i class="bi bi-info-circle me-1"></i> {{ __('pages.cadastral_note') }}</p>
                </div>
            @endif

            <!-- 6. Map -->
            @if($property->latitude && $property->longitude)
                <div class="card rounded-4 p-3 p-md-4 mb-4 bg-white shadow-sm">
                    <h2 class="brand-font fs-5 mb-3"><i class="bi bi-geo-alt-fill text-danger me-2"></i> {{ __('pages.location_map') }}</h2>
                    <div id="singlePropertyMap" class="rounded-4 border w-100" style="height: 320px;"></div>
                    <div class="text-muted small mt-2 d-flex flex-wrap align-items-center gap-2">
                        <span><i class="bi bi-info-circle me-1"></i> {{ __('pages.coordinates') }}: {{ $property->latitude }}, {{ $property->longitude }}</span>
                        <a href="https://www.google.com/maps/search/?api=1&query={{ $property->latitude }},{{ $property->longitude }}" target="_blank" rel="noopener" class="text-decoration-none fw-semibold">
                            <i class="bi bi-box-arrow-up-right me-1"></i> {{ __('pages.open_in_google_maps') }}
                        </a>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar (drops below content under lg) -->
        <div class="col-12 col-lg-4">
            <div class="sticky-lg-top" style="top: 84px;">
                <!-- WhatsApp card -->
                <div class="card border-0 shadow-sm p-3 p-md-4 rounded-4 bg-dark text-white mb-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <span class="btn-icon bg-white text-success fs-4"><i class="bi bi-whatsapp"></i></span>
                        <div class="min-w-0">
                            <h3 class="brand-font fs-6 text-white mb-0">{{ __('pages.chat_on_whatsapp') }}</h3>
                            <small class="text-white-50">{{ __('pages.instant_response') }}</small>
                        </div>
                    </div>
                    <a href="https://wa.me/{{ $whatsappNumber }}?text={{ urlencode(__('pages.whatsapp_property_message', ['title' => $property->title, 'url' => url()->current()])) }}" target="_blank" rel="noopener" class="btn btn-whatsapp w-100 py-2">
                        <i class="bi bi-whatsapp me-1"></i> {{ __('pages.start_whatsapp_chat') }}
                    </a>
                </div>

                <!-- Inquiry / viewing / reservation -->
                <div class="card rounded-4 p-3 p-md-4 shadow-sm bg-white mb-4">
                    <ul class="nav nav-pills nav-fill gap-1 mb-3" id="inquiryTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active fw-bold small px-2" data-bs-toggle="tab" data-bs-target="#inquiryTabPane" type="button" role="tab" aria-selected="true">
                                <i class="bi bi-envelope me-1"></i> {{ __('pages.inquire') }}
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-bold small px-2" data-bs-toggle="tab" data-bs-target="#viewingTabPane" type="button" role="tab" aria-selected="false">
                                <i class="bi bi-calendar-event me-1"></i> {{ __('pages.book_viewing') }}
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-bold small px-2" data-bs-toggle="tab" data-bs-target="#reserveTabPane" type="button" role="tab" aria-selected="false">
                                <i class="bi bi-bookmark-check-fill me-1"></i> {{ __('pages.reserve_plot') }}
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <!-- Tab 1: Inquiry -->
                        <div class="tab-pane fade show active" id="inquiryTabPane" role="tabpanel">
                            <form action="{{ route('public.inquire') }}" method="POST">
                                @csrf
                                <input type="hidden" name="property_id" value="{{ $property->id }}">

                                <div class="mb-3">
                                    <label for="inquiryName" class="form-label small fw-bold text-muted">{{ __('pages.full_name') }} *</label>
                                    <input type="text" id="inquiryName" name="name" class="form-control" placeholder="Juma Rashid" autocomplete="name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="inquiryPhone" class="form-label small fw-bold text-muted">{{ __('pages.phone_whatsapp') }} *</label>
                                    <input type="tel" id="inquiryPhone" name="phone" class="form-control" placeholder="0784 100 200" data-tz-phone autocomplete="tel" required>
                                </div>
                                <div class="mb-3">
                                    <label for="inquiryEmail" class="form-label small fw-bold text-muted">{{ __('pages.email_address') }}</label>
                                    <input type="email" id="inquiryEmail" name="email" class="form-control" placeholder="juma@example.com" autocomplete="email">
                                </div>
                                <div class="mb-3">
                                    <label for="inquiryChannel" class="form-label small fw-bold text-muted">{{ __('pages.preferred_contact_channel') }}</label>
                                    <select id="inquiryChannel" name="preferred_contact_method" class="form-select">
                                        <option value="WhatsApp">WhatsApp</option>
                                        <option value="Phone">{{ __('pages.phone_call') }}</option>
                                        <option value="Email">{{ __('pages.email') }}</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="inquiryMessage" class="form-label small fw-bold text-muted">{{ __('pages.your_message') }} *</label>
                                    <textarea id="inquiryMessage" name="message" rows="3" class="form-control" required>{{ __('pages.inquiry_default_message', ['title' => $property->title, 'code' => $property->property_code]) }}</textarea>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary py-2 fw-bold shadow-sm">
                                        <i class="bi bi-send-fill me-1"></i> {{ __('pages.send_inquiry') }}
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Tab 2: Book viewing -->
                        <div class="tab-pane fade" id="viewingTabPane" role="tabpanel">
                            <form action="{{ route('public.viewing.book') }}" method="POST">
                                @csrf
                                <input type="hidden" name="property_id" value="{{ $property->id }}">

                                <div class="mb-3">
                                    <label for="viewingName" class="form-label small fw-bold text-muted">{{ __('pages.full_name') }} *</label>
                                    <input type="text" id="viewingName" name="name" class="form-control" placeholder="Juma Rashid" autocomplete="name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="viewingPhone" class="form-label small fw-bold text-muted">{{ __('pages.phone_number') }} *</label>
                                    <input type="tel" id="viewingPhone" name="phone" class="form-control" placeholder="0784 100 200" data-tz-phone autocomplete="tel" required>
                                </div>
                                <div class="mb-3">
                                    <label for="viewingEmail" class="form-label small fw-bold text-muted">{{ __('pages.email_address') }}</label>
                                    <input type="email" id="viewingEmail" name="email" class="form-control" placeholder="juma@example.com" autocomplete="email">
                                </div>
                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <label for="viewingDate" class="form-label small fw-bold text-muted">{{ __('pages.date') }} *</label>
                                        <input type="date" id="viewingDate" name="preferred_date" class="form-control" min="{{ date('Y-m-d') }}" value="{{ date('Y-m-d', strtotime('+1 day')) }}" required>
                                    </div>
                                    <div class="col-6">
                                        <label for="viewingTime" class="form-label small fw-bold text-muted">{{ __('pages.time_eat') }} *</label>
                                        <select id="viewingTime" name="preferred_time" class="form-select" required>
                                            <option value="09:00 AM">09:00</option>
                                            <option value="11:00 AM">11:00</option>
                                            <option value="02:00 PM">14:00</option>
                                            <option value="04:00 PM">16:00</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="viewingMessage" class="form-label small fw-bold text-muted">{{ __('pages.viewing_notes') }}</label>
                                    <textarea id="viewingMessage" name="message" rows="2" class="form-control" placeholder="{{ __('pages.viewing_notes_placeholder') }}"></textarea>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-success py-2 fw-bold shadow-sm">
                                        <i class="bi bi-calendar-check me-1"></i> {{ __('pages.confirm_viewing') }}
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Tab 3: Reserve plot -->
                        <div class="tab-pane fade" id="reserveTabPane" role="tabpanel">
                            <form action="{{ route('public.reservation.reserve') }}" method="POST">
                                @csrf
                                <input type="hidden" name="property_id" value="{{ $property->id }}">

                                <div class="alert alert-warning py-2 small mb-3">
                                    <i class="bi bi-info-circle me-1"></i> {{ __('pages.reservation_notice') }}
                                </div>
                                <div class="mb-3">
                                    <label for="reserveName" class="form-label small fw-bold text-muted">{{ __('pages.full_name') }} *</label>
                                    <input type="text" id="reserveName" name="name" class="form-control" placeholder="Juma Rashid" autocomplete="name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="reservePhone" class="form-label small fw-bold text-muted">{{ __('pages.phone_sms') }} *</label>
                                    <input type="tel" id="reservePhone" name="phone" class="form-control" placeholder="0784 100 200" data-tz-phone autocomplete="tel" required>
                                </div>
                                <div class="mb-3">
                                    <label for="reserveEmail" class="form-label small fw-bold text-muted">{{ __('pages.email_address') }}</label>
                                    <input type="email" id="reserveEmail" name="email" class="form-control" placeholder="juma@example.com" autocomplete="email">
                                </div>
                                <div class="mb-3">
                                    <label for="reserveDeposit" class="form-label small fw-bold text-muted">{{ __('pages.reservation_deposit') }}</label>
                                    <div class="input-group">
                                        <span class="input-group-text">TSh</span>
                                        <input type="number" id="reserveDeposit" name="deposit_amount" class="form-control" inputmode="decimal" min="0" step="1000" value="{{ $property->deposit_amount ?: 500000 }}" required>
                                    </div>
                                    <div class="form-text">{{ __('pages.mobile_money_hint') }}</div>
                                </div>
                                <div class="mb-3">
                                    <label for="reserveNotes" class="form-label small fw-bold text-muted">{{ __('pages.reservation_notes') }}</label>
                                    <textarea id="reserveNotes" name="notes" rows="2" class="form-control" placeholder="{{ __('pages.reservation_notes_placeholder') }}"></textarea>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-warning py-2 fw-bold text-dark shadow-sm">
                                        <i class="bi bi-bookmark-check-fill me-1"></i> {{ __('pages.place_reservation') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="text-center text-muted small mt-3">
                        <i class="bi bi-shield-lock text-success me-1"></i> {{ __('pages.data_protected_by', ['company' => setting('company_name', current_organization()?->name ?? 'RehoSpace')]) }}
                    </div>
                </div>

                <!-- Agent / listing desk card -->
                <div class="card rounded-4 p-3 p-md-4 shadow-sm bg-white">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <span class="btn-icon bg-primary text-white fw-bold fs-5">{{ strtoupper(substr($property->owner?->first_name ?? setting('company_name', 'R'), 0, 1)) }}</span>
                        <div class="min-w-0">
                            <h3 class="brand-font fs-6 mb-0 text-truncate">{{ $property->owner?->company_name ?? __('pages.verified_listing_desk') }}</h3>
                            <small class="text-success fw-semibold"><i class="bi bi-patch-check-fill me-1"></i> {{ __('pages.authorized_representative') }}</small>
                        </div>
                    </div>
                    <div class="d-grid gap-2">
                        <a href="tel:{{ preg_replace('/\s+/', '', $contactPhone) }}" class="btn btn-outline-dark fw-bold">
                            <i class="bi bi-telephone-fill text-primary me-1"></i> {{ format_phone_tz($contactPhone) }}
                        </a>
                        <a href="https://wa.me/{{ $whatsappNumber }}?text={{ urlencode(__('pages.whatsapp_property_message', ['title' => $property->title, 'url' => url()->current()])) }}" target="_blank" rel="noopener" class="btn btn-whatsapp fw-bold">
                            <i class="bi bi-whatsapp me-1"></i> WhatsApp
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Similar properties -->
    @if($similarProperties->count())
        <div class="border-top pt-4 pt-md-5 mt-4 mt-md-5">
            <div class="page-header">
                <div>
                    <span class="section-tag">{{ __('pages.recommendations') }}</span>
                    <h2 class="brand-font mb-0">{{ __('pages.similar_properties_in', ['city' => $property->city]) }}</h2>
                </div>
                <div class="page-actions">
                    <a href="{{ route('public.locations.show', Str::slug($property->city)) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">{{ __('app.view_all') }}</a>
                </div>
            </div>
            <div class="row g-4 flex-nowrap flex-md-wrap overflow-x-auto pb-4">
                @foreach($similarProperties as $sp)
                    <div class="col-10 col-sm-7 col-md-4">
                        @include('public.partials.property-card', ['p' => $sp])
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

<!-- Mobile bottom action bar -->
@include('public.partials.mobile-bottom-bar', ['property' => $property])

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Gallery thumbnails
    const mainImage = document.getElementById('mainGalleryImg');
    document.querySelectorAll('.gallery-thumb').forEach(function (thumb) {
        thumb.addEventListener('click', function () {
            if (mainImage) {
                mainImage.src = thumb.getAttribute('data-full');
            }
            document.querySelectorAll('.gallery-thumb').forEach(function (t) { t.classList.remove('border-primary'); });
            thumb.classList.add('border-primary');
        });
    });

    // Tooltips
    if (window.bootstrap && bootstrap.Tooltip) {
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) { new bootstrap.Tooltip(el); });
    }

    @if($property->latitude && $property->longitude)
    // Leaflet map
    const lat = {{ $property->latitude }};
    const lng = {{ $property->longitude }};
    const map = L.map('singlePropertyMap', { scrollWheelZoom: false }).setView([lat, lng], 15);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    const marker = L.marker([lat, lng]).addTo(map);
    marker.bindPopup(`<strong>{{ addslashes($property->title) }}</strong><br>{{ addslashes($property->address) }}`).openPopup();

    @if($parcel && $parcel->boundary_coordinates_json)
        const polyCoords = [
            @foreach($parcel->boundary_coordinates_json as $pt)
                [{{ $pt['lat'] }}, {{ $pt['lng'] }}],
            @endforeach
        ];
        if (polyCoords.length >= 3) {
            const polygon = L.polygon(polyCoords, { color: '#10b981', fillColor: '#10b981', fillOpacity: 0.25 }).addTo(map);
            map.fitBounds(polygon.getBounds(), { padding: [30, 30] });
        }
    @endif
    @endif
});
</script>
@endsection
