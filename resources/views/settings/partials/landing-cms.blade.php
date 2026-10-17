@php
    $visibilityToggles = [
        'landing_topbar_enabled' => ['id' => 'toggleTopbar', 'label' => __('settings.vis_topbar')],
        'landing_navbar_enabled' => ['id' => 'toggleNavbar', 'label' => __('settings.vis_navbar')],
        'landing_hero_enabled' => ['id' => 'toggleHero', 'label' => __('settings.vis_hero')],
        'landing_search_enabled' => ['id' => 'toggleHeroSearch', 'label' => __('settings.vis_search')],
        'landing_categories_enabled' => ['id' => 'toggleQuickBrowse', 'label' => __('settings.vis_categories')],
        'landing_featured_enabled' => ['id' => 'toggleFeatured', 'label' => __('settings.vis_featured')],
        'landing_latest_enabled' => ['id' => 'toggleLatest', 'label' => __('settings.vis_latest')],
        'landing_land_enabled' => ['id' => 'toggleLand', 'label' => __('settings.vis_land')],
        'landing_services_enabled' => ['id' => 'toggleServices', 'label' => __('settings.vis_services')],
        'landing_projects_enabled' => ['id' => 'toggleProjects', 'label' => __('settings.vis_projects')],
        'landing_trust_enabled' => ['id' => 'toggleTrust', 'label' => __('settings.vis_trust')],
        'landing_owner_cta_enabled' => ['id' => 'toggleOwnerCta', 'label' => __('settings.vis_owner_cta')],
        'landing_whatsapp_enabled' => ['id' => 'toggleWhatsapp', 'label' => __('settings.vis_whatsapp')],
        'landing_nav_list_btn_enabled' => ['id' => 'toggleListBtn', 'label' => __('settings.vis_list_btn')],
    ];
    $cmsSections = [
        'visibility' => ['icon' => 'bi-toggles', 'title' => __('settings.cms_visibility'), 'hint' => __('settings.cms_visibility_hint')],
        'topbar' => ['icon' => 'bi-window-dock', 'title' => __('settings.cms_topbar'), 'hint' => __('settings.cms_topbar_hint')],
        'navbar' => ['icon' => 'bi-menu-button-wide', 'title' => __('settings.cms_navbar'), 'hint' => __('settings.cms_navbar_hint')],
        'hero' => ['icon' => 'bi-image-fill', 'title' => __('settings.cms_hero'), 'hint' => __('settings.cms_hero_hint')],
        'search' => ['icon' => 'bi-search', 'title' => __('settings.cms_search'), 'hint' => __('settings.cms_search_hint')],
        'services' => ['icon' => 'bi-gear-wide-connected', 'title' => __('settings.cms_services'), 'hint' => __('settings.cms_services_hint')],
        'projects' => ['icon' => 'bi-layers-half', 'title' => __('settings.cms_projects'), 'hint' => __('settings.cms_projects_hint')],
        'trust' => ['icon' => 'bi-bar-chart-line', 'title' => __('settings.cms_trust'), 'hint' => __('settings.cms_trust_hint')],
        'owner-cta' => ['icon' => 'bi-megaphone', 'title' => __('settings.cms_owner_cta'), 'hint' => __('settings.cms_owner_cta_hint')],
        'whatsapp' => ['icon' => 'bi-whatsapp', 'title' => __('settings.cms_whatsapp'), 'hint' => __('settings.cms_whatsapp_hint')],
        'footer' => ['icon' => 'bi-layout-text-window-reverse', 'title' => __('settings.cms_footer'), 'hint' => __('settings.cms_footer_hint')],
        'about' => ['icon' => 'bi-info-circle-fill', 'title' => __('settings.cms_about'), 'hint' => __('settings.cms_about_hint')],
    ];
    $currentHeroImg = setting('landing_hero_image') ?: asset('images/hero-dodoma-plot.jpg');
    $storyPreview = setting('about_story_image') ?: 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?w=800&auto=format&fit=crop&q=80';
    $defaultLocale = setting('default_locale', config('app.locale', 'sw'));
@endphp

<form action="{{ route('settings.landing') }}" method="POST" enctype="multipart/form-data">
    @csrf

    {{-- Header --}}
    <div class="card rounded-4 border-0 mb-3 bg-primary text-white">
        <div class="card-body p-3 p-md-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="min-w-0">
                <h4 class="brand-font mb-1 text-white"><i class="bi bi-globe2 me-2"></i>{{ __('settings.cms_title') }}</h4>
                <p class="mb-0 text-white-50 small">{{ __('settings.cms_subtitle') }}</p>
            </div>
            <div class="d-flex flex-wrap gap-2 w-100 w-sm-auto">
                <a href="{{ route('public.home') }}" target="_blank" rel="noopener" class="btn btn-light fw-bold flex-fill">
                    <i class="bi bi-box-arrow-up-right me-1"></i>{{ __('settings.preview_site') }}
                </a>
                <button type="submit" class="btn btn-warning fw-bold shadow flex-fill">
                    <i class="bi bi-save me-1"></i>{{ __('settings.save_landing') }}
                </button>
            </div>
        </div>
    </div>

    <div class="accordion shadow-xs rounded-4 overflow-hidden mb-3" id="landingAccordion">
        @foreach($cmsSections as $sectionKey => $section)
            @php $collapseId = 'cms-'.$sectionKey; @endphp
            <div class="accordion-item">
                <h2 class="accordion-header" id="{{ $collapseId }}-heading">
                    <button class="accordion-button fw-bold {{ $loop->first ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $collapseId }}" aria-expanded="{{ $loop->first ? 'true' : 'false' }}" aria-controls="{{ $collapseId }}">
                        <i class="bi {{ $section['icon'] }} text-primary me-2"></i>
                        <span class="min-w-0">
                            <span class="d-block">{{ $loop->iteration }}. {{ $section['title'] }}</span>
                            <small class="d-block text-muted fw-normal">{{ $section['hint'] }}</small>
                        </span>
                    </button>
                </h2>
                <div id="{{ $collapseId }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" aria-labelledby="{{ $collapseId }}-heading">
                    <div class="accordion-body p-3 p-md-4">

                        @switch($sectionKey)
                            @case('visibility')
                                <div class="row g-2 g-md-3">
                                    @foreach($visibilityToggles as $name => $toggle)
                                        <div class="col-12 col-sm-6 col-lg-4">
                                            <div class="form-check form-switch p-3 ps-5 border rounded-3 bg-light h-100">
                                                <input type="hidden" name="{{ $name }}" value="0">
                                                <input class="form-check-input" type="checkbox" role="switch" name="{{ $name }}" value="1" id="{{ $toggle['id'] }}" @checked(setting($name, '1') === '1')>
                                                <label class="form-check-label fw-semibold small" for="{{ $toggle['id'] }}">{{ $toggle['label'] }}</label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                @break

                            @case('topbar')
                                <h6 class="fw-bold mb-3"><i class="bi bi-geo-alt-fill text-danger me-1"></i>{{ __('settings.topbar_ticker_title') }}</h6>
                                <div class="row g-3 mb-4">
                                    @include('settings.partials.landing-field', ['base' => 'landing_topbar_ticker_label', 'label' => __('settings.topbar_ticker_label'), 'swFallback' => setting('landing_topbar_ticker_label', 'Tanzania:'), 'enFallback' => setting('landing_topbar_ticker_label', 'Tanzania:')])
                                    @include('settings.partials.landing-field', ['base' => 'landing_topbar_ticker_text', 'label' => __('settings.topbar_ticker_text'), 'swFallback' => setting('landing_topbar_ticker_text', 'Dar es Salaam • Dodoma • Arusha • Mwanza'), 'enFallback' => setting('landing_topbar_ticker_text', 'Dar es Salaam • Dodoma • Arusha • Mwanza'), 'help' => __('settings.topbar_ticker_text_help')])
                                </div>

                                <h6 class="fw-bold mb-3 border-top pt-3">{{ __('settings.topbar_links_title') }}</h6>
                                <div class="row g-3">
                                    <div class="col-12 col-md-6">
                                        <label class="form-label fw-semibold mb-1" for="landingContactPhone">{{ __('settings.hotline_phone') }}</label>
                                        <input type="tel" name="contact_phone" class="form-control" id="landingContactPhone" data-tz-phone autocomplete="tel" placeholder="0784 100 200" value="{{ setting('contact_phone', '+255 784 100 200') }}">
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label fw-semibold mb-1" for="landingContactEmail">{{ __('settings.support_email') }}</label>
                                        <input type="email" name="contact_email" class="form-control" id="landingContactEmail" inputmode="email" autocomplete="email" value="{{ setting('contact_email', 'info@rehospace.co.tz') }}">
                                    </div>
                                    @include('settings.partials.landing-field', ['base' => 'landing_topbar_survey_text', 'label' => __('settings.topbar_survey_btn'), 'sw' => 'Portal ya Upimaji Ardhi', 'en' => 'Cadastral Survey Portal'])
                                    @include('settings.partials.landing-field', ['base' => 'landing_topbar_staff_text', 'label' => __('settings.topbar_staff_btn'), 'sw' => 'Mfumo wa Wafanyakazi', 'en' => 'Staff Portal'])
                                </div>
                                @break

                            @case('navbar')
                                <h6 class="fw-bold mb-3">{{ __('settings.nav_labels_title') }}</h6>
                                <div class="row g-3 mb-4">
                                    @include('settings.partials.landing-field', ['base' => 'landing_nav_home_label', 'label' => __('settings.nav_home'), 'sw' => 'Nyumbani', 'en' => 'Home', 'col' => 'col-12 col-md-6 col-xl-4'])
                                    @include('settings.partials.landing-field', ['base' => 'landing_nav_about_label', 'label' => __('settings.nav_about'), 'sw' => 'Kuhusu Sisi', 'en' => 'About Us', 'col' => 'col-12 col-md-6 col-xl-4'])
                                    @include('settings.partials.landing-field', ['base' => 'landing_nav_services_label', 'label' => __('settings.nav_services'), 'sw' => 'Huduma Zetu', 'en' => 'Services', 'col' => 'col-12 col-md-6 col-xl-4'])
                                    @include('settings.partials.landing-field', ['base' => 'landing_nav_projects_label', 'label' => __('settings.nav_projects'), 'sw' => 'Miradi', 'en' => 'Projects', 'col' => 'col-12 col-md-6 col-xl-4'])
                                    @include('settings.partials.landing-field', ['base' => 'landing_nav_contact_label', 'label' => __('settings.nav_contact'), 'sw' => 'Wasiliana Nasi', 'en' => 'Contact Us', 'col' => 'col-12 col-md-6 col-xl-4'])
                                </div>

                                <h6 class="fw-bold mb-3 border-top pt-3">{{ __('settings.nav_buttons_title') }}</h6>
                                <div class="row g-3">
                                    @include('settings.partials.landing-field', ['base' => 'landing_nav_login_btn_text', 'label' => __('settings.nav_login_btn'), 'sw' => 'Ingia', 'en' => 'Login'])
                                    @include('settings.partials.landing-field', ['base' => 'landing_nav_list_btn_text', 'label' => __('settings.nav_list_btn'), 'sw' => 'Weka Tangazo', 'en' => 'List Property'])
                                    <div class="col-12 col-md-6">
                                        <label class="form-label fw-semibold mb-1" for="landingNavListBtnIcon">{{ __('settings.nav_list_btn_icon') }}</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi {{ setting('landing_nav_list_btn_icon', 'bi-plus-circle-fill') }}"></i></span>
                                            <input type="text" name="landing_nav_list_btn_icon" class="form-control font-monospace" id="landingNavListBtnIcon" value="{{ setting('landing_nav_list_btn_icon', 'bi-plus-circle-fill') }}" placeholder="bi-plus-circle-fill">
                                        </div>
                                        <div class="form-text">{{ __('settings.icon_class_help') }}</div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label fw-semibold mb-1" for="landingNavListBtnUrl">{{ __('settings.nav_list_btn_url') }}</label>
                                        <input type="url" name="landing_nav_list_btn_url" class="form-control" id="landingNavListBtnUrl" inputmode="url" value="{{ setting('landing_nav_list_btn_url', route('login')) }}" placeholder="https://...">
                                    </div>
                                </div>
                                @break

                            @case('hero')
                                <div class="row g-3 mb-4">
                                    @include('settings.partials.landing-field', ['base' => 'landing_hero_badge_text', 'label' => __('settings.hero_badge'), 'sw' => 'Portal Iliyothibitishwa ya Ardhi', 'en' => 'Verified Land & Survey Portal', 'col' => 'col-12'])
                                    @include('settings.partials.landing-field', ['base' => 'landing_hero_title', 'label' => __('settings.hero_title'), 'type' => 'textarea', 'sw' => 'Miradi ya Viwanja & Upimaji wa Ardhi Tanzania', 'en' => 'Cadastral Land & Master Projects in Tanzania'])
                                    @include('settings.partials.landing-field', ['base' => 'landing_hero_subtitle', 'label' => __('settings.hero_subtitle'), 'type' => 'textarea', 'sw' => 'Pata viwanja vilivyopimwa vyenye Hati Miliki na Michoro ya Mipango Miji kwa uwazi na haraka.', 'en' => 'Verified land plots with Title Deeds, Town Planning drawings, and professional geomatics.'])
                                    @include('settings.partials.landing-field', ['base' => 'landing_hero_cta_primary_text', 'label' => __('settings.hero_cta_primary'), 'sw' => 'Tazama Miradi', 'en' => 'Explore Projects'])
                                    @include('settings.partials.landing-field', ['base' => 'landing_hero_cta_secondary_text', 'label' => __('settings.hero_cta_secondary'), 'sw' => 'Huduma Zetu', 'en' => 'Our Services'])
                                </div>

                                <div class="p-3 bg-light rounded-3 border">
                                    <h6 class="fw-bold mb-2"><i class="bi bi-card-image text-primary me-1"></i>{{ __('settings.hero_image') }}</h6>
                                    <div class="row g-3 align-items-center">
                                        <div class="col-12 col-md-4">
                                            <div class="ratio ratio-16x9 rounded-3 overflow-hidden border shadow-xs bg-dark">
                                                <img src="{{ $currentHeroImg }}" class="object-fit-cover" id="heroImagePreview" alt="{{ __('settings.hero_image') }}" loading="lazy">
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <label class="form-label fw-semibold mb-1" for="landingHeroImageFile">{{ __('settings.upload_image') }}</label>
                                            <input type="file" name="landing_hero_image_file" class="form-control" id="landingHeroImageFile" accept="image/*" data-preview-target="#heroImagePreview">
                                            <div class="form-text">{{ __('settings.hero_image_help') }}</div>
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <label class="form-label fw-semibold mb-1" for="landingHeroImageUrl">{{ __('settings.or_image_url') }}</label>
                                            <input type="url" name="landing_hero_image" class="form-control" id="landingHeroImageUrl" inputmode="url" value="{{ setting('landing_hero_image') }}" placeholder="https://...">
                                        </div>
                                    </div>
                                </div>
                                @break

                            @case('search')
                                <div class="row g-3">
                                    @include('settings.partials.landing-field', ['base' => 'landing_search_tab_buy', 'label' => __('settings.search_tab_buy'), 'sw' => 'Nunua', 'en' => 'Buy', 'col' => 'col-12 col-md-6 col-xl-3'])
                                    @include('settings.partials.landing-field', ['base' => 'landing_search_tab_rent', 'label' => __('settings.search_tab_rent'), 'sw' => 'Panga', 'en' => 'Rent', 'col' => 'col-12 col-md-6 col-xl-3'])
                                    @include('settings.partials.landing-field', ['base' => 'landing_search_tab_land', 'label' => __('settings.search_tab_land'), 'sw' => 'Ardhi & Viwanja', 'en' => 'Land & Plots', 'col' => 'col-12 col-md-6 col-xl-3'])
                                    @include('settings.partials.landing-field', ['base' => 'landing_search_tab_developments', 'label' => __('settings.search_tab_developments'), 'sw' => 'Miradi', 'en' => 'Developments', 'col' => 'col-12 col-md-6 col-xl-3'])
                                    @include('settings.partials.landing-field', ['base' => 'landing_search_btn_text', 'label' => __('settings.search_btn_text'), 'sw' => 'Tafuta', 'en' => 'Search'])
                                    <div class="col-12 col-md-6">
                                        <label class="form-label fw-semibold mb-1" for="landingSearchBtnIcon">{{ __('settings.search_btn_icon') }}</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi {{ setting('landing_search_btn_icon', 'bi-search') }}"></i></span>
                                            <input type="text" name="landing_search_btn_icon" class="form-control font-monospace" id="landingSearchBtnIcon" value="{{ setting('landing_search_btn_icon', 'bi-search') }}" placeholder="bi-search">
                                        </div>
                                        <div class="form-text">{{ __('settings.icon_class_help') }}</div>
                                    </div>
                                </div>
                                @break

                            @case('services')
                                <div class="row g-3">
                                    @include('settings.partials.landing-field', ['base' => 'landing_services_tag', 'label' => __('settings.section_tag'), 'sw' => 'Ufumbuzi wa Kitaalamu', 'en' => 'Professional Solutions'])
                                    @include('settings.partials.landing-field', ['base' => 'landing_services_title', 'label' => __('settings.section_title'), 'sw' => 'Huduma Zetu 8 Kuu', 'en' => 'Our 8 Core Services'])
                                </div>
                                @break

                            @case('projects')
                                <div class="row g-3">
                                    @include('settings.partials.landing-field', ['base' => 'landing_projects_tag', 'label' => __('settings.section_tag'), 'sw' => 'Ramani za Viwanja & Mipango Miji', 'en' => 'Master Plans & Cadastral GIS'])
                                    @include('settings.partials.landing-field', ['base' => 'landing_projects_title', 'label' => __('settings.section_title'), 'sw' => 'Miradi ya Viwanja & Ramani za Shapefile', 'en' => 'Land Projects & Interactive Plot Maps'])
                                    @include('settings.partials.landing-field', ['base' => 'landing_projects_subtitle', 'label' => __('settings.section_subtitle'), 'sw' => 'Vipimo halisi vya GIS, michoro ya mipango miji na status ya viwanja vilivyopimwa.', 'en' => 'Verified GIS boundaries, cadastral master plans, and live plot availability.'])
                                    @include('settings.partials.landing-field', ['base' => 'landing_projects_cta_text', 'label' => __('settings.cta_button_text'), 'sw' => 'Miradi Yote', 'en' => 'Explore All'])
                                </div>
                                @break

                            @case('trust')
                                @php
                                    $statDefaults = [
                                        1 => ['label' => __('settings.stat_projects'), 'sw' => 'Miradi ya Mipango', 'en' => 'Master Projects', 'placeholder' => __('settings.auto')],
                                        2 => ['label' => __('settings.stat_plots'), 'sw' => 'Viwanja Vilivyopimwa', 'en' => 'Surveyed Plots', 'placeholder' => __('settings.auto')],
                                        3 => ['label' => __('settings.stat_services'), 'sw' => 'Huduma Kuu', 'en' => 'Core Services', 'placeholder' => '8'],
                                        4 => ['label' => __('settings.stat_clients'), 'sw' => 'Wateja Walioridhika', 'en' => 'Satisfied Clients', 'placeholder' => __('settings.auto')],
                                    ];
                                @endphp
                                <p class="small text-muted mb-3">{{ __('settings.trust_help') }}</p>
                                <div class="row g-3">
                                    @foreach($statDefaults as $i => $stat)
                                        <div class="col-12 col-md-6 col-xl-3">
                                            <div class="p-3 border rounded-3 bg-light h-100">
                                                <div class="row g-2">
                                                    @include('settings.partials.landing-field', ['base' => "landing_stat_{$i}_label", 'label' => $stat['label'], 'sw' => $stat['sw'], 'en' => $stat['en'], 'col' => 'col-12'])
                                                    <div class="col-12">
                                                        <label class="form-label small text-muted mb-1" for="landingStat{{ $i }}Override">{{ __('settings.value_override') }}</label>
                                                        <input type="text" name="landing_stat_{{ $i }}_override" class="form-control" id="landingStat{{ $i }}Override" inputmode="numeric" value="{{ setting("landing_stat_{$i}_override") }}" placeholder="{{ $stat['placeholder'] }}">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                @break

                            @case('owner-cta')
                                <div class="row g-3">
                                    @include('settings.partials.landing-field', ['base' => 'landing_cta_banner_badge', 'label' => __('settings.badge_text'), 'sw' => 'Wataalamu wa Upimaji Ardhi', 'en' => 'Geomatics & Land Advisory'])
                                    @include('settings.partials.landing-field', ['base' => 'landing_cta_banner_btn_text', 'label' => __('settings.contact_button_text'), 'sw' => 'Wasiliana Nasi', 'en' => 'Contact Us'])
                                    @include('settings.partials.landing-field', ['base' => 'landing_cta_banner_title', 'label' => __('settings.banner_title'), 'sw' => 'Unahitaji Kupima Ardhi au Kupata Kiwanja?', 'en' => 'Ready to Secure Your Land or Request a Survey?'])
                                    @include('settings.partials.landing-field', ['base' => 'landing_cta_banner_subtitle', 'label' => __('settings.banner_subtitle'), 'type' => 'textarea', 'sw' => 'Wasiliana na wataalamu wetu wa geomatics na ardhi kupata msaada wa haraka na wa uhakika.', 'en' => 'Contact our geomatics consultants and surveyors today for swift assistance.'])
                                </div>
                                @break

                            @case('whatsapp')
                                <div class="row g-3">
                                    <div class="col-12 col-md-6">
                                        <label class="form-label fw-semibold mb-1" for="defaultLocale"><i class="bi bi-globe text-primary me-1"></i>{{ __('settings.default_language') }}</label>
                                        <select name="default_locale" class="form-select" id="defaultLocale">
                                            <option value="sw" @selected($defaultLocale === 'sw')>Kiswahili</option>
                                            <option value="en" @selected($defaultLocale === 'en')>English</option>
                                        </select>
                                        <div class="form-text">{{ __('settings.default_language_help') }}</div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label fw-semibold mb-1" for="landingContactWhatsapp"><i class="bi bi-whatsapp text-success me-1"></i>{{ __('settings.contact_whatsapp') }}</label>
                                        <input type="tel" name="contact_whatsapp" class="form-control" id="landingContactWhatsapp" data-tz-phone="e164" inputmode="numeric" placeholder="255784100200" value="{{ setting('contact_whatsapp', '255784100200') }}">
                                        <div class="form-text">{{ __('settings.contact_whatsapp_help') }}</div>
                                    </div>
                                    @include('settings.partials.landing-field', ['base' => 'landing_whatsapp_message', 'label' => __('settings.whatsapp_message'), 'type' => 'textarea', 'sw' => 'Habari, ningependa kupata maelezo kuhusu miradi ya viwanja na upimaji ardhi.', 'en' => 'Hello, I am interested in exploring plot projects and land survey services.', 'col' => 'col-12', 'help' => __('settings.whatsapp_message_help')])
                                </div>
                                @break

                            @case('footer')
                                <div class="row g-3">
                                    @include('settings.partials.landing-field', ['base' => 'footer_bio', 'label' => __('settings.footer_bio'), 'type' => 'textarea', 'sw' => 'Mfumo rasmi wa kielektroniki wa mauzo ya viwanja na upimaji wa ardhi Tanzania.', 'en' => 'Verified Real Estate & Land Survey Marketplace. The premier digital property marketplace and cadastral surveying ecosystem in Tanzania.', 'col' => 'col-12'])
                                    @include('settings.partials.landing-field', ['base' => 'footer_newsletter_title', 'label' => __('settings.newsletter_title'), 'sw' => 'Pata Taarifa za Viwanja', 'en' => 'Stay Updated'])
                                    @include('settings.partials.landing-field', ['base' => 'footer_newsletter_subtitle', 'label' => __('settings.newsletter_subtitle'), 'sw' => 'Jiunge kupata taarifa za miradi mipya ya viwanja vilivyopimwa.', 'en' => 'Subscribe for newly verified land projects and survey updates.'])
                                    @include('settings.partials.landing-field', ['base' => 'footer_newsletter_btn_text', 'label' => __('settings.newsletter_btn'), 'sw' => 'Jiunge', 'en' => 'Subscribe'])
                                    @include('settings.partials.landing-field', ['base' => 'footer_copyright', 'label' => __('settings.footer_copyright'), 'sw' => 'Haki Zote Zimehifadhiwa.', 'en' => 'All Rights Reserved.'])
                                </div>
                                @break

                            @case('about')
                                <h6 class="fw-bold mb-3">{{ __('settings.about_hero_title') }}</h6>
                                <div class="row g-3 mb-4">
                                    @include('settings.partials.landing-field', ['base' => 'about_hero_badge', 'label' => __('settings.badge_text'), 'sw' => 'Kuhusu REMS & Kampuni', 'en' => 'About REMS & Platform'])
                                    @include('settings.partials.landing-field', ['base' => 'about_hero_title', 'label' => __('settings.hero_title'), 'type' => 'textarea', 'sw' => 'Wataalamu wa Upimaji Ardhi & Usimamizi wa Majengo Tanzania', 'en' => 'Master Land Surveying & Property Geomatics in Tanzania'])
                                    @include('settings.partials.landing-field', ['base' => 'about_hero_subtitle', 'label' => __('settings.hero_subtitle'), 'type' => 'textarea', 'sw' => 'Tuko hapa kukupa huduma bora, za uwazi na zilizothibitishwa za mauzo ya viwanja, upimaji ardhi, na michoro ya mipango miji.', 'en' => 'Empowering landowners and property investors with verified cadastral surveys, title deed processing, and master-planned land projects.'])
                                </div>

                                <h6 class="fw-bold mb-3 border-top pt-3">{{ __('settings.about_story_title') }}</h6>
                                <div class="row g-3 mb-4">
                                    @include('settings.partials.landing-field', ['base' => 'about_story_tag', 'label' => __('settings.section_tag'), 'sw' => 'Uzoefu & Uaminifu', 'en' => 'Our History & Experience'])
                                    @include('settings.partials.landing-field', ['base' => 'about_story_title', 'label' => __('settings.section_title'), 'sw' => 'Kuinua Viwango vya Upimaji na Mauzo ya Viwanja', 'en' => 'Elevating Land Surveying & Property Standards'])
                                    @include('settings.partials.landing-field', ['base' => 'about_story_bio', 'label' => __('settings.story_paragraph'), 'type' => 'textarea', 'rows' => 3, 'col' => 'col-12', 'sw' => 'Tunatoa huduma kamili za kisheria na kitaalamu kwa kushirikiana na Wapimaji Ardhi waliosajiliwa na Wataalamu wa Mipango Miji. Mfumo wetu unalinda na kuongeza thamani ya uwekezaji wako wa viwanja na majengo nchini Tanzania.', 'en' => 'We provide comprehensive geomatics and legal land management services in partnership with registered land surveyors and town planners. Our ecosystem protects and elevates your property investments across Tanzania.'])
                                    <div class="col-12">
                                        <div class="p-3 bg-light rounded-3 border">
                                            <h6 class="fw-bold small mb-2"><i class="bi bi-image text-primary me-1"></i>{{ __('settings.story_image') }}</h6>
                                            <div class="row g-3 align-items-center">
                                                <div class="col-12 col-md-4">
                                                    <div class="ratio ratio-16x9 rounded-3 overflow-hidden border shadow-xs bg-dark">
                                                        <img src="{{ $storyPreview }}" class="object-fit-cover" id="storyImagePreview" alt="{{ __('settings.story_image') }}" loading="lazy">
                                                    </div>
                                                </div>
                                                <div class="col-12 col-md-4">
                                                    <label class="form-label fw-semibold mb-1" for="aboutStoryImageFile">{{ __('settings.upload_image') }}</label>
                                                    <input type="file" name="about_story_image_file" class="form-control" id="aboutStoryImageFile" accept="image/*" data-preview-target="#storyImagePreview">
                                                </div>
                                                <div class="col-12 col-md-4">
                                                    <label class="form-label fw-semibold mb-1" for="aboutStoryImageUrl">{{ __('settings.or_image_url') }}</label>
                                                    <input type="url" name="about_story_image" class="form-control" id="aboutStoryImageUrl" inputmode="url" value="{{ setting('about_story_image') }}" placeholder="https://...">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <h6 class="fw-bold mb-3 border-top pt-3">{{ __('settings.about_values_title') }}</h6>
                                <div class="row g-3 mb-4">
                                    @foreach(['vision' => ['label' => __('settings.vision'), 'title_sw' => 'Dira Yetu', 'title_en' => 'Our Vision', 'text_sw' => 'Kuwa kiongozi mkuu wa kitaifa katika kutoa suluhisho la kisasa la upimaji wa viwanja na mauzo ya ardhi yenye Hati Miliki nchini Tanzania.', 'text_en' => 'To be the leading digital infrastructure for verified land surveying, master-planned plot allocation, and property geomatics in Tanzania.'],
                                               'mission' => ['label' => __('settings.mission'), 'title_sw' => 'Dhamira Yetu', 'title_en' => 'Our Mission', 'text_sw' => 'Kuwapa wateja wetu huduma za haraka, za uhakika na zenye uwazi mkubwa katika kupima, kurasimisha na kumilikisha viwanja bila migogoro.', 'text_en' => 'To deliver precise geomatics surveying, seamless title deed processing, and secure land plot acquisitions with zero conflict.'],
                                               'values' => ['label' => __('settings.core_values'), 'title_sw' => 'Misingi Yetu', 'title_en' => 'Our Core Values', 'text_sw' => 'Uaminifu, Usahihi wa Vipimo, Uwazi wa Kisheria na Huduma Bora za Wateja kabla na baada ya ununuzi.', 'text_en' => 'Integrity, Spatial Accuracy, Legal Transparency, and Continuous Customer Support across every transaction.']] as $pillar => $pillarMeta)
                                        <div class="col-12 col-lg-4">
                                            <div class="p-3 border rounded-3 bg-light h-100">
                                                <div class="row g-2">
                                                    @include('settings.partials.landing-field', ['base' => "about_{$pillar}_title", 'label' => $pillarMeta['label'].' — '.__('settings.title_label'), 'sw' => $pillarMeta['title_sw'], 'en' => $pillarMeta['title_en'], 'col' => 'col-12'])
                                                    @include('settings.partials.landing-field', ['base' => "about_{$pillar}_text", 'label' => $pillarMeta['label'].' — '.__('settings.text_label'), 'type' => 'textarea', 'sw' => $pillarMeta['text_sw'], 'en' => $pillarMeta['text_en'], 'col' => 'col-12'])
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <h6 class="fw-bold mb-3 border-top pt-3">{{ __('settings.about_stats_title') }}</h6>
                                <div class="row g-3 mb-4">
                                    @foreach([1 => ['number' => '100%', 'sw' => 'Viwanja Vilivyopimwa', 'en' => 'Surveyed Land Plots'], 2 => ['number' => '50+', 'sw' => 'Miradi ya Viwanja', 'en' => 'Master Land Projects'], 3 => ['number' => '10+', 'sw' => 'Mikoa Tanzania', 'en' => 'Regions Covered'], 4 => ['number' => '5,000+', 'sw' => 'Wateja Waliotimiza Ndoto', 'en' => 'Happy Landowners']] as $i => $stat)
                                        <div class="col-12 col-md-6 col-xl-3">
                                            <div class="p-3 border rounded-3 bg-light h-100">
                                                <div class="row g-2">
                                                    <div class="col-12">
                                                        <label class="form-label fw-semibold mb-1" for="aboutStat{{ $i }}Number">{{ __('settings.stat_n_value', ['n' => $i]) }}</label>
                                                        <input type="text" name="about_stat{{ $i }}_number" class="form-control" id="aboutStat{{ $i }}Number" value="{{ setting("about_stat{$i}_number", $stat['number']) }}" placeholder="{{ $stat['number'] }}">
                                                    </div>
                                                    @include('settings.partials.landing-field', ['base' => "about_stat{$i}_label", 'label' => __('settings.stat_n_label', ['n' => $i]), 'sw' => $stat['sw'], 'en' => $stat['en'], 'col' => 'col-12'])
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <h6 class="fw-bold mb-3 border-top pt-3">{{ __('settings.about_cta_title') }}</h6>
                                <div class="row g-3">
                                    @include('settings.partials.landing-field', ['base' => 'about_cta_title', 'label' => __('settings.banner_title'), 'sw' => 'Unahitaji Ushauri wa Upimaji au Ununuzi wa Kiwanja?', 'en' => 'Need Land Surveying or Plot Purchasing Assistance?'])
                                    @include('settings.partials.landing-field', ['base' => 'about_cta_subtitle', 'label' => __('settings.banner_subtitle'), 'type' => 'textarea', 'sw' => 'Wasiliana na wataalamu wetu wapimaji waliothibitishwa kupata msaada wa haraka.', 'en' => 'Contact our team of registered geomatics experts today for immediate guidance.'])
                                </div>
                                @break
                        @endswitch

                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="d-grid d-sm-flex justify-content-sm-end mb-4">
        <button type="submit" class="btn btn-primary btn-lg fw-bold px-5 shadow rounded-pill">
            <i class="bi bi-check2-circle me-1"></i>{{ __('settings.save_landing_all') }}
        </button>
    </div>
</form>
