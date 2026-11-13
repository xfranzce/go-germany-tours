@extends('layouts.app')

@php
    $heroPath = $page->data['hero_image'] ?? null;
    $heroMedia = \App\Models\Media::forPath($heroPath);
    $icons = ['car' => '🚗', 'steering' => '🧭', 'bus' => '🚌'];
@endphp

@if($heroMedia)
    @push('head')
        <link rel="preload" as="image" href="{{ $heroMedia->url(1280) }}" imagesrcset="{{ $heroMedia->srcset() }}" imagesizes="100vw" fetchpriority="high">
    @endpush
@endif

@section('content')
{{-- 3. HERO --}}
<section class="relative isolate bg-brand-navy text-white" aria-labelledby="hero-title">
    @if($heroMedia)
        <x-picture :path="$heroPath" priority sizes="100vw" class="absolute inset-0 -z-20 w-full h-full object-cover"/>
    @endif
    <div class="absolute inset-0 -z-10 bg-gradient-to-r from-brand-navy-dark/95 via-brand-navy/85 to-brand-navy/55" aria-hidden="true"></div>

    <div class="container-x grid gap-10 lg:grid-cols-[1.1fr_0.9fr] lg:items-center py-10 sm:py-14 lg:py-20">
        <div>
            <p class="inline-block bg-brand-gold text-brand-navy font-extrabold text-xs sm:text-sm uppercase tracking-widest rounded-full px-3 py-1">Germany tours for US travelers</p>
            <h1 id="hero-title" class="mt-4 text-4xl sm:text-5xl lg:text-6xl font-extrabold">{{ $page->text('hero_h1', 'Tailor-Made Germany Tours Through Its Fairytale Towns') }}</h1>
            <p class="mt-5 text-lg sm:text-xl text-white/90 max-w-xl">{{ $page->text('hero_sub', 'Self drive, chauffeur driven or small-group coach. Explore Quedlinburg, Saxon Switzerland, Cochem, Hiddensee and Gengenbach, planned around you.') }}</p>

            <ul class="mt-7 grid gap-3 sm:grid-cols-3 max-w-2xl">
                @foreach([['check', 'Free, no-obligation quote'], ['shield', 'Self drive, chauffeur or coach'], ['adjust', 'Itineraries built around you']] as [$ic, $txt])
                    <li class="flex items-center gap-2.5 bg-white/10 border border-white/20 rounded-xl px-3 py-2.5 font-bold text-sm">
                        <span class="grid place-items-center w-8 h-8 shrink-0 rounded-full bg-brand-gold text-brand-navy"><x-icon :name="$ic" class="w-5 h-5"/></span>{{ $txt }}
                    </li>
                @endforeach
            </ul>
        </div>

        <x-hero-quiz/>
    </div>
</section>

{{-- 4. WHY TRAVELLERS CHOOSE US --}}
<section class="py-16 sm:py-20 bg-brand-custard-light" aria-labelledby="why-title">
    <div class="container-x">
        <x-section-heading id="why-title" eyebrow="Why {{ $site['brand_name'] }}" :title="$page->text('why_title', 'Why travelers choose us')"
            :intro="$page->text('why_intro', 'Germany is easier to enjoy when the planning is done for you.')"/>
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @foreach([
                ['adjust', 'Choose how you travel', 'Drive yourself, relax with a private driver, or join a small-group coach tour.'],
                ['pin', 'Beyond the usual stops', 'Quedlinburg, Saxon Switzerland, Cochem, Hiddensee and Gengenbach: towns and landscapes many itineraries skip.'],
                ['check', 'Built around you', 'Tell us your dates, group size and budget and we shape the trip to fit.'],
                ['chat', 'A real person replies', 'Send your details and a member of the team follows up with your quote.'],
            ] as [$ic, $t, $d])
                <div class="bg-white rounded-2xl p-6 shadow-card text-center">
                    <span class="mx-auto grid place-items-center w-14 h-14 rounded-full bg-brand-gold text-brand-navy"><x-icon :name="$ic" class="w-7 h-7"/></span>
                    <h3 class="text-xl mt-4">{{ $t }}</h3>
                    <p class="mt-2 text-ink-soft">{{ $d }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- 5. TOUR TYPES --}}
<section class="py-16 sm:py-20" aria-labelledby="tours-title" id="tours">
    <div class="container-x">
        <x-section-heading id="tours-title" eyebrow="Ways to travel" :title="$page->text('tours_title', 'Pick the way you like to travel')"
            :intro="$page->text('tours_intro', 'The same beautiful places, three very different experiences.')"/>
        <div class="grid gap-6 md:grid-cols-3">
            @foreach($tourTypes as $t)
                <article class="card flex flex-col">
                    <a href="{{ route('tours.show', $t->slug) }}" class="block aspect-[3/2] bg-brand-custard" tabindex="-1" aria-hidden="true">
                        <x-picture :path="$t->image" sizes="(min-width: 768px) 24rem, 92vw" class="w-full h-full object-cover"/>
                    </a>
                    <div class="p-6 flex flex-col flex-1">
                        <h3 class="text-2xl"><span aria-hidden="true">{{ $icons[$t->icon] ?? '🧭' }}</span> <a href="{{ route('tours.show', $t->slug) }}" class="hover:underline">{{ $t->name }}</a></h3>
                        <p class="mt-2 text-ink-soft flex-1">{{ $t->summary }}</p>
                        <div class="mt-5 grid gap-2 sm:grid-cols-2 md:grid-cols-1 xl:grid-cols-2">
                            <x-button size="sm" data-title="Get a quote: {{ $t->name }}" data-prefill="Interested in: {{ $t->name }} tours."
                                      @click="$store.modal.show({form:'package_quote', ...$el.dataset})">Get a Quote</x-button>
                            <x-button variant="outline" size="sm" data-title="Ask us a question" data-subtitle="{{ $t->name }} tours" data-prefill="Question about {{ $t->name }} tours: "
                                      @click="$store.modal.show({form:'package_question', ...$el.dataset})">Ask Us a Question</x-button>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>

{{-- 6. FEATURED PACKAGES --}}
@if($featured->isNotEmpty())
<section class="py-16 sm:py-20 bg-brand-custard" aria-labelledby="featured-title">
    <div class="container-x">
        <x-section-heading id="featured-title" eyebrow="Featured packages" :title="$page->text('featured_title', 'Popular ways to see Germany')"
            :intro="$page->text('featured_intro', 'A few ideas to start from. Every trip can be adjusted to suit you.')"/>
        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach($featured as $package)<x-package-card :package="$package"/>@endforeach
        </div>
        <p class="mt-10 text-center"><x-button :href="route('packages.index')" variant="outline">See all packages <x-icon name="arrow-right" class="w-5 h-5"/></x-button></p>
    </div>
</section>
@endif

{{-- 7. HOW IT WORKS --}}
<section class="py-16 sm:py-20" aria-labelledby="how-title">
    <div class="container-x">
        <x-section-heading id="how-title" eyebrow="How it works" :title="$page->text('how_title', 'Your trip in three simple steps')"/>
        <ol class="grid gap-8 md:grid-cols-3">
            @foreach([['Tell us what you want', 'Answer a few quick questions about where, when and how you like to travel. It takes under 30 seconds.'], ['Get your tailored quote', 'We put together a quote that fits your dates, group size and budget.'], ['Book and go', 'Happy with the plan? Confirm it and we handle the details so you can enjoy Germany.']] as $n => [$t, $d])
                <li class="relative bg-brand-custard-light rounded-2xl p-6 pt-10">
                    <span class="absolute -top-5 left-6 grid place-items-center w-11 h-11 rounded-full bg-brand-red text-white font-display font-extrabold text-xl">{{ $n + 1 }}</span>
                    <h3 class="text-xl">{{ $t }}</h3>
                    <p class="mt-2 text-ink-soft">{{ $d }}</p>
                </li>
            @endforeach
        </ol>
    </div>
</section>

{{-- 8. TESTIMONIALS (placeholders until real reviews exist) --}}
@if($testimonials->isNotEmpty())
<section class="py-16 sm:py-20 bg-brand-navy text-white" aria-labelledby="reviews-title">
    <div class="container-x">
        <x-section-heading id="reviews-title" light eyebrow="Guest reviews" :title="$page->text('testimonials_title', 'What travelers say')"/>
        <div class="grid gap-6 md:grid-cols-3">
            @foreach($testimonials as $r)
                <figure class="bg-white/10 border border-white/20 rounded-2xl p-6">
                    @if($r->is_placeholder)<p class="inline-block bg-brand-gold text-brand-navy text-xs font-extrabold px-2 py-0.5 rounded">PLACEHOLDER</p>@endif
                    <blockquote class="mt-2 text-white/95">{{ $r->quote }}</blockquote>
                    <figcaption class="mt-4 font-bold">{{ $r->name }}<span class="block font-normal text-white/75 text-sm">{{ $r->location }}</span></figcaption>
                </figure>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- 9. FAQ --}}
@if($faqs->isNotEmpty())
<section class="py-16 sm:py-20" aria-labelledby="faq-title" id="faq">
    <div class="container-x max-w-3xl">
        <x-section-heading id="faq-title" eyebrow="FAQ" :title="$page->text('faq_title', 'Frequently asked questions')"/>
        <div class="space-y-3">
            @foreach($faqs as $faq)<x-faq-item :question="$faq->question" :open="$loop->first">{{ $faq->answer }}</x-faq-item>@endforeach
        </div>
    </div>
</section>
@endif

{{-- 10. FINAL CTA --}}
<section class="py-16 bg-brand-red text-white" aria-labelledby="cta-title">
    <div class="container-x text-center max-w-3xl">
        <h2 id="cta-title" class="text-3xl sm:text-4xl">{{ $page->text('cta_title', 'Ready to see Germany your way?') }}</h2>
        <p class="mt-3 text-lg text-white/95">{{ $page->text('cta_text', 'Get a free, no-obligation quote in under a minute.') }}</p>
        <div class="mt-7 flex flex-wrap justify-center gap-3">
            <button type="button" class="btn btn-gold text-lg" @click="$store.modal.show()">Start Your Adventure</button>
            @php $tel = preg_replace('/[^+\d]/', '', $site['phone'] ?? ''); @endphp
            @if($tel)<a class="btn btn-outline-light text-lg" href="tel:{{ $tel }}"><x-icon name="phone" class="w-5 h-5"/> {{ $site['phone_display'] ?: $site['phone'] }}</a>@endif
        </div>
    </div>
</section>
@endsection
