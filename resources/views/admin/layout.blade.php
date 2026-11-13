<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Admin') | {{ $site['brand_name'] }} admin</title>
    @vite(['resources/css/app.css', 'resources/js/admin.js'])
</head>
<body class="bg-gray-100 min-h-screen text-brand-navy" x-data="{ nav: false }">
@php
    $links = [
        ['admin.dashboard', 'Dashboard'], ['admin.enquiries.index', 'Enquiries'], ['admin.packages.index', 'Packages'],
        ['admin.tour-types.index', 'Tour types'], ['admin.destinations.index', 'Destinations'], ['admin.quiz.index', 'Hero quiz'],
        ['admin.pages.index', 'Pages'], ['admin.testimonials.index', 'Testimonials'], ['admin.faqs.index', 'FAQs'],
        ['admin.posts.index', 'Travel guides'], ['admin.media.index', 'Media'], ['admin.settings.edit', 'Site settings'], ['admin.users.index', 'Admin users'],
    ];
@endphp
<div class="lg:flex min-h-screen">
    <aside class="lg:w-60 bg-brand-navy text-white lg:min-h-screen shrink-0">
        <div class="flex items-center justify-between px-4 py-3">
            <a href="{{ route('admin.dashboard') }}" class="font-display font-extrabold text-lg">{{ $site['brand_name'] }}</a>
            <button type="button" class="lg:hidden p-2" @click="nav = !nav" :aria-expanded="nav"><span class="sr-only">Menu</span><x-icon name="menu" class="w-6 h-6"/></button>
        </div>
        <nav class="px-2 pb-4 lg:block" :class="nav ? 'block' : 'hidden'" aria-label="Admin">
            @foreach($links as [$route, $label])
                @php $active = request()->routeIs(str_replace(['.index', '.edit'], '.*', $route)); @endphp
                <a href="{{ route($route) }}" class="block px-3 py-2 rounded-lg font-bold text-sm {{ $active ? 'bg-brand-gold text-brand-navy' : 'hover:bg-white/10' }}" @if($active) aria-current="page" @endif>{{ $label }}</a>
            @endforeach
            <a href="{{ url('/') }}" target="_blank" rel="noopener" class="block px-3 py-2 mt-3 text-sm text-white/80 underline">View website</a>
            <form method="POST" action="{{ route('admin.logout') }}" class="px-3 mt-2">@csrf
                <button class="text-sm underline text-white/80">Log out ({{ auth('admin')->user()->name }})</button>
            </form>
        </nav>
    </aside>

    <main class="flex-1 min-w-0 p-4 sm:p-8">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
            <h1 class="text-2xl sm:text-3xl">@yield('heading')</h1>
            <div class="flex gap-2 flex-wrap">@yield('actions')</div>
        </div>

        @if(session('status'))<div class="mb-4 rounded-lg bg-green-100 border border-green-600 text-green-900 font-bold p-3" role="status">{{ session('status') }}</div>@endif
        @if($errors->any())
            <div class="mb-4 rounded-lg bg-red-50 border border-brand-red text-brand-red-dark p-3" role="alert">
                <p class="font-bold">Please fix the following:</p>
                <ul class="list-disc pl-5 text-sm">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
            </div>
        @endif

        @yield('content')
    </main>
</div>
</body>
</html>
