# Design system

Hand-built with Tailwind CSS v4. **Every brand-specific value lives in one place: the `@theme` block in `resources/css/app.css`.** Re-skinning = editing that block (plus the logo and the seeded copy).

## Tokens (`resources/css/app.css`)
| Token | Value | Use |
|---|---|---|
| `--color-brand-navy` | `#003049` Deep Space Blue | text, header/footer, hero overlay, quiz headings |
| `--color-brand-red` | `#D6282B` Flag Red | primary buttons (white text 4.9:1), final CTA band |
| `--color-brand-gold` | `#FCBF49` Sunflower Gold | secondary buttons, badges, accents (navy text) |
| `--color-brand-custard` / `-light` | `#EAE2B7` / `#F7F3DF` Vanilla Custard | section backgrounds, cards' borders |
| `--color-brand-tangerine` | `#F77F00` Vivid Tangerine | quiz/sidebar **border**, focus ring, progress bar. Never small text (fails contrast) |
| derived | `navy-dark #001F30`, `navy-soft`, `red-dark #B01E21` (link/eyebrow text), `gold-dark` | hovers and text-on-light |
| `--font-display` | Bodoni Moda (variable, self-hosted; the goeurotours.com heading font). Backup pages (`body.legacy-fonts`) keep Cormorant Garamond | headings |
| `--font-sans` | Manrope (variable, self-hosted; the goeurotours.com body font). Backup pages keep Nunito Sans | body |
| `--radius-card`, `--shadow-card`, `--breakpoint-xs` | | cards |

Utilities are generated automatically: `bg-brand-red`, `text-brand-navy`, `border-brand-tangerine`, `font-display`, etc.

## Component classes (`@layer components`)
`.btn` + `.btn-primary | .btn-gold | .btn-outline | .btn-outline-light`, `.btn-sm`; `.field-label`, `.field-input`, `.field-error`; `.card` (lifts + image zoom on hover); `.eyebrow` (small caps label with leading rule, add `centered` for a trailing rule); `.flag-rule` (thin navy-red-gold Germany accent under headings); `.step-num` (large 01/02/03 numerals); buttons are small uppercase with a 4px radius; `.container-x` (max-width + 16/24px gutters); `.prose-ggt` (rich text).

## Blade components (`resources/views/components`)
| Component | Purpose |
|---|---|
| `<x-button href variant size>` | link or button |
| `<x-section-heading title eyebrow intro light>` | section H2 with `.eyebrow` label (editorial style, inspired by gobritaintrips.com) |
| `<x-video-bg src poster>` | muted looping `<video>` from `public/videos/`; renders nothing if the file is missing; paused for reduced-motion. Section heading also takes `emphasis` (italic second line) |
| `<x-faq-item question open>` | native `<details>` accordion (no JS, crawlable) |
| `<x-form-field name label type options :bag>` | label + input/select/textarea + server and Alpine errors |
| `<x-enquiry-form form-id form-name :package :extras :modal :quiz>` | the shared enquiry form (consent, reCAPTCHA, honeypot) |
| `<x-hero-quiz>` | data-driven multi-step quiz card (not used on the home hero at the moment; the hero is a full-screen video with a "Where to next?" destination dropdown) |
| `<x-package-card :package :level>` | package tile with Get-a-Quote |
| `<x-picture :path alt sizes priority hires>` (default src 1920px, `hires` 2560px; srcset offers all widths up to 3840px) | responsive WebP `<img>` from the Media table, placeholder if missing |
| `<x-icon name>` | inline SVG icon set (add paths in the component) |
| Modal | `partials/modal.blade.php`, opened with `$store.modal.show({form, packageId, title, subtitle, prefill})` |
| Admin | `<x-admin.field>`, `<x-admin.rich>` (Trix), `<x-admin.image>` |

## Heading scale (set once in `@layer base` of `app.css`; do not override per page)
| Level | Size (mobile / desktop) | Use |
|---|---|---|
| Home hero H1 | 36px / 72px, weight 500, last words gold italic + underline | only the home hero |
| Page H1 | 36px / 48px | inner pages, thank-you, 404 |
| Section H2 | 34px / 44px, weight 600, italic `emphasis` second line optional | every home section (via `<x-section-heading>` or a plain `<h2>`) |
| Card / feature H3 | 24px | cards, steps, feature blocks |
| Content H2 on tour/package/destination pages | `text-2xl sm:text-3xl`; card-title H2s use `text-2xl` | in-page subsections |
Avoid `!text-*` overrides on headings; change the base scale instead.

## Layout rules
Mobile first; sections use `py-16 sm:py-20`; content in `.container-x`; side gutters never below 16px; nothing wider than the viewport (verified at 360/768/1280). Motion respects `prefers-reduced-motion`. Focus ring is tangerine 3px on every focusable element.

## Re-skinning for another GoTour brand
1. Change the colour/font tokens in `@theme`. If you change fonts: `npm i @fontsource-variable/<font>`, update the two `@font-face` blocks and the preload list in `App\Support\Seo::fontPreloads()`.
2. Check contrast of text-on-colour pairs (AA 4.5:1 normal, 3:1 large). Keep one dark colour for text, one strong action colour for buttons.
3. Admin > Site settings: brand name, legal company name, logo, favicon, default SEO.
4. Change seeders (`CatalogSeeder`, `QuizSeeder`, `ContentSeeder`, `PagesSeeder`) and `config/gogermany.php` defaults; replace `database/seed-images/` + `credits.json`, update `ImageSeeder` mapping and the destinations in the hero quiz question.
5. The header wordmark flag-stripe (`partials/header.blade.php`) and the footer stripe are Germany-specific: swap or remove.
6. Run `npm run build` and `php artisan test`.
