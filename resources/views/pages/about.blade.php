@extends('layouts.app')

@section('content')
@include('partials.page-header', ['title' => $page->title, 'crumbs' => [['Home', route('home')], [$page->title, null]]])
<div class="container-x py-12"><div class="prose-ggt text-lg">{!! $content !!}</div>
    <p class="mt-8"><x-button @click="$store.modal.show()">Get a Free Quote</x-button></p>
</div>
@if(request()->routeIs('about')){{-- the team page shares this view --}}
@include('partials.sections.why')
@endif
@endsection
