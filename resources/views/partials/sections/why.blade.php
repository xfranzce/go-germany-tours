{{-- 6. OUR DIFFERENCE (dark, split layout) --}}
<section class="relative isolate overflow-hidden py-16 sm:py-24 bg-brand-navy text-white" aria-labelledby="why-title">
    {{-- faint backdrop on smaller screens; a full map column on wide ones --}}
    <x-germany-map class="pointer-events-none absolute -z-10 top-1/2 h-[110%] w-auto -translate-y-1/2 opacity-25 -right-16 xl:hidden"/>
    <div class="container-x relative grid gap-14 lg:grid-cols-[0.8fr_1.2fr] xl:grid-cols-[0.8fr_0.7fr_1.3fr] xl:gap-10">
        <div>
            <p class="eyebrow text-brand-gold">Why {{ $site['brand_name'] }}</p>
            <h2 id="why-title" class="mt-4 text-white">{{ $page->text('why_title', 'Why travelers choose us') }}</h2>
            <span class="flag-rule" aria-hidden="true"></span>
            <p class="mt-5 text-base sm:text-lg text-white/85">{{ $page->text('why_intro', 'Germany is easier to enjoy when the planning is done for you.') }}</p>
            <button type="button" class="btn btn-gold mt-8" @click="$store.modal.show()">Start planning</button>
        </div>
        <div class="hidden xl:flex items-center justify-center" aria-hidden="true"><x-germany-map class="h-[28rem] w-auto"/></div>
        <ul data-carousel="sm" data-carousel-light class="-mx-4 flex snap-x snap-mandatory gap-4 overflow-x-auto px-4 pb-2 [scrollbar-width:none] sm:mx-0 sm:grid sm:grid-cols-2 sm:gap-x-10 sm:gap-y-8 sm:overflow-visible sm:px-0 sm:pb-0 [&::-webkit-scrollbar]:hidden">
            @foreach([
                ['adjust', 'Choose how you travel', 'Drive yourself, relax with a private driver, or join a small-group coach tour.'],
                ['pin', 'Beyond the usual stops', 'Quedlinburg, Saxon Switzerland, Cochem, Hiddensee and Gengenbach: towns and landscapes many itineraries skip.'],
                ['check', 'Built around you', 'Tell us your dates, group size and budget and we shape the trip to fit.'],
                ['chat', 'A real person replies', 'Send your details and a member of the team follows up with your quote.'],
            ] as [$ic, $t, $d])
                <li class="w-[78%] shrink-0 snap-center border-t border-white/25 pt-5 sm:w-auto sm:shrink">
                    <span class="grid place-items-center w-11 h-11 rounded-full border border-brand-gold text-brand-gold"><x-icon :name="$ic" class="w-6 h-6"/></span>
                    <h3 class="mt-4">{{ $t }}</h3>
                    <p class="mt-2 text-white/80">{{ $d }}</p>
                </li>
            @endforeach
        </ul>
    </div>
</section>
