@extends('layouts.app')

@section('content')
@include('partials.page-header', ['title' => 'Photo credits', 'crumbs' => [['Home', route('home')], ['Photo credits', null]]])
<div class="container-x py-12">
    <p class="max-w-2xl text-lg">Photographs on this site are used under open licences. Thank you to the photographers below.</p>
    <ul class="mt-6 space-y-3 max-w-3xl">
        @foreach($credits as $c)
            <li class="card p-4">
                <a class="font-bold underline" href="{{ $c['page'] }}" target="_blank" rel="noopener">{{ $c['title'] }}</a>
                <span class="block text-ink-soft">By {{ $c['artist'] }} &middot; <a class="underline" href="{{ $c['license_url'] ?: $c['page'] }}" target="_blank" rel="noopener">{{ $c['license'] }}</a> &middot; via Wikimedia Commons</span>
            </li>
        @endforeach
    </ul>
</div>
@endsection
