@extends('layouts.app')

@section('title', 'Edit ' . $property->title)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="brand-font mb-1">Edit Property</h3>
        <p class="text-muted small mb-0">{{ $property->title }} ({{ $property->property_code }})</p>
    </div>
    <a href="{{ route('properties.show', $property) }}" class="btn btn-light border btn-sm"><i class="bi bi-arrow-left me-1"></i> Back to Property</a>
</div>

<form action="{{ route('properties.update', $property) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header brand-font">General Details</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Property Title *</label>
                        <input type="text" name="title" class="form-control" value="{{ $property->title }}" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Property Type *</label>
                            <select name="property_type_id" class="form-select" required>
                                @foreach($types as $t)
                                    <option value="{{ $t->id }}" {{ $property->property_type_id == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Listing Type *</label>
                            <select name="listing_type" class="form-select" required>
                                <option value="Sale" {{ $property->listing_type == 'Sale' ? 'selected' : '' }}>Sale</option>
                                <option value="Rent" {{ $property->listing_type == 'Rent' ? 'selected' : '' }}>Rent</option>
                                <option value="Lease" {{ $property->listing_type == 'Lease' ? 'selected' : '' }}>Lease</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Description</label>
                        <textarea name="description" rows="5" class="form-control">{{ $property->description }}</textarea>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header brand-font">Location & Coordinates</div>
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">City *</label>
                            <input type="text" name="city" class="form-control" value="{{ $property->city }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">State/District</label>
                            <input type="text" name="state" class="form-control" value="{{ $property->state }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Street Address *</label>
                            <input type="text" name="address" class="form-control" value="{{ $property->address }}" required>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header brand-font">Pricing & Status</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Price ({{ current_currency() }})</label>
                        <input type="number" step="0.01" name="price" class="form-control fw-bold" value="{{ $property->price }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Rent Price</label>
                        <input type="number" step="0.01" name="rent_price" class="form-control" value="{{ $property->rent_price }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Status *</label>
                        <select name="status" class="form-select" required>
                            <option value="Available" {{ $property->status == 'Available' ? 'selected' : '' }}>Available</option>
                            <option value="Reserved" {{ $property->status == 'Reserved' ? 'selected' : '' }}>Reserved</option>
                            <option value="Under Contract" {{ $property->status == 'Under Contract' ? 'selected' : '' }}>Under Contract</option>
                            <option value="Sold" {{ $property->status == 'Sold' ? 'selected' : '' }}>Sold</option>
                            <option value="Leased" {{ $property->status == 'Leased' ? 'selected' : '' }}>Leased</option>
                        </select>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold shadow-sm">Save Changes</button>
        </div>
    </div>
</form>
@endsection
