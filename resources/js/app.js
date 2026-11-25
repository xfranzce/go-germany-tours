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
        this.initPhone();
        // $refs only exist once Alpine has built the form's children, so look the captcha box up after that.
        this.$nextTick(() => {
            const el = this.$refs.captcha;
            if (!el) return;
            const render = () => captcha.render(el);
            if (opts.eager) {
                render();
            } else {
                // Only load Google's script once the visitor starts using the form (keeps pages fast and cookie-free until then).
                ['focusin', 'pointerdown', 'touchstart'].forEach((ev) => this.$el.addEventListener(ev, render, { once: true, passive: true }));
            }
        });
    },

    /** Phone: the chosen country's dial code is written into the phone box, and the length is checked per country (same table as the server, Countries::LENGTHS). */
    initPhone() {
        const form = this.$el, sel = form.querySelector('[name="phone_country"]'), input = form.querySelector('[name="phone"]');
        let table = {};
        try { table = JSON.parse(form.dataset.phones || '{}'); } catch { return; }
        if (!sel || !input) return;
        const dial = () => (table[sel.value] || [])[0] || '';
        // Put the new dial code in front, replacing a dial code that is already there ("+44 7911..." -> "+1 7911...").
        const apply = () => {
            const d = dial();
            if (!d) return;
            const rest = input.value.trim().replace(/^\+\d{0,4}\s*/, '');
            input.value = `${d} ${rest}`;
            if (this.errors.phone) this.checkPhone();
        };
        const onlyDial = () => input.value.replace(/\D/g, '') === dial().replace(/\D/g, '');
        // Every form starts with the default country's dial code in the box (compact forms included).
        if (!input.value.trim()) input.value = `${dial()} `;
        sel.addEventListener('change', apply);
        // Re-picking the country that is already selected fires no "change", so also fill an emptied box when the select is used.
        ['focus', 'click'].forEach((ev) => sel.addEventListener(ev, () => { if (!input.value.trim()) input.value = `${dial()} `; }));
        input.addEventListener('focus', () => { if (!input.value.trim()) input.value = `${dial()} `; });
        input.addEventListener('blur', () => { if (!onlyDial()) this.checkPhone(); });
        input.addEventListener('input', () => { if (this.errors.phone) this.checkPhone(); });
        this.checkPhone = () => {
            const [d, min, max] = table[sel.value] || ['', 4, 14];
            const country = (sel.options[sel.selectedIndex]?.text || '').replace(/\s*\(.*$/, '');
            let digits = input.value.replace(/\D/g, '');
            const dd = d.replace(/\D/g, '');
            if (input.value.trim().startsWith('+') && digits.startsWith(dd)) digits = digits.slice(dd.length);
            if (sel.value !== 'IT') digits = digits.replace(/^0+/, '');
            const expect = min === max ? `${min} digits` : `${min} to ${max} digits`;
            let msg = '';
            if (!digits.length) msg = 'Please enter your phone number.';
            else if (digits.length < min) msg = `This number is too short. Numbers in ${country} have ${expect} after ${d}.`;
            else if (digits.length > max) msg = `This number is too long. Numbers in ${country} have ${expect} after ${d}.`;
            const { phone, ...others } = this.errors;
            this.errors = msg ? { ...others, phone: [msg] } : others;
            return !msg;
        };
    },
    checkPhone() { return true; },

    async submit(event) {
        const form = event.target;
        this.generalError = '';
        if (!form.reportValidity()) return;
        if (!this.checkPhone()) { form.querySelector('[name="phone"]')?.focus(); return; }
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
    ready: false,     // true once Alpine has taken over from the server-rendered first question
    touched: false,   // true after the first click, so only later steps animate in
    holdH: 0,         // height of the server-rendered first question, kept for the interactive one so the card does not jump
    init() { this.holdH = this.$refs.ssr ? Math.ceil(this.$refs.ssr.getBoundingClientRect().height) : 0; this.ready = true; },
    get total() { return this.steps.length; },
    get onForm() { return this.i >= this.total; },
    get step() { return this.steps[this.i] || null; },
    get pct() { return Math.round((this.i / (this.total + 1)) * 100); },
    pick(label) {
        this.answers[this.step.key] = label;
        setTimeout(() => this.advance(), 180);
    },
    advance() {
        this.touched = true;
        if (this.i < this.total) this.i++;
        this.$nextTick(() => this.$refs.card?.querySelector('[data-autofocus]')?.focus({ preventScroll: true }));
    },
    next() { if (this.answers[this.step.key]) this.advance(); },
    back() { this.touched = true; if (this.i > 0) this.i--; },
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
        const hide = bp === 'all' ? '' : bp === 'sm' ? 'sm:hidden' : 'md:hidden';
        const arrow = (dir) => `<button type="button" data-dir="${dir}" aria-label="${dir < 0 ? 'Previous' : 'Next'}" class="grid size-10 place-items-center rounded-full border-2 transition ${light ? 'border-white text-white hover:bg-white hover:text-brand-navy' : 'border-brand-navy text-brand-navy hover:bg-brand-navy hover:text-white'}"><svg viewBox="0 0 24 24" class="size-4 ${dir < 0 ? 'rotate-180' : ''}" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></button>`;
        const nav = document.createElement('div');
        nav.className = `mt-4 flex items-center justify-center gap-4 ${hide}`;
        track.after(nav);
        // One "position" per card step; when several cards are visible (desktop) there are fewer positions than cards.
        const step = () => (items[1].offsetLeft - items[0].offsetLeft) || items[0].offsetWidth || 1;
        const positions = () => Math.max(1, items.length - Math.max(1, Math.round((track.clientWidth + 20) / step())) + 1);
        let dots = [], cur = 0, count = 0;
        const build = () => {
            count = positions();
            nav.innerHTML = arrow(-1)
                + `<div class="flex items-center gap-2" aria-hidden="true">${Array.from({ length: count }, () => `<span class="h-2 w-2 rounded-full transition-all ${light ? 'bg-white/40' : 'bg-brand-navy/25'}"></span>`).join('')}</div>`
                + arrow(1);
            dots = [...nav.querySelectorAll('span')];
            paint();
        };
        const paint = () => {
            cur = Math.min(count - 1, Math.max(0, Math.round(track.scrollLeft / step())));
            if (track.scrollLeft + track.clientWidth >= track.scrollWidth - 4) cur = count - 1;
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
            const to = Math.max(0, Math.min(count - 1, cur + Number(b.dataset.dir)));
            const max = track.scrollWidth - track.clientWidth;
            // centre the card on phones (one card visible), align it to the start when several are visible
            const left = to === count - 1 ? max : count === items.length ? items[to].offsetLeft - (track.clientWidth - items[to].offsetWidth) / 2 : to * step();
            track.scrollTo({ left: Math.max(0, Math.min(max, left)), behavior: 'smooth' });
        });
        track.addEventListener('scroll', () => requestAnimationFrame(paint), { passive: true });
        let rt; addEventListener('resize', () => { clearTimeout(rt); rt = setTimeout(() => { if (positions() !== count) build(); else paint(); }, 150); });
        build();
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

/* ------------------------------------------------------------------
 | Scroll reveal: a section marked data-reveal="<effect>" animates its
 | content in when it scrolls into view. Effects: up, rise, left, split
 | (first block from the left, last from the right), pop, zoom, blur,
 | fade. Rows marked data-reveal-stagger animate card by card. Nothing
 | is hidden for visitors who prefer reduced motion or without
 | IntersectionObserver, and the helper classes are removed once the
 | animation is done so hover effects on the cards work as normal.
 * ------------------------------------------------------------------ */
function initReveal() {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches || !('IntersectionObserver' in window)) return;
    const sections = [...document.querySelectorAll('[data-reveal]')];
    if (!sections.length) return;

    const io = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) return;
            const sec = entry.target;
            io.unobserve(sec);
            sec.classList.add('rv-in');
            const items = sec._rvItems || [];
            setTimeout(() => items.forEach((el) => {
                el.classList.remove('rv', 'rv-up', 'rv-rise', 'rv-left', 'rv-right', 'rv-pop', 'rv-zoom', 'rv-blur', 'rv-fade');
                el.style.removeProperty('--rv-d');
            }), 650 + Math.min(items.length, 7) * 110 + 300);
        });
    }, { threshold: 0.12, rootMargin: '0px 0px -8% 0px' });

    sections.forEach((sec) => {
        const effect = sec.dataset.reveal || 'up';
        // content blocks: the section's visible children (skip decorative backgrounds), or what is inside a lone wrapper
        let kids = [...sec.children].filter((c) => !c.matches('.absolute, [aria-hidden="true"], script, style'));
        if (kids.length === 1) kids = [...kids[0].children].filter((c) => !c.matches('.absolute, script, style'));
        if (!kids.length) return;
        const items = [];
        kids.forEach((k) => (k.hasAttribute('data-reveal-stagger') ? items.push(...k.children) : items.push(k)));
        items.forEach((el, i) => {
            let fx = effect;
            if (effect === 'split') fx = i === 0 ? 'left' : i === items.length - 1 ? 'right' : 'up';
            el.classList.add('rv', `rv-${fx}`);
            el.style.setProperty('--rv-d', `${Math.min(i, 6) * 110}ms`);
        });
        sec._rvItems = items;
        io.observe(sec);
    });
}
document.addEventListener('DOMContentLoaded', initReveal);

// Number counters: <dd data-count-to="18"> counts up from 0 the first time it scrolls into view.
// The real number is already in the HTML (no-JS, search engines, reduced motion), so nothing is lost if this does not run.
function initCounters() {
    const els = document.querySelectorAll('[data-count-to]');
    if (!els.length || !('IntersectionObserver' in window) || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    const run = (el) => {
        const to = parseInt(el.dataset.countTo, 10);
        if (!Number.isFinite(to) || to <= 0) return;
        const dur = 1600, t0 = performance.now();
        const tick = (now) => {
            const p = Math.min((now - t0) / dur, 1);
            el.textContent = String(Math.round(to * (1 - Math.pow(1 - p, 3))));   // ease-out
            if (p < 1) requestAnimationFrame(tick);
        };
        el.textContent = '0';
        requestAnimationFrame(tick);
    };
    const io = new IntersectionObserver((entries) => {
        entries.forEach((e) => { if (e.isIntersecting) { io.unobserve(e.target); run(e.target); } });
    }, { threshold: 0.6 });
    els.forEach((el) => io.observe(el));
}
document.addEventListener('DOMContentLoaded', initCounters);

Alpine.start();
