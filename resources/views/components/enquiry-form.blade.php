@props([
    'formId',                    // unique per form on the page (error bag + element ids)
    'formName',                  // hero_quiz | header_modal | package_quote | package_question | contact
    'package' => null,           // Package model for package forms
    'extras' => false,           // adds time of year, group size, nights
    'modal' => false,            // modal form: form_name/package are driven by the Alpine modal store
    'quiz' => false,             // inside the hero quiz: answers come from the parent Alpine scope
    'submitLabel' => 'Get My Free Quote',
    'eager' => false,            // render reCAPTCHA immediately (quiz final step, modal)
    'inNoscript' => false,       // true when this form is itself rendered inside a <noscript>
])
@php
    use App\Services\EnquiryService;
    use App\Services\RecaptchaService;
    use App\Support\Countries;

    $bag = ($errors ?? new \Illuminate\Support\ViewErrorBag)->getBag($formId);
    $siteKey = app(RecaptchaService::class)->siteKey();
    $company = e($site['company_name']);
    $consent = e(EnquiryService::consentText());
    $consent = preg_replace('/Terms and Conditions/', '<a href="'.route('terms').'" target="_blank" rel="noopener" class="underline font-bold">Terms and Conditions</a>', $consent, 1);
    $consent = preg_replace('/Privacy Policy/', '<a href="'.route('privacy').'" target="_blank" rel="noopener" class="underline font-bold">Privacy Policy</a>', $consent, 1);
    $countryOptions = collect(Countries::all())->map(fn ($c, $iso) => "{$c[0]} ({$c[1]})")->all();
    $submitted = old('form_id') === $formId;
    $compact = ($quiz || $modal) && ! $inNoscript;   // shorter layout: tighter spacing, paired fields, optional request box
@endphp
<form method="POST" action="{{ route('enquiry.store') }}" id="{{ $formId }}"
      x-data="enquiryForm({ eager: {{ $eager ? 'true' : 'false' }}, formName: @js($formName) })" @submit.prevent="submit($event)" class="{{ $compact ? 'space-y-3 [&_.field-input]:!min-h-11 [&_.field-input]:!py-2 [&_.field-label]:!mb-0.5' : 'space-y-4' }}" aria-labelledby="{{ $formId }}-legend">
    @csrf
    <span id="{{ $formId }}-legend" class="sr-only">Enquiry form</span>
    <input type="hidden" name="form_id" value="{{ $formId }}">
    @if($modal)
        <input type="hidden" name="form_name" :value="$store.modal.form">
        <input type="hidden" name="package_id" :value="$store.modal.packageId">
    @else
        <input type="hidden" name="form_name" value="{{ $formName }}">
        @if($package)<input type="hidden" name="package_id" value="{{ $package->id }}">@endif
    @endif
    <input type="hidden" name="page_url" value="{{ url()->full() }}">
    @if($quiz)
        <template x-for="(value, key) in answers" :key="key"><input type="hidden" :name="'quiz[' + key + ']'" :value="value"></template>
    @endif

    {{-- Honeypot: real people never see or fill this --}}
    <div style="position:absolute;left:-9999px;top:auto;width:1px;height:1px;overflow:hidden" aria-hidden="true">
        <label>Website <input type="text" name="contact_url" tabindex="-1" autocomplete="off"></label>
    </div>

    @if($submitted && $bag->any())
        <p class="rounded-lg bg-red-50 border border-brand-red text-brand-red-dark font-bold p-3" role="alert">Please check the highlighted fields below.</p>
    @endif

    <div class="grid {{ $compact ? 'gap-3' : 'gap-4' }} sm:grid-cols-2">
        <x-form-field name="first_name" label="First name" :form-id="$formId" :bag="$bag" required autocomplete="given-name"/>
        <x-form-field name="last_name" label="Last name" :form-id="$formId" :bag="$bag" required autocomplete="family-name"/>
    </div>
    <x-form-field name="email" type="email" label="Email" :form-id="$formId" :bag="$bag" required autocomplete="email"/>

    <div class="grid {{ $compact ? 'gap-3' : 'gap-4' }} grid-cols-[minmax(0,10rem)_1fr]">
        <x-form-field name="phone_country" type="select" label="Country code" :form-id="$formId" :bag="$bag" :options="$countryOptions" :value="'US'" required/>
        <x-form-field name="phone" type="tel" label="Phone" :form-id="$formId" :bag="$bag" required autocomplete="tel-national" placeholder="e.g. 555 123 4567"/>
    </div>

    @if($extras)
        <div class="grid gap-4 sm:grid-cols-3">
            <x-form-field name="time_of_year" type="select" label="Time of year" :form-id="$formId" :bag="$bag" :options="['Spring' => 'Spring', 'Summer' => 'Summer', 'Autumn' => 'Autumn', 'Winter' => 'Winter']"/>
            <x-form-field name="group_size" type="select" label="Group size" :form-id="$formId" :bag="$bag" :options="array_combine(array_merge(range(1, 8), ['9 or more']), array_merge(range(1, 8), ['9 or more']))"/>
            <x-form-field name="nights" type="select" label="Nights" :form-id="$formId" :bag="$bag" :options="array_combine(['3 to 4', '5 to 6', '7 to 8', '9 to 10', '11 to 14', '15 or more'], ['3 to 4', '5 to 6', '7 to 8', '9 to 10', '11 to 14', '15 or more'])"/>
        </div>
    @endif

    <div class="{{ $compact ? 'grid gap-3 sm:grid-cols-2 sm:items-end' : 'space-y-4' }}">
        <x-form-field name="travel_date" type="date" :min="now()->format('Y-m-d')" label="Preferred travel date (approx.)" :form-id="$formId" :bag="$bag"/>
        <x-form-field name="second_quote" type="select" :label="$compact ? 'Second quote from a partner?' : 'Would you like a second quote from a partner?'" :form-id="$formId" :bag="$bag"
                      :options="['Yes' => 'Yes', 'No' => 'No', 'Maybe' => 'Maybe, tell me more']"/>
    </div>
    @if($compact)
        <div x-data="{ more: {{ $modal ? 'true' : 'false' }} }">
            <button type="button" x-show="!more" @click="more = true; $nextTick(() => $refs.sr.querySelector('textarea').focus())"
                    class="text-sm font-bold text-brand-red-dark underline underline-offset-2 hover:text-brand-navy">+ Add a special request (optional)</button>
            <div x-ref="sr" x-show="more" x-cloak>
                <x-form-field name="special_request" type="textarea" label="Special request" :form-id="$formId" :bag="$bag" :rows="2"
                              :x-value="$modal ? '$store.modal.prefill' : null"/>
            </div>
        </div>
    @else
        <x-form-field name="special_request" type="textarea" label="Special request" :form-id="$formId" :bag="$bag" :rows="3"
                      :x-value="$modal ? '$store.modal.prefill' : null"/>
    @endif

    {{-- Required consent: unticked by default. Wording is fixed in config/gogermany.php --}}
    <div>
        <div class="flex items-start gap-3">
            <input id="{{ $formId }}-consent" type="checkbox" name="consent" value="1" required class="mt-1 h-5 w-5 shrink-0 accent-brand-red"
                   aria-describedby="{{ $formId }}-consent-err" @checked($submitted && old('consent'))>
            <label for="{{ $formId }}-consent" class="{{ $compact ? 'text-[0.7rem] leading-snug' : 'text-xs leading-relaxed' }} text-ink-soft">{!! $consent !!} <span class="text-brand-red font-bold" aria-hidden="true">*</span></label>
        </div>
        <div id="{{ $formId }}-consent-err" aria-live="polite">
            @if($bag->first('consent'))<p class="field-error">{{ $bag->first('consent') }}</p>@endif
            <template x-if="errors.consent"><p class="field-error" x-text="errors.consent[0]"></p></template>
        </div>
    </div>

    {{-- reCAPTCHA v2: rendered lazily so the Google script only loads when a form is used --}}
    <div>
        <div x-ref="captcha" data-sitekey="{{ $siteKey }}" class="min-h-[78px] max-w-full overflow-hidden"></div>
        {{-- No-JavaScript reCAPTCHA fallback (a <noscript> cannot be nested inside another <noscript>) --}}
        @if($inNoscript)
            <iframe src="https://www.google.com/recaptcha/api/fallback?k={{ $siteKey }}" title="reCAPTCHA" width="302" height="422" style="border:0;max-width:100%"></iframe>
            <textarea name="g-recaptcha-response" required class="field-input" placeholder="Paste the code shown above"></textarea>
        @else
            <noscript>
                <iframe src="https://www.google.com/recaptcha/api/fallback?k={{ $siteKey }}" title="reCAPTCHA" width="302" height="422" style="border:0;max-width:100%"></iframe>
                <textarea name="g-recaptcha-response" required class="field-input" placeholder="Paste the code shown above"></textarea>
            </noscript>
        @endif
        <div aria-live="polite">
            @if($bag->first('g-recaptcha-response'))<p class="field-error">{{ $bag->first('g-recaptcha-response') }}</p>@endif
            <template x-if="errors['g-recaptcha-response']"><p class="field-error" x-text="errors['g-recaptcha-response'][0]"></p></template>
        </div>
    </div>

    <p class="field-error" x-show="generalError" x-text="generalError" role="alert" x-cloak></p>

    <button type="submit" class="btn btn-primary w-full text-lg" :disabled="sending || success">
        <span x-show="!sending && !success">{{ $submitLabel }}</span>
        <span x-show="sending" x-cloak>Sending...</span>
        <span x-show="success" x-cloak>Thank you!</span>
    </button>
    <p x-show="success" x-cloak class="text-center font-bold text-brand-navy" role="status">Thank you! Your enquiry has been sent. Taking you to the next page...</p>
</form>
