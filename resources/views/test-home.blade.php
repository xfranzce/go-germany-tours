@extends('layouts.app')

@php
    // Layout and section order copied from goscottishtours.com, using this site's colours, fonts and components.
    $heroPath = $page->data['hero_image'] ?? null;
    $heroMedia = \App\Models\Media::forPath($heroPath);
    $byType = $packages->groupBy(fn ($p) => $p->tourType->slug);
    $ebMonth = now()->addMonthsNoOverflow(3)->format('F Y');
    $icons = ['self-drive' => 'car', 'chauffeur-driven' => 'chauffeur', 'coach-tours' => 'bus'];
    $media = fn (string $stem) => \App\Models\Media::query()->where('path', 'like', "media/{$stem}-%")->first();
    $destCount = \App\Models\Destination::active()->count();
    $reveal = "x-data=\"{ shown: false }\" x-init=\"const o = new IntersectionObserver(([e]) => { if (e.isIntersecting) { shown = true; o.disconnect() } }, { threshold: 0.12 }); o.observe(\$el)\"";
    $places = [
        ['quedlinburg-lane', 'Quedlinburg'], ['neuschwanstein-castle', 'Neuschwanstein'], ['cochem-mosel', 'Cochem'],
        ['saxon-switzerland-bastei', 'Saxon Switzerland'], ['hiddensee-beach', 'Hiddensee'], ['bacharach-rhine', 'Bacharach'],
    ];
@endphp

@section('content')
{{-- 1. HERO: full-height photo, eyebrow, headline, intro, "complete the survey" with the quiz centred, trust row at the bottom --}}
<section class="relative isolate flex min-h-svh flex-col items-center overflow-hidden bg-brand-navy-dark text-center text-white" aria-labelledby="hero-title">
    @if($heroMedia)<x-picture :path="$heroPath" priority hires sizes="100vw" class="absolute inset-0 -z-20 h-full w-full object-cover"/>@endif
    <div class="absolute inset-0 -z-10 bg-gradient-to-b from-brand-navy-dark/85 via-brand-navy-dark/55 to-brand-navy-dark/90" aria-hidden="true"></div>
    <div class="container-x flex max-w-3xl flex-1 flex-col items-center pt-32 pb-8 sm:pt-36">
        <p class="eyebrow centered text-brand-gold">Authentic Germany experiences</p>
        <h1 id="hero-title" class="mt-5 leading-[1.08]">Find the best value vacation
            <em class="mt-2 block font-medium italic text-brand-gold">trip to Germany</em></h1>
        <p class="mt-5 hidden max-w-2xl text-base text-white/90 sm:block sm:text-lg">Discover Germany via self-drive, chauffeur-driven or small-group coach tours, planned around you with local insight and carefully chosen stays.</p>
        <p class="mt-8 font-display text-xl text-white sm:text-2xl">Complete the quiz below for your <span class="text-brand-gold">free quote</span></p>
        <div class="mt-5 w-full max-w-xl"><x-hero-quiz/></div>
    </div>
    <ul class="container-x hidden flex-wrap items-center justify-center gap-x-10 gap-y-3 border-t border-white/20 py-5 text-xs font-bold uppercase tracking-[0.16em] sm:flex">
        @foreach([['star', 'Excellent reviews'], ['lock', 'Private tours'], ['pin', 'Local Germany experts']] as [$ic, $t])
            <li class="flex items-center gap-2.5"><x-icon :name="$ic" class="h-4 w-4 text-brand-gold"/>{{ $t }}</li>
        @endforeach
    </ul>
</section>

{{-- 2. PROMISES (dark band, four columns divided by thin lines) + at-a-glance counts --}}
<section class="bg-brand-navy-dark text-white" aria-label="Why book with us">
    <ul class="mx-auto grid max-w-[1390px] sm:grid-cols-2 lg:grid-cols-4 lg:divide-x lg:divide-white/15">
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
    <ul class="border-t border-white/15 bg-brand-navy">
        <li class="mx-auto flex max-w-4xl flex-wrap items-center justify-center gap-x-12 gap-y-3 px-4 py-5 text-xs font-bold uppercase tracking-[0.16em] text-white/90">
            <span><b class="mr-2 font-display text-2xl text-brand-gold">{{ $packages->count() }}</b>Germany tours</span>
            <span><b class="mr-2 font-display text-2xl text-brand-gold">{{ $destCount }}</b>Destinations</span>
            <span><b class="mr-2 font-display text-2xl text-brand-gold">{{ $tourTypes->count() }}</b>Ways to travel</span>
        </li>
    </ul>
</section>

{{-- 3. ABOUT INTRO (soft tinted, centred) --}}
<section class="bg-brand-custard-light py-16 text-center sm:py-24" aria-labelledby="intro-title">
    <div class="container-x max-w-3xl">
        <p class="eyebrow centered text-brand-red-dark">Discover Germany</p>
        <h2 id="intro-title" class="mt-4">A Germany trip that is
            <em class="block font-medium italic text-brand-red">truly yours.</em></h2>
        <span class="flag-rule centered" aria-hidden="true"></span>
        <p class="mt-6 text-base text-ink-soft sm:text-lg">Expertly planned Germany vacation packages, including self-drive, chauffeur-driven and coach tours. Perfect for families, couples, friends and special occasions, with every detail shaped around how you like to travel.</p>
        <button type="button" class="btn btn-primary mt-8 px-9" @click="$store.modal.show()">Get instant quote <x-icon name="arrow-right" class="h-5 w-5"/></button>
    </div>
</section>

{{-- 4. SIGNATURE VACATION: three cards (icon, tagline, summary, three ticks, three buttons) --}}
<section class="bg-white py-14 sm:py-20" aria-labelledby="sig-title" id="tours">
    <div class="container-x">
        <div class="mx-auto mb-12 max-w-3xl text-center">
            <p class="eyebrow centered text-brand-red-dark">Signature vacation</p>
            <h2 id="sig-title" class="mt-4">Our most-loved Germany tours</h2>
            <span class="flag-rule centered" aria-hidden="true"></span>
            <p class="mt-5 text-base text-ink-soft sm:text-lg">Choose the travel style that suits you. Each is designed for comfort, ease and memorable experiences.</p>
        </div>
        <div class="grid gap-8 md:grid-cols-3">
            @foreach($tourTypes as $t)
                @php $incl = array_slice(array_values(array_filter(array_map('trim', preg_split('/\R/', (string) $t->inclusions)))), 0, 3); @endphp
                <article class="card flex flex-col text-center">
                    <a href="{{ route('tours.show', $t->slug) }}" class="block aspect-[4/3] overflow-hidden bg-brand-custard" tabindex="-1" aria-hidden="true">
                        <x-picture :path="$t->image" sizes="(min-width: 768px) 24rem, 92vw" class="h-full w-full object-cover transition duration-700 hover:scale-105"/>
                    </a>
                    <div class="flex flex-1 flex-col items-center p-7">
                        <span class="-mt-14 grid size-14 place-items-center rounded-full border-4 border-white bg-brand-gold text-brand-navy shadow-lg" aria-hidden="true"><x-icon :name="$icons[$t->slug] ?? 'pin'" class="h-6 w-6"/></span>
                        <h3 class="mt-4"><a href="{{ route('tours.show', $t->slug) }}" class="hover:underline">{{ $t->name }}</a></h3>
                        <p class="mt-3 text-ink-soft">{{ $t->summary }}</p>
                        @if($incl)
                            <ul class="mt-5 space-y-2 text-left text-sm font-semibold text-brand-navy">
                                @foreach($incl as $i)<li class="flex items-start gap-2"><x-icon name="check" class="mt-0.5 h-4 w-4 shrink-0 text-brand-red"/>{{ $i }}</li>@endforeach
                            </ul>
                        @endif
                        <div class="mt-auto grid w-full gap-2 pt-6">
                            <x-button :href="route('tours.show', $t->slug)" variant="outline" size="sm">More info</x-button>
                            <x-button size="sm" data-title="Get a quote: {{ $t->name }}" data-prefill="Interested in: {{ $t->name }} tours."
                                      @click="$store.modal.show({form:'package_quote', ...$el.dataset})">Get a quote</x-button>
                            <x-button variant="outline" size="sm" data-title="Ask us a question" data-subtitle="{{ $t->name }} tours" data-prefill="Question about {{ $t->name }} tours: "
                                      @click="$store.modal.show({form:'package_question', ...$el.dataset})">Ask us a question</x-button>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>

{{-- 5. DISCOUNT BLOCK (white to tint to white); uses our real early bird offer --}}
<section class="bg-gradient-to-b from-white via-brand-custard-light to-white py-16 sm:py-24" aria-labelledby="offer-title">
    <div class="container-x grid items-center gap-8 text-center lg:grid-cols-[auto_1fr] lg:gap-14 lg:text-left">
        <div class="mx-auto grid size-36 place-items-center rounded-full bg-brand-gold text-brand-navy shadow-xl shadow-brand-navy/20 sm:size-44 lg:mx-0" aria-hidden="true">
            <span class="text-center font-display font-extrabold leading-none"><span class="block text-6xl">25%</span><span class="mt-1 block text-sm uppercase tracking-[0.2em]">off</span></span>
        </div>
        <div>
            <p class="eyebrow text-brand-red-dark">Early bird offer</p>
            <h2 id="offer-title" class="mt-4">Start planning &amp; claim your discount.</h2>
            <p class="mt-4 max-w-2xl text-base text-ink-soft sm:text-lg">Plan ahead and book early. Ask for a free, no-obligation quote for {{ $ebMonth }} and we will include your early bird discount.</p>
            <ul class="mt-5 flex flex-wrap justify-center gap-3 lg:justify-start">
                @foreach(['Free consultation', 'No-obligation quote', 'Itineraries built around you'] as $chip)
                    <li class="rounded-full border border-brand-navy/15 bg-white px-4 py-1.5 text-xs font-bold uppercase tracking-wider text-brand-navy">{{ $chip }}</li>
                @endforeach
            </ul>
            <x-button class="mt-7" data-title="Early bird offer: {{ $ebMonth }}" data-prefill="Early bird offer: 25% off, travelling in {{ $ebMonth }}."
                      @click="$store.modal.show({form:'package_quote', ...$el.dataset})">Claim your discount <x-icon name="arrow-right" class="h-5 w-5"/></x-button>
        </div>
    </div>
</section>

{{-- 6. PACKAGES PER TRAVEL STYLE --}}
@foreach($tourTypes as $t)
    @php $all = $byType[$t->slug] ?? collect(); $list = $all->take(6); @endphp
    @continue($list->isEmpty())
    <section class="py-14 sm:py-20 {{ $loop->even ? 'bg-brand-custard-light' : 'bg-white' }}" aria-labelledby="pk-{{ $t->slug }}">
        <div class="container-x">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <p class="eyebrow centered flex items-center justify-center gap-2 text-brand-red-dark"><x-icon :name="$icons[$t->slug] ?? 'pin'" class="h-5 w-5"/>{{ $t->name }}</p>
                <h2 id="pk-{{ $t->slug }}" class="mt-4">{{ $t->name }} packages</h2>
                <span class="flag-rule centered" aria-hidden="true"></span>
                <p class="mt-5 text-base text-ink-soft sm:text-lg">{{ $t->summary }}</p>
                <p class="mt-6 inline-flex rounded-full bg-brand-navy px-6 py-2 text-xs font-bold uppercase tracking-[0.16em] text-white"><span class="mr-2 text-brand-gold">{{ $all->count() }}</span> itineraries</p>
            </div>
            <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                @foreach($list as $package)<x-package-card :package="$package"/>@endforeach
            </div>
            <p class="mt-12 text-center"><x-button :href="route('tours.show', $t->slug)" variant="outline">See all {{ strtolower($t->name) }} packages <x-icon name="arrow-right" class="h-5 w-5"/></x-button></p>
        </div>
    </section>
@endforeach

{{-- 7. "START YOUR ADVENTURE" photo band with counters --}}
@php $cta = $media('neuschwanstein-castle'); @endphp
<section class="relative isolate overflow-hidden bg-brand-navy-dark py-16 text-center text-white sm:py-24" aria-labelledby="adv-title">
    @if($cta)<x-picture :path="$cta->path" hires sizes="100vw" class="absolute inset-0 -z-20 h-full w-full object-cover"/>@endif
    <div class="absolute inset-0 -z-10 bg-brand-navy-dark/70" aria-hidden="true"></div>
    <div class="container-x max-w-4xl">
        <p class="eyebrow centered text-brand-gold">Germany · Your next adventure awaits</p>
        <h2 id="adv-title" class="mt-4 text-white">Start your Germany adventure
            <em class="block font-medium italic text-brand-gold">Book your dream tour today!</em></h2>
        <dl class="mt-10 grid grid-cols-3 gap-4">
            @foreach([[$destCount, 'Destinations'], [$packages->count(), 'Germany tours'], [$tourTypes->count(), 'Ways to travel']] as [$n, $l])
                <div><dd class="font-display text-5xl text-brand-gold sm:text-6xl">{{ $n }}</dd><dt class="mt-2 text-xs font-bold uppercase tracking-[0.18em] text-white/85">{{ $l }}</dt></div>
            @endforeach
        </dl>
        <button type="button" class="btn btn-primary mt-10 px-10 shadow-lg shadow-black/30" @click="$store.modal.show()">Get quote now! <x-icon name="arrow-right" class="h-5 w-5"/></button>
    </div>
</section>

{{-- 8. TRAVEL WITH CONFIDENCE: copy left, four cards right --}}
<section class="bg-white py-16 sm:py-24" aria-labelledby="conf-title" {!! $reveal !!}>
    <div class="container-x grid gap-12 lg:grid-cols-2 lg:gap-20">
        <div>
            <p class="eyebrow text-brand-red-dark">Travel with confidence</p>
            <h2 id="conf-title" class="mt-4">Discover Germany,
                <em class="block font-medium italic text-brand-red">stress-free &amp; unforgettable.</em></h2>
            <span class="flag-rule" aria-hidden="true"></span>
            <p class="mt-5 text-base text-ink-soft sm:text-lg">Everything is taken care of, from local expertise and comfortable transport to carefully selected accommodation and seamless daily planning. Simply relax and enjoy the journey.</p>
            <p class="mt-4 text-base text-ink-soft sm:text-lg">Experience fairytale towns, river valleys and hidden gems without the hassle of organising every detail yourself.</p>
            <button type="button" class="btn btn-primary mt-8 px-9" @click="$store.modal.show()">Start planning your Germany adventure <x-icon name="arrow-right" class="h-5 w-5"/></button>
        </div>
        <ul class="grid gap-4 sm:grid-cols-2">
            @foreach([
                ['shield', 'Peace of mind', 'Carefully planned routes, with support when you need it.'],
                ['car', 'Your pace, your way', 'Self drive, chauffeur or coach: you choose how you travel.'],
                ['adjust', 'Guided, yet unrushed', 'A structured journey with flexibility built in.'],
                ['check', 'Seamless travel', 'Quality stays and smooth transfers throughout.'],
            ] as $i => [$ic, $t, $d])
                <li class="rounded-xl border border-brand-navy/10 bg-brand-custard-light p-6 transition duration-700 ease-out hover:-translate-y-0.5 hover:border-brand-gold hover:shadow-xl"
                    :class="shown ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'" style="transition-delay: {{ $i * 120 }}ms">
                    <span class="grid size-12 place-items-center rounded-full bg-brand-gold text-brand-navy shadow-lg shadow-brand-gold/30"><x-icon :name="$ic" class="h-5 w-5"/></span>
                    <h3 class="mt-4 text-lg">{{ $t }}</h3>
                    <p class="mt-2 text-sm text-ink-soft">{{ $d }}</p>
                </li>
            @endforeach
        </ul>
    </div>
</section>

{{-- 9. REVIEWS (shared home section; only shows real testimonials) --}}
@include('partials.sections.reviews')

{{-- 10. BEGIN YOUR JOURNEY: strip of places --}}
<section class="bg-brand-custard-light py-14 sm:py-20" aria-labelledby="places-title">
    <div class="container-x mb-10 max-w-3xl text-center">
        <p class="eyebrow centered text-brand-red-dark">Begin your journey</p>
        <h2 id="places-title" class="mt-4">Discover the best of Germany</h2>
        <span class="flag-rule centered" aria-hidden="true"></span>
        <p class="mt-5 text-base text-ink-soft sm:text-lg">A closer look at the landscapes, culture and moments that await on our journeys.</p>
    </div>
    <div class="flex snap-x snap-mandatory gap-4 overflow-x-auto px-4 pb-4 [scrollbar-width:none] sm:px-6 [&::-webkit-scrollbar]:hidden" tabindex="0" role="region" aria-label="Places in Germany">
        @foreach($places as [$stem, $label])
            @php $m = $media($stem); @endphp
            @continue(! $m)
            <figure class="group relative h-80 w-[72vw] shrink-0 snap-center overflow-hidden rounded-2xl shadow-card sm:h-96 sm:w-72">
                <x-picture :path="$m->path" sizes="(min-width: 640px) 18rem, 72vw" class="h-full w-full object-cover transition duration-[1200ms] group-hover:scale-105"/>
                <div class="absolute inset-0 bg-gradient-to-t from-brand-navy-dark/85 via-transparent to-transparent" aria-hidden="true"></div>
                <figcaption class="absolute inset-x-0 bottom-0 p-5 font-display text-2xl text-white">{{ $label }}<span class="mt-2 block h-[3px] w-10 bg-brand-gold transition-all duration-500 group-hover:w-20" aria-hidden="true"></span></figcaption>
            </figure>
        @endforeach
    </div>
</section>

{{-- 11. HOW IT WORKS: dark gradient, three numbered steps --}}
<section class="relative isolate overflow-hidden bg-brand-navy-dark py-16 text-white sm:py-24" aria-labelledby="how-title">
    <div class="pointer-events-none absolute -z-10 -top-32 -left-32 size-[30rem] rounded-full bg-brand-gold/15 blur-[120px]" aria-hidden="true"></div>
    <div class="pointer-events-none absolute -z-10 -bottom-32 -right-32 size-[26rem] rounded-full bg-brand-red/20 blur-[120px]" aria-hidden="true"></div>
    <div class="container-x">
        <div class="mx-auto mb-12 max-w-3xl text-center">
            <p class="eyebrow centered text-brand-gold">How it works</p>
            <h2 id="how-title" class="mt-4 text-white">Three steps to your Germany tour.</h2>
            <span class="flag-rule centered" aria-hidden="true"></span>
            <p class="mt-5 text-base text-white/85 sm:text-lg">Enjoy a stress-free journey with every detail expertly arranged.</p>
        </div>
        <ol class="grid gap-6 md:grid-cols-3">
            @foreach([
                ['Get your free quote', 'Share your travel preferences and a member of the team will reply with your quote.'],
                ['Review your itinerary', 'Review your itinerary and pricing, and make any final adjustments with our team.'],
                ['Travel with confidence', 'We organise hotels, transport and experiences, so you can simply arrive and enjoy Germany.'],
            ] as $i => [$t, $d])
                <li class="rounded-2xl border border-white/15 bg-white/5 p-8 backdrop-blur">
                    <span class="text-xs font-bold uppercase tracking-[0.2em] text-brand-gold">Step 0{{ $i + 1 }}/03</span>
                    <h3 class="mt-3 text-white">{{ $t }}</h3>
                    <p class="mt-3 text-white/80">{{ $d }}</p>
                </li>
            @endforeach
        </ol>
        <p class="mt-10 text-center"><button type="button" class="btn btn-primary px-10 shadow-lg shadow-black/30" @click="$store.modal.show()">Start your Germany dream trip <x-icon name="arrow-right" class="h-5 w-5"/></button></p>
    </div>
</section>

{{-- 12. LET'S PLAN YOUR JOURNEY (tinted band) --}}
<section class="bg-brand-custard-light py-14 text-center sm:py-20" aria-labelledby="plan-title">
    <div class="container-x max-w-3xl">
        <p class="eyebrow centered text-brand-red-dark">Begin your journey</p>
        <h2 id="plan-title" class="mt-4">Let's plan your <em class="font-medium italic text-brand-red">Germany journey.</em></h2>
        <p class="mt-5 text-base text-ink-soft sm:text-lg">Tell us how you would like to travel and we will create a journey tailored to your interests, pace and budget.</p>
        <button type="button" class="btn btn-primary mt-8 px-9" data-title="Ask us a question" data-prefill="Question about a Germany tour: "
                @click="$store.modal.show({form:'package_question', ...$el.dataset})">Ask a question <x-icon name="arrow-right" class="h-5 w-5"/></button>
    </div>
</section>

{{-- 13. EARLY-BIRD BAND: photo with left-to-right navy fade --}}
@php $eb = $media('saxon-switzerland-bastei'); @endphp
<section class="relative isolate overflow-hidden bg-brand-navy-dark py-16 text-white sm:py-24" aria-labelledby="lock-title">
    @if($eb)<x-picture :path="$eb->path" hires sizes="100vw" class="absolute inset-0 -z-20 h-full w-full object-cover object-bottom"/>@endif
    <div class="absolute inset-0 -z-10 bg-gradient-to-r from-brand-navy-dark/95 via-brand-navy-dark/65 to-brand-navy-dark/15" aria-hidden="true"></div>
    <div class="container-x">
        <div class="max-w-xl">
            <p class="eyebrow text-brand-gold">Early-bird pricing</p>
            <h2 id="lock-title" class="mt-4 text-white">Lock in your next adventure.</h2>
            <span class="flag-rule" aria-hidden="true"></span>
            <p class="mt-5 text-base text-white/90 sm:text-lg">Secure your dream Germany journey early and experience premium, stress-free travel for considerably less.</p>
            <x-button class="mt-8" data-title="Early bird offer: {{ $ebMonth }}" data-prefill="Early bird offer: 25% off, travelling in {{ $ebMonth }}."
                      @click="$store.modal.show({form:'package_quote', ...$el.dataset})">Learn how to save up to 25% <x-icon name="arrow-right" class="h-5 w-5"/></x-button>
        </div>
    </div>
</section>
@endsection
