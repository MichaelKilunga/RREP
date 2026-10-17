@extends('layouts.app')

@section('title', __('admin.module_required_title'))

@section('content')
<div class="card mx-auto my-4 my-md-5 max-w-xl">
    <div class="empty-state">
        <div class="empty-icon bg-warning-subtle text-warning"><i class="bi bi-lock-fill"></i></div>
        <h4 class="brand-font">{{ __('admin.module_required_title') }}</h4>
        <p class="mb-1">{!! __('admin.module_required_text', ['module' => '<strong>'.e($module).'</strong>']) !!}</p>
        <p class="small mb-4">{{ __('admin.module_required_hint') }}</p>
        <div class="d-grid d-sm-block">
            <a href="{{ route('dashboard') }}" class="btn btn-primary"><i class="bi bi-arrow-left me-1"></i> {{ __('admin.return_dashboard') }}</a>
        </div>
    </div>
</div>
@endsection
