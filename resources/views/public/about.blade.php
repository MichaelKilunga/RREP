@extends('layouts.public')

@section('title', setting_trans('about_hero_badge', 'Kuhusu Sisi', 'About Us') . ' - ' . setting('company_name', 'REMS'))

@section('content')
<!-- 1. Hero Banner (Clean, Modern & Responsive) -->
<section class="py-5 text-white position-relative overflow-hidden" style="background: linear-gradient(135deg, #091224 0%, #1e3a8a 50%, #0b1120 100%); min-height: 380px;">
    <div class="container py-4 py-lg-5 text-center position-relative z-1">
        <div class="max-w-3xl mx-auto">
            <span class="badge bg-primary px-3 py-2 rounded-pill fw-semibold mb-3 border border-primary-subtle">
                <i class="bi bi-info-circle-fill me-1"></i> {{ setting_trans('about_hero_badge', 'Kuhusu REMS & Kampuni', 'About REMS & Platform') }}
            </span>
            <h1 class="display-4 fw-extrabold text-white mb-3 text-balance">
                {{ setting_trans('about_hero_title', 'Wataalamu wa Upimaji Ardhi & Usimamizi wa Majengo Tanzania', 'Master Land Surveying & Property Geomatics in Tanzania') }}
            </h1>
            <p class="lead text-white-50 mb-4 mx-auto fs-5" style="max-width: 720px;">
                {{ setting_trans('about_hero_subtitle', 'Tuko hapa kukupa huduma bora, za uwazi na zilizothibitishwa za mauzo ya viwanja, upimaji ardhi, na michoro ya mipango miji.', 'Empowering landowners and property investors with verified cadastral surveys, title deed processing, and master-planned land projects.') }}
            </p>
            <div class="d-flex flex-wrap justify-content-center gap-3 align-items-center">
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

<!-- 2. Company Story, Bio & 4 Core Pillars -->
<section class="py-5 bg-white">
    <div class="container py-3">
        <div class="row g-5 align-items-center mb-5">
            <!-- Left: Text Content & Bio -->
            <div class="col-lg-6">
                <span class="text-uppercase text-primary fw-bold small tracking-wider mb-2 d-inline-block">
                    <i class="bi bi-shield-check me-1"></i> {{ setting_trans('about_story_tag', 'Uzoefu & Uaminifu', 'Our History & Experience') }}
                </span>
                <h2 class="fw-extrabold text-dark mb-3 display-6 fs-2">
                    {{ setting_trans('about_story_title', 'Kuinua Viwango vya Upimaji na Mauzo ya Viwanja', 'Elevating Land Surveying & Property Standards') }}
                </h2>
                <p class="text-secondary leading-relaxed mb-4 fs-6">
                    {{ setting_trans('about_story_bio', 'Tunatoa huduma kamili za kisheria na kitaalamu kwa kushirikiana na Wapimaji Ardhi waliosajiliwa na Wataalamu wa Mipango Miji. Mfumo wetu unalinda na kuongeza thamani ya uwekezaji wako wa viwanja na majengo nchini Tanzania.', 'We provide comprehensive geomatics and legal land management services in partnership with registered land surveyors and town planners. Our ecosystem protects and elevates your property investments across Tanzania.') }}
                </p>

                <!-- 4 Pillars Grid -->
                <div class="row g-3">
                    <div class="col-sm-6">
                        <div class="p-3 rounded-4 bg-light border border-light-subtle h-100 shadow-xs">
                            <i class="bi bi-geo-alt-fill text-danger fs-4 mb-2 d-block"></i>
                            <h6 class="fw-bold text-dark mb-1">{{ app()->getLocale() === 'sw' ? 'Vipimo Halisi vya GIS' : 'Cadastral GIS Accuracy' }}</h6>
                            <small class="text-muted">{{ app()->getLocale() === 'sw' ? 'Wapimaji waliosajiliwa na GPS zenye usahihi wa juu.' : 'Licensed geomatics surveyors with high-precision RTK GPS.' }}</small>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="p-3 rounded-4 bg-light border border-light-subtle h-100 shadow-xs">
                            <i class="bi bi-file-earmark-check-fill text-success fs-4 mb-2 d-block"></i>
                            <h6 class="fw-bold text-dark mb-1">{{ app()->getLocale() === 'sw' ? 'Hati Miliki Zilizothibitishwa' : 'Title Deed Processing' }}</h6>
                            <small class="text-muted">{{ app()->getLocale() === 'sw' ? 'Uhakiki rasmi na usaidizi wa Hati za Wizara.' : 'End-to-end Certificate of Occupancy facilitation.' }}</small>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="p-3 rounded-4 bg-light border border-light-subtle h-100 shadow-xs">
                            <i class="bi bi-diagram-3-fill text-primary fs-4 mb-2 d-block"></i>
                            <h6 class="fw-bold text-dark mb-1">{{ app()->getLocale() === 'sw' ? 'Michoro ya Mipango Miji' : 'Town Planning Master Plans' }}</h6>
                            <small class="text-muted">{{ app()->getLocale() === 'sw' ? 'Upangaji bora wa matumizi ya ardhi na miundombinu.' : 'Approved TP drawings and sustainable infrastructure layout.' }}</small>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="p-3 rounded-4 bg-light border border-light-subtle h-100 shadow-xs">
                            <i class="bi bi-shield-lock-fill text-warning fs-4 mb-2 d-block"></i>
                            <h6 class="fw-bold text-dark mb-1">{{ app()->getLocale() === 'sw' ? 'Uwazi Katika Mauzo' : 'Transparent Transactions' }}</h6>
                            <small class="text-muted">{{ app()->getLocale() === 'sw' ? 'Hakuna migogoro wala viwanja vya kughushi.' : 'Verified boundaries with zero double-allocation risk.' }}</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Feature Image -->
            <div class="col-lg-6">
                <div class="position-relative">
                    <div class="rounded-4 overflow-hidden shadow-lg border border-2 border-white">
                        @php
                            $storyImg = setting('about_story_image') ?: 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?w=800&auto=format&fit=crop&q=80';
                        @endphp
                        <img src="{{ $storyImg }}" alt="About Land Surveying" class="w-100 object-fit-cover" style="height: 420px;">
                    </div>
                    <div class="position-absolute bottom-0 start-0 m-3 p-3 bg-dark bg-opacity-90 backdrop-blur rounded-3 text-white border border-white border-opacity-25 d-none d-sm-flex align-items-center gap-3 shadow-lg" style="max-width: 320px;">
                        <i class="bi bi-patch-check-fill text-success fs-1"></i>
                        <div>
                            <h6 class="fw-bold mb-0 text-white">{{ setting('company_name', 'REMS') }}</h6>
                            <small class="text-white-50" style="font-size: 0.75rem;">{{ app()->getLocale() === 'sw' ? 'Wataalamu walioidhinishwa na Wizara' : 'Ministry Certified Geomatics Team' }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 3. Vision, Mission & Core Values 3-Card Grid -->
<section class="py-5 bg-light">
    <div class="container py-3">
        <div class="text-center max-w-2xl mx-auto mb-5">
            <span class="text-uppercase text-primary fw-bold small tracking-wider">{{ app()->getLocale() === 'sw' ? 'Misingi Yetu' : 'Foundational Principles' }}</span>
            <h2 class="fw-extrabold text-dark display-6 fs-3">{{ app()->getLocale() === 'sw' ? 'Dira, Dhamira na Misingi YETU' : 'Vision, Mission & Core Values' }}</h2>
        </div>

        <div class="row g-4">
            <!-- Vision -->
            <div class="col-md-4">
                <div class="card h-100 border-0 rounded-4 p-4 shadow-sm bg-white hover-shadow transition-all text-center">
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary mx-auto d-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px; font-size: 1.75rem;">
                        <i class="bi bi-eye-fill"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">{{ setting_trans('about_vision_title', 'Dira Yetu', 'Our Vision') }}</h5>
                    <p class="text-secondary small mb-0 line-height-md">
                        {{ setting_trans('about_vision_text', 'Kuwa kiongozi mkuu wa kitaifa katika kutoa suluhisho la kisasa la upimaji wa viwanja na mauzo ya ardhi yenye Hati Miliki nchini Tanzania.', 'To be the leading digital infrastructure for verified land surveying, master-planned plot allocation, and property geomatics in Tanzania.') }}
                    </p>
                </div>
            </div>

            <!-- Mission -->
            <div class="col-md-4">
                <div class="card h-100 border-0 rounded-4 p-4 shadow-sm bg-white hover-shadow transition-all text-center">
                    <div class="rounded-circle bg-success bg-opacity-10 text-success mx-auto d-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px; font-size: 1.75rem;">
                        <i class="bi bi-bullseye"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">{{ setting_trans('about_mission_title', 'Dhamira Yetu', 'Our Mission') }}</h5>
                    <p class="text-secondary small mb-0 line-height-md">
                        {{ setting_trans('about_mission_text', 'Kuwapa wateja wetu huduma za haraka, za uhakika na zenye uwazi mkubwa katika kupima, kurasimisha na kumilikisha viwanja bila migogoro.', 'To deliver precise geomatics surveying, seamless title deed processing, and secure land plot acquisitions with zero conflict.') }}
                    </p>
                </div>
            </div>

            <!-- Core Values -->
            <div class="col-md-4">
                <div class="card h-100 border-0 rounded-4 p-4 shadow-sm bg-white hover-shadow transition-all text-center">
                    <div class="rounded-circle bg-warning bg-opacity-10 text-warning mx-auto d-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px; font-size: 1.75rem;">
                        <i class="bi bi-gem"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">{{ setting_trans('about_values_title', 'Misingi Yetu', 'Our Core Values') }}</h5>
                    <p class="text-secondary small mb-0 line-height-md">
                        {{ setting_trans('about_values_text', 'Uaminifu, Usahihi wa Vipimo, Uwazi wa Kisheria na Huduma Bora za Wateja kabla na baada ya ununuzi.', 'Integrity, Spatial Accuracy, Legal Transparency, and Continuous Customer Support across every transaction.') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 4. Key Statistics & Achievements Bar -->
<section class="py-5 bg-dark text-white border-top border-bottom border-secondary">
    <div class="container py-2">
        <div class="row g-4 text-center">
            <div class="col-6 col-md-3">
                <div class="p-3">
                    <div class="display-5 fw-extrabold text-primary mb-1">{{ setting_trans('about_stat1_number', '100%', '100%') }}</div>
                    <div class="text-white-50 small font-semibold text-uppercase" style="letter-spacing: 0.05em;">{{ setting_trans('about_stat1_label', 'Viwanja Vilivyopimwa', 'Surveyed Land Plots') }}</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="p-3">
                    <div class="display-5 fw-extrabold text-success mb-1">{{ setting_trans('about_stat2_number', '50+', '50+') }}</div>
                    <div class="text-white-50 small font-semibold text-uppercase" style="letter-spacing: 0.05em;">{{ setting_trans('about_stat2_label', 'Miradi ya Viwanja', 'Master Land Projects') }}</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="p-3">
                    <div class="display-5 fw-extrabold text-warning mb-1">{{ setting_trans('about_stat3_number', '10+', '10+') }}</div>
                    <div class="text-white-50 small font-semibold text-uppercase" style="letter-spacing: 0.05em;">{{ setting_trans('about_stat3_label', 'Mikoa Tanzania', 'Regions Covered') }}</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="p-3">
                    <div class="display-5 fw-extrabold text-info mb-1">{{ setting_trans('about_stat4_number', '5,000+', '5,000+') }}</div>
                    <div class="text-white-50 small font-semibold text-uppercase" style="letter-spacing: 0.05em;">{{ setting_trans('about_stat4_label', 'Wateja Waliotimiza Ndoto', 'Happy Landowners') }}</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 5. Call-to-Action Banner -->
<section class="py-5 bg-primary text-white text-center">
    <div class="container py-3">
        <h2 class="fw-extrabold text-white mb-2 display-6 fs-3">
            {{ setting_trans('about_cta_title', 'Unahitaji Ushauri wa Upimaji au Ununuzi wa Kiwanja?', 'Need Land Surveying or Plot Purchasing Assistance?') }}
        </h2>
        <p class="lead text-white-50 mb-4 max-w-xl mx-auto fs-6">
            {{ setting_trans('about_cta_subtitle', 'Wasiliana na wataalamu wetu wapimaji waliothibitishwa kupata msaada wa haraka.', 'Contact our team of registered geomatics experts today for immediate guidance.') }}
        </p>
        <div class="d-flex flex-wrap justify-content-center gap-3">
            <a href="tel:{{ setting('contact_phone', '+255784100200') }}" class="btn btn-light text-primary btn-lg px-4 fw-bold rounded-pill shadow-sm">
                <i class="bi bi-telephone-fill me-2 text-primary"></i> {{ setting('contact_phone', '+255 784 100 200') }}
            </a>
            <a href="https://wa.me/{{ setting('contact_whatsapp', '255784100200') }}?text={{ urlencode(setting_trans('landing_whatsapp_message', 'Habari, ningependa kupata maelezo kuhusu miradi ya viwanja na upimaji ardhi.', 'Hello, I am interested in exploring plot projects and land survey services.')) }}" target="_blank" class="btn btn-success btn-lg px-4 fw-bold rounded-pill shadow-sm">
                <i class="bi bi-whatsapp me-2"></i> WhatsApp Direct
            </a>
        </div>
    </div>
</section>
@endsection
