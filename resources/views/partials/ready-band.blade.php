{{-- Closing "Ready for X?" band with Get a free quote + Ask us a question (destination and travel-style pages).
     Params: $eyebrow, $before (plain heading text), $emphasis (italic red part), $subject (used in the quote / question pre-fill). --}}
<section class="bg-brand-cream py-16 text-center sm:py-24" aria-labelledby="ready-title">
    <div class="container-x max-w-2xl">
        <p class="eyebrow centered text-brand-red-dark">{{ $eyebrow }}</p>
        <h2 id="ready-title" class="mt-4">{{ $before }} <em class="font-medium italic text-brand-red">{{ $emphasis }}</em></h2>
        <span class="flag-rule centered" aria-hidden="true"></span>
        <p class="mt-5 text-base text-ink-soft sm:text-lg">Tell us when you want to go and who is coming. We will send a free quote.</p>
        <div class="mt-8 flex flex-wrap justify-center gap-3">
            <x-button data-title="Get a quote: {{ $subject }}" data-prefill="Interested in: {{ $subject }}."
                      @click="$store.modal.show({form:'package_quote', ...$el.dataset})">Get a free quote <x-icon name="arrow-right" class="w-5 h-5"/></x-button>
            <x-button variant="outline" data-title="Ask us a question" data-subtitle="{{ $subject }}" data-prefill="Question about {{ $subject }}: "
                      @click="$store.modal.show({form:'package_question', ...$el.dataset})">Ask us a question</x-button>
        </div>
    </div>
</section>
