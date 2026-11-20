@props(['path' => null, 'alt' => null, 'sizes' => '100vw', 'priority' => false, 'class' => '', 'hires' => false])
@php $m = \App\Models\Media::forPath($path); @endphp
@if($m)
    {{-- default src is 1920px (hires: 2560px); browsers pick from srcset up to 3840px --}}
    <img src="{{ $m->url($hires ? max(array_filter($m->widths, fn ($w) => $w <= 2560) ?: [max($m->widths)]) : max(array_filter($m->widths, fn ($w) => $w <= 1920) ?: [min($m->widths)])) }}" srcset="{{ $m->srcset() }}" sizes="{{ $sizes }}"
         width="{{ $m->width }}" height="{{ $m->height }}" alt="{{ $alt ?? $m->alt }}"
         @if($priority) fetchpriority="high" loading="eager" @else loading="lazy" fetchpriority="low" @endif decoding="async" class="{{ $class }}">
@else
    {{-- No image uploaded yet: clearly labelled placeholder --}}
    <div class="{{ $class }} bg-brand-custard grid place-items-center text-ink-soft text-sm font-bold" role="img" aria-label="Image placeholder">[Image placeholder]</div>
@endif
