@props(['light' => true])
@php
    // Shown only when an administrator has switched it on AND entered the real rating (Admin > Site settings > Google rating badge).
    $on = ($site['google_rating_enabled'] ?? '0') === '1';
    $r = is_numeric($site['google_rating'] ?? null) ? (float) $site['google_rating'] : 0;
    $url = trim($site['google_reviews_url'] ?? '');
    $star = 'M12 2.5l2.94 5.96 6.58.96-4.76 4.64 1.12 6.55L12 17.5l-5.88 3.1 1.12-6.55L2.48 9.42l6.58-.96L12 2.5z';
@endphp
@if($on && $r >= 1 && $r <= 5)
    @php $tag = $url ? 'a' : 'div'; @endphp
    <{{ $tag }} @if($url) href="{{ $url }}" target="_blank" rel="noopener" @endif
        {{ $attributes->merge(['class' => 'inline-flex items-center gap-3 text-sm font-semibold '.($light ? 'text-white' : 'text-brand-navy')]) }}
        aria-label="Google traveler rating: {{ rtrim(rtrim(number_format($r, 1), '0'), '.') }} out of 5">
        <span class="inline-flex items-center gap-2" aria-hidden="true">
            <svg viewBox="0 0 48 48" class="size-5 shrink-0"><path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/><path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/><path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/><path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/></svg>
            <span class="text-base font-bold">Google</span>
        </span>
        <span class="h-6 w-px bg-current opacity-30" aria-hidden="true"></span>
        <span class="relative inline-flex" aria-hidden="true">
            <span class="flex gap-0.5 text-white/25">@for($i = 0; $i < 5; $i++)<svg viewBox="0 0 24 24" class="size-4 fill-current"><path d="{{ $star }}"/></svg>@endfor</span>
            <span class="absolute inset-y-0 left-0 overflow-hidden" style="width: {{ $r / 5 * 100 }}%"><span class="flex w-max gap-0.5 text-brand-gold">@for($i = 0; $i < 5; $i++)<svg viewBox="0 0 24 24" class="size-4 fill-current"><path d="{{ $star }}"/></svg>@endfor</span></span>
        </span>
        <span aria-hidden="true"><b class="font-extrabold">{{ rtrim(rtrim(number_format($r, 1), '0'), '.') }}/5</b> <span class="font-normal opacity-90">Traveler Rating</span></span>
    </{{ $tag }}>
@endif
