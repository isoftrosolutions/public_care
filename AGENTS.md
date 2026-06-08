# Public Care Ayurveda — Project Documentation

## Project Overview

Public Care Ayurveda is a PHP-based Ayurvedic healthcare website. It provides a platform for users to browse Ayurvedic products, book doctor consultations, read health blog posts, and manage their appointments/orders. The admin panel allows staff to manage products, orders, appointments, blog content, and site data.

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
├── includes/         # Reusable PHP partials (header, footer, config, functions)
├── admin/            # Admin panel pages
├── config/           # Database connection config
├── sql/              # SQL dump / schema files
├── assets/           # CSS, JS, images, uploads
│   ├── css/
│   ├── js/
│   ├── images/
│   └── uploads/
├── src/              # Original HTML templates (reference only, not served)
└── *.php             # Public-facing pages at root
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
| `orders`      | Product orders                    |
| `order_items` | Line items within an order        |
| `cart`        | Shopping cart items               |
| `contacts`    | Contact form submissions          |
| `reviews`     | Product reviews                   |
| `blog_posts`  | Health & wellness articles        |

## Key Files

| File                                   | Purpose                                          |
|----------------------------------------|--------------------------------------------------|
| `config/database.php`                  | MySQL connection (host=localhost, user=root, pass=empty, db=public_care_ayurveda) |
| `includes/config.php`                  | Session start, constants, BASE_URL, DB include    |
| `includes/header.php`                  | HTML head, nav, Tailwind CDN, Google Fonts        |
| `includes/footer.php`                  | Closing tags, scripts, footer content             |
| `includes/functions.php`               | Shared helper functions                           |

## Base URL

```php
define('BASE_URL', '/public_care_ayurveda');
```

All links and redirects use `BASE_URL` for portability.

## Database Credentials (`config/database.php`)

```php
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'public_care_ayurveda';
```

## How Pages Work

1. Every page starts with `require_once 'includes/config.php';` to boot sessions and constants.
2. `include 'includes/header.php';` renders the full `<head>`, Tailwind CDN, Google Fonts, Material Symbols, and the navigation bar.
3. Page-specific content is rendered in the middle.
4. `include 'includes/footer.php';` closes the HTML.

### Navigation & Active Detection

The `$current_page` variable is set before including the header:

```php
$current_page = 'home'; // or 'products', 'doctors', 'blog', 'contact', etc.
```

The header checks `$current_page` to apply `active` styling to the corresponding nav link.

## Frontend Dependencies (loaded via CDN)

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

Tailwind custom colors are defined via inline config in `includes/header.php`:

```html
<script>
tailwind.config = {
  theme: {
    extend: {
      colors: {
        primary: '#1B4332',
        accent: '#D4AF37',
        secondary: '#B7E4C7',
      }
    }
  }
}
</script>
```

## Design System

**Style: Modern Traditionalism** — blends clinical credibility with botanical warmth.

- Ayurvedic healthcare aesthetic (earthy greens, warm gold accents, natural textures)
- Generous whitespace for readability
- Card-based layouts for products, doctors, blog posts
- Rounded corners, soft shadows, clean typography
- Responsive (mobile-first via Tailwind)
- Contact/booking forms styled as clean cards
- Material Symbols used for icons rather than images

## Session-Based Authentication

Auth is handled via PHP `$_SESSION`:

```php
$_SESSION['user_id']   // user ID
$_SESSION['user_name'] // display name
$_SESSION['role']      // 'admin' or 'user'
```

- Login check: `if (!isset($_SESSION['user_id'])) { header('Location: ' . BASE_URL . '/login.php'); exit; }`
- Admin check: `if ($_SESSION['role'] !== 'admin') { /* deny */ }`

### Session-Protected Pages

- `cart.php`, `checkout.php`, `my-orders.php`, `my-appointments.php` require user login.
- All `/admin/` pages require admin role.

## All Pages

### Public Pages (Root Level)

| Page                    | Description                                |
|-------------------------|--------------------------------------------|
| `index.php`             | Home page — hero, featured products, doctors, blog preview |
| `about.php`             | About us — clinic story, team intro        |
| `products.php`          | Product listing with category filters      |
| `product-detail.php?id=XX` | Single product detail page             |
| `doctors.php`           | Doctor listing / profiles                  |
| `doctor-detail.php?id=XX` | Single doctor profile with booking CTA  |
| `blog.php`              | Blog listing                               |
| `blog-detail.php?id=XX` | Single blog post                          |
| `contact.php`           | Contact form                               |
| `cart.php`              | Shopping cart (auth required)              |
| `checkout.php`          | Checkout page (auth required)              |
| `my-orders.php`         | User's order history (auth required)       |
| `my-appointments.php`   | User's appointments (auth required)        |
| `login.php`             | Login form                                 |
| `register.php`          | Registration form                          |
| `logout.php`            | Logout + session destroy + redirect        |
| `search.php`            | Search results for products/blog           |
| `terms.php`             | Terms & conditions                         |
| `privacy.php`           | Privacy policy                             |

### Admin Pages (`/admin/`)

| Page                  | Description                               |
|-----------------------|-------------------------------------------|
| `admin/index.php`     | Dashboard with stats                      |
| `admin/users.php`     | Manage users                              |
| `admin/products.php`  | CRUD products                             |
| `admin/categories.php`| CRUD categories                           |
| `admin/doctors.php`   | CRUD doctors                              |
| `admin/appointments.php` | View/manage appointments              |
| `admin/orders.php`    | View/manage orders                        |
| `admin/blog.php`      | CRUD blog posts                           |
| `admin/contacts.php`  | View contact submissions                  |
| `admin/reviews.php`   | Moderate product reviews                  |
| `admin/admin-header.php` | Admin nav + layout partial            |
| `admin/admin-footer.php` | Admin footer partial                  |

## How to Add Features

### New Public Page

1. Create `your-page.php` at root.
2. Add `require_once 'includes/config.php';` at top.
3. Set `$current_page = 'your-page';`.
4. Include header/footer.
5. Write page content.
6. Add nav link in `includes/header.php` with active detection.

### New Admin Page

1. Create `admin/your-page.php`.
2. Add auth guard: `if ($_SESSION['role'] !== 'admin')`.
3. Include `admin/admin-header.php` and `admin/admin-footer.php`.
4. Add link in admin nav (in `admin/admin-header.php`).

### How to Modify Existing Pages

- **Header/nav changes**: edit `includes/header.php`
- **Footer changes**: edit `includes/footer.php`
- **DB queries**: use `config/database.php` connection (`$conn = mysqli_connect(...)`)
- **Styling**: Tailwind utility classes directly in HTML, or edit `tailwind.config` block in header
- **Colors**: global changes go in the `tailwind.config` colors block in header
