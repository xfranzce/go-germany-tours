@props(['name', 'label', 'value' => null, 'help' => null])
@php $id = 'rich-'.$name; @endphp
<div {{ $attributes->only('class') }}>
    <label for="{{ $id }}-editor" class="field-label">{{ $label }}</label>
    <input id="{{ $id }}" type="hidden" name="{{ $name }}" value="{{ old($name, $value) }}">
    <trix-editor id="{{ $id }}-editor" input="{{ $id }}" class="trix-content bg-white border-2 border-gray-300 rounded-lg min-h-40 prose-ggt max-w-none"></trix-editor>
    @if($help)<p class="text-xs text-ink-soft mt-1">{{ $help }}</p>@endif
    @error($name)<p class="field-error">{{ $message }}</p>@enderror
</div>
