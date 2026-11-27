@extends('layouts.app')

{{-- Destinations overview, laid out like gobelgiumtrips.com/destinations: quiz hero, then one card per destination (photo, tour count, name, Explore link). --}}
@section('content')
@include('partials.collection-hero', [
    'heroImage' => $destinations->first()?->image, 'eyebrow' => 'Discover Germany',
    'title' => 'Destinations worth slowing down for',
    'lead' => $destinations->count().' places to build a Germany trip around. Pick one and we will show you the tours that go there.',
    'crumbs' => [['Home', route('home')], ['Destinations', null]],
])

<section class="bg-brand-cream py-16 sm:py-24" aria-labelledby="dest-title">
    <div class="container-x">
        <div class="mb-10 max-w-2xl">
            <h2 id="dest-title">All our <em class="font-medium italic text-brand-red">destinations</em></h2>
            <span class="flag-rule" aria-hidden="true"></span>
            <p class="mt-5 text-base text-ink-soft sm:text-lg">Pick a place to see the tours that go there.</p>
        </div>
        <ul class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($destinations as $d)
                <li>
                    <a href="{{ route('destinations.show', $d->slug) }}" class="group flex h-full flex-col overflow-hidden rounded-2xl bg-white shadow-card ring-1 ring-brand-custard transition duration-300 hover:-translate-y-1 hover:shadow-xl">
                        <span class="relative block aspect-[4/3] overflow-hidden bg-brand-custard">
                            @if($d->image)<x-picture :path="$d->image" sizes="(min-width: 1024px) 30vw, (min-width: 640px) 50vw, 100vw" class="h-full w-full object-cover transition duration-700 group-hover:scale-105"/>@endif
                        </span>
                        <span class="flex flex-1 flex-col p-6">
                            <span class="text-xs font-extrabold uppercase tracking-[0.18em] text-brand-red-dark">{{ $d->packages_count }} {{ Str::plural('tour', $d->packages_count) }}</span>
                            <span class="mt-2 font-display text-3xl font-semibold text-brand-navy">{{ $d->name }}</span>
                            @if($d->tagline)<span class="mt-1 text-sm text-ink-soft">{{ $d->tagline }}</span>@endif
                            <span class="mt-auto inline-flex items-center gap-2 pt-5 text-sm font-bold text-brand-navy">
                                <span class="underline decoration-brand-red decoration-2 underline-offset-4">Explore {{ $d->name }}</span>
                                <x-icon name="arrow-right" class="h-4 w-4 transition group-hover:translate-x-1"/>
                            </span>
                        </span>
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</section>
@include('partials.sections.why')
@include('partials.sections.how')
@include('partials.sections.reviews')
@include('partials.sections.faq')
@endsection
