@extends('layouts.app')

{{-- Travel styles overview, laid out like gobelgiumtrips.com/travel-styles: quiz hero, photo tile per style (name, tour count, Explore), closing band. --}}
@section('content')
@include('partials.collection-hero', [
    'heroImage' => $styles->first()?->image, 'eyebrow' => 'Travel your way',
    'title' => 'Travel styles for Germany',
    'lead' => 'Slow or active, scenic or full of culture, with a glass of Riesling or a private driver. Pick a style to see the trips that fit it.',
    'crumbs' => [['Home', route('home')], ['Travel styles', null]],
])

<section class="bg-white py-16 sm:py-24" aria-labelledby="styles-title">
    <div class="container-x">
        <div class="mb-10 max-w-2xl">
            <h2 id="styles-title">All our <em class="font-medium italic text-brand-red">travel styles</em></h2>
            <span class="flag-rule" aria-hidden="true"></span>
            <p class="mt-5 text-base text-ink-soft sm:text-lg">Pick a style to see the trips that fit it. Every style works with self drive, a private driver or a small-group coach.</p>
        </div>
        @include('partials.collection-tiles', ['items' => $styles, 'itemRoute' => 'travel-styles.show'])
    </div>
</section>

@include('partials.ready-band', ['eyebrow' => 'Your Germany trip', 'before' => 'Not sure which', 'emphasis' => 'style suits you?', 'subject' => 'a Germany trip'])
@endsection
