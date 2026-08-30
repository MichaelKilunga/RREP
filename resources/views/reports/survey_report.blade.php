@extends('layouts.app')

@section('title', 'Survey Projects Report')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 d-print-none">
    <div>
        <h3 class="brand-font mb-1">Land Survey & Cadastral Audit Report</h3>
        <p class="text-muted small mb-0">Total Projects: {{ $surveys->count() }} &bull; Boundary Beacons & Milestones</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('reports.index') }}" class="btn btn-light border btn-sm"><i class="bi bi-arrow-left me-1"></i> Reports Center</a>
        <button onclick="window.print()" class="btn btn-dark btn-sm"><i class="bi bi-printer me-1"></i> Print</button>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
            <thead class="table-light">
                <tr>
                    <th>Project Code</th>
                    <th>Project Name</th>
                    <th>Location</th>
                    <th>Acreage</th>
                    <th>Lead Surveyor</th>
                    <th>Beacons</th>
                    <th>Milestones Progress</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($surveys as $s)
                    <tr>
                        <td class="fw-bold text-primary">{{ $s->project_code }}</td>
                        <td class="fw-semibold">{{ $s->project_name }}</td>
                        <td>{{ $s->location_name }}</td>
                        <td>{{ $s->total_area }} {{ $s->area_unit }}</td>
                        <td>{{ $s->leadSurveyor?->name ?? 'Unassigned' }}</td>
                        <td><span class="badge bg-primary-subtle text-primary">{{ $s->beacons->count() }} Beacons</span></td>
                        <td>{{ $s->milestones->where('status', 'Completed')->count() }} / {{ $s->milestones->count() }} Done</td>
                        <td><span class="badge bg-warning-subtle text-warning-emphasis">{{ $s->status }}</span></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
