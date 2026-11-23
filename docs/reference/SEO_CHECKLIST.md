# SEO and launch checklist

## Built in (verify, don't rebuild)
- Fully server-rendered HTML, one `<h1>` per page (a test enforces it), semantic landmarks (`header`, `nav`, `main`, `footer`), skip link, descriptive link text.
- Unique `<title>` and meta description per page (edit in Admin), canonical URL, Open Graph + Twitter cards, per-page `noindex` toggle.
- JSON-LD: `TravelAgency` (all pages), `BreadcrumbList`, `FAQPage` (tour type pages, `/faqs` and packages with FAQs; the home page shows no FAQs so it has none), `TouristTrip` (packages; **no price Offer is emitted while a package is flagged Sample**), `Article` (guides).
- `/sitemap.xml` auto-generated (excludes drafts, noindex, thank-you, admin); `/robots.txt` blocks `/admin`, `/thank-you`, `/enquiry` and lists the sitemap. Filtered package listings (`?type=`/`?destination=`) are `noindex`.
- Images: WebP, `srcset`, width/height set (no layout shift), lazy below the fold, hero preloaded, alt text mandatory in admin. Fonts self-hosted, `font-display: swap`, preloaded. About 30 KB gzipped JS and 15 KB CSS (brotli) on public pages. reCAPTCHA loads only when a form is used.
- Security headers, HTTPS HSTS header when served over HTTPS, `.htaccess` compression and long caching for `/build`.

## Lighthouse (latest run 2026-11-24, Lighthouse 13, Chrome headless)
Pages: the home page `/` and one package page `/packages/germany-fairytale-towns-coach-tour`, each at **mobile** (412x823, simulated slow 4G, 4x CPU) and **desktop**. Run after the Bavarian video hero, the bright home sections, the blog section, the count-up numbers and the fixed-background adventure band. **Nothing is below 90 after one fix (below).**

| Page | Size | Perf | A11y | Best practices | SEO | LCP |
|---|---|---|---|---|---|---|
| `/` | Mobile | 97 (93-97) | 100 | 96* | 100 | 1.3 s |
| `/` | Desktop | 100 | 100 | 96* | 100 | 0.4 s |
| `/packages/germany-fairytale-towns-coach-tour` | Mobile | 97 | 100 | 96* | 100 | 2.4 s |
| `/packages/germany-fairytale-towns-coach-tour` | Desktop | 100 | 100 | 96* | 100 | 0.7 s |

CLS 0 on mobile, 0.038 on the home page desktop (green); TBT 0-180 ms. Mobile Speed Index varies between runs (a run straight after editing a Blade file showed 5.9 s and a score of 93; re-run it).
\* Best practices reads 96 through the proxy (see below): Chrome logging CORS errors for assets linked to `APP_URL` (:8000) from the :8001 proxy, not a site problem.

**What was below 90 and the fix (2026-11-24).** The home page scored **63 mobile / 64 desktop** (TBT 10 s): the "Start your Germany adventure" band got a `position: fixed` (parallax) background, and the lazy video loader (`x-video-bg`) uses an IntersectionObserver, which treats a fixed element as always on screen, so the 1080p `adventure.mp4` started downloading and decoding at page load next to the hero video. Fix: the band carries `data-video-root` and `x-video-bg` watches that section instead of the video itself. Rule: **any `x-video-bg` that is `position: fixed` needs `data-video-root` on its section.** Blocking either video individually also restored 99, which is how it was traced.

After the **bright "fairytale" home hero** (2026-11-20, later the same day): home mobile 96-97 (LCP 2.6 s, Speed Index 1.2-1.3 s), home desktop 99 (CLS 0.03), accessibility/SEO 100. The hero photo is deliberately served from the 480w (phones) / 1280w (desktop) files via `sizes`, because the full-width variants of that photo are large; keep that in mind if the hero image is changed.

Earlier run, 2026-11-19 (before the later changes above): home mobile 99-100, package mobile 95-97, desktop 100, and the fixes listed under "What was fixed" below.
**How it was measured.** The live host (Hostinger CDN) answers Lighthouse with `403`, so the live site cannot be audited with it (use PageSpeed Insights or Chrome DevTools on the live site instead). The numbers above are from a local copy behind `scripts/lighthouse-proxy.mjs`, which serves `public/` files directly with brotli and the production cache headers (`php artisan serve` alone is single-threaded and uncompressed: it scored 77 and 86 on mobile purely because of that). Best practices reads 96 if you test through the proxy because assets are linked to `APP_URL` (:8000) and Chrome logs CORS errors; test `php artisan serve` directly for that category, or set `APP_URL=http://127.0.0.1:8001`.
```
php artisan serve                      # terminal 1
node scripts/lighthouse-proxy.mjs      # terminal 2 (project root)
npx lighthouse http://127.0.0.1:8001/ --preset=desktop --view      # or omit --preset for mobile
```
MySQL must be running (XAMPP) for the local copy to render. Run each mobile test a couple of times and ignore a first run straight after editing a Blade file (view recompile makes FCP spike to about 4.5 s).

**What was fixed (everything that scored under 90 or lost points):**
- Accessibility (home 96): the "Itineraries" and counter blocks were `<dl>`s with the `<dd>` before the `<dt>`. Now `<dt>` then `<dd>`, shown value-over-label with `flex-col-reverse`.
- SEO (home 92): 21 "More info" links had no descriptive text. They now read "Explore Self Drive" (tour types) and "View details" (packages).
- Header logo link: `aria-label` ("Go Germany Tours home") did not contain the visible text; it is now the plain brand name.
- Signature cards: image `sizes` match the new mobile carousel width.
- Package page: preload for the first gallery photo.
- Gotcha found on the way: do **not** put `sr-only` (absolutely positioned) text inside the home page carousels. It made Chrome report an off-screen card photo as the LCP element and the mobile score fell to 61-88. Use visible descriptive text or `aria-label` for names instead (but Lighthouse `link-text` only reads visible text).

**Not covered:** other pages (about, contact, guides, tour types) were not re-run this time; the 2026-11-11 results were 97-98 for `/contact` and 95-96 for `/packages`. The photos are served with 7-day cache headers (`/build` is immutable for a year); long-lived caching for `/storage` is a possible later gain.

## Before launch
- [ ] Replace every `[CONFIRM: ...]`, SAMPLE and PLACEHOLDER item (CONTENT_TO_CONFIRM.md).
- [ ] Real **reCAPTCHA keys** (domain registered), test keys removed.
- [x] Legal company name set ("Go German Tours"; it appears in the consent text).
- [ ] Terms and Privacy **reviewed by a lawyer**; bump the terms version.
- [ ] Phone/email/address set; notification emails set and a real enquiry email received.
- [ ] GTM ID set; conversion fires once on `/thank-you` (Tag Assistant). GHL: `ghl:setup`, `ghl:test`, one live enquiry appears with UTMs.
- [ ] `APP_ENV=production`, `APP_DEBUG=false`, HTTPS forced, `www`/non-www redirect chosen, canonical host matches.
- [ ] Search Console: verify domain, submit `sitemap.xml`; check Coverage/Enhancements for structured data.
- [ ] Rich Results Test on `/faqs` (FAQ) and one package.
- [ ] Titles <= 60-70 chars, descriptions 120-160 chars, unique; share image set; favicon set.
- [ ] Every image has meaningful alt text; photo credits page still linked (CC BY).
- [ ] Mobile check at 360, 768, 1280: no horizontal scroll, tap targets, sticky header, modal, quiz.
- [ ] Keyboard-only pass through the quiz and forms (focus visible); contrast AA (palette is checked: tangerine is never used for small text on white).
- [ ] Google Ads: message match (H1/sub-heading edited per campaign in Admin > Pages > Home), click-to-call number shown, final URL with UTM suffix, auto-tagging on.
- [ ] 404 page, `/robots.txt`, `/sitemap.xml` open on the live domain.
- [ ] Cookie/consent banner needs for your target markets reviewed (US ads only is lighter; EU/UK visitors need more). Not built.
