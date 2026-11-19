# Prompt: build a sister site for a new country

Two ways to do this. **Option A is faster and safer.**

**Option A (recommended): copy this repo and re-skin it.** Copy the project folder to a new folder, delete `.git`, run `git init`, create a new database, new `.env`, and give Claude Code the prompt below with "the existing code is already in this folder". Everything that works today (forms, GHL, Google Sheets, tracking, admin) comes with it.

**Option B: build from nothing.** Start an empty folder and paste the prompt below. Claude has to rebuild every feature, so expect many rounds of fixes.

Fill in the `<<...>>` values first. Never paste passwords or API keys into the prompt; Claude will ask for them or you enter them later in the Admin screens.

---

## THE PROMPT (copy everything below this line)

You are building **<<BRAND NAME, e.g. Go Greece Tours>>**, a Google Ads landing-page website for travel to **<<COUNTRY>>**. It is a sister site of an existing site, "Go Germany Tours", and must have the same features, structure, admin and business rules, with all content, imagery, places and copy changed for <<COUNTRY>>.

### 0. Project facts (fill in)
- Brand name: <<...>>  Legal company name: <<...>>  Domain: <<...>>
- Country / main regions and cities: <<...>>
- Audience and language: <<e.g. US travellers aged 35 to 65, American English>>
- Currency (only if prices are ever shown): <<...>>
- Tour types (3 in the original: Self Drive, Chauffeur Driven, Coach Tours): <<keep / change>>
- Brand colours and fonts: <<keep the original palette, or give new hex values>>
- Sister brands for the "Our Partners" strip: <<names + logo files>>
- Hosting: <<e.g. Hostinger shared hosting over SSH>>

### 1. Stack and conventions
- Laravel 12 (PHP 8.2+), server-rendered Blade, Tailwind CSS v4 via Vite, Alpine.js, MySQL (SQLite only for tests). No SPA, keep JavaScript small. Develop on XAMPP (Windows). Keep it conventional: a skeleton the team will iterate on.
- Keep a `CLAUDE.md` in the repo root describing the folder map, the business rules below and the gotchas. **Update it whenever you change anything it describes.**
- All design tokens live only in `resources/css/app.css` (`@theme`). Use `brand-*` colour utilities and `font-display` / `font-sans`. Accent colour only behind dark text or as a border. WCAG AA.
- Public pages: one `<h1>` per page, SEO through one `Seo::make()` helper (title, meta description, canonical, Open Graph, JSON-LD). Images through a `<x-picture>` component (WebP, srcset, width/height, lazy unless priority).
- Section headings: small eyebrow, plain `<h2>`, a short rule, intro text. Never override heading sizes on sections.
- Write files UTF-8 without BOM. Write tests (PHPUnit, SQLite) for every feature and keep the suite green.
- Never invent business facts (prices, reviews, awards, years in business, licences, team names, addresses). Use clearly marked placeholders `[CONFIRM: ...]`, `SAMPLE`, `PLACEHOLDER`, and list every one in `docs/reference/CONTENT_TO_CONFIRM.md`. Testimonials stay hidden until they are real.

### 2. Public website (pages and sections)
1. **Header**: transparent over the top of the page with white text, turns solid white after scrolling, no top bar. Mega menus: **Tours** (one column per tour type with its packages and photo cards) and **About Us** (About Us, Meet Our Team, Testimonials, FAQs). The mobile menu is an accordion mega menu. Pages that start on a light background set a `solid_header` section flag.
2. **Home page** in this order: hero (headline and trust points on the left, the hero quiz on the right; on phones the trust points sit in one row) → social proof / intro → signature journeys (packages carousel) → "Travel your way" cards for each tour type (photo, number, name, summary, a 2x2 tick list of inclusions, "Get a Quote" and "Ask Us a Question" buttons) → "Why" section with a decorative dashed map of the country and route → "How it works" → guest reviews → FAQ → travel-guide blog carousel (offset carousel, category tag, excerpt, mouse drag-scroll) → Early Bird offer band (25% off, month computed as today + 3 months on every request, opens the quote modal) → light closing call-to-action band → partners strip → footer.
3. **Tour type pages** (`/tours/{slug}`): photo hero with the hero quiz on the right (full-colour photo, left-to-right navy gradient overlay, short top gradient for the header), About section with a "What to expect" checklist, packages, then the shared home sections (banner, why, how, reviews, FAQ, partners). FAQs can be tied to one tour type or to the home page.
4. **Packages**: listing page with the same hero and shared sections; package detail page with itinerary, highlights, inclusions, exclusions, gallery and a sticky quote form in the sidebar; package cards component.
5. **Destinations** pages (kept but not linked in the menu), **Travel guides** blog (index + post, categories), **About** (company story, why us, "your way" section, partners), **Meet Our Team** (intro + 3-column cards, click opens a profile pop-up), **Testimonials**, **FAQs**, **Contact** (intro, contact details with address, email, phone, hours and social links, plus the enquiry form), **Thank you** page, **Terms**, **Privacy**, **404**, `sitemap.xml` and a `robots.txt` route (there must be no static `public/robots.txt`).
6. **Optional background videos** for the hero, banner and closing band from `public/videos/{hero,banner,cta}.mp4`; if a file is missing the photo shows.
7. **Mobile carousels**: any snap-scroll row with `data-carousel` gets arrows and dots from the shared JavaScript.
8. Footer, logo mark component (compass mark plus wordmark, replaceable by an uploaded logo in Site settings), favicon inline in the layout.

### 3. Enquiry system (the core of the site)
- **Every form posts to one endpoint** `POST /enquiry` handled by one `EnquiryService` that validates and saves. Never create a second submission path. One Blade component `<x-enquiry-form>` is used by: the hero quiz, the pop-up modal (opened by every "Get a Quote" / "Ask Us a Question" / tour-type / package button), the package sidebar, and the contact page.
- **Hero quiz**: multi-step, glass-style card (translucent navy, blur, gold single-colour icons with no circles, gold progress bar), steps and options are database-driven and editable in Admin. The step `key` names the answer everywhere; never change keys of live steps. The last step is the contact form in a frosted panel with placeholders instead of labels. There is a no-JavaScript fallback form.
- **Fields**: first name, last name, email, phone with a country picker (dial codes), travel date (date picker that blocks past days), "second quote" Yes/No, optional special request, package context, consent checkbox, reCAPTCHA.
- **Phone numbers** are stored in E.164. The country chosen next to the phone is saved as the enquiry country and sent to the CRM as the contact's country; never send a fixed default.
- **Consent**: fixed text in config with a `{company}` placeholder, checkbox required and unticked by default, enforced in the browser and on the server. Store as evidence: consent flag, exact text shown, terms version, timestamp, IP, user agent and page URL. A "terms version" setting is bumped when legal text changes. Do not reword the consent text without legal sign-off.
- **reCAPTCHA v2** (checkbox) verified server-side after all other validation, **fails closed** if Google is unreachable. The Google script loads lazily when someone focuses or touches a form, with explicit rendering so several widgets and the modal work. Keys are entered in Admin > reCAPTCHA (secret stored encrypted and never shown, a "Test secret key" button, a warning when Google's public test keys are live), with `.env` as fallback. Local `.env` uses Google's public test keys.
- **Spam and abuse**: honeypot field (filled = fake success, nothing saved), rate limit 3 per minute and 10 per hour per IP, validation rules for **every** field the controller reads (only validated keys reach the controller).
- **Tracking**: on the first GET capture UTM parameters, `gclid`, `gbraid`, `wbraid`, referrer and landing page, plus Google Ads ValueTrack parameters (`utm_device`, `utm_adcreative`, `utm_adposition`), into encrypted first-touch and latest-touch cookies for 30 days (first touch written once). Save both as JSON on the enquiry. The "source" is the UTM source, else `google-ads` if there is a gclid, else the referrer host, else `direct`.
- **GTM**: container ID in Site settings; the snippet only renders when set. dataLayer events: `enquiry_submitted` (AJAX success) and `enquiry_thank_you` (thank-you page, only once straight after a real submission; the Google Ads conversion attaches to this one).
- **Notifications**: a staff email for each enquiry, queued, never blocking the enquiry.

### 4. Integrations (all optional, all off until configured, none may ever block an enquiry)
- **GoHighLevel CRM**: entered in Admin > GoHighLevel (token stored encrypted, never shown again, on/off switch, "Test connection", "Create missing custom fields"), `.env` as fallback. Off whenever there is no token or location ID. Service classes: field-name constants (the single source of field names, matched by exact name), client, payload builder, sync; queued job with retries and backoff; status, error and attempts stored on the enquiry with a retry button in the enquiry screen. Send contact, country, quiz answers as `Quiz: <Key>` fields, first-touch and last-touch fields (returning contacts keep their First values), plain fields `utm_source`, `utm_medium`, `utm_campaign`, `utm_content`, `utm_term`, `utm_device`, `utm_gclid`, `utm_adcreative`, `utm_adposition` and `form_source`. Provide `ghl:setup` and `ghl:test` commands. Tests must never reach the real CRM.
- **Google Sheets**: Admin > Google Sheets (Administrators only). The owner pastes a small Google Apps Script into their own sheet, deploys it as a Web app (execute as me, access: Anyone), and pastes the plain `https://script.google.com/macros/s/.../exec` URL (not the company-domain `/a/macros/` form) into the admin. URL stored encrypted, a shared token is embedded in the script shown on the page, with a Copy button, an on/off switch and an "Add a test row" button. One tab per form (Hero quiz, Get a Quote including the modal, Ask a Question, Contact). Columns are matched by header name; fixed columns plus one column per quiz question key; the script adds new columns on the right; phone numbers kept as text. The send runs after the response, queued with retries; status and error are stored on the enquiry, which has a "Send to sheet now" button. Explain in the admin page that Workspace accounts may block "Anyone" access and a personal Gmail account then works.

### 5. Admin (`/admin`, separate guard, model `AdminUser`)
- Roles: `admin` (everything) and `editor` (only packages, posts, pages, media, tour types, destinations; new admin routes are admin-only unless added to the editor list, enforced by a middleware).
- Sidebar with icons: Dashboard, Enquiries, Packages, Tour types (with summary, "What to expect" points, inclusions), Destinations, Hero quiz, FAQs (with "Shows on" tour type), Testimonials (hidden by default), Team, Travel guides (with category), Pages (CMS text for each page), Media, Site settings (company, contact details, address, hours, social links, GTM ID, terms version, logo), GoHighLevel, Google Sheets, reCAPTCHA, Admin users.
- Enquiries: list with filters and CSV export, detail screen with all evidence, status, internal notes, GHL status and retry, Sheets status and send-now, delete.
- Rich text is edited with Trix and sanitised on save through an allow-list helper; output with `{!! !!}` only for sanitised content. Images are processed to WebP (quality 85, widths 480 to 3840) with a regenerate command. Settings are key/value in the database with defaults in config, cached, and shared to all views.

### 6. Content and seed data for <<COUNTRY>>
- Seed realistic **starter** content so the site looks complete: tour types, destinations/regions, 6 to 9 sample packages marked `[SAMPLE]` with itinerary, 6 or more travel-guide posts across the categories (self-drive, chauffeur driven, coach tours), FAQs for the home page and for each tour type, 6 team placeholders, how-it-works steps, reasons-why points, partner logos, and the hero quiz steps (time of year, travel style, group size, nights, flights booked, budget).
- Imagery: use properly licensed photos (record credits in a `credits.json`) or generate images; never use unlicensed photos. The decorative country map must use public-domain data (Natural Earth).
- Copy is written in the brand voice for the audience above. Use no invented statistics, awards, prices or reviews.

### 7. Deployment
- A `scripts/deploy.sh` that builds assets, commits and pushes, then over SSH pulls on the server, installs Composer dependencies without dev packages, runs migrations, imports seed images, caches config/routes/views and checks the live URL returns 200. The SSH password comes from an environment variable and is never stored in the repo. Document it in `docs/reference/DEPLOYMENT.md`. Migrations that add data must be safe to run on an existing site.

### 8. How to work
1. Start by reading the folder and writing a short plan; then build in this order: foundation and design tokens → layout, header, footer → database and models → admin → public pages → enquiry system and quiz → tracking → reCAPTCHA → GHL → Google Sheets → SEO, sitemap, robots → seed content and images → tests → deployment script → docs.
2. After each stage run the test suite and fix failures before moving on.
3. Finish with an acceptance check: submit one enquiry from each form (hero quiz, modal, package sidebar, contact) and confirm it is saved with consent evidence and tracking, appears in the admin, reaches GHL and the sheet when those are configured, fires both dataLayer events, and that the honeypot, rate limit and reCAPTCHA failure paths behave as specified.
4. At the end list everything that needs my input (placeholders, API keys, domain, real photos, legal text) in `CONTENT_TO_CONFIRM.md`.

---

## Things to change per country (checklist for Option A)

- `config/gogermany.php` (rename to the new brand), brand name, legal company name, domain, `.env` (`APP_NAME`, `APP_URL`, database name).
- Logo mark and wordmark, favicon, colours in `resources/css/app.css` if the brand differs.
- The decorative map component (`germany-map`): replace the outline and route with the new country's.
- Seeders and images: tour types, destinations, packages, posts, FAQs, quiz options (currency in the budget step), team, partners, how-it-works, reasons-why.
- Page copy and SEO titles (watch for the words "Germany" and "German" in views, seeders, migrations and tests: `grep -ri german`).
- Terms, Privacy and consent wording need legal review for the new company and country.
- New GHL sub-account or location, new Google Sheet and Apps Script (a new token is generated), new reCAPTCHA keys for the new domain, new GTM container and Google Ads conversion.
- New hosting and deploy settings in `.deploy.env`.
