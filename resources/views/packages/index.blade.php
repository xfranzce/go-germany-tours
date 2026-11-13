@extends('layouts.app')

@section('content')
@include('partials.page-header', [
    'title' => 'Germany Tour Packages',
    'lead' => 'Self drive, chauffeur driven and coach tours. Filter by tour type or destination, then ask for a quote.',
    'crumbs' => [['Home', route('home')], ['Packages', null]],
])

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
