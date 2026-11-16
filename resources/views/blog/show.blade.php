@extends('layouts.app')

@section('content')
@include('partials.page-header', ['title' => $post->title, 'lead' => $post->excerpt, 'crumbs' => [['Home', route('home')], ['Travel guides', route('blog.index')], [$post->title, null]]])
<article class="container-x py-12">
    @if($post->image)
        <div class="mb-8 aspect-[21/9] overflow-hidden rounded-[var(--radius-card)] bg-brand-custard shadow-card">
            <x-picture :path="$post->image" priority hires sizes="(min-width: 1390px) 1340px, 100vw" class="h-full w-full object-cover"/>
        </div>
    @endif
    <p class="text-sm text-ink-soft mb-6">Published {{ $post->published_at->format('F j, Y') }} &middot; {{ $post->readingMinutes() }} min read</p>
    <div class="prose-ggt text-lg">{!! $post->body !!}</div>
    <p class="mt-10"><x-button @click="$store.modal.show()">Get a Free Quote</x-button></p>
</article>
@endsection
