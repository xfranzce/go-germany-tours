# Admin guide (for non-developers)

Log in at **/admin** (for example `https://gogermanytours.com/admin`) with the email and password you were given. Five wrong attempts in a minute locks the login for a minute. Search engines are told never to list the admin.

Dangerous buttons (delete) ask you to **click twice**.

## Dashboard
Enquiries this week, how many are still **New**, how many have been New for over 24 hours, and failed CRM syncs. Below: the latest enquiries and quick links.

## Enquiries (your inbox)
- **Filter** by name/email/phone/package, status, source (where they came from), form, date. **Export CSV** opens an export panel: pick the **from/to dates** (inclusive, empty = no limit) and tick the **forms** to include (Hero quiz, Header modal, Package quote, Ask a question, Contact page; all ticked = every form), optionally a status and source, then **Download CSV**. The file name includes the date range. If nothing matches you get a message instead of an empty file.
- Open an enquiry to see everything they sent, their quiz answers, the **consent evidence** (the exact consent wording they saw, when, their IP address, browser and page) and **tracking** (the ad campaign, keyword and Google click ID that brought them, first and latest visit).
- Set the **status** (New, Contacted, Quoted, Won, Lost) and add **internal notes** that only staff see. Press Save.
- **GoHighLevel** box: shows whether the contact reached your CRM. If it says *failed*, read the error, fix the cause (usually the token) and press **Retry sync**.
- Keep consent evidence: delete enquiries only when you are sure (for example test messages).

## Packages
Add or edit a trip: title, tour type, destination, nights, "from" price, short summary, full description (rich text), highlights (one per line), day-by-day itinerary (add/move/remove days), included / not included, FAQs, SEO title and description, share image.
- **Gallery images**: choose a file and write the **alt text** (a short description for people who cannot see the picture; required). JPG/PNG/WebP up to 8 MB. They are resized and converted automatically. The lowest **Order** number is the cover.
- **Published** tick makes it visible; **Featured** puts it on the home page (the first 3 featured show). **Sample** shows a SAMPLE badge: untick it once the content and price are real.
- On the list page: Publish/Unpublish, Feature, **Duplicate** (creates an unpublished copy), set **Order** numbers and press *Save order*.
- If a price is blank the page says "Price on request".

## Tour types and Destinations
The lists packages use. They also create the menu items and landing pages (`/tours/...`, `/destinations/...`). A tour type or destination that still has packages cannot be deleted.

## Hero quiz
The questions in the orange "Start your adventure" box. For each step: question, **Tiles** (tap to go on) or **Dropdown**, order, on/off, and its answers (emoji icon, text, on/off, order). You can add, rename, remove and reorder. Add a new step at the bottom: its **answer key** (e.g. `interests`) names the answer in enquiries and in GoHighLevel. It cannot be changed later, and a new step starts switched off until you add answers and tick *Step is active*. If you add a step, ask a developer to run `php artisan ghl:setup` so the CRM gets the matching field.

## Pages
Home page wording (leave a box empty to use the default shown in it), hero background image, About, Contact, Terms and Conditions, Privacy Policy, plus SEO title/description per page and a "hide from search engines" tick. In legal pages `{company}` is replaced with the legal company name. **The Terms and Privacy pages are starter templates: have a lawyer review them.**
Also here: **Testimonials** (only publish real, permissioned reviews) and **FAQs** (also sent to Google as FAQ data).

**Where an FAQ shows.** Each FAQ has one "Shows on" choice: **Home page (general)**, or one **Tour type**, **Destination** or **Travel style** page (e.g. "Destination: Cochem"). Those pages show their own questions under "Questions about Cochem". General FAQs show on the FAQs page, the Packages and Destinations overviews, and on any tour type, destination or travel style page that has no FAQs of its own. Every destination and travel style starts with five draft questions; answers containing `[CONFIRM: ...]` need your real answer before ads run.

**Where a testimonial shows.** In the testimonial form, "What is this review about?" has three dropdowns: Destination, Tour type and Travel style. Pick the one the guest is talking about (e.g. a review of a Mosel trip: Destination = Cochem) and the review shows on that page, with a heading like "What travelers say about Cochem". Leave all three on "None" for a **general** review: general reviews show on the home page, Packages and the Destinations overview, and on any destination / tour type / travel style page that has no reviews of its own yet. The **About** column in the list shows where each one goes. The Testimonials page (`/testimonials`) always lists every active review.

## Travel guides
Short articles for search engines. Draft until *Published* is ticked and the date has passed.

## Media
Upload images (alt text required), fix alt text, delete unused ones. An image still in use cannot be deleted.

## Site settings
- **Brand and legal**: brand name, **legal company name** (printed in the consent text next to every form and in the footer), logo, favicon, terms version label (change it whenever Terms/Privacy/consent wording changes), and the "save up to X%" modal offer (leave blank unless you have a real offer).
- **Contact details**: phone (blank hides it everywhere), email, address, social links, footer text.
- **Notification emails**: who gets an email for each new enquiry (comma separated). **No address = no email.**
- **Google Tag Manager ID** (`GTM-XXXXXXX`), reCAPTCHA keys. GoHighLevel status (the secret token is only in the server file `.env`, never here).
- **Default SEO** title, description and share image.

## Admin users
Add people (password at least 12 characters), change a name/email/password, remove a person. You cannot remove yourself or the last admin.

## When something looks wrong
Hard refresh (Ctrl+F5). If a change does not appear on the site, save again; settings are cached for up to an hour at most. Ask a developer for server errors (`storage/logs/laravel.log`).

## Packages: bulk actions and trash
On Admin > Packages, tick one or more rows (or the box in the header to tick all) and use the toolbar: **Publish, Unpublish, Feature, Unfeature, Mark sample, Not sample, Trash**. Trashed packages disappear from the public site (soft delete, nothing is erased). Open the **Trash** tab, tick packages and press **Restore** to bring them back. Deleting a package from its edit page also moves it to the trash.

## Admin roles (Administrator and Editor)
Admin > Admin users > Add admin (or Edit) has a **Role** field.
- **Administrator**: everything.
- **Editor**: only Packages, Pages, Travel guides (posts), Media, and the categories/tags (Tour types and Destinations). Enquiries, Hero quiz, Testimonials, FAQs, Site settings and Admin users are hidden and blocked (403). Editors land on Packages after signing in.
- At least one Administrator must always exist. Existing users were set to Administrator.
Permissions live in `AdminUser::EDITOR_AREAS` and are enforced by the `admin.role` middleware (`EnsureAdminRole`).

## History (Administrators only)
**Admin > History** lists everything done in the admin: who (name and email), what they added, updated, deleted or restored, the date and time, and for edits the old and new values. Filter by person, action, area or date. Logins and failed logins are shown too. Passwords, keys and tokens are never shown. It starts recording from the day this feature went live.
