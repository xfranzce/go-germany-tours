@php $steps = \App\Models\QuizStep::forQuiz(); @endphp
<section id="quote" aria-labelledby="quiz-title"
         class="relative overflow-hidden rounded-2xl bg-white text-brand-navy shadow-2xl shadow-black/40 ring-1 ring-white/30 min-h-[26rem] scroll-mt-28"
         x-data="heroQuiz(@js($steps))" x-ref="card">
    <div class="relative overflow-hidden bg-gradient-to-br from-brand-navy-dark via-brand-navy to-brand-navy px-5 pb-5 pt-6 text-center text-white sm:px-7">
        <span class="absolute inset-x-0 top-0 h-1.5 bg-gradient-to-r from-brand-red via-brand-gold to-brand-red" aria-hidden="true"></span>
        <span class="pointer-events-none absolute -right-10 -top-10 size-36 rounded-full bg-brand-gold/20 blur-2xl" aria-hidden="true"></span>
        <h2 id="quiz-title" class="relative !text-2xl text-white sm:!text-3xl">Start your adventure</h2>
        <p class="relative mt-2 inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-1 text-xs font-bold uppercase tracking-[0.12em] text-brand-gold"><x-icon name="lock" class="h-3.5 w-3.5"/> Free quote &middot; under 30 seconds</p>
        <div class="relative mt-4 h-2.5 overflow-hidden rounded-full bg-white/20" role="progressbar" aria-label="Quiz progress"
             :aria-valuenow="pct" aria-valuemin="0" aria-valuemax="100">
            <div class="h-full rounded-full bg-gradient-to-r from-brand-gold to-brand-tangerine transition-all duration-500" :style="'width:' + Math.max(pct, 6) + '%'"></div>
        </div>
    </div>

    <div class="p-5 sm:p-7">
    <noscript>
        <p class="mt-2 text-center text-sm text-ink-soft">Tell us about your trip and we will send a quote.</p>
        <div class="mt-4"><x-enquiry-form form-id="hero-plain" form-name="hero_quiz" :extras="true" :in-noscript="true"/></div>
    </noscript>

    <div x-cloak>
        {{-- Question steps --}}
        <template x-if="!onForm && step">
            <fieldset class="animate-fade-up" :key="i">
                <p class="inline-flex items-center rounded-full bg-brand-gold/25 px-2.5 py-0.5 text-[0.7rem] font-extrabold uppercase tracking-[0.14em] text-brand-navy" x-text="'Question ' + (i + 1) + ' of ' + total"></p>
                <legend class="mt-2 font-display text-2xl font-bold leading-snug" x-text="step.question"></legend>

                <div x-show="step.type === 'tiles'" class="mt-4 grid grid-cols-2 gap-3">
                    <template x-for="(o, idx) in step.options" :key="o.label">
                        <button type="button" @click="pick(o.label)" :data-autofocus="idx === 0 ? '' : null"
                                class="group flex flex-col items-center justify-center gap-2 text-center rounded-2xl border-2 bg-white p-3 min-h-28 font-bold shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-brand-gold hover:bg-brand-custard-light hover:shadow-lg focus-visible:border-brand-gold"
                                :class="answers[step.key] === o.label ? 'border-brand-gold bg-brand-gold/20 ring-2 ring-brand-gold/40' : 'border-brand-custard'"
                                :aria-pressed="answers[step.key] === o.label">
                            <span class="grid size-14 place-items-center rounded-full bg-brand-custard-light text-3xl leading-none transition duration-200 group-hover:scale-110 group-hover:bg-white" x-text="o.icon" aria-hidden="true"></span>
                            <span class="text-sm text-brand-navy" x-text="o.label"></span>
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

                <button type="button" x-show="i > 0" @click="back()" class="mt-4 text-sm font-bold underline underline-offset-2">&larr; Back</button>
            </fieldset>
        </template>

        {{-- Final step: contact details --}}
        <template x-if="onForm">
            <div class="animate-fade-up">
                <p class="font-display text-2xl font-bold">Where should we send your quote?</p>
                <details class="mt-1 text-xs text-ink-soft" x-show="summary()"><summary class="cursor-pointer font-bold">Your answers &#10003; <span class="font-normal underline">show</span></summary><span class="mt-1 block" x-text="summary()"></span></details>
                <div class="mt-3">
                    <x-enquiry-form form-id="hero-quiz" form-name="hero_quiz" :quiz="true" :eager="true" submit-label="Send My Free Quote"/>
                </div>
                <button type="button" @click="back()" class="mt-3 text-sm font-bold underline underline-offset-2">&larr; Back</button>
            </div>
        </template>
    </div>
    </div>
</section>
