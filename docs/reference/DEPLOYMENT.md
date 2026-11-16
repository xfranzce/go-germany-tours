# Deployment: Hostinger shared / Business web hosting (hPanel)

> Not yet performed: nothing here has been run against a real Hostinger account. Hostinger options vary by plan, so check each step against your hPanel. The domain GoGermanyTours.com is not set up yet.

## Before you start

- Plan needs **PHP 8.2+** (hPanel > Advanced > PHP Configuration; enable extensions `curl, fileinfo, gd, intl, mbstring, openssl, pdo_mysql, zip`), **MySQL**, **SSH** (Business plans and up; otherwise use File Manager + a pre-built `vendor`), and **Cron Jobs**.
- Build assets **locally** (hosting often has no Node): `npm install && npm run build`. Upload `public/build`.
- Never upload `.env` from your PC. Create the production one on the server.

## 1. Create the database
hPanel > Databases > MySQL Databases: create database + user, note the names (they are prefixed, e.g. `u123_ggt`).

## 2. Upload the code
Put the whole project **outside the public web root**, e.g. `/home/uXXXX/domains/gogermanytours.com/app/`, so `.env` and `vendor` are never web-accessible.
Point the domain at the project's `public` folder using one of:
- **Best**: hPanel > Websites > (domain) > Advanced > *Change document root* to `.../app/public` (if your plan offers it).
- Otherwise: replace `public_html` with a symlink to `app/public` over SSH (`rm -r public_html && ln -s app/public public_html`).
- Last resort: copy the contents of `public/` into `public_html/` and edit the two paths in `public_html/index.php` to point at `../app/vendor/autoload.php` and `../app/bootstrap/app.php`; also point `storage:link` at `public_html/storage`.

Upload with Git (SSH `git clone`), SFTP, or a zip. Exclude `node_modules`, `.env`, `storage/logs/*`.

## 3. Production `.env` (create on the server)
```
APP_NAME="Go Germany Tours"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://gogermanytours.com
APP_KEY=                      # php artisan key:generate --force
LOG_LEVEL=warning
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=u123_ggt
DB_USERNAME=u123_ggt
DB_PASSWORD=...
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database     # real queue (see step 6)
MAIL_MAILER=smtp              # Hostinger mailbox SMTP details
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=465
MAIL_SCHEME=smtps
MAIL_USERNAME=noreply@gogermanytours.com
MAIL_PASSWORD=...
MAIL_FROM_ADDRESS=noreply@gogermanytours.com
RECAPTCHA_SITE_KEY=           # REAL keys from google.com/recaptcha (v2 checkbox). Remove the test keys!
RECAPTCHA_SECRET_KEY=
GHL_API_TOKEN=                # leave blank until ready
GHL_LOCATION_ID=
ADMIN_SEED_EMAIL=you@yourdomain.com
ADMIN_SEED_PASSWORD=a-long-unique-password
```
Register the real domain (and `localhost` is not needed) in the reCAPTCHA admin console.

## 4. Install and migrate (SSH, inside the app folder)
```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate --force
php artisan migrate --force
php artisan db:seed --force          # first install only: creates pages, packages, quiz, admin
php artisan storage:link
php artisan config:cache && php artisan route:cache && php artisan view:cache
chmod -R 775 storage bootstrap/cache
```
Then change the admin password at Admin > Admin users, and remove `ADMIN_SEED_PASSWORD` from `.env`. After every deploy: `php artisan migrate --force` then re-run the three `:cache` commands.

## 5. Settings in the admin
Admin > Site settings: legal company name, phone, email, address, notification emails, GTM ID, logo. Replace SAMPLE packages and placeholders (CONTENT_TO_CONFIRM.md).

## 6. Queue worker and scheduler (shared hosting = cron)
Shared hosting cannot keep a worker running, so use cron (hPanel > Advanced > Cron Jobs), **every minute**:
```
* * * * * cd /home/uXXXX/domains/gogermanytours.com/app && /usr/bin/php artisan queue:work --stop-when-empty --max-time=50 --tries=3 >> /dev/null 2>&1
* * * * * cd /home/uXXXX/domains/gogermanytours.com/app && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
```
Use the PHP binary path hPanel shows for your PHP version. Enquiry emails and GHL syncs run within about a minute of the submission. The app currently defines **no scheduled tasks**, so the second line is harmless and ready for when some are added. (On a VPS use Supervisor: `php artisan queue:work --sleep=3 --tries=3`.) If the queue is not running, enquiries are still saved; only email/CRM are delayed.

## 7. After going live
- HTTPS on (hPanel > SSL, force HTTPS). `.htaccess` already adds compression and 1-year caching for `/build` assets.
- Submit `https://gogermanytours.com/sitemap.xml` in Google Search Console.
- Run the checks in SEO_CHECKLIST.md and test one real enquiry end to end (email + admin + GHL).
- Backups: hPanel backups plus a regular MySQL dump.
- GHL: add the token/location to `.env`, `php artisan config:cache`, `php artisan ghl:setup`, `php artisan ghl:test`.

## Current Hostinger deployment (shared hosting, SSH port 65002)
- Site: `mediumturquoise-mosquito-631405.hostingersite.com`. App in `~/domains/<site>/app` (git clone of GitHub `main`); `public_html` is a **symlink to `app/public`**. Old folder backup: `~/domains/_old_public_html_mediumturquoise_backup`.
- DB host on the server is `127.0.0.1` (the `srv*.hstgr.io` host rejects non-whitelisted IPs). `.env` exists only on the server.
- The server CLI default is PHP 8.2 but the lockfile needs 8.4: use `/opt/alt/php84/usr/bin/php`. The web PHP version is forced by `AddHandler application/x-httpd-alt-php84___lsphp .php` at the top of `public/.htaccess` on the server (marked `skip-worktree`; not committed).
- No Node on the server: run `npm run build` locally and upload `public/build` (gitignored).
- Testimonials have `name`, `location`, `source` (shown on the card); drafts are seeded hidden.
- Update: `git pull`, `/opt/alt/php84/usr/bin/php $(which composer) install --no-dev -o`, `php84 artisan migrate --force`, `php84 artisan optimize:clear`, upload `public/build` if CSS/JS changed.
