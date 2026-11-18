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
    @include('partials.sections.why')
    @include('partials.sections.banner')
    @include('partials.sections.partners')
@elseif(request()->routeIs('team'))
    <section class="py-14 sm:py-20" aria-labelledby="team-title">
        <div class="container-x">
            <div class="mx-auto max-w-3xl text-center">
                <p class="eyebrow centered text-brand-red-dark">Our team</p>
                <h2 id="team-title" class="mt-4">The people behind your trip</h2>
                <span class="flag-rule centered" aria-hidden="true"></span>
                <div class="prose-ggt mx-auto mt-5 text-base sm:text-lg">{!! $content !!}</div>
            </div>
        </div>
    </section>
    @if($members->isNotEmpty())
    <section class="bg-brand-custard-light pb-16 pt-4 sm:pb-24" aria-label="Team members">
        <div class="container-x">
            <ul class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 lg:gap-8">
                @foreach($members as $m)
                    <li class="group card flex flex-col overflow-hidden text-center transition duration-500 hover:-translate-y-1.5 hover:shadow-xl">
                        <div class="relative aspect-[4/3] overflow-hidden bg-gradient-to-br from-brand-navy to-brand-navy-dark">
                            @if($m->image)
                                <x-picture :path="$m->image" sizes="(min-width: 1024px) 28rem, (min-width: 640px) 45vw, 92vw" class="h-full w-full object-cover object-top transition duration-[1200ms] ease-out group-hover:scale-105"/>
                            @else
                                <span class="grid h-full w-full place-items-center text-brand-gold/70" aria-hidden="true"><x-icon name="users" class="h-20 w-20"/></span>
                            @endif
                            <span class="absolute inset-x-0 bottom-0 flex h-1" aria-hidden="true"><span class="flex-1 bg-brand-navy"></span><span class="flex-1 bg-brand-red"></span><span class="flex-1 bg-brand-gold"></span></span>
                        </div>
                        <div class="flex flex-1 flex-col p-6">
                            <h3 class="text-xl">{{ $m->name }}</h3>
                            @if($m->role)<p class="mt-1 text-xs font-bold uppercase tracking-[0.16em] text-brand-red-dark">{{ $m->role }}</p>@endif
                            @if($m->bio)<p class="mt-3 text-ink-soft">{{ $m->bio }}</p>@endif
                        </div>
                    </li>
                @endforeach
            </ul>
            <p class="mt-12 text-center"><x-button @click="$store.modal.show()">Get a Free Quote</x-button></p>
        </div>
    </section>
    @endif
@else
    <div class="container-x py-12"><div class="prose-ggt text-lg">{!! $content !!}</div>
        <p class="mt-8"><x-button @click="$store.modal.show()">Get a Free Quote</x-button></p>
    </div>
@endif
@endsection
