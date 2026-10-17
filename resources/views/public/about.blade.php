@extends('layouts.public')

@section('title', __('pages.about_us') . ' - ' . setting('company_name', 'REMS'))
@section('meta_description', setting_trans('about_hero_subtitle', 'Tuko hapa kukupa huduma bora, za uwazi na zilizothibitishwa za mauzo ya viwanja, upimaji ardhi, na michoro ya mipango miji.', 'Empowering landowners and property investors with verified cadastral surveys, title deed processing, and master-planned land projects.'))

@php
    $contactPhone = setting('contact_phone', '+255 784 100 200');
    $whatsappNumber = setting('contact_whatsapp', '255784100200');
@endphp

@section('content')
<!-- 1. Hero -->
<section class="page-hero hero-img-survey py-4 py-md-5">
    <div class="container py-2 py-lg-4 text-center position-relative z-1">
        <div class="max-w-3xl mx-auto">
            <span class="badge bg-white bg-opacity-10 border border-white border-opacity-25 text-white px-3 py-2 rounded-pill fw-semibold mb-3">
                <i class="bi bi-info-circle-fill me-1"></i> {{ setting_trans('about_hero_badge', 'Kuhusu Sisi', 'About Us') }}
            </span>
            <h1 class="fw-extrabold mb-3 text-balance">
                {{ setting_trans('about_hero_title', 'Wataalamu wa Upimaji Ardhi & Usimamizi wa Majengo Tanzania', 'Master Land Surveying & Property Geomatics in Tanzania') }}
            </h1>
            <p class="lead text-white-50 mb-4 mx-auto max-w-2xl">
                {{ setting_trans('about_hero_subtitle', 'Tuko hapa kukupa huduma bora, za uwazi na zilizothibitishwa za mauzo ya viwanja, upimaji ardhi, na michoro ya mipango miji.', 'Empowering landowners and property investors with verified cadastral surveys, title deed processing, and master-planned land projects.') }}
            </p>
            <div class="d-grid d-sm-flex flex-wrap justify-content-sm-center gap-2 gap-sm-3">
                <a href="{{ route('public.projects') }}" class="btn btn-primary btn-lg px-4 fw-bold rounded-pill shadow">
                    <i class="bi bi-map-fill me-2"></i> {{ setting_trans('landing_hero_cta_primary_text', 'Tazama Miradi', 'Explore Projects') }}
                </a>
                <a href="{{ route('public.contact') }}" class="btn btn-outline-light btn-lg px-4 fw-semibold rounded-pill">
                    <i class="bi bi-headset me-2"></i> {{ setting_trans('landing_nav_contact_label', 'Wasiliana Nasi', 'Contact Us') }}
                </a>
            </div>
        </div>
    </div>
</section>

<!-- 2. Story & pillars -->
<section class="py-4 py-md-5 bg-white">
    <div class="container py-md-3">
        <div class="row g-4 g-lg-5 align-items-center">
            <div class="col-12 col-lg-6">
                <span class="section-tag"><i class="bi bi-shield-check me-1"></i> {{ setting_trans('about_story_tag', 'Uzoefu & Uaminifu', 'Our History & Experience') }}</span>
                <h2 class="fw-extrabold text-dark mb-3">{{ setting_trans('about_story_title', 'Kuinua Viwango vya Upimaji na Mauzo ya Viwanja', 'Elevating Land Surveying & Property Standards') }}</h2>
                <p class="text-secondary leading-relaxed mb-4">
                    {{ setting_trans('about_story_bio', 'Tunatoa huduma kamili za kisheria na kitaalamu kwa kushirikiana na Wapimaji Ardhi waliosajiliwa na Wataalamu wa Mipango Miji. Mfumo wetu unalinda na kuongeza thamani ya uwekezaji wako wa viwanja na majengo nchini Tanzania.', 'We provide comprehensive geomatics and legal land management services in partnership with registered land surveyors and town planners. Our ecosystem protects and elevates your property investments across Tanzania.') }}
                </p>

                @php
                    $pillars = [
                        ['icon' => 'bi-geo-alt-fill text-danger', 'title' => __('app.cadastral_gis_accuracy'), 'text' => __('app.licensed_geomatics_surveyors_with_high_precision')],
                        ['icon' => 'bi-file-earmark-check-fill text-success', 'title' => __('app.title_deed_processing'), 'text' => __('app.end_to_end_certificate_of_occupancy')],
                        ['icon' => 'bi-diagram-3-fill text-primary', 'title' => __('app.town_planning_master_plans'), 'text' => __('app.approved_tp_drawings_and_sustainable_infrastructure')],
                        ['icon' => 'bi-shield-lock-fill text-warning', 'title' => __('app.transparent_transactions'), 'text' => __('app.verified_boundaries_with_zero_double_allocation')],
                    ];
                @endphp
                <div class="row g-3">
                    @foreach($pillars as $pillar)
                        <div class="col-12 col-sm-6">
                            <div class="p-3 rounded-4 bg-light border h-100 shadow-xs">
                                <i class="bi {{ $pillar['icon'] }} fs-4 mb-2 d-block"></i>
                                <h3 class="fw-bold text-dark fs-6 mb-1">{{ $pillar['title'] }}</h3>
                                <small class="text-muted">{{ $pillar['text'] }}</small>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="position-relative">
                    <div class="ratio ratio-4x3 rounded-4 overflow-hidden shadow-lg border border-2 border-white">
                        <img src="{{ setting('about_story_image') ?: asset('images/hero-survey-team.jpg') }}" alt="{{ __('pages.about_image_alt') }}" class="object-fit-cover" loading="lazy">
                    </div>
                    <div class="position-absolute bottom-0 start-0 m-3 p-3 bg-dark bg-opacity-90 backdrop-blur rounded-3 text-white border border-white border-opacity-25 d-none d-sm-flex align-items-center gap-3 shadow-lg max-w-xl">
                        <i class="bi bi-patch-check-fill text-success fs-1"></i>
                        <div>
                            <h3 class="fw-bold mb-0 text-white fs-6">{{ setting('company_name', 'REMS') }}</h3>
                            <small class="text-white-50">{{ __('app.ministry_certified_geomatics_team') }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 3. Vision, mission, values -->
<section class="py-4 py-md-5 bg-light">
    <div class="container py-md-3">
        <div class="text-center max-w-2xl mx-auto mb-4 mb-md-5">
            <span class="section-tag">{{ __('app.foundational_principles') }}</span>
            <h2 class="fw-extrabold text-dark">{{ __('app.vision_mission_and_core_values') }}</h2>
        </div>

        @php
            $principles = [
                ['icon' => 'bi-eye-fill', 'color' => 'primary', 'title' => setting_trans('about_vision_title', 'Dira Yetu', 'Our Vision'), 'text' => setting_trans('about_vision_text', 'Kuwa kiongozi mkuu wa kitaifa katika kutoa suluhisho la kisasa la upimaji wa viwanja na mauzo ya ardhi yenye Hati Miliki nchini Tanzania.', 'To be the leading digital infrastructure for verified land surveying, master-planned plot allocation, and property geomatics in Tanzania.')],
                ['icon' => 'bi-bullseye', 'color' => 'success', 'title' => setting_trans('about_mission_title', 'Dhamira Yetu', 'Our Mission'), 'text' => setting_trans('about_mission_text', 'Kuwapa wateja wetu huduma za haraka, za uhakika na zenye uwazi mkubwa katika kupima, kurasimisha na kumilikisha viwanja bila migogoro.', 'To deliver precise geomatics surveying, seamless title deed processing, and secure land plot acquisitions with zero conflict.')],
                ['icon' => 'bi-gem', 'color' => 'warning', 'title' => setting_trans('about_values_title', 'Misingi Yetu', 'Our Core Values'), 'text' => setting_trans('about_values_text', 'Uaminifu, Usahihi wa Vipimo, Uwazi wa Kisheria na Huduma Bora za Wateja kabla na baada ya ununuzi.', 'Integrity, Spatial Accuracy, Legal Transparency, and Continuous Customer Support across every transaction.')],
            ];
        @endphp
        <div class="row g-3 g-md-4">
            @foreach($principles as $item)
                <div class="col-12 col-md-4">
                    <div class="card h-100 border-0 rounded-4 p-4 shadow-sm bg-white hover-shadow transition-all text-center">
                        <span class="touch-target rounded-circle p-2 fs-2 mx-auto mb-3 bg-{{ $item['color'] }}-subtle text-{{ $item['color'] }}"><i class="bi {{ $item['icon'] }}"></i></span>
                        <h3 class="fw-bold text-dark fs-5 mb-2">{{ $item['title'] }}</h3>
                        <p class="text-secondary small mb-0 leading-relaxed">{{ $item['text'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- 4. Stats -->
<section class="py-4 py-md-5 bg-dark text-white">
    <div class="container py-md-2">
        @php
            $statItems = [
                ['color' => 'primary', 'number' => setting_trans('about_stat1_number', '100%', '100%'), 'label' => setting_trans('about_stat1_label', 'Viwanja Vilivyopimwa', 'Surveyed Land Plots')],
                ['color' => 'success', 'number' => setting_trans('about_stat2_number', '50+', '50+'), 'label' => setting_trans('about_stat2_label', 'Miradi ya Viwanja', 'Master Land Projects')],
                ['color' => 'warning', 'number' => setting_trans('about_stat3_number', '10+', '10+'), 'label' => setting_trans('about_stat3_label', 'Mikoa Tanzania', 'Regions Covered')],
                ['color' => 'info', 'number' => setting_trans('about_stat4_number', '5,000+', '5,000+'), 'label' => setting_trans('about_stat4_label', 'Wateja Waliotimiza Ndoto', 'Happy Landowners')],
            ];
        @endphp
        <div class="row g-3 g-md-4 text-center">
            @foreach($statItems as $stat)
                <div class="col-6 col-md-3">
                    <div class="p-2 p-md-3">
                        <div class="fs-1 fw-extrabold text-{{ $stat['color'] }} mb-1 lh-1">{{ $stat['number'] }}</div>
                        <div class="text-white-50 small font-semibold text-uppercase tracking-wider">{{ $stat['label'] }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- 5. CTA -->
<section class="py-4 py-md-5 bg-primary text-white text-center">
    <div class="container py-md-3">
        <h2 class="fw-extrabold text-white mb-2">{{ setting_trans('about_cta_title', 'Unahitaji Ushauri wa Upimaji au Ununuzi wa Kiwanja?', 'Need Land Surveying or Plot Purchasing Assistance?') }}</h2>
        <p class="lead text-white-50 mb-4 max-w-xl mx-auto">{{ setting_trans('about_cta_subtitle', 'Wasiliana na wataalamu wetu wapimaji waliothibitishwa kupata msaada wa haraka.', 'Contact our team of registered geomatics experts today for immediate guidance.') }}</p>
        <div class="d-grid d-sm-flex flex-wrap justify-content-sm-center gap-2 gap-sm-3">
            <a href="tel:{{ preg_replace('/\s+/', '', $contactPhone) }}" class="btn btn-light text-primary btn-lg px-4 fw-bold rounded-pill shadow-sm">
                <i class="bi bi-telephone-fill me-2"></i> {{ format_phone_tz($contactPhone) }}
            </a>
            <a href="https://wa.me/{{ $whatsappNumber }}?text={{ urlencode(setting_trans('landing_whatsapp_message', 'Habari, ningependa kupata maelezo kuhusu miradi ya viwanja na upimaji ardhi.', 'Hello, I am interested in exploring plot projects and land survey services.')) }}" target="_blank" rel="noopener" class="btn btn-whatsapp btn-lg px-4 fw-bold rounded-pill shadow-sm">
                <i class="bi bi-whatsapp me-2"></i> {{ __('pages.whatsapp_direct') }}
            </a>
        </div>
    </div>
</section>
@endsection
