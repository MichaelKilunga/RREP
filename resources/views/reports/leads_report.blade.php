@extends('layouts.app')

@section('title', 'Leads & CRM Report')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 d-print-none">
    <div>
        <h3 class="brand-font mb-1">Lead Conversion & Pipeline Analytics</h3>
        <p class="text-muted small mb-0">Total Inquiries: {{ $leads->count() }} &bull; Source Attribution & Pipeline Distribution</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('reports.index') }}" class="btn btn-light border btn-sm"><i class="bi bi-arrow-left me-1"></i> Reports Center</a>
        <button onclick="window.print()" class="btn btn-dark btn-sm"><i class="bi bi-printer me-1"></i> Print</button>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card p-3 h-100">
            <h6 class="brand-font mb-3">Lead Sources Attribution</h6>
            <ul class="list-group list-group-flush">
                @foreach($sources as $src)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>{{ $src->source }}</span>
                        <span class="badge bg-primary rounded-pill">{{ $src->count }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card p-3 h-100">
            <h6 class="brand-font mb-3">Pipeline Stage Velocity</h6>
            <ul class="list-group list-group-flush">
                @foreach($stages as $stg)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>{{ $stg->stage }}</span>
                        <span class="badge bg-secondary rounded-pill">{{ $stg->count }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
@endsection
