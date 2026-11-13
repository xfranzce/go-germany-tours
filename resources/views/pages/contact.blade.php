@extends('layouts.app')

@section('content')
@include('partials.page-header', ['title' => $page->title, 'crumbs' => [['Home', route('home')], [$page->title, null]]])
@php $tel = preg_replace('/[^+\d]/', '', $site['phone'] ?? ''); @endphp
<div class="container-x py-12 grid gap-10 lg:grid-cols-[1fr_24rem]">
    <div>
        <div class="prose-ggt text-lg mb-8">{!! $content !!}</div>
        <div class="card p-5 sm:p-7 border-4 border-brand-tangerine max-w-2xl"><x-enquiry-form form-id="contact" form-name="contact" :extras="true" submit-label="Send My Enquiry"/></div>
    </div>
    <aside class="space-y-4 self-start">
        <h2 class="text-2xl">Contact details</h2>
        <ul class="space-y-3">
            @if($tel)<li class="flex gap-3"><x-icon name="phone" class="w-6 h-6 text-brand-red-dark"/><a class="font-bold underline" href="tel:{{ $tel }}">{{ $site['phone_display'] ?: $site['phone'] }}</a></li>@endif
            @if(!empty($site['email']))<li class="flex gap-3"><x-icon name="mail" class="w-6 h-6 text-brand-red-dark"/><a class="font-bold underline" href="mailto:{{ $site['email'] }}">{{ $site['email'] }}</a></li>@endif
            @if(!empty($site['address']))<li class="flex gap-3"><x-icon name="pin" class="w-6 h-6 text-brand-red-dark"/>{{ $site['address'] }}</li>@endif
            @if(!$tel && empty($site['email']) && empty($site['address']))<li class="text-ink-soft">Use the form to reach us. [CONFIRM: phone, email and address in Admin &gt; Site settings]</li>@endif
        </ul>
    </aside>
</div>
@endsection
