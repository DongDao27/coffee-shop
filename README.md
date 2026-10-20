<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Coffee Shop — REST API lab

Requires PHP 8.4 (tested with 8.4.25), Composer, and SQLite extensions `pdo_sqlite`, `sqlite3`, and `fileinfo`.

```powershell
composer install
Copy-Item .env.example .env # First installation only
php artisan key:generate # First installation only
New-Item database/database.sqlite -ItemType File # Only if absent
php artisan migrate
php artisan db:seed
php artisan serve
```

The local database is `database/database.sqlite`. Never commit `.env` or the database.

API base URL: `http://127.0.0.1:8000/api/v1`. Categories and products each expose GET list, POST, GET detail, PUT/PATCH, and DELETE. Send `Accept: application/json`; JSON request bodies containing Vietnamese must be encoded as UTF-8 in Windows PowerShell.

Products support `search`, `category_id`, and `sort=price_asc|price_desc`. Both lists paginate 10 rows per page. Successful creation returns 201, deletion 204, missing records 404, and invalid input 422. Product names may repeat. Section 6 now protects both resources with Sanctum bearer tokens and `read`, `create`, `update`, `delete` abilities. No token returns 401; insufficient permission returns 403.

Create the 1,000 pagination demo categories explicitly (repeatable, without duplicate demo names):

```powershell
php artisan db:seed --class=PaginationDemoSeeder
```

This seeder is separate from the default seeds. Compare `/api/v1/categories?page=1` and `?page=2` to see distinct IDs. Local demo data is not included in Git.

Scramble documentation: `/docs/api`, OpenAPI JSON: `/docs/api.json` (local environment). Verify registered URLs with `php artisan route:list --path=docs`.

```powershell
php artisan test
vendor\bin\pint --test
composer validate
```

`tests/Feature/RestApiTest.php` verifies CRUD, validation, search/filter/sort, pagination, and repeatable demo seeds using SQLite `:memory:` configured in `phpunit.xml`. It does not reset the local learning database.

For frontend assets when needed: `npm install`, then `npm run dev` or `npm run build`.

## Running the completed lab

The UI uses Blade, Tailwind 4 and Livewire 3 (bundled Alpine; no separate Alpine startup). The homepage displays categories that contain products; pagination demo categories remain available through the API. Readding a cart product replaces its quantity. Checkout validates server data, rejects empty carts, and creates signed order pages. This is order placement, without payment processing.

Authentication routes include `/register`, `/login`, `/forgot-password`, `/dashboard`, `/profile`, and `/api-tokens`. Register your own account, then use the verification link in the local mail log. Profile and token screens require recent password confirmation. Profile supports password changes, confirmed 2FA with authenticator/recovery codes and account deletion. Plaintext API tokens are displayed at creation; copy them then. `/product-images` allows verified users to upload JPEG/PNG/WebP images up to 2 MB. Run `php artisan storage:link` for public image URLs.

For local work, run each command in a separate terminal:

```powershell
php artisan serve
npm run dev
php artisan queue:work --tries=3 --timeout=60
```

`MAIL_MAILER=log` is the default: notifications are written locally, not delivered to a real inbox. Order notifications use the database queue, so a worker must run. Daily logs are in `storage/logs/laravel-YYYY-MM-DD.log`. `/telescope` is registered only in local development when the dev package exists; sensitive auth screens are excluded from request capture. Slack/email integrations require your real configuration and are not connected.

Queue/scheduler demonstrations:

```powershell
php artisan lab:heartbeat
php artisan lab:heartbeat --queue
php artisan lab:heartbeat --queue --fail # Intentional failed-job example
php artisan queue:work --stop-when-empty --tries=1
php artisan queue:failed
php artisan schedule:list
php artisan schedule:run
```

The heartbeat runs every minute with `withoutOverlapping`. Use `schedule:work` only while observing locally, then stop it. An intentional failure remains visible in `queue:failed` after the demo. Tests use fake notifications and a separate in-memory SQLite database.

The lab's original source by nghuuquyen is the reference for the layouts, components, domain models and tests: https://github.com/nghuuquyen/lab-laravel-framework . This version adapts the code to the current project's schema and adds validation, signed-route enforcement, local-only tooling and security regression tests.

## Deployment status

Local implementation and build are complete. No live deployment has been performed because no hosting/server/domain was supplied. Set production environment variables (`APP_ENV=production`, `APP_DEBUG=false`, real `APP_URL`), use HTTPS, point the web root to `public/`, install dependencies with `composer install --no-dev --optimize-autoloader`, build assets with `npm ci` and `npm run build`, configure persistent storage and database backups, run controlled migrations, then configure a worker and minute scheduler under the server's process manager. Preserve APP_KEY on existing deployments. Review authentication, cart/checkout, logs and API access after deploy. No real payment or external Slack/email service is configured.

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
