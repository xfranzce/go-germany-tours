@props(['name' => 'image', 'label' => 'Image', 'current' => null, 'altRequired' => true, 'help' => 'JPG, PNG or WebP, max 8 MB. It is resized and converted to WebP automatically.'])
@php $media = \App\Models\Media::forPath($current); @endphp
<fieldset {{ $attributes->only('class') }} class="border border-gray-300 rounded-lg p-3 bg-white">
    <legend class="field-label px-1">{{ $label }}</legend>
    @if($media)
        <div class="flex items-center gap-3 mb-3">
            <img src="{{ $media->url(min(480, max($media->widths))) }}" alt="{{ $media->alt }}" width="96" height="{{ max(1, (int) round(96 * $media->height / $media->width)) }}" class="w-24 rounded">
            <label class="inline-flex items-center gap-2 text-sm font-bold"><input type="checkbox" name="{{ $name }}_remove" value="1" class="h-4 w-4 accent-brand-red"> Remove this image</label>
        </div>
    @endif
    <input type="file" name="{{ $name }}_file" accept="image/jpeg,image/png,image/webp" class="block text-sm">
    <label class="field-label mt-3" for="{{ $name }}_alt">Alt text (describe the image) @if($altRequired)<span class="text-brand-red">*</span>@endif</label>
    <input id="{{ $name }}_alt" type="text" name="{{ $name }}_alt" value="{{ old($name.'_alt', $media?->alt) }}" class="field-input" maxlength="200">
    <p class="text-xs text-ink-soft mt-1">{{ $help }}</p>
    @error($name.'_file')<p class="field-error">{{ $message }}</p>@enderror
    @error($name.'_alt')<p class="field-error">{{ $message }}</p>@enderror
</fieldset>
