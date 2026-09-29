@extends('layouts.app')

@section('title', 'Manage Services - Admin Panel')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">Company Services Management</h3>
            <p class="text-muted small mb-0">Control and manage all public services displayed on the website.</p>
        </div>
        <a href="{{ route('admin.services.create') }}" class="btn btn-primary fw-bold rounded-pill">
            <i class="bi bi-plus-circle me-1"></i> Add New Service
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Order</th>
                            <th>Icon</th>
                            <th>Title & Category</th>
                            <th>Short Description</th>
                            <th>Sub-Services</th>
                            <th>Status</th>
                            <th class="pe-4 text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($services as $srv)
                            <tr>
                                <td class="ps-4 fw-bold text-muted">#{{ $srv->display_order }}</td>
                                <td>
                                    <div class="rounded-3 bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; font-size: 1.25rem;">
                                        <i class="bi {{ $srv->icon }}"></i>
                                    </div>
                                </td>
                                <td>
                                    <strong class="text-dark d-block">{{ $srv->title }}</strong>
                                    <span class="badge bg-light text-dark border">{{ $srv->category }}</span>
                                </td>
                                <td><small class="text-muted line-clamp-2" style="max-width: 280px;">{{ $srv->short_description }}</small></td>
                                <td>
                                    @if(!empty($srv->sub_services))
                                        <div class="d-flex flex-wrap gap-1" style="max-width: 220px;">
                                            @foreach($srv->sub_services as $sub)
                                                <span class="badge bg-light text-primary border" style="font-size: 0.65rem;">{{ $sub }}</span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $srv->is_active ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $srv->is_active ? 'Active' : 'Disabled' }}
                                    </span>
                                </td>
                                <td class="pe-4 text-end">
                                    <a href="{{ route('admin.services.edit', $srv->id) }}" class="btn btn-sm btn-outline-primary rounded-circle me-1" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('admin.services.destroy', $srv->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this service?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">No services created yet. Click "Add New Service" to create one.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
