@php $tel = preg_replace('/[^+\d]/', '', $site['phone'] ?? ''); @endphp
<div class="bg-brand-navy-dark text-white text-sm">
    <div class="container-x flex items-center justify-between gap-3 py-1.5">
        <div class="flex items-center gap-x-5 gap-y-1 flex-wrap">
            @if(!empty($site['email']))
                <a href="mailto:{{ $site['email'] }}" class="hidden sm:inline-flex items-center gap-1.5 hover:text-brand-gold">
                    <x-icon name="mail" class="w-4 h-4"/> {{ $site['email'] }}
                </a>
            @endif
            @if($tel)
                <a href="tel:{{ $tel }}" class="inline-flex items-center gap-1.5 font-bold hover:text-brand-gold">
                    <x-icon name="phone" class="w-4 h-4"/> {{ $site['phone_display'] ?: $site['phone'] }}
                </a>
            @endif
            <a href="{{ route('contact') }}" class="hidden md:inline hover:text-brand-gold underline underline-offset-2">Get Instant Quote</a>
        </div>
        <button type="button" class="btn btn-gold btn-sm !min-h-8 !py-1" @click="$store.modal.show()">Start Your Adventure</button>
    </div>
</div>
