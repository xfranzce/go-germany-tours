@extends('layouts.app')

@php
    $heroPath = $page->data['hero_image'] ?? null;
    $heroMedia = \App\Models\Media::forPath($heroPath);
    // Gallery: every stored photo (1920px wide), in a fixed order with its place name
    $galleryLabels = [
        'hero-quedlinburg' => 'Quedlinburg', 'saxon-switzerland-bastei' => 'Saxon Switzerland', 'cochem-mosel' => 'Cochem',
        'hiddensee-lighthouse' => 'Hiddensee', 'quedlinburg-lane' => 'Quedlinburg', 'gengenbach-rathaus' => 'Gengenbach',
        'mosel-vineyard' => 'Mosel valley', 'hiddensee-beach' => 'Hiddensee',
    ];
    $galleryItems = collect($galleryLabels)->map(function ($label, $stem) {
        $m = \App\Models\Media::query()->where('path', 'like', "media/{$stem}-%")->first();
        return $m ? ['media' => $m, 'path' => $m->path, 'label' => $label] : null;
    })->filter()->values();
    $gallerySpans = ['md:col-span-2 md:row-span-2', '', 'md:row-span-2', '', 'md:col-span-2', 'md:row-span-2', 'md:row-span-2', 'md:col-span-2'];
    $travelers = [
        ['First-time visitors', 'Classic highlights and an easy first trip to Germany.', 'neuschwanstein-castle'],
        ['Couples', 'Romantic stays in fairytale towns and along the river.', 'couple-rhine-vineyard'],
        ['Families', 'Relaxed itineraries that work for every age.', 'family-old-town'],
        ['History lovers', 'Castles, abbeys and medieval streets at your own pace.', 'medieval-castle-path'],
        ['Nature lovers', 'Sandstone cliffs, vineyards and the Baltic coast.', 'saxon-switzerland-bastei'],
        ['Special occasions', 'Anniversaries, milestone birthdays and family reunions.', 'anniversary-dinner-terrace'],
    ];
@endphp

@if($heroMedia)
    @push('head')
        <link rel="preload" as="image" href="{{ $heroMedia->url(1280) }}" imagesrcset="{{ $heroMedia->srcset() }}" imagesizes="100vw" fetchpriority="high">
    @endpush
@endif

@section('content')
{{-- 1. HERO: video background; headline and copy on the left, the quote quiz on the right --}}
<section class="relative isolate flex min-h-svh items-center bg-brand-navy-dark text-white overflow-hidden" aria-labelledby="hero-title">
    @if($heroMedia)
        <x-picture :path="$heroPath" priority hires sizes="100vw" class="absolute inset-0 -z-20 w-full h-full object-cover"/>
    @endif
    <x-video-bg src="hero.mp4" class="absolute inset-0 -z-[15] w-full h-full object-cover"/>
    <div class="absolute inset-0 -z-10 bg-gradient-to-r from-brand-navy-dark/85 via-brand-navy-dark/50 to-brand-navy-dark/30" aria-hidden="true"></div>
    <div class="absolute inset-0 -z-10 bg-gradient-to-t from-brand-navy-dark/70 via-transparent to-brand-navy-dark/30" aria-hidden="true"></div>

    <div class="container-x grid items-center gap-8 pt-32 pb-12 lg:grid-cols-[1.1fr_0.9fr] lg:gap-14 lg:pt-32 lg:pb-10 [@media(max-height:820px)]:lg:pt-28 [@media(max-height:820px)]:lg:pb-6">
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
            <h1 id="hero-title" class="mt-6 max-w-3xl text-[1.75rem] sm:text-5xl lg:text-[3.25rem] xl:text-[3.5rem] 2xl:text-6xl [@media(max-height:820px)]:lg:!text-[2.75rem] leading-[1.08] mx-auto lg:mx-0">
                <span class="block">{{ $h1a }}</span>
                @if($h1b)
                    <em class="relative mx-auto lg:mx-0 mt-2 block w-fit whitespace-nowrap not-italic sm:mt-3"><span class="italic font-medium text-brand-gold">{{ $h1b }}</span>
                        <svg class="hl-underline absolute left-0 -bottom-2 sm:-bottom-3 w-full h-3 sm:h-4 text-brand-gold" viewBox="0 0 300 12" preserveAspectRatio="none" fill="none" aria-hidden="true">
                            <path d="M2 8 C 60 2, 110 11, 170 6 S 260 3, 298 7" stroke="currentColor" stroke-width="3" stroke-linecap="round" pathLength="1"/>
                        </svg></em>
                @endif
                @if($h1c)<span class="block">{{ $h1c }}</span>@endif
            </h1>
            <p class="mx-auto lg:mx-0 mt-5 max-w-xl text-base sm:text-lg text-white/90 [@media(max-height:820px)]:lg:text-base">{{ $page->text('hero_sub', 'Self drive, chauffeur driven or small-group coach. Explore Quedlinburg, Saxon Switzerland, Cochem, Hiddensee and Gengenbach, planned around you.') }}</p>

            <ul class="mx-auto lg:mx-0 mt-6 grid max-w-xl grid-cols-2 gap-x-6 gap-y-3 border-t border-white/25 pt-5 text-left [@media(max-height:820px)]:lg:mt-4" aria-label="Why travel with us">
                @foreach([['pin', 'Germany locations'], ['lock', 'Private tours'], ['chat', 'Professional guides'], ['star', 'Excellent reviews']] as [$ic, $txt])
                    <li class="flex items-center gap-3">
                        <span class="grid size-10 shrink-0 place-items-center rounded-full bg-brand-gold text-brand-navy shadow-lg shadow-black/20" aria-hidden="true"><x-icon :name="$ic" class="h-5 w-5"/></span>
                        <span class="text-sm font-bold uppercase tracking-[0.12em] text-white sm:text-[0.8rem]">{{ $txt }}</span>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="mx-auto w-full max-w-xl lg:mx-0 lg:ml-auto">
            <x-hero-quiz/>
        </div>
    </div>

    <a href="#tours" class="absolute bottom-6 left-1/2 -translate-x-1/2 hidden lg:flex flex-col items-center gap-2 text-[0.65rem] font-bold uppercase tracking-[0.3em] text-white/70 hover:text-brand-gold">
        Scroll <span class="block w-px h-10 bg-gradient-to-b from-brand-gold to-transparent" aria-hidden="true"></span>
    </a>
</section>
{{-- 2. TRUST STRIP --}}
<section class="bg-brand-custard-light text-brand-navy border-b border-brand-custard" aria-label="Why book with us">
    <ul class="container-x grid gap-3 sm:grid-cols-3 py-5 text-center font-semibold text-xs uppercase tracking-[0.16em]">
        @foreach([['check', 'Free, no-obligation quote'], ['shield', 'Self drive, chauffeur or coach'], ['adjust', 'Itineraries built around you']] as [$ic, $txt])
            <li class="flex items-center justify-center gap-2.5"><x-icon :name="$ic" class="w-4 h-4 text-brand-red"/>{{ $txt }}</li>
        @endforeach
    </ul>
</section>

{{-- 1b. WHY BOOK WITH US (light): layered photo collage + benefit cards (reveal on scroll) --}}
@php
    $whyFind = fn (string $stem) => ($m = \App\Models\Media::query()->where('path', 'like', "media/{$stem}-%")->first()) ? ['path' => $m->path, 'media' => $m] : null;
    $whyImg = $whyFind('neuschwanstein-castle') ?? $galleryItems->first(fn ($g) => str_contains($g['path'], 'quedlinburg-lane')) ?? $galleryItems->first();
    $whyImg2 = $whyFind('bacharach-rhine') ?? $galleryItems->first(fn ($g) => str_contains($g['path'], 'cochem')) ?? $galleryItems->skip(1)->first();
@endphp
<section class="relative isolate overflow-hidden bg-gradient-to-br from-brand-custard-light to-white text-brand-navy py-20 sm:py-28" aria-labelledby="book-title"
         x-data="{ shown: false }" x-init="const o = new IntersectionObserver(([e]) => { if (e.isIntersecting) { shown = true; o.disconnect() } }, { threshold: 0.15 }); o.observe($el)">
    <div class="pointer-events-none absolute -z-10 -top-40 -left-40 size-[34rem] rounded-full bg-brand-gold/25 blur-[120px]" aria-hidden="true"></div>
    <div class="pointer-events-none absolute -z-10 -bottom-40 -right-32 size-[30rem] rounded-full bg-brand-custard blur-[100px]" aria-hidden="true"></div>
    <span class="pointer-events-none absolute -z-10 right-0 top-6 font-display italic text-[11rem] sm:text-[17rem] leading-none text-brand-navy/[0.025] select-none" aria-hidden="true">Germany</span>

    <div class="container-x grid gap-16 lg:gap-24 lg:grid-cols-[0.9fr_1fr] lg:items-center">
        @if($whyImg)
            <div class="relative mx-auto w-full max-w-md lg:max-w-none pb-16 pr-10 sm:pr-16 transition duration-1000 ease-out" :class="shown ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10'">
                {{-- gold offset frame --}}
                <div class="absolute left-5 top-5 right-16 bottom-11 sm:left-7 sm:top-7 rounded-t-[999px] border-2 border-brand-gold/70" aria-hidden="true"></div>
                <div class="relative aspect-[4/5] overflow-hidden rounded-t-[999px] shadow-[0_40px_80px_-30px_rgb(0_48_73/0.55)]">
                    <x-picture :path="$whyImg['path']" hires sizes="(min-width: 1024px) 40vw, 90vw" class="w-full h-full object-cover object-[32%_50%] scale-105 hover:scale-110 transition duration-[1500ms]"/>
                    <div class="absolute inset-0 bg-gradient-to-t from-brand-navy/25 via-transparent to-transparent" aria-hidden="true"></div>
                </div>
                @if($whyImg2)
                    <div class="why-float absolute right-0 bottom-0 w-[58%] aspect-[4/3] overflow-hidden rounded-lg border-[6px] border-white shadow-[0_30px_60px_-20px_rgb(0_48_73/0.5)]">
                        <x-picture :path="$whyImg2['path']" sizes="(min-width: 1024px) 24vw, 50vw" class="w-full h-full object-cover"/>
                    </div>
                @endif
                <div class="absolute left-0 bottom-6 z-10 flex items-center gap-3 rounded-full bg-brand-gold text-brand-navy pl-3 pr-5 py-2.5 shadow-xl">
                    <span class="grid place-items-center size-9 rounded-full bg-brand-navy text-brand-gold"><x-icon name="check" class="w-4 h-4"/></span>
                    <span class="text-xs font-extrabold uppercase tracking-wider leading-tight">Free, no-obligation<br>quote</span>
                </div>
            </div>
        @endif

        <div>
            <p class="eyebrow text-brand-red-dark">Why book with {{ $site['brand_name'] }}</p>
            <h2 id="book-title" class="mt-4 text-brand-navy">Every German journey,
                <em class="block italic font-medium text-brand-red">feel uniquely yours.</em></h2>
            <span class="flag-rule" aria-hidden="true"></span>
            <p class="mt-5 text-base sm:text-lg text-ink-soft max-w-xl">Discover Germany through journeys shaped around how you like to travel. From fairytale towns with half-timbered streets to river valleys, sandstone cliffs and the Baltic coast, every detail is planned to make your trip unforgettable.</p>

            <ol class="mt-10 grid gap-4 max-w-xl">
                @foreach([
                    ['Choose how you travel', 'Self drive, private chauffeur or small-group coach.', 'adjust'],
                    ['Beyond the usual stops', 'Quedlinburg, Cochem, Gengenbach and other places many itineraries skip.', 'pin'],
                    ['Built around you, with real support', 'Your dates, group size and budget, with a real person to reply.', 'chat'],
                ] as $i => [$t, $d, $ic])
                    <li class="group relative flex gap-5 items-start overflow-hidden rounded-xl border border-brand-navy/10 bg-white shadow-card p-5 sm:p-6 transition duration-700 ease-out hover:border-brand-gold hover:shadow-xl hover:-translate-y-0.5"
                        :class="shown ? 'opacity-100 translate-x-0' : 'opacity-0 translate-x-10'" style="transition-delay: {{ 250 + $i * 180 }}ms">
                        <span class="absolute -right-1 -top-4 font-display text-8xl leading-none text-brand-navy/[0.05] group-hover:text-brand-gold/30 transition" aria-hidden="true">0{{ $i + 1 }}</span>
                        <span class="grid place-items-center shrink-0 size-12 rounded-full bg-brand-gold text-brand-navy shadow-lg shadow-brand-gold/30 group-hover:scale-110 transition" aria-hidden="true"><x-icon :name="$ic" class="w-5 h-5"/></span>
                        <div class="relative">
                            <h3 class="text-brand-navy">{{ $t }}</h3>
                            <p class="mt-1 text-sm sm:text-base text-ink-soft">{{ $d }}</p>
                        </div>
                    </li>
                @endforeach
            </ol>

            <button type="button" class="btn btn-gold mt-10 px-10 shadow-lg shadow-brand-gold/20" @click="$store.modal.show()">Get a free quote <x-icon name="arrow-right" class="w-5 h-5"/></button>
        </div>
    </div>
</section>

{{-- 3. SIGNATURE JOURNEYS: heading left, tour-type tabs right; tabs filter the packages --}}
@if($featured->isNotEmpty())
@php
    $tabs = $featured->groupBy(fn ($p) => $p->tourType->slug)->map(fn ($g) => ['name' => $g->first()->tourType->name, 'packages' => $g->take(6)]);
@endphp
<section class="py-16 sm:py-24" aria-labelledby="featured-title" x-data="{ tab: '{{ $tabs->keys()->first() }}' }">
    <div class="container-x">
        <div class="mb-12 flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
            <x-section-heading id="featured-title" align="left" eyebrow="Signature journeys" :title="$page->text('featured_title', 'Popular ways to see Germany')" emphasis="Planned around you."
                :intro="$page->text('featured_intro', 'A few ideas to start from. Every trip can be adjusted to suit you.')" class="!mb-0 lg:max-w-xl"/>
            <div role="tablist" aria-label="Choose how you travel" class="grid w-full grid-cols-3 gap-2 lg:flex lg:w-auto lg:shrink-0"
>
                @foreach($tabs as $slug => $t)
                    <button type="button" role="tab" id="tab-{{ $slug }}" aria-controls="panel-{{ $slug }}"
                            :aria-selected="tab === '{{ $slug }}'" :tabindex="tab === '{{ $slug }}' ? 0 : -1"
                            @click="tab = '{{ $slug }}'"
                            @keydown.right.prevent="$el.nextElementSibling?.focus(); $el.nextElementSibling?.click()"
                            @keydown.left.prevent="$el.previousElementSibling?.focus(); $el.previousElementSibling?.click()"
                            class="flex min-w-0 flex-col items-center justify-center gap-1 rounded-2xl border-2 px-1.5 py-2.5 text-center text-[0.65rem] font-extrabold uppercase leading-tight tracking-[0.08em] transition duration-300 sm:text-xs sm:tracking-[0.14em] lg:inline-flex lg:shrink-0 lg:flex-row lg:gap-2 lg:rounded-full lg:px-6 lg:py-2.5 lg:text-left"
                            :class="tab === '{{ $slug }}' ? 'border-brand-navy bg-brand-navy text-white shadow-lg shadow-brand-navy/20' : 'border-brand-navy/20 bg-white text-brand-navy hover:border-brand-navy'">
                        <x-icon :name="['self-drive' => 'car', 'chauffeur-driven' => 'chauffeur', 'coach-tours' => 'bus'][$slug] ?? 'pin'" class="size-6 shrink-0"/>
                        {{ $t['name'] }}
                    </button>
                @endforeach
            </div>
        </div>

        @foreach($tabs as $slug => $t)
            <div role="tabpanel" id="panel-{{ $slug }}" aria-labelledby="tab-{{ $slug }}" tabindex="0"
                 x-show="tab === '{{ $slug }}'" @unless($loop->first) style="display:none" @endunless
                 x-transition:enter="transition duration-500 ease-out" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($t['packages'] as $package)<x-package-card :package="$package"/>@endforeach
                </div>
                <p class="mt-12 text-center"><x-button :href="route('tours.show', $slug)" variant="outline">See all {{ strtolower($t['name']) }} packages <x-icon name="arrow-right" class="w-5 h-5"/></x-button></p>
            </div>
        @endforeach
    </div>
</section>
@endif

@include('partials.sections.banner')

{{-- 5. TRAVEL YOUR WAY (tour types) --}}
<section class="py-16 sm:py-24" aria-labelledby="tours-title" id="tours">
    <div class="container-x">
        <x-section-heading id="tours-title" eyebrow="Travel your way" :title="$page->text('tours_title', 'See Germany differently')"
            :intro="$page->text('tours_intro', 'The same beautiful places, three very different experiences.')"/>
        {{-- phones: swipeable carousel; md and up: three-column grid --}}
        <div class="-mx-4 flex snap-x snap-mandatory gap-4 overflow-x-auto px-4 pb-4 [scrollbar-width:none] md:mx-0 md:grid md:grid-cols-3 md:gap-8 md:overflow-visible md:px-0 md:pb-0 [&::-webkit-scrollbar]:hidden" data-carousel="md" aria-label="Ways to travel">
            @foreach($tourTypes as $t)
                <article class="card flex w-[82%] shrink-0 snap-center flex-col md:w-auto md:shrink">
                    <a href="{{ route('tours.show', $t->slug) }}" class="block aspect-[4/3] bg-brand-custard overflow-hidden" tabindex="-1" aria-hidden="true">
                        <x-picture :path="$t->image" sizes="(min-width: 768px) 24rem, 92vw" class="w-full h-full object-cover"/>
                    </a>
                    <div class="p-7 flex flex-col flex-1">
                        <p class="eyebrow text-brand-red-dark">{{ sprintf('%02d', $loop->iteration) }}</p>
                        <h3 class="mt-2"><a href="{{ route('tours.show', $t->slug) }}" class="hover:underline">{{ $t->name }}</a></h3>
                        <p class="mt-3 text-ink-soft flex-1">{{ $t->summary }}</p>
                        <div class="mt-6 grid gap-2 sm:grid-cols-2 md:grid-cols-1 xl:grid-cols-2">
                            <x-button size="sm" data-title="Get a quote: {{ $t->name }}" data-prefill="Interested in: {{ $t->name }} tours."
                                      @click="$store.modal.show({form:'package_quote', ...$el.dataset})">Get a Quote</x-button>
                            <x-button variant="outline" size="sm" data-title="Ask us a question" data-subtitle="{{ $t->name }} tours" data-prefill="Question about {{ $t->name }} tours: "
                                      @click="$store.modal.show({form:'package_question', ...$el.dataset})">Ask Us a Question</x-button>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>

@include('partials.sections.why')

{{-- 6b. BY TRAVELER --}}
<section class="py-16 sm:py-24 bg-brand-custard-light" aria-labelledby="trav-title">
    <div class="container-x">
        <x-section-heading id="trav-title" eyebrow="By traveler" title="Journeys for every" emphasis="travel style."
            intro="Whoever is coming along, we shape the trip to suit."/>
        <div x-data="{ a: 0, n: {{ count($travelers) }}, go(d) { this.a = (this.a + d + this.n) % this.n; const c = this.$refs.strip.children[this.a]; c && c.scrollIntoView({ inline: 'center', block: 'nearest', behavior: 'smooth' }) } }">
            <div x-ref="strip" class="-mx-4 flex snap-x snap-mandatory gap-3 overflow-x-auto px-4 pb-2 [scrollbar-width:none] sm:mx-0 sm:px-0 md:snap-none md:gap-4 md:overflow-visible [&::-webkit-scrollbar]:hidden">
                @foreach($travelers as $i => [$tt, $td, $stem])
                    @php $tm = \App\Models\Media::query()->where('path', 'like', "media/{$stem}-%")->first(); @endphp
                    <div class="group relative h-[26rem] w-[72vw] shrink-0 snap-center cursor-pointer overflow-hidden rounded-2xl bg-brand-navy shadow-card transition-[flex-grow] duration-500 ease-out sm:w-[22rem] md:h-[30rem] md:w-auto md:shrink md:basis-0 max-md:!grow-0 lg:h-[32rem]"
                         :style="{ flexGrow: a === {{ $i }} ? 5 : 1 }" style="flex-grow: {{ $i === 0 ? 5 : 1 }}"
                         tabindex="0" role="button" :aria-expanded="a === {{ $i }}" aria-label="{{ $tt }}"
                         @mouseenter="if (window.matchMedia('(min-width: 768px)').matches) a = {{ $i }}" @focus="a = {{ $i }}" @click="a = {{ $i }}" @keydown.enter.prevent="a = {{ $i }}">
                        @if($tm)<x-picture :path="$tm->path" sizes="(min-width: 768px) 40vw, 72vw" class="absolute inset-0 h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"/>@endif
                        <div class="absolute inset-0 bg-gradient-to-t from-brand-navy-dark/85 via-brand-navy-dark/10 to-transparent transition-opacity duration-500" :class="a === {{ $i }} ? 'opacity-100' : 'opacity-40'" aria-hidden="true"></div>
                        <button type="button" class="absolute right-4 top-4 grid size-11 place-items-center rounded-full bg-white text-brand-navy shadow-lg transition hover:bg-brand-gold" x-show="a === {{ $i }}" x-transition.opacity
                                aria-label="Get a quote: {{ $tt }}" data-title="Get a quote: {{ $tt }}" data-prefill="Trip style: {{ $tt }}."
                                @click.stop="$store.modal.show({form:'package_quote', ...$el.dataset})"><x-icon name="arrow-right" class="size-5"/></button>
                        <div class="absolute inset-x-0 bottom-0 p-6 text-white md:p-7">
                            <h3 class="font-display text-3xl leading-tight text-white" :class="a === {{ $i }} ? '' : 'md:sr-only'">{{ $tt }}</h3>
                            <p class="mt-2 max-w-xs text-white/85" x-show="a === {{ $i }}" x-transition.opacity>{{ $td }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-6 flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <button type="button" class="grid size-11 place-items-center rounded-full border-2 border-brand-navy text-brand-navy transition hover:bg-brand-navy hover:text-white" aria-label="Previous" @click="go(-1)"><x-icon name="arrow-right" class="size-5 rotate-180"/></button>
                    <button type="button" class="grid size-11 place-items-center rounded-full border-2 border-brand-navy text-brand-navy transition hover:bg-brand-navy hover:text-white" aria-label="Next" @click="go(1)"><x-icon name="arrow-right" class="size-5"/></button>
                </div>
                <button type="button" class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-[0.18em] text-brand-red-dark hover:text-brand-navy" @click="$store.modal.show()">Plan my trip <x-icon name="arrow-right" class="size-4"/></button>
            </div>
        </div>
    </div>
</section>

@include('partials.sections.how')

@include('partials.sections.reviews')

{{-- 9. GALLERY ("A visual journey"): checkerboard mosaic, captions always visible, click for lightbox --}}
@php
    $mosaic = collect([
        ['quedlinburg-lane', 'Half-timbered streets', '', 'Quedlinburg, Saxony-Anhalt'],
        ['cochem-mosel', 'Riverside towns', '', 'Cochem, Mosel valley'],
        ['neuschwanstein-castle', 'Castles and the Alps', 'sm:col-span-2', 'Neuschwanstein, Bavaria'],
        ['saxon-switzerland-bastei', 'Sandstone cliffs and mist', 'sm:col-span-2 sm:col-start-3 sm:row-start-1', 'Saxon Switzerland, Saxony'],
        ['hiddensee-beach', 'Baltic coast', '', 'Hiddensee island'],
        ['bacharach-rhine', 'Rhine valley', '', 'Bacharach, Rhine'],
    ])->map(function ($t) {
        $m = \App\Models\Media::query()->where('path', 'like', "media/{$t[0]}-%")->first();
        return $m ? ['media' => $m, 'path' => $m->path, 'label' => $t[1], 'span' => $t[2], 'place' => $t[3]] : null;
    })->filter()->values();
@endphp
@if($mosaic->count() >= 4)
<section class="relative isolate overflow-hidden py-20 sm:py-28 bg-brand-navy-dark text-white" aria-labelledby="gallery-title"
         x-data="{ open: false, src: '', cap: '', alt: '', shown: false }" @keydown.escape.window="open = false"
         x-init="const o = new IntersectionObserver(([e]) => { if (e.isIntersecting) { shown = true; o.disconnect() } }, { threshold: 0.1 }); o.observe($el)">
    <div class="pointer-events-none absolute -z-10 -top-32 -right-32 size-[30rem] rounded-full bg-brand-gold/20 blur-[120px]" aria-hidden="true"></div>
    <div class="pointer-events-none absolute -z-10 bottom-0 -left-32 size-[26rem] rounded-full bg-brand-tangerine/15 blur-[120px]" aria-hidden="true"></div>
    <div class="container-x max-w-[1700px]">
        <div class="mb-10 grid gap-6 lg:grid-cols-2 lg:items-end lg:gap-16">
            <x-section-heading id="gallery-title" light align="left" eyebrow="A visual journey" title="Beyond the postcard." class="!mb-0"/>
            <p class="text-base sm:text-lg text-white/85 lg:max-w-xl lg:justify-self-end">A glimpse of Germany beyond the familiar, from quiet village lanes and half-timbered streets to misty cliffs, river valleys and landscapes worth taking the longer way for. Tap a photo to see it full size.</p>
        </div>
        <div data-carousel="sm" data-carousel-light class="-mx-4 flex snap-x snap-mandatory gap-[15px] overflow-x-auto px-4 pb-2 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden sm:mx-0 sm:grid sm:grid-cols-4 sm:overflow-visible sm:px-0 sm:pb-0 sm:auto-rows-[18rem] lg:auto-rows-[25rem]">
            @foreach($mosaic as $g)
                <button type="button" class="group relative isolate h-[18rem] w-[80%] shrink-0 snap-center overflow-hidden rounded-none sm:h-auto sm:w-auto sm:shrink text-left shadow-[0_20px_40px_-24px_rgb(0_0_0/0.8)] transition duration-700 ease-out hover:-translate-y-1.5 hover:shadow-[0_34px_60px_-26px_rgb(0_0_0/0.9)] {{ $g['span'] }}"
                        :class="shown ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-12'" style="transition-delay: {{ $loop->index * 110 }}ms"
                        data-src="{{ $g['media']->url() }}" data-cap="{{ $g['label'] }}" data-alt="{{ $g['media']->alt }}"
                        @click="src = $el.dataset.src; cap = $el.dataset.cap; alt = $el.dataset.alt; open = true"
                        aria-label="View {{ $g['label'] }} photo full size">
                    <x-picture :path="$g['path']" hires sizes="(min-width: 1024px) 50vw, 100vw" class="absolute inset-0 -z-10 w-full h-full object-cover transition duration-[1400ms] ease-out group-hover:scale-105"/>
                    <span class="absolute inset-0 -z-[5]" style="background-image: linear-gradient(180deg, rgb(0 48 73 / 0) 55%, rgb(0 48 73 / 0.92) 100%)" aria-hidden="true"></span>
                    <span class="absolute left-4 top-4 inline-flex items-center gap-1.5 rounded-full bg-white/90 px-3 py-1.5 text-[0.7rem] font-extrabold uppercase tracking-wider text-brand-navy shadow-md backdrop-blur"><x-icon name="pin" class="w-3.5 h-3.5 text-brand-red"/>{{ $g['place'] }}</span>
                    <span class="absolute right-4 top-4 grid size-11 place-items-center rounded-full bg-brand-gold text-brand-navy opacity-0 scale-75 shadow-lg transition duration-500 group-hover:opacity-100 group-hover:scale-100 group-focus-visible:opacity-100 group-focus-visible:scale-100" aria-hidden="true"><svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"/></svg></span>
                    <span class="absolute inset-x-0 bottom-0 p-5 sm:p-6">
                        <span class="block font-display text-2xl sm:text-3xl font-semibold leading-tight text-white">{{ $g['label'] }}</span>
                        <span class="mt-3 block h-[3px] w-10 bg-brand-gold transition-all duration-500 group-hover:w-24" aria-hidden="true"></span>
                    </span>
                </button>
            @endforeach
        </div>
        <div class="mt-12 flex flex-col items-center gap-4 text-center transition duration-1000 ease-out" :class="shown ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'" style="transition-delay: 800ms">
            <p class="font-display text-2xl sm:text-3xl text-white">Seen enough to start planning? <em class="text-brand-gold">Let us build it around you.</em></p>
            <button type="button" class="btn btn-gold px-10 shadow-lg shadow-brand-gold/20" @click="$store.modal.show()">Get a free quote <x-icon name="arrow-right" class="w-5 h-5"/></button>
        </div>
    </div>

    {{-- Lightbox --}}
    <div x-show="open" x-cloak x-transition.opacity class="fixed inset-0 z-[60] bg-brand-navy-dark/95 grid place-items-center p-4"
         role="dialog" aria-modal="true" aria-label="Photo viewer" @click.self="open = false">
        <button type="button" class="absolute top-4 right-4 p-2 text-white hover:text-brand-gold" @click="open = false"><span class="sr-only">Close</span><x-icon name="x" class="w-8 h-8"/></button>
        <figure class="max-w-6xl w-full text-white">
            <img :src="src" :alt="alt" class="w-full max-h-[82vh] object-contain rounded">
            <figcaption class="mt-3 text-center font-display text-2xl" x-text="cap"></figcaption>
        </figure>
    </div>
</section>
@endif

{{-- 10. OUR STORY teaser: layered photo collage + promises --}}
@php
    $storyA = \App\Models\Media::query()->where('path', 'like', 'media/quedlinburg-lane-%')->first();
    $storyB = \App\Models\Media::query()->where('path', 'like', 'media/mosel-vineyard-%')->first();
@endphp
<section class="relative overflow-hidden py-16 sm:py-24 bg-brand-custard-light" aria-labelledby="story-title">
    <span class="pointer-events-none absolute -right-24 -top-24 size-96 rounded-full border-2 border-dashed border-brand-gold/50" aria-hidden="true"></span>
    <span class="pointer-events-none absolute -bottom-32 -left-24 size-[28rem] rounded-full bg-brand-gold/10" aria-hidden="true"></span>
    <div class="container-x relative grid gap-14 lg:grid-cols-[1.05fr_0.95fr] lg:items-center lg:gap-20">
        {{-- collage --}}
        <div class="relative mx-auto w-full max-w-xl pb-16 pr-8 sm:pr-14 lg:mx-0">
            <span class="absolute -left-4 -top-4 h-full w-[88%] rounded-[var(--radius-card)] border-2 border-dashed border-brand-gold" aria-hidden="true"></span>
            <div class="relative aspect-[4/5] w-[88%] overflow-hidden rounded-[var(--radius-card)] shadow-card">
                @if($storyA)<x-picture :path="$storyA->path" sizes="(min-width: 1024px) 28rem, 80vw" class="size-full object-cover"/>@else<x-picture :path="$destinations->first()?->image ?? $heroPath" sizes="28rem" class="size-full object-cover"/>@endif
            </div>
            @if($storyB)
                <div class="absolute bottom-0 right-0 aspect-square w-[52%] overflow-hidden rounded-[var(--radius-card)] border-[6px] border-brand-custard-light shadow-xl">
                    <x-picture :path="$storyB->path" sizes="(min-width: 1024px) 15rem, 40vw" class="size-full object-cover"/>
                </div>
            @endif
            <div class="absolute left-4 top-6 flex items-center gap-3 rounded-full bg-white py-2 pl-2 pr-5 shadow-lg sm:-left-6">
                <span class="grid size-10 place-items-center rounded-full bg-brand-navy text-white"><x-logo-mark class="size-7"/></span>
                <span class="text-sm font-bold leading-tight text-brand-navy">Free, no-obligation<br>quote</span>
            </div>
        </div>

        {{-- copy --}}
        <div>
            <x-section-heading id="story-title" align="left" eyebrow="Get to know us" title="Plan with" emphasis="peace of mind." class="!mb-0 !max-w-none"/>
            <p class="mt-5 text-lg text-ink-soft">Thoughtfully crafted journeys across Germany, shaped by your travel style. Tell us what you have in mind and a member of the team will be in touch with your quote.</p>
            <ul class="mt-8 space-y-4">
                @foreach([['adjust', 'Self drive, private chauffeur or small-group coach'], ['check', 'Itineraries built around your dates, group and budget'], ['chat', 'A real person replies with your quote']] as [$ic, $t])
                    <li class="flex items-center gap-4">
                        <span class="grid size-11 shrink-0 place-items-center rounded-full bg-white text-brand-red shadow ring-1 ring-brand-gold/60"><x-icon :name="$ic" class="size-5"/></span>
                        <span class="text-lg font-semibold text-brand-navy">{{ $t }}</span>
                    </li>
                @endforeach
            </ul>
            <div class="mt-9 flex flex-wrap gap-3">
                <button type="button" class="btn btn-primary px-8" @click="$store.modal.show()">Get my free quote <x-icon name="arrow-right" class="size-5"/></button>
                <x-button :href="route('about')" variant="outline">About us</x-button>
            </div>
        </div>
    </div>
</section>

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

@include('partials.sections.faq')

{{-- 11b. JOURNAL --}}
@if($posts->isNotEmpty())
<section class="py-16 sm:py-24 bg-brand-custard-light" aria-labelledby="journal-title">
    <div class="container-x">
        <x-section-heading id="journal-title" eyebrow="Travel journal" title="Ideas for planning" emphasis="your Germany trip."/>
        <div data-carousel="md" class="-mx-4 flex snap-x snap-mandatory gap-4 overflow-x-auto px-4 pb-4 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden md:mx-0 md:overflow-visible md:px-0 md:pb-0 md:grid md:grid-cols-2 md:gap-8 lg:grid-cols-3">
            @foreach($posts as $post)
                <article class="group card flex w-[82%] shrink-0 snap-center flex-col overflow-hidden md:w-auto md:shrink transition duration-500 hover:-translate-y-1.5 hover:shadow-xl">
                    <a href="{{ route('blog.show', $post->slug) }}" class="relative block aspect-[3/2] overflow-hidden bg-brand-custard" tabindex="-1" aria-hidden="true">
                        <x-picture :path="$post->image" sizes="(min-width: 1024px) 28rem, (min-width: 768px) 45vw, 92vw" class="h-full w-full object-cover transition duration-[1200ms] ease-out group-hover:scale-105"/>
                        <span class="absolute left-4 top-4 rounded-full bg-brand-gold px-3 py-1 text-[0.7rem] font-extrabold uppercase tracking-wider text-brand-navy shadow">Travel guide</span>
                    </a>
                    <div class="flex flex-1 flex-col p-6 sm:p-7">
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-ink-soft">{{ $post->published_at->format('M j, Y') }} <span aria-hidden="true">&middot;</span> {{ $post->readingMinutes() }} min read</p>
                        <h3 class="mt-3"><a class="transition hover:text-brand-red" href="{{ route('blog.show', $post->slug) }}">{{ $post->title }}</a></h3>
                        <p class="mt-3 flex-1 text-ink-soft">{{ $post->excerpt }}</p>
                        <a class="mt-5 inline-flex items-center gap-2 text-xs font-bold uppercase tracking-[0.18em] text-brand-red-dark" href="{{ route('blog.show', $post->slug) }}">Read more <x-icon name="arrow-right" class="h-4 w-4 transition group-hover:translate-x-1"/><span class="sr-only">: {{ $post->title }}</span></a>
                    </div>
                </article>
            @endforeach
        </div>
        <p class="mt-12 text-center"><x-button :href="route('blog.index')" variant="outline">All travel guides</x-button></p>
    </div>
</section>
@endif

{{-- 11c. SPEAK TO A SPECIALIST --}}
@php $tel2 = preg_replace('/[^+\d]/', '', $site['phone'] ?? ''); @endphp
@if($tel2 || !empty($site['email']))
<section class="py-14 bg-white border-t border-brand-custard" aria-labelledby="contact-strip-title">
    <div class="container-x grid gap-8 md:grid-cols-[1.2fr_1fr_1fr] md:items-center text-center md:text-left">
        <div>
            <p class="eyebrow text-brand-red-dark">Prefer to talk?</p>
            <h2 id="contact-strip-title" class="mt-2">Speak to a Germany specialist</h2>
        </div>
        @if($tel2)<a href="tel:{{ $tel2 }}" class="flex items-center justify-center md:justify-start gap-3 font-bold hover:text-brand-red"><span class="grid place-items-center w-11 h-11 rounded-full border border-brand-navy/30"><x-icon name="phone" class="w-5 h-5"/></span>{{ $site['phone_display'] ?: $site['phone'] }}</a>@endif
        @if(!empty($site['email']))<a href="mailto:{{ $site['email'] }}" class="flex items-center justify-center md:justify-start gap-3 font-bold hover:text-brand-red break-all"><span class="grid place-items-center w-11 h-11 rounded-full border border-brand-navy/30 shrink-0"><x-icon name="mail" class="w-5 h-5"/></span>{{ $site['email'] }}</a>@endif
    </div>
</section>
@endif
{{-- 12. CLOSING CTA band --}}
<section class="relative isolate py-24 bg-brand-navy-dark text-white overflow-hidden" aria-labelledby="cta-title">
    @if($heroMedia)
        <x-picture :path="$heroPath" sizes="100vw" class="absolute inset-0 -z-20 w-full h-full object-cover opacity-30"/>
    @endif
    <x-video-bg src="cta.mp4" class="absolute inset-0 -z-[15] w-full h-full object-cover opacity-40"/>
    <div class="container-x text-center max-w-3xl">
        <p class="eyebrow centered text-brand-gold">Start planning</p>
        <h2 id="cta-title" class="mt-4 text-white">{{ $page->text('cta_title', 'Ready to see Germany your way?') }}</h2>
        <span class="flag-rule centered" aria-hidden="true"></span>
        <p class="mt-5 text-base sm:text-lg text-white/85">{{ $page->text('cta_text', 'Get a free, no-obligation quote in under a minute.') }}</p>
        <div class="mt-9 flex flex-wrap justify-center gap-3">
            <button type="button" class="btn btn-primary text-lg" @click="$store.modal.show()">Start Your Adventure</button>
            @php $tel = preg_replace('/[^+\d]/', '', $site['phone'] ?? ''); @endphp
            @if($tel)<a class="btn btn-outline-light text-lg" href="tel:{{ $tel }}"><x-icon name="phone" class="w-5 h-5"/> {{ $site['phone_display'] ?: $site['phone'] }}</a>@endif
        </div>
    </div>
</section>
@endsection
