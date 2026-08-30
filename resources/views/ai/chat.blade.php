@extends('layouts.app')

@section('title', 'AI Smart Studio')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="brand-font mb-1"><i class="bi bi-stars text-warning me-2"></i>RehoSpace AI Smart Studio</h3>
        <p class="text-muted small mb-0">Powered by Google Gemini generative models for real estate intelligence</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card d-flex flex-column" style="height: 650px;">
            <div class="card-header brand-font bg-white d-flex justify-content-between align-items-center">
                <span><i class="bi bi-chat-dots-fill text-primary me-2"></i>Intelligence Chat Workbench</span>
                <span class="badge bg-primary-subtle text-primary">Gemini 1.5 Flash</span>
            </div>
            <div class="card-body flex-grow-1 overflow-auto p-4" id="studioChatBox" style="background: #f8fafc;">
                <div class="d-flex gap-3 mb-4">
                    <div class="rounded-circle bg-dark text-warning p-2 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                        <i class="bi bi-stars fs-5"></i>
                    </div>
                    <div class="card p-3 shadow-sm border-0 bg-white" style="max-width: 80%; font-size: 0.9rem; line-height: 1.6;">
                        Welcome to <strong>RehoSpace AI Studio</strong>. You can ask me to:
                        <ul class="mb-0 mt-2 ps-3">
                            <li>Estimate property valuations across Dar es Salaam, Arusha, and Dodoma</li>
                            <li>Draft real estate marketing descriptions and social copy</li>
                            <li>Analyze lead conversion probability</li>
                            <li>Summarize lease agreement clauses and tenant terms</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="card-footer bg-white p-3 border-top">
                <div class="input-group">
                    <input type="text" id="studioPromptInput" class="form-control form-control-lg" placeholder="Ask AI assistant anything about properties or contracts...">
                    <button class="btn btn-primary px-4 fw-bold" id="studioSendBtn"><i class="bi bi-send-fill me-1"></i> Ask AI</button>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Valuation Quick Tool -->
        <div class="card mb-4">
            <div class="card-header brand-font"><i class="bi bi-calculator text-success me-2"></i>Quick Valuation Estimator</div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Select Property to Evaluate</label>
                    <select id="evalPropSelect" class="form-select">
                        @foreach($properties as $pr)
                            <option value="{{ $pr->title }} in {{ $pr->city }} ({{ $pr->area_size }} {{ $pr->area_unit }})">{{ $pr->title }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="btn btn-outline-success w-100 btn-sm" id="btnQuickValuate">
                    <i class="bi bi-stars me-1"></i> Generate AI Price Estimate
                </button>
            </div>
        </div>

        <!-- Recent AI Prompts -->
        <div class="card">
            <div class="card-header brand-font">Suggested Prompts</div>
            <div class="card-body d-grid gap-2">
                <button class="btn btn-light border btn-sm text-start prompt-suggestion">📈 Analyze current prime residential price trends in Masaki, Dar es Salaam</button>
                <button class="btn btn-light border btn-sm text-start prompt-suggestion">📜 Draft standard tenant penalty clause for late rental payments</button>
                <button class="btn btn-light border btn-sm text-start prompt-suggestion">🏡 What are the top amenities high-net-worth buyers look for in East Africa?</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function () {
    function sendStudioMessage(prompt) {
        if (!prompt.trim()) return;

        $('#studioChatBox').append(`
            <div class="d-flex justify-content-end mb-3">
                <div class="card p-3 bg-primary text-white border-0 shadow-sm" style="max-width: 80%; font-size: 0.9rem;">
                    ${prompt}
                </div>
            </div>
        `);
        $('#studioPromptInput').val('');
        $('#studioChatBox').scrollTop($('#studioChatBox')[0].scrollHeight);

        $('#studioChatBox').append(`
            <div class="d-flex gap-3 mb-3 studio-loading">
                <div class="rounded-circle bg-dark text-warning p-2 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                    <i class="bi bi-stars fs-5"></i>
                </div>
                <div class="card p-3 shadow-sm border-0 bg-white text-muted" style="max-width: 80%;">
                    <span class="spinner-border spinner-border-sm text-primary me-2"></span> Generating intelligence...
                </div>
            </div>
        `);

        $.ajax({
            url: "{{ route('ai.ask') }}",
            method: "POST",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: { prompt: prompt },
            success: function (res) {
                $('.studio-loading').remove();
                $('#studioChatBox').append(`
                    <div class="d-flex gap-3 mb-3">
                        <div class="rounded-circle bg-dark text-warning p-2 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                            <i class="bi bi-stars fs-5"></i>
                        </div>
                        <div class="card p-3 shadow-sm border-0 bg-white" style="max-width: 80%; font-size: 0.9rem; line-height: 1.6;">
                            ${res.response.replace(/\\n/g, '<br>')}
                        </div>
                    </div>
                `);
                $('#studioChatBox').scrollTop($('#studioChatBox')[0].scrollHeight);
            },
            error: function () {
                $('.studio-loading').remove();
                $('#studioChatBox').append(`<div class="alert alert-danger p-2 small">Error contacting AI.</div>`);
            }
        });
    }

    $('#studioSendBtn').on('click', function () {
        sendStudioMessage($('#studioPromptInput').val());
    });

    $('#studioPromptInput').on('keypress', function (e) {
        if (e.which === 13) sendStudioMessage($(this).val());
    });

    $('.prompt-suggestion').on('click', function () {
        sendStudioMessage($(this).text().replace(/^[^\s]+\s/, ''));
    });

    $('#btnQuickValuate').on('click', function () {
        const prop = $('#evalPropSelect').val();
        sendStudioMessage('Provide a detailed market valuation and price range in TZS for ' + prop);
    });
});
</script>
@endsection
