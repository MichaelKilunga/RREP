@extends('layouts.auth')

@section('title', __('app.upload_too_large_title'))

@section('content')
@php $postLimitMb = round(min(ini_size_kb('post_max_size'), 1048576) / 1024, 1); @endphp
<div class="card mx-auto my-4 my-md-5 max-w-xl">
    <div class="empty-state">
        <div class="empty-icon bg-warning-subtle text-warning-emphasis"><i class="bi bi-file-earmark-arrow-up"></i></div>
        <h4 class="brand-font">{{ __('app.upload_too_large_title') }}</h4>
        <p class="mb-1">{{ __('app.upload_too_large_text', ['max' => $postLimitMb]) }}</p>
        <p class="small mb-4">{{ __('app.upload_too_large_hint') }}</p>
        <a href="{{ url()->previous() !== url()->current() ? url()->previous() : url('/') }}" class="btn btn-primary"><i class="bi bi-arrow-left me-1"></i> {{ __('pages.back') }}</a>
    </div>
</div>
@endsection
