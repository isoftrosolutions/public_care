# Public Care Ayurveda — Project Documentation

## Project Overview

Public Care Ayurveda is a PHP-based Ayurvedic healthcare website with e-commerce, doctor consultation booking, and a wellness blog. The admin panel manages products, orders, appointments, and site data.

## Tech Stack

| Layer       | Technology                        |
|-------------|-----------------------------------|
| Backend     | PHP 8.x                           |
| Database    | MySQL                             |
| Frontend    | Tailwind CSS (inline via CDN)     |
| Server      | Apache (XAMPP)                    |

## Directory Structure

```
public_care_ayurveda/
├── includes/         # Header, footer, config
├── admin/            # Admin panel (dashboard, appointments, orders)
├── config/           # Database connection
├── sql/              # SQL schema
├── assets/           # CSS, JS, images, uploads
├── src/              # Original HTML templates (reference)
├── docs/             # Design docs, notes
├── .htaccess         # Security: blocks directory listing & .git access
├── cart-update.php   # Ajax cart handler (add/remove/delete)
├── subscribe.php     # Newsletter signup handler
└── *.php             # Public-facing pages
```

## Database

**Database name:** `public_care_ayurveda`

### Tables

| Table         | Purpose                           |
|---------------|-----------------------------------|
| `users`       | Registered users & admin accounts |
| `products`    | Ayurvedic products                |
| `categories`  | Product categories                |
| `doctors`     | Ayurvedic doctors                 |
| `appointments`| Doctor appointment bookings       |
| `orders`      | Product orders (with shipping address columns) |
| `order_items` | Line items within an order        |
| `cart`        | Shopping cart items               |
| `contacts`    | Contact form submissions          |
| `reviews`     | Product reviews                   |
| `blog_posts`  | Health & wellness articles        |
| `subscribers` | Newsletter email subscribers      |

## Key Files

| File                                   | Purpose                                          |
|----------------------------------------|--------------------------------------------------|
| `config/database.php`                  | MySQL connection — logs errors, no user exposure |
| `includes/config.php`                  | Session config, CSRF token, BASE_URL, security headers, error suppression |
| `includes/header.php`                  | HTML head, nav, Tailwind CDN, Google Fonts, cart badge |
| `includes/footer.php`                  | Closing tags, mobile menu toggle JS, scroll animations |
| `cart-update.php`                      | Add/remove/delete cart items (DB for logged-in, session for guests) |
| `subscribe.php`                        | Newsletter signup with CSRF validation            |
| `.htaccess`                            | Directory listing disabled, .git access blocked   |

## Base URL

```php
define('BASE_URL', '/www/public_care_ayurveda');
```

Access the site at `http://localhost/www/public_care_ayurveda`.

## Database Credentials

```php
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'public_care_ayurveda';
```

## Security Features

### CSRF Protection
- Token generated per session in `includes/config.php`
- Validated on all POST handlers: login, register, contact, checkout, booking, newsletter, cart-update
- Hidden `csrf_token` field included in every form

### Session Security
- `session_regenerate_id(true)` after login and registration
- Session timeout after 7200 seconds (2 hours) of inactivity
- HttpOnly cookies enabled (`session.cookie_httponly = 1`)
- Strict session mode (`session.use_strict_mode = 1`)

### SQL Injection Prevention
- All queries use prepared statements (`bind_param`) where user input is involved
- Cart keys filtered through `array_map('intval', ...)` before SQL use
- Only integer-cast values used in inline SQL for internal operations

### XSS Prevention
- All user/output data wrapped in `htmlspecialchars()`
- Error details never exposed to users (logged server-side)

### Security Headers
- `X-Content-Type-Options: nosniff`
- `X-Frame-Options: DENY`
- `Referrer-Policy: strict-origin-when-cross-origin`

### Directory Protection
- `.htaccess` with `Options -Indexes` disables directory listing
- `.git/` access blocked via mod_rewrite
- All empty directories have `index.php` redirect placeholders

## How Pages Work

1. Every page starts with `require_once 'includes/config.php';` — session boot, constants, security headers
2. `include 'includes/header.php';` renders `<head>`, Tailwind CDN, Google Fonts, Material Symbols, navigation
3. Page-specific content rendered in the middle
4. `include 'includes/footer.php';` closes HTML, includes mobile menu JS and scroll animations

### Navigation & Active Detection

`$current_page` is set before including the header (uses `basename($_SERVER['SCRIPT_NAME'])` automatically via config). Header checks it to apply active styling to nav links.

## Ecommerce Flow

1. **Shop** (`shop.php`) — Browse products with category/search/price/sort filters and pagination
2. **Product Detail** (`product-details.php`) — View product, add to cart via POST to `cart-update.php`
3. **Add to Cart** — Shop & homepage `.add-to-cart` buttons use `fetch()` to `cart-update.php?action=add&id=X` with live badge update
4. **Cart** (`shopping-cart.php`) — View items, adjust quantity (+/-/delete) via `cart-update.php`
5. **Checkout** (`checkout.php`) — Requires login, collects shipping address, validates stock, saves order + address to DB
6. **Order** — PRG pattern (redirect after POST), order number displayed, cart cleared

### Cart Behavior
- **Logged-in users**: Cart stored in `cart` table (DB), keyed by `user_id`
- **Guest users**: Cart stored in `$_SESSION['cart']` associative array
- `$_SESSION['cart_count']` always synced for header badge display

### Payment Methods (Indian Gateways)
- Razorpay — UPI, cards, net banking & wallets
- Paytm — Paytm wallet, UPI & cards
- UPI — Google Pay, PhonePe, BHIM & more

## Frontend Dependencies (CDN)

| Resource              | URL                                                                 |
|-----------------------|---------------------------------------------------------------------|
| Tailwind CSS          | `cdn.tailwindcss.com`                                               |
| Google Font Serif     | `Source Serif 4`                                                    |
| Google Font Sans      | `Manrope`                                                           |
| Material Symbols      | `fonts.googleapis.com/css2?family=Material+Symbols+Outlined`        |

## Color Scheme

| Role       | Name         | Hex       |
|------------|--------------|-----------|
| Primary    | Forest Green | `#1B4332` |
| Accent     | Warm Gold    | `#D4AF37` |
| Secondary  | Sage Green   | `#B7E4C7` |
| Background | Cool White   | `#F8FAF5` |
| Text       | Dark Charcoal| `#1A1A2E` |

## Session-Based Authentication

```php
$_SESSION['user_id']   // user ID
$_SESSION['user_name'] // display name
$_SESSION['role']      // 'admin' or 'customer'
$_SESSION['csrf_token']// CSRF protection token
```

- Login redirects to `$_SESSION['redirect_after_login']` if set (used by checkout)
- Admin check: `$_SESSION['role'] !== 'admin'`

### Session-Protected Pages
- `checkout.php` — redirects to login with `redirect_after_login` if not authenticated
- All `/admin/` pages require admin role

## Public Pages

| Page                    | Description                                |
|-------------------------|--------------------------------------------|
| `index.php`             | Home page — hero, featured products, doctors, blog, wellness plans (`#wellness-plans`) |
| `about-us.php`          | About us                                    |
| `shop.php`              | Product listing with filters & pagination   |
| `product-details.php`   | Single product detail + add to cart         |
| `doctor-listing.php`    | Doctor listing / profiles                   |
| `doctor-profile.php`    | Single doctor profile                       |
| `wellness-blog.php`     | Blog listing                                |
| `appointment-booking.php`| Book consultation (requires login)          |
| `contact-us.php`        | Contact form                                |
| `checkout.php`          | Checkout (requires login)                   |
| `shopping-cart.php`     | Shopping cart                               |
| `login.php`             | Login form                                  |
| `register.php`          | Registration form                           |
| `logout.php`            | Logout + session destroy + redirect         |
| `cart-update.php`       | Cart CRUD handler (add/remove/delete)       |
| `subscribe.php`         | Newsletter signup handler                   |

## Admin Pages (`/admin/`)

| Page                  | Description                               |
|-----------------------|-------------------------------------------|
| `admin/dashboard.php` | Dashboard with stats (revenue, orders, users) |
| `admin/appointments.php` | View/manage appointments               |
| `admin/orders.php`    | View/manage orders                        |

## How to Add Features

### New Public Page
1. Create `your-page.php` at root
2. Add `require_once 'includes/config.php';` at top
3. Include header/footer
4. Add nav link in `includes/header.php` with active detection

### New Admin Page
1. Create `admin/your-page.php`
2. Auth guard: `if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin')`
3. Include header styles directly (admin pages have standalone HTML)

### DB Queries
- Use `getDB()` from `config/database.php` for the connection
- Always use prepared statements for user-supplied data
- Error details are logged server-side, never shown to users
