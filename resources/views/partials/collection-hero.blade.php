{{-- Photo hero with the hero quiz on the right, shared by the destination and travel-style pages.
     Params: $heroImage (media path or null), $eyebrow, $title (the page h1), $lead, $crumbs ([label, url|null] pairs). --}}
<section class="relative isolate overflow-hidden bg-brand-navy text-white">
    @if($heroImage)<x-picture :path="$heroImage" priority sizes="100vw" class="absolute inset-0 -z-20 h-full w-full object-cover"/>@endif
    <div class="absolute inset-0 -z-10 bg-gradient-to-r from-brand-navy-dark/85 via-brand-navy-dark/45 to-transparent" aria-hidden="true"></div>
    <div class="absolute inset-x-0 top-0 -z-10 h-40 bg-gradient-to-b from-brand-navy-dark/60 to-transparent" aria-hidden="true"></div>
    <div class="container-x grid items-center gap-10 pt-28 pb-12 sm:pt-36 sm:pb-16 lg:grid-cols-[1.1fr_0.9fr] lg:gap-14">
        <div>
            <nav aria-label="Breadcrumb" class="mb-3 text-sm text-white/80">
                <ol class="flex flex-wrap gap-x-2">
                    @foreach($crumbs as [$label, $url])
                        <li>@if($url)<a class="underline underline-offset-2 hover:text-brand-gold" href="{{ $url }}">{{ $label }}</a> <span aria-hidden="true">/</span>@else<span aria-current="page">{{ $label }}</span>@endif</li>
                    @endforeach
                </ol>
            </nav>
            @if($eyebrow)<p class="eyebrow text-brand-gold [text-shadow:0_1px_8px_rgb(0_0_0/0.6)]">{{ $eyebrow }}</p>@endif
            <h1 class="mt-4 max-w-3xl text-4xl [text-shadow:0_2px_14px_rgb(0_0_0/0.45)] sm:text-5xl">{{ $title }}</h1>
            @if($lead)<p class="mt-4 max-w-xl text-lg text-white/95 [text-shadow:0_1px_10px_rgb(0_0_0/0.5)]">{{ $lead }}</p>@endif
            <ul class="mt-6 max-w-xl space-y-3 border-t border-white/25 pt-5" aria-label="Why book with us">
                @foreach([['check', 'Free, no-obligation quote'], ['shield', 'Self drive, chauffeur or coach'], ['adjust', 'Itineraries built around you']] as [$ic, $txt])
                    <li class="flex items-center gap-3">
                        <span class="grid size-10 shrink-0 place-items-center rounded-full bg-brand-gold text-brand-navy shadow-lg shadow-black/20" aria-hidden="true"><x-icon :name="$ic" class="h-5 w-5"/></span>
                        <span class="text-sm font-bold uppercase tracking-[0.12em] text-white sm:text-[0.8rem]">{{ $txt }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
        <div class="mx-auto w-full max-w-xl lg:ml-auto lg:mr-0"><x-hero-quiz/></div>
    </div>
</section>
