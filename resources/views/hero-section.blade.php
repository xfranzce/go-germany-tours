@extends('layouts.app')

@php
    $heroMedia = \App\Models\Media::forPath($page->data['hero_image'] ?? null);
@endphp

@section('content')
@include('partials.home.hero-section-page')
@endsection
