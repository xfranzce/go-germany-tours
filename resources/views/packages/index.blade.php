@extends('layouts.app')

@section('content')
@php $heroImg = ($type?->image) ?: $types->first(fn ($t) => $t->image)?->image; @endphp
<section class="relative isolate overflow-hidden bg-brand-navy text-white">
    @if($heroImg)<x-picture :path="$heroImg" priority sizes="100vw" class="absolute inset-0 -z-20 h-full w-full object-cover opacity-25"/>@endif
    <div class="absolute inset-0 -z-10 bg-gradient-to-r from-brand-navy-dark/90 via-brand-navy/70 to-brand-navy/50" aria-hidden="true"></div>
    <div class="container-x grid items-center gap-10 pt-28 pb-12 sm:pt-36 sm:pb-16 lg:grid-cols-[1.1fr_0.9fr] lg:gap-14">
        <div>
            <nav aria-label="Breadcrumb" class="mb-3 text-sm text-white/80">
                <ol class="flex flex-wrap gap-x-2">
                    <li><a class="underline underline-offset-2 hover:text-brand-gold" href="{{ route('home') }}">Home</a> <span aria-hidden="true">/</span></li>
                    <li><span aria-current="page">Packages</span></li>
                </ol>
            </nav>
            <h1 class="max-w-3xl text-4xl sm:text-5xl">Germany Tour Packages</h1>
            <p class="mt-4 max-w-xl text-lg text-white/90">Self drive, chauffeur driven and coach tours. Filter by tour type or destination, then ask for a quote.</p>
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

<div class="container-x py-10">
    <form method="GET" action="{{ route('packages.index') }}" class="card p-4 sm:p-5 grid gap-4 sm:grid-cols-[1fr_1fr_auto] items-end" aria-label="Filter packages">
        <div>
            <label for="f-type" class="field-label">Tour type</label>
            <select id="f-type" name="type" class="field-input">
                <option value="">All tour types</option>
                @foreach($types as $t)<option value="{{ $t->slug }}" @selected($type?->id === $t->id)>{{ $t->name }}</option>@endforeach
            </select>
        </div>
        <div>
            <label for="f-dest" class="field-label">Destination</label>
            <select id="f-dest" name="destination" class="field-input">
                <option value="">All destinations</option>
                @foreach($destinations as $d)<option value="{{ $d->slug }}" @selected($dest?->id === $d->id)>{{ $d->name }}</option>@endforeach
            </select>
        </div>
        <div class="flex gap-2">
            <button class="btn btn-primary" type="submit">Filter</button>
            @if($type || $dest)<a class="btn btn-outline" href="{{ route('packages.index') }}">Reset</a>@endif
        </div>
    </form>

    <p class="mt-6 text-ink-soft" role="status">{{ $packages->count() }} {{ \Illuminate\Support\Str::plural('package', $packages->count()) }} found</p>

    @if($packages->isEmpty())
        <div class="mt-6 card p-8 text-center">
            <p class="text-lg font-bold">No packages match those filters yet.</p>
            <p class="mt-2 text-ink-soft">We build trips to order, so tell us what you have in mind.</p>
            <x-button class="mt-5" @click="$store.modal.show()">Ask for a custom quote</x-button>
        </div>
    @else
        <div class="mt-6 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach($packages as $package)<x-package-card :package="$package" :level="2"/>@endforeach
        </div>
    @endif
</div>
@endsection
