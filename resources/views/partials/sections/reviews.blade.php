{{-- 8. TESTIMONIALS (only shows when at least one is active; use real, permissioned reviews with a source) --}}
@if($testimonials->isNotEmpty())
@php
    $tCount = $testimonials->count();
    $tGrid = $tCount === 1 ? 'md:max-w-2xl md:mx-auto' : ($tCount === 2 ? 'md:grid-cols-2 md:max-w-4xl md:mx-auto' : 'md:grid-cols-2 lg:grid-cols-3');
@endphp
<section class="relative overflow-hidden py-16 sm:py-24 bg-brand-custard-light" aria-labelledby="reviews-title">
    <span class="pointer-events-none absolute -top-10 left-4 font-display text-[22rem] leading-none text-brand-gold/20 select-none" aria-hidden="true">&ldquo;</span>
    <div class="container-x relative">
        <x-section-heading id="reviews-title" eyebrow="Guest reviews" :title="$page->text('testimonials_title', 'What travelers say')"/>
        <div class="-mx-4 flex snap-x snap-mandatory gap-4 overflow-x-auto px-4 pb-4 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden md:mx-0 md:overflow-visible md:px-0 md:pb-0 md:grid md:gap-6 lg:gap-8 {{ $tGrid }}">
            @foreach($testimonials as $r)
                @php $initials = \Illuminate\Support\Str::of($r->name)->replaceMatches('/[^\p{L}\s]/u', '')->explode(' ')->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode(''); @endphp
                <figure class="group relative flex w-[85%] shrink-0 snap-center flex-col md:w-auto md:shrink bg-white rounded-[var(--radius-card)] p-7 sm:p-8 shadow-card border-t-4 border-brand-gold transition duration-300 hover:-translate-y-1 hover:shadow-xl">
                    <span class="font-display text-6xl leading-none text-brand-gold h-8" aria-hidden="true">&ldquo;</span>
                    @if($r->is_placeholder)<p class="mb-2 self-start bg-brand-gold text-brand-navy text-xs font-extrabold px-2 py-0.5 rounded">PLACEHOLDER</p>@endif
                    <blockquote class="mt-3 flex-1 font-display text-xl sm:text-2xl leading-snug text-brand-navy">{{ $r->quote }}</blockquote>
                    <figcaption class="mt-6 flex items-center gap-4 border-t border-brand-custard pt-5">
                        <span class="grid place-items-center size-12 shrink-0 rounded-full bg-brand-navy text-brand-gold font-bold text-sm tracking-wide" aria-hidden="true">{{ $initials }}</span>
                        <span class="min-w-0">
                            <span class="block font-bold text-brand-navy">{{ $r->name }}</span>
                            @if($r->location)<span class="block text-sm text-ink-soft">{{ $r->location }}</span>@endif
                            @if($r->source)<span class="mt-1.5 inline-block rounded-full bg-brand-custard-light px-2.5 py-0.5 text-xs font-semibold text-ink-soft">{{ $r->source }}</span>@endif
                        </span>
                    </figcaption>
                </figure>
            @endforeach
        </div>
    </div>
</section>
@endif
