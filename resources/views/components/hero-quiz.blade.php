@php $steps = \App\Models\QuizStep::forQuiz(); @endphp
<section id="quote" aria-labelledby="quiz-title"
         class="relative overflow-hidden rounded-2xl border border-white/25 bg-brand-navy-dark/45 text-white shadow-2xl shadow-black/40 backdrop-blur-xl min-h-[26rem] scroll-mt-28 supports-[not(backdrop-filter:blur(1px))]:bg-brand-navy-dark/85"
         x-data="heroQuiz(@js($steps))" x-ref="card">
    <span class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-brand-gold to-transparent" aria-hidden="true"></span>
    <div class="p-5 sm:p-7">
    <div class="text-center">
        <h2 id="quiz-title" class="!text-2xl text-white sm:!text-3xl">Start your adventure</h2>
        <p class="mt-2 inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-[0.14em] text-brand-gold"><x-icon name="lock" class="h-3.5 w-3.5"/> Free quote &middot; under 30 seconds</p>
        <div class="mt-4 h-1.5 overflow-hidden rounded-full bg-white/20" role="progressbar" aria-label="Quiz progress"
             :aria-valuenow="pct" aria-valuemin="0" aria-valuemax="100">
            <div class="h-full rounded-full bg-brand-gold transition-all duration-500" :style="'width:' + Math.max(pct, 6) + '%'"></div>
        </div>
    </div>

    <noscript>
        <p class="mt-4 text-center text-sm text-white/85">Tell us about your trip and we will send a quote.</p>
        <div class="mt-4 rounded-xl border border-white/60 bg-white/90 p-4 text-brand-navy shadow-lg backdrop-blur-md"><x-enquiry-form form-id="hero-plain" form-name="hero_quiz" :extras="true" :in-noscript="true"/></div>
    </noscript>

    <div x-cloak class="mt-6">
        {{-- Question steps --}}
        <template x-if="!onForm && step">
            <fieldset class="animate-fade-up" :key="i">
                <p class="text-[0.7rem] font-extrabold uppercase tracking-[0.16em] text-brand-gold" x-text="'Question ' + (i + 1) + ' of ' + total"></p>
                <legend class="mt-1 font-display text-2xl font-bold leading-snug text-white" x-text="step.question"></legend>

                <div x-show="step.type === 'tiles'" class="mt-4 grid grid-cols-2 gap-3">
                    <template x-for="(o, idx) in step.options" :key="o.label">
                        <button type="button" @click="pick(o.label)" :data-autofocus="idx === 0 ? '' : null"
                                class="group flex flex-col items-center justify-center gap-2 text-center rounded-2xl border bg-white/10 p-3 min-h-28 font-bold text-white transition duration-200 hover:-translate-y-0.5 hover:border-brand-gold hover:bg-white/20 focus-visible:border-brand-gold"
                                :class="answers[step.key] === o.label ? 'border-brand-gold bg-brand-gold/25 ring-1 ring-brand-gold' : 'border-white/25'"
                                :aria-pressed="answers[step.key] === o.label">
                            <span class="text-4xl leading-none transition duration-200 group-hover:scale-110 [filter:grayscale(1)_sepia(1)_saturate(4)_brightness(1.15)]" x-text="o.icon" aria-hidden="true"></span>
                            <span class="text-sm text-white" x-text="o.label"></span>
                        </button>
                    </template>
                </div>

                <div x-show="step.type === 'dropdown'" class="mt-4">
                    <label class="sr-only" :for="'q-' + step.key" x-text="step.question"></label>
                    <select :id="'q-' + step.key" class="field-input" x-model="answers[step.key]" data-autofocus>
                        <option value="">Select...</option>
                        <template x-for="o in step.options" :key="o.label"><option :value="o.label" x-text="o.label"></option></template>
                    </select>
                    <button type="button" class="btn btn-primary w-full mt-4" :disabled="!answers[step.key]" @click="next()">Next</button>
                </div>

                <button type="button" x-show="i > 0" @click="back()" class="mt-4 text-sm font-bold text-white/90 underline underline-offset-2 hover:text-brand-gold">&larr; Back</button>
            </fieldset>
        </template>

        {{-- Final step: contact details --}}
        <template x-if="onForm">
            <div class="animate-fade-up">
                <p class="font-display text-2xl font-bold text-white">Where should we send your quote?</p>
                <details class="mt-1 text-xs text-white/80" x-show="summary()"><summary class="cursor-pointer font-bold">Your answers &#10003; <span class="font-normal underline">show</span></summary><span class="mt-1 block" x-text="summary()"></span></details>
                <div class="mt-3 rounded-xl border border-white/60 bg-white/90 p-4 text-brand-navy shadow-lg backdrop-blur-md">
                    <x-enquiry-form form-id="hero-quiz" form-name="hero_quiz" :quiz="true" :eager="true" submit-label="Send My Free Quote"/>
                </div>
                <button type="button" @click="back()" class="mt-3 text-sm font-bold text-white/90 underline underline-offset-2 hover:text-brand-gold">&larr; Back</button>
            </div>
        </template>
    </div>
    </div>
</section>
