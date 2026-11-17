@extends('layouts.app')

@section('content')
@include('partials.page-header', [
    'title' => $type->name.' Tours of Germany',
    'lead' => $type->summary,
    'crumbs' => [['Home', route('home')], ['Tours', route('packages.index')], [$type->name, null]],
])
<div class="container-x py-12 grid gap-10 lg:grid-cols-[1fr_22rem]">
    <div>
        @if($type->image)
            <div class="rounded-2xl overflow-hidden mb-8"><x-picture :path="$type->image" priority sizes="(min-width: 1024px) 46rem, 100vw" class="w-full max-h-[26rem] object-cover"/></div>
        @endif
        <section aria-labelledby="about-type">
            <p class="eyebrow text-brand-red-dark">About this tour</p>
            <h2 id="about-type" class="mt-3">About {{ $type->name }} tours</h2>
            <span class="flag-rule" aria-hidden="true"></span>
            @if($type->summary)<p class="mt-5 text-lg text-ink-soft">{{ $type->summary }}</p>@endif
            <div class="prose-ggt mt-5">{!! $type->description !!}</div>
        </section>
    </div>
    <aside class="card p-5 self-start border-4 border-brand-tangerine">
        <h2 class="text-2xl">Get a {{ strtolower($type->name) }} quote</h2>
        <p class="mt-2 text-ink-soft">Free and with no obligation.</p>
        <x-button class="w-full mt-4" data-title="Get a quote: {{ $type->name }}" data-prefill="Interested in: {{ $type->name }} tours."
                  @click="$store.modal.show({form:'package_quote', ...$el.dataset})">Get a Quote</x-button>
        <x-button variant="outline" class="w-full mt-2" data-title="Ask us a question" data-subtitle="{{ $type->name }} tours" data-prefill="Question about {{ $type->name }} tours: "
                  @click="$store.modal.show({form:'package_question', ...$el.dataset})">Ask Us a Question</x-button>
    </aside>
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
