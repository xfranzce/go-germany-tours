@extends('layouts.app')

@section('content')
@include('partials.collection-hero', [
    'heroImage' => $destinations->first()?->image, 'eyebrow' => 'Discover Germany',
    'title' => 'Find your dream destination in Germany',
    'lead' => 'From half-timbered towns and castle-topped rivers to sandstone peaks and quiet Baltic islands. Pick a region and we will plan the trip around you.',
    'crumbs' => [['Home', route('home')], ['Destinations', null]],
])

<section class="bg-brand-cream py-16 sm:py-24" aria-labelledby="dest-title">
    <div class="container-x">
        <x-section-heading id="dest-title" eyebrow="The Germany collection" title="Discover Germany" emphasis="region by region"
            intro="Each region below can be explored by self drive, with a private driver or on a small-group coach tour."/>
        <div class="space-y-10 lg:space-y-14">
            @foreach($destinations as $d)
                @php $places = $d->placeList(); @endphp
                <article class="grid overflow-hidden rounded-2xl bg-white shadow-card lg:grid-cols-2" aria-labelledby="dest-{{ $d->slug }}">
                    <a href="{{ route('destinations.show', $d->slug) }}" class="group relative block min-h-64 overflow-hidden bg-brand-custard {{ $loop->even ? 'lg:order-2' : '' }}" tabindex="-1" aria-hidden="true">
                        @if($d->image)<x-picture :path="$d->image" sizes="(min-width: 1024px) 50vw, 100vw" class="absolute inset-0 h-full w-full object-cover transition duration-700 group-hover:scale-105"/>@endif
                    </a>
                    <div class="p-6 sm:p-10">
                        @if($d->tagline)<p class="eyebrow text-brand-red-dark">{{ $d->tagline }}</p>@endif
                        <h2 id="dest-{{ $d->slug }}" class="mt-3">{{ $d->name }}</h2>
                        <span class="flag-rule" aria-hidden="true"></span>
                        @if($d->summary)<p class="mt-5 text-ink-soft">{{ $d->summary }}</p>@endif
                        @if($places)
                            <ul class="mt-6 grid gap-x-6 gap-y-3 sm:grid-cols-2">
                                @foreach($places as [$name, $note])
                                    <li class="flex items-start gap-3">
                                        <span class="mt-0.5 grid size-6 shrink-0 place-items-center rounded-full bg-brand-gold text-brand-navy" aria-hidden="true"><x-icon name="pin" class="size-3.5"/></span>
                                        <span><span class="block font-bold text-brand-navy">{{ $name }}</span>@if($note)<span class="block text-sm text-ink-soft">{{ $note }}</span>@endif</span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                        <div class="mt-8 flex flex-wrap gap-3">
                            <x-button :href="route('destinations.show', $d->slug)" variant="outline" size="sm">Explore {{ $d->name }}</x-button>
                            <x-button size="sm" data-title="Get a quote: {{ $d->name }}" data-prefill="Interested in: {{ $d->name }}."
                                      @click="$store.modal.show({form:'package_quote', ...$el.dataset})">Get a quote</x-button>
                        </div>
                        @if($d->packages_count)<p class="mt-3 text-sm text-ink-soft">{{ $d->packages_count }} {{ Str::plural('tour', $d->packages_count) }} in this region</p>@endif
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
@include('partials.sections.why')
@include('partials.sections.how')
@include('partials.sections.reviews')
@include('partials.sections.faq')
@endsection
