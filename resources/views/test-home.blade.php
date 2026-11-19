@extends('layouts.app')

@php
    $heroPath = $page->data['hero_image'] ?? null;
    $heroMedia = \App\Models\Media::forPath($heroPath);
    $byType = $packages->groupBy(fn ($p) => $p->tourType->slug);
    $ebMonth = now()->addMonthsNoOverflow(3)->format('F Y');
    $icons = ['self-drive' => 'car', 'chauffeur-driven' => 'chauffeur', 'coach-tours' => 'bus'];
@endphp

@section('content')
{{-- 1. HERO: centred, eyebrow + headline + two CTAs + trust row --}}
<section class="relative isolate flex min-h-svh items-center overflow-hidden bg-brand-navy-dark text-center text-white" aria-labelledby="hero-title">
    @if($heroMedia)<x-picture :path="$heroPath" priority hires sizes="100vw" class="absolute inset-0 -z-20 h-full w-full object-cover"/>@endif
    <div class="absolute inset-0 -z-10 bg-gradient-to-b from-brand-navy-dark/80 via-brand-navy-dark/45 to-brand-navy-dark/85" aria-hidden="true"></div>
    <div class="container-x max-w-4xl pt-36 pb-24">
        <p class="eyebrow centered text-brand-gold">Authentic Germany experiences</p>
        <h1 id="hero-title" class="mt-6 leading-[1.08]">Find the best value vacation
            <em class="mt-2 block font-medium italic text-brand-gold">trip to Germany</em></h1>
        <p class="mx-auto mt-6 max-w-2xl text-base text-white/90 sm:text-lg">Self-drive, chauffeur-driven or small-group coach tours, planned around you with local insight and carefully chosen stays.</p>
        <div class="mt-9 flex flex-wrap justify-center gap-3">
            <button type="button" class="btn btn-primary px-9 shadow-lg shadow-black/30" @click="$store.modal.show()">Start your adventure <x-icon name="arrow-right" class="h-5 w-5"/></button>
            <a href="#quiz" class="btn btn-outline-light px-9">Complete the quiz for your quote</a>
        </div>
        <ul class="mt-12 flex flex-wrap items-center justify-center gap-x-8 gap-y-3 border-t border-white/25 pt-6 text-xs font-bold uppercase tracking-[0.16em] sm:text-sm">
            @foreach([['star', 'Excellent reviews'], ['lock', 'Private tours'], ['pin', 'Local Germany experts']] as [$ic, $t])
                <li class="flex items-center gap-2.5"><x-icon :name="$ic" class="h-4 w-4 text-brand-gold"/>{{ $t }}</li>
            @endforeach
        </ul>
    </div>
</section>

{{-- 2. FOUR PROMISES --}}
<section class="border-b border-brand-custard bg-brand-custard-light py-14 text-brand-navy" aria-label="Why book with us">
    <ul class="container-x grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
        @foreach([
            ['check', 'Free, no-obligation quote', 'Tell us what you have in mind and get a quote in return.'],
            ['shield', 'Clear, honest pricing', 'Know what is included before you decide.'],
            ['adjust', 'Locally crafted itineraries', 'Built around your dates, group and budget.'],
            ['chat', 'Real people, real support', 'A member of the team replies to you.'],
        ] as [$ic, $t, $d])
            <li class="text-center">
                <span class="mx-auto grid size-14 place-items-center rounded-full bg-brand-gold text-brand-navy shadow-lg shadow-brand-gold/30"><x-icon :name="$ic" class="h-6 w-6"/></span>
                <h3 class="mt-4 text-lg">{{ $t }}</h3>
                <p class="mt-2 text-sm text-ink-soft">{{ $d }}</p>
            </li>
        @endforeach
    </ul>
</section>

{{-- 3. STATS (counts come from the database, nothing invented) --}}
<section class="bg-brand-navy py-12 text-white" aria-label="Germany tours at a glance">
    <ul class="container-x grid grid-cols-2 gap-8 text-center lg:grid-cols-4">
        @foreach([[$packages->count(), 'Tour packages'], [$tourTypes->count(), 'Ways to travel'], [\App\Models\Destination::active()->count(), 'Destinations'], ['7', 'Days a week support [CONFIRM]']] as [$n, $l])
            <li>
                <span class="block font-display text-5xl text-brand-gold">{{ $n }}</span>
                <span class="mt-2 block text-xs font-bold uppercase tracking-[0.18em] text-white/85">{{ $l }}</span>
            </li>
        @endforeach
    </ul>
</section>

{{-- 4. ABOUT INTRO: heading left, copy + CTA right --}}
<section class="py-16 sm:py-24" aria-labelledby="intro-title">
    <div class="container-x grid gap-10 lg:grid-cols-2 lg:items-center lg:gap-20">
        <div>
            <p class="eyebrow text-brand-red-dark">Who we are</p>
            <h2 id="intro-title" class="mt-4">A Germany trip that is
                <em class="block font-medium italic text-brand-red">truly yours.</em></h2>
            <span class="flag-rule" aria-hidden="true"></span>
        </div>
        <div>
            <p class="text-base text-ink-soft sm:text-lg">Discover Germany through journeys shaped around how you like to travel, from fairytale towns with half-timbered streets to river valleys, sandstone cliffs and the Baltic coast.</p>
            <button type="button" class="btn btn-primary mt-8 px-9" @click="$store.modal.show()">Get instant quote <x-icon name="arrow-right" class="h-5 w-5"/></button>
        </div>
    </div>
</section>

{{-- 5. SIGNATURE VACATIONS: three cards with bullets and three buttons --}}
<section class="bg-brand-custard-light py-16 sm:py-24" aria-labelledby="sig-title" id="tours">
    <div class="container-x">
        <x-section-heading id="sig-title" eyebrow="Signature vacations" title="Choose how you" emphasis="travel Germany."/>
        <div class="grid gap-8 md:grid-cols-3">
            @foreach($tourTypes as $t)
                @php $incl = array_slice(array_values(array_filter(array_map('trim', preg_split('/\R/', (string) $t->inclusions)))), 0, 3); @endphp
                <article class="card flex flex-col">
                    <a href="{{ route('tours.show', $t->slug) }}" class="block aspect-[4/3] overflow-hidden bg-brand-custard" tabindex="-1" aria-hidden="true">
                        <x-picture :path="$t->image" sizes="(min-width: 768px) 24rem, 92vw" class="h-full w-full object-cover"/>
                    </a>
                    <div class="flex flex-1 flex-col p-7">
                        <h3><a href="{{ route('tours.show', $t->slug) }}" class="hover:underline">{{ $t->name }}</a></h3>
                        <p class="mt-3 text-ink-soft">{{ $t->summary }}</p>
                        @if($incl)
                            <ul class="mt-5 space-y-2 text-sm font-semibold text-brand-navy">
                                @foreach($incl as $i)<li class="flex items-start gap-2"><x-icon name="check" class="mt-0.5 h-4 w-4 shrink-0 text-brand-red"/>{{ $i }}</li>@endforeach
                            </ul>
                        @endif
                        <div class="mt-auto grid gap-2 pt-6">
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

{{-- 6. OFFER BAND (existing early bird offer) --}}
<section class="relative isolate overflow-hidden bg-brand-navy py-14 text-white sm:py-16" aria-labelledby="offer-title">
    <div class="absolute inset-0 -z-10 bg-gradient-to-r from-brand-navy-dark via-brand-navy to-brand-navy-dark" aria-hidden="true"></div>
    <div class="container-x grid items-center gap-8 text-center lg:grid-cols-[auto_1fr_auto] lg:gap-12 lg:text-left">
        <div class="mx-auto grid size-32 place-items-center rounded-full border-4 border-brand-gold bg-brand-gold text-brand-navy shadow-xl shadow-black/30 sm:size-40 lg:mx-0" aria-hidden="true">
            <span class="text-center font-display font-extrabold leading-none"><span class="block text-5xl sm:text-6xl">25%</span><span class="mt-1 block text-sm uppercase tracking-[0.2em]">off</span></span>
        </div>
        <div>
            <p class="eyebrow text-brand-gold">Early bird offer</p>
            <h2 id="offer-title" class="mt-3 text-white">Save 25% on Germany tours in {{ $ebMonth }}</h2>
            <ul class="mt-5 grid gap-2 text-white/90 sm:grid-cols-2">
                @foreach(['Free, no-obligation quote', 'Itineraries built around you', 'Plan ahead and book early'] as $b)
                    <li class="flex items-center gap-2 justify-center lg:justify-start"><x-icon name="check" class="h-4 w-4 text-brand-gold"/>{{ $b }}</li>
                @endforeach
            </ul>
        </div>
        <x-button data-title="Early bird offer: {{ $ebMonth }}" data-prefill="Early bird offer: 25% off, travelling in {{ $ebMonth }}."
                  @click="$store.modal.show({form:'package_quote', ...$el.dataset})">Claim your discount <x-icon name="arrow-right" class="h-5 w-5"/></x-button>
    </div>
</section>

{{-- 7. PACKAGES PER TOUR TYPE: heading + count stats, then card grid (alternating backgrounds) --}}
@foreach($tourTypes as $t)
    @php $list = ($byType[$t->slug] ?? collect())->take(6); @endphp
    @continue($list->isEmpty())
    <section class="py-16 sm:py-24 {{ $loop->odd ? '' : 'bg-brand-custard-light' }}" aria-labelledby="pk-{{ $t->slug }}">
        <div class="container-x">
            <div class="mb-12 flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div class="lg:max-w-2xl">
                    <p class="eyebrow flex items-center gap-2 text-brand-red-dark"><x-icon :name="$icons[$t->slug] ?? 'pin'" class="h-5 w-5"/>{{ $t->name }}</p>
                    <h2 id="pk-{{ $t->slug }}" class="mt-4">{{ $t->name }} packages</h2>
                    <span class="flag-rule" aria-hidden="true"></span>
                    <p class="mt-5 text-base text-ink-soft sm:text-lg">{{ $t->summary }}</p>
                </div>
                <dl class="flex gap-8 text-center">
                    <div><dt class="text-xs font-bold uppercase tracking-[0.16em] text-ink-soft">Itineraries</dt><dd class="font-display text-4xl text-brand-navy">{{ ($byType[$t->slug] ?? collect())->count() }}</dd></div>
                </dl>
            </div>
            <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                @foreach($list as $package)<x-package-card :package="$package"/>@endforeach
            </div>
            <p class="mt-12 text-center"><x-button :href="route('tours.show', $t->slug)" variant="outline">See all {{ strtolower($t->name) }} packages <x-icon name="arrow-right" class="h-5 w-5"/></x-button></p>
        </div>
    </section>
@endforeach

{{-- 8. QUIZ ("Complete the survey below") --}}
<section id="quiz" class="relative isolate overflow-hidden bg-brand-navy-dark py-16 text-white sm:py-24" aria-labelledby="quiz-title">
    @if($heroMedia)<x-picture :path="$heroPath" sizes="100vw" class="absolute inset-0 -z-20 h-full w-full object-cover opacity-30"/>@endif
    <div class="container-x grid items-center gap-10 lg:grid-cols-2 lg:gap-16">
        <div class="text-center lg:text-left">
            <p class="eyebrow text-brand-gold">Exclusive access</p>
            <h2 id="quiz-title" class="mt-4 text-white">Answer a few questions,
                <em class="block font-medium italic text-brand-gold">get your free quote.</em></h2>
            <span class="flag-rule mx-auto lg:mx-0" aria-hidden="true"></span>
            <p class="mt-5 text-base text-white/85 sm:text-lg">It takes under a minute, with no obligation.</p>
        </div>
        <div class="mx-auto w-full max-w-xl"><x-hero-quiz/></div>
    </div>
</section>
@endsection
