@extends('layouts.app')

@section('content')
<section class="relative isolate overflow-hidden bg-brand-navy text-white">
    @if($type->image)<x-picture :path="$type->image" priority sizes="100vw" class="absolute inset-0 -z-20 h-full w-full object-cover opacity-25"/>@endif
    <div class="absolute inset-0 -z-10 bg-gradient-to-r from-brand-navy-dark/90 via-brand-navy/70 to-brand-navy/50" aria-hidden="true"></div>
    <div class="container-x grid items-center gap-10 pt-28 pb-12 sm:pt-36 sm:pb-16 lg:grid-cols-[1.1fr_0.9fr] lg:gap-14">
        <div>
            <nav aria-label="Breadcrumb" class="mb-3 text-sm text-white/80">
                <ol class="flex flex-wrap gap-x-2">
                    <li><a class="underline underline-offset-2 hover:text-brand-gold" href="{{ route('home') }}">Home</a> <span aria-hidden="true">/</span></li>
                    <li><a class="underline underline-offset-2 hover:text-brand-gold" href="{{ route('packages.index') }}">Tours</a> <span aria-hidden="true">/</span></li>
                    <li><span aria-current="page">{{ $type->name }}</span></li>
                </ol>
            </nav>
            <h1 class="max-w-3xl text-4xl sm:text-5xl">{{ $type->name }} Tours of Germany</h1>
            @if($type->summary)<p class="mt-4 max-w-xl text-lg text-white/90">{{ $type->summary }}</p>@endif
        </div>
        <div class="mx-auto w-full max-w-xl lg:ml-auto lg:mr-0"><x-hero-quiz/></div>
    </div>
</section>
<div class="container-x grid items-center gap-10 py-12 lg:grid-cols-2 lg:gap-16 lg:py-16">
    <section aria-labelledby="about-type">
        <p class="eyebrow text-brand-red-dark">About this tour</p>
        <h2 id="about-type" class="mt-3">About {{ $type->name }} tours</h2>
        <span class="flag-rule" aria-hidden="true"></span>
        @if($type->summary)<p class="mt-5 text-lg text-ink-soft">{{ $type->summary }}</p>@endif
        <div class="prose-ggt mt-5">{!! $type->description !!}</div>
    </section>
    @if($type->image)
        <div class="overflow-hidden rounded-2xl shadow-card"><x-picture :path="$type->image" sizes="(min-width: 1024px) 46rem, 100vw" class="w-full max-h-[32rem] object-cover"/></div>
    @endif
</div>
<section class="bg-brand-custard py-14" aria-labelledby="type-pkgs">
    <div class="container-x">
        <h2 id="type-pkgs" class="text-2xl sm:text-3xl mb-6">{{ $type->name }} packages</h2>
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
@include('partials.sections.reviews')
@include('partials.sections.faq', ['faqTitle' => 'Questions about '.strtolower($type->name).' tours'])
@endsection
