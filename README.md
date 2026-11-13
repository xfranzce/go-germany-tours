# Go Germany Tours

Google Ads landing-page site for the **Go Germany Tours** brand (GoTour family): Laravel 12, Blade, Tailwind v4, Alpine, MySQL, custom admin at `/admin`. Targets US travelers (USD).

## Get it running (Windows + XAMPP)

Start **MySQL** in the XAMPP control panel, create an empty database `go_germany_tours` (phpMyAdmin or `mysql -u root -e "CREATE DATABASE go_germany_tours CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"`), then:

```powershell
composer setup          # installs PHP + JS deps, creates .env, key, migrates + seeds, links storage, builds assets
# edit .env: set ADMIN_SEED_EMAIL and ADMIN_SEED_PASSWORD, then run:  php artisan db:seed
php artisan serve       # http://127.0.0.1:8000   (admin: /admin)
```

Live-reload CSS/JS while editing: `npm run dev` in a second terminal.

Full local guide (php.ini extensions, `gogermanytours.test` virtual host, mail, queue, troubleshooting): [docs/reference/LOCAL_XAMPP.md](docs/reference/LOCAL_XAMPP.md).

## Docs

| File | What |
|---|---|
| [CLAUDE.md](CLAUDE.md) | Project map and the rules that are not obvious from the code |
| [docs/reference/LOCAL_XAMPP.md](docs/reference/LOCAL_XAMPP.md) | Run it locally |
| [docs/reference/DEPLOYMENT.md](docs/reference/DEPLOYMENT.md) | Deploy to Hostinger shared hosting |
| [docs/reference/ADMIN_GUIDE.md](docs/reference/ADMIN_GUIDE.md) | Plain-English guide for non-developers |
| [docs/reference/FORMS_AND_TRACKING.md](docs/reference/FORMS_AND_TRACKING.md) | Forms, consent, reCAPTCHA, UTMs, GTM |
| [docs/reference/GHL_INTEGRATION.md](docs/reference/GHL_INTEGRATION.md) | GoHighLevel setup and fields |
| [docs/reference/SEO_CHECKLIST.md](docs/reference/SEO_CHECKLIST.md) | Pre-launch checks |
| [docs/reference/DESIGN_SYSTEM.md](docs/reference/DESIGN_SYSTEM.md) | Tokens, components, re-skinning |
| [docs/reference/IMAGE_CREDITS.md](docs/reference/IMAGE_CREDITS.md) | Photo sources and licences |
| [docs/reference/CONTENT_TO_CONFIRM.md](docs/reference/CONTENT_TO_CONFIRM.md) | Every placeholder that needs a real answer |

## Tests

`php artisan test` (in-memory SQLite; GoHighLevel credentials are force-blanked so tests can never reach the live CRM).
