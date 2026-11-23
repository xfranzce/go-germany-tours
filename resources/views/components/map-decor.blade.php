{{-- Decorative "old travel map" background: gold contour lines in two opposite corners and a small compass top-right.
     Place inside a `relative isolate overflow-hidden` section; it is purely decorative (aria-hidden, no pointer events, behind the content). --}}
@php
    $contour = function () {
        $out = '';
        for ($i = 0; $i < 9; $i++) {
            $t = $i * 15;
            $out .= '<path d="M-10 '.(125 + $t).' C 55 '.(112 + $t).', 85 '.(52 + $t * .6).', 160 '.(30 + $t * .45).' S 275 -5, '.(310 + $t * 1.3).' -14"/>';
        }

        return $out;
    };
@endphp
<div class="pointer-events-none absolute inset-0 -z-10 text-brand-gold" aria-hidden="true">
    <svg class="absolute left-0 top-0 h-48 w-72 opacity-60 sm:h-72 sm:w-[26rem]" viewBox="0 0 420 300" fill="none" stroke="currentColor" stroke-width="1.1">{!! $contour() !!}</svg>
    <svg class="absolute bottom-0 right-0 h-48 w-72 rotate-180 opacity-60 sm:h-72 sm:w-[26rem]" viewBox="0 0 420 300" fill="none" stroke="currentColor" stroke-width="1.1">{!! $contour() !!}</svg>
    <svg class="absolute right-4 top-4 h-20 w-20 opacity-70 sm:right-12 sm:top-8 sm:h-32 sm:w-32" viewBox="0 0 100 100" fill="none" stroke="currentColor" stroke-width=".8">
        <circle cx="50" cy="50" r="35"/><circle cx="50" cy="50" r="31"/>
        @for($a = 0; $a < 360; $a += 10)<line x1="50" y1="{{ 50 - 31 }}" x2="50" y2="{{ $a % 90 === 0 ? 50 - 26 : 50 - 28.5 }}" transform="rotate({{ $a }} 50 50)"/>@endfor
        <polygon points="50,12 55.5,44.5 88,50 55.5,55.5 50,88 44.5,55.5 12,50 44.5,44.5" fill="currentColor" fill-opacity=".35"/>
        <polygon points="50,26 53.5,46.5 74,50 53.5,53.5 50,74 46.5,53.5 26,50 46.5,46.5" transform="rotate(45 50 50)" fill="currentColor" fill-opacity=".2"/>
        <g fill="currentColor" stroke="none" font-family="Georgia, serif" font-size="7" text-anchor="middle"><text x="50" y="8.5">N</text><text x="93" y="52.5">E</text><text x="50" y="97">S</text><text x="7" y="52.5">W</text></g>
    </svg>
</div>
