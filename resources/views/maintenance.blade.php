<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Back soon | {{ $site['brand_name'] }}</title>
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#003049">
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-brand-navy-dark text-white">
@php $tel = preg_replace('/[^+\d]/', '', $site['phone'] ?? ''); @endphp
<main class="grid min-h-screen place-items-center px-4 py-12 text-center">
    <div class="max-w-xl">
        <p class="flex items-center justify-center gap-3 font-display text-2xl font-semibold"><x-logo-mark class="size-12 shrink-0"/>{{ $site['brand_name'] }}</p>
        <p class="eyebrow centered mt-10 text-brand-gold">Be right back</p>
        <h1 class="mt-4 text-white">We will be back <em class="font-medium italic text-brand-gold">soon.</em></h1>
        <span class="flag-rule centered" aria-hidden="true"></span>
        <p class="mt-6 text-base text-white/85 sm:text-lg">{{ $message }}</p>
        @if($tel || ! empty($site['email']))
            <ul class="mt-8 flex flex-wrap items-center justify-center gap-x-8 gap-y-3 border-t border-white/20 pt-6 font-bold">
                @if($tel)<li><a class="inline-flex items-center gap-2 hover:text-brand-gold" href="tel:{{ $tel }}"><x-icon name="phone" class="h-4 w-4"/> {{ $site['phone_display'] ?: $site['phone'] }}</a></li>@endif
                @if(! empty($site['email']))<li><a class="inline-flex items-center gap-2 hover:text-brand-gold" href="mailto:{{ $site['email'] }}"><x-icon name="mail" class="h-4 w-4"/> {{ $site['email'] }}</a></li>@endif
            </ul>
        @endif
    </div>
</main>
</body>
</html>
