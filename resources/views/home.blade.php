@extends('layouts.app')

@php
    // Layout and section order copied from goscottishtours.com, using this site's colours, fonts and components.
    $heroPath = $page->data['hero_image'] ?? null;
    $heroMedia = \App\Models\Media::forPath($heroPath);
    $byType = $packages->groupBy(fn ($p) => $p->tourType->slug);
    $ebMonth = now()->addMonthsNoOverflow(3)->format('F Y');
    $icons = ['self-drive' => 'car', 'chauffeur-driven' => 'chauffeur', 'coach-tours' => 'bus'];
    $media = fn (string $stem) => \App\Models\Media::byStem($stem);   // one shared query instead of one per photo
    $destCount = \App\Models\Destination::active()->count();
    $places = [
        ['quedlinburg-lane', 'Quedlinburg'], ['neuschwanstein-castle', 'Neuschwanstein'], ['cochem-mosel', 'Cochem'],
        ['saxon-switzerland-bastei', 'Saxon Switzerland'], ['hiddensee-beach', 'Hiddensee'], ['bacharach-rhine', 'Bacharach'],
    ];
@endphp

{{-- the hero is bright, so the header starts solid white instead of transparent --}}
@section('solid_header', '1')

@section('content')
{{-- 1. HERO (fairytale: bright, warm). The previous dark hero is kept in partials/home/hero-dark.blade.php. --}}
@include('partials.home.hero-fairytale')

{{-- 2. PROMISES (dark band, four columns divided by thin lines) + at-a-glance counts --}}
<section class="hidden bg-brand-navy-dark text-white sm:block" aria-label="Why book with us" data-reveal="up">
    <ul class="mx-auto grid max-w-[1390px] sm:grid-cols-2 lg:grid-cols-4 lg:divide-x lg:divide-white/15" data-reveal-stagger>
        @foreach([
            ['No hidden fees', 'What you see is what you pay. No surprises, just great value.'],
            ['Clear, honest pricing', 'Know what is included before you decide.'],
            ['Locally crafted itineraries', 'Designed around the places you want to see.'],
            ['Real support', 'A member of the team replies to you.'],
        ] as [$t, $d])
            <li class="px-8 py-8 text-center lg:py-10">
                <h3 class="text-lg text-brand-gold">{{ $t }}</h3>
                <p class="mt-2 text-sm text-white/80">{{ $d }}</p>
            </li>
        @endforeach
    </ul>
</section>

{{-- 3. ABOUT INTRO (soft tinted, centred) --}}
@php $aboutImg = $media('couple-rhine-vineyard') ?? $media('quedlinburg-lane'); @endphp
<section class="bg-brand-cream py-16 sm:py-24" aria-labelledby="intro-title" data-reveal="split">
    <div class="container-x grid items-center gap-10 lg:grid-cols-2 lg:gap-20">
        @if($aboutImg)
            <div class="relative aspect-[4/3] w-full overflow-hidden rounded-2xl shadow-card sm:aspect-[5/4] lg:aspect-auto lg:h-full lg:min-h-[34rem]">
                <x-picture :path="$aboutImg->path" hires sizes="(min-width: 1536px) 1000px, (min-width: 1024px) 50vw, 100vw" class="absolute inset-0 h-full w-full object-cover"/>
            </div>
        @endif
        <div class="text-center lg:text-left">
            <p class="eyebrow text-brand-red-dark after:h-px after:w-7 after:bg-current after:content-[''] lg:after:hidden">Discover Germany</p>
            <h2 id="intro-title" class="mt-4">A <em class="font-medium italic text-brand-red">Germany trip</em>
                <span class="block">that is truly yours</span></h2>
            <span class="flag-rule max-lg:mx-auto" aria-hidden="true"></span>
            <p class="mt-6 text-base text-ink-soft sm:text-lg">Expertly planned Germany vacation packages, including self-drive, chauffeur-driven and coach tours.</p>
            <p class="mt-4 text-base text-ink-soft sm:text-lg">Perfect for families, couples, friends and special occasions, with every detail shaped around how you like to travel.</p>
            <button type="button" class="btn btn-primary mt-8 px-9" @click="$store.modal.show()">Get instant quote <x-icon name="arrow-right" class="h-5 w-5"/></button>
        </div>
    </div>
</section>

{{-- 4. SIGNATURE VACATION: three cards (icon, tagline, summary, three ticks, three buttons) --}}
<section class="bg-white py-14 sm:py-20" aria-labelledby="sig-title" id="tours" data-reveal="rise">
    <div class="container-x">
        <div class="mx-auto mb-12 max-w-3xl text-center">
            <p class="eyebrow centered text-brand-red-dark">Signature vacation</p>
            <h2 id="sig-title" class="mt-4">Our most-loved Germany tours</h2>
            <span class="flag-rule centered" aria-hidden="true"></span>
            <p class="mt-5 text-base text-ink-soft sm:text-lg">Choose the travel style that suits you. Each is designed for comfort, ease and memorable experiences.</p>
        </div>
        <div class="-mx-4 flex snap-x snap-mandatory gap-4 overflow-x-auto px-4 pb-4 [scrollbar-width:none] md:mx-0 md:grid md:grid-cols-3 md:gap-8 md:overflow-visible md:px-0 md:pb-0 [&::-webkit-scrollbar]:hidden" data-carousel="md" aria-label="Ways to travel" data-reveal-stagger>
            @foreach($tourTypes as $t)
                @php $incl = array_slice(array_values(array_filter(array_map('trim', preg_split('/\R/', (string) $t->inclusions)))), 0, 3); @endphp
                {{-- photo card: photo fills the card, dark fade, icon + title + copy over it --}}
                <article class="group relative isolate flex min-h-[30rem] w-[82%] shrink-0 snap-center flex-col md:w-auto md:shrink justify-end overflow-hidden rounded-2xl bg-brand-navy-dark text-white shadow-card">
                    <x-picture :path="$t->image" sizes="(min-width: 768px) 24rem, 82vw" class="absolute inset-0 -z-20 h-full w-full object-cover transition duration-[1200ms] group-hover:scale-105"/>
                    <div class="absolute inset-0 -z-10 bg-gradient-to-t from-brand-navy-dark via-brand-navy-dark/75 to-brand-navy-dark/10" aria-hidden="true"></div>
                    <div class="p-6 pt-40">
                        <div class="flex items-center gap-3">
                            <span class="grid size-10 shrink-0 place-items-center rounded-full bg-brand-gold text-brand-navy" aria-hidden="true"><x-icon :name="$icons[$t->slug] ?? 'pin'" class="h-5 w-5"/></span>
                            <h3 class="text-white"><a href="{{ route('tours.show', $t->slug) }}" class="hover:underline">{{ $t->name }}</a></h3>
                        </div>
                        <p class="mt-3 text-sm text-white/85">{{ $t->summary }}</p>
                        @if($incl)
                            <ul class="mt-4 space-y-1.5 text-sm font-semibold">
                                @foreach($incl as $i)<li class="flex items-start gap-2"><x-icon name="check" class="mt-0.5 h-4 w-4 shrink-0 text-brand-gold"/>{{ $i }}</li>@endforeach
                            </ul>
                        @endif
                        <a href="{{ route('tours.show', $t->slug) }}" class="mt-4 inline-flex items-center gap-1 text-xs font-bold uppercase tracking-[0.16em] text-brand-gold hover:underline">Explore {{ $t->name }} <x-icon name="arrow-right" class="h-4 w-4"/></a>
                        <div class="mt-5 grid grid-cols-2 gap-2">
                            <x-button size="sm" data-title="Get a quote: {{ $t->name }}" data-prefill="Interested in: {{ $t->name }} tours."
                                      @click="$store.modal.show({form:'package_quote', ...$el.dataset})">Get a quote</x-button>
                            <x-button variant="outline-light" size="sm" data-title="Ask us a question" data-subtitle="{{ $t->name }} tours" data-prefill="Question about {{ $t->name }} tours: "
                                      @click="$store.modal.show({form:'package_question', ...$el.dataset})">Ask us a question</x-button>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>

{{-- 5. DISCOUNT BLOCK (white to tint to white); uses our real early bird offer --}}
<section class="bg-gradient-to-b from-white via-brand-cream to-white py-10 sm:py-24" aria-labelledby="offer-title" data-reveal="pop">
    <div class="container-x grid items-center gap-5 text-center sm:gap-8 lg:gap-14 lg:text-left lg:grid-cols-[auto_1fr_minmax(0,0.8fr)]">
        <div class="mx-auto hidden size-36 place-items-center rounded-full bg-brand-gold text-brand-navy shadow-xl shadow-brand-navy/20 sm:grid sm:size-44 lg:mx-0" aria-hidden="true">
            <span class="text-center font-display font-extrabold leading-none"><span class="block text-6xl">25%</span><span class="mt-1 block text-sm uppercase tracking-[0.2em]">off</span></span>
        </div>
        <div>
            {{-- phones: a compact pill instead of the big badge --}}
            <span class="mb-4 inline-flex items-center gap-2 rounded-full bg-brand-gold px-5 py-2 font-display font-extrabold leading-none text-brand-navy shadow-lg shadow-brand-navy/15 sm:hidden" aria-hidden="true"><span class="text-2xl">25%</span><span class="text-xs uppercase tracking-[0.2em]">off</span></span>
            <p class="eyebrow block text-brand-red-dark sm:inline-flex">Early bird offer</p>
            <h2 id="offer-title" class="mt-3 sm:mt-4">Start planning &amp; claim your discount.</h2>
            <p class="mt-4 max-w-2xl text-base text-ink-soft sm:text-lg">Plan ahead and book early. Ask for a free, no-obligation quote for {{ $ebMonth }} and we will include your early bird discount.</p>
            <ul class="mt-4 flex flex-wrap justify-center gap-2 sm:mt-5 sm:gap-3 lg:justify-start">
                @foreach(['Free consultation', 'No-obligation quote', 'Itineraries built around you'] as $chip)
                    <li class="rounded-full border border-brand-navy/15 bg-white px-3 py-1 text-[0.65rem] font-bold uppercase tracking-wider text-brand-navy max-sm:last:hidden sm:px-4 sm:py-1.5 sm:text-xs">{{ $chip }}</li>
                @endforeach
            </ul>
            <x-button class="mt-5 w-full sm:mt-7 sm:w-auto" data-title="Early bird offer: {{ $ebMonth }}" data-prefill="Early bird offer: 25% off, travelling in {{ $ebMonth }}."
                      @click="$store.modal.show({form:'package_quote', ...$el.dataset})">Claim your discount <x-icon name="arrow-right" class="h-5 w-5"/></x-button>
        </div>
        {{-- Germany symbol: dashed map with route on a navy panel, gold offset frame, floating tag --}}
        <div class="relative mx-auto hidden w-full max-w-sm pb-6 pr-6 md:block lg:max-w-none">
            <span class="absolute bottom-0 right-0 top-6 left-6 rounded-2xl border-2 border-brand-gold/70" aria-hidden="true"></span>
            <div class="relative isolate grid aspect-[4/3] place-items-center overflow-hidden rounded-2xl bg-brand-navy-dark shadow-card lg:aspect-[5/4]" aria-hidden="true">
                <span class="absolute -z-10 size-72 rounded-full bg-brand-gold/20 blur-[90px]"></span>
                <x-germany-map class="h-[88%] w-auto"/>
            </div>
            <div class="absolute -left-4 bottom-12 flex items-center gap-3 rounded-full bg-brand-navy py-2 pl-2 pr-5 text-white shadow-xl">
                <span class="grid size-9 place-items-center rounded-full bg-brand-gold text-brand-navy"><x-icon name="pin" class="h-4 w-4"/></span>
                <span class="text-xs font-extrabold uppercase leading-tight tracking-wider">Travelling in<br>{{ $ebMonth }}</span>
            </div>
        </div>
    </div>
</section>

{{-- 6. PACKAGES PER TRAVEL STYLE --}}
@foreach($tourTypes as $t)
    @php $all = $byType[$t->slug] ?? collect(); $list = $all->take(6); @endphp
    @continue($list->isEmpty())
    <section class="bg-white py-12 sm:py-16" aria-labelledby="pk-{{ $t->slug }}">
        <div class="container-x">
            {{-- heading left, count right --}}
            <div class="mb-8 flex items-end justify-between gap-6">
                <div>
                    <p class="eyebrow flex items-center gap-2 text-brand-red-dark"><x-icon :name="$icons[$t->slug] ?? 'pin'" class="h-5 w-5"/>{{ $t->name }}</p>
                    <h2 id="pk-{{ $t->slug }}" class="mt-3">{{ $t->name }} <em class="font-medium italic text-brand-red">packages</em></h2>
                </div>
                <dl class="flex shrink-0 flex-col-reverse text-center"><dt class="text-[0.65rem] font-bold uppercase tracking-[0.16em] text-ink-soft">Itineraries</dt><dd class="font-display text-4xl text-brand-navy">{{ $all->count() }}</dd></dl>
            </div>
            {{-- navy cards, swipe row --}}
            <div class="-mx-4 flex snap-x snap-mandatory gap-5 overflow-x-auto px-4 pb-4 [scrollbar-width:none] sm:mx-0 sm:px-0 md:grid md:grid-cols-3 md:overflow-visible [&::-webkit-scrollbar]:hidden" data-carousel="md" aria-label="{{ $t->name }} packages" data-reveal-stagger>
                @foreach($list as $package)
                    <article class="flex w-[82%] shrink-0 snap-center flex-col overflow-hidden rounded-2xl bg-brand-navy text-white shadow-card sm:w-[22rem] md:w-auto md:shrink">
                        <a href="{{ $package->url() }}" class="relative block aspect-[3/2] overflow-hidden bg-brand-navy-dark" tabindex="-1" aria-hidden="true">
                            <x-picture :path="$package->coverPath()" sizes="(min-width: 1024px) 22rem, 82vw" class="h-full w-full object-cover"/>
                            <div class="absolute inset-0 bg-gradient-to-t from-brand-navy/80 to-transparent" aria-hidden="true"></div>
                            @if($package->is_sample)<span class="absolute left-3 top-3 rounded-full bg-brand-gold px-2.5 py-1 text-xs font-extrabold text-brand-navy">SAMPLE</span>@endif
                            <span class="absolute bottom-3 left-4 text-xs font-bold uppercase tracking-wider text-brand-gold">{{ $package->destination->name }} &middot; {{ $package->nights }} nights</span>
                        </a>
                        <div class="flex flex-1 flex-col p-5">
                            <h3 class="text-lg leading-snug text-white"><a href="{{ $package->url() }}" class="hover:underline">{{ $package->title }}</a></h3>
                            <p class="mt-2 flex-1 text-sm text-white/80">{{ $package->summary }}</p>
                            <p class="mt-4 flex items-baseline gap-3 border-t border-white/15 pt-4 font-sans text-2xl font-extrabold">
                                @if($package->hasSale())<s class="text-sm font-semibold text-white/60">{{ $package->wasLabel() }}</s>@endif{{ $package->priceLabel() }}
                                @if($package->is_sample)<span class="text-xs font-normal text-white/70">sample price</span>@endif
                            </p>
                            <div class="mt-4 grid grid-cols-2 gap-2">
                                <x-button size="sm" data-title="Get a quote" data-subtitle="{{ $package->title }}"
                                          @click="$store.modal.show({form:'package_quote', packageId: {{ $package->id }}, ...$el.dataset})">Get a quote</x-button>
                                <x-button :href="$package->url()" variant="outline-light" size="sm">View details</x-button>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
@endforeach

{{-- 7. "START YOUR ADVENTURE" photo band with counters --}}
@php $cta = $media('neuschwanstein-castle'); @endphp
<section class="relative isolate overflow-hidden bg-brand-navy-dark py-16 text-center text-white sm:py-24" aria-labelledby="adv-title" data-reveal="zoom">
    @if($cta)<x-picture :path="$cta->path" hires sizes="100vw" class="absolute inset-0 -z-20 h-full w-full object-cover"/>@endif
    <div class="absolute inset-0 -z-10 bg-brand-navy-dark/70" aria-hidden="true"></div>
    <div class="container-x max-w-4xl">
        <p class="eyebrow centered text-brand-gold">Germany · Your next adventure awaits</p>
        <h2 id="adv-title" class="mt-4 text-white">Start your Germany adventure
            <em class="block font-medium italic text-brand-gold">Book your dream tour today!</em></h2>
        <dl class="mx-auto mt-10 grid max-w-2xl grid-cols-3 gap-3 sm:gap-4">
            @foreach([[$destCount, 'Destinations'], [$packages->count(), 'Germany tours'], [$tourTypes->count(), 'Ways to travel']] as [$n, $l])
                <div class="flex flex-col-reverse rounded-xl border border-white/25 bg-white/10 px-2 py-5 backdrop-blur"><dt class="mt-2 text-[0.65rem] font-bold uppercase tracking-[0.16em] text-white/85 sm:text-xs">{{ $l }}</dt><dd class="font-display text-4xl text-brand-gold sm:text-5xl">{{ $n }}</dd></div>
            @endforeach
        </dl>
        <button type="button" class="btn btn-primary mt-10 px-10 shadow-lg shadow-black/30" @click="$store.modal.show()">Get quote now! <x-icon name="arrow-right" class="h-5 w-5"/></button>
    </div>
</section>

{{-- 8. TRAVEL WITH CONFIDENCE: copy left, four cards right --}}
<section class="bg-white py-16 sm:py-24" aria-labelledby="conf-title" data-reveal="split">
    <div class="container-x grid gap-12 lg:grid-cols-2 lg:gap-20">
        <div class="text-center lg:text-left">
            <p class="eyebrow text-brand-red-dark after:h-px after:w-7 after:bg-current after:content-[''] lg:after:hidden">Travel with confidence</p>
            <h2 id="conf-title" class="mt-4">Discover Germany,
                <em class="block font-medium italic text-brand-red">stress-free &amp; unforgettable.</em></h2>
            <span class="flag-rule max-lg:mx-auto" aria-hidden="true"></span>
            <p class="mt-5 text-base text-ink-soft sm:text-lg">Everything is taken care of, from local expertise and comfortable transport to carefully selected accommodation and seamless daily planning. Simply relax and enjoy the journey.</p>
            <p class="mt-4 text-base text-ink-soft sm:text-lg">Experience fairytale towns, river valleys and hidden gems without the hassle of organising every detail yourself.</p>
            <button type="button" class="btn btn-primary mt-8 px-9" @click="$store.modal.show()">Start planning your Germany adventure <x-icon name="arrow-right" class="h-5 w-5"/></button>
        </div>
        <ul class="-mx-4 flex snap-x snap-mandatory gap-4 overflow-x-auto px-4 py-2 [scrollbar-width:none] sm:mx-0 sm:grid sm:grid-cols-2 sm:overflow-visible sm:px-0 sm:py-0 [&::-webkit-scrollbar]:hidden" data-carousel="sm" aria-label="Why travel with confidence" data-reveal-stagger>
            @foreach([
                ['shield', 'Peace of mind', 'Carefully planned routes, with support when you need it.'],
                ['car', 'Your pace, your way', 'Self drive, chauffeur or coach: you choose how you travel.'],
                ['adjust', 'Guided, yet unrushed', 'A structured journey with flexibility built in.'],
                ['check', 'Seamless travel', 'Quality stays and smooth transfers throughout.'],
            ] as $i => [$ic, $t, $d])
                <li class="w-[82%] shrink-0 snap-center rounded-xl border border-brand-navy/10 bg-brand-custard-light p-6 transition duration-700 ease-out hover:-translate-y-0.5 hover:border-brand-gold hover:shadow-xl sm:w-auto sm:shrink"
                   >
                    <span class="grid size-12 place-items-center rounded-full bg-brand-gold text-brand-navy shadow-lg shadow-brand-gold/30"><x-icon :name="$ic" class="h-5 w-5"/></span>
                    <h3 class="mt-4 text-lg">{{ $t }}</h3>
                    <p class="mt-2 text-sm text-ink-soft">{{ $d }}</p>
                </li>
            @endforeach
        </ul>
    </div>
</section>

{{-- 9. REVIEWS (shared home section; only shows real testimonials) --}}
<div data-reveal="fade">@include('partials.sections.reviews')</div>

{{-- 10. BEGIN YOUR JOURNEY: strip of places --}}
<section class="bg-white py-14 sm:py-20" aria-labelledby="places-title" data-reveal="blur">
    <div class="container-x mb-10 max-w-3xl text-center">
        <p class="eyebrow centered text-brand-red-dark">Begin your journey</p>
        <h2 id="places-title" class="mt-4">Discover the best of Germany</h2>
        <span class="flag-rule centered" aria-hidden="true"></span>
        <p class="mt-5 text-base text-ink-soft sm:text-lg">A closer look at the landscapes, culture and moments that await on our journeys.</p>
    </div>
    {{-- slow endless scroll: the row is rendered twice and slides left by exactly one copy, then loops seamlessly. Pauses on hover; static and swipeable when reduced motion is on. --}}
    <style>
        .places-track{display:flex;width:max-content;animation:places-scroll 80s linear infinite}
        .places-marquee:hover .places-track,.places-marquee:focus-within .places-track{animation-play-state:paused}
        @keyframes places-scroll{to{transform:translateX(-50%)}}
        @media (prefers-reduced-motion:reduce){.places-marquee{overflow-x:auto}.places-track{animation:none}.places-track>[aria-hidden=true]{display:none}}
    </style>
    <div class="places-marquee overflow-hidden" role="region" aria-label="Places in Germany">
        <div class="places-track">
            @foreach([false, true] as $dup)
                <div class="flex shrink-0" @if($dup) aria-hidden="true" @endif>
                    @foreach($places as [$stem, $label])
                        @php $m = $media($stem); @endphp
                        @continue(! $m)
                        <figure class="group relative h-72 w-[68vw] shrink-0 overflow-hidden sm:w-[20rem] md:h-80 md:w-[22rem]">
                            <x-picture :path="$m->path" sizes="(min-width: 768px) 22rem, 68vw" class="h-full w-full object-cover transition duration-[1200ms] group-hover:scale-105"/>
                            <div class="absolute inset-0 bg-gradient-to-t from-brand-navy-dark/85 via-transparent to-transparent" aria-hidden="true"></div>
                            <figcaption class="absolute inset-x-0 bottom-0 p-5 font-display text-2xl text-white">{{ $label }}<span class="mt-2 block h-[3px] w-10 bg-brand-gold transition-all duration-500 group-hover:w-20" aria-hidden="true"></span></figcaption>
                        </figure>
                    @endforeach
                </div>
            @endforeach
        </div>
    </div></section>

{{-- 11. HOW IT WORKS: dark gradient, three numbered steps --}}
<section class="relative isolate overflow-hidden bg-brand-navy-dark py-16 text-white sm:py-24" aria-labelledby="how-title" data-reveal="rise">
    <div class="pointer-events-none absolute -z-10 -top-32 -left-32 size-[30rem] rounded-full bg-brand-gold/15 blur-[120px]" aria-hidden="true"></div>
    <div class="pointer-events-none absolute -z-10 -bottom-32 -right-32 size-[26rem] rounded-full bg-brand-red/20 blur-[120px]" aria-hidden="true"></div>
    <div class="container-x">
        <div class="mx-auto mb-12 max-w-3xl text-center">
            <p class="eyebrow centered text-brand-gold">How it works</p>
            <h2 id="how-title" class="mt-4 text-white">Three steps to your Germany tour.</h2>
            <span class="flag-rule centered" aria-hidden="true"></span>
            <p class="mt-5 text-base text-white/85 sm:text-lg">Enjoy a stress-free journey with every detail expertly arranged.</p>
        </div>
        <ol class="-mx-4 flex snap-x snap-mandatory gap-4 overflow-x-auto px-4 pb-2 [scrollbar-width:none] md:mx-0 md:grid md:grid-cols-3 md:gap-6 md:overflow-visible md:px-0 md:pb-0 [&::-webkit-scrollbar]:hidden" data-carousel="md" data-carousel-light aria-label="How it works" data-reveal-stagger>
            @foreach([
                ['document', 'Get your free quote', 'Share your travel preferences and a member of the team will reply with your quote.'],
                ['pin', 'Review your itinerary', 'Review your itinerary and pricing, and make any final adjustments with our team.'],
                ['shield', 'Travel with confidence', 'We organise hotels, transport and experiences, so you can simply arrive and enjoy Germany.'],
            ] as $i => [$ic, $t, $d])
                <li class="w-[82%] shrink-0 snap-center md:w-auto md:shrink rounded-2xl border border-white/15 bg-white/5 p-8 backdrop-blur">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-[0.2em] text-brand-gold">Step 0{{ $i + 1 }}/03</span>
                        <span class="grid size-12 place-items-center rounded-full bg-brand-gold text-brand-navy shadow-lg shadow-black/30" aria-hidden="true"><x-icon :name="$ic" class="h-6 w-6"/></span>
                    </div>
                    <h3 class="mt-5 text-white">{{ $t }}</h3>
                    <p class="mt-3 text-white/80">{{ $d }}</p>
                </li>
            @endforeach
        </ol>
        <p class="mt-10 text-center"><button type="button" class="btn btn-primary px-10 shadow-lg shadow-black/30" @click="$store.modal.show()">Start your Germany dream trip <x-icon name="arrow-right" class="h-5 w-5"/></button></p>
    </div>
</section>

{{-- 11b. OUR PARTNERS (shared partial; also on About) --}}
{{-- same deep navy as "How it works" above, so the two read as one dark block before the cream band --}}
<div data-reveal="fade">@include('partials.sections.partners', ['bg' => 'bg-brand-navy-dark border-t border-white/10'])</div>

{{-- 12. LET'S PLAN YOUR JOURNEY (tinted band) --}}
<section class="bg-brand-cream py-14 text-center sm:py-20" aria-labelledby="plan-title" data-reveal="zoom">
    <div class="container-x max-w-3xl">
        <p class="eyebrow centered text-brand-red-dark">Begin your journey</p>
        <h2 id="plan-title" class="mt-4">Let's plan your <em class="font-medium italic text-brand-red">Germany journey.</em></h2>
        <p class="mt-5 text-base text-ink-soft sm:text-lg">Tell us how you would like to travel and we will create a journey tailored to your interests, pace and budget.</p>
        <button type="button" class="btn btn-primary mt-8 px-9" data-title="Ask us a question" data-prefill="Question about a Germany tour: "
                @click="$store.modal.show({form:'package_question', ...$el.dataset})">Ask a question <x-icon name="arrow-right" class="h-5 w-5"/></button>
        <ul class="mt-8 flex flex-wrap items-center justify-center gap-x-8 gap-y-3 border-t border-brand-navy/10 pt-6" aria-label="What to expect">
            @foreach([['lock', 'Secure booking'], ['check', 'Free quote'], ['shield', 'No obligation']] as [$ic, $t])
                <li class="flex items-center gap-2.5 text-xs font-bold uppercase tracking-[0.16em] text-brand-navy sm:text-sm"><span class="grid size-8 shrink-0 place-items-center rounded-full bg-brand-gold text-brand-navy" aria-hidden="true"><x-icon :name="$ic" class="h-4 w-4"/></span>{{ $t }}</li>
            @endforeach
        </ul>
    </div>
</section>

{{-- 13. EARLY-BIRD BAND: photo with left-to-right navy fade --}}
@php $eb = $media('coach-tour-cochem'); @endphp
<section class="relative isolate overflow-hidden bg-brand-navy-dark py-16 text-white sm:py-24" aria-labelledby="lock-title" data-reveal="left">
    {{-- phones: photo fills the band. lg+: the whole photo (3:2) sits on the right at the band height, fading into the navy on its left, so group and bus both show without making the band taller --}}
    @if($eb)<div class="absolute inset-y-0 right-0 -z-20 w-full lg:aspect-[3/2] lg:w-auto lg:[mask-image:linear-gradient(to_right,transparent,#000_30%)]"><x-picture :path="$eb->path" hires sizes="(min-width: 1024px) 60vw, 100vw" class="h-full w-full object-cover object-[75%_100%] lg:object-center"/></div>@endif
    <div class="absolute inset-0 -z-10 bg-gradient-to-r from-brand-navy-dark/95 via-brand-navy-dark/65 to-brand-navy-dark/15 max-lg:from-brand-navy-dark/80 max-lg:via-brand-navy-dark/75 max-lg:to-brand-navy-dark/70" aria-hidden="true"></div>
    <div class="container-x">
        <div class="max-w-xl text-center max-lg:mx-auto lg:text-left">
            <p class="eyebrow text-brand-gold after:h-px after:w-7 after:bg-current after:content-[''] lg:after:hidden">Early-bird pricing</p>
            <h2 id="lock-title" class="mt-4 text-white">Lock in your next adventure.</h2>
            <span class="flag-rule max-lg:mx-auto" aria-hidden="true"></span>
            <p class="mt-5 text-base text-white/90 sm:text-lg">Secure your dream Germany journey early and experience premium, stress-free travel for considerably less.</p>
            <x-button class="mt-8" data-title="Early bird offer: {{ $ebMonth }}" data-prefill="Early bird offer: 25% off, travelling in {{ $ebMonth }}."
                      @click="$store.modal.show({form:'package_quote', ...$el.dataset})">Learn how to save up to 25% <x-icon name="arrow-right" class="h-5 w-5"/></x-button>
        </div>
    </div>
</section>
@endsection
