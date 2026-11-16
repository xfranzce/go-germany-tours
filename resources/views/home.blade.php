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
        ['First-time visitors', 'Classic highlights and an easy first trip to Germany.'],
        ['Couples', 'Romantic stays in fairytale towns and along the river.'],
        ['Families', 'Relaxed itineraries that work for every age.'],
        ['History lovers', 'Castles, abbeys and medieval streets at your own pace.'],
        ['Nature lovers', 'Sandstone cliffs, vineyards and the Baltic coast.'],
        ['Special occasions', 'Anniversaries, milestone birthdays and family reunions.'],
    ];
@endphp

@if($heroMedia)
    @push('head')
        <link rel="preload" as="image" href="{{ $heroMedia->url(1280) }}" imagesrcset="{{ $heroMedia->srcset() }}" imagesizes="100vw" fetchpriority="high">
    @endpush
@endif

@section('content')
{{-- 1. HERO: full-screen video background, headline and a destination dropdown (no quiz) --}}
<section class="relative isolate flex min-h-[88svh] flex-col justify-center bg-brand-navy-dark text-white overflow-hidden" aria-labelledby="hero-title">
    @if($heroMedia)
        <x-picture :path="$heroPath" priority hires sizes="100vw" class="absolute inset-0 -z-20 w-full h-full object-cover"/>
    @endif
    <x-video-bg src="hero.mp4" class="absolute inset-0 -z-[15] w-full h-full object-cover"/>
    <div class="absolute inset-0 -z-10 bg-gradient-to-t from-brand-navy-dark/90 via-brand-navy-dark/35 to-brand-navy-dark/55" aria-hidden="true"></div>

    <div class="container-x pt-20 pb-36 sm:pt-28 sm:pb-44 text-center">
        <p class="eyebrow centered text-brand-gold">The Germany Collection</p>
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
        <h1 id="hero-title" class="mx-auto mt-6 max-w-5xl text-4xl sm:text-6xl lg:text-7xl leading-[1.08]">
            <span class="block">{{ $h1a }}</span>
            @if($h1b)
                <em class="relative mx-auto mt-2 block w-fit not-italic sm:mt-3"><span class="italic font-medium text-brand-gold">{{ $h1b }}</span>
                    <svg class="hl-underline absolute left-0 -bottom-2 sm:-bottom-3 w-full h-3 sm:h-4 text-brand-gold" viewBox="0 0 300 12" preserveAspectRatio="none" fill="none" aria-hidden="true">
                        <path d="M2 8 C 60 2, 110 11, 170 6 S 260 3, 298 7" stroke="currentColor" stroke-width="3" stroke-linecap="round" pathLength="1"/>
                    </svg></em>
            @endif
            @if($h1c)<span class="block">{{ $h1c }}</span>@endif
        </h1>
        <p class="mx-auto mt-6 max-w-xl text-base sm:text-lg text-white/90">{{ $page->text('hero_sub', 'Self drive, chauffeur driven or small-group coach. Explore Quedlinburg, Saxon Switzerland, Cochem, Hiddensee and Gengenbach, planned around you.') }}</p>

        @if($destinations->isNotEmpty())
            <form class="mx-auto mt-10 max-w-2xl" x-data="{ url: '' }" @submit.prevent="window.location.href = url || '#tours'" aria-label="Choose a destination">
                <label for="hero-dest" class="eyebrow centered text-white/90">Where to next?</label>
                <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:gap-0 sm:rounded sm:bg-white/10 sm:backdrop-blur-md sm:border sm:border-white/30 sm:p-1.5">
                    <div class="relative flex-1">
                        <select id="hero-dest" x-model="url"
                                class="w-full appearance-none bg-white/10 sm:bg-transparent backdrop-blur-md sm:backdrop-blur-none border border-white/30 sm:border-0 rounded sm:rounded-none text-white font-semibold tracking-wide px-5 py-4 pr-12 min-h-14 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-gold [&>option]:text-brand-navy [&>option]:bg-white">
                            <option value="">Choose a destination</option>
                            @foreach($destinations as $d)
                                <option value="{{ route('destinations.show', $d->slug) }}">{{ $d->name }}</option>
                            @endforeach
                        </select>
                        <x-icon name="chevron-down" class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 w-5 h-5 text-brand-gold"/>
                    </div>
                    <button type="submit" class="btn btn-primary sm:px-8">Explore destination</button>
                </div>
            </form>
        @endif

        <p class="mt-8"><button type="button" class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-[0.2em] text-white/90 hover:text-brand-gold border-b border-white/40 pb-1" @click="$store.modal.show()">Or get a free quote <x-icon name="arrow-right" class="w-4 h-4"/></button></p>
    </div>

    {{-- Destination ticker floating over the bottom of the hero --}}
    @if($destinations->isNotEmpty())
    <div class="absolute inset-x-0 bottom-0 marquee border-t border-white/25 bg-transparent py-4 sm:py-5" aria-label="Destinations we cover">
    {{-- Two identical halves, each repeating the list 3x so a half is always wider than the screen; the track slides -50% and loops seamlessly --}}
    <div class="marquee-track font-display text-base sm:text-xl text-white [text-shadow:0_1px_8px_rgb(0_20_30/0.6)]">
        @foreach([0, 1] as $half)
            <div class="flex shrink-0 items-center" @if($half) aria-hidden="true" @endif>
                @foreach(range(1, 3) as $rep)
                    <ul class="flex shrink-0 items-center" @if($rep > 1) aria-hidden="true" @endif>
                        @foreach($destinations as $d)
                            <li class="flex items-center">
                                <a href="{{ route('destinations.show', $d->slug) }}" class="px-4 sm:px-6 whitespace-nowrap hover:text-brand-gold transition" @if($half || $rep > 1) tabindex="-1" @endif>{{ $d->name }}</a>
                                <span class="text-brand-gold text-[0.6rem]" aria-hidden="true">&#9670;</span>
                            </li>
                        @endforeach
                    </ul>
                @endforeach
            </div>
        @endforeach
    </div>    </div>
    @endif

    <a href="#tours" class="absolute bottom-24 left-1/2 -translate-x-1/2 hidden sm:flex flex-col items-center gap-2 text-[0.65rem] font-bold uppercase tracking-[0.3em] text-white/70 hover:text-brand-gold">
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

{{-- 1b. WHY BOOK WITH US: layered photo collage + glass benefit cards (reveal on scroll) --}}
@php
    $whyImg = $galleryItems->first(fn ($g) => str_contains($g['path'], 'quedlinburg-lane')) ?? $galleryItems->first();
    $whyImg2 = $galleryItems->first(fn ($g) => str_contains($g['path'], 'cochem')) ?? $galleryItems->skip(1)->first();
@endphp
<section class="relative isolate overflow-hidden bg-brand-navy-dark text-white py-20 sm:py-28" aria-labelledby="book-title"
         x-data="{ shown: false }" x-init="const o = new IntersectionObserver(([e]) => { if (e.isIntersecting) { shown = true; o.disconnect() } }, { threshold: 0.15 }); o.observe($el)">
    <div class="pointer-events-none absolute -z-10 -top-40 -left-40 size-[34rem] rounded-full bg-brand-gold/20 blur-[120px]" aria-hidden="true"></div>
    <div class="pointer-events-none absolute -z-10 -bottom-40 -right-32 size-[30rem] rounded-full bg-brand-tangerine/15 blur-[120px]" aria-hidden="true"></div>
    <span class="pointer-events-none absolute -z-10 right-0 top-6 font-display italic text-[11rem] sm:text-[17rem] leading-none text-white/[0.04] select-none" aria-hidden="true">Germany</span>

    <div class="container-x grid gap-16 lg:gap-24 lg:grid-cols-[0.9fr_1fr] lg:items-center">
        @if($whyImg)
            <div class="relative mx-auto w-full max-w-md lg:max-w-none pb-16 pr-10 sm:pr-16 transition duration-1000 ease-out" :class="shown ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10'">
                {{-- gold offset frame --}}
                <div class="absolute left-5 top-5 right-16 bottom-11 sm:left-7 sm:top-7 rounded-t-[999px] border-2 border-brand-gold/70" aria-hidden="true"></div>
                <div class="relative aspect-[4/5] overflow-hidden rounded-t-[999px] shadow-[0_40px_80px_-30px_rgb(0_0_0/0.8)]">
                    <x-picture :path="$whyImg['path']" hires sizes="(min-width: 1024px) 40vw, 90vw" class="w-full h-full object-cover scale-105 hover:scale-110 transition duration-[1500ms]"/>
                    <div class="absolute inset-0 bg-gradient-to-t from-brand-navy-dark/50 via-transparent to-transparent" aria-hidden="true"></div>
                </div>
                @if($whyImg2)
                    <div class="why-float absolute right-0 bottom-0 w-[58%] aspect-[4/3] overflow-hidden rounded-lg border-[6px] border-brand-custard-light shadow-[0_30px_60px_-20px_rgb(0_0_0/0.7)]">
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
            <p class="eyebrow text-brand-gold">Why book with {{ $site['brand_name'] }}</p>
            <h2 id="book-title" class="mt-4 text-white">Every German journey,
                <em class="block italic font-medium text-brand-gold">feel uniquely yours.</em></h2>
            <span class="flag-rule" aria-hidden="true"></span>
            <p class="mt-5 text-base sm:text-lg text-white/85 max-w-xl">Discover Germany through journeys shaped around how you like to travel. From fairytale towns with half-timbered streets to river valleys, sandstone cliffs and the Baltic coast, every detail is planned to make your trip unforgettable.</p>

            <ol class="mt-10 grid gap-4 max-w-xl">
                @foreach([
                    ['Choose how you travel', 'Self drive, private chauffeur or small-group coach.', 'adjust'],
                    ['Beyond the usual stops', 'Quedlinburg, Cochem, Gengenbach and other places many itineraries skip.', 'pin'],
                    ['Built around you, with real support', 'Your dates, group size and budget, with a real person to reply.', 'chat'],
                ] as $i => [$t, $d, $ic])
                    <li class="group relative flex gap-5 items-start overflow-hidden rounded-xl border border-white/10 bg-white/[0.06] backdrop-blur-sm p-5 sm:p-6 transition duration-700 ease-out hover:border-brand-gold/60 hover:bg-white/[0.1] hover:-translate-y-0.5"
                        :class="shown ? 'opacity-100 translate-x-0' : 'opacity-0 translate-x-10'" style="transition-delay: {{ 250 + $i * 180 }}ms">
                        <span class="absolute -right-1 -top-4 font-display text-8xl leading-none text-white/[0.06] group-hover:text-brand-gold/20 transition" aria-hidden="true">0{{ $i + 1 }}</span>
                        <span class="grid place-items-center shrink-0 size-12 rounded-full bg-brand-gold text-brand-navy shadow-lg shadow-brand-gold/20 group-hover:scale-110 transition" aria-hidden="true"><x-icon :name="$ic" class="w-5 h-5"/></span>
                        <div class="relative">
                            <h3 class="text-white">{{ $t }}</h3>
                            <p class="mt-1 text-sm sm:text-base text-white/80">{{ $d }}</p>
                        </div>
                    </li>
                @endforeach
            </ol>

            <button type="button" class="btn btn-gold mt-10 px-10 shadow-lg shadow-brand-gold/20" @click="$store.modal.show()">Get a free quote <x-icon name="arrow-right" class="w-5 h-5"/></button>
        </div>
    </div>
</section>

{{-- 3. SIGNATURE JOURNEYS (featured packages) --}}
@if($featured->isNotEmpty())
<section class="py-16 sm:py-24" aria-labelledby="featured-title">
    <div class="container-x">
        <x-section-heading id="featured-title" eyebrow="Signature journeys" :title="$page->text('featured_title', 'Popular ways to see Germany')" emphasis="Planned around you."
            :intro="$page->text('featured_intro', 'A few ideas to start from. Every trip can be adjusted to suit you.')"/>
        <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
            @foreach($featured as $package)<x-package-card :package="$package"/>@endforeach
        </div>
        <p class="mt-12 text-center"><x-button :href="route('packages.index')" variant="outline">See all packages <x-icon name="arrow-right" class="w-5 h-5"/></x-button></p>
    </div>
</section>
@endif

{{-- 4b. FULL-BLEED STATEMENT BANNER (fixed background on desktop) --}}
@if($galleryItems->count() > 2)
<section class="relative isolate overflow-hidden text-white text-center" aria-labelledby="banner-title">
    <div class="absolute inset-0 -z-20 bg-brand-navy-dark lg:bg-fixed bg-cover bg-center"
         style="background-image:url('{{ $galleryItems[1]['media']->url() }}')" aria-hidden="true"></div>
    <x-video-bg src="banner.mp4" class="absolute inset-0 -z-[15] w-full h-full object-cover"/>
    <div class="absolute inset-0 -z-10 bg-brand-navy-dark/60" aria-hidden="true"></div>
    <div class="container-x max-w-3xl py-28 sm:py-40">
        <p class="eyebrow centered text-brand-gold">Germany, your way</p>
        <h2 id="banner-title" class="mt-4 text-white">Half-timbered streets, castle hills and quiet coastlines.</h2>
        <span class="flag-rule centered"></span>
        <p class="mt-5 text-base sm:text-lg text-white/85">Tell us how you like to travel and we will shape the journey around you.</p>
        <button type="button" class="btn btn-gold mt-9" @click="$store.modal.show()">Plan my trip</button>
    </div>
</section>
@endif

{{-- 5. TRAVEL YOUR WAY (tour types) --}}
<section class="py-16 sm:py-24" aria-labelledby="tours-title" id="tours">
    <div class="container-x">
        <x-section-heading id="tours-title" eyebrow="Travel your way" :title="$page->text('tours_title', 'See Germany differently')"
            :intro="$page->text('tours_intro', 'The same beautiful places, three very different experiences.')"/>
        <div class="grid gap-8 md:grid-cols-3">
            @foreach($tourTypes as $t)
                <article class="card flex flex-col">
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

{{-- 6. OUR DIFFERENCE (dark, split layout) --}}
<section class="py-16 sm:py-24 bg-brand-navy text-white" aria-labelledby="why-title">
    <div class="container-x grid gap-14 lg:grid-cols-[0.8fr_1.2fr]">
        <div>
            <p class="eyebrow text-brand-gold">Why {{ $site['brand_name'] }}</p>
            <h2 id="why-title" class="mt-4 text-white">{{ $page->text('why_title', 'Why travelers choose us') }}</h2>
            <span class="flag-rule" aria-hidden="true"></span>
            <p class="mt-5 text-base sm:text-lg text-white/85">{{ $page->text('why_intro', 'Germany is easier to enjoy when the planning is done for you.') }}</p>
            <button type="button" class="btn btn-gold mt-8" @click="$store.modal.show()">Start planning</button>
        </div>
        <ul class="grid gap-x-10 gap-y-8 sm:grid-cols-2">
            @foreach([
                ['adjust', 'Choose how you travel', 'Drive yourself, relax with a private driver, or join a small-group coach tour.'],
                ['pin', 'Beyond the usual stops', 'Quedlinburg, Saxon Switzerland, Cochem, Hiddensee and Gengenbach: towns and landscapes many itineraries skip.'],
                ['check', 'Built around you', 'Tell us your dates, group size and budget and we shape the trip to fit.'],
                ['chat', 'A real person replies', 'Send your details and a member of the team follows up with your quote.'],
            ] as [$ic, $t, $d])
                <li class="border-t border-white/25 pt-5">
                    <span class="grid place-items-center w-11 h-11 rounded-full border border-brand-gold text-brand-gold"><x-icon :name="$ic" class="w-6 h-6"/></span>
                    <h3 class="mt-4">{{ $t }}</h3>
                    <p class="mt-2 text-white/80">{{ $d }}</p>
                </li>
            @endforeach
        </ul>
    </div>
</section>

{{-- 6b. BY TRAVELER --}}
<section class="py-16 sm:py-24 bg-brand-custard-light" aria-labelledby="trav-title">
    <div class="container-x">
        <x-section-heading id="trav-title" eyebrow="By traveler" title="Journeys for every" emphasis="travel style."
            intro="Whoever is coming along, we shape the trip to suit."/>
        <div class="grid gap-px bg-brand-navy/15 border border-brand-navy/15 rounded-md overflow-hidden sm:grid-cols-2 lg:grid-cols-3">
            @foreach($travelers as [$tt, $td])
                <button type="button" class="group text-left bg-white p-8 sm:p-10 hover:bg-brand-navy hover:text-white transition-colors duration-300"
                        data-title="Get a quote: {{ $tt }}" data-prefill="Trip style: {{ $tt }}."
                        @click="$store.modal.show({form:'package_quote', ...$el.dataset})">
                    <span class="font-display text-3xl text-brand-red group-hover:text-brand-gold">{{ sprintf('%02d', $loop->iteration) }}</span>
                    <span class="block font-display text-3xl mt-3">{{ $tt }}</span>
                    <span class="block mt-2 text-ink-soft group-hover:text-white/80">{{ $td }}</span>
                    <span class="mt-5 inline-flex items-center gap-2 text-xs font-bold uppercase tracking-[0.18em] text-brand-red-dark group-hover:text-brand-gold">Plan this trip <x-icon name="arrow-right" class="w-4 h-4"/></span>
                </button>
            @endforeach
        </div>
    </div>
</section>

{{-- 7. HOW IT WORKS --}}
<section class="py-16 sm:py-24" aria-labelledby="how-title">
    <div class="container-x">
        <x-section-heading id="how-title" eyebrow="How it works" :title="$page->text('how_title', 'Your trip in three simple steps')"/>
        <ol class="grid gap-10 md:grid-cols-3">
            @foreach([['Tell us what you want', 'Answer a few quick questions about where, when and how you like to travel. It takes under 30 seconds.'], ['Get your tailored quote', 'We put together a quote that fits your dates, group size and budget.'], ['Book and go', 'Happy with the plan? Confirm it and we handle the details so you can enjoy Germany.']] as $n => [$t, $d])
                <li class="border-t border-brand-navy/20 pt-6">
                    <span class="step-num" aria-hidden="true">{{ sprintf('%02d', $n + 1) }}</span>
                    <h3 class="mt-2">{{ $t }}</h3>
                    <p class="mt-2 text-ink-soft">{{ $d }}</p>
                </li>
            @endforeach
        </ol>
    </div>
</section>

{{-- 8. TESTIMONIALS (only shows when at least one is active; use real, permissioned reviews with a source) --}}
@if($testimonials->isNotEmpty())
@php
    $tCount = $testimonials->count();
    $tGrid = $tCount === 1 ? 'max-w-2xl mx-auto' : ($tCount === 2 ? 'md:grid-cols-2 max-w-4xl mx-auto' : 'md:grid-cols-2 lg:grid-cols-3');
@endphp
<section class="relative overflow-hidden py-16 sm:py-24 bg-brand-custard-light" aria-labelledby="reviews-title">
    <span class="pointer-events-none absolute -top-10 left-4 font-display text-[22rem] leading-none text-brand-gold/20 select-none" aria-hidden="true">&ldquo;</span>
    <div class="container-x relative">
        <x-section-heading id="reviews-title" eyebrow="Guest reviews" :title="$page->text('testimonials_title', 'What travelers say')"/>
        <div class="grid gap-6 lg:gap-8 {{ $tGrid }}">
            @foreach($testimonials as $r)
                @php $initials = \Illuminate\Support\Str::of($r->name)->replaceMatches('/[^\p{L}\s]/u', '')->explode(' ')->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode(''); @endphp
                <figure class="group relative flex flex-col bg-white rounded-[var(--radius-card)] p-7 sm:p-8 shadow-card border-t-4 border-brand-gold transition duration-300 hover:-translate-y-1 hover:shadow-xl">
                    <span class="font-display text-6xl leading-none text-brand-gold h-8" aria-hidden="true">&ldquo;</span>
                    @if($r->is_placeholder)<p class="mb-2 self-start bg-brand-gold text-brand-navy text-xs font-extrabold px-2 py-0.5 rounded">PLACEHOLDER</p>@endif
                    <blockquote class="mt-3 flex-1 font-display text-xl sm:text-2xl leading-snug text-brand-navy">{{ $r->quote }}</blockquote>
                    <figcaption class="mt-6 flex items-center gap-4 border-t border-brand-custard pt-5">
                        <span class="grid place-items-center size-12 shrink-0 rounded-full bg-brand-navy text-brand-gold font-bold text-sm tracking-wide" aria-hidden="true">{{ $initials }}</span>
                        <span class="min-w-0">
                            <span class="block font-bold text-brand-navy">{{ $r->name }}</span>
                            @if($r->location)<span class="block text-sm text-ink-soft">{{ $r->location }}</span>@endif
                            @if($r->source)<span class="mt-1.5 inline-block rounded-full bg-brand-custard-light px-2.5 py-0.5 text-xs font-semibold text-ink-soft">{{ $r->source }}</span>@endif
                        </span>
                    </figcaption>
                </figure>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- 9. GALLERY: mosaic of full-resolution photos, click to open a lightbox --}}
@if($galleryItems->count() >= 4)
<section class="py-16 sm:py-24 bg-brand-navy-dark text-white" aria-labelledby="gallery-title"
         x-data="{ open: false, src: '', cap: '', alt: '' }" @keydown.escape.window="open = false">
    <div class="container-x">
        <x-section-heading id="gallery-title" light eyebrow="Gallery" title="Beyond" emphasis="the postcard." intro="Castles, cliffs, riverside villages and half-timbered streets. Tap a photo to see it full size."/>
        <div class="grid grid-cols-2 md:grid-cols-4 grid-flow-dense auto-rows-[11rem] sm:auto-rows-[14rem] lg:auto-rows-[17rem] gap-2 sm:gap-3">
            @foreach($galleryItems as $g)
                <button type="button" class="group relative overflow-hidden rounded-md {{ $gallerySpans[$loop->index] ?? '' }}"
                        data-src="{{ $g['media']->url() }}" data-cap="{{ $g['label'] }}" data-alt="{{ $g['media']->alt }}"
                        @click="src = $el.dataset.src; cap = $el.dataset.cap; alt = $el.dataset.alt; open = true"
                        aria-label="View {{ $g['label'] }} photo full size">
                    <x-picture :path="$g['path']" hires sizes="(min-width: 1024px) 50vw, 100vw" class="absolute inset-0 w-full h-full object-cover transition duration-[1200ms] group-hover:scale-105"/>
                    <span class="absolute inset-0 bg-gradient-to-t from-brand-navy-dark/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 group-focus-visible:opacity-100 transition-opacity duration-500" aria-hidden="true"></span>
                    <span class="absolute left-4 bottom-3 font-display text-2xl text-white opacity-0 translate-y-2 group-hover:opacity-100 group-hover:translate-y-0 group-focus-visible:opacity-100 transition duration-500">{{ $g['label'] }}</span>
                </button>
            @endforeach
        </div>
    </div>

    {{-- Lightbox --}}
    <div x-show="open" x-cloak x-transition.opacity class="fixed inset-0 z-[60] bg-brand-navy-dark/95 grid place-items-center p-4"
         role="dialog" aria-modal="true" aria-label="Photo viewer" @click.self="open = false">
        <button type="button" class="absolute top-4 right-4 p-2 text-white hover:text-brand-gold" @click="open = false"><span class="sr-only">Close</span><x-icon name="x" class="w-8 h-8"/></button>
        <figure class="max-w-6xl w-full">
            <img :src="src" :alt="alt" class="w-full max-h-[82vh] object-contain rounded">
            <figcaption class="mt-3 text-center font-display text-2xl" x-text="cap"></figcaption>
        </figure>
    </div>
</section>
@endif

{{-- 10. OUR STORY teaser --}}
<section class="py-16 sm:py-24 bg-brand-custard-light" aria-labelledby="story-title">
    <div class="container-x grid gap-12 lg:grid-cols-2 lg:items-center">
        <div class="rounded-[var(--radius-card)] overflow-hidden aspect-[4/3] shadow-card">
            <x-picture :path="$destinations->first()?->image ?? $heroPath" sizes="(min-width: 1024px) 36rem, 92vw" class="w-full h-full object-cover"/>
        </div>
        <div>
            <p class="eyebrow text-brand-red-dark">Get to know us</p>
            <h2 id="story-title" class="mt-3">Plan with peace of mind</h2>
            <p class="mt-5 text-lg text-ink-soft">Thoughtfully crafted journeys across Germany, shaped by your travel style. Tell us what you have in mind and a member of the team will be in touch with your quote.</p>
            <div class="mt-8 flex flex-wrap gap-3">
                <x-button :href="route('about')" variant="outline">About us</x-button>
                <x-button :href="route('contact')">Contact us</x-button>
            </div>
        </div>
    </div>
</section>

{{-- 11. FAQ --}}
@if($faqs->isNotEmpty())
<section class="py-16 sm:py-24" aria-labelledby="faq-title" id="faq">
    <div class="container-x max-w-3xl">
        <x-section-heading id="faq-title" eyebrow="FAQ" :title="$page->text('faq_title', 'Frequently asked questions')"/>
        <div class="space-y-3">
            @foreach($faqs as $faq)<x-faq-item :question="$faq->question" :open="$loop->first">{{ $faq->answer }}</x-faq-item>@endforeach
        </div>
    </div>
</section>
@endif

{{-- 11b. JOURNAL --}}
@if($posts->isNotEmpty())
<section class="py-16 sm:py-24 bg-brand-custard-light" aria-labelledby="journal-title">
    <div class="container-x">
        <x-section-heading id="journal-title" eyebrow="Travel journal" title="Ideas for planning" emphasis="your Germany trip."/>
        <div class="grid gap-8 md:grid-cols-3">
            @foreach($posts as $post)
                <article class="card flex flex-col p-8">
                    <p class="eyebrow text-brand-red-dark">Guide {{ sprintf('%02d', $loop->iteration) }}</p>
                    <h3 class="mt-3"><a class="hover:text-brand-red transition" href="{{ route('blog.show', $post->slug) }}">{{ $post->title }}</a></h3>
                    <p class="mt-3 text-ink-soft flex-1">{{ $post->excerpt }}</p>
                    <a class="mt-6 inline-flex items-center gap-2 text-xs font-bold uppercase tracking-[0.18em] text-brand-red-dark" href="{{ route('blog.show', $post->slug) }}">Read more <x-icon name="arrow-right" class="w-4 h-4"/><span class="sr-only">: {{ $post->title }}</span></a>
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
