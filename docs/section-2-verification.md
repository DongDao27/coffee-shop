# Section 2 verification

Verified on 21 October 2026 (Asia/Saigon).

## Automated checks

`php artisan test`: **6 passed, 64 assertions**.

The REST API feature tests run against SQLite `:memory:` with RefreshDatabase, separate from the learning database. They cover product creation (201), detail (200), partial update (200), rejected negative price (422), preservation of the previous price after rejection, missing fields (422), invalid category reference (422), deletion (204), and missing record (404). Category CRUD, duplicate names, unchanged-name updates, and null-on-delete product relationships are also checked.

Search/filter/sort tests cover ascending and descending price, substring search combined with category filtering, and invalid sort rejection (422). Pagination tests seed exactly 1,000 demo categories, verify two distinct 10-row pages, and repeat the seeder without duplicates.

## Local HTTP checks

Base URL: `http://127.0.0.1:8000`.

- GET products sorted descending: 65,000 → 35,000 → 30,000 (before demo creation).
- Search `cà phê`: two coffee products; filter category 1: two Cafe products.
- Demo product ID 4 was created with UTF-8 JSON by the learner, price 40,000 and category Cafe.
- PATCH demo price to 45,000: 200.
- PATCH negative price: 422; POST empty body: 422.
- GET demo after rejected update: price still 45,000.
- DELETE demo ID 4: 204; subsequent GET: 404. Only this disposable product was removed.
- Categories: total 1,003, last page 101. Page 1 IDs: 1,2,3,5,6,7,8,9,10,11; page 2 IDs: 12,13,14,15,16,17,18,19,20,21.
- `/docs/api`: 200.
- `/docs/api.json`: valid OpenAPI 3.1.2 with `/v1/categories`, `/v1/categories/{category}`, `/v1/products`, and `/v1/products/{product}`.

ID gaps are normal after earlier API CRUD checks. Local database contents are ignored by Git; recreate pagination examples with `php artisan db:seed --class=PaginationDemoSeeder`.

Scramble v0.13.49 was installed through Composer. `composer validate` passes. Import the local `/docs/api.json` URL into Postman as OpenAPI to generate a collection, or use the attached project test suite for repeatable checks.
