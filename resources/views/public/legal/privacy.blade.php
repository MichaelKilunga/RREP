@extends('layouts.public')

@section('title', 'Privacy Policy - REMS Real Estate Platform')

@section('content')
<div class="bg-dark text-white py-4 mb-4">
    <div class="container">
        <h1 class="brand-font text-white display-6 fw-bold mb-1">Privacy Policy</h1>
        <p class="text-white-50 small mb-0">How REMS protects and manages customer, property owner, and survey data</p>
    </div>
</div>

<div class="container pb-5">
    <div class="card p-4 p-md-5 border rounded-4 bg-white shadow-sm max-w-850 mx-auto text-secondary" style="line-height: 1.8;">
        <h4 class="brand-font text-dark mb-3">1. Information We Collect</h4>
        <p>REMS collects personal information when you register an account, submit property inquiries, schedule site viewings, or request cadastral land survey services. This includes your name, phone number, email address, property preferences, and survey coordinates.</p>

        <h4 class="brand-font text-dark mt-4 mb-3">2. Property Owner Privacy Protection</h4>
        <p>In accordance with platform security rules, property owners' personal contact numbers and sensitive legal documents are shielded from public display unless explicitly authorized. All public communication is routed through approved platform listing desks and certified agents.</p>

        <h4 class="brand-font text-dark mt-4 mb-3">3. Cadastral Survey & GIS Data</h4>
        <p>Geospatial boundary coordinates, beacon numbers, and topographical drawings uploaded to the platform are used exclusively for land verification, due diligence, and approved transaction processing.</p>

        <h4 class="brand-font text-dark mt-4 mb-3">4. Contact & Compliance</h4>
        <p>If you have any questions regarding our privacy practices or wish to request deletion of your account data, please contact our data compliance officer at <a href="mailto:{{ setting('contact_email', 'privacy@rehospace.co.tz') }}">{{ setting('contact_email', 'privacy@rehospace.co.tz') }}</a>.</p>
    </div>
</div>
@endsection
