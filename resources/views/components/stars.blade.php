@props(['rating', 'count' => null, 'sample' => false, 'light' => false])
@php
    $r = max(0, min(5, (float) $rating));
    $star = 'M12 2.5l2.94 5.96 6.58.96-4.76 4.64 1.12 6.55L12 17.5l-5.88 3.1 1.12-6.55L2.48 9.42l6.58-.96L12 2.5z';
@endphp
<div {{ $attributes->merge(['class' => 'inline-flex items-center gap-2']) }} role="img" aria-label="Rated {{ number_format($r, 1) }} out of 5{{ $count ? ' from '.$count.' reviews' : '' }}{{ $sample ? ' (sample rating)' : '' }}">
    <span class="relative inline-flex" aria-hidden="true">
        <span class="flex gap-0.5 text-brand-navy/15">
            @for($i = 0; $i < 5; $i++)<svg viewBox="0 0 24 24" class="size-4 fill-current"><path d="{{ $star }}"/></svg>@endfor
        </span>
        <span class="absolute inset-y-0 left-0 overflow-hidden" style="width: {{ $r / 5 * 100 }}%">
            <span class="flex gap-0.5 text-brand-gold-dark w-max drop-shadow-[0_1px_0_rgb(227_164_43/0.4)]">
                @for($i = 0; $i < 5; $i++)<svg viewBox="0 0 24 24" class="size-4 fill-current"><path d="{{ $star }}"/></svg>@endfor
            </span>
        </span>
    </span>
    <span class="text-sm font-extrabold {{ $light ? 'text-white' : 'text-brand-navy' }}">{{ number_format($r, 1) }}</span>
    @if($count)<span class="text-xs {{ $light ? 'text-white/75' : 'text-ink-soft' }}">({{ number_format($count) }} reviews)</span>@endif
    @if($sample)<span class="rounded-full bg-brand-gold/25 px-2 py-0.5 text-[0.65rem] font-bold uppercase tracking-wide text-brand-navy">sample rating</span>@endif
</div>
