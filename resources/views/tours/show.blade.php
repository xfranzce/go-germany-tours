@extends('layouts.app')

@section('content')
@php $base = trim(preg_replace('/\s+tours$/i', '', $type->name)); @endphp
<section class="relative isolate overflow-hidden bg-brand-navy text-white">
    @if($type->image)<x-picture :path="$type->image" priority sizes="100vw" class="absolute inset-0 -z-20 h-full w-full object-cover"/>@endif
    <div class="absolute inset-0 -z-10 bg-gradient-to-r from-brand-navy-dark/85 via-brand-navy-dark/45 to-transparent" aria-hidden="true"></div>
    <div class="absolute inset-x-0 top-0 -z-10 h-40 bg-gradient-to-b from-brand-navy-dark/60 to-transparent" aria-hidden="true"></div>
    <div class="container-x grid items-center gap-10 pt-28 pb-12 sm:pt-36 sm:pb-16 lg:grid-cols-[1.1fr_0.9fr] lg:gap-14">
        <div>
            <nav aria-label="Breadcrumb" class="mb-3 text-sm text-white/80">
                <ol class="flex flex-wrap gap-x-2">
                    <li><a class="underline underline-offset-2 hover:text-brand-gold" href="{{ route('home') }}">Home</a> <span aria-hidden="true">/</span></li>
                    <li><a class="underline underline-offset-2 hover:text-brand-gold" href="{{ route('packages.index') }}">Tours</a> <span aria-hidden="true">/</span></li>
                    <li><span aria-current="page">{{ $type->name }}</span></li>
                </ol>
            </nav>
            <h1 class="max-w-3xl text-4xl sm:text-5xl">{{ $base }} Tours of Germany</h1>
            @if($type->summary)<p class="mt-4 max-w-xl text-lg text-white/90">{{ $type->summary }}</p>@endif
            <ul class="mt-6 max-w-xl space-y-3 border-t border-white/25 pt-5" aria-label="Why book with us">
                @foreach([['check', 'Free, no-obligation quote'], ['shield', 'Self drive, chauffeur or coach'], ['adjust', 'Itineraries built around you']] as [$ic, $txt])
                    <li class="flex items-center gap-3">
                        <span class="grid size-10 shrink-0 place-items-center rounded-full bg-brand-gold text-brand-navy shadow-lg shadow-black/20" aria-hidden="true"><x-icon :name="$ic" class="h-5 w-5"/></span>
                        <span class="text-sm font-bold uppercase tracking-[0.12em] text-white sm:text-[0.8rem]">{{ $txt }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
        <div class="mx-auto w-full max-w-xl lg:ml-auto lg:mr-0"><x-hero-quiz/></div>
    </div>
</section>
<div class="container-x grid items-center gap-10 py-12 lg:grid-cols-2 lg:gap-16 lg:py-16">
    <section aria-labelledby="about-type">
        <p class="eyebrow text-brand-red-dark">About this tour</p>
        <h2 id="about-type" class="mt-3">About {{ $base }} tours</h2>
        <span class="flag-rule" aria-hidden="true"></span>
        @if($type->summary)<p class="mt-5 text-lg text-ink-soft">{{ $type->summary }}</p>@endif
        <div class="prose-ggt mt-5">{!! $type->description !!}</div>
        @php $points = array_values(array_filter(array_map('trim', preg_split('/\R/', (string) $type->highlights)))); @endphp
        @if($points)
            <h3 class="mt-8 text-xl">What to expect</h3>
            <ul class="mt-4 space-y-3">
                @foreach($points as $pt)
                    <li class="flex items-start gap-3">
                        <span class="mt-0.5 grid size-7 shrink-0 place-items-center rounded-full bg-brand-gold text-brand-navy" aria-hidden="true"><x-icon name="check" class="size-4"/></span>
                        <span class="font-semibold text-brand-navy">{{ $pt }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
        <x-button class="mt-8" data-title="Get a quote: {{ $type->name }}" data-prefill="Interested in: {{ $base }} tours."
                  @click="$store.modal.show({form:'package_quote', ...$el.dataset})">Get a Quote <x-icon name="arrow-right" class="w-5 h-5"/></x-button>
    </section>
    @if($type->image)
        <div class="overflow-hidden rounded-2xl shadow-card"><x-picture :path="$type->image" sizes="(min-width: 1024px) 46rem, 100vw" class="w-full max-h-[32rem] object-cover"/></div>
    @endif
</div>
<section class="bg-brand-cream py-14" aria-labelledby="type-pkgs">
    <div class="container-x">
        <x-section-heading id="type-pkgs" eyebrow="{{ $type->name }} packages" title="Popular {{ strtolower($base) }} journeys" emphasis="Planned around you."
            intro="A few ideas to start from. Every trip can be adjusted to suit your dates, group size and budget."/>
        @if($packages->isEmpty())<p class="text-ink-soft">No packages listed yet. Ask us for a tailored trip.</p>
        @else<div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">@foreach($packages as $p)<x-package-card :package="$p"/>@endforeach</div>@endif
        <p class="mt-8 text-ink-soft">Other ways to travel:
            @foreach($others as $o)<a class="underline font-bold" href="{{ route('tours.show', $o->slug) }}">{{ $o->name }}</a>{{ $loop->last ? '' : ', ' }}@endforeach
        </p>
    </div>
</section>
@include('partials.sections.banner')
@include('partials.sections.why')
@include('partials.sections.how')
@include('partials.sections.reviews', ['reviewsTitle' => $testimonials->first()?->tour_type_id === $type->id ? 'What travelers say about our '.strtolower($base).' tours' : null])
@include('partials.sections.faq', ['faqTitle' => $ownFaqs ? 'Questions about '.strtolower($base).' tours' : null])
@endsection
