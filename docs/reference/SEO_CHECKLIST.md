# SEO and launch checklist

## Built in (verify, don't rebuild)
- Fully server-rendered HTML, one `<h1>` per page (a test enforces it), semantic landmarks (`header`, `nav`, `main`, `footer`), skip link, descriptive link text.
- Unique `<title>` and meta description per page (edit in Admin), canonical URL, Open Graph + Twitter cards, per-page `noindex` toggle.
- JSON-LD: `TravelAgency` (all pages), `BreadcrumbList`, `FAQPage` (home + packages with FAQs), `TouristTrip` (packages; **no price Offer is emitted while a package is flagged Sample**), `Article` (guides).
- `/sitemap.xml` auto-generated (excludes drafts, noindex, thank-you, admin); `/robots.txt` blocks `/admin`, `/thank-you`, `/enquiry` and lists the sitemap. Filtered package listings (`?type=`/`?destination=`) are `noindex`.
- Images: WebP, `srcset`, width/height set (no layout shift), lazy below the fold, hero preloaded, alt text mandatory in admin. Fonts self-hosted, `font-display: swap`, preloaded. About 30 KB gzipped JS and 7 KB CSS on public pages. reCAPTCHA loads only when a form is used.
- Security headers, HTTPS HSTS header when served over HTTPS, `.htaccess` compression and long caching for `/build`.

## Lighthouse (mobile, run 2026-11-11 against `php artisan serve`)
| Page | Perf | A11y | Best practices | SEO |
|---|---|---|---|---|
| `/` | 90-94 (varies per run) | 100 | 100 | 100 |
| `/packages` | 95-96 | 100 | 100 | 100 |
| `/packages/quedlinburg-and-the-harz-self-drive` | 91-93 | 100 | 100 | 100 |
| `/contact` | 97-98 | 100 | 100 | 100 |

Home and package pages sit only just above 90 (LCP about 3 s on simulated slow 4G; the hero image is the LCP element). Dev-server numbers; no gzip/HTTP caching, so production on Hostinger should be the same or better. Re-run on the live site (Chrome DevTools > Lighthouse > Mobile).

## Before launch
- [ ] Replace every `[CONFIRM: ...]`, SAMPLE and PLACEHOLDER item (CONTENT_TO_CONFIRM.md).
- [ ] Real **reCAPTCHA keys** (domain registered), test keys removed.
- [ ] Legal company name set (it appears in the consent text). Terms and Privacy **reviewed by a lawyer**; bump the terms version.
- [ ] Phone/email/address set; notification emails set and a real enquiry email received.
- [ ] GTM ID set; conversion fires once on `/thank-you` (Tag Assistant). GHL: `ghl:setup`, `ghl:test`, one live enquiry appears with UTMs.
- [ ] `APP_ENV=production`, `APP_DEBUG=false`, HTTPS forced, `www`/non-www redirect chosen, canonical host matches.
- [ ] Search Console: verify domain, submit `sitemap.xml`; check Coverage/Enhancements for structured data.
- [ ] Rich Results Test on home (FAQ) and one package.
- [ ] Titles <= 60-70 chars, descriptions 120-160 chars, unique; share image set; favicon set.
- [ ] Every image has meaningful alt text; photo credits page still linked (CC BY).
- [ ] Mobile check at 360, 768, 1280: no horizontal scroll, tap targets, sticky header, modal, quiz.
- [ ] Keyboard-only pass through the quiz and forms (focus visible); contrast AA (palette is checked: tangerine is never used for small text on white).
- [ ] Google Ads: message match (H1/sub-heading edited per campaign in Admin > Pages > Home), click-to-call number shown, final URL with UTM suffix, auto-tagging on.
- [ ] 404 page, `/robots.txt`, `/sitemap.xml` open on the live domain.
- [ ] Cookie/consent banner needs for your target markets reviewed (US ads only is lighter; EU/UK visitors need more). Not built.
