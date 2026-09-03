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
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --rrep-primary: {{ $primaryColor }};
            --rrep-secondary: {{ $secondaryColor }};
            --rrep-accent: {{ $accentColor }};
        }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .brand-font { font-family: 'Plus Jakarta Sans', sans-serif; }
        .auth-card {
            background: #ffffff;
            border-radius: 1rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            overflow: hidden;
            width: 100%;
            max-width: 440px;
        }
        .btn-primary {
            background-color: var(--rrep-primary);
            border-color: var(--rrep-primary);
        }
        .btn-primary:hover, .btn-primary:focus {
            filter: brightness(0.9);
            background-color: var(--rrep-primary);
            border-color: var(--rrep-primary);
        }
        .text-primary {
            color: var(--rrep-primary) !important;
        }
        .bg-primary {
            background-color: var(--rrep-primary) !important;
        }
    </style>

    @if(!empty($branding?->custom_css))
        <!-- Custom CSS Configured in System Admin Panel -->
        <style id="rrep-auth-custom-css">
            {!! $branding->custom_css !!}
        </style>
    @endif
</head>
<body>
    <div class="container p-3">
        @yield('content')
    </div>
</body>
</html>
