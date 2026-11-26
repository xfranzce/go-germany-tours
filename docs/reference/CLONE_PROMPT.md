# Prompt: build a sister site (new country, or content copied from an existing website)

This file holds a prompt you paste into Claude Code to get a new site with **every function and feature of Go Germany Tours**, but with different content. There are two content modes:

- **Mode 1, new country:** fresh copy, places, packages and images for a country you name (for example Go Greece Tours).
- **Mode 2, copy an existing website:** the content (pages, tours, FAQs, team, contact details, images) is taken from a website you name (for example your current WordPress site), and rebuilt on this platform.

And two ways to start:

- **Option A (recommended): copy this repo and re-skin it.** Copy the project folder to a new folder, delete `.git`, run `git init`, create a new empty MySQL database, copy `.env.example` to `.env`, then paste the prompt with "the existing code is already in this folder". Everything that works today comes with it, so Claude only changes content and branding.
- **Option B: build from nothing.** Paste the prompt into an empty folder. Claude has to rebuild every feature; expect many rounds of fixes.

Before pasting: fill in the `<<...>>` values in section 0, delete the mode you are not using, and never paste passwords, API keys or SSH passwords into the prompt (enter them later in the admin screens, or give the SSH password only when Claude asks to deploy).

Keep this file up to date when features change (CLAUDE.md says so).

---

## THE PROMPT (copy everything below this line)

You are building **<<BRAND NAME, e.g. Go Greece Tours>>**, a Google Ads landing-page website for tours in **<<COUNTRY / REGION>>**. It is a sister site of "Go Germany Tours" and must have the **same features, structure, admin and business rules** listed below. Only the content, branding and imagery change.

<<Option A: The existing Go Germany Tours code is already in this folder. Read `CLAUDE.md` first, keep every feature working, and change content and branding only. Option B: This folder is empty; build everything below.>>

### 0. Project facts (fill in)
- Brand name: <<...>>   Legal company name: <<...>>   Domain: <<...>>
- Country / main regions and cities: <<...>>
- Audience and language: <<e.g. US travellers aged 35 to 65, American English (organize, color)>>
- Tour types (the original has 3: Self Drive, Chauffeur Driven, Coach Tours): <<keep / change>>
- Brand colours: <<keep the original palette, or give hex values>>   Fonts: <<keep Manrope (text) + Bodoni Moda (headings), or name others>>
- Sister brands for the "Our Partners" strip: <<names + logo files>>
- Contact details to show (phone, email, address, hours, social links): <<... or "take them from the source website">>
- Hosting: <<e.g. Hostinger shared hosting over SSH; host, port, user, app folder go in `.deploy.env`, never the password>>

**Content mode (keep ONE):**
- **Mode 1, new country.** Write fresh starter content for <<COUNTRY>> (section 6). Mark every package `[SAMPLE]` and every unknown fact `[CONFIRM: ...]`.
- **Mode 2, copy an existing website: <<https://source-site.com>>.** Read the source site page by page (home, about, team, every tour/package, FAQs, contact, blog posts, terms and privacy) and move its content onto this platform: real tour names, itineraries, prices, inclusions, FAQs, team members, contact details, testimonials and blog posts. Rules: copy text faithfully (fix only spelling and obvious formatting), never invent anything the source does not say, keep a list of every source URL you used in `docs/reference/CONTENT_SOURCES.md`, and reuse the source's images **only if the owner confirms they hold the rights** (ask me); otherwise use licensed or AI images and record them in `credits.json`. Anything the platform needs that the source does not have (for example quiz steps, "What to expect" points) gets a `[CONFIRM: ...]` placeholder. Testimonials copied from the source stay hidden until I confirm they are real and current.

### 1. Stack and conventions
- Laravel 12 (PHP 8.2+), server-rendered Blade, Tailwind CSS v4 via Vite, Alpine.js, MySQL (SQLite only for tests). No SPA, keep JavaScript small. Develop on XAMPP (Windows). Keep it conventional: a skeleton the team will iterate on.
- Keep a `CLAUDE.md` in the repo root with the folder map, the business rules below and the gotchas. **Update it whenever you change anything it describes.**
- Design tokens live only in `resources/css/app.css` (`@theme`): `brand-*` colours, `--font-sans` (text) and `--font-display` (headings), self-hosted variable fonts via `@fontsource-variable` (latin subset, `font-display: swap`, preloaded). Accent colour only behind dark text or as a border. WCAG AA. CTA buttons are one red (`.btn-primary`).
- Public pages: one `<h1>` per page, SEO through one `Seo::make()` helper (title, description, canonical, Open Graph, JSON-LD). Images through `<x-picture>` (WebP, srcset, width/height, lazy unless `priority`, `fetchpriority="low"` below the fold, `?v=<updated_at>` cache-busting on every URL).
- Section headings: eyebrow, plain `<h2>`, a short flag-style rule, intro text. Never override heading sizes on section titles.
- Write files UTF-8 **without BOM**. Write PHPUnit tests (SQLite) for every feature and keep the suite green.
- **Never invent business facts** (prices, reviews, awards, years in business, licences, team names, addresses). Use `[CONFIRM: ...]`, `SAMPLE`, `PLACEHOLDER` and list each one in `docs/reference/CONTENT_TO_CONFIRM.md`. Testimonials are hidden until real.

### 2. Public website
1. **Header**: transparent over the top of the page (white text), solid white after scrolling, no top bar. Menu order: Home, **Tours** mega menu (one column per tour type with its packages and photo cards, plus "All packages"), **Destinations** and **Travel Styles** mega menus (an image card per region / style plus an "All ..." link), **About Us** mega menu (About Us, Meet Our Team, Testimonials, FAQs), Contact, and a red "Free Quote" button. The mobile menu is an accordion mega menu. Pages that start on a light background set a `solid_header` flag.
2. **Home page** (top to bottom): full-screen **hero** with a background photo AND a looping background video of the same scene (photo shows first, video fades in once playing, not loaded on phones or with reduced motion), a left-to-right dark overlay and text shadows for readability, eyebrow + two-line headline with a gold italic highlighted phrase and hand-drawn underline + intro on the left, the **hero quiz** card on the right → a "Why book with us" strip of 4 promises (icon, one-line title, short text) → About intro with a large photo → "Travel your way" cards per tour type (photo, summary, 2x2 tick list from the tour type's inclusions, Get a Quote / Ask Us a Question) → Early-bird discount block (25% off, month = today + 3 months on every request, opens the quote modal) → one **package carousel per tour type** (white cards, arrows + dots at every screen size, 3 cards visible on desktop, mouse drag) → "Start your adventure" band with a **fixed (parallax) background** photo + video and count-up counters (destinations, tours, ways to travel: real database counts) → confidence points → reviews (hidden until real) → places marquee (edge to edge) → how it works (3 steps) → Our Partners logo strip (white) → travel journal (latest 6 posts, horizontal cards with arrows) → "Let's plan" closing band → footer. Every section except the hero has 90px top/bottom and 60px side padding from tablet width up.
3. **Tour type pages** (`/tours/{slug}`): photo hero with the hero quiz on the right, About section with a "What to expect" checklist, that type's packages, then shared sections (banner, why, how, reviews, FAQ for that tour type, partners). Shared sections live in `resources/views/partials/sections/*` so they are edited once.
4. **Packages**: listing page and detail page (itinerary, highlights, inclusions, exclusions, gallery, price with optional "was" price, sticky quote form in the sidebar); one package-card component.
5. **Destinations and Travel styles** (as on goeurotours.com): `/destinations` overview (quiz hero, then a card per destination: photo, tour count, name, tagline, Explore link) and a page per destination (quiz hero "Explore X", "Discover X" text with highlight-place chips from `Name: note` lines, "Trips that visit X" package grid with a "Filter all packages" link, "More destinations to explore" photo tiles, "Ready for X?" closing band); give every destination 5 tours; `/travel-styles` card grid and a page per style (Slow, Scenic, Luxury, Food & Wine, Cultural & Heritage, Adventure: tagline, summary, "What to expect" checklist, its packages). Packages belong to one destination and to any number of travel styles (ticked on the package form). All these pages use a photo hero with the hero quiz and the shared sections.
6. **Other pages**: Travel guides blog (index, post, categories), About (story, why us, partners), Meet Our Team (intro from the CMS + 3-column cards from the team table), Testimonials, FAQs, Contact (details + form), Thank you, Terms, Privacy, Photo credits, 404, Maintenance page, `sitemap.xml` and a `robots.txt` **route** (no static `public/robots.txt`).
7. **Shared front-end pieces**: `data-carousel="md|sm|all"` rows get arrows and dots (dots = scroll positions, so fewer dots when several cards are visible); `data-count-to="N"` counters count up once in view; scroll-reveal animations (`data-reveal`); `x-video-bg` lazy background videos (a fixed/parallax video needs `data-video-root` on its section or it loads at once and wrecks Lighthouse).
8. **Logo**: a compass logo mark + two-line wordmark component, replaceable by an uploaded logo in Site settings; favicon inline in the layout.
9. **Optional Google rating badge** in the hero: off by default, only shown when switched on in Site settings AND a real rating is entered. Never emit `aggregateRating` markup.

### 3. Enquiry system (the core of the site)
- **Every form posts to one endpoint** `POST /enquiry`, handled by one `EnquiryService` that validates and saves. Never a second submission path. One Blade component `<x-enquiry-form>` is used by the hero quiz, the pop-up modal (every Get a Quote / Ask Us a Question / tour-type / package button), the package sidebar and the contact page.
- **Hero quiz**: multi-step glass card (translucent navy, blur, gold progress bar), steps and options database-driven and editable in Admin > Hero quiz. Option tiles show thin gold **line-art SVG icons** mapped from the emoji stored on each option (`QuizIcons`; unmapped emoji fall back to the emoji). The step `key` names the answer everywhere; never change keys of live steps. The first question is also rendered on the server so the card is never empty before JS runs. The last step is the contact form. There is a no-JavaScript fallback form.
- **Fields**: first name, last name, email, country code + phone, travel date (no past dates), "second quote" Yes/No, optional special request, package context, consent checkbox, reCAPTCHA.
- **Phone**: the selected country's dial code is written into the phone box (pre-filled with the default country on load, swapped when the country changes, keeping the typed number). The number length is checked **per country** (a min/max table) in the browser AND on the server, with "This number is too short / too long. Numbers in <country> have N digits after +X." messages; a typed or pre-filled dial code and a leading trunk 0 are not counted. Stored as E.164; the chosen country is saved as the enquiry's country and sent to the CRM (never a fixed default).
- **Consent**: fixed text in config with a `{company}` placeholder; checkbox required, unticked by default, enforced in browser and server. Store as evidence: consent flag, exact text shown, terms version, timestamp, IP, user agent, page URL. Bump the "terms version" setting when legal text changes. Do not reword without legal sign-off.
- **reCAPTCHA v2** verified server-side after all other validation, **fails closed**. Script loads lazily on first focus/touch, explicit rendering for several widgets and the modal. Keys in Admin > reCAPTCHA (secret encrypted, never shown, "Test secret key" button, warning when Google's public test keys are live), `.env` fallback; local `.env` uses Google's test keys.
- **Spam**: honeypot field (filled = fake success, nothing saved), rate limit 3/minute and 10/hour per IP, a validation rule for **every** field the controller reads.
- **Tracking**: on the first GET capture UTMs, `gclid`, `gbraid`, `wbraid`, ValueTrack `utm_device` / `utm_adcreative` / `utm_adposition`, referrer and landing page into encrypted first-touch (written once) and latest-touch cookies for 30 days; save both as JSON on the enquiry. "Source" = UTM source, else `google-ads` when there is a click ID, else referrer host, else `direct`.
- **GTM**: container ID in Site settings, snippet only when set. dataLayer events `enquiry_submitted` (AJAX success) and `enquiry_thank_you` (thank-you page, once, straight after a real submission; the Google Ads conversion goes on this one).
- **Notifications**: queued staff email per enquiry, never blocking it.

### 4. Integrations (optional, off until configured, never block an enquiry)
- **GoHighLevel CRM**: Admin > GoHighLevel (token encrypted, never shown again, on/off switch, Test connection, Create missing custom fields), `.env` fallback, off without token or location ID. Field names live in one constants class, matched by exact name. Queued job with retries; status/error/attempts on the enquiry with a retry button. Send contact, country, quiz answers as `Quiz: <Key>`, first/last-touch fields (returning contacts keep First values), plain `utm_*` fields and `form_source`. `ghl:setup` and `ghl:test` commands. Tests never reach the real CRM.
- **Google Sheets log**: Admin > Google Sheets. The owner pastes a shown Apps Script (with an embedded shared token and a Copy button) into their sheet, deploys it as a Web app (execute as me, access Anyone), and pastes the `https://script.google.com/macros/s/.../exec` URL (stored encrypted). One tab per form, columns matched by header name (fixed columns + one per quiz key, new columns added on the right, phones kept as text), on/off switch, "Add a test row", per-enquiry status and "Send to sheet now". Explain that Workspace accounts may block "Anyone" access.

### 5. Admin (`/admin`, separate guard, model `AdminUser`)
- **Roles**: `admin` (everything) and `editor` (only packages, posts, pages, media, tour types, destinations, travel styles; enforced by middleware; new routes are admin-only unless added to the editor list). Editors skip the dashboard and land on Packages.
- **Dashboard**: period switch 7/30/90 days; "Needs your attention" banner (new enquiries waiting more than 24 h, CRM/Sheet sync failures); KPI cards (enquiries vs the previous period, new not yet actioned, from Google Ads, won with %); enquiries-per-day bar chart; pipeline by status (New, Contacted, Quoted, Won, Lost) linking to the filtered inbox; top lists for source/medium, campaign, form and package; recent enquiries with relative times, email, source and coloured status pills; a **Site health** checklist with Fix links (real reCAPTCHA keys, CRM connected, Google Sheet connected, phone set, GTM set, notification email set, no `[CONFIRM]` left in settings, maintenance mode off); quick buttons. Every number comes from the database; nothing estimated.
- **Sidebar**: Dashboard, Enquiries, Packages, Tour types (summary, "What to expect", inclusions), Destinations (tagline, highlight places), Travel styles, Hero quiz, Pages (CMS text per page), Testimonials (hidden by default), FAQs ("Shows on" home or a tour type), Team, Travel guides, Media, Site settings (company, contact details, address, hours, social links, GTM ID, terms version, logo, Google rating), Header & Footer code, Maintenance mode, GoHighLevel, Google Sheets, reCAPTCHA, Admin users, History.
- **Enquiries**: list with filters and CSV export; detail with all evidence, status, internal notes, CRM and Sheet status with retry/send-now, delete.
- **Header & Footer code**: snippets (script, style, html) by position (head, body start, body end) and scope (whole site or listed paths with `*` wildcards), cached, public pages only, never breaks a page.
- **Maintenance mode**: switch + message; visitors get a 503 page with Retry-After and noindex; `/admin` and logged-in admins/editors are never blocked; red banner in the admin while on.
- **History**: an activity log of who changed what (old → new values), when and from which IP, written automatically by a trait on every admin-managed model, plus logins/logouts/failed logins; secrets stored as "(hidden)"; logging never throws.
- Rich text through Trix, sanitised on save with an allow-list; output unescaped only for sanitised content. Images processed to WebP (q85, widths 480 to 3840) with a regenerate command. Settings are key/value with defaults in config, cached, shared to all views.

### 6. Content, images and backups
- **Mode 1**: seed realistic starter content: tour types, destinations, 6 to 9 `[SAMPLE]` packages with itineraries, 6+ travel-guide posts, FAQs for home and each tour type, team placeholders, how-it-works steps, reasons-why points, partner logos, quiz steps (time of year, travel style, group size, nights, flights booked, budget in the right currency). **Mode 2**: seed from the source website as described in section 0.
- **Images**: high-resolution photos only (aim for 3000px+ wide). Licensed photos (record artist, licence and source URL in `database/seed-images/credits.json`, shown on a Photo credits page) or AI images (recorded as AI with the tool name). Never unlicensed photos. Seed images live in `database/seed-images/*.jpg`; a seed-import command imports new ones and re-encodes changed ones (md5 manifest) on every deploy. The decorative country map uses public-domain data (Natural Earth).
- **Hero/band videos**: optional AI image-to-video loops (1080p, a few seconds, seamless) made from the same still as the photo behind them.
- **Backups of designs**: before a big home-page redesign, freeze the current home page as an unlisted snapshot page (own controller, view and private copies of its partials/videos, noindex, Disallow in robots, not in sitemap or menus, old fonts kept via a `legacy-fonts` body class) and document how to restore it in `CLAUDE.md`. Never edit snapshot pages afterwards.

### 7. Performance and SEO (must stay 90+)
- Lighthouse 90+ in every category on the home page and one package page, mobile and desktop. The live host may block Lighthouse; audit a local copy through a small proxy script that serves `public/` with brotli and production cache headers (`scripts/lighthouse-proxy.mjs`). Record results in `docs/reference/SEO_CHECKLIST.md`.
- Keep: compiled CSS inlined in the public layout, hero photo preloaded, fonts preloaded, server-rendered first quiz question, videos never on phones, lazy below-the-fold images, one query for home photos. Do not put `sr-only` text inside carousels (it confuses LCP).

### 8. Deployment
- `scripts/deploy.sh "message"`: builds assets, commits, pushes, then over SSH pulls, installs Composer dependencies without dev packages, runs migrations, imports/re-encodes seed images, caches config/routes/views and checks the live URL returns 200. The SSH password comes from an environment variable (`GGT_SSH_PASSWORD`-style) and is never written to disk or the repo; host details live in a git-ignored `.deploy.env`. Data migrations must be safe on an existing site. Document it in `docs/reference/DEPLOYMENT.md`. Deploy after every finished change.

### 9. How to work
1. Read the folder (and `CLAUDE.md` in Option A) and write a short plan. In Mode 2, first list every page of the source site you will copy and show me that list.
2. Build in this order (Option B): foundation and tokens → layout, header, footer → database and models → admin → public pages → enquiry system and quiz → phone check → tracking → reCAPTCHA → GHL → Google Sheets → dashboard, history, maintenance, header/footer code → SEO, sitemap, robots → content and images → tests → deploy script → docs. In Option A: rename brand and config → colours, fonts, logo, map → content (seeders/migrations or the source site) → images and credits → copy in views (`grep -ri german` until nothing is left) → tests → docs.
3. Run the test suite after each stage and fix failures before moving on.
4. Acceptance check: submit one enquiry from each form (hero quiz, modal, package sidebar, contact); confirm it is saved with consent evidence, tracking and an E.164 phone, appears on the dashboard and in Enquiries, reaches GHL and the sheet when configured, fires both dataLayer events; confirm the honeypot, rate limit, reCAPTCHA failure and phone too-short/too-long paths; run Lighthouse.
5. Finish by listing everything that needs my input (placeholders, API keys, domain, real photos, legal review, testimonials to confirm) in `CONTENT_TO_CONFIRM.md`.

---

## Checklist for Option A (copying this repo)

- Rename `config/gogermany.php` and every `gogermany.` reference to the new brand; brand name, legal company name, domain; `.env` (`APP_NAME`, `APP_URL`, new database, new `APP_KEY` via `php artisan key:generate`).
- Logo mark and wordmark, favicon, colours and fonts in `resources/css/app.css` (and the font preload list in `Seo::fontPreloads()`).
- The decorative `germany-map` component: new outline and route for the new country.
- Seeders, migrations that add data, and seed images: tour types, destinations, packages, posts, FAQs, quiz options (budget currency), team, partners, how-it-works, reasons-why, `credits.json`, `ImageSeeder::ALT`.
- Hero and band photos/videos in `public/videos/` and `database/seed-images/`.
- All copy and SEO titles: `grep -ri "german\|bavaria\|neuschwanstein\|rhine"` in `resources/`, `database/`, `app/`, `tests/`.
- Delete the Go Germany snapshot pages (`/hero-old`, `/home-old-2`, `/test-home`, `/hero-section`: controllers, views, partials, videos, routes, robots lines, tests) unless you want them.
- Terms, Privacy and the consent wording: legal review for the new company and country; bump the terms version.
- New GHL sub-account/location, new Google Sheet and Apps Script (new token), new reCAPTCHA keys for the new domain, new GTM container and Google Ads conversion, new notification email.
- New hosting details in `.deploy.env` and `DEPLOYMENT.md`.
