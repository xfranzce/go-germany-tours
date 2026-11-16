@extends('layouts.app')

@section('content')
@include('partials.page-header', ['title' => 'Frequently asked questions', 'lead' => 'Quick answers about our tours, quotes and planning.', 'crumbs' => [['Home', route('home')], ['About Us', route('about')], ['FAQs', null]]])

<section class="py-14 sm:py-20" aria-label="Frequently asked questions">
    <div class="container-x max-w-3xl">
        @if($faqs->isEmpty())
            <p class="text-center text-lg text-ink-soft">Our FAQs are on their way. In the meantime, ask us anything and we will reply personally.</p>
        @else
            <div class="space-y-3">
                @foreach($faqs as $faq)<x-faq-item :question="$faq->question" :open="$loop->first">{{ $faq->answer }}</x-faq-item>@endforeach
            </div>
        @endif

        <div class="mt-12 rounded-[var(--radius-card)] bg-brand-custard-light p-8 text-center">
            <p class="font-display text-2xl text-brand-navy sm:text-3xl">Still have a question?</p>
            <p class="mt-2 text-ink-soft">Ask us anything or request a free, no-obligation quote.</p>
            <p class="mt-5 flex flex-wrap justify-center gap-3">
                <x-button @click="$store.modal.show()">Ask us a question</x-button>
                <x-button :href="route('contact')" variant="outline">Contact us</x-button>
            </p>
        </div>
    </div>
</section>
@endsection
