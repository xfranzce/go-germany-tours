# Running locally on Windows + XAMPP

Tested on: Windows 10, XAMPP at `C:\xampp`, PHP 8.5.12, Composer 2.11, Node 26 / npm 11, Chrome (for Lighthouse). Laravel 12 needs PHP 8.2 or newer.

## 1. PHP extensions

Open `C:\xampp\php\php.ini` and make sure these lines have **no leading semicolon**, then restart Apache (only needed for the vhost; `artisan serve` reads php.ini on start):

```
extension=curl
extension=fileinfo
extension=gd
extension=intl
extension=mbstring
extension=openssl
extension=pdo_mysql
extension=zip
extension=sqlite3     ; only needed to run the tests (in-memory SQLite)
extension=pdo_sqlite
```

Check with `C:\xampp\php\php.exe -m`. (On this machine `gd`, `intl`, `zip` and `sqlite3` were enabled during setup; the original file is saved as `php.ini.bak-gogermany`.)

## 2. Database

Start **MySQL** in the XAMPP control panel, then either use phpMyAdmin (http://localhost/phpmyadmin) or run:

```powershell
C:\xampp\mysql\bin\mysql.exe -u root -e "CREATE DATABASE IF NOT EXISTS go_germany_tours CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
```

`.env.example` already matches XAMPP defaults: `DB_HOST=127.0.0.1`, `DB_PORT=3306`, `DB_DATABASE=go_germany_tours`, `DB_USERNAME=root`, empty `DB_PASSWORD`. (For a non-root user: `CREATE USER 'ggt'@'localhost' IDENTIFIED BY 'a-password'; GRANT ALL ON go_germany_tours.* TO 'ggt'@'localhost';` and put them in `.env`.)

## 3. Install and build

```powershell
cd "F:\Work\DG Ventures\code\go-germany-tours"
composer install
copy .env.example .env          # skip if .env exists
php artisan key:generate
# in .env set ADMIN_SEED_EMAIL and ADMIN_SEED_PASSWORD (the first admin login)
php artisan migrate --seed
php artisan storage:link
npm install
npm run build                    # production assets; use "npm run dev" while editing
```

`composer setup` does all of this except choosing the admin credentials (run `php artisan db:seed` afterwards; it is safe to repeat).

## 4. View the site

**(a) `php artisan serve`** (simplest): http://127.0.0.1:8000, admin at http://127.0.0.1:8000/admin. If it fails on Windows, use PHP's built-in server instead:

```powershell
php -S 127.0.0.1:8000 -t public vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php
```

**(b) Apache virtual host `gogermanytours.test`** (needs an administrator editor because it changes system files):

1. `C:\xampp\apache\conf\httpd.conf`: make sure `Include conf/extra/httpd-vhosts.conf` is **not** commented out, and `LoadModule rewrite_module modules/mod_rewrite.so` is enabled.
2. Add to `C:\xampp\apache\conf\extra\httpd-vhosts.conf`:

```apache
<VirtualHost *:80>
    ServerName gogermanytours.test
    DocumentRoot "F:/Work/DG Ventures/code/go-germany-tours/public"
    <Directory "F:/Work/DG Ventures/code/go-germany-tours/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

3. Add this line to `C:\Windows\System32\drivers\etc\hosts` (open Notepad **as Administrator**):

```
127.0.0.1   gogermanytours.test
```

4. Restart Apache, set `APP_URL=http://gogermanytours.test` in `.env`, run `php artisan config:clear`, open http://gogermanytours.test.

## 5. Mail, reCAPTCHA, queue

- **Mail**: `MAIL_MAILER=log` writes enquiry emails to `storage/logs/laravel.log`. To see rendered mail, install Mailpit and use `MAIL_MAILER=smtp`, `MAIL_HOST=127.0.0.1`, `MAIL_PORT=1025`. Emails only send once **Admin > Site settings > Send new-enquiry emails to** has an address.
- **reCAPTCHA**: `.env` ships Google's public *test* keys (they always pass and show a "for testing only" banner). Real keys: Admin > Site settings, or `.env`.
- **Queue**: `QUEUE_CONNECTION=sync` runs emails and GHL syncs immediately. Production uses a real queue; see DEPLOYMENT.md.
- **GoHighLevel**: off while `GHL_API_TOKEN` / `GHL_LOCATION_ID` are empty.

## 6. Troubleshooting

| Problem | Fix |
|---|---|
| Apache will not start, port 80 in use | Stop IIS/Skype/World Wide Web Publishing, or change Apache's `Listen 80` to `8080` (then use `http://gogermanytours.test:8080`) |
| MySQL port 3306 busy | Another MySQL is running; stop it or change `DB_PORT` |
| `could not find driver` / `Class "Intervention..."` / GD errors | An extension in section 1 is off. Run `php -m`, edit php.ini, restart `artisan serve` |
| `SQLSTATE[HY000] [1049] Unknown database` | Create the database (section 2) |
| Pages show unstyled | Run `npm run build` (or start `npm run dev`); delete `public/hot` if the dev server is not running |
| Images 404 | `php artisan storage:link` (re-run after moving the project) |
| `The stream or file "storage/logs/laravel.log" could not be opened` | Ensure `storage` and `bootstrap/cache` are writable by your user |
| **419 Page Expired** on a form | Session/CSRF cookie missing: clear cookies, make sure `APP_URL` matches the address in the browser, run `php artisan config:clear` |
| Form says the CAPTCHA failed locally | Needs internet (Google verifies the token). Check `RECAPTCHA_*` in `.env` and run `php artisan config:clear` |
| Changes to `.env` ignored | `php artisan config:clear` |
| Fonts/CSS stale after a design change | `npm run build` and hard refresh (Ctrl+F5) |
