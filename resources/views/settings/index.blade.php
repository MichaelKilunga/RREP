@extends('layouts.app')

@section('title', __('app.settings'))

@php
    $isProduction = is_production_mode();
    $tabs = [
        'environment' => ['icon' => 'bi-hdd-network-fill', 'label' => __('settings.tab_environment')],
        'pushsms' => ['icon' => 'bi-chat-dots-fill', 'label' => __('settings.tab_pushsms')],
        'toggles' => ['icon' => 'bi-toggles', 'label' => __('settings.tab_toggles')],
        'social' => ['icon' => 'bi-share', 'label' => __('settings.tab_social')],
        'branding' => ['icon' => 'bi-palette-fill', 'label' => __('settings.tab_branding')],
        'landing-cms' => ['icon' => 'bi-globe2', 'label' => __('settings.tab_landing')],
        'taxonomies' => ['icon' => 'bi-tags-fill', 'label' => __('settings.tab_taxonomies')],
        'branches' => ['icon' => 'bi-building', 'label' => __('settings.tab_branches')],
    ];
@endphp

@section('content')
<div class="page-header">
    <div class="min-w-0">
        <span class="section-tag"><i class="bi bi-gear-wide-connected me-1"></i>{{ __('app.system_administration_and_configuration') }}</span>
        <h3 class="brand-font mb-1 text-dark">{{ __('app.settings') }}</h3>
        <p class="text-muted small mb-0">{{ __('settings.page_intro') }}</p>
    </div>
    <div class="page-actions">
        <span class="badge {{ $isProduction ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-warning-subtle text-warning-emphasis border border-warning-subtle' }} px-3 py-2 rounded-pill fw-semibold">
            <i class="bi {{ $isProduction ? 'bi-shield-check-fill' : 'bi-laptop' }} me-1"></i>
            {{ $isProduction ? __('settings.mode_production_live') : __('settings.mode_local_development') }}
        </span>
    </div>
</div>

{{-- Environment status summary --}}
<div class="card border-0 shadow-xs mb-4 rounded-4 overflow-hidden border-start border-4 {{ $isProduction ? 'bg-success-subtle border-success' : 'bg-warning-subtle border-warning' }}">
    <div class="card-body p-3 p-md-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center gap-3 min-w-0">
                <div class="btn-icon {{ $isProduction ? 'bg-success text-white' : 'bg-warning text-dark' }} shadow-xs">
                    <i class="bi {{ $isProduction ? 'bi-shield-check-fill' : 'bi-laptop' }} fs-5"></i>
                </div>
                <div class="min-w-0">
                    <h6 class="mb-1 fw-bold">
                        {{ __('settings.system_status') }}:
                        <span class="{{ $isProduction ? 'text-success' : 'text-dark' }}">{{ $isProduction ? __('settings.mode_production_live') : __('settings.mode_local_development') }}</span>
                    </h6>
                    <p class="text-secondary small mb-0">
                        {{ $isProduction ? __('settings.production_status_help') : __('settings.local_status_help') }}
                    </p>
                </div>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <span class="badge bg-white text-dark border shadow-xs px-3 py-2 rounded-pill"><i class="bi bi-bug me-1 text-muted"></i>{{ __('settings.debug') }}: {{ $isDebug ? __('settings.enabled') : __('settings.disabled') }}</span>
                <span class="badge bg-white text-dark border shadow-xs px-3 py-2 rounded-pill"><i class="bi bi-box-arrow-in-right me-1 text-muted"></i>{{ __('settings.quick_login') }}: {{ $isProduction ? __('settings.off') : __('settings.on') }}</span>
                <span class="badge bg-white text-dark border shadow-xs px-3 py-2 rounded-pill"><i class="bi bi-database me-1 text-muted"></i>{{ __('settings.demo_records') }}: {{ $demoStats['total'] }}</span>
            </div>
        </div>
    </div>
</div>

{{-- Settings tabs: horizontally scrollable on phones, restored from the URL hash --}}
<div class="bg-white border rounded-4 shadow-xs p-1.5 mb-4">
    <ul class="nav nav-pills flex-nowrap overflow-auto scroll-x gap-2 m-0 p-0" id="settingsTabs" role="tablist" aria-label="{{ __('app.settings') }}">
        @foreach($tabs as $paneId => $tab)
            <li class="nav-item flex-shrink-0" role="presentation">
                <button class="nav-link fw-semibold rounded-pill px-3 py-2 text-nowrap {{ $loop->first ? 'active' : '' }}" id="{{ $paneId }}-tab" data-bs-toggle="pill" data-bs-target="#{{ $paneId }}" type="button" role="tab" aria-controls="{{ $paneId }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                    <i class="bi {{ $tab['icon'] }} me-1.5"></i>{{ $tab['label'] }}
                    @if($paneId === 'environment')
                        <span class="badge {{ $isProduction ? 'bg-success' : 'bg-warning text-dark' }} ms-1">{{ $isProduction ? __('settings.live') : __('settings.local') }}</span>
                    @elseif($paneId === 'branches')
                        <span class="badge bg-secondary-subtle text-secondary ms-1">{{ $branches->count() }}</span>
                    @endif
                </button>
            </li>
        @endforeach
    </ul>
</div>

<div class="tab-content" id="settingsTabContent">
    <div class="tab-pane fade show active" id="environment" role="tabpanel" aria-labelledby="environment-tab" tabindex="0">
        @include('settings.partials.environment')
    </div>

    <div class="tab-pane fade" id="pushsms" role="tabpanel" aria-labelledby="pushsms-tab" tabindex="0">
        @include('settings.partials.pushsms')
    </div>

    <div class="tab-pane fade" id="toggles" role="tabpanel" aria-labelledby="toggles-tab" tabindex="0">
        @include('settings.partials.toggles')
    </div>

    <div class="tab-pane fade" id="social" role="tabpanel" aria-labelledby="social-tab" tabindex="0">
        @include('settings.partials.social')
    </div>

    <div class="tab-pane fade" id="branding" role="tabpanel" aria-labelledby="branding-tab" tabindex="0">
        @include('settings.partials.branding')
    </div>

    <div class="tab-pane fade" id="landing-cms" role="tabpanel" aria-labelledby="landing-cms-tab" tabindex="0">
        @include('settings.partials.landing-cms')
    </div>

    <div class="tab-pane fade" id="taxonomies" role="tabpanel" aria-labelledby="taxonomies-tab" tabindex="0">
        @include('settings.partials.taxonomies')
    </div>

    <div class="tab-pane fade" id="branches" role="tabpanel" aria-labelledby="branches-tab" tabindex="0">
        @include('settings.partials.branches')
    </div>
</div>

@include('settings.partials.branch-modals')
@endsection

@section('scripts')
<script>
/**
 * Refresh the live PushSMS balance (called by the button in the PushSMS tab).
 */
function refreshSmsBalance() {
    const el = document.getElementById('liveBalanceText');
    const unitsLabel = @json(__('settings.sms_units'));
    el.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> ' + @json(__('settings.checking'));
    fetch("{{ route('settings.sms_balance') }}", { headers: { 'Accept': 'application/json' } })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success' && !isNaN(data.balance)) {
                el.innerHTML = Number(data.balance).toLocaleString() + ' <span class="fs-6 fw-normal">' + unitsLabel + '</span>';
            } else {
                el.innerText = data.balance || data.message || @json(__('settings.unavailable'));
            }
        })
        .catch(() => {
            el.innerText = @json(__('settings.balance_error'));
        });
}

document.addEventListener('DOMContentLoaded', function () {
    // 1. Restore the active tab from the URL hash (#branches etc.) and keep the hash in sync.
    const showTabFromHash = (hash) => {
        if (!hash) { return; }
        const triggerBtn = document.querySelector('#settingsTabs button[data-bs-target="' + hash + '"]');
        if (triggerBtn && window.bootstrap) {
            bootstrap.Tab.getOrCreateInstance(triggerBtn).show();
            triggerBtn.scrollIntoView({ block: 'nearest', inline: 'center' });
        }
    };
    showTabFromHash(window.location.hash);
    window.addEventListener('hashchange', () => showTabFromHash(window.location.hash));
    document.querySelectorAll('#settingsTabs button[data-bs-toggle="pill"]').forEach((btn) => {
        btn.addEventListener('shown.bs.tab', (event) => {
            const target = event.target.getAttribute('data-bs-target');
            if (target && window.history && window.history.replaceState) {
                window.history.replaceState(null, '', target);
            }
        });
    });

    // 2. Destructive / important actions: confirm before submitting (SweetAlert2 when available).
    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (form.dataset.confirmed === '1') { return; }
            event.preventDefault();
            const message = form.dataset.confirm;
            const proceed = () => { form.dataset.confirmed = '1'; form.requestSubmit ? form.requestSubmit() : form.submit(); };
            if (window.Swal) {
                Swal.fire({
                    title: form.dataset.confirmTitle || @json(__('settings.are_you_sure')),
                    text: message,
                    icon: form.dataset.confirmIcon || 'warning',
                    showCancelButton: true,
                    confirmButtonColor: form.dataset.confirmColor || '#dc2626',
                    confirmButtonText: form.dataset.confirmButton || @json(__('settings.yes_continue')),
                    cancelButtonText: @json(__('app.cancel')),
                }).then((res) => { if (res.isConfirmed) { proceed(); } });
            } else if (window.confirm(message)) {
                proceed();
            }
        });
    });

    // 3. Image previews for file inputs (data-preview-target="#img-id").
    document.querySelectorAll('input[type="file"][data-preview-target]').forEach((input) => {
        input.addEventListener('change', () => {
            const target = document.querySelector(input.dataset.previewTarget);
            const file = input.files && input.files[0];
            if (!target || !file || !file.type.startsWith('image/')) { return; }
            const reader = new FileReader();
            reader.onload = (e) => {
                if (target.tagName === 'IMG') {
                    target.src = e.target.result;
                } else {
                    target.innerHTML = '<img src="' + e.target.result + '" alt="" class="img-fluid object-fit-contain" style="max-height:48px">';
                }
                target.classList.remove('d-none');
            };
            reader.readAsDataURL(file);
        });
    });

    // 4. Colour pickers: keep the hex text box in sync and update the brand preview card.
    const preview = document.getElementById('brandPreviewCard');
    const applyPreview = () => {
        if (!preview) { return; }
        document.querySelectorAll('input[type="color"][data-preview-var]').forEach((picker) => {
            preview.style.setProperty(picker.dataset.previewVar, picker.value);
        });
    };
    document.querySelectorAll('input[type="color"][data-hex-target]').forEach((picker) => {
        const hex = document.querySelector(picker.dataset.hexTarget);
        if (!hex) { return; }
        picker.addEventListener('input', () => { hex.value = picker.value; applyPreview(); });
        hex.addEventListener('input', () => {
            const value = hex.value.trim();
            if (/^#[0-9a-fA-F]{6}$/.test(value)) { picker.value = value; applyPreview(); }
        });
    });
    const monogram = document.getElementById('brandMonogram');
    const companyName = document.getElementById('companyName');
    const previewMonogram = document.getElementById('brandPreviewMonogram');
    const previewName = document.getElementById('brandPreviewName');
    if (monogram && previewMonogram) { monogram.addEventListener('input', () => { previewMonogram.textContent = monogram.value || 'R'; }); }
    if (companyName && previewName) { companyName.addEventListener('input', () => { previewName.textContent = companyName.value; }); }
    applyPreview();

    // 5. SMS character counters: GSM-7 (160 per segment) vs Unicode (70 per segment).
    const gsmPattern = /^[A-Za-z0-9@£$¥èéùìòÇØøÅåΔ_ΦΓΛΩΠΨΣΘΞÆæßÉ !"#¤%&'()*+,\-.\/:;<=>?¡ÄÖÑÜ§¿äöñüà^{}\\\[~\]|€\r\n]*$/;
    document.querySelectorAll('textarea[data-sms-counter]').forEach((area) => {
        const out = document.querySelector(area.dataset.smsCounter);
        if (!out) { return; }
        const update = () => {
            const text = area.value || '';
            const isGsm = gsmPattern.test(text);
            const perSegment = isGsm ? 160 : 70;
            const segments = text.length === 0 ? 0 : Math.ceil(text.length / perSegment);
            out.textContent = text.length + ' ' + @json(__('settings.chars')) + ' · ' + segments + ' SMS (' + (isGsm ? 'GSM 160' : 'Unicode 70') + ')';
            out.classList.toggle('text-danger', segments > 1);
        };
        area.addEventListener('input', update);
        update();
    });

    // 6. Auto-uppercase the SMS sender ID and branch codes.
    document.querySelectorAll('input[data-uppercase]').forEach((input) => {
        input.addEventListener('input', () => { input.value = input.value.toUpperCase(); });
    });
});

$(document).ready(function () {
    const csrfToken = $('meta[name="csrf-token"]').attr('content');

    function updateBulkBranchesBar() {
        const count = $('.branch-checkbox:checked').length;
        $('#bulkBranchesCount').text(count);
        $('#bulkBranchesBar').toggleClass('d-none', count === 0);
        if (count === 0) {
            $('#selectAllBranchCheckboxes').prop('checked', false);
        }
    }

    $('#selectAllBranchCheckboxes').on('change', function () {
        $('.branch-checkbox').prop('checked', $(this).is(':checked'));
        updateBulkBranchesBar();
    });

    $(document).on('change', '.branch-checkbox', updateBulkBranchesBar);

    $('#bulkCancelBranchesBtn').on('click', function () {
        $('.branch-checkbox, #selectAllBranchCheckboxes').prop('checked', false);
        updateBulkBranchesBar();
    });

    function showToast(message, icon = 'success') {
        if (window.RREP && window.RREP.toast) {
            window.RREP.toast(message, icon);
        } else if (typeof Swal !== 'undefined') {
            Swal.fire({ toast: true, position: 'top-end', icon: icon, title: message, showConfirmButton: false, timer: 3500, timerProgressBar: true });
        }
    }

    const deleteLabel = '<i class="bi bi-trash me-1"></i> ' + @json(__('settings.delete_selected'));

    $('#bulkDeleteBranchesBtn').on('click', function () {
        const selectedIds = $('.branch-checkbox:checked').map(function () { return $(this).val(); }).get();
        if (selectedIds.length === 0) return;

        Swal.fire({
            title: @json(__('settings.bulk_delete_title')),
            text: @json(__('settings.bulk_delete_text')).replace(':count', selectedIds.length),
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            confirmButtonText: @json(__('settings.yes_delete_selected')),
            cancelButtonText: @json(__('app.cancel'))
        }).then((res) => {
            if (!res.isConfirmed) return;
            const btn = $('#bulkDeleteBranchesBtn');
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> ' + @json(__('settings.deleting')));

            $.ajax({
                url: "{{ route('settings.branches.bulk_delete') }}",
                type: 'POST',
                data: { _token: csrfToken, ids: selectedIds },
                success: function (r) {
                    btn.prop('disabled', false).html(deleteLabel);
                    if (r.success) {
                        selectedIds.forEach(id => {
                            const row = $('#branch-row-' + id);
                            if (row.length) {
                                row.fadeOut(300, function () { $(this).remove(); });
                            }
                        });
                        $('.branch-checkbox, #selectAllBranchCheckboxes').prop('checked', false);
                        updateBulkBranchesBar();
                        showToast(r.message);
                    }
                },
                error: function () {
                    btn.prop('disabled', false).html(deleteLabel);
                    showToast(@json(__('settings.bulk_delete_failed')), 'error');
                }
            });
        });
    });
});
</script>
@endsection
