# CLAUDE.md: Go Germany Tours

Google Ads landing-page site for one GoTour brand. Server-rendered Laravel 12 (PHP 8.2+; developed on XAMPP PHP 8.5), Blade, Tailwind v4 (Vite), Alpine.js, MySQL (SQLite only in tests). Custom admin at `/admin`. Skeleton the team will iterate on: keep it conventional.
**Update this file whenever you change anything it describes.**

## Run it (XAMPP)
`composer setup` then `php artisan serve` (see README / docs/reference/LOCAL_XAMPP.md). Assets: `npm run build` (or `npm run dev`). Tests: `php artisan test`. MySQL must be running; DB `go_germany_tours`, user root, no password.

## Folder map
- `app/Http/Controllers/*` public pages; `Admin/*` admin screens (`routes/admin.php`, guard `admin`, model `AdminUser`)
- `app/Services/EnquiryService.php` ONE place that validates + saves every enquiry; `RecaptchaService`, `ImageService` (WebP q85, widths 480-3840px; `php artisan images:regenerate` re-encodes seeded images from `database/seed-images`)
- `app/Services/Ghl/*` GoHighLevel: `GhlFields` (field names), `GhlClient`, `GhlPayloadBuilder`, `GhlSync`; jobs in `app/Jobs`; commands `ghl:setup`, `ghl:test`
- Admin roles: `admin_users.role` (admin|editor); Editors only reach `AdminUser::EDITOR_AREAS` (packages, posts, pages, media, tour-types, destinations) via `EnsureAdminRole` middleware (alias `admin.role`). New admin routes are admin-only unless added to that list.
- `app/Http/Middleware/CaptureTracking.php` first/last-touch cookies; `SecurityHeaders.php`
- `app/Support/Seo.php` page SEO + JSON-LD; `Html.php` rich-text sanitiser; `Countries.php` dial codes + E.164
- `app/Models/Setting.php` key/value site settings (defaults in `config/gogermany.php`), shared to all views as `$site`
- `resources/css/app.css` ALL design tokens (`@theme`); `resources/js/app.js` Alpine: modal store, `enquiryForm`, `heroQuiz`, lazy reCAPTCHA; `admin.js` Trix + repeater
- `resources/views/components/*` button, section-heading, faq-item, form-field, enquiry-form, hero-quiz, package-card, picture, icon; `video-bg` (optional looping background videos from `public/videos/{hero,banner,cta}.mp4`; missing file = photo shows; see `public/videos/README.txt`); `components/admin/*` admin fields
- `database/seeders/*`, `database/seed-images/*` (licensed photos + `credits.json`)
- `public/images/partners/*` sister-brand logos for the home "Our Partners" strip (copied from gobritaintrips.com; edit the `$partners` array in `home.blade.php`)
- `docs/reference/*` guides; `tests/Feature/*`

## Business rules that are NOT obvious from the code
1. **Every enquiry form** posts to `POST /enquiry` (`EnquiryController` -> `EnquiryService`). Forms: hero quiz (`<x-hero-quiz>`, in the home hero on the right, headline and copy on the left; the destination dropdown and ticker were removed, as were the Destinations menu in the header and the footer column; destination pages/admin still exist), modal (also used for tour-type/package/"Ask us a question" buttons), package sidebar, contact. One Blade component `<x-enquiry-form>`. Never create a second submission path.
2. **Consent text is fixed** in `config/gogermany.php` (`consent_text`). The checkbox is required, unticked by default, enforced in browser AND server (`accepted`). `{company}` = legal company name from settings. Do not reword without legal sign-off; bump `terms_version` (Admin > Site settings) if Terms/Privacy/consent change.
3. **Evidence is stored per enquiry**: consent flag, exact text shown, terms version, timestamp, IP, user agent, page URL.
4. **reCAPTCHA v2** verified server-side after all other validation passes; **fails closed** (Google unreachable = rejected). The Google script loads lazily only when someone uses a form (focus/touch), explicit rendering so several widgets/modal work. Local `.env` uses Google's public TEST keys; real keys go in Admin > Site settings or `.env`.
5. Honeypot field `contact_url`: filled = silent fake success, nothing saved. Rate limit 3/min and 10/hour per IP (`config/gogermany.php`).
6. **Tracking**: UTMs/gclid/gbraid/wbraid/referrer/landing page captured on first GET into encrypted cookies `ggt_ft` (first touch, written once) and `ggt_lt` (latest), 30 days; saved on the enquiry as JSON and sent to GHL as `... First` / `... Last` custom fields. Returning GHL contacts keep their existing First values.
7. **GHL is OFF** whenever `GHL_API_TOKEN` or `GHL_LOCATION_ID` is empty. Secrets live only in `.env`. GHL/email failures never block an enquiry (queued, retried, status stored on the enquiry; retry button in admin). **Field names live only in `GhlFields`** and are matched by exact name: rename in GHL and in that class together. `phpunit.xml` force-blanks GHL env so tests never reach the CRM.
8. GTM snippet and dataLayer events: GTM ID in admin settings; snippet only rendered when set. Events: `enquiry_submitted` (on AJAX success), `enquiry_thank_you` (thank-you page, only once straight after a real submission; attach the Google Ads conversion to this).
9. Quiz steps/options are database-driven (Admin > Hero quiz). The step `key` names the answer in enquiries and in GHL (`Quiz: <Key>`); never change keys of live steps.
10. Placeholders are written `[CONFIRM: ...]` / `SAMPLE` / `PLACEHOLDER` and listed in `docs/reference/CONTENT_TO_CONFIRM.md`. **Never invent business facts** (prices, reviews, awards, years, licences). Terms/Privacy are starter templates needing legal review.
11. Phone numbers are stored E.164 (`Countries::e164`). Only `$request->validated()` keys reach a controller: **declare a rule for every field you read** (a missing rule once silently dropped quiz option ids).
12. Rich text goes through `Html::clean()` on save (allow-list); output is `{!! !!}` only for that sanitised content.

## Deploying
Live site (Hostinger) is updated with `GGT_SSH_PASSWORD=... bash scripts/deploy.sh "message"` (see docs/reference/DEPLOYMENT.md). After any local change, deploy it.

## Conventions
- Header menus: **Tours** mega menu (one column per tour type, with its packages) and **About Us** mega menu (About Us, Meet Our Team `/meet-our-team`, Testimonials `/testimonials`, FAQs `/faqs`). Team page is a CMS page (key `team`, Admin > Pages); Testimonials/FAQs pages read the same data as the home page. `AboutMenuPagesSeeder` adds the team page on existing sites.
- Header is transparent over the page top (white text) and turns solid white after scrolling; there is no top bar. Pages that start on a light background must add `@section('solid_header', '1')` (404, thank-you do). Page headers have extra top padding to sit under it.
- Section headings: eyebrow, then plain `<h2>` (global size, `mt-4`), `flag-rule`, intro `mt-5 text-base sm:text-lg`. Never override heading font sizes on section titles; prefer `<x-section-heading>`.
- Design tokens only in `resources/css/app.css` `@theme`; use `brand-*` colour utilities, `font-display`/`font-sans`. Tangerine only behind navy text or as a border (contrast). WCAG AA.
- Public views are Blade, server-rendered, one `<h1>` per page, SEO through `Seo::make()` in the controller. Images via `<x-picture>` (srcset + width/height, lazy unless `priority`).
- No Blade directives (`@js`) inside component-tag attributes; use `data-*` + `$el.dataset`.
- Write files UTF-8 **without BOM** (a BOM corrupts JSON responses). Don't use `Set-Content -Encoding utf8` on Windows PowerShell 5.1 for PHP.
- Keep JS small; no SPA. Update docs when behaviour changes.

## Gotchas
- `Setting` values are cached (`settings.all`) and memoised per process: tests call `Setting::flush()`.
- `php artisan migrate:fresh --seed` does not delete files in `storage/app/public/media`; clear it by hand if you reseed repeatedly.
- `public/robots.txt` must not exist (robots is a route).
- PHP 8.5 needs `\Pdo\Mysql::ATTR_SSL_CA` (patched in `config/database.php`).
- Git was not installed when this was generated: install Git for Windows, then `git init` and commit (`.gitignore` is ready).
