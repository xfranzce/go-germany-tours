{{-- Expects: $title (the page H1), optional $crumbs [[name,url]], $lead --}}
<section class="bg-brand-navy text-white">
    <div class="container-x py-10 sm:py-14">
        @if(!empty($crumbs))
            <nav aria-label="Breadcrumb" class="text-sm text-white/80 mb-3">
                <ol class="flex flex-wrap gap-x-2">
                    @foreach($crumbs as [$name, $url])
                        <li>@if($url)<a class="underline underline-offset-2 hover:text-brand-gold" href="{{ $url }}">{{ $name }}</a> <span aria-hidden="true">/</span>@else<span aria-current="page">{{ $name }}</span>@endif</li>
                    @endforeach
                </ol>
            </nav>
        @endif
        <h1 class="text-4xl sm:text-5xl max-w-4xl">{{ $title }}</h1>
        @if(!empty($lead))<p class="mt-3 text-lg text-white/90 max-w-3xl">{{ $lead }}</p>@endif
    </div>
</section>
