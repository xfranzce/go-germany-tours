@props(['src', 'poster' => null, 'class' => ''])
{{-- Looping muted background video from public/videos/{src}. Renders nothing if the file is missing, so the photo behind it shows. --}}
@if(file_exists(public_path('videos/'.$src)))
    <video class="{{ $class }}" autoplay muted loop playsinline preload="metadata" aria-hidden="true" tabindex="-1"
           @if($poster) poster="{{ $poster }}" @endif
           x-data x-init="if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) { $el.pause(); $el.removeAttribute('autoplay') }">
        <source src="{{ asset('videos/'.$src) }}" type="video/mp4">
    </video>
@endif
