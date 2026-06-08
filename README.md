# Public Care Ayurveda

**Ancient Wisdom for Modern Living** — A full-featured Ayurvedic healthcare website with e-commerce, doctor consultations, and wellness blog.

## Tech Stack

| Layer       | Technology     |
|-------------|----------------|
| Backend     | PHP 8.x        |
| Database    | MySQL          |
| Frontend    | Tailwind CSS   |
| Server      | Apache (XAMPP) |

## Features

- **Shop** — Browse & purchase Ayurvedic products with cart/checkout flow (Indian Rupees)
- **Doctors** — View doctor profiles and book consultations
- **Wellness Blog** — Health articles with categories & rich content
- **User Accounts** — Register, login, order history, appointment management
- **Admin Panel** — Manage products, orders, appointments, blog, users, and more
- **Responsive Design** — Mobile-first layout using Tailwind CSS
- **Indian Payment Gateways** — Razorpay, Paytm, UPI (Google Pay, PhonePe, BHIM)
- **Security** — CSRF protection, session hardening, XSS prevention, SQL injection prevention

## Database

**Database name:** `public_care_ayurveda`

Import `sql/schema.sql` into MySQL to set up all tables: users, products, categories, doctors, appointments, orders, order_items, cart, contacts, reviews, blog_posts, subscribers.

## Installation

1. Clone the repo into your XAMPP `htdocs` directory:
   ```bash
   git clone https://github.com/isoftrosolutions/public_care.git
   ```
2. Start Apache & MySQL via XAMPP Control Panel
3. Create a MySQL database named `public_care_ayurveda`
4. Import `sql/schema.sql` into the database
5. Update database credentials in `config/database.php` if needed
6. Open `http://localhost/www/public_care_ayurveda` in your browser

## Directory Structure

```
├── includes/         # Header, footer, config
├── admin/            # Admin panel (dashboard, appointments, orders)
├── config/           # Database connection
├── sql/              # Database schema
├── assets/           # CSS, JS, images, uploads
├── src/              # Original HTML templates (reference)
├── .htaccess         # Security: blocks directory listing & .git access
├── cart-update.php   # Ajax cart handler (add/remove/delete)
├── subscribe.php     # Newsletter signup handler
└── *.php             # Public-facing pages
```
