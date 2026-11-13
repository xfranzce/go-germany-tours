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
| `--font-display` | Fraunces (variable, self-hosted) | headings |
| `--font-sans` | Nunito Sans (variable, self-hosted) | body |
| `--radius-card`, `--shadow-card`, `--breakpoint-xs` | | cards |

Utilities are generated automatically: `bg-brand-red`, `text-brand-navy`, `border-brand-tangerine`, `font-display`, etc.

## Component classes (`@layer components`)
`.btn` + `.btn-primary | .btn-gold | .btn-outline | .btn-outline-light`, `.btn-sm`; `.field-label`, `.field-input`, `.field-error`; `.card`; `.container-x` (max-width + 16/24px gutters); `.prose-ggt` (rich text).

## Blade components (`resources/views/components`)
| Component | Purpose |
|---|---|
| `<x-button href variant size>` | link or button |
| `<x-section-heading title eyebrow intro light>` | centered section H2 with gold rule |
| `<x-faq-item question open>` | native `<details>` accordion (no JS, crawlable) |
| `<x-form-field name label type options :bag>` | label + input/select/textarea + server and Alpine errors |
| `<x-enquiry-form form-id form-name :package :extras :modal :quiz>` | the shared enquiry form (consent, reCAPTCHA, honeypot) |
| `<x-hero-quiz>` | data-driven multi-step quiz card |
| `<x-package-card :package :level>` | package tile with Get-a-Quote |
| `<x-picture :path alt sizes priority>` | responsive WebP `<img>` from the Media table, placeholder if missing |
| `<x-icon name>` | inline SVG icon set (add paths in the component) |
| Modal | `partials/modal.blade.php`, opened with `$store.modal.show({form, packageId, title, subtitle, prefill})` |
| Admin | `<x-admin.field>`, `<x-admin.rich>` (Trix), `<x-admin.image>` |

## Layout rules
Mobile first; sections use `py-16 sm:py-20`; content in `.container-x`; side gutters never below 16px; nothing wider than the viewport (verified at 360/768/1280). Motion respects `prefers-reduced-motion`. Focus ring is tangerine 3px on every focusable element.

## Re-skinning for another GoTour brand
1. Change the colour/font tokens in `@theme`. If you change fonts: `npm i @fontsource-variable/<font>`, update the two `@font-face` blocks and the preload list in `App\Support\Seo::fontPreloads()`.
2. Check contrast of text-on-colour pairs (AA 4.5:1 normal, 3:1 large). Keep one dark colour for text, one strong action colour for buttons.
3. Admin > Site settings: brand name, legal company name, logo, favicon, default SEO.
4. Change seeders (`CatalogSeeder`, `QuizSeeder`, `ContentSeeder`, `PagesSeeder`) and `config/gogermany.php` defaults; replace `database/seed-images/` + `credits.json`, update `ImageSeeder` mapping and the destinations in the hero quiz question.
5. The header wordmark flag-stripe (`partials/header.blade.php`) and the footer stripe are Germany-specific: swap or remove.
6. Run `npm run build` and `php artisan test`.
