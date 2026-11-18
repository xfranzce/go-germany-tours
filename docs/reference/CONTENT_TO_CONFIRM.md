# Content to confirm

Nothing below was invented as fact. Each item is a placeholder (`[CONFIRM: ...]`, SAMPLE, PLACEHOLDER) or draft copy that **a human must confirm or replace before launch**. Edit in the admin unless noted.

## Business details (Admin > Site settings)
| Item | Now | Where it shows |
|---|---|---|
| **Legal company name** | `[CONFIRM: legal company name]` | **Consent text beside every form (also stored as evidence)**, footer, legal pages |
| Phone and display format | empty (hidden everywhere) | top bar, footer, contact page, CTA |
| Public email | empty (hidden) | top bar, footer, contact page |
| Address | empty (hidden) | footer, contact, structured data |
| Notification emails | empty (no staff email sent) | enquiry emails |
| Social links | empty | footer |
| Logo / favicon | text wordmark + generated favicon | header, tab |
| Modal offer "save up to X%" | blank (neutral heading) | modal heading |
| Google Tag Manager ID | empty (no GTM) | all pages |
| reCAPTCHA keys | Google test keys | all forms |
| GHL token + location ID | empty (integration off) | `.env` only |
| Domain | GoGermanyTours.com not set up; `APP_URL` local | canonical, sitemap |

## Copy and claims (review wording)
- **Home hero badges and "Why choose us"**: "Free, no-obligation quote", "Self drive, chauffeur or coach", "Itineraries built around you", "A real person replies": confirm each is true for the business. The suggested "Best Price Guarantee / Local Experts / Reviews" badges were **not** used because no facts were supplied; add them only if verified (rating and review counts only from real reviews).
- **Testimonials**: 3 entries labelled PLACEHOLDER. Replace with real, permissioned reviews (Admin > Testimonials).
- **Home FAQs** (6) are draft answers; three contain `[CONFIRM: ...]` (combining destinations/minimum stays, flight policy, deposit/payment/cancellation, partner arrangement for "second quote").
- **About page**: company story, years in business, team, accreditation/licence numbers: `[CONFIRM: ...]`.
- **Destination descriptions** are short generic descriptions written from general knowledge (e.g. Quedlinburg half-timbered town, Hiddensee car-free island): have someone who knows them add real detail and check accuracy.
- **Tour type descriptions** (Self Drive / Chauffeur Driven / Coach Tours): generic; confirm how each is actually run.
- **Travel guides** (2): sample articles labelled `[SAMPLE ARTICLE]`.

## Packages (6 SAMPLE)
Names, durations (5-10 nights), itineraries, inclusions and "from" prices ($1,650 to $3,290) are **illustrative placeholders**, flagged SAMPLE on the site and not emitted as prices in structured data. Replace each with a real package, untick *Sample*, set real prices.

## Legal (starter templates only)
Terms and Conditions and Privacy Policy contain `[CONFIRM: ...]` blocks (pricing/deposit terms, cancellation, liability and governing law, processors and partners, retention and user rights, cookie/consent requirements). **They are not legal advice. Have a qualified lawyer review them before launch.** The SMS consent wording is fixed in code by instruction; also confirm it meets carrier/10DLC requirements for your SMS provider.

## Images
8 Wikimedia Commons photos (3 CC0, 5 CC BY needing the credit already on `/photo-credits`). Confirm you accept Commons as a source or supply your own (IMAGE_CREDITS.md). The Quedlinburg hero shows scaffolding. No brand/destination imagery was supplied.

## Tracking and ops
- Google Ads conversion action and GTM trigger (FORMS_AND_TRACKING.md).
- Cookie banner/consent management for non-US visitors: not built.
- Hostinger plan features (SSH, document root, cron) confirmed against the real plan.

## Testimonials
Three dummy testimonials (badge "PLACEHOLDER", source "Sample review: replace before launch") are shown on the home page. Replace them in Admin > Testimonials with real, permissioned reviews (name, town, source) and untick "placeholder" before running ads.

## Sample packages
There are 18 SAMPLE packages (6 per tour type; names, nights, prices and 5.0 "sample rating" are placeholders). The last 12 come from `MoreSamplePackagesSeeder` (`php artisan db:seed --class=MoreSamplePackagesSeeder`, safe to re-run). Replace or delete them in Admin > Packages and untick "Sample / placeholder" for real ones.

## Meet Our Team page
`/meet-our-team` holds three PLACEHOLDER team members. Replace them in Admin > Pages > Meet Our Team with the real people (name, role, short bio) or remove the section. Do not publish placeholders.

- Home "Early bird offer" (25% off, month = 3 months ahead, auto): confirm the offer terms (who qualifies, booking deadline, exclusions) and that the discount is real before running ads.

- Meet Our Team: six placeholder cards (Admin > Team) need real names, roles, bios and photos; the intro text (Admin > Pages > Meet Our Team) has a [CONFIRM] line for the real team story.

## Contact page
- Intro paragraph, head office address (if not set in Site settings), support email, phone, operating hours and "reply within 24 hours" are placeholders. Set address/email/phone/social in Admin > Site settings; hours and reply time are in `resources/views/pages/contact.blade.php`.
