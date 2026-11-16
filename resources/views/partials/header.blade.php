@php
    $navTypes = \App\Models\TourType::active()->get(['id', 'name', 'slug', 'summary', 'image']);
    // Packages per tour type for the Tours mega menu (4 each)
    $navPackages = \App\Models\Package::published()->get(['id', 'title', 'slug', 'nights', 'tour_type_id'])->groupBy('tour_type_id');
    $aboutLinks = [
        ['about', 'About Us', 'Who we are and how we plan your trip', 'shield'],
        ['team', 'Meet Our Team', 'The people behind your journey', 'chat'],
        ['testimonials', 'Testimonials', 'What travelers say about us', 'star'],
        ['faqs', 'FAQs', 'Answers to common questions', 'check'],
    ];
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
    <div class="container-x flex items-center justify-between gap-4 py-3">
        <a href="{{ route('home') }}" class="flex items-center gap-2.5 font-display font-extrabold text-xl leading-none" aria-label="{{ $site['brand_name'] }} home">
            @if($logo)
                <img src="{{ $logo->url(min(480, max($logo->widths))) }}" alt="{{ $site['brand_name'] }}" width="{{ $logo->width }}" height="{{ $logo->height }}" class="h-10 w-auto">
            @else
                <span class="flex flex-col w-7 h-7 rounded-md overflow-hidden shrink-0" aria-hidden="true">
                    <span class="flex-1 bg-brand-navy"></span><span class="flex-1 bg-brand-red"></span><span class="flex-1 bg-brand-gold"></span>
                </span>
                <span>{{ $site['brand_name'] }}</span>
            @endif
        </a>

        <nav class="hidden lg:flex items-center gap-1 font-bold text-sm uppercase tracking-wider" aria-label="Main">
            {{-- TOURS: full-width mega menu, one column per tour type --}}
            <div @mouseenter="hover('tours')" @mouseleave="menu === 'tours' && (menu = null)">
                <button type="button" class="px-3 py-2 rounded-lg hover:bg-current/10 inline-flex items-center gap-1" :aria-expanded="menu === 'tours'" aria-controls="mega-tours"
                        @click="menu = menu === 'tours' ? null : 'tours'">
                    Tours <span class="inline-flex transition" :class="menu === 'tours' && 'rotate-180'"><x-icon name="chevron-down" class="w-4 h-4"/></span>
                </button>
                <div id="mega-tours" x-show="menu === 'tours'" x-cloak x-transition.opacity.duration.200ms
                     class="absolute inset-x-0 top-full normal-case tracking-normal before:absolute before:inset-x-0 before:-top-4 before:h-4">
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

            <a href="{{ route('packages.index') }}" class="px-3 py-2 rounded-lg hover:bg-current/10">Packages</a>

            {{-- ABOUT US: mega menu with the four company pages and a quote promo --}}
            <div class="relative" @mouseenter="hover('about')" @mouseleave="menu === 'about' && (menu = null)">
                <button type="button" class="px-3 py-2 rounded-lg hover:bg-current/10 inline-flex items-center gap-1" :aria-expanded="menu === 'about'" aria-controls="mega-about"
                        @click="menu = menu === 'about' ? null : 'about'">
                    About Us <span class="inline-flex transition" :class="menu === 'about' && 'rotate-180'"><x-icon name="chevron-down" class="w-4 h-4"/></span>
                </button>
                <div id="mega-about" x-show="menu === 'about'" x-cloak x-transition.opacity.duration.200ms
                     class="absolute right-0 top-full w-[40rem] normal-case tracking-normal before:absolute before:inset-x-0 before:-top-4 before:h-4">
                    <div class="mt-0 grid grid-cols-[1fr_1.7fr] overflow-hidden rounded-b-2xl border-t-4 border-brand-gold bg-white text-brand-navy shadow-[0_30px_60px_-20px_rgb(0_48_73/0.45)]">
                        <div class="flex flex-col justify-between gap-6 bg-brand-navy p-6 text-white">
                            <div>
                                <p class="eyebrow text-brand-gold">Go Germany Tours</p>
                                <p class="mt-3 font-display text-2xl font-semibold leading-tight">Journeys planned around you.</p>
                                <p class="mt-2 text-sm font-normal text-white/80">Tell us how you like to travel and we will shape the trip.</p>
                            </div>
                            <button type="button" class="btn btn-gold btn-sm self-start" @click="menu = null; $store.modal.show()">Get a free quote</button>
                        </div>
                        <ul class="grid gap-1 p-4">
                            @foreach($aboutLinks as [$r, $label, $desc, $ic])
                                <li><a href="{{ route($r) }}" class="group flex items-center gap-4 rounded-xl p-3 hover:bg-brand-custard-light" @click="menu = null">
                                    <span class="grid size-11 shrink-0 place-items-center rounded-full bg-brand-custard text-brand-navy transition group-hover:bg-brand-gold"><x-icon :name="$ic" class="h-5 w-5"/></span>
                                    <span><span class="block font-bold group-hover:text-brand-red">{{ $label }}</span><span class="block text-sm font-normal text-ink-soft">{{ $desc }}</span></span>
                                </a></li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>

            <a href="{{ route('contact') }}" class="px-3 py-2 rounded-lg hover:bg-current/10">Contact</a>
            <button type="button" class="btn btn-primary btn-sm ml-2" @click="$store.modal.show()">Free Quote</button>
        </nav>

        <button type="button" class="lg:hidden p-2 -mr-2 rounded-lg" :aria-expanded="mobile" aria-controls="mobile-menu" @click="mobile = !mobile">
            <span class="sr-only">Menu</span>
            <x-icon name="menu" class="w-7 h-7" x-show="!mobile"/>
            <x-icon name="x" class="w-7 h-7" x-show="mobile" x-cloak/>
        </button>
    </div>

    <nav id="mobile-menu" class="lg:hidden border-t border-brand-custard bg-white text-brand-navy" x-show="mobile" x-cloak aria-label="Mobile">
        <div class="container-x py-3 grid gap-1 font-bold max-h-[75vh] overflow-y-auto">
            <p class="text-xs uppercase tracking-wider text-ink-soft mt-1">Tours</p>
            @foreach($navTypes as $t)<a class="py-2 pl-2" href="{{ route('tours.show', $t->slug) }}">{{ $t->name }}</a>@endforeach
            <hr class="my-2 border-brand-custard">
            <a class="py-2" href="{{ route('packages.index') }}">All packages</a>
            <p class="text-xs uppercase tracking-wider text-ink-soft mt-3">About Us</p>
            @foreach($aboutLinks as [$r, $label])<a class="py-2 pl-2" href="{{ route($r) }}">{{ $label }}</a>@endforeach
            <hr class="my-2 border-brand-custard">
            <a class="py-2" href="{{ route('contact') }}">Contact</a>
            <button type="button" class="btn btn-primary mt-2" @click="mobile = false; $store.modal.show()">Get a Free Quote</button>
        </div>
    </nav>
</header>
