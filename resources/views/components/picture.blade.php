@props(['path' => null, 'alt' => null, 'sizes' => '100vw', 'priority' => false, 'class' => ''])
@php $m = \App\Models\Media::forPath($path); @endphp
@if($m)
    <img src="{{ $m->url(min(1280, max($m->widths))) }}" srcset="{{ $m->srcset() }}" sizes="{{ $sizes }}"
         width="{{ $m->width }}" height="{{ $m->height }}" alt="{{ $alt ?? $m->alt }}"
         @if($priority) fetchpriority="high" loading="eager" @else loading="lazy" @endif decoding="async" class="{{ $class }}">
@else
    {{-- No image uploaded yet: clearly labelled placeholder --}}
    <div class="{{ $class }} bg-brand-custard grid place-items-center text-ink-soft text-sm font-bold" role="img" aria-label="Image placeholder">[Image placeholder]</div>
@endif
