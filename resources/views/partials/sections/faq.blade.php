{{-- 11. FAQ --}}
@if($faqs->isNotEmpty())
<section class="py-16 sm:py-24" aria-labelledby="faq-title" id="faq">
    <div class="container-x max-w-3xl">
        <x-section-heading id="faq-title" eyebrow="FAQ" :title="$faqTitle ?? $page->text('faq_title', 'Frequently asked questions')"/>
        <div class="space-y-3">
            @foreach($faqs as $faq)<x-faq-item :question="$faq->question" :open="$loop->first">{{ $faq->answer }}</x-faq-item>@endforeach
        </div>
    </div>
</section>
@endif
