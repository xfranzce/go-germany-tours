{{-- Global enquiry modal. Opened with $store.modal.show({form, packageId, packageName, title, prefill}) --}}
@php $pct = trim($site['modal_discount_percent'] ?? ''); @endphp
<div id="enquiry-modal" x-data x-show="$store.modal.open" x-cloak @keydown.escape.window="$store.modal.hide()"
     data-title="{{ $pct !== '' ? "Fill out this form, save up to {$pct}% on your trip" : 'Start your adventure: get a free quote' }}"
     data-subtitle="It takes less than a minute. No obligation."
     class="fixed inset-0 z-[60] flex items-start sm:items-center justify-center p-3 sm:p-6 overflow-y-auto"
     role="dialog" aria-modal="true" aria-labelledby="modal-title">
    <div class="fixed inset-0 bg-brand-navy-dark/70" @click="$store.modal.hide()" aria-hidden="true"></div>
    <div class="relative bg-white rounded-2xl shadow-card w-full max-w-xl my-6 border-t-8 border-brand-tangerine"
         x-show="$store.modal.open" x-transition.opacity.duration.150ms x-trap.noscroll="$store.modal.open">
        <button type="button" class="absolute top-3 right-3 p-2 rounded-full hover:bg-brand-custard-light" @click="$store.modal.hide()">
            <span class="sr-only">Close</span><x-icon name="x" class="w-6 h-6"/>
        </button>
        <div class="p-5 sm:p-7">
            <h2 id="modal-title" class="text-2xl pr-8" x-text="$store.modal.title"></h2>
            <p class="mt-1 text-ink-soft" x-text="$store.modal.subtitle"></p>
            <template x-if="$store.modal.open">
                <div class="mt-5">
                    <x-enquiry-form form-id="modal" form-name="header_modal" :extras="true" modal="true" :eager="true"/>
                </div>
            </template>
        </div>
    </div>
</div>
