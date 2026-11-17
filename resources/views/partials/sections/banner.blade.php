{{-- 4b. ALPS STATEMENT BANNER: full-bleed mountain photo, slow zoom, frosted panel, reveal on scroll --}}
@php $alps = \App\Models\Media::query()->where('path', 'like', 'media/allgau-alps-%')->first(); @endphp
@if($alps)
<section class="relative isolate overflow-hidden flex items-center min-h-[36rem] sm:min-h-[46rem] text-white text-center" aria-labelledby="banner-title"
         x-data="{ shown: false }" x-init="const o = new IntersectionObserver(([e]) => { if (e.isIntersecting) { shown = true; o.disconnect() } }, { threshold: 0.2 }); o.observe($el)">
    <div class="absolute inset-0 -z-20 bg-brand-navy-dark" aria-hidden="true">
        <x-picture :path="$alps->path" hires sizes="100vw" class="banner-zoom w-full h-full object-cover"/>
    </div>
    <x-video-bg src="banner.mp4" class="absolute inset-0 -z-[15] w-full h-full object-cover"/>
    <div class="absolute inset-0 -z-10 bg-gradient-to-b from-brand-navy-dark/55 via-brand-navy-dark/15 to-brand-navy-dark/75" aria-hidden="true"></div>
    <div class="absolute inset-0 -z-10 bg-[radial-gradient(ellipse_70%_60%_at_50%_50%,rgb(0_31_48/0.6),transparent)]" aria-hidden="true"></div>

    <div class="container-x max-w-4xl py-24 sm:py-32 transition duration-1000 ease-out [text-shadow:0_2px_28px_rgb(0_20_30/0.75)]"
         :class="shown ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10'">
        <p class="eyebrow centered text-brand-gold">Germany, your way</p>
        <h2 id="banner-title" class="mt-4 text-white">From Alpine peaks to
            <em class="block italic font-medium text-brand-gold">fairytale towns.</em></h2>
        <span class="flag-rule centered" aria-hidden="true"></span>
        <p class="mt-5 mx-auto max-w-2xl text-base sm:text-lg text-white">Tell us how you like to travel and we will shape the journey around you.</p>
        <ul class="mt-6 flex flex-wrap items-center justify-center gap-x-4 gap-y-2 text-xs sm:text-sm font-bold uppercase tracking-[0.2em] text-white/90">
            @foreach(['Self drive', 'Private chauffeur', 'Small-group coach'] as $chip)
                <li class="flex items-center gap-4">@unless($loop->first)<span class="size-1.5 rounded-full bg-brand-gold" aria-hidden="true"></span>@endunless{{ $chip }}</li>
            @endforeach
        </ul>
        <div class="mt-10 flex flex-wrap justify-center gap-3 [text-shadow:none]">
            <button type="button" class="btn btn-gold px-9 shadow-lg shadow-black/30" @click="$store.modal.show()">Plan my trip <x-icon name="arrow-right" class="w-5 h-5"/></button>
            <a href="{{ route('packages.index') }}" class="btn btn-outline-light px-9">See packages</a>
        </div>
    </div>
</section>
@endif
