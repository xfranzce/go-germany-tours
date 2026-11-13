@php
    $navTypes = \App\Models\TourType::active()->get(['name', 'slug']);
    $navDests = \App\Models\Destination::active()->get(['name', 'slug']);
    $logo = \App\Models\Media::forPath($site['logo'] ?? null);
@endphp
<header class="sticky top-0 z-40 bg-white/95 backdrop-blur shadow-sm" x-data="{ mobile: false }" @keydown.escape="mobile = false">
    <div class="container-x flex items-center justify-between gap-4 py-3">
        <a href="{{ route('home') }}" class="flex items-center gap-2.5 font-display font-extrabold text-xl text-brand-navy leading-none" aria-label="{{ $site['brand_name'] }} home">
            @if($logo)
                <img src="{{ $logo->url(min(480, max($logo->widths))) }}" alt="{{ $site['brand_name'] }}" width="{{ $logo->width }}" height="{{ $logo->height }}" class="h-10 w-auto">
            @else
                <span class="flex flex-col w-7 h-7 rounded-md overflow-hidden shrink-0" aria-hidden="true">
                    <span class="flex-1 bg-brand-navy"></span><span class="flex-1 bg-brand-red"></span><span class="flex-1 bg-brand-gold"></span>
                </span>
                <span>{{ $site['brand_name'] }}</span>
            @endif
        </a>

        <nav class="hidden lg:flex items-center gap-1 font-bold" aria-label="Main">
            @foreach([['Tours', 'tours', $navTypes, 'tours.show'], ['Destinations', 'destinations', $navDests, 'destinations.show']] as [$label, $id, $items, $route])
                <div class="relative" x-data="{ open: false }" @keydown.escape="open = false" @click.outside="open = false">
                    <button type="button" class="px-3 py-2 rounded-lg hover:bg-brand-custard-light inline-flex items-center gap-1" :aria-expanded="open" aria-controls="menu-{{ $id }}" @click="open = !open">
                        {{ $label }} <x-icon name="chevron-down" class="w-4 h-4"/>
                    </button>
                    <ul id="menu-{{ $id }}" x-show="open" x-cloak x-transition.opacity class="absolute left-0 mt-1 min-w-56 bg-white rounded-xl shadow-card p-2 z-50">
                        @foreach($items as $item)
                            <li><a class="block px-3 py-2 rounded-lg hover:bg-brand-custard-light" href="{{ route($route, $item->slug) }}">{{ $item->name }}</a></li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
            <a href="{{ route('packages.index') }}" class="px-3 py-2 rounded-lg hover:bg-brand-custard-light">Packages</a>
            <a href="{{ route('about') }}" class="px-3 py-2 rounded-lg hover:bg-brand-custard-light">About</a>
            <a href="{{ route('contact') }}" class="px-3 py-2 rounded-lg hover:bg-brand-custard-light">Contact</a>
            <button type="button" class="btn btn-primary btn-sm ml-2" @click="$store.modal.show()">Free Quote</button>
        </nav>

        <button type="button" class="lg:hidden p-2 -mr-2 rounded-lg" :aria-expanded="mobile" aria-controls="mobile-menu" @click="mobile = !mobile">
            <span class="sr-only">Menu</span>
            <x-icon name="menu" class="w-7 h-7" x-show="!mobile"/>
            <x-icon name="x" class="w-7 h-7" x-show="mobile" x-cloak/>
        </button>
    </div>

    <nav id="mobile-menu" class="lg:hidden border-t border-brand-custard bg-white" x-show="mobile" x-cloak aria-label="Mobile">
        <div class="container-x py-3 grid gap-1 font-bold max-h-[75vh] overflow-y-auto">
            <p class="text-xs uppercase tracking-wider text-ink-soft mt-1">Tours</p>
            @foreach($navTypes as $t)<a class="py-2 pl-2" href="{{ route('tours.show', $t->slug) }}">{{ $t->name }}</a>@endforeach
            <p class="text-xs uppercase tracking-wider text-ink-soft mt-2">Destinations</p>
            @foreach($navDests as $d)<a class="py-2 pl-2" href="{{ route('destinations.show', $d->slug) }}">{{ $d->name }}</a>@endforeach
            <hr class="my-2 border-brand-custard">
            <a class="py-2" href="{{ route('packages.index') }}">All packages</a>
            <a class="py-2" href="{{ route('about') }}">About</a>
            <a class="py-2" href="{{ route('contact') }}">Contact</a>
            <button type="button" class="btn btn-primary mt-2" @click="mobile = false; $store.modal.show()">Get a Free Quote</button>
        </div>
    </nav>
</header>
