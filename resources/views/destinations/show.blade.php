@extends('layouts.app')

{{-- One destination, laid out like gobelgiumtrips.com/destinations/bruges: quiz hero, "Discover X", the trips that visit it,
     more destinations, and a "Ready for X?" closing band. --}}
@section('content')
@include('partials.collection-hero', [
    'heroImage' => $destination->image, 'eyebrow' => $destination->tagline ?: 'Discover '.$destination->name,
    'title' => 'Explore '.$destination->name, 'lead' => $destination->summary,
    'crumbs' => [['Home', route('home')], ['Destinations', route('destinations.index')], [$destination->name, null]],
])
@php $places = $destination->placeList(); @endphp

{{-- ABOUT --}}
<section class="bg-white py-16 sm:py-24" aria-labelledby="about-dest">
    <div class="container-x max-w-3xl text-center">
        <p class="eyebrow centered text-brand-red-dark">About {{ $destination->name }}</p>
        <h2 id="about-dest" class="mt-4">Discover <em class="font-medium italic text-brand-red">{{ $destination->name }}</em></h2>
        <span class="flag-rule centered" aria-hidden="true"></span>
        @if($destination->description)<div class="prose-ggt mx-auto mt-6 text-left sm:text-center">{!! $destination->description !!}</div>
        @elseif($destination->summary)<p class="mt-6 text-lg text-ink-soft">{{ $destination->summary }}</p>@endif
        @if($places)
            <ul class="mt-8 flex flex-wrap justify-center gap-2" aria-label="Highlights of the region">
                @foreach($places as [$name, $note])
                    <li class="inline-flex items-center gap-1.5 rounded-full border border-brand-custard bg-brand-custard-light px-3.5 py-1.5 text-sm font-semibold text-brand-navy" @if($note) title="{{ $note }}" @endif>
                        <x-icon name="pin" class="size-4 text-brand-red"/>{{ $name }}</li>
                @endforeach
            </ul>
        @endif
        <x-button class="mt-8" data-title="Get a quote: {{ $destination->name }}" data-prefill="Interested in: {{ $destination->name }}."
                  @click="$store.modal.show({form:'package_quote', ...$el.dataset})">Get a free quote <x-icon name="arrow-right" class="w-5 h-5"/></x-button>
    </div>
</section>

{{-- TRIPS --}}
<section class="bg-brand-cream py-16 sm:py-24" aria-labelledby="dest-pkgs">
    <div class="container-x">
        <div class="mb-10 flex flex-wrap items-end justify-between gap-6">
            <div class="max-w-2xl">
                <h2 id="dest-pkgs">Trips that visit <em class="font-medium italic text-brand-red">{{ $destination->name }}</em></h2>
                <span class="flag-rule" aria-hidden="true"></span>
                <p class="mt-5 text-base text-ink-soft sm:text-lg">Sample trips to start from. Every one can be changed to fit your dates, pace and budget.</p>
            </div>
            <x-button :href="route('packages.index', ['destination' => $destination->slug])" variant="outline" size="sm">Filter all packages <x-icon name="arrow-right" class="h-4 w-4"/></x-button>
        </div>
        @if($packages->isEmpty())<p class="text-ink-soft">No packages listed here yet. Ask us for a tailored trip.</p>
        @else<div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">@foreach($packages as $p)<x-package-card :package="$p"/>@endforeach</div>@endif
    </div>
</section>

{{-- MORE DESTINATIONS --}}
@if($others->isNotEmpty())
<section class="bg-white py-16 sm:py-24" aria-labelledby="dest-more">
    <div class="container-x">
        <h2 id="dest-more">More destinations to <em class="font-medium italic text-brand-red">explore</em></h2>
        <span class="flag-rule" aria-hidden="true"></span>
        <ul class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($others as $o)
                <li>
                    <a href="{{ route('destinations.show', $o->slug) }}" class="group relative isolate flex min-h-56 flex-col justify-end overflow-hidden rounded-2xl bg-brand-navy p-6 text-white shadow-card">
                        @if($o->image)<x-picture :path="$o->image" sizes="(min-width: 1024px) 30vw, (min-width: 640px) 50vw, 100vw" class="absolute inset-0 -z-20 h-full w-full object-cover transition duration-700 group-hover:scale-105"/>@endif
                        <span class="absolute inset-0 -z-10 bg-gradient-to-t from-brand-navy-dark/90 via-brand-navy-dark/35 to-transparent" aria-hidden="true"></span>
                        <span class="font-display text-3xl font-semibold">{{ $o->name }}</span>
                        <span class="mt-1 text-sm text-white/85">{{ $o->packages_count }} {{ Str::plural('tour', $o->packages_count) }}</span>
                        <span class="mt-3 inline-flex items-center gap-2 text-xs font-extrabold uppercase tracking-[0.16em] text-brand-gold">Explore <x-icon name="arrow-right" class="h-4 w-4 transition group-hover:translate-x-1"/></span>
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</section>
@endif

{{-- READY FOR X? --}}
<section class="bg-brand-cream py-16 text-center sm:py-24" aria-labelledby="dest-ready">
    <div class="container-x max-w-2xl">
        <p class="eyebrow centered text-brand-red-dark">{{ $destination->name }}</p>
        <h2 id="dest-ready" class="mt-4">Ready for <em class="font-medium italic text-brand-red">{{ $destination->name }}?</em></h2>
        <span class="flag-rule centered" aria-hidden="true"></span>
        <p class="mt-5 text-base text-ink-soft sm:text-lg">Tell us when you want to go and who is coming. We will send a free quote.</p>
        <div class="mt-8 flex flex-wrap justify-center gap-3">
            <x-button data-title="Get a quote: {{ $destination->name }}" data-prefill="Interested in: {{ $destination->name }}."
                      @click="$store.modal.show({form:'package_quote', ...$el.dataset})">Get a free quote <x-icon name="arrow-right" class="w-5 h-5"/></x-button>
            <x-button variant="outline" data-title="Ask us a question" data-subtitle="{{ $destination->name }}" data-prefill="Question about {{ $destination->name }}: "
                      @click="$store.modal.show({form:'package_question', ...$el.dataset})">Ask us a question</x-button>
        </div>
    </div>
</section>
@endsection
