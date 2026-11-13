@props(['name', 'label', 'type' => 'text', 'value' => null, 'options' => [], 'required' => false, 'help' => null, 'rows' => 4, 'step' => null, 'placeholder' => '', 'maxlength' => null])
@php
    $id = 'f-'.str_replace(['.', '[', ']'], '-', $name);
    $current = old(str_replace(['[', ']'], ['.', ''], $name), $value);
@endphp
<div {{ $attributes->only('class') }}>
    @if($type === 'checkbox')
        <label class="inline-flex items-center gap-2 font-bold">
            <input type="hidden" name="{{ $name }}" value="0">
            <input type="checkbox" name="{{ $name }}" value="1" class="h-5 w-5 accent-brand-red" @checked($current)> {{ $label }}
        </label>
    @else
        <label for="{{ $id }}" class="field-label">{{ $label }}@if($required) <span class="text-brand-red">*</span>@endif</label>
        @if($type === 'textarea')
            <textarea id="{{ $id }}" name="{{ $name }}" rows="{{ $rows }}" class="field-input" placeholder="{{ $placeholder }}" @required($required) @if($maxlength) maxlength="{{ $maxlength }}" @endif>{{ $current }}</textarea>
        @elseif($type === 'select')
            <select id="{{ $id }}" name="{{ $name }}" class="field-input" @required($required)>
                @foreach($options as $k => $text)<option value="{{ $k }}" @selected((string) $current === (string) $k)>{{ $text }}</option>@endforeach
            </select>
        @else
            <input id="{{ $id }}" type="{{ $type }}" name="{{ $name }}" value="{{ $type === 'password' ? '' : $current }}" class="field-input"
                   placeholder="{{ $placeholder }}" @required($required) @if($step) step="{{ $step }}" @endif @if($maxlength) maxlength="{{ $maxlength }}" @endif autocomplete="{{ $type === 'password' ? 'new-password' : 'off' }}">
        @endif
    @endif
    @if($help)<p class="text-xs text-ink-soft mt-1">{{ $help }}</p>@endif
    @error(str_replace(['[', ']'], ['.', ''], $name))<p class="field-error">{{ $message }}</p>@enderror
</div>
