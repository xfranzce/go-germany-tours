@props(['title', 'eyebrow' => null, 'intro' => null, 'align' => 'center', 'tag' => 'h2', 'light' => false, 'emphasis' => null])
<div {{ $attributes->except('id')->merge(['class' => 'mb-14 max-w-2xl '.($align === 'center' ? 'text-center mx-auto' : '')]) }}>
    @if($eyebrow)
        <p class="eyebrow {{ $align === 'center' ? 'centered' : '' }} {{ $light ? 'text-brand-gold' : 'text-brand-red-dark' }}">{{ $eyebrow }}</p>
    @endif
    <{{ $tag }} @if($attributes->get('id')) id="{{ $attributes->get('id') }}" @endif class="mt-4 {{ $light ? 'text-white' : '' }}">{{ $title }}@if($emphasis) <em class="block italic font-medium {{ $light ? 'text-brand-gold' : 'text-brand-red' }}">{{ $emphasis }}</em>@endif</{{ $tag }}>
    <span class="flag-rule {{ $align === 'center' ? 'centered' : '' }}" aria-hidden="true"></span>
    @if($intro)<p class="mt-5 text-base sm:text-lg {{ $light ? 'text-white/85' : 'text-ink-soft' }}">{{ $intro }}</p>@endif
</div>
