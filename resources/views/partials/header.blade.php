@php
    $navTypes = \App\Models\TourType::active()->get(['id', 'name', 'slug', 'summary', 'image']);
    // Packages per tour type for the Tours mega menu (4 each)
    $navPackages = \App\Models\Package::published()->get(['id', 'title', 'slug', 'nights', 'tour_type_id'])->groupBy('tour_type_id');
    // Destinations and Travel styles mega menus (image cards)
    $navDestinations = \App\Models\Destination::active()->get(['id', 'name', 'slug', 'tagline', 'summary', 'image']);
    $navStyles = \App\Models\TravelStyle::active()->get(['id', 'name', 'slug', 'tagline', 'image']);
    $aboutLinks = [
        ['about', 'About Us', 'Who we are and how we plan your trip', 'hero-quedlinburg'],
        ['team', 'Meet Our Team', 'The people behind your journey', 'gengenbach-rathaus'],
        ['testimonials', 'Testimonials', 'What travelers say about us', 'cochem-mosel'],
        ['faqs', 'FAQs', 'Answers to common questions', 'saxon-switzerland-bastei'],
    ];
    $aboutMedia = \App\Models\Media::query()->where(function ($q) use ($aboutLinks) {
        foreach ($aboutLinks as $l) { $q->orWhere('path', 'like', 'media/'.$l[3].'-%'); }
    })->get();
    $logo = \App\Models\Media::forPath($site['logo'] ?? null);
    // Pages that start on a light background keep a solid bar (@section('solid_header', '1'))
    $solid = \Illuminate\Support\Facades\View::hasSection('solid_header');
@endphp
<header class="{{ $solid ? 'sticky' : 'fixed inset-x-0' }} top-0 z-40 border-b transition-colors duration-300"
        x-data="{ mobile: false, menu: null, scrolled: {{ $solid ? 'true' : 'false' }},
                  hover(name) { if (window.matchMedia('(hover: hover)').matches) this.menu = name } }"
        @keydown.escape="mobile = false; menu = null" @click.outside="menu = null"
        @if(! $solid) x-init="scrolled = window.scrollY > 24" @scroll.window.passive="scrolled = window.scrollY > 24" @endif
        :class="scrolled || mobile || menu ? 'bg-white/95 backdrop-blur border-brand-custard text-brand-navy shadow-sm' : 'bg-transparent border-transparent text-white'">
    <div class="container-x flex items-center justify-between gap-4 transition-[padding] duration-300 {{ $solid ? 'py-4' : '' }}"
         @if(! $solid) :class="scrolled ? 'py-3' : 'py-5 lg:py-7'" @endif>
        <a href="{{ route('home') }}" class="flex items-center gap-2.5 font-display font-extrabold text-xl leading-none" aria-label="{{ $site['brand_name'] }}">
            @if($logo)
                <img src="{{ $logo->url(min(480, max($logo->widths))) }}" alt="{{ $site['brand_name'] }}" width="{{ $logo->width }}" height="{{ $logo->height }}" class="h-10 w-auto">
            @else
                <x-logo-mark class="size-11 shrink-0"/>
                <span class="flex flex-col leading-none"><span class="font-display text-[1.7rem] font-semibold tracking-tight">Go Germany</span><span class="mt-1.5 flex items-center gap-2 text-[0.62rem] font-semibold uppercase tracking-[0.42em]"><span class="h-px w-5 bg-brand-gold"></span>Tours</span></span>
            @endif
        </a>

        <nav class="hidden lg:flex items-center gap-0.5 whitespace-nowrap font-bold text-[0.78rem] uppercase tracking-wide xl:gap-1 xl:text-sm xl:tracking-wider" aria-label="Main">
            <a href="{{ route('home') }}" class="px-2 py-2 xl:px-3 rounded-lg hover:bg-current/10" @if(request()->routeIs('home')) aria-current="page" @endif>Home</a>

            {{-- TOURS: full-width mega menu, one column per tour type --}}
            <div @mouseenter="hover('tours')" @mouseleave="menu === 'tours' && (menu = null)">
                {{-- "Tours" is a link to the Packages page; hovering (or the arrow button, for keyboard and touch) opens the mega menu --}}
                <span class="inline-flex items-center rounded-lg uppercase hover:bg-current/10">
                    <a href="{{ route('packages.index') }}" class="py-2 pl-2 pr-0.5 xl:pl-3 xl:pr-1" @if(request()->routeIs('packages.*', 'tours.*')) aria-current="page" @endif>Tours</a>
                    <button type="button" class="py-2 pl-0.5 pr-2 xl:pl-1 xl:pr-3" :aria-expanded="menu === 'tours'" aria-controls="mega-tours" aria-label="Show the tours menu"
                            @click="menu = menu === 'tours' ? null : 'tours'">
                        <span class="inline-flex transition" :class="menu === 'tours' && 'rotate-180'"><x-icon name="chevron-down" class="w-4 h-4"/></span>
                    </button>
                </span>
                <div id="mega-tours" x-show="menu === 'tours'" x-cloak x-transition.opacity.duration.200ms
                     class="absolute inset-x-0 top-full whitespace-normal text-sm normal-case tracking-normal before:absolute before:inset-x-0 before:-top-4 before:h-4">
                    <div class="border-t-4 border-brand-gold bg-white text-brand-navy shadow-[0_30px_60px_-20px_rgb(0_48_73/0.45)]">
                        <div class="container-x grid gap-8 py-8 lg:grid-cols-3">
                            @foreach($navTypes as $t)
                                @php $pk = ($navPackages[$t->id] ?? collect())->take(4); @endphp
                                <div class="min-w-0">
                                    <a href="{{ route('tours.show', $t->slug) }}" class="group block" @click="menu = null">
                                        @if($t->image)
                                            <span class="block aspect-[16/7] overflow-hidden rounded-lg bg-brand-custard">
                                                <x-picture :path="$t->image" sizes="26rem" class="h-full w-full object-cover transition duration-700 group-hover:scale-105"/>
                                            </span>
                                        @endif
                                        <span class="mt-3 block font-display text-2xl font-semibold group-hover:text-brand-red">{{ $t->name }}</span>
                                    </a>
                                    @if($t->summary)<p class="mt-1 text-sm font-normal text-ink-soft">{{ $t->summary }}</p>@endif
                                    @if($pk->isNotEmpty())
                                        <ul class="mt-3 space-y-0.5 border-t border-brand-custard pt-3">
                                            @foreach($pk as $p)
                                                <li><a href="{{ $p->url() }}" class="flex items-baseline justify-between gap-3 rounded-md px-2 py-1.5 text-sm font-semibold hover:bg-brand-custard-light hover:text-brand-red" @click="menu = null">
                                                    <span class="truncate">{{ $p->title }}</span><span class="shrink-0 text-xs font-normal text-ink-soft">{{ $p->nights }} nights</span></a></li>
                                            @endforeach
                                        </ul>
                                    @endif
                                    <a href="{{ route('tours.show', $t->slug) }}" class="mt-3 inline-flex items-center gap-2 text-xs font-extrabold uppercase tracking-[0.16em] text-brand-red-dark hover:text-brand-navy" @click="menu = null">View all {{ strtolower($t->name) }} <x-icon name="arrow-right" class="h-4 w-4"/></a>
                                </div>
                            @endforeach
                        </div>
                        <div class="border-t border-brand-custard bg-brand-custard-light">
                            <div class="container-x flex flex-wrap items-center justify-between gap-3 py-3 text-sm">
                                <span class="font-semibold">Not sure which suits you? We will plan it around you.</span>
                                <span class="flex gap-2"><a href="{{ route('packages.index') }}" class="btn btn-outline btn-sm" @click="menu = null">All packages</a><button type="button" class="btn btn-primary btn-sm" @click="menu = null; $store.modal.show()">Get a free quote</button></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- DESTINATIONS and TRAVEL STYLES: full-width mega menus with an image card per item (like goeurotours.com) --}}
            @foreach([['destinations', 'Destinations', route('destinations.index'), 'destinations.*', $navDestinations, 'destinations.show', 'All destinations', 'Not sure where to start? Tell us what you love and we will suggest a region.'],
                      ['styles', 'Travel Styles', route('travel-styles.index'), 'travel-styles.*', $navStyles, 'travel-styles.show', 'All travel styles', 'Every style works with self drive, a private driver or a small-group coach.']]
                     as [$key, $label, $allUrl, $routePattern, $items, $itemRoute, $allLabel, $promo])
                @continue($items->isEmpty())
                <div @mouseenter="hover('{{ $key }}')" @mouseleave="menu === '{{ $key }}' && (menu = null)">
                    <span class="inline-flex items-center rounded-lg uppercase hover:bg-current/10">
                        <a href="{{ $allUrl }}" class="py-2 pl-2 pr-0.5 xl:pl-3 xl:pr-1" @if(request()->routeIs($routePattern)) aria-current="page" @endif>{{ $label }}</a>
                        <button type="button" class="py-2 pl-0.5 pr-2 xl:pl-1 xl:pr-3" :aria-expanded="menu === '{{ $key }}'" aria-controls="mega-{{ $key }}" aria-label="Show the {{ strtolower($label) }} menu"
                                @click="menu = menu === '{{ $key }}' ? null : '{{ $key }}'">
                            <span class="inline-flex transition" :class="menu === '{{ $key }}' && 'rotate-180'"><x-icon name="chevron-down" class="w-4 h-4"/></span>
                        </button>
                    </span>
                    <div id="mega-{{ $key }}" x-show="menu === '{{ $key }}'" x-cloak x-transition.opacity.duration.200ms
                         class="absolute inset-x-0 top-full whitespace-normal text-sm normal-case tracking-normal before:absolute before:inset-x-0 before:-top-4 before:h-4">
                        <div class="border-t-4 border-brand-gold bg-white text-brand-navy shadow-[0_30px_60px_-20px_rgb(0_48_73/0.45)]">
                            <div class="container-x grid gap-5 py-8" style="grid-template-columns: repeat({{ min(6, $items->count()) }}, minmax(0, 1fr))">
                                @foreach($items->take(6) as $it)
                                    <a href="{{ route($itemRoute, $it->slug) }}" class="group block min-w-0" @click="menu = null">
                                        <span class="block aspect-[4/3] overflow-hidden rounded-xl bg-brand-custard">
                                            @if($it->image)<x-picture :path="$it->image" sizes="16rem" class="h-full w-full object-cover transition duration-700 group-hover:scale-105"/>@endif
                                        </span>
                                        <span class="mt-3 flex items-center justify-between gap-2 font-display text-xl font-semibold group-hover:text-brand-red">{{ $it->name }}<x-icon name="arrow-right" class="h-4 w-4 shrink-0 -translate-x-1 opacity-0 transition group-hover:translate-x-0 group-hover:opacity-100"/></span>
                                        @if($it->tagline)<span class="mt-0.5 block text-sm font-normal text-ink-soft">{{ $it->tagline }}</span>@endif
                                    </a>
                                @endforeach
                            </div>
                            <div class="border-t border-brand-custard bg-brand-custard-light">
                                <div class="container-x flex flex-wrap items-center justify-between gap-3 py-3 text-sm">
                                    <span class="font-semibold">{{ $promo }}</span>
                                    <span class="flex gap-2"><a href="{{ $allUrl }}" class="btn btn-outline btn-sm" @click="menu = null">{{ $allLabel }}</a><button type="button" class="btn btn-primary btn-sm" @click="menu = null; $store.modal.show()">Get a free quote</button></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            {{-- ABOUT US: full-width mega menu, an image card for each company page plus a quote promo --}}
            <div @mouseenter="hover('about')" @mouseleave="menu === 'about' && (menu = null)">
                <button type="button" class="px-2 py-2 xl:px-3 rounded-lg hover:bg-current/10 inline-flex items-center gap-1 uppercase" :aria-expanded="menu === 'about'" aria-controls="mega-about"
                        @click="menu = menu === 'about' ? null : 'about'">
                    About Us <span class="inline-flex transition" :class="menu === 'about' && 'rotate-180'"><x-icon name="chevron-down" class="w-4 h-4"/></span>
                </button>
                <div id="mega-about" x-show="menu === 'about'" x-cloak x-transition.opacity.duration.200ms
                     class="absolute inset-x-0 top-full whitespace-normal text-sm normal-case tracking-normal before:absolute before:inset-x-0 before:-top-4 before:h-4">
                    <div class="border-t-4 border-brand-gold bg-white text-brand-navy shadow-[0_30px_60px_-20px_rgb(0_48_73/0.45)]">
                        <div class="container-x grid gap-6 py-8 lg:grid-cols-[1fr_repeat(4,minmax(0,1fr))]">
                            <div class="flex flex-col justify-between gap-6 rounded-xl bg-brand-navy p-6 text-white">
                                <div>
                                    <p class="eyebrow text-brand-gold">{{ $site['brand_name'] }}</p>
                                    <p class="mt-3 font-display text-2xl font-semibold leading-tight">Journeys planned around you.</p>
                                    <p class="mt-2 text-sm font-normal text-white/80">Tell us how you like to travel and we will shape the trip.</p>
                                </div>
                                <button type="button" class="btn btn-primary btn-sm self-start" @click="menu = null; $store.modal.show()">Get a free quote</button>
                            </div>
                            @foreach($aboutLinks as [$r, $label, $desc, $stem])
                                @php $img = $aboutMedia->first(fn ($m) => str_starts_with($m->path, 'media/'.$stem.'-')); @endphp
                                <a href="{{ route($r) }}" class="group block min-w-0" @click="menu = null">
                                    <span class="block aspect-[4/3] overflow-hidden rounded-xl bg-brand-custard">
                                        @if($img)<x-picture :path="$img->path" sizes="20rem" class="h-full w-full object-cover transition duration-700 group-hover:scale-105"/>@endif
                                    </span>
                                    <span class="mt-3 flex items-center justify-between gap-2 font-display text-xl font-semibold group-hover:text-brand-red">{{ $label }}<x-icon name="arrow-right" class="h-4 w-4 shrink-0 -translate-x-1 opacity-0 transition group-hover:translate-x-0 group-hover:opacity-100"/></span>
                                    <span class="mt-0.5 block text-sm font-normal text-ink-soft">{{ $desc }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <a href="{{ route('contact') }}" class="px-2 py-2 xl:px-3 rounded-lg hover:bg-current/10">Contact</a>
            <button type="button" class="btn btn-primary btn-sm ml-1 xl:ml-2" @click="$store.modal.show()">Free Quote</button>
        </nav>

        <button type="button" class="lg:hidden flex shrink-0 items-center gap-2 rounded-full border-2 border-current py-1.5 pl-4 pr-3 text-xs font-extrabold uppercase tracking-[0.14em]" :aria-expanded="mobile" aria-controls="mobile-menu" @click="mobile = !mobile">
            <span x-text="mobile ? 'Close' : 'Menu'">Menu</span>
            <x-icon name="menu" class="size-6" x-show="!mobile"/>
            <x-icon name="x" class="size-6" x-show="mobile" x-cloak/>
        </button>
    </div>

    {{-- Mobile menu: accordion mega menu (Tours with photos, About Us with photos) --}}
    <nav id="mobile-menu" class="lg:hidden border-t border-brand-custard bg-white text-brand-navy" x-show="mobile" x-cloak aria-label="Mobile"
         x-data="{ sec: 'tours' }">
        <div class="container-x max-h-[calc(100svh-4.5rem)] overflow-y-auto py-3 font-bold">
            <a class="block border-b border-brand-custard py-3.5 text-sm uppercase tracking-wider" href="{{ route('home') }}" @if(request()->routeIs('home')) aria-current="page" @endif>Home</a>
            {{-- TOURS --}}
            <div class="border-b border-brand-custard">
                <button type="button" class="flex w-full items-center justify-between py-3.5 text-sm uppercase tracking-wider" :aria-expanded="sec === 'tours'" aria-controls="m-tours" @click="sec = sec === 'tours' ? null : 'tours'">
                    Tours <span class="inline-flex transition" :class="sec === 'tours' && 'rotate-180'"><x-icon name="chevron-down" class="size-5"/></span>
                </button>
                <div id="m-tours" x-show="sec === 'tours'" x-transition.opacity class="space-y-3 pb-4">
                    @foreach($navTypes as $t)
                        <a href="{{ route('tours.show', $t->slug) }}" class="flex items-center gap-4 rounded-xl border border-brand-custard p-2.5" @click="mobile = false">
                            <span class="block size-20 shrink-0 overflow-hidden rounded-lg bg-brand-custard">
                                @if($t->image)<x-picture :path="$t->image" sizes="6rem" class="h-full w-full object-cover"/>@endif
                            </span>
                            <span class="min-w-0">
                                <span class="block font-display text-xl font-semibold leading-tight">{{ $t->name }}</span>
                                @if($t->summary)<span class="mt-1 line-clamp-2 block text-sm font-normal text-ink-soft">{{ $t->summary }}</span>@endif
                            </span>
                        </a>
                    @endforeach
                    <a href="{{ route('packages.index') }}" class="flex items-center justify-between rounded-xl bg-brand-custard-light px-4 py-3 text-sm uppercase tracking-wider text-brand-red-dark" @click="mobile = false">All packages <x-icon name="arrow-right" class="size-4"/></a>
                </div>
            </div>

            {{-- DESTINATIONS and TRAVEL STYLES --}}
            @foreach([['m-destinations', 'Destinations', $navDestinations, 'destinations.show', route('destinations.index'), 'All destinations'], ['m-styles', 'Travel Styles', $navStyles, 'travel-styles.show', route('travel-styles.index'), 'All travel styles']] as [$mid, $label, $items, $itemRoute, $allUrl, $allLabel])
                @continue($items->isEmpty())
                <div class="border-b border-brand-custard">
                    <button type="button" class="flex w-full items-center justify-between py-3.5 text-sm uppercase tracking-wider" :aria-expanded="sec === '{{ $mid }}'" aria-controls="{{ $mid }}" @click="sec = sec === '{{ $mid }}' ? null : '{{ $mid }}'">
                        {{ $label }} <span class="inline-flex transition" :class="sec === '{{ $mid }}' && 'rotate-180'"><x-icon name="chevron-down" class="size-5"/></span>
                    </button>
                    <div id="{{ $mid }}" x-show="sec === '{{ $mid }}'" x-cloak x-transition.opacity class="pb-4">
                        <div class="grid grid-cols-2 gap-3">
                            @foreach($items as $it)
                                <a href="{{ route($itemRoute, $it->slug) }}" class="block min-w-0" @click="mobile = false">
                                    <span class="block aspect-[4/3] overflow-hidden rounded-xl bg-brand-custard">@if($it->image)<x-picture :path="$it->image" sizes="12rem" class="h-full w-full object-cover"/>@endif</span>
                                    <span class="mt-2 block font-display text-lg font-semibold leading-tight">{{ $it->name }}</span>
                                </a>
                            @endforeach
                        </div>
                        <a href="{{ $allUrl }}" class="mt-3 flex items-center justify-between rounded-xl bg-brand-custard-light px-4 py-3 text-sm uppercase tracking-wider text-brand-red-dark" @click="mobile = false">{{ $allLabel }} <x-icon name="arrow-right" class="size-4"/></a>
                    </div>
                </div>
            @endforeach

            {{-- ABOUT US --}}
            <div class="border-b border-brand-custard">
                <button type="button" class="flex w-full items-center justify-between py-3.5 text-sm uppercase tracking-wider" :aria-expanded="sec === 'about'" aria-controls="m-about" @click="sec = sec === 'about' ? null : 'about'">
                    About Us <span class="inline-flex transition" :class="sec === 'about' && 'rotate-180'"><x-icon name="chevron-down" class="size-5"/></span>
                </button>
                <div id="m-about" x-show="sec === 'about'" x-transition.opacity class="grid grid-cols-2 gap-3 pb-4">
                    @foreach($aboutLinks as [$r, $label, $desc, $stem])
                        @php $img = $aboutMedia->first(fn ($m) => str_starts_with($m->path, 'media/'.$stem.'-')); @endphp
                        <a href="{{ route($r) }}" class="block min-w-0" @click="mobile = false">
                            <span class="block aspect-[4/3] overflow-hidden rounded-xl bg-brand-custard">@if($img)<x-picture :path="$img->path" sizes="12rem" class="h-full w-full object-cover"/>@endif</span>
                            <span class="mt-2 block font-display text-lg font-semibold leading-tight">{{ $label }}</span>
                        </a>
                    @endforeach
                </div>
            </div>

            <a class="block border-b border-brand-custard py-3.5 text-sm uppercase tracking-wider" href="{{ route('contact') }}">Contact</a>
            <button type="button" class="btn btn-primary mt-4 w-full" @click="mobile = false; $store.modal.show()">Get a Free Quote</button>
        </div>
    </nav>
</header>
