{{-- 10b. PARTNERS: sister travel brands, scrolling logo strip (logos in public/images/partners) --}}
@php
    $partners = [
        ['go-italia-trips', 'Go Italia Trips', 975, 256],
        ['go-asian-tours', 'Go Asian Tours', 209, 66],
        ['go-scottish-tours', 'Go Scottish Tours', 204, 76],
        ['go-rail-travel', 'Go Rail Travel', 632, 147],
        ['go-iceland-tours', 'Go Iceland Tours', 193, 72],
        ['go-irish-tours', 'Go Irish Tours', 243, 88],
        ['go-euro-golf', 'Go Euro Golf', 215, 54],
        ['stagit', 'Stagit.ie', 204, 75]
    ];
@endphp
<section class="relative bg-brand-navy text-white py-14 sm:py-16" aria-labelledby="partners-title">
    <div class="container-x grid gap-10 lg:grid-cols-[minmax(0,26rem)_1fr] lg:items-center">
        <div class="lg:border-r lg:border-white/15 lg:pr-10">
            <p class="eyebrow text-brand-gold">Our trusted global travel partners</p>
            <h2 id="partners-title" class="mt-4 text-white">Our Partners</h2>
            <span class="flag-rule" aria-hidden="true"></span>
            <p class="mt-5 text-base sm:text-lg text-white/85">Specialist travel brands, each an expert in its own region, working together to craft journeys worth remembering.</p>
        </div>
        <div class="marquee [mask-image:linear-gradient(to_right,transparent,#000_8%,#000_92%,transparent)]" aria-label="Our partner travel brands">
            <div class="marquee-track items-center gap-14 pr-14" style="animation-duration: 40s">
                @foreach([false, true] as $dup)
                    @foreach($partners as [$file, $name, $w, $h])
                        <img src="{{ asset('images/partners/'.$file.'.png') }}" width="{{ $w }}" height="{{ $h }}" alt="{{ $dup ? '' : $name }}" @if($dup) aria-hidden="true" @endif
                             loading="lazy" decoding="async" class="h-12 sm:h-14 w-auto max-w-none shrink-0 opacity-80 hover:opacity-100 transition">
                    @endforeach
                @endforeach
            </div>
        </div>
    </div>
</section>
