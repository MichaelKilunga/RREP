{{--
    Bilingual landing-CMS field: renders "<base>_sw" and "<base>_en" inputs.
    Variables: base, label, sw (default), en (default), type (text|textarea), rows, help, col, swFallback, enFallback
--}}
@php
    $type = $type ?? 'text';
    $rows = $rows ?? 2;
    $col = $col ?? 'col-12 col-md-6';
    $fieldId = Str::camel($base);
    $swValue = setting($base.'_sw', $swFallback ?? ($sw ?? ''));
    $enValue = setting($base.'_en', $enFallback ?? ($en ?? ''));
@endphp
<div class="{{ $col }}">
    <label class="form-label fw-semibold mb-1" for="{{ $fieldId }}Sw">{{ $label }}</label>
    <div class="input-group mb-2">
        <span class="input-group-text fw-bold small" title="Kiswahili">SW</span>
        @if($type === 'textarea')
            <textarea name="{{ $base }}_sw" class="form-control" id="{{ $fieldId }}Sw" rows="{{ $rows }}" aria-label="{{ $label }} (Kiswahili)">{{ $swValue }}</textarea>
        @else
            <input type="text" name="{{ $base }}_sw" class="form-control" id="{{ $fieldId }}Sw" value="{{ $swValue }}" aria-label="{{ $label }} (Kiswahili)">
        @endif
    </div>
    <div class="input-group">
        <span class="input-group-text fw-bold small" title="English">EN</span>
        @if($type === 'textarea')
            <textarea name="{{ $base }}_en" class="form-control" id="{{ $fieldId }}En" rows="{{ $rows }}" aria-label="{{ $label }} (English)">{{ $enValue }}</textarea>
        @else
            <input type="text" name="{{ $base }}_en" class="form-control" id="{{ $fieldId }}En" value="{{ $enValue }}" aria-label="{{ $label }} (English)">
        @endif
    </div>
    @if(!empty($help))
        <div class="form-text">{{ $help }}</div>
    @endif
</div>
