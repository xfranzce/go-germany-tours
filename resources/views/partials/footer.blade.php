@php $tel = preg_replace('/[^+\d]/', '', $site['phone'] ?? ''); @endphp
<footer class="bg-brand-navy text-white {{ trim(View::yieldContent('flush_footer')) ? '' : 'mt-16' }}">
    <div class="h-1.5 flex" aria-hidden="true"><span class="flex-1 bg-brand-navy-dark"></span><span class="flex-1 bg-brand-red"></span><span class="flex-1 bg-brand-gold"></span></div>
    <div class="container-x py-12 grid gap-10 sm:grid-cols-2 lg:grid-cols-4">
        <div class="lg:col-span-2">
            <p class="flex items-center gap-3 font-display text-2xl font-semibold"><x-logo-mark class="size-12 shrink-0"/><span class="flex flex-col leading-none"><span class="text-3xl">Go Germany</span><span class="mt-1.5 flex items-center gap-2 text-[0.65rem] font-semibold uppercase tracking-[0.42em]"><span class="h-px w-5 bg-brand-gold"></span>Tours</span></span></p>
            <p class="mt-3 max-w-md text-white/85">{{ $site['footer_text'] }}</p>
            <ul class="mt-5 space-y-2 text-white/90">
                @if($tel)<li><a class="inline-flex items-center gap-2 hover:text-brand-gold" href="tel:{{ $tel }}"><x-icon name="phone" class="w-4 h-4"/> {{ $site['phone_display'] ?: $site['phone'] }}</a></li>@endif
                @if(!empty($site['email']))<li><a class="inline-flex items-center gap-2 hover:text-brand-gold" href="mailto:{{ $site['email'] }}"><x-icon name="mail" class="w-4 h-4"/> {{ $site['email'] }}</a></li>@endif
                @if(!empty($site['address']))<li class="inline-flex items-start gap-2"><x-icon name="pin" class="w-4 h-4 mt-1"/> {{ $site['address'] }}</li>@endif
            </ul>
            @php $socials = array_filter(['Facebook' => $site['facebook_url'] ?? '', 'Instagram' => $site['instagram_url'] ?? '', 'YouTube' => $site['youtube_url'] ?? '']); @endphp
            @if($socials)
                <ul class="mt-4 flex gap-4">
                    @foreach($socials as $name => $url)<li><a class="underline underline-offset-2 hover:text-brand-gold" rel="noopener" target="_blank" href="{{ $url }}">{{ $name }}</a></li>@endforeach
                </ul>
            @endif
        </div>
        <nav aria-label="Explore">
            <p class="eyebrow text-brand-gold">Explore</p>
            <ul class="mt-3 space-y-2">
                <li><a class="hover:text-brand-gold" href="{{ route('packages.index') }}">All packages</a></li>
                @foreach(\App\Models\TourType::active()->get(['name', 'slug']) as $t)
                    <li><a class="hover:text-brand-gold" href="{{ route('tours.show', $t->slug) }}">{{ $t->name }}</a></li>
                @endforeach
                <li><a class="hover:text-brand-gold" href="{{ route('blog.index') }}">Travel guides</a></li>
            </ul>
        </nav>
        <nav aria-label="About us">
            <p class="eyebrow text-brand-gold">About us</p>
            <ul class="mt-3 space-y-2">
                <li><a class="hover:text-brand-gold" href="{{ route('about') }}">About Us</a></li>
                <li><a class="hover:text-brand-gold" href="{{ route('team') }}">Meet Our Team</a></li>
                <li><a class="hover:text-brand-gold" href="{{ route('testimonials') }}">Testimonials</a></li>
                <li><a class="hover:text-brand-gold" href="{{ route('faqs') }}">FAQs</a></li>
            </ul>
        </nav>
    </div>
    <div class="border-t border-white/15">
        <div class="container-x py-5 flex flex-col gap-3 md:flex-row md:items-center md:justify-between text-sm text-white/85">
            <p>&copy; {{ date('Y') }} {{ $site['company_name'] }}. All rights reserved.</p>
            <nav aria-label="Legal"><ul class="flex flex-wrap gap-x-5 gap-y-1">
                <li><a class="hover:text-brand-gold underline underline-offset-2" href="{{ route('about') }}">About</a></li>
                <li><a class="hover:text-brand-gold underline underline-offset-2" href="{{ route('contact') }}">Contact</a></li>
                <li><a class="hover:text-brand-gold underline underline-offset-2" href="{{ route('terms') }}">Terms and Conditions</a></li>
                <li><a class="hover:text-brand-gold underline underline-offset-2" href="{{ route('privacy') }}">Privacy Policy</a></li>
                <li><a class="hover:text-brand-gold underline underline-offset-2" href="{{ route('credits') }}">Photo credits</a></li>
            </ul></nav>
        </div>
    </div>
</footer>
