{{-- HOME HERO (bright Bavarian village): photo `bavaria-village` + video hero-bavaria.mp4 (both AI-generated, credited), headline left, quiz right, light overlays + soft text shadow for contrast.
     Copied from the /hero-section trial page on 2026-11-23. The previous castle hero is partials/home/hero-dark.blade.php and the frozen old home page is /hero-old. --}}
@php $heroPhoto = \App\Models\Media::byStem('koenigssee') ?? $heroMedia; @endphp
@if($heroPhoto)
    @push('head')
        <link rel="preload" as="image" href="{{ $heroPhoto->url(1280) }}" imagesrcset="{{ $heroPhoto->srcset() }}" imagesizes="(min-width: 1024px) 80vw, 100vw" fetchpriority="high">
    @endpush
@endif

{{-- 1. HERO: video background; headline and copy on the left, the quote quiz on the right --}}
<section class="relative isolate flex min-h-svh flex-col bg-brand-navy-dark text-white overflow-hidden" aria-labelledby="hero-title">
    @if($heroPhoto)
        <x-picture :path="$heroPhoto->path" priority sizes="(min-width: 1024px) 80vw, 100vw" class="absolute inset-0 -z-20 w-full h-full object-cover object-[68%_50%] lg:object-center"/>
    @endif
    <x-video-bg src="adventure.mp4" class="absolute inset-0 -z-[15] w-full h-full object-cover"/>
    <div class="absolute inset-0 -z-10 bg-gradient-to-r from-brand-navy-dark/45 via-brand-navy-dark/10 to-transparent" aria-hidden="true"></div>
    <div class="absolute inset-0 -z-10 bg-gradient-to-t from-brand-navy-dark/10 via-transparent to-transparent" aria-hidden="true"></div>

    <div class="container-x my-auto grid items-center gap-8 pt-32 pb-12 lg:grid-cols-[1.1fr_0.9fr] lg:gap-14 lg:pt-32 lg:pb-10 [@media(max-height:820px)]:lg:pt-28 [@media(max-height:820px)]:lg:pb-6">
        <div class="text-center lg:text-left">
            <p class="eyebrow text-brand-gold">The Germany Collection</p>
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
            <h1 id="hero-title" class="mt-6 max-w-3xl [text-shadow:0_2px_14px_rgb(0_0_0/0.4)] text-[1.75rem] sm:text-5xl lg:text-[3.25rem] xl:text-[3.5rem] 2xl:text-6xl [@media(max-height:820px)]:lg:!text-[2.75rem] leading-[1.08] mx-auto lg:mx-0">
                <span class="block">{{ $h1a }}</span>
                @if($h1b)
                    <em class="relative mx-auto lg:mx-0 mt-2 block w-fit whitespace-nowrap not-italic sm:mt-3"><span class="italic font-medium text-brand-gold">{{ $h1b }}</span>
                        <svg class="hl-underline absolute left-0 -bottom-2 sm:-bottom-3 w-full h-3 sm:h-4 text-brand-gold" viewBox="0 0 300 12" preserveAspectRatio="none" fill="none" aria-hidden="true">
                            <path d="M2 8 C 60 2, 110 11, 170 6 S 260 3, 298 7" stroke="currentColor" stroke-width="3" stroke-linecap="round" pathLength="1"/>
                        </svg></em>
                @endif
                @if($h1c)<span class="block">{{ $h1c }}</span>@endif
            </h1>
            <p class="mx-auto lg:mx-0 mt-5 max-w-xl [text-shadow:0_1px_10px_rgb(0_0_0/0.45)] text-base sm:text-lg text-white [@media(max-height:820px)]:lg:text-base">{{ $page->text('hero_sub', 'Self drive, chauffeur driven or small-group coach. Explore Quedlinburg, Saxon Switzerland, Cochem, Hiddensee and Gengenbach, planned around you.') }}</p>

            <x-google-rating class="mt-6 flex-wrap justify-center lg:justify-start"/>

        </div>

        <div class="mx-auto w-full max-w-xl lg:mx-0 lg:ml-auto lg:max-w-[574px]">
            <x-hero-quiz>
                {{-- highlights, inside the quiz card --}}
                <ul class="mt-6 flex flex-wrap items-center justify-center gap-x-3 gap-y-2 border-t border-white/20 pt-4 text-[0.66rem] font-bold uppercase tracking-[0.04em] text-white sm:flex-nowrap sm:text-[0.62rem] lg:flex-wrap xl:flex-nowrap" aria-label="Highlights">
                @foreach([['check', 'Free, no-obligation quote'], ['lock', 'Private tours'], ['pin', 'Local Germany experts']] as [$ic, $txt])
                    <li class="flex items-center gap-1.5 whitespace-nowrap"><x-icon :name="$ic" class="h-3.5 w-3.5 shrink-0 text-brand-gold"/>{{ $txt }}</li>
                @endforeach
            </ul>
            </x-hero-quiz>
        </div>
    </div>

    <a href="#tours" class="absolute bottom-6 left-1/2 -translate-x-1/2 hidden 2xl:flex flex-col items-center gap-2 text-[0.65rem] font-bold uppercase tracking-[0.3em] text-white/70 hover:text-brand-gold">
        Scroll <span class="block w-px h-10 bg-gradient-to-b from-brand-gold to-transparent" aria-hidden="true"></span>
    </a>
</section>
