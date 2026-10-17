@php
    $primaryColor = $branding?->primary_color ?: '#0f52ba';
    $secondaryColor = $branding?->secondary_color ?: '#495057';
    $accentColor = $branding?->accent_color ?: '#00a86b';
    $brandMonogram = setting('brand_monogram', 'R');
    $brandName = setting('company_name', $org?->name ?? 'RehoSpace');
    $currentOgImage = setting('og_default_image') ?: asset('images/og-default.jpg');
    $colourFields = [
        'primary_color' => ['label' => __('settings.primary_colour'), 'help' => __('settings.primary_colour_help'), 'value' => $primaryColor, 'var' => '--rrep-primary'],
        'secondary_color' => ['label' => __('settings.secondary_colour'), 'help' => __('settings.secondary_colour_help'), 'value' => $secondaryColor, 'var' => '--rrep-secondary'],
        'accent_color' => ['label' => __('settings.accent_colour'), 'help' => __('settings.accent_colour_help'), 'value' => $accentColor, 'var' => '--rrep-accent'],
    ];
@endphp

<form action="{{ route('settings.branding') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="row g-3 g-lg-4">
        <div class="col-12 col-xl-8">
            {{-- Identity --}}
            <div class="card rounded-4 mb-3">
                <div class="card-header">
                    <h5 class="brand-font mb-0"><i class="bi bi-building text-primary me-1.5"></i>{{ __('settings.identity_title') }}</h5>
                    <small class="text-muted fw-normal">{{ __('settings.identity_subtitle') }}</small>
                </div>
                <div class="card-body p-3 p-md-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-8">
                            <label class="form-label fw-semibold" for="companyName">{{ __('settings.company_name') }}</label>
                            <input type="text" name="company_name" class="form-control" id="companyName" autocomplete="organization" value="{{ $brandName }}" placeholder="AVENIX Company Limited" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold" for="brandMonogram">{{ __('settings.monogram') }}</label>
                            <input type="text" name="brand_monogram" class="form-control text-center fw-bold fs-5" id="brandMonogram" maxlength="3" value="{{ $brandMonogram }}" placeholder="R">
                            <div class="form-text">{{ __('settings.monogram_help') }}</div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold" for="companySubtitle">{{ __('settings.company_subtitle') }}</label>
                            <input type="text" name="company_subtitle" class="form-control" id="companySubtitle" value="{{ setting('company_subtitle', 'Real Estate & Land Survey') }}" placeholder="{{ __('settings.company_subtitle_placeholder') }}">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold" for="companyTagline">{{ __('settings.company_tagline') }}</label>
                            <input type="text" name="company_tagline" class="form-control" id="companyTagline" value="{{ $branding?->company_tagline ?? setting('company_tagline', 'Verified Real Estate & Land Survey Marketplace') }}" placeholder="{{ __('settings.company_tagline_placeholder') }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Logo, favicon, share image --}}
            <div class="card rounded-4 mb-3">
                <div class="card-header">
                    <h5 class="brand-font mb-0"><i class="bi bi-image text-primary me-1.5"></i>{{ __('settings.logos_title') }}</h5>
                    <small class="text-muted fw-normal">{{ __('settings.logos_subtitle') }}</small>
                </div>
                <div class="card-body p-3 p-md-4">
                    <div class="row g-3 g-md-4">
                        {{-- Header logo --}}
                        <div class="col-12 col-md-6">
                            <h6 class="fw-bold mb-2">{{ __('settings.header_logo') }}</h6>
                            <div class="d-flex align-items-center gap-3 mb-2">
                                <div class="p-2 bg-white border rounded-3 shadow-xs d-inline-flex align-items-center justify-content-center flex-shrink-0" id="headerLogoPreview">
                                    @if(!empty($branding?->header_logo))
                                        <img src="{{ $branding->header_logo }}" alt="{{ __('settings.current_logo') }}" class="img-fluid object-fit-contain" style="max-height:48px;max-width:140px;">
                                    @else
                                        <span class="btn-icon bg-primary text-white fw-bold rounded-3">{{ $brandMonogram }}</span>
                                    @endif
                                </div>
                                <div class="flex-grow-1 min-w-0">
                                    <label class="form-label small fw-semibold mb-1" for="headerLogoFile">{{ __('settings.upload_logo') }}</label>
                                    <input type="file" name="header_logo_file" class="form-control" id="headerLogoFile" accept="image/*" data-preview-target="#headerLogoPreview">
                                </div>
                            </div>
                            <label class="form-label small text-muted mb-1" for="headerLogoUrl">{{ __('settings.or_logo_url') }}</label>
                            <input type="url" name="header_logo_url" class="form-control" id="headerLogoUrl" inputmode="url" value="{{ $branding?->header_logo }}" placeholder="https://example.com/logo.png">
                            <div class="form-text">{{ __('settings.logo_help') }}</div>
                        </div>

                        {{-- Favicon --}}
                        <div class="col-12 col-md-6">
                            <h6 class="fw-bold mb-2">{{ __('settings.favicon') }}</h6>
                            <div class="d-flex align-items-center gap-3 mb-2">
                                <div class="p-2 bg-white border rounded-3 shadow-xs d-inline-flex align-items-center justify-content-center flex-shrink-0" id="faviconPreview">
                                    @if(!empty($branding?->favicon))
                                        <img src="{{ $branding->favicon }}" alt="{{ __('settings.current_favicon') }}" class="object-fit-contain" width="36" height="36">
                                    @else
                                        <span class="btn-icon bg-secondary text-white rounded-3"><i class="bi bi-globe"></i></span>
                                    @endif
                                </div>
                                <div class="flex-grow-1 min-w-0">
                                    <label class="form-label small fw-semibold mb-1" for="faviconFile">{{ __('settings.upload_favicon') }}</label>
                                    <input type="file" name="favicon_file" class="form-control" id="faviconFile" accept="image/*,.ico" data-preview-target="#faviconPreview">
                                </div>
                            </div>
                            <label class="form-label small text-muted mb-1" for="faviconUrl">{{ __('settings.or_favicon_url') }}</label>
                            <input type="url" name="favicon_url" class="form-control" id="faviconUrl" inputmode="url" value="{{ $branding?->favicon }}" placeholder="https://example.com/favicon.png">
                            <div class="form-text">{{ __('settings.favicon_help') }}</div>
                        </div>

                        {{-- Open Graph / WhatsApp share card --}}
                        <div class="col-12 pt-3 border-top">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                                <h6 class="fw-bold mb-0"><i class="bi bi-whatsapp text-success me-1"></i>{{ __('settings.share_image') }}</h6>
                                <span class="badge bg-success-subtle text-success border border-success-subtle">1200 × 630 px · JPG/PNG · &lt; 300KB</span>
                            </div>
                            <p class="small text-muted mb-3">{{ __('settings.share_image_help') }}</p>
                            <div class="row g-3 align-items-center">
                                <div class="col-12 col-md-5">
                                    <div class="ratio ratio-16x9 rounded-3 overflow-hidden border shadow-xs bg-dark">
                                        <img src="{{ $currentOgImage }}" alt="{{ __('settings.share_image_preview') }}" class="object-fit-cover" id="shareImagePreview" loading="lazy">
                                    </div>
                                </div>
                                <div class="col-12 col-md-7">
                                    <label class="form-label small fw-semibold mb-1" for="shareImageFile">{{ __('settings.upload_share_image') }}</label>
                                    <input type="file" name="social_share_image_file" class="form-control mb-2" id="shareImageFile" accept="image/jpeg,image/png,image/webp" data-preview-target="#shareImagePreview">
                                    <label class="form-label small text-muted mb-1" for="shareImageUrl">{{ __('settings.or_image_url') }}</label>
                                    <input type="url" name="social_share_image_url" class="form-control" id="shareImageUrl" inputmode="url" value="{{ setting('og_default_image') }}" placeholder="{{ asset('images/og-default.jpg') }}">
                                    <div class="form-text">{{ __('settings.share_image_default_help') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Colours & CSS --}}
            <div class="card rounded-4 mb-3">
                <div class="card-header">
                    <h5 class="brand-font mb-0"><i class="bi bi-palette-fill text-warning me-1.5"></i>{{ __('settings.colours_title') }}</h5>
                    <small class="text-muted fw-normal">{{ __('settings.colours_subtitle') }}</small>
                </div>
                <div class="card-body p-3 p-md-4">
                    <div class="row g-3 mb-3">
                        @foreach($colourFields as $name => $field)
                            @php $fieldId = Str::camel($name); @endphp
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold" for="{{ $fieldId }}">{{ $field['label'] }}</label>
                                <div class="input-group">
                                    <input type="color" name="{{ $name }}" class="form-control form-control-color" id="{{ $fieldId }}" value="{{ $field['value'] }}" data-hex-target="#{{ $fieldId }}Hex" data-preview-var="{{ $field['var'] }}" title="{{ $field['label'] }}">
                                    <input type="text" class="form-control font-monospace text-uppercase" id="{{ $fieldId }}Hex" value="{{ $field['value'] }}" maxlength="7" pattern="#[0-9a-fA-F]{6}" aria-label="{{ $field['label'] }} (hex)" autocomplete="off">
                                </div>
                                <div class="form-text">{{ $field['help'] }}</div>
                            </div>
                        @endforeach
                    </div>

                    <label class="form-label fw-semibold" for="customCss"><i class="bi bi-code-slash me-1"></i>{{ __('settings.custom_css') }}</label>
                    <textarea name="custom_css" class="form-control font-monospace small" id="customCss" rows="4" placeholder="/* .hero-title { font-weight: 900; } */">{{ $branding?->custom_css }}</textarea>
                    <div class="form-text">{{ __('settings.custom_css_help') }}</div>
                </div>
                <div class="card-footer bg-white border-top-0 pt-0 pb-3 px-3 px-md-4">
                    <div class="d-grid d-sm-flex justify-content-sm-end">
                        <button type="submit" class="btn btn-primary fw-bold rounded-pill px-4 py-2 shadow-sm">
                            <i class="bi bi-save me-1"></i>{{ __('settings.save_branding') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Live preview --}}
        <div class="col-12 col-xl-4">
            <div class="card rounded-4 position-sticky" style="top: calc(var(--rrep-topbar-height) + 1rem);">
                <div class="card-header">
                    <h6 class="brand-font mb-0"><i class="bi bi-eye me-1.5 text-primary"></i>{{ __('settings.live_preview') }}</h6>
                    <small class="text-muted fw-normal">{{ __('settings.live_preview_help') }}</small>
                </div>
                <div class="card-body p-3">
                    <div class="rounded-4 border overflow-hidden" id="brandPreviewCard" style="--rrep-primary: {{ $primaryColor }}; --rrep-secondary: {{ $secondaryColor }}; --rrep-accent: {{ $accentColor }};">
                        <div class="d-flex align-items-center gap-2 p-3 text-white" style="background: var(--rrep-secondary);">
                            <span class="btn-icon rounded-3 fw-bold text-white" style="background: var(--rrep-primary);" id="brandPreviewMonogram">{{ $brandMonogram }}</span>
                            <span class="fw-bold text-truncate" id="brandPreviewName">{{ $brandName }}</span>
                        </div>
                        <div class="p-3 bg-white">
                            <div class="fw-bold mb-1" style="color: var(--rrep-primary);">{{ __('settings.preview_headline') }}</div>
                            <div class="small text-muted mb-3">{{ __('settings.preview_body') }}</div>
                            <div class="d-flex flex-wrap gap-2">
                                <span class="btn btn-sm text-white fw-semibold rounded-pill px-3" style="background: var(--rrep-primary);">{{ __('settings.preview_primary_btn') }}</span>
                                <span class="btn btn-sm text-white fw-semibold rounded-pill px-3" style="background: var(--rrep-accent);">{{ __('settings.preview_accent_btn') }}</span>
                            </div>
                        </div>
                        <div class="d-flex small">
                            <div class="flex-fill p-2 text-center text-white" style="background: var(--rrep-primary);">{{ __('settings.primary_colour') }}</div>
                            <div class="flex-fill p-2 text-center text-white" style="background: var(--rrep-secondary);">{{ __('settings.secondary_colour') }}</div>
                            <div class="flex-fill p-2 text-center text-white" style="background: var(--rrep-accent);">{{ __('settings.accent_colour') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
