# Admin guide (for non-developers)

Log in at **/admin** (for example `https://gogermanytours.com/admin`) with the email and password you were given. Five wrong attempts in a minute locks the login for a minute. Search engines are told never to list the admin.

Dangerous buttons (delete) ask you to **click twice**.

## Dashboard
Enquiries this week, how many are still **New**, how many have been New for over 24 hours, and failed CRM syncs. Below: the latest enquiries and quick links.

## Enquiries (your inbox)
- **Filter** by name/email/phone/package, status, source (where they came from), date. **Export CSV** downloads the filtered list.
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
Also here: **Testimonials** (only publish real, permissioned reviews) and **Home page FAQs** (also sent to Google as FAQ data).

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
