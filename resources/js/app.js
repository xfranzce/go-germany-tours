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
        } else if (opts.modal) {
            // The modal form exists on every page but stays hidden: show the captcha as soon as the modal opens, so people see it without having to click a field first.
            this.$watch('$store.modal.open', (open) => { if (open) this.$nextTick(render); });
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

/* ------------------------------------------------------------------
 | Mobile carousels: any [data-carousel] row gets prev/next arrows and
 | dots under it so people can tell it swipes. data-carousel="md" = the
 | breakpoint where the row stops being a carousel; data-carousel-light
 | for dark backgrounds.
 * ------------------------------------------------------------------ */
function initCarousels() {
    document.querySelectorAll('[data-carousel]').forEach((track) => {
        const items = [...track.children];
        if (items.length < 2) return;
        const bp = track.dataset.carousel || 'md';
        const light = track.hasAttribute('data-carousel-light');
        const hide = bp === 'sm' ? 'sm:hidden' : 'md:hidden';
        const arrow = (dir) => `<button type="button" data-dir="${dir}" aria-label="${dir < 0 ? 'Previous' : 'Next'}" class="grid size-10 place-items-center rounded-full border-2 transition ${light ? 'border-white text-white hover:bg-white hover:text-brand-navy' : 'border-brand-navy text-brand-navy hover:bg-brand-navy hover:text-white'}"><svg viewBox="0 0 24 24" class="size-4 ${dir < 0 ? 'rotate-180' : ''}" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></button>`;
        const nav = document.createElement('div');
        nav.className = `mt-4 flex items-center justify-center gap-4 ${hide}`;
        nav.innerHTML = arrow(-1)
            + `<div class="flex items-center gap-2" aria-hidden="true">${items.map(() => `<span class="h-2 w-2 rounded-full transition-all ${light ? 'bg-white/40' : 'bg-brand-navy/25'}"></span>`).join('')}</div>`
            + arrow(1);
        track.after(nav);
        const dots = [...nav.querySelectorAll('span')];
        let cur = 0;
        const paint = () => {
            const mid = track.scrollLeft + track.clientWidth / 2;
            cur = items.reduce((b, el, i) => (Math.abs(el.offsetLeft + el.offsetWidth / 2 - mid) < Math.abs(items[b].offsetLeft + items[b].offsetWidth / 2 - mid) ? i : b), 0);
            if (track.scrollLeft + track.clientWidth >= track.scrollWidth - 4) cur = items.length - 1;
            if (track.scrollLeft <= 4) cur = 0;
            dots.forEach((d, i) => {
                d.classList.toggle('w-6', i === cur);
                d.classList.toggle('w-2', i !== cur);
                d.classList.toggle(light ? 'bg-brand-gold' : 'bg-brand-red', i === cur);
                d.classList.toggle(light ? 'bg-white/40' : 'bg-brand-navy/25', i !== cur);
            });
        };
        nav.addEventListener('click', (e) => {
            const b = e.target.closest('button');
            if (!b) return;
            const to = Math.max(0, Math.min(items.length - 1, cur + Number(b.dataset.dir)));
            track.scrollTo({ left: items[to].offsetLeft - (track.clientWidth - items[to].offsetWidth) / 2, behavior: 'smooth' });
        });
        track.addEventListener('scroll', () => requestAnimationFrame(paint), { passive: true });
        paint();
    });
}
document.addEventListener('DOMContentLoaded', initCarousels);

/** Mouse drag-to-scroll for [data-drag-scroll] rows (touch already scrolls natively). */
function initDragScroll() {
    document.querySelectorAll('[data-drag-scroll]').forEach((el) => {
        let down = false, moved = false, startX = 0, startLeft = 0;
        el.classList.add('cursor-grab');
        el.addEventListener('dragstart', (e) => e.preventDefault());
        el.addEventListener('pointerdown', (e) => {
            if (e.pointerType !== 'mouse' || e.button !== 0) return;
            down = true; moved = false; startX = e.clientX; startLeft = el.scrollLeft;
        });
        window.addEventListener('pointermove', (e) => {
            if (!down) return;
            const dx = e.clientX - startX;
            if (!moved && Math.abs(dx) > 5) {
                moved = true;
                el.classList.remove('snap-mandatory', 'cursor-grab');
                el.classList.add('cursor-grabbing', 'select-none');
                el.style.scrollSnapType = 'none';
            }
            if (moved) el.scrollLeft = startLeft - dx;
        });
        const end = () => {
            if (!down) return;
            down = false;
            if (moved) {
                el.classList.remove('cursor-grabbing', 'select-none');
                el.classList.add('cursor-grab');
                el.style.scrollSnapType = '';
                el.classList.add('snap-mandatory');
            }
        };
        window.addEventListener('pointerup', end);
        window.addEventListener('pointercancel', end);
        // a drag must not count as a click on a card link
        el.addEventListener('click', (e) => { if (moved) { e.preventDefault(); e.stopPropagation(); moved = false; } }, true);
    });
}
document.addEventListener('DOMContentLoaded', initDragScroll);


Alpine.start();
