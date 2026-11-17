{{-- 7. HOW IT WORKS: heading + CTA left, winding dashed route with three stops right (lg); simple vertical route on mobile --}}
@php
    $how = [
        ['Tell us what you want', 'Answer a few quick questions about where, when and how you like to travel. It takes under 30 seconds.', 110, 100],
        ['Get your tailored quote', 'We put together a quote that fits your dates, group size and budget.', 300, 270],
        ['Book and go', 'Happy with the plan? Confirm it and we handle the details so you can enjoy Germany.', 130, 450],
    ];
@endphp
<section class="py-16 sm:py-24 bg-white" aria-labelledby="how-title">
    <div class="container-x grid items-center gap-12 lg:grid-cols-[5fr_7fr] lg:gap-8">
        <div>
            <x-section-heading id="how-title" align="left" eyebrow="How it works" :title="$page->text('how_title', 'Your trip in three simple steps')" intro="Tell us what you have in mind and we shape the journey around you." class="!mb-8"/>
            <button type="button" class="btn btn-gold px-9 shadow-lg shadow-brand-gold/20" @click="$store.modal.show()">Get my free quote <x-icon name="arrow-right" class="w-5 h-5"/></button>
        </div>

        {{-- mobile / tablet: vertical route --}}
        <ol class="relative mx-3 space-y-8 border-l-2 border-dashed border-brand-gold/70 pl-10 sm:mx-6 lg:hidden">
            @foreach($how as $n => [$t, $d])
                <li class="relative">
                    <span class="absolute -left-[3.6rem] top-0 grid size-11 place-items-center rounded-full bg-brand-navy font-display text-lg text-white ring-4 ring-brand-gold shadow-md" aria-hidden="true">{{ sprintf('%02d', $n + 1) }}</span>
                    <h3>{{ $t }}</h3>
                    <p class="mt-2 text-ink-soft">{{ $d }}</p>
                </li>
            @endforeach
        </ol>

        {{-- desktop: winding route --}}
        <ol class="relative hidden aspect-[600/540] w-full lg:block">
            <svg class="absolute inset-0 size-full" viewBox="0 0 600 540" preserveAspectRatio="none" fill="none" aria-hidden="true">
                <path d="M60 0C60 50 110 50 110 100C110 190 300 170 300 270C300 370 130 350 130 450C130 490 170 510 190 540" stroke="#FCBF49" stroke-width="2.5" stroke-dasharray="3 9" stroke-linecap="round" vector-effect="non-scaling-stroke"/>
            </svg>
            @foreach($how as $n => [$t, $d, $x, $y])
                <li class="absolute" style="left: {{ round($x / 6, 2) }}%; top: {{ round($y / 5.4, 2) }}%;">
                    <span class="absolute left-0 top-0 grid size-14 -translate-x-1/2 -translate-y-1/2 place-items-center rounded-full bg-brand-navy font-display text-xl text-white ring-4 ring-brand-gold shadow-lg" aria-hidden="true">{{ sprintf('%02d', $n + 1) }}</span>
                    <div class="absolute left-0 top-0 ml-12 w-64 -translate-y-1/2">
                        <h3>{{ $t }}</h3>
                        <p class="mt-2 text-ink-soft">{{ $d }}</p>
                    </div>
                </li>
            @endforeach
        </ol>
    </div>
</section>
