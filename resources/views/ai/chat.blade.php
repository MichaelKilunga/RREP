@extends('layouts.app')

@section('title', __('ops.ai_title'))

@section('styles')
<style>
    .ai-chat-card { height: min(650px, calc(100dvh - 12rem)); min-height: 420px; }
    @media (max-width: 767.98px) {
        .ai-chat-card { height: calc(100dvh - var(--rrep-topbar-height) - var(--rrep-bottom-nav-height) - var(--rrep-safe-bottom) - 7rem); min-height: 380px; }
    }
    .chat-bubble { max-width: 85%; font-size: 0.9rem; line-height: 1.6; overflow-wrap: anywhere; }
    @media (min-width: 768px) { .chat-bubble { max-width: 80%; } }
    .chat-avatar { width: 38px; height: 38px; flex-shrink: 0; }
</style>
@endsection

@section('content')
<div class="page-header">
    <div class="min-w-0">
        <h1 class="brand-font mb-1"><i class="bi bi-stars text-warning me-2"></i>{{ __('ops.ai_title') }}</h1>
        <p class="text-muted small mb-0">{{ __('ops.ai_subtitle') }}</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card d-flex flex-column ai-chat-card">
            <div class="card-header brand-font bg-white d-flex justify-content-between align-items-center gap-2">
                <span class="text-truncate"><i class="bi bi-chat-dots-fill text-primary me-2"></i>{{ __('ops.ai_workbench') }}</span>
                <span class="badge bg-primary-subtle text-primary text-nowrap">Gemini</span>
            </div>
            <div class="card-body flex-grow-1 overflow-auto p-3 p-md-4 bg-light min-w-0" id="studioChatBox" aria-live="polite">
                <div class="d-flex gap-2 gap-md-3 mb-4">
                    <div class="rounded-circle bg-dark text-warning d-flex align-items-center justify-content-center chat-avatar">
                        <i class="bi bi-stars"></i>
                    </div>
                    <div class="card p-3 shadow-xs border-0 bg-white chat-bubble">
                        {{ __('ops.ai_welcome') }}
                        <ul class="mb-0 mt-2 ps-3">
                            <li>{{ __('ops.ai_can_valuation') }}</li>
                            <li>{{ __('ops.ai_can_marketing') }}</li>
                            <li>{{ __('ops.ai_can_leads') }}</li>
                            <li>{{ __('ops.ai_can_contracts') }}</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="card-footer bg-white p-2 p-md-3 border-top">
                <form id="studioForm" class="input-group" data-no-loading>
                    <label for="studioPromptInput" class="visually-hidden">{{ __('ops.ai_prompt_label') }}</label>
                    <input type="text" id="studioPromptInput" class="form-control" placeholder="{{ __('ops.ai_prompt_placeholder') }}" autocomplete="off" enterkeyhint="send">
                    <button class="btn btn-primary px-3 px-md-4 fw-bold" id="studioSendBtn" type="submit"><i class="bi bi-send-fill"></i><span class="d-none d-sm-inline ms-1">{{ __('ops.ask_ai') }}</span></button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Valuation Quick Tool -->
        <div class="card mb-4">
            <div class="card-header brand-font"><i class="bi bi-calculator text-success me-2"></i>{{ __('ops.quick_valuation') }}</div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="evalPropSelect" class="form-label small fw-semibold">{{ __('ops.select_property_to_evaluate') }}</label>
                    <select id="evalPropSelect" class="form-select">
                        @foreach($properties as $pr)
                            <option value="{{ $pr->title }} in {{ $pr->city }} ({{ $pr->area_size }} {{ $pr->area_unit }})">{{ $pr->title }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="btn btn-outline-success w-100" id="btnQuickValuate" type="button">
                    <i class="bi bi-stars me-1"></i> {{ __('ops.generate_estimate') }}
                </button>
            </div>
        </div>

        <!-- Suggested prompts -->
        <div class="card">
            <div class="card-header brand-font">{{ __('ops.suggested_prompts') }}</div>
            <div class="card-body d-grid gap-2">
                <button class="btn btn-light border text-start prompt-suggestion" type="button"><i class="bi bi-graph-up me-2 text-primary"></i>{{ __('ops.prompt_trends') }}</button>
                <button class="btn btn-light border text-start prompt-suggestion" type="button"><i class="bi bi-file-text me-2 text-primary"></i>{{ __('ops.prompt_clause') }}</button>
                <button class="btn btn-light border text-start prompt-suggestion" type="button"><i class="bi bi-house-heart me-2 text-primary"></i>{{ __('ops.prompt_amenities') }}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function () {
    const generatingText = @json(__('ops.ai_generating'));
    const errorText = @json(__('ops.ai_error'));

    function escapeHtml(value) {
        return String(value).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function scrollChat() {
        const box = $('#studioChatBox')[0];
        if (box) { box.scrollTop = box.scrollHeight; }
    }

    function aiBubble(inner, extraClass) {
        return '<div class="d-flex gap-2 gap-md-3 mb-3 ' + (extraClass || '') + '">' +
            '<div class="rounded-circle bg-dark text-warning d-flex align-items-center justify-content-center chat-avatar"><i class="bi bi-stars"></i></div>' +
            '<div class="card p-3 shadow-xs border-0 bg-white chat-bubble">' + inner + '</div></div>';
    }

    function sendStudioMessage(prompt) {
        if (!prompt.trim()) return;

        $('#studioChatBox').append(
            '<div class="d-flex justify-content-end mb-3"><div class="card p-3 bg-primary text-white border-0 shadow-xs chat-bubble">' + escapeHtml(prompt) + '</div></div>'
        );
        $('#studioPromptInput').val('');
        scrollChat();

        $('#studioChatBox').append(aiBubble('<span class="spinner-border spinner-border-sm text-primary me-2"></span>' + escapeHtml(generatingText), 'studio-loading text-muted'));
        scrollChat();
        $('#studioSendBtn').prop('disabled', true);

        $.ajax({
            url: "{{ route('ai.ask') }}",
            method: "POST",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: { prompt: prompt },
            success: function (res) {
                $('.studio-loading').remove();
                $('#studioChatBox').append(aiBubble(escapeHtml(res.response || '').replace(/\n/g, '<br>')));
                scrollChat();
            },
            error: function () {
                $('.studio-loading').remove();
                $('#studioChatBox').append('<div class="alert alert-danger p-2 small">' + escapeHtml(errorText) + '</div>');
                scrollChat();
            },
            complete: function () {
                $('#studioSendBtn').prop('disabled', false);
            }
        });
    }

    $('#studioForm').on('submit', function (e) {
        e.preventDefault();
        sendStudioMessage($('#studioPromptInput').val());
    });

    $('.prompt-suggestion').on('click', function () {
        sendStudioMessage($(this).text().trim());
    });

    $('#btnQuickValuate').on('click', function () {
        const prop = $('#evalPropSelect').val();
        sendStudioMessage('Provide a detailed market valuation and price range in TZS for ' + prop);
    });
});
</script>
@endsection
