@extends('layouts.app')

@section('content')
@include('partials.collection-hero', [
    'heroImage' => $styles->first()?->image, 'eyebrow' => 'Travel your way',
    'title' => 'Germany tours by travel style',
    'lead' => 'Slow or active, scenic or full of culture, with a glass of Riesling or a private driver. Choose how you like to travel and we will shape the trip around it.',
    'crumbs' => [['Home', route('home')], ['Travel styles', null]],
])

<section class="bg-brand-cream py-16 sm:py-24" aria-labelledby="styles-title">
    <div class="container-x">
        <x-section-heading id="styles-title" eyebrow="Travel styles" title="How do you like" emphasis="to travel?"
            intro="Every style can be combined with self drive, a private driver or a small-group coach tour."/>
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($styles as $s)
                <a href="{{ route('travel-styles.show', $s->slug) }}" class="group relative isolate flex min-h-[22rem] flex-col justify-end overflow-hidden rounded-2xl bg-brand-navy p-6 text-white shadow-card">
                    @if($s->image)<x-picture :path="$s->image" sizes="(min-width: 1024px) 30vw, (min-width: 640px) 50vw, 100vw" class="absolute inset-0 -z-20 h-full w-full object-cover transition duration-700 group-hover:scale-105"/>@endif
                    <span class="absolute inset-0 -z-10 bg-gradient-to-t from-brand-navy-dark/90 via-brand-navy-dark/40 to-transparent" aria-hidden="true"></span>
                    @if($s->tagline)<span class="eyebrow text-brand-gold [text-shadow:0_1px_6px_rgb(0_0_0/0.7)]">{{ $s->tagline }}</span>@endif
                    <h2 class="mt-2 text-white">{{ $s->name }}</h2>
                    @if($s->summary)<p class="mt-2 line-clamp-3 text-sm text-white/90">{{ $s->summary }}</p>@endif
                    <span class="mt-4 inline-flex items-center gap-2 text-xs font-extrabold uppercase tracking-[0.16em] text-brand-gold">
                        View {{ $s->packages_count }} {{ Str::plural('tour', $s->packages_count) }} <x-icon name="arrow-right" class="h-4 w-4 transition group-hover:translate-x-1"/></span>
                </a>
            @endforeach
        </div>
    </div>
</section>
@include('partials.sections.why')
@include('partials.sections.how')
@include('partials.sections.reviews')
@include('partials.sections.faq')
@endsection
