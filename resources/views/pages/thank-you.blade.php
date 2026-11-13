@extends('layouts.app')

@section('content')
<section class="py-20 bg-brand-custard-light">
    <div class="container-x max-w-2xl text-center">
        <span class="mx-auto grid place-items-center w-16 h-16 rounded-full bg-brand-gold text-brand-navy"><x-icon name="check" class="w-9 h-9"/></span>
        <h1 class="text-4xl mt-5">Thank you, we have your enquiry</h1>
        <p class="mt-4 text-lg text-ink-soft">A member of the team will be in touch soon with your quote. Please check your email (and spam folder) for our reply.</p>
        <p class="mt-8"><x-button :href="route('packages.index')" variant="outline">Browse packages</x-button></p>
    </div>
</section>

{{-- Ads conversion hook: fires once, only straight after a real submission. Attach the Google Ads conversion to this event in GTM. --}}
@if(!empty($done) && !empty($done['id']))
    <script>
        window.dataLayer = window.dataLayer || [];
        window.dataLayer.push({ event: 'enquiry_thank_you', form_name: @json($done['form']), package_name: @json($done['package']), enquiry_id: @json($done['id']) });
    </script>
@endif
@endsection
