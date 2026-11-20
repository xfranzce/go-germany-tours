{{-- HOME HERO (fairytale): a bright, warm storybook look. Real photo of a half-timbered German town in daylight, soft cream light on the left for the
     headline, golden sun glow and a few twinkling sparkles; the quiz card stays dark navy so it stands out. The previous dark hero is in hero-dark.blade.php. --}}
@php
    $fairy = \App\Models\Media::byStem('quedlinburg-lane');
@endphp
@if($fairy)
    @push('head')
        <link rel="preload" as="image" href="{{ $fairy->url(1280) }}" imagesrcset="{{ $fairy->srcset() }}" imagesizes="(min-width: 1024px) 75vw, 50vw" fetchpriority="high">
    @endpush
@endif

<section class="relative isolate flex flex-col overflow-hidden bg-brand-cream text-brand-navy lg:min-h-[44rem]" aria-labelledby="hero-title">
    @if($fairy)
        <x-picture :path="$fairy->path" priority sizes="(min-width: 1024px) 75vw, 50vw" class="absolute inset-0 -z-20 h-full w-full object-cover object-[82%_50%] lg:object-[62%_50%]"/>
    @endif
    {{-- soft cream light: strong on the left behind the text, fading out over the photo (top-down on phones) --}}
    <div class="absolute inset-0 -z-10 bg-gradient-to-b from-brand-cream/90 via-brand-cream/40 to-transparent lg:bg-gradient-to-r lg:from-brand-cream/92 lg:via-brand-cream/48 lg:to-transparent" aria-hidden="true"></div>
    <div class="absolute inset-0 -z-10 bg-gradient-to-t from-brand-gold/25 via-transparent to-transparent" aria-hidden="true"></div>
    <div class="pointer-events-none absolute -z-10 -top-28 -left-20 size-[34rem] rounded-full bg-brand-gold/35 blur-[120px]" aria-hidden="true"></div>
    {{-- twinkling sparkles --}}
    <div class="pointer-events-none absolute inset-0 -z-10" aria-hidden="true">
        @foreach([[7, 26, 0], [20, 14, 1.3], [34, 34, 2.2], [11, 58, .7], [30, 70, 1.8], [44, 20, 2.9], [3, 12, 3.4]] as [$x, $y, $d])
            <span class="sparkle" style="left: {{ $x }}%; top: {{ $y }}%; animation-delay: {{ $d }}s"></span>
        @endforeach
    </div>

    <div class="container-x grid items-center gap-8 pt-28 pb-12 lg:grid-cols-[1.1fr_0.9fr] lg:gap-14 lg:pt-28 lg:pb-10">
        <div class="text-center lg:text-left">
            <p class="eyebrow text-brand-red-dark">The Germany Collection</p>
            @php
                // Highlighted words: wrap them in *stars* in Admin > Pages > Home; otherwise the last three words are highlighted.
                $h1 = trim($page->text('hero_h1', 'Tailor-Made Tours of *Germany\'s Fairytale Towns*'));
                if (preg_match('/^(.*?)\*(.+?)\*(.*)$/s', $h1, $m)) {
                    [$h1a, $h1b, $h1c] = [trim($m[1]), trim($m[2]), trim($m[3])];
                } else {
                    $w = preg_split('/\s+/', $h1);
                    $n = count($w) > 4 ? 3 : (count($w) > 1 ? 1 : 0);
                    [$h1a, $h1b, $h1c] = [implode(' ', array_slice($w, 0, count($w) - $n)), implode(' ', array_slice($w, count($w) - $n)), ''];
                }
            @endphp
            <h1 id="hero-title" class="mx-auto mt-6 max-w-3xl text-[1.75rem] leading-[1.08] text-brand-navy sm:text-5xl lg:mx-0 lg:text-[3.25rem] xl:text-[3.5rem] 2xl:text-6xl [@media(max-height:820px)]:lg:!text-[2.75rem]">
                <span class="block">{{ $h1a }}</span>
                @if($h1b)
                    <em class="relative mx-auto mt-2 block w-fit whitespace-nowrap not-italic sm:mt-3 lg:mx-0"><span class="font-medium italic text-brand-red">{{ $h1b }}</span>
                        <svg class="hl-underline absolute left-0 -bottom-2 h-3 w-full text-brand-gold-dark sm:-bottom-3 sm:h-4" viewBox="0 0 300 12" preserveAspectRatio="none" fill="none" aria-hidden="true">
                            <path d="M2 8 C 60 2, 110 11, 170 6 S 260 3, 298 7" stroke="currentColor" stroke-width="3" stroke-linecap="round" pathLength="1"/>
                        </svg></em>
                @endif
                @if($h1c)<span class="block">{{ $h1c }}</span>@endif
            </h1>
            <p class="mx-auto mt-5 max-w-xl text-base font-medium text-brand-navy/90 sm:text-lg lg:mx-0 [@media(max-height:820px)]:lg:text-base">{{ $page->text('hero_sub', 'Self drive, chauffeur driven or small-group coach. Explore Quedlinburg, Saxon Switzerland, Cochem, Hiddensee and Gengenbach, planned around you.') }}</p>

            <x-google-rating :light="false" class="mt-6 flex-wrap justify-center lg:justify-start"/>
        </div>

        <div class="mx-auto w-full max-w-xl lg:mx-0 lg:ml-auto">
            <x-hero-quiz :solid="true">
                {{-- highlights, inside the quiz card --}}
                <ul class="mt-6 flex flex-wrap items-center justify-center gap-x-3 gap-y-2 border-t border-white/20 pt-4 text-[0.66rem] font-bold uppercase tracking-[0.04em] text-white sm:flex-nowrap sm:text-[0.62rem] lg:flex-wrap xl:flex-nowrap" aria-label="Highlights">
                    @foreach([['check', 'Free, no-obligation quote'], ['lock', 'Private tours'], ['pin', 'Local Germany experts']] as [$ic, $txt])
                        <li class="flex items-center gap-1.5 whitespace-nowrap"><x-icon :name="$ic" class="h-3.5 w-3.5 shrink-0 text-brand-gold"/>{{ $txt }}</li>
                    @endforeach
                </ul>
            </x-hero-quiz>
        </div>
    </div>
</section>
