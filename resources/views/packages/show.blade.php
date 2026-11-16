@extends('layouts.app')

@section('content')
@include('partials.page-header', [
    'title' => $package->title,
    'lead' => $package->summary,
    'crumbs' => [['Home', route('home')], ['Packages', route('packages.index')], [$package->title, null]],
])

<div class="container-x py-10 grid gap-10 lg:grid-cols-[1fr_22rem]">
    <div class="min-w-0 space-y-10">
        {{-- Gallery --}}
        @if($package->images->isNotEmpty())
            <div class="grid gap-3 {{ $package->images->count() > 1 ? 'sm:grid-cols-3' : '' }}">
                @foreach($package->images as $img)
                    <div class="rounded-2xl overflow-hidden bg-brand-custard {{ $loop->first && $package->images->count() > 1 ? 'sm:col-span-3' : '' }}">
                        <x-picture :path="$img->path" :alt="$img->alt" :priority="$loop->first"
                            :sizes="$loop->first ? '(min-width: 1024px) 46rem, 100vw' : '(min-width: 640px) 15rem, 100vw'" class="w-full h-full object-cover {{ $loop->first ? 'max-h-[28rem]' : 'aspect-[3/2]' }}"/>
                    </div>
                @endforeach
            </div>
        @endif

        <dl class="grid grid-cols-3 gap-3 text-center">
            <div class="bg-brand-custard-light rounded-xl p-3"><dt class="text-xs font-bold uppercase tracking-wider text-ink-soft">Style</dt><dd class="font-bold">{{ $package->tourType->name }}</dd></div>
            <div class="bg-brand-custard-light rounded-xl p-3"><dt class="text-xs font-bold uppercase tracking-wider text-ink-soft">Duration</dt><dd class="font-bold">{{ $package->nights }} nights</dd></div>
            <div class="bg-brand-custard-light rounded-xl p-3"><dt class="text-xs font-bold uppercase tracking-wider text-ink-soft">Destination</dt><dd class="font-bold"><a class="underline" href="{{ route('destinations.show', $package->destination->slug) }}">{{ $package->destination->name }}</a></dd></div>
        </dl>

        @if($package->is_sample)
            <p class="rounded-xl border-2 border-brand-gold bg-brand-custard-light p-4 font-bold">SAMPLE package: itinerary, inclusions and pricing are placeholders and will be replaced.</p>
        @endif

        <section aria-labelledby="overview">
            <h2 id="overview" class="text-2xl sm:text-3xl">Overview</h2>
            <div class="prose-ggt mt-4">{!! $package->description !!}</div>
        </section>

        @if($package->highlights)
            <section aria-labelledby="highlights">
                <h2 id="highlights" class="text-2xl sm:text-3xl">Highlights</h2>
                <ul class="mt-4 grid gap-2">
                    @foreach($package->highlights as $h)
                        <li class="flex gap-3"><span class="mt-1 grid place-items-center w-6 h-6 shrink-0 rounded-full bg-brand-gold text-brand-navy"><x-icon name="check" class="w-4 h-4"/></span>{{ $h }}</li>
                    @endforeach
                </ul>
            </section>
        @endif

        @if($package->itinerary)
            <section aria-labelledby="itinerary">
                <h2 id="itinerary" class="text-2xl sm:text-3xl">Day-by-day itinerary</h2>
                <div class="mt-4 space-y-3">
                    @foreach($package->itinerary as $day)
                        <x-faq-item :question="$day['title'] ?? 'Day '.$loop->iteration" :open="$loop->first">{{ $day['body'] ?? '' }}</x-faq-item>
                    @endforeach
                </div>
            </section>
        @endif

        @if($package->inclusions || $package->exclusions)
            <section class="grid gap-6 sm:grid-cols-2" aria-label="Inclusions and exclusions">
                @foreach([['Included', $package->inclusions, 'check'], ['Not included', $package->exclusions, 'x']] as [$label, $items, $ic])
                    @if($items)
                        <div class="card p-5">
                            <h2 class="text-2xl">{{ $label }}</h2>
                            <ul class="mt-3 space-y-2">@foreach($items as $i)<li class="flex gap-2"><x-icon :name="$ic" class="w-5 h-5 mt-0.5 shrink-0 {{ $ic === 'check' ? 'text-green-700' : 'text-brand-red-dark' }}"/>{{ $i }}</li>@endforeach</ul>
                        </div>
                    @endif
                @endforeach
            </section>
        @endif

        @if($package->faqs->isNotEmpty())
            <section aria-labelledby="pfaq">
                <h2 id="pfaq" class="text-2xl sm:text-3xl">Questions about this trip</h2>
                <div class="mt-4 space-y-3">@foreach($package->faqs as $f)<x-faq-item :question="$f->question">{{ $f->answer }}</x-faq-item>@endforeach</div>
            </section>
        @endif
    </div>

    {{-- Quote sidebar --}}
    <aside class="lg:sticky lg:top-24 self-start" aria-labelledby="quote-title" id="quote">
        <div class="bg-white rounded-2xl border-4 border-brand-tangerine shadow-card p-5">
            <p class="font-sans font-extrabold text-2xl sm:text-3xl leading-tight tracking-tight text-brand-navy">@if($package->hasSale())<s class="block text-base font-sans font-semibold text-ink-soft decoration-brand-red">{{ $package->wasLabel() }}</s>@endif{{ $package->priceLabel() }}</p>
            @if($package->is_sample)<p class="text-xs text-ink-soft">Sample price: placeholder only</p>@endif
            <h2 id="quote-title" class="text-xl mt-2">Get a quote for this trip</h2>
            <div class="mt-4"><x-enquiry-form form-id="package-quote" form-name="package_quote" :package="$package" :extras="true"/></div>
            <button type="button" class="btn btn-outline w-full mt-4"
                @click="$store.modal.show({form:'package_question', packageId: {{ $package->id }}, title:'Ask us a question', subtitle: @js($package->title)})">Ask Us a Question</button>
        </div>
    </aside>
</div>

@if($related->isNotEmpty())
    <section class="bg-brand-custard py-14" aria-labelledby="related">
        <div class="container-x">
            <h2 id="related" class="text-2xl sm:text-3xl mb-6">More in {{ $package->destination->name }}</h2>
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">@foreach($related as $p)<x-package-card :package="$p"/>@endforeach</div>
        </div>
    </section>
@endif
@endsection
