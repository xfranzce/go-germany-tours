@props(['src', 'poster' => null, 'class' => ''])
{{-- Looping muted background video from public/videos/{src}. Renders nothing if the file is missing, so the photo behind it shows.
     The file is only fetched once the page has loaded AND the video is near the screen, so it never delays the first paint. --}}
@if(file_exists(public_path('videos/'.$src)))
    <video class="{{ $class }} opacity-0 transition-opacity duration-[1500ms]" muted loop playsinline preload="none" aria-hidden="true" tabindex="-1" x-on:playing="$el.classList.remove('opacity-0')"
           @if($poster) poster="{{ $poster }}" @endif
           data-src="{{ asset('videos/'.$src).'?v='.filemtime(public_path('videos/'.$src)) }}"
           x-data x-init="
               if (window.matchMedia('(prefers-reduced-motion: reduce)').matches || window.matchMedia('(max-width: 767px)').matches || (navigator.connection && navigator.connection.saveData)) return;
               const go = () => { new IntersectionObserver((e, o) => { if (e[0].isIntersecting) { o.disconnect(); $el.src = $el.dataset.src; $el.play().catch(() => {}) } }, { rootMargin: '300px' }).observe($el) };
               document.readyState === 'complete' ? setTimeout(go, 400) : addEventListener('load', () => setTimeout(go, 400), { once: true })"></video>
@endif
