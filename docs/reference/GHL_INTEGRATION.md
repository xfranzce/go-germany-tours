# GoHighLevel (GHL) integration

> **Status: built and unit/feature-tested against faked HTTP responses only. It has NOT been run against a live GHL account** (no token was available). The first real run of `ghl:setup` and `ghl:test` is the verification step: please run them and report any API error.

## What it does
After each enquiry is saved, a queued job (`SyncEnquiryToGhl`) **upserts the contact by email** through the GHL API v2 (`https://services.leadconnectorhq.com`, header `Version: 2021-07-28`). It never blocks or fails the enquiry: on error it retries with backoff (30 s, 2 min, 10 min, 30 min, 5 tries; client errors such as 401/403/422 are not retried), stores `ghl_status` (`off | pending | synced | failed`), `ghl_contact_id`, `ghl_error` and attempts on the enquiry, and logs without ever including the token. Admin > Enquiry > **Retry sync** re-queues it.

**It is OFF** whenever `GHL_API_TOKEN` or `GHL_LOCATION_ID` is empty (status `off`, no HTTP calls).

## 1. Create the Private Integration Token (PIT)
In GHL: switch to the **sub-account** > **Settings > Private Integrations > Create new integration**. Name it "Go Germany Tours website". Tick these scopes:

| Scope | Why |
|---|---|
| `contacts.readonly` | find an existing contact by email |
| `contacts.write` | create/update contacts (and delete the test contact) |
| `locations/customFields.readonly` | list existing custom fields |
| `locations/customFields.write` | create custom fields (`ghl:setup`) |
| `locations.readonly` | optional: lets `ghl:test` confirm the location |

If tags are rejected on upsert, also add `locations/tags.readonly` and `locations/tags.write`. Copy the token (shown once). The **Location ID** is the sub-account ID (Settings > Business Profile, or the `/location/<ID>/` part of the URL).

## 2. Put them in `.env` (never in code or Git)
```
GHL_API_TOKEN=pit-xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx
GHL_LOCATION_ID=xxxxxxxxxxxxxxxxxxxx
```
Then `php artisan config:clear` (locally) or `php artisan config:cache` (production).

## 3. Create the fields and test
```powershell
php artisan ghl:setup     # lists existing fields, creates only the missing ones, prints what it created. Safe to re-run. --dry-run previews.
php artisan ghl:test      # checks token + location, upserts a clearly labelled TEST contact, then deletes it (--keep to leave it)
```
Re-run `ghl:setup` whenever you add a quiz step (it creates `Quiz: <Step Key>`). Field IDs are cached for an hour (`ghl.field_ids`; `ghl:setup` clears the cache).

## What is sent
Contact: first name, last name, email, phone (E.164), source `<Brand> website`, tags: brand name, `source: <source>`, `package: <name>` (package enquiries), and the form name ("Hero quiz", "Header modal", "Package quote", "Ask a question", "Contact page").

Custom fields (names are exact; defined only in `app/Services/Ghl/GhlFields.php`):

| Group | Fields |
|---|---|
| Enquiry | `Preferred Travel Date`, `Second Quote Preference`, `Special Request`, `Package`, `Form Name`, `Time of Year`, `Group Size`, `Nights` |
| Quiz | `Quiz: <Key>` for every quiz step (Destination, Time Of Year, Travel Style, Group Size, Nights, Flights Booked, Budget, Likelihood), and `Quiz Answers (Full)` (all answers as text) |
| Consent | `Consent Given` (Yes), `Consent Timestamp`, `Consent Text Version` |
| Tracking (each exists as **First** and **Last**) | `UTM Source`, `UTM Medium`, `UTM Campaign`, `UTM Term`, `UTM Content`, `GCLID`, `GBRAID`, `WBRAID`, `Referrer`, `Landing Page URL` (e.g. `UTM Source First`, `GCLID Last`) |

**Every form** (hero quiz, modal, package quote, ask-a-question, contact) sends the tracking fields plus `Form Name`; first-touch values come from the 30-day cookie captured on the first visit.

**Returning contacts**: the job looks the contact up by email first. `... Last` fields are always updated; a `... First` field is sent **only if the contact does not already have a value**, so first-touch data is never erased. (Verified in `tests/Feature/GhlIntegrationTest.php`.)

## Day-to-day
- Staff may move the custom fields into **folders** inside GHL by hand (the API cannot create folders). **Do not rename them**: fields are matched by exact name. To rename: change it in GHL **and** in `GhlFields` in the same release.
- If a field is missing in GHL, the sync still succeeds, skips that field and logs `GHL field '...' does not exist: run php artisan ghl:setup`.
- `phpunit.xml` force-blanks `GHL_API_TOKEN`/`GHL_LOCATION_ID`, so tests can never reach the live CRM. Tests that exercise the client set fake credentials at runtime and use `Http::fake()`.
- To rotate the token: create a new PIT, update `.env`, clear/cache config, delete the old one in GHL.

## Assumptions to confirm on first live run
Endpoints used: `GET /locations/{id}/customFields`, `POST /locations/{id}/customFields` (`dataType` TEXT/LARGE_TEXT, `model` contact), `GET /contacts/search/duplicate?locationId&email`, `POST /contacts/upsert` (`customFields: [{id, field_value}]`), `DELETE /contacts/{id}`, `GET /locations/{id}`. If GHL changes a response shape, only `GhlClient.php` / `GhlSync.php` need adjusting.

## Entering the credentials in the admin (no server access needed)
Admin > **GoHighLevel** (Administrators only):
1. Paste the **Location ID** and the **Private Integration token** (`pit-...`), keep "Send enquiries to GoHighLevel" ticked, Save.
2. Press **Test connection**, then **Create missing custom fields** (same as `php artisan ghl:setup`).
The token is stored encrypted with the app key and never shown again (only its last 4 characters). Values saved here override `.env`. Untick the switch to pause the sync; enquiries are always saved here first and never blocked. If `APP_KEY` is ever changed, paste the token again.


## Fields that already exist in GoHighLevel
If a field was created by hand (even with a slightly different label, e.g. "Special Requests"), "Create missing custom fields" and `ghl:setup` reuse it by its field key (`contact.special_request`) and by GHL's "already exists" reply instead of failing. Enquiries then write to that existing field.


## UTM and form source fields
Besides the `... First` / `... Last` attribution fields, each enquiry fills these plain GHL custom fields (they must exist with exactly these names; "Create missing custom fields" adds any that are missing and reuses ones you already have): `utm_source`, `utm_medium`, `utm_campaign`, `utm_content`, `utm_term`, `utm_device`, `utm_gclid`, `utm_adcreative`, `utm_adposition`, `form_source`.

- Values are the visitor's latest tracked visit, falling back to the first visit.
- `utm_device`, `utm_adcreative` and `utm_adposition` come from the landing-page URL. In Google Ads use this final URL suffix: `utm_source=google&utm_medium=cpc&utm_campaign={campaignid}&utm_content={adgroupid}&utm_term={keyword}&utm_device={device}&utm_adcreative={creative}&utm_adposition={adposition}`.
- `utm_gclid` is the Google click id (`gclid`, added automatically by Google Ads auto-tagging).
- `form_source` is the form and the page it was sent from, for example `Hero quiz (/)` or `Package enquiry (/packages/mosel-valley)`.
