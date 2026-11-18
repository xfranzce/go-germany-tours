@extends('layouts.app')

@section('content')
@include('partials.page-header', ['title' => 'Germany Travel Guides', 'lead' => 'Ideas and tips for planning your trip.', 'crumbs' => [['Home', route('home')], ['Travel guides', null]]])
<div class="container-x py-12 sm:py-16">
    @forelse($posts as $post)
        @if($loop->first && $posts->currentPage() === 1)
            {{-- Featured: latest guide, large image beside the text --}}
            <article class="group mb-10 grid overflow-hidden rounded-[var(--radius-card)] bg-white shadow-card lg:grid-cols-[1.25fr_1fr]">
                <a href="{{ route('blog.show', $post->slug) }}" class="relative block min-h-[16rem] overflow-hidden bg-brand-custard lg:min-h-[26rem]" tabindex="-1" aria-hidden="true">
                    <x-picture :path="$post->image" priority hires sizes="(min-width: 1024px) 55vw, 100vw" class="absolute inset-0 h-full w-full object-cover transition duration-[1400ms] ease-out group-hover:scale-105"/>
                    <span class="absolute left-5 top-5 rounded-full bg-brand-gold px-3 py-1 text-[0.7rem] font-extrabold uppercase tracking-wider text-brand-navy shadow">{{ $post->category ?: 'Latest guide' }}</span>
                </a>
                <div class="flex flex-col justify-center p-7 sm:p-10">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-ink-soft">{{ $post->published_at->format('F j, Y') }} <span aria-hidden="true">&middot;</span> {{ $post->readingMinutes() }} min read</p>
                    <h2 class="mt-3"><a class="transition hover:text-brand-red" href="{{ route('blog.show', $post->slug) }}">{{ $post->title }}</a></h2>
                    <span class="flag-rule" aria-hidden="true"></span>
                    <p class="mt-5 text-base sm:text-lg text-ink-soft">{{ $post->excerpt }}</p>
                    <p class="mt-7"><x-button :href="route('blog.show', $post->slug)">Read the guide <x-icon name="arrow-right" class="h-5 w-5"/><span class="sr-only">: {{ $post->title }}</span></x-button></p>
                </div>
            </article>
            <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
        @else
            @if($loop->first)<div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">@endif
            <article class="group card flex flex-col overflow-hidden transition duration-500 hover:-translate-y-1.5 hover:shadow-xl">
                <a href="{{ route('blog.show', $post->slug) }}" class="relative block aspect-[3/2] overflow-hidden bg-brand-custard" tabindex="-1" aria-hidden="true">
                    <x-picture :path="$post->image" sizes="(min-width: 1024px) 28rem, (min-width: 768px) 45vw, 92vw" class="h-full w-full object-cover transition duration-[1200ms] ease-out group-hover:scale-105"/>
                    @if($post->category)<span class="absolute left-4 top-4 rounded-full bg-brand-gold px-3 py-1 text-[0.7rem] font-extrabold uppercase tracking-wider text-brand-navy shadow">{{ $post->category }}</span>@endif
                </a>
                <div class="flex flex-1 flex-col p-6 sm:p-7">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-ink-soft">{{ $post->published_at->format('M j, Y') }} <span aria-hidden="true">&middot;</span> {{ $post->readingMinutes() }} min read</p>
                    <h2 class="mt-3 text-2xl"><a class="transition hover:text-brand-red" href="{{ route('blog.show', $post->slug) }}">{{ $post->title }}</a></h2>
                    <p class="mt-3 flex-1 text-ink-soft">{{ $post->excerpt }}</p>
                    <a class="mt-5 inline-flex items-center gap-2 text-xs font-bold uppercase tracking-[0.18em] text-brand-red-dark" href="{{ route('blog.show', $post->slug) }}">Read the guide <x-icon name="arrow-right" class="h-4 w-4 transition group-hover:translate-x-1"/><span class="sr-only">: {{ $post->title }}</span></a>
                </div>
            </article>
        @endif
        @if($loop->last)</div>@endif
    @empty
        <p class="text-ink-soft">No guides yet.</p>
    @endforelse
    <div class="mt-10">{{ $posts->links() }}</div>
</div>
@endsection
