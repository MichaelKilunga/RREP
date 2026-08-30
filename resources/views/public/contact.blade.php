@extends('layouts.public')

@section('title', 'Contact Us & Office Locations')

@section('content')
<div class="bg-dark text-white py-5 text-center" style="background: linear-gradient(135deg, #091224 0%, #1e3a8a 100%);">
    <div class="container py-3">
        <span class="badge bg-primary px-3 py-2 rounded-pill mb-2">Get in Touch</span>
        <h1 class="brand-font display-5 fw-bold text-white mb-2">Contact REMS Platform & Offices</h1>
        <p class="lead text-white-50 mx-auto mb-0" style="max-width: 650px;">
            Reach out to our customer support, listing desks, or geomatics land survey coordinators across Tanzania.
        </p>
    </div>
</div>

<div class="container py-5">
    <div class="row g-5">
        <!-- Contact Form -->
        <div class="col-lg-7">
            <div class="card p-4 p-md-5 border rounded-4 bg-white shadow-sm">
                <h3 class="brand-font mb-2">Send Us a Direct Message</h3>
                <p class="text-muted small mb-4">Have an inquiry about a property, land parcel, or survey service? Fill out the form and our team will get back to you.</p>

                <form action="{{ route('public.contact.submit') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Your Full Name *</label>
                            <input type="text" name="name" class="form-control" placeholder="John Doe" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Phone Number (WhatsApp) *</label>
                            <input type="text" name="phone" class="form-control" placeholder="+255 7..." required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Email Address *</label>
                            <input type="email" name="email" class="form-control" placeholder="john@example.com" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Subject / Department *</label>
                            <select name="subject" class="form-select" required>
                                <option value="General Inquiry">General Marketplace Inquiry</option>
                                <option value="Land Survey Support">Land Survey & GIS Department</option>
                                <option value="Property Listing Help">Property Listing & Verification</option>
                                <option value="Commercial Investment">Commercial Investment & Projects</option>
                                <option value="Technical Support">Platform Technical Support</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted">Message Details *</label>
                            <textarea name="message" rows="4" class="form-control" placeholder="How can we assist you today?" required></textarea>
                        </div>
                        <div class="col-12 mt-4">
                            <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5 fw-bold shadow">
                                <i class="bi bi-send-fill me-1"></i> Send Message
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Office Details & Regional Branches -->
        <div class="col-lg-5">
            <div class="card p-4 border rounded-4 bg-white shadow-sm mb-4">
                <h5 class="brand-font mb-3">Headquarters Contact</h5>
                <div class="d-flex flex-column gap-3 text-secondary small">
                    <div class="d-flex align-items-start gap-3">
                        <i class="bi bi-geo-alt-fill text-danger fs-5"></i>
                        <div>
                            <strong>Physical Address:</strong><br>
                            {{ setting('contact_address', 'Plot 42, Victoria Business Tower, New Bagamoyo Road, Dar es Salaam, Tanzania') }}
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <i class="bi bi-telephone-fill text-success fs-5"></i>
                        <div>
                            <strong>Phone / Help Desk:</strong><br>
                            {{ setting('contact_phone', '+255 784 100 200') }}
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <i class="bi bi-whatsapp text-success fs-5"></i>
                        <div>
                            <strong>WhatsApp Hotline:</strong><br>
                            +{{ setting('contact_whatsapp', '255784100200') }}
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <i class="bi bi-envelope-fill text-primary fs-5"></i>
                        <div>
                            <strong>Email:</strong><br>
                            {{ setting('contact_email', 'info@rehospace.co.tz') }}
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <i class="bi bi-clock-fill text-muted fs-5"></i>
                        <div>
                            <strong>Business Hours:</strong><br>
                            Monday – Friday: 8:00 AM – 5:30 PM<br>Saturday: 9:00 AM – 2:00 PM
                        </div>
                    </div>
                </div>
            </div>

            <!-- Regional Branches List -->
            @if(isset($branches) && $branches->count())
                <div class="card p-4 border rounded-4 bg-white shadow-sm">
                    <h5 class="brand-font mb-3">Regional Hubs</h5>
                    <div class="d-flex flex-column gap-2">
                        @foreach($branches as $b)
                            <div class="p-2 rounded-3 bg-light border small">
                                <strong>{{ $b->name }}</strong> ({{ $b->city }})<br>
                                <span class="text-muted"><i class="bi bi-geo-alt me-1"></i>{{ $b->address }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
