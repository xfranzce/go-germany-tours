@props(['package', 'level' => 3])
<article class="card flex flex-col h-full">
    <a href="{{ $package->url() }}" class="relative block aspect-[3/2] bg-brand-custard overflow-hidden" tabindex="-1" aria-hidden="true">
        <x-picture :path="$package->coverPath()" sizes="(min-width: 1024px) 22rem, (min-width: 640px) 45vw, 92vw" class="w-full h-full object-cover"/>
        @if($package->is_sample)
            <span class="absolute top-3 left-3 bg-brand-gold text-brand-navy text-xs font-extrabold px-2.5 py-1 rounded-full">SAMPLE</span>
        @endif
    </a>
    <div class="p-5 flex flex-col flex-1">
        <p class="text-xs font-extrabold uppercase tracking-wider text-brand-red-dark">{{ $package->tourType->name }} &middot; {{ $package->destination->name }}</p>
        <h{{ $level }} class="text-xl mt-1"><a href="{{ $package->url() }}" class="hover:underline">{{ $package->title }}</a></h{{ $level }}>
        <p class="mt-2 text-ink-soft text-sm flex-1">{{ $package->summary }}</p>
        <dl class="mt-4 pt-4 border-t border-brand-custard flex items-end justify-between">
            <div><dt class="text-xs uppercase tracking-wider text-ink-soft font-bold">Duration</dt><dd class="font-bold">{{ $package->nights }} nights</dd></div>
            <div class="text-right"><dt class="text-xs uppercase tracking-wider text-ink-soft font-bold">Price</dt><dd class="font-sans font-extrabold text-xl sm:text-2xl leading-tight tracking-tight text-brand-navy">@if($package->hasSale())<s class="block text-sm font-sans font-semibold text-ink-soft decoration-brand-red">{{ $package->wasLabel() }}</s>@endif{{ $package->priceLabel() }}@if($package->is_sample)<span class="block text-xs font-sans font-normal text-ink-soft">sample price</span>@endif</dd></div>
        </dl>
        <div class="mt-4 grid grid-cols-2 gap-2">
            <x-button :href="$package->url()" variant="outline" size="sm">View details</x-button>
            <x-button size="sm" data-title="Get a quote" data-subtitle="{{ $package->title }}"
                      @click="$store.modal.show({form:'package_quote', packageId: {{ $package->id }}, ...$el.dataset})">Get a Quote</x-button>
        </div>
    </div>
</article>
