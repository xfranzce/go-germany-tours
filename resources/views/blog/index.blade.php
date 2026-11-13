@extends('layouts.app')

@section('content')
@include('partials.page-header', ['title' => 'Germany Travel Guides', 'lead' => 'Ideas and tips for planning your trip.', 'crumbs' => [['Home', route('home')], ['Travel guides', null]]])
<div class="container-x py-12">
    <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
        @forelse($posts as $post)
            <article class="card p-6">
                <h2 class="text-xl"><a class="hover:underline" href="{{ route('blog.show', $post->slug) }}">{{ $post->title }}</a></h2>
                <p class="mt-2 text-ink-soft">{{ $post->excerpt }}</p>
                <p class="mt-3"><a class="font-bold underline" href="{{ route('blog.show', $post->slug) }}">Read the guide <span class="sr-only">{{ $post->title }}</span></a></p>
            </article>
        @empty
            <p class="text-ink-soft">No guides yet.</p>
        @endforelse
    </div>
    <div class="mt-8">{{ $posts->links() }}</div>
</div>
@endsection
