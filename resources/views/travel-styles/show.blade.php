@extends('layouts.app')

{{-- One travel style, laid out like gobelgiumtrips.com/travel-styles/{style}: quiz hero, "Germany for X" text + what to expect,
     the trips in this style, other styles, and a "Ready for X?" closing band. --}}
@section('content')
@include('partials.collection-hero', [
    'heroImage' => $style->image, 'eyebrow' => $style->tagline ?: $style->name.' travel',
    'title' => $style->name.' Tours of Germany', 'lead' => $style->summary,
    'crumbs' => [['Home', route('home')], ['Travel styles', route('travel-styles.index')], [$style->name, null]],
])
@php $points = $style->points(); @endphp

{{-- ABOUT --}}
<section class="bg-white py-16 sm:py-24" aria-labelledby="about-style">
    <div class="container-x max-w-3xl text-center">
        <p class="eyebrow centered text-brand-red-dark">{{ $style->name }} travel</p>
        <h2 id="about-style" class="mt-4">Germany for <em class="font-medium italic text-brand-red">{{ strtolower($style->name) }}</em> travelers</h2>
        <span class="flag-rule centered" aria-hidden="true"></span>
        @if($style->description)<div class="prose-ggt mx-auto mt-6 text-left sm:text-center">{!! $style->description !!}</div>
        @elseif($style->summary)<p class="mt-6 text-lg text-ink-soft">{{ $style->summary }}</p>@endif
        @if($points)
            <ul class="mt-8 flex flex-wrap justify-center gap-2" aria-label="What to expect">
                @foreach($points as $pt)
                    <li class="inline-flex items-center gap-1.5 rounded-full border border-brand-custard bg-brand-custard-light px-3.5 py-1.5 text-sm font-semibold text-brand-navy">
                        <x-icon name="check" class="size-4 text-brand-red"/>{{ $pt }}</li>
                @endforeach
            </ul>
        @endif
        <x-button class="mt-8" data-title="Get a quote: {{ $style->name }} trip" data-prefill="Interested in: a {{ strtolower($style->name) }} trip."
                  @click="$store.modal.show({form:'package_quote', ...$el.dataset})">Get a free quote <x-icon name="arrow-right" class="w-5 h-5"/></x-button>
    </div>
</section>

{{-- TRIPS --}}
<section class="bg-brand-cream py-16 sm:py-24" aria-labelledby="style-pkgs">
    <div class="container-x">
        <div class="mb-10 flex flex-wrap items-end justify-between gap-6">
            <div class="max-w-2xl">
                <h2 id="style-pkgs">{{ $style->name }} <em class="font-medium italic text-brand-red">trips</em></h2>
                <span class="flag-rule" aria-hidden="true"></span>
                <p class="mt-5 text-base text-ink-soft sm:text-lg">{{ $packages->count() }} {{ Str::plural('trip', $packages->count()) }} to start from. Every one can be changed to fit your dates, pace and budget.</p>
            </div>
            <x-button :href="route('packages.index')" variant="outline" size="sm">All packages <x-icon name="arrow-right" class="h-4 w-4"/></x-button>
        </div>
        @if($packages->isEmpty())<p class="text-ink-soft">No packages listed for this style yet. Ask us for a tailored trip.</p>
        @else<div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">@foreach($packages as $p)<x-package-card :package="$p"/>@endforeach</div>@endif
    </div>
</section>

{{-- OTHER STYLES --}}
@if($others->isNotEmpty())
<section class="bg-white py-16 sm:py-24" aria-labelledby="style-more">
    <div class="container-x">
        <h2 id="style-more">Other ways to see <em class="font-medium italic text-brand-red">Germany</em></h2>
        <span class="flag-rule" aria-hidden="true"></span>
        <div class="mt-10">@include('partials.collection-tiles', ['items' => $others, 'itemRoute' => 'travel-styles.show'])</div>
    </div>
</section>
@endif

{{-- REVIEWS: this style's own (Admin > Testimonials "About"), else the general ones --}}
@include('partials.sections.reviews', ['reviewsTitle' => $testimonials->first()?->travel_style_id === $style->id ? 'What '.strtolower($style->name).' travelers say' : null])

{{-- FAQ: this style's own (Admin > FAQs "Shows on"), else the general ones --}}
@include('partials.sections.faq', ['faqTitle' => $ownFaqs ? 'Questions about '.strtolower($style->name).' trips' : null])

@include('partials.ready-band', ['eyebrow' => $style->name, 'before' => 'Ready for a', 'emphasis' => strtolower($style->name).' trip?', 'subject' => 'a '.strtolower($style->name).' trip'])
@endsection
