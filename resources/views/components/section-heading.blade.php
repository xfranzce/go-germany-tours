@props(['title', 'eyebrow' => null, 'intro' => null, 'align' => 'center', 'tag' => 'h2', 'light' => false])
<div {{ $attributes->except('id')->merge(['class' => 'mb-10 max-w-3xl '.($align === 'center' ? 'text-center mx-auto' : '')]) }}>
    @if($eyebrow)
        <p class="text-sm font-extrabold uppercase tracking-[0.15em] {{ $light ? 'text-brand-gold' : 'text-brand-red-dark' }}">{{ $eyebrow }}</p>
    @endif
    <{{ $tag }} @if($attributes->get('id')) id="{{ $attributes->get('id') }}" @endif class="text-3xl sm:text-4xl mt-1 {{ $light ? 'text-white' : '' }}">{{ $title }}</{{ $tag }}>
    <div class="mt-3 h-1 w-16 rounded bg-brand-gold {{ $align === 'center' ? 'mx-auto' : '' }}" aria-hidden="true"></div>
    @if($intro)<p class="mt-4 text-lg {{ $light ? 'text-white/90' : 'text-ink-soft' }}">{{ $intro }}</p>@endif
</div>
