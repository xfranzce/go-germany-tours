# Forms, consent and tracking

## One path for every form
`<x-enquiry-form>` (Blade) -> `POST /enquiry` -> `EnquiryController` -> `EnquiryService`. Where it appears:

| Place | `form_name` | Notes |
|---|---|---|
| Hero quiz final step | `hero_quiz` | Quiz answers sent as `quiz[key]=answer`; plain form shown inside `<noscript>` without JS |
| Modal (top bar, header, CTA buttons) | `header_modal` | Same modal reused: tour-type "Get a Quote" = `package_quote`, "Ask Us a Question" = `package_question`; package buttons also send `package_id` |
| Package sidebar | `package_quote` | Sends `package_id` and the package name is stored |
| Contact page | `contact` | |

Fields: first/last name*, email*, country (dial code) + phone*, preferred travel date (approx.), second-quote-from-partner select, special request; the modal, package and contact forms add time of year, group size, nights.

## Rules enforced on every submission (browser AND server)
1. **reCAPTCHA v2 checkbox**, verified server-side only after the other fields are valid. Explicit rendering handles several widgets on a page and inside the modal. The script loads lazily on first interaction (keeps pages fast). Unreachable Google or a bad token = rejected (**fails closed**). No JS: a `<noscript>` fallback iframe is included.
2. **Consent checkbox**: required, unticked, exact wording in `config/gogermany.php`, "Terms and Conditions" and "Privacy Policy" link (new tab) to the pages, `{company}` = legal company name (Admin > Site settings).
3. **Stored with each enquiry**: `consent_given`, `consent_text` (exact wording as rendered), `terms_version`, `consented_at`, `ip_address`, `user_agent`, `page_url`.
4. CSRF, validation, **honeypot** (`contact_url`), **rate limit** (3/min, 10/hour per IP), text sanitising (tags stripped), inline errors (`aria-live`), success state then redirect to `/thank-you`.
5. Phone stored **E.164** from the selected country code (leading trunk zeros removed); 8-15 digits total.

## First-touch / last-touch tracking
`CaptureTracking` middleware (GET requests only, not admin) writes two encrypted cookies for **30 days**:
- `ggt_ft` first touch: written once (UTMs/click ids if present, else the referrer/landing page).
- `ggt_lt` latest touch: overwritten whenever a visit has tracking parameters or an external referrer.

Captured keys: `utm_source, utm_medium, utm_campaign, utm_term, utm_content, gclid, gbraid, wbraid`, plus `referrer`, `landing_page`, `captured_at`. Saved on the enquiry as JSON (`first_touch`, `last_touch`) and `source` (utm_source, else "google-ads" for a gclid, else the referrer host, else "direct"). Both sets go to GoHighLevel on every form (see GHL_INTEGRATION.md).
Tip for ads: use `?utm_source=google&utm_medium=cpc&utm_campaign={campaignid}&utm_term={keyword}` as the final-URL suffix; Google adds `gclid` automatically when auto-tagging is on.

## Google Tag Manager and Google Ads conversion
1. Admin > Site settings > **Google Tag Manager ID** (`GTM-XXXXXXX`). The snippet is output only when set (head script + body `<noscript>`).
2. `window.dataLayer` events:
   - `enquiry_submitted` pushed in the browser when the AJAX submit succeeds. Data: `form_name`.
   - `enquiry_thank_you` pushed on `/thank-you` **only once, straight after a real submission** (not on reloads/direct visits). Data: `form_name`, `package_name`, `enquiry_id`.
3. In GTM create a trigger **Custom Event = `enquiry_thank_you`**, then attach your **Google Ads Conversion Tracking** tag (and Conversion Linker) to it. Use the data-layer variables `form_name` / `enquiry_id` (as transaction id to de-duplicate).
4. Preview in GTM Tag Assistant: submit the form and confirm the event fires once.
5. `/thank-you` is `noindex` and disallowed in robots.txt.

## Email notification
After saving, a queued job emails the addresses in **Admin > Site settings > Send new-enquiry emails to** (none set = no email, a warning is logged). Locally mail goes to the log (see LOCAL_XAMPP.md). Failures retry 3 times and never affect the enquiry.

## Testing locally
`.env` has Google's public test reCAPTCHA keys (always pass). Try: open `/?utm_source=google&utm_medium=cpc&utm_campaign=test&gclid=TEST` first, then submit a form, then open the enquiry in the admin and check Tracking and Consent evidence. Automated coverage is in `tests/Feature/EnquiryFormTest.php`.
