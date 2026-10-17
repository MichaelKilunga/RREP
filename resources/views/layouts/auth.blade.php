<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    @php
        $org = current_organization();
        $branding = $org?->branding ?: \App\Models\BrandingConfig::first();
        $companyName = setting('company_name', $org?->name ?? 'RehoSpace');
        $primaryColor = $branding?->primary_color ?? '#0f52ba';
        $secondaryColor = $branding?->secondary_color ?? '#495057';
        $accentColor = $branding?->accent_color ?? '#00a86b';
        $faviconUrl = $branding?->favicon ?: setting('site_favicon');
    @endphp

    <!-- Progressive Web App -->
    <link rel="manifest" href="{{ route('pwa.manifest') }}">
    <meta name="theme-color" content="{{ $primaryColor }}">
    <link rel="apple-touch-icon" sizes="192x192" href="{{ route('pwa.icon', 192) }}">

    <title>@yield('title', 'Sign In') - {{ $companyName }}</title>

    <!-- Dynamic Favicon Injected from System Admin Branding -->
    @if(!empty($faviconUrl))
        <link rel="icon" type="image/x-icon" href="{{ $faviconUrl }}">
        <link rel="shortcut icon" href="{{ $faviconUrl }}">
        <link rel="apple-touch-icon" href="{{ $faviconUrl }}">
    @else
        <link rel="icon" type="image/x-icon" href="/favicon.ico">
    @endif

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    {{ Vite::fonts() }}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --rrep-primary: {{ $primaryColor }};
            --rrep-primary-rgb: {{ brand_rgb($primaryColor) }};
            --rrep-primary-hover: {{ brand_shade($primaryColor, -18) }};
            --rrep-secondary: {{ $secondaryColor }};
            --rrep-accent: {{ $accentColor }};
        }
    </style>

    @if(!empty($branding?->custom_css))
        <!-- Custom CSS Configured in System Admin Panel -->
        <style id="rrep-auth-custom-css">
            {!! $branding->custom_css !!}
        </style>
    @endif
</head>
<body class="auth-shell">
    <div class="container px-0 px-sm-3">
        @yield('content')
    </div>
</body>
</html>
