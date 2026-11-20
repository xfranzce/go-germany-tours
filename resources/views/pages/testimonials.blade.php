@extends('layouts.app')

@section('content')
@include('partials.page-header', ['title' => 'What our travelers say', 'lead' => 'Real words from the people we plan trips for.', 'crumbs' => [['Home', route('home')], ['About Us', route('about')], ['Testimonials', null]]])

<section class="bg-brand-cream py-14 sm:py-20" aria-label="Testimonials">
    <div class="container-x">
        @if($testimonials->isEmpty())
            <p class="mx-auto max-w-xl text-center text-lg text-ink-soft">Traveler reviews will appear here soon. In the meantime, tell us about your trip and we will plan it around you.</p>
        @else
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3 lg:gap-8">
                @foreach($testimonials as $r)
                    @php $initials = \Illuminate\Support\Str::of($r->name)->replaceMatches('/[^\p{L}\s]/u', '')->explode(' ')->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode(''); @endphp
                    <figure class="group relative flex flex-col rounded-[var(--radius-card)] border-t-4 border-brand-gold bg-white p-7 shadow-card transition duration-300 hover:-translate-y-1 hover:shadow-xl sm:p-8">
                        <span class="h-8 font-display text-6xl leading-none text-brand-gold" aria-hidden="true">&ldquo;</span>
                        @if($r->is_placeholder)<p class="mb-2 self-start rounded bg-brand-gold px-2 py-0.5 text-xs font-extrabold text-brand-navy">PLACEHOLDER</p>@endif
                        <blockquote class="mt-3 flex-1 font-display text-xl leading-snug text-brand-navy sm:text-2xl">{{ $r->quote }}</blockquote>
                        <figcaption class="mt-6 flex items-center gap-4 border-t border-brand-custard pt-5">
                            <span class="grid size-12 shrink-0 place-items-center rounded-full bg-brand-navy text-sm font-bold tracking-wide text-brand-gold" aria-hidden="true">{{ $initials }}</span>
                            <span class="min-w-0">
                                <span class="block font-bold text-brand-navy">{{ $r->name }}</span>
                                @if($r->location)<span class="block text-sm text-ink-soft">{{ $r->location }}</span>@endif
                                @if($r->source)<span class="mt-1.5 inline-block rounded-full bg-brand-custard-light px-2.5 py-0.5 text-xs font-semibold text-ink-soft">{{ $r->source }}</span>@endif
                            </span>
                        </figcaption>
                    </figure>
                @endforeach
            </div>
        @endif

        <div class="mt-12 text-center">
            <p class="font-display text-2xl text-brand-navy sm:text-3xl">Ready to plan your own trip?</p>
            <p class="mt-4"><x-button @click="$store.modal.show()">Get a Free Quote</x-button></p>
        </div>
    </div>
</section>
@endsection
