@extends('layouts.app')

@section('content')
@include('partials.page-header', ['title' => 'Photo credits', 'crumbs' => [['Home', route('home')], ['Photo credits', null]]])
<div class="container-x py-12">
    <p class="max-w-2xl text-lg">Photographs on this site are used under open licences. Thank you to the photographers below. A few illustrative images (tour styles) are AI-generated and are labelled as such.</p>
    <ul class="mt-6 space-y-3 max-w-3xl">
        @foreach($credits as $c)
            @if(($c['type'] ?? '') === 'ai')
            <li class="card p-4">
                <span class="font-bold">{{ $c['title'] }}</span>
                <span class="block text-ink-soft">AI-generated illustration &middot; made with {{ $c['tool'] }}. Not a photograph of a real trip.</span>
            </li>
            @continue
            @endif
            <li class="card p-4">
                <a class="font-bold underline" href="{{ $c['page'] }}" target="_blank" rel="noopener">{{ $c['title'] }}</a>
                <span class="block text-ink-soft">By {{ $c['artist'] }} &middot; <a class="underline" href="{{ $c['license_url'] ?: $c['page'] }}" target="_blank" rel="noopener">{{ $c['license'] }}</a> &middot; via Wikimedia Commons</span>
            </li>
        @endforeach
    </ul>
</div>
@endsection
