@extends('layouts.app')

@section('content')
@include('partials.collection-hero', [
    'heroImage' => $style->image, 'eyebrow' => $style->tagline ?: $style->name.' travel',
    'title' => $style->name.' Tours of Germany', 'lead' => $style->summary,
    'crumbs' => [['Home', route('home')], ['Travel styles', route('travel-styles.index')], [$style->name, null]],
])
@php $points = $style->points(); @endphp
@if($style->description || $points)
<div class="container-x grid items-start gap-10 py-12 lg:grid-cols-2 lg:gap-16 lg:py-16">
    <section aria-labelledby="about-style">
        <p class="eyebrow text-brand-red-dark">{{ $style->name }} travel</p>
        <h2 id="about-style" class="mt-3">Travel Germany the {{ strtolower($style->name) }} way</h2>
        <span class="flag-rule" aria-hidden="true"></span>
        @if($style->description)<div class="prose-ggt mt-5">{!! $style->description !!}</div>@elseif($style->summary)<p class="mt-5 text-lg text-ink-soft">{{ $style->summary }}</p>@endif
        <x-button class="mt-8" data-title="Get a quote: {{ $style->name }} trip" data-prefill="Interested in: a {{ strtolower($style->name) }} trip."
                  @click="$store.modal.show({form:'package_quote', ...$el.dataset})">Get a Quote <x-icon name="arrow-right" class="w-5 h-5"/></x-button>
    </section>
    @if($points)
        <section class="rounded-2xl bg-white p-6 shadow-card sm:p-8" aria-labelledby="style-expect">
            <h2 id="style-expect" class="text-2xl">What to expect</h2>
            <ul class="mt-6 space-y-4">
                @foreach($points as $pt)
                    <li class="flex items-start gap-3">
                        <span class="mt-0.5 grid size-7 shrink-0 place-items-center rounded-full bg-brand-gold text-brand-navy" aria-hidden="true"><x-icon name="check" class="size-4"/></span>
                        <span class="font-semibold text-brand-navy">{{ $pt }}</span>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif
</div>
@endif
<section class="bg-brand-cream py-14 sm:py-20" aria-labelledby="style-pkgs">
    <div class="container-x">
        <x-section-heading id="style-pkgs" :eyebrow="$style->name.' packages'" :title="'Signature '.strtolower($style->name).' journeys'" emphasis="Planned around you."
            intro="Choose a ready-made trip, or use one as inspiration for a journey tailored entirely to you."/>
        @if($packages->isEmpty())<p class="text-ink-soft">No packages listed for this style yet. Ask us for a tailored trip.</p>
        @else<div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">@foreach($packages as $p)<x-package-card :package="$p"/>@endforeach</div>@endif
        <p class="mt-8 text-ink-soft">Other travel styles:
            @foreach($others as $o)<a class="underline font-bold" href="{{ route('travel-styles.show', $o->slug) }}">{{ $o->name }}</a>{{ $loop->last ? '' : ', ' }}@endforeach
        </p>
    </div>
</section>
@include('partials.sections.how')
@include('partials.sections.reviews')
@include('partials.sections.faq')
@endsection
