# Ayurviro — AGENTS.md

**Brand name is "Ayurviro"** (`includes/config.php:6` — `SITE_NAME`). Repo name is historical.

## Quickstart

- Standard LAMP: PHP 8.1+, MySQL, Apache (XAMPP), no build step
- DB: `public_care_ayurveda`. Import `sql/schema.sql` (tables + seed data)
- URL: `http://localhost/www/public_care_ayurveda`
- `config/database.php` is git-ignored. Copy from `config/database.example.php` (credentials: root/empty)
- `includes/config-local.php` is git-ignored — use for env overrides (e.g. OpenAI key)

## Page Architecture

Every PHP page follows this pattern:

```php
require_once 'includes/config.php';  // session, CSRF, security headers, BASE_URL, language
include 'includes/header.php';       // <head>, nav, cart badge, language switcher
// page content
include 'includes/footer.php';       // close tags, mobile menu JS
```

- `$current_page` set automatically via `basename($_SERVER['SCRIPT_NAME'])` — header uses it for nav active styling
- All forms include hidden `<input name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">` — **every POST handler must validate it**
- Admin pages use standalone layout: `admin/includes/head.php` + `admin/includes/sidebar.php` (NOT public header/footer). Use `$active_page` variable matched against sidebar keys.

## Multi-Language

Languages: `hi` (Hindi, default), `en`, `pa` (Punjabi), `bg` (Haryanvi), `bho` (Bhojpuri).
- Translation keys in `includes/translations/{lang}.json`. Usage: `<?= __('nav_home') ?>`
- Lang selection via `?lang=code` query param; saved to `users.preferred_language` for logged-in users
- `$_SESSION['lang']` defaults to browser Accept-Language or `'hi'`

## Authentication & Authorization

- **Auth handlers** live in `auth/`: `auth/login.php`, `auth/register.php`, `auth/logout.php`, `auth/google-callback.php`. The root pages (`login.php`, `register.php`, `logout.php`) delegate POST processing to these via `require __DIR__ . '/auth/...'`
- **Google OAuth**: `auth/google-callback.php` verifies the GIS credential token, links/creates user by `google_id` column, and sets session. Client ID in `GOOGLE_CLIENT_ID` constant (from `config-local.php`).
- Session keys: `user_id`, `user_name`, `role`, `csrf_token`
- Admin guard: `$_SESSION['role'] !== 'admin'`
- Login saves `$_SESSION['redirect_after_login']` (used by checkout)
- Session timeout: 7200s (2h), `session_regenerate_id(true)` after login/register
- `feature_helpers.php` provides `require_login()`, `current_user($db)`, `h()`, `money()`

## Key Patterns

- **CSRF**: Every POST handler: `if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? ''))`
- **DB**: `getDB()` singleton from `config/database.php`. Use prepared statements for user input. Inline SQL only with `intval()`/direct int-cast values
- **Errors**: `display_errors = 0, log_errors = 1` — never expose to users
- **Security headers**: `X-Content-Type-Options: nosniff`, `X-Frame-Options: DENY`, `Referrer-Policy: strict-origin-when-cross-origin`
- **.htaccess**: Directory listing disabled, `.git/` access blocked
- **No CI, no build step**

## Ecommerce Flow

1. Shop: `shop.php` (filters: category, search, price, sort, pagination)
2. Product detail: `product-details.php?id=N` — add-to-cart POSTs to `cart-update.php`
3. Add to cart uses `fetch()` to `cart-update.php?action=add&id=N` with live badge update
4. Cart: `shopping-cart.php` — quantity +/-/delete via `cart-update.php`
5. Checkout: `checkout.php` — requires login, PRG pattern (redirect after POST)
6. **Logged-in**: cart in `cart` table keyed by `user_id`. **Guest**: `$_SESSION['cart']` associative array
7. `$_SESSION['cart_count']` synced on every cart mutation for header badge

## API (`/api/*.php`)

JSON endpoints. All include `api/helpers.php` (session, CORS, OPTIONS handler). Helpers: `jsonResponse()`, `jsonError()`, `requireAuth()`. These all call `exit()` — tested via subprocess `exec()`, not `@runInSeparateProcess`.

## Frontend (inline Tailwind via CDN)

| Property | Public | Admin |
|---|---|---|
| Tailwind | `cdn.tailwindcss.com` + forms + container-queries | Same |
| Headings | DM Sans | Source Serif 4 |
| Body | Plus Jakarta Sans | Manrope |
| Icons | Material Symbols Outlined | Same |
| Primary | `#005221` | `#012d1d` |

Tailwind config inlined in `<script>` in `header.php` / `admin/includes/head.php`. Custom classes: `.hover-lift`, `.hide-scrollbar`, `.bento-card`.

## Notable Pages & Features

- **Multi-tier commerce**: `retailer-dashboard.php`, `retailer-orders.php`, `retailer-reports.php`, `retailer-stock.php`, `distributor-panel.php`
- **Lab tests**: `lab-tests.php`, `lab-booking.php`
- **Health features**: `health-coach.php`, `my-health.php`, `health-records.php`, `video-consult.php`, `prescriptions.php`
- **Order Punch**: `order-punch.php` — quick ordering flow
- **Wallet / Wishlist / Subscriptions / Notifications**: `wallet.php`, `wishlist.php`, `subscriptions.php`, `notifications.php`
- **Static prototypes**: `src/pages/` — standalone HTML mockups of key pages
- **Design experiments**: `new-flow/stitch_unified_design_system/`

## Database (`sql/schema.sql` — canonical)

**Core**: `users`, `products`, `categories`, `doctors`, `appointments`, `orders`, `order_items`, `cart`, `contacts`, `reviews`, `blog_posts`, `settings`

**Feature**: `dosha_questions`, `dosha_assessments`, `dosha_responses`, `health_reminders`, `reminder_logs`, `patient_metrics`, `family_members`, `consultations`, `prescriptions`, `user_languages`

Schema caveats:
- `users`: `preferred_language`, `email_notifications`, `google_id` added via ALTER, not in CREATE
- `appointments`: `consultation_id`, `meeting_link` added via ALTER (lines 319-320) referencing `notes` column that **doesn't exist** — ALTER will fail unless `notes` column is added first
- `orders` in `payment.php` references `discount_amount`, `wallet_used`, `gst_amount` columns defined in `sql/vedmitra_schema.sql` but **not** in canonical `schema.sql`

## AI Features

- OpenAI: API key from `settings` table (`openai_api_key`) or `OPENAI_API_KEY` env var
- Model stored as `openai_model` in `settings` (default `'gpt-5.2'`)
- `includes/ai-helper.php` — health assistant API
- `includes/chatbot.php` — keyword-based chatbot widget

## Tests

- **Framework**: PHPUnit 11 (`require-dev`)
- **Run**: `composer test` or `vendor/bin/phpunit -c tests/phpunit.xml`
- **Unit only** (no DB): `composer test:unit`
- **Integration only** (MySQL required): `composer test:integration`
- `tests/phpunit.xml`: 2 suites (`unit`, `integration`), bootstrap mocks `$_SERVER` + `$_SESSION`
- API helper tests use `exec()` subprocess via `tests/Fixtures/test_json_response.php` (not `@runInSeparateProcess`)
- Integration tests never close DB in teardown (static `$conn` singleton)
- Cleanup queries use `intval()`; data ops use prepared statements
- `$conn->insert_id` instead of `LAST_INSERT_ID()`
