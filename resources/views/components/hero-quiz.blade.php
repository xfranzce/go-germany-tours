@php $steps = \App\Models\QuizStep::forQuiz(); @endphp
<section id="quote" aria-labelledby="quiz-title"
         class="bg-white text-brand-navy rounded-lg border-t-4 border-x border-b border-brand-tangerine border-x-brand-custard border-b-brand-custard shadow-2xl p-6 sm:p-8 min-h-[30rem] scroll-mt-28"
         x-data="heroQuiz(@js($steps))" x-ref="card">
    <h2 id="quiz-title" class="text-center !text-2xl sm:!text-3xl">Start your adventure</h2>

    <noscript>
        <p class="mt-2 text-center text-sm text-ink-soft">Tell us about your trip and we will send a quote.</p>
        <div class="mt-4"><x-enquiry-form form-id="hero-plain" form-name="hero_quiz" :extras="true" :in-noscript="true"/></div>
    </noscript>

    <div x-cloak class="mt-3">
        <div class="flex items-center justify-center gap-1.5 text-sm text-ink-soft">
            <x-icon name="lock" class="w-4 h-4"/> Takes less than 30 seconds
        </div>
        <div class="mt-3 h-2 rounded-full bg-brand-custard overflow-hidden" role="progressbar" aria-label="Quiz progress"
             :aria-valuenow="pct" aria-valuemin="0" aria-valuemax="100">
            <div class="h-full bg-brand-tangerine transition-all duration-300" :style="'width:' + pct + '%'"></div>
        </div>

        {{-- Question steps --}}
        <template x-if="!onForm && step">
            <fieldset class="mt-5 animate-fade-up" :key="i">
                <legend class="font-display font-bold text-xl leading-snug" x-text="step.question"></legend>
                <p class="text-xs text-ink-soft mt-1" x-text="'Question ' + (i + 1) + ' of ' + total"></p>

                <div x-show="step.type === 'tiles'" class="mt-4 grid grid-cols-2 gap-3">
                    <template x-for="(o, idx) in step.options" :key="o.label">
                        <button type="button" @click="pick(o.label)" :data-autofocus="idx === 0 ? '' : null"
                                class="flex flex-col items-center justify-center gap-1 text-center rounded-xl border-2 p-3 min-h-24 font-bold transition hover:border-brand-tangerine hover:bg-brand-custard-light focus-visible:border-brand-tangerine"
                                :class="answers[step.key] === o.label ? 'border-brand-tangerine bg-brand-custard' : 'border-brand-custard'"
                                :aria-pressed="answers[step.key] === o.label">
                            <span class="text-3xl leading-none" x-text="o.icon" aria-hidden="true"></span>
                            <span class="text-sm" x-text="o.label"></span>
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
            <div class="mt-5 animate-fade-up">
                <p class="font-display font-bold text-xl">Where should we send your quote?</p>
                <details class="mt-1 text-xs text-ink-soft" x-show="summary()"><summary class="cursor-pointer font-bold">Your answers &#10003; <span class="font-normal underline">show</span></summary><span class="mt-1 block" x-text="summary()"></span></details>
                <div class="mt-3">
                    <x-enquiry-form form-id="hero-quiz" form-name="hero_quiz" :quiz="true" :eager="true" submit-label="Send My Free Quote"/>
                </div>
                <button type="button" @click="back()" class="mt-3 text-sm font-bold underline underline-offset-2">&larr; Back</button>
            </div>
        </template>
    </div>
</section>
