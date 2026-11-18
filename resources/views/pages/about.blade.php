@extends('layouts.app')

@section('content')
@include('partials.page-header', ['title' => $page->title, 'crumbs' => [['Home', route('home')], [$page->title, null]]])
@if(request()->routeIs('about'))
    @php $aboutImg = \App\Models\Destination::active()->get()->first(fn ($d) => $d->image)?->image ?? \App\Models\TourType::active()->get()->first(fn ($t) => $t->image)?->image; @endphp
    <section class="py-14 sm:py-20" aria-labelledby="about-title">
        <div class="container-x grid items-center gap-10 lg:grid-cols-2 lg:gap-16">
            @if($aboutImg)
                <div class="overflow-hidden rounded-2xl shadow-card"><x-picture :path="$aboutImg" sizes="(min-width: 1024px) 46rem, 100vw" class="aspect-[4/3] w-full object-cover"/></div>
            @endif
            <div class="{{ $aboutImg ? '' : 'lg:col-span-2 max-w-3xl' }}">
                <p class="eyebrow text-brand-red-dark">About {{ $site['brand_name'] }}</p>
                <h2 id="about-title" class="mt-4">Germany vacations planned around you</h2>
                <span class="flag-rule" aria-hidden="true"></span>
                <div class="prose-ggt mt-5 text-base sm:text-lg">{!! $content !!}</div>
                <p class="mt-8"><x-button @click="$store.modal.show()">Get a Free Quote</x-button></p>
            </div>
        </div>
    </section>
    {{-- the team page shares this view --}}
    @include('partials.sections.banner')
    @include('partials.sections.why')
    @include('partials.sections.partners')
@else
    <div class="container-x py-12"><div class="prose-ggt text-lg">{!! $content !!}</div>
        <p class="mt-8"><x-button @click="$store.modal.show()">Get a Free Quote</x-button></p>
    </div>
@endif
@endsection
