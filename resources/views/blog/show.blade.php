@extends('layouts.app')

@section('content')
@include('partials.page-header', ['title' => $post->title, 'lead' => $post->excerpt, 'crumbs' => [['Home', route('home')], ['Travel guides', route('blog.index')], [$post->title, null]]])
<article class="container-x py-12">
    <p class="text-sm text-ink-soft mb-6">Published {{ $post->published_at->format('F j, Y') }}</p>
    <div class="prose-ggt text-lg">{!! $post->body !!}</div>
    <p class="mt-10"><x-button @click="$store.modal.show()">Get a Free Quote</x-button></p>
</article>
@endsection
