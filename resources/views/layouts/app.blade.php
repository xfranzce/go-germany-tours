@php
    $seo = $seo ?? \App\Support\Seo::make();
    $fullTitle = \App\Support\Seo::titleWithBrand($seo['title']);
    $schemas = array_filter(array_merge([\App\Support\Seo::organizationSchema(), \App\Support\Seo::breadcrumbSchema($seo['breadcrumbs'])], $seo['schema']));
    $gtm = trim($site['gtm_id'] ?? '');
    $faviconMedia = \App\Models\Media::forPath($site['favicon'] ?? null);
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $fullTitle }}</title>
    <meta name="description" content="{{ $seo['description'] }}">
    <link rel="canonical" href="{{ $seo['canonical'] }}">
    @if($seo['noindex'])<meta name="robots" content="noindex, follow">@else<meta name="robots" content="index, follow, max-image-preview:large">@endif
    <meta name="theme-color" content="#003049">

    <meta property="og:site_name" content="{{ $site['brand_name'] }}">
    <meta property="og:type" content="{{ $seo['type'] }}">
    <meta property="og:title" content="{{ $fullTitle }}">
    <meta property="og:description" content="{{ $seo['description'] }}">
    <meta property="og:url" content="{{ $seo['canonical'] }}">
    @if($seo['image'])<meta property="og:image" content="{{ $seo['image'] }}">@endif
    <meta name="twitter:card" content="{{ $seo['image'] ? 'summary_large_image' : 'summary' }}">
    <meta name="twitter:title" content="{{ $fullTitle }}">
    <meta name="twitter:description" content="{{ $seo['description'] }}">
    @if($seo['image'])<meta name="twitter:image" content="{{ $seo['image'] }}">@endif

    @if($faviconMedia)
        <link rel="icon" href="{{ $faviconMedia->url(min($faviconMedia->widths)) }}" type="image/webp">
    @else
        <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'%3E%3Ccircle cx='32' cy='32' r='32' fill='%23003049'/%3E%3Ccircle cx='32' cy='32' r='27' fill='none' stroke='%23FCBF49' stroke-width='1.5'/%3E%3Cpath d='M32 10 32 32 27 32z' fill='%23D6282B'/%3E%3Cpath d='M32 10 37 32 32 32z' fill='%23A81E21'/%3E%3Cpath d='M32 54 32 32 37 32z' fill='%23FCBF49'/%3E%3Cpath d='M32 54 27 32 32 32z' fill='%23E0A21F'/%3E%3C/svg%3E">
    @endif

    @foreach(\App\Support\Seo::fontPreloads() as $font)
        <link rel="preload" href="{{ $font }}" as="font" type="font/woff2" crossorigin>
    @endforeach
    @stack('head')
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @foreach($schemas as $schema)
        <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
    @endforeach

    <script>window.dataLayer = window.dataLayer || [];</script>
    @if($gtm)
        <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer',@json($gtm));</script>
    @endif
    {!! \App\Support\Snippets::render('head') !!}
</head>
<body class="min-h-screen flex flex-col" x-data>
@if($gtm)
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ $gtm }}" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
@endif
{!! \App\Support\Snippets::render('body_start') !!}
<a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:z-[100] focus:bg-white focus:p-3 focus:m-2 focus:rounded">Skip to content</a>

@include('partials.header')

<main id="main" class="flex-1">
    @yield('content')
</main>

@include('partials.footer')
@include('partials.modal')

@stack('scripts')
{!! \App\Support\Snippets::render('body_end') !!}
</body>
</html>
