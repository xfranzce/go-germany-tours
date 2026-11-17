@props([
    'name', 'label', 'formId', 'type' => 'text', 'required' => false, 'options' => [], 'placeholder' => '',
    'autocomplete' => null, 'min' => null, 'hint' => null, 'rows' => 3, 'value' => null, 'bag' => null, 'xValue' => null,
])
@php
    $id = "{$formId}-{$name}";
    $serverError = $bag?->first($name);
    $current = old('form_id') === $formId ? old($name, $value) : $value;
@endphp
<div {{ $attributes->only('class') }}>
    <label for="{{ $id }}" class="field-label">{{ $label }}@if($required) <span class="text-brand-red" aria-hidden="true">*</span><span class="sr-only">(required)</span>@endif</label>

    @if($type === 'textarea')
        <textarea id="{{ $id }}" name="{{ $name }}" rows="{{ $rows }}" class="field-input" placeholder="{{ $placeholder }}"
                  @required($required) maxlength="2000" aria-describedby="{{ $id }}-err"
                  :aria-invalid="errors.{{ $name }} ? 'true' : 'false'"
                  @if($xValue) x-model="{{ $xValue }}" @endif>{{ $current }}</textarea>
    @elseif($type === 'select')
        <select id="{{ $id }}" name="{{ $name }}" class="field-input" @required($required) aria-describedby="{{ $id }}-err"
                :aria-invalid="errors.{{ $name }} ? 'true' : 'false'">
            @if(!$required || $placeholder)<option value="">{{ $placeholder ?: 'Select...' }}</option>@endif
            @foreach($options as $val => $text)
                <option value="{{ is_int($val) ? $text : $val }}" @selected((string) $current === (string) (is_int($val) ? $text : $val))>{{ $text }}</option>
            @endforeach
        </select>
    @else
        <input id="{{ $id }}" type="{{ $type }}" name="{{ $name }}" value="{{ $current }}" class="field-input"
               placeholder="{{ $placeholder }}" @required($required) @if($autocomplete) autocomplete="{{ $autocomplete }}" @endif @if($min) min="{{ $min }}" @endif
               maxlength="150" aria-describedby="{{ $id }}-err" :aria-invalid="errors.{{ $name }} ? 'true' : 'false'">
    @endif

    @if($hint)<p class="text-xs text-ink-soft mt-1">{{ $hint }}</p>@endif
    <div id="{{ $id }}-err" aria-live="polite">
        @if($serverError)<p class="field-error">{{ $serverError }}</p>@endif
        <template x-if="errors.{{ $name }}"><p class="field-error" x-text="errors.{{ $name }}[0]"></p></template>
    </div>
</div>
