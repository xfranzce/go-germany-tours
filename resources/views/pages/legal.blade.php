@extends('layouts.app')

@section('content')
@include('partials.page-header', ['title' => $page->title, 'crumbs' => [['Home', route('home')], [$page->title, null]]])
<div class="container-x py-12">
    <div class="prose-ggt">{!! $content !!}</div>
    <p class="mt-8 text-sm text-ink-soft">Version {{ \App\Models\Setting::get('terms_version', config('gogermany.terms_version')) }}</p>
</div>
@endsection
