@extends('layouts.app')

@section('content')
@include('partials.page-header', [
    'title' => $destination->name.' Tours and Vacations',
    'lead' => $destination->summary,
    'crumbs' => [['Home', route('home')], ['Destinations', route('packages.index')], [$destination->name, null]],
])
<div class="container-x py-12 grid gap-10 lg:grid-cols-[1fr_22rem]">
    <div>
        @if($destination->image)
            <div class="rounded-2xl overflow-hidden mb-8"><x-picture :path="$destination->image" priority sizes="(min-width: 1024px) 46rem, 100vw" class="w-full max-h-[26rem] object-cover"/></div>
        @endif
        <div class="prose-ggt">{!! $destination->description !!}</div>
    </div>
    <aside class="card p-5 self-start border-4 border-brand-tangerine">
        <h2 class="text-2xl">Plan your {{ $destination->name }} trip</h2>
        <p class="mt-2 text-ink-soft">Tell us your dates and we will send a quote.</p>
        <x-button class="w-full mt-4" data-title="Get a quote: {{ $destination->name }}" data-prefill="Interested in: {{ $destination->name }}."
                  @click="$store.modal.show({form:'package_quote', ...$el.dataset})">Get a Free Quote</x-button>
    </aside>
</div>
<section class="bg-brand-custard py-14" aria-labelledby="dest-pkgs">
    <div class="container-x">
        <h2 id="dest-pkgs" class="text-2xl sm:text-3xl mb-6">Packages in {{ $destination->name }}</h2>
        @if($packages->isEmpty())
            <p class="text-ink-soft">No packages listed here yet. Ask us for a tailored trip.</p>
        @else
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">@foreach($packages as $p)<x-package-card :package="$p"/>@endforeach</div>
        @endif
        <p class="mt-8 text-ink-soft">Also explore:
            @foreach($others as $o)<a class="underline font-bold" href="{{ route('destinations.show', $o->slug) }}">{{ $o->name }}</a>{{ $loop->last ? '' : ', ' }}@endforeach
        </p>
    </div>
</section>
@endsection
