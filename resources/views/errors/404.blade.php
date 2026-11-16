@extends('layouts.app', ['seo' => \App\Support\Seo::make('Page not found', 'Sorry, we could not find that page.', ['noindex' => true])])

@section('content')
<section class="py-24 bg-brand-custard-light">
    <div class="container-x max-w-xl text-center">
        <p class="font-display font-extrabold text-7xl text-brand-red">404</p>
        <h1 class="text-4xl sm:text-5xl mt-2">We could not find that page</h1>
        <p class="mt-3 text-ink-soft text-lg">It may have moved. Try the home page or browse our packages.</p>
        <p class="mt-6 flex justify-center gap-3 flex-wrap"><x-button :href="route('home')">Home</x-button><x-button :href="route('packages.index')" variant="outline">Packages</x-button></p>
    </div>
</section>
@endsection
