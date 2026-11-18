@extends('layouts.app')

@section('content')
@include('partials.page-header', ['title' => $page->title, 'crumbs' => [['Home', route('home')], [$page->title, null]]])
@php
    $tel = preg_replace('/[^+\d]/', '', $site['phone'] ?? '');
    $phoneShown = $tel ? ($site['phone_display'] ?: $site['phone']) : '+00 000 000 0000 [PLACEHOLDER]';
    $emailShown = $site['email'] ?? '' ?: 'hello@example.com [PLACEHOLDER]';
    $addressShown = $site['address'] ?? '' ?: '123 Sample Street, City, Country [PLACEHOLDER: head office address]';
@endphp
<div class="container-x py-12 sm:py-16">
    <div class="mx-auto max-w-3xl text-center">
        <p class="eyebrow text-brand-red-dark">Get in touch</p>
        <h2 class="mt-4">We're here to help you explore Germany</h2>
        <span class="flag-rule mx-auto" aria-hidden="true"></span>
        <p class="mt-5 text-base sm:text-lg text-ink-soft">Have a question about a package or need help planning your trip? Our team of Germany travel experts is here to help every step of the way. [PLACEHOLDER: replace this intro]</p>
    </div>

    <div class="mt-12 grid gap-10 lg:grid-cols-[24rem_1fr] lg:items-start">
        <aside class="space-y-6">
            <ul class="space-y-5">
                <li class="flex gap-4"><x-icon name="pin" class="mt-1 h-6 w-6 shrink-0 text-brand-red-dark"/><div><p class="font-bold text-brand-navy">Head office</p><p class="text-ink-soft">{{ $addressShown }}</p></div></li>
                <li class="flex gap-4"><x-icon name="mail" class="mt-1 h-6 w-6 shrink-0 text-brand-red-dark"/><div><p class="font-bold text-brand-navy">Customer support email</p>@if(!empty($site['email']))<a class="text-ink-soft underline" href="mailto:{{ $site['email'] }}">{{ $site['email'] }}</a>@else<p class="text-ink-soft">{{ $emailShown }}</p>@endif</div></li>
                <li class="flex gap-4"><x-icon name="phone" class="mt-1 h-6 w-6 shrink-0 text-brand-red-dark"/><div><p class="font-bold text-brand-navy">Phone</p>@if($tel)<a class="text-ink-soft underline" href="tel:{{ $tel }}">{{ $phoneShown }}</a>@else<p class="text-ink-soft">{{ $phoneShown }}</p>@endif</div></li>
                <li class="flex gap-4"><x-icon name="clock" class="mt-1 h-6 w-6 shrink-0 text-brand-red-dark"/><div><p class="font-bold text-brand-navy">Operating hours</p>
                    <ul class="text-ink-soft">
                        <li>Monday to Friday: 9:00 AM to 6:00 PM</li>
                        <li>Saturday: 10:00 AM to 4:00 PM</li>
                        <li>Sunday: Closed (reply by email) [PLACEHOLDER: hours and time zone]</li>
                    </ul></div></li>
            </ul>
            @if(!empty($site['facebook_url']) || !empty($site['instagram_url']))
                <div class="flex gap-3 text-sm font-bold">
                    @if(!empty($site['facebook_url']))<a class="underline" href="{{ $site['facebook_url'] }}" rel="noopener" target="_blank">Facebook</a>@endif
                    @if(!empty($site['instagram_url']))<a class="underline" href="{{ $site['instagram_url'] }}" rel="noopener" target="_blank">Instagram</a>@endif
                </div>
            @endif
        </aside>

        <div>
            <div class="card border-4 border-brand-tangerine p-5 sm:p-7">
                <h3 class="text-2xl">Send us a message</h3>
                <p class="mt-2 mb-5 text-ink-soft">A travel expert will reply within 24 hours. [PLACEHOLDER: confirm reply time]</p>
                @if(trim(strip_tags($content ?? '')) !== '')<div class="prose-ggt mb-5">{!! $content !!}</div>@endif
                <x-enquiry-form form-id="contact" form-name="contact" :extras="true" submit-label="Send My Enquiry"/>
            </div>
        </div>
    </div>
</div>
@endsection
