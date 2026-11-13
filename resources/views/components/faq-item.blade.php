@props(['question', 'open' => false])
{{-- Native <details>: works without JavaScript and is fully crawlable --}}
<details class="group bg-white rounded-xl border border-brand-custard shadow-sm" @if($open) open @endif>
    <summary class="flex items-center justify-between gap-4 cursor-pointer list-none px-5 py-4 font-bold text-lg">
        <span>{{ $question }}</span>
        <x-icon name="chevron-down" class="w-5 h-5 shrink-0 transition-transform group-open:rotate-180"/>
    </summary>
    <div class="px-5 pb-5 text-ink-soft prose-ggt">{{ $slot }}</div>
</details>
