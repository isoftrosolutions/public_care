# Ayurviro — Google Stitch Screen Prompts

## Brand Identity
| Element | Value |
|---------|-------|
| **Brand Name** | Ayurviro |
| **Primary Color** | `#005221` (deep forest green) |
| **Accent Color** | `#D4AF37` (warm gold) |
| **Secondary** | `#B7E4C7` (sage green) |
| **Background** | `#F8FAF5` (warm white) |
| **Text** | `#1A1A1A` dark charcoal |
| **Heading Font** | Plus Jakarta Sans |
| **Body Font** | DM Sans |
| **Logo** | `assets/uploads/logo.jpeg` (green leaf + brand mark) |

## Screen Index

### Customer-Facing (12 screens)
| # | File | Screen | Status |
|---|------|--------|--------|
| 1 | `23-splash-screen.md` | Splash / Welcome Screen | 🔲 To build |
| 2 | `24-language-selection.md` | Language Selection | 🔲 To build |
| 3 | `01-payment-screen.md` | Payment Screen | 🔲 To build |
| 4 | `02-prescription-screen.md` | Digital Prescription | 🔲 To build |
| 5 | `03-video-call-screen.md` | Video Call Interface | 🔲 To build |
| 6 | `04-health-records-screen.md` | Health Records | 🔲 To build |
| 7 | `05-profile-screen.md` | Profile / Settings | 🔲 To build |
| 8 | `06-wallet-screen.md` | Wallet | 🔲 To build |
| 9 | `07-notifications-screen.md` | Notifications | 🔲 To build |
| 10 | `08-wishlist-screen.md` | Wishlist | 🔲 To build |
| 11 | `09-subscriptions-screen.md` | Subscriptions (Monthly Meds) | 🔲 To build |

### B2B / Retailer (4 screens)
| # | File | Screen | Status |
|---|------|--------|--------|
| 12 | `10-retailer-dashboard.md` | Retailer Dashboard | 🔲 To build |
| 13 | `11-retailer-order-management.md` | Retailer Order Management | 🔲 To build |
| 14 | `12-retailer-stock-management.md` | Retailer Stock Management | 🔲 To build |
| 15 | `13-retailer-reports.md` | Retailer Sales Reports | 🔲 To build |

### Distributor (1 screen)
| # | File | Screen | Status |
|---|------|--------|--------|
| 16 | `14-distributor-panel.md` | Distributor Portal | 🔲 To build |

### Admin (9 screens)
| # | File | Screen | Status |
|---|------|--------|--------|
| 17 | `15-admin-dashboard.md` | Admin Dashboard | 🔲 To build |
| 18 | `16-admin-user-management.md` | User Management | 🔲 To build |
| 19 | `17-admin-doctor-management.md` | Doctor Management | 🔲 To build |
| 20 | `18-admin-product-management.md` | Product Management | 🔲 To build |
| 21 | `19-admin-orders-management.md` | Orders Management | 🔲 To build |
| 22 | `20-admin-returns-management.md` | Returns & Refunds | 🔲 To build |
| 23 | `21-admin-coupons.md` | Coupons & Offers | 🔲 To build |
| 24 | `22-admin-reports.md` | Reports & Analytics | 🔲 To build |

## Already Built (PHP Pages)
These exist as working PHP files — use for reference, not Stitch:

- `index.php` — Home page
- `login.php` / `register.php` — Auth screens
- `shop.php` / `product-details.php` — Medicine browsing
- `shopping-cart.php` / `checkout.php` — Cart & checkout
- `order-tracking.php` / `orders.php` — Order management
- `order-punch.php` — B2B order punch
- `lab-tests.php` / `lab-booking.php` — Lab tests
- `ai-assistant.php` — AI health chat
- `returns.php` — Return/replacement form
- `doctor-listing.php` / `doctor-profile.php` / `appointment-booking.php` — Doctor consultation
- `about-us.php` / `contact-us.php` — Info pages
- `wellness-blog.php` — Blog
- `subscribe.php` — Newsletter
- `my-health.php` / `my-family.php` / `dosha-quiz.php` / `health-coach.php` — Health features

## How to Use These Prompts
1. Feed each `.md` file into Google Stitch as the prompt
2. Stitch will generate the screen UI (HTML/Tailwind)
3. Convert generated HTML to PHP files using existing patterns:
   - Add `require_once 'includes/config.php';` at top
   - Include `includes/header.php` and `includes/footer.php`
   - Add backend logic (session checks, DB queries, CSRF, prepared statements)
4. Match existing php-ecommerce skill conventions

## Design Consistency Rules
- All screens: Tailwind CSS via CDN (as already set in `includes/header.php`)
- Forms: always include `csrf_token` hidden input
- Mobile-first responsive
- Hover/tap states on all interactive elements
- No `className` — use `class`
- All icons: Material Symbols Outlined (already loaded in header)
- Buttons: `#005221` primary, `rounded-lg`, consistent padding

## Priority Order for Stitch
1. **Payment Screen** — needed to complete checkout flow
2. **Profile / Settings** — user account management
3. **Notifications** — engagement & retention
4. **Wishlist** — conversion booster
5. **Prescription** — core healthcare feature
6. **Subscriptions** — recurring revenue
7. **Wallet** — payment retention
8. **Health Records** — health data consolidation
9. **Video Call** — consultation delivery
10. **Splash + Language** — brand intro
11. **Retailer & Distributor** — B2B expansion
12. **Admin Panel** — platform management
