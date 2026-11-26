{{-- Photo tiles (name, tour count, Explore) for destinations or travel styles.
     Params: $items (models with name, slug, image, packages_count), $itemRoute ('destinations.show' | 'travel-styles.show'). --}}
<ul class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
    @foreach($items as $o)
        <li>
            <a href="{{ route($itemRoute, $o->slug) }}" class="group relative isolate flex min-h-60 flex-col justify-end overflow-hidden rounded-2xl bg-brand-navy p-6 text-white shadow-card">
                @if($o->image)<x-picture :path="$o->image" sizes="(min-width: 1024px) 30vw, (min-width: 640px) 50vw, 100vw" class="absolute inset-0 -z-20 h-full w-full object-cover transition duration-700 group-hover:scale-105"/>@endif
                <span class="absolute inset-0 -z-10 bg-gradient-to-t from-brand-navy-dark/90 via-brand-navy-dark/35 to-transparent" aria-hidden="true"></span>
                <span class="font-display text-3xl font-semibold [text-shadow:0_1px_8px_rgb(0_0_0/0.5)]">{{ $o->name }}</span>
                <span class="mt-1 text-sm text-white/90">{{ $o->packages_count }} {{ Str::plural('tour', $o->packages_count) }}@if($o->tagline) &middot; {{ $o->tagline }}@endif</span>
                <span class="mt-3 inline-flex items-center gap-2 text-xs font-extrabold uppercase tracking-[0.16em] text-brand-gold">Explore <x-icon name="arrow-right" class="h-4 w-4 transition group-hover:translate-x-1"/></span>
            </a>
        </li>
    @endforeach
</ul>
