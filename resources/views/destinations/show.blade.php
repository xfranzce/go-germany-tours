@extends('layouts.app')

@section('content')
@include('partials.collection-hero', [
    'heroImage' => $destination->image, 'eyebrow' => $destination->tagline ?: 'Discover '.$destination->name,
    'title' => $destination->name.' Tours and Vacations', 'lead' => $destination->summary,
    'crumbs' => [['Home', route('home')], ['Destinations', route('destinations.index')], [$destination->name, null]],
])
@php $places = $destination->placeList(); @endphp
<div class="container-x grid items-start gap-10 py-12 lg:grid-cols-2 lg:gap-16 lg:py-16">
    <section aria-labelledby="about-dest">
        <p class="eyebrow text-brand-red-dark">About {{ $destination->name }}</p>
        <h2 id="about-dest" class="mt-3">Explore {{ $destination->name }}</h2>
        <span class="flag-rule" aria-hidden="true"></span>
        <div class="prose-ggt mt-5">{!! $destination->description !!}</div>
        <x-button class="mt-8" data-title="Get a quote: {{ $destination->name }}" data-prefill="Interested in: {{ $destination->name }}."
                  @click="$store.modal.show({form:'package_quote', ...$el.dataset})">Get a Quote <x-icon name="arrow-right" class="w-5 h-5"/></x-button>
    </section>
    @if($places)
        <section class="rounded-2xl bg-white p-6 shadow-card sm:p-8" aria-labelledby="dest-places">
            <h2 id="dest-places" class="text-2xl">Highlights of the region</h2>
            <ul class="mt-6 space-y-4">
                @foreach($places as [$name, $note])
                    <li class="flex items-start gap-3">
                        <span class="mt-0.5 grid size-7 shrink-0 place-items-center rounded-full bg-brand-gold text-brand-navy" aria-hidden="true"><x-icon name="pin" class="size-4"/></span>
                        <span><span class="block font-bold text-brand-navy">{{ $name }}</span>@if($note)<span class="block text-sm text-ink-soft">{{ $note }}</span>@endif</span>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif
</div>
<section class="bg-brand-cream py-14 sm:py-20" aria-labelledby="dest-pkgs">
    <div class="container-x">
        <x-section-heading id="dest-pkgs" :eyebrow="$destination->name.' packages'" :title="'Journeys in '.$destination->name" emphasis="Planned around you."
            intro="A few ideas to start from. Every trip can be adjusted to suit your dates, group size and budget."/>
        @if($packages->isEmpty())<p class="text-ink-soft">No packages listed here yet. Ask us for a tailored trip.</p>
        @else<div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">@foreach($packages as $p)<x-package-card :package="$p"/>@endforeach</div>@endif
        <p class="mt-8 text-ink-soft">Also explore:
            @foreach($others as $o)<a class="underline font-bold" href="{{ route('destinations.show', $o->slug) }}">{{ $o->name }}</a>{{ $loop->last ? '' : ', ' }}@endforeach
        </p>
    </div>
</section>
@include('partials.sections.how')
@include('partials.sections.reviews')
@include('partials.sections.faq')
@endsection
