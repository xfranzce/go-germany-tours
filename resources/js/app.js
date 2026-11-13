import Alpine from 'alpinejs';
import focus from '@alpinejs/focus';

window.Alpine = Alpine;
Alpine.plugin(focus);

/* ------------------------------------------------------------------
 | reCAPTCHA v2 (checkbox), explicit rendering, loaded lazily so the
 | Google script only downloads when somebody actually uses a form.
 | Several widgets per page are supported (hero, modal, inline forms).
 * ------------------------------------------------------------------ */
let recaptchaLoading = null;
function loadRecaptcha() {
    if (!recaptchaLoading) {
        recaptchaLoading = new Promise((resolve, reject) => {
            window.__ggtRecaptchaReady = resolve;
            const s = document.createElement('script');
            s.src = 'https://www.google.com/recaptcha/api.js?render=explicit&onload=__ggtRecaptchaReady';
            s.async = true;
            s.defer = true;
            s.onerror = () => { recaptchaLoading = null; reject(new Error('reCAPTCHA failed to load')); };
            document.head.appendChild(s);
        });
    }
    return recaptchaLoading;
}

const captcha = {
    async render(el) {
        if (!el || el.dataset.widgetId || !el.dataset.sitekey) return;
        el.dataset.widgetId = 'pending';
        try {
            await loadRecaptcha();
            window.grecaptcha.ready(() => {
                el.dataset.widgetId = String(window.grecaptcha.render(el, {
                    sitekey: el.dataset.sitekey,
                    // The normal widget is 304px wide: use compact when the container is narrower.
                    size: (el.clientWidth || window.innerWidth) < 310 ? 'compact' : 'normal',
                }));
            });
        } catch {
            delete el.dataset.widgetId;
        }
    },
    reset(el) {
        const id = el && el.dataset.widgetId;
        if (id && id !== 'pending' && window.grecaptcha) window.grecaptcha.reset(Number(id));
    },
};

/* ------------------------------------------------------------------
 | Enquiry modal (one shared form; other buttons pre-configure it)
 * ------------------------------------------------------------------ */
Alpine.store('modal', {
    open: false,
    form: 'header_modal',
    packageId: '',
    title: '',
    subtitle: '',
    prefill: '',
    opener: null,
    defaults() {
        const el = document.getElementById('enquiry-modal');
        return { title: el?.dataset.title || 'Get your free quote', subtitle: el?.dataset.subtitle || '' };
    },
    show(opts = {}) {
        const d = this.defaults();
        this.form = opts.form || 'header_modal';
        this.packageId = opts.packageId || '';
        this.title = opts.title || d.title;
        this.subtitle = opts.subtitle ?? d.subtitle;
        this.prefill = opts.prefill || '';
        this.opener = document.activeElement;
        this.open = true;
    },
    hide() {
        this.open = false;
        this.opener?.focus?.();
    },
});

/* ------------------------------------------------------------------
 | Enquiry form behaviour: AJAX submit with inline errors, then redirect.
 | Without JavaScript the form posts normally and the server redirects.
 * ------------------------------------------------------------------ */
Alpine.data('enquiryForm', (opts = {}) => ({
    errors: {},
    sending: false,
    success: false,
    generalError: '',

    init() {
        const el = this.$refs.captcha;
        if (!el) return;
        const render = () => captcha.render(el);
        if (opts.eager) {
            this.$nextTick(render);
        } else {
            // Only load Google's script once the visitor starts using the form (keeps pages fast and cookie-free until then).
            ['focusin', 'pointerdown', 'touchstart'].forEach((ev) => this.$el.addEventListener(ev, render, { once: true, passive: true }));
        }
    },

    async submit(event) {
        const form = event.target;
        this.generalError = '';
        if (!form.reportValidity()) return;
        this.sending = true;
        this.errors = {};
        try {
            const res = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            });
            if (res.status === 422) {
                this.errors = (await res.json()).errors || {};
                captcha.reset(this.$refs.captcha);
                this.$nextTick(() => form.querySelector('[aria-invalid="true"], .field-error')?.scrollIntoView({ block: 'center', behavior: 'smooth' }));
            } else if (res.status === 429) {
                this.generalError = 'Too many attempts. Please wait a minute and try again.';
            } else if (res.ok) {
                const data = await res.json();
                this.success = true;
                window.dataLayer = window.dataLayer || [];
                window.dataLayer.push({ event: 'enquiry_submitted', form_name: opts.formName || form.elements.form_name?.value });
                setTimeout(() => { window.location.href = data.redirect; }, 700);
            } else {
                this.generalError = 'Sorry, something went wrong. Please try again or contact us directly.';
                captcha.reset(this.$refs.captcha);
            }
        } catch {
            this.generalError = 'Network problem. Please check your connection and try again.';
        } finally {
            this.sending = false;
        }
    },
}));

/* ------------------------------------------------------------------
 | Hero quiz: data-driven steps (from the database), answers kept in
 | Alpine state and submitted with the contact form in ONE request.
 * ------------------------------------------------------------------ */
Alpine.data('heroQuiz', (steps) => ({
    steps,
    i: 0,
    answers: {},
    get total() { return this.steps.length; },
    get onForm() { return this.i >= this.total; },
    get step() { return this.steps[this.i] || null; },
    get pct() { return Math.round((this.i / (this.total + 1)) * 100); },
    pick(label) {
        this.answers[this.step.key] = label;
        setTimeout(() => this.advance(), 180);
    },
    advance() {
        if (this.i < this.total) this.i++;
        this.$nextTick(() => this.$refs.card?.querySelector('[data-autofocus]')?.focus({ preventScroll: true }));
    },
    next() { if (this.answers[this.step.key]) this.advance(); },
    back() { if (this.i > 0) this.i--; },
    summary() {
        return this.steps.filter((s) => this.answers[s.key]).map((s) => this.answers[s.key]).join(' · ');
    },
}));

Alpine.start();
