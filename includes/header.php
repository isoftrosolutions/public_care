<!DOCTYPE html>
<html class="scroll-smooth light" lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title><?= htmlspecialchars($site_title) ?> | <?= SITE_NAME ?></title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Source+Serif+4:wght@400;600;700&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<script>
tailwind.config = {
  darkMode: "class",
  theme: {
    extend: {
      colors: {
        surface: "#f4fafd", "surface-dim": "#d4dbdd", "surface-bright": "#f4fafd",
        "surface-container-lowest": "#ffffff", "surface-container-low": "#eef5f7",
        "surface-container": "#e8eff1", "surface-container-high": "#e2e9ec",
        "surface-container-highest": "#dde4e6", "on-surface": "#161d1f",
        "on-surface-variant": "#414844", "inverse-surface": "#2b3234",
        "inverse-on-surface": "#ebf2f4", outline: "#717973",
        "outline-variant": "#c1c8c2", "surface-tint": "#3f6653",
        primary: "#012d1d", "on-primary": "#ffffff",
        "primary-container": "#1b4332", "on-primary-container": "#86af99",
        "inverse-primary": "#a5d0b9", secondary: "#735c00",
        "on-secondary": "#ffffff", "secondary-container": "#fed65b",
        "on-secondary-container": "#745c00", tertiary: "#002d1a",
        "on-tertiary": "#ffffff", "tertiary-container": "#1a432e",
        "on-tertiary-container": "#84b095", error: "#ba1a1a",
        "on-error": "#ffffff", "error-container": "#ffdad6",
        "on-error-container": "#93000a", "primary-fixed": "#c1ecd4",
        "primary-fixed-dim": "#a5d0b9", "on-primary-fixed": "#002114",
        "on-primary-fixed-variant": "#274e3d", "secondary-fixed": "#ffe088",
        "secondary-fixed-dim": "#e9c349", "on-secondary-fixed": "#241a00",
        "on-secondary-fixed-variant": "#574500", "tertiary-fixed": "#c0edd0",
        "tertiary-fixed-dim": "#a4d1b4", "on-tertiary-fixed": "#002112",
        "on-tertiary-fixed-variant": "#264f39", background: "#f4fafd",
        "on-background": "#161d1f", "surface-variant": "#dde4e6"
      },
      borderRadius: { DEFAULT: "0.25rem", lg: "0.5rem", xl: "0.75rem", full: "9999px" },
      spacing: {
        base: "8px", gutter: "24px", "section-gap": "80px",
        "margin-mobile": "16px", "margin-desktop": "48px", "container-max": "1200px"
      },
      fontFamily: {
        "display-lg": ["Source Serif 4"], "headline-lg": ["Source Serif 4"],
        "headline-md": ["Source Serif 4"], "headline-lg-mobile": ["Source Serif 4"],
        "body-lg": ["Manrope"], "body-md": ["Manrope"],
        "label-md": ["Manrope"], "label-sm": ["Manrope"]
      },
      fontSize: {
        "display-lg": ["48px", { lineHeight: "56px", letterSpacing: "-0.02em", fontWeight: "700" }],
        "headline-lg": ["32px", { lineHeight: "40px", fontWeight: "600" }],
        "headline-md": ["24px", { lineHeight: "32px", fontWeight: "600" }],
        "headline-lg-mobile": ["28px", { lineHeight: "36px", fontWeight: "600" }],
        "body-lg": ["18px", { lineHeight: "28px", fontWeight: "400" }],
        "body-md": ["16px", { lineHeight: "24px", fontWeight: "400" }],
        "label-md": ["14px", { lineHeight: "20px", letterSpacing: "0.05em", fontWeight: "600" }],
        "label-sm": ["12px", { lineHeight: "16px", fontWeight: "500" }]
      }
    }
  }
};
</script>
<style>
.material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
@media (max-width: 1023px) {
  nav.mobile-open { display: flex !important; flex-direction: column; position: absolute; top: 80px; left: 0; width: 100%; background: #f4fafd; padding: 24px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); z-index: 50; }
}
</style>
</head>
<body class="bg-surface text-on-surface font-body-md overflow-x-hidden">

<header class="bg-surface dark:bg-inverse-surface shadow-sm sticky top-0 z-50">
<div class="flex justify-between items-center h-20 px-gutter w-full max-w-container-max mx-auto">
<div class="flex items-center gap-4">
<a href="<?= BASE_URL ?>/index.php">
<img alt="Public Care Ayurveda Logo" class="h-12 w-auto object-contain" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAo879HCds3w21ve37kcaDk6TDBD6jGLmvKyZY044-2j4TI7_Fo3jg5MGIn2vS_Uy3jg523kaixaI9ewUNDrQnspvl92wCOxeXw3qX_NNldczaZIT3RTuZZl-ERSsjdCWSpLmC_9EVaIHo1ut2kqTZDdm2Ighvwpvul1Cg1nfmqPr1xgZydE72HjEN6ISVD-7gJT1zBWpbOG5yyRaloF-sBOHDbzme6CNKxf-SZ6ElHJDdS2ew6k7nkVl2Ul0YZYjPaefVIy8Wakas"/>
</a>
</div>
<nav class="hidden lg:flex items-center gap-8">
<a class="<?= $current_page === 'index.php' ? 'text-primary border-b-2 border-primary' : 'text-on-surface-variant hover:text-primary' ?> pb-1 font-label-md text-label-md transition-colors" href="<?= BASE_URL ?>/index.php">Home</a>
<a class="<?= $current_page === 'shop.php' ? 'text-primary border-b-2 border-primary' : 'text-on-surface-variant hover:text-primary' ?> pb-1 font-label-md text-label-md transition-colors" href="<?= BASE_URL ?>/shop.php">Shop</a>
<a class="<?= in_array($current_page, ['doctor-listing.php','doctor-profile.php']) ? 'text-primary border-b-2 border-primary' : 'text-on-surface-variant hover:text-primary' ?> pb-1 font-label-md text-label-md transition-colors" href="<?= BASE_URL ?>/doctor-listing.php">Consult Doctor</a>
<a class="text-on-surface-variant hover:text-primary pb-1 font-label-md text-label-md transition-colors" href="<?= BASE_URL ?>/index.php#wellness-plans">Wellness Plans</a>
<a class="<?= $current_page === 'wellness-blog.php' ? 'text-primary border-b-2 border-primary' : 'text-on-surface-variant hover:text-primary' ?> pb-1 font-label-md text-label-md transition-colors" href="<?= BASE_URL ?>/wellness-blog.php">Blog</a>
<a class="<?= $current_page === 'about-us.php' ? 'text-primary border-b-2 border-primary' : 'text-on-surface-variant hover:text-primary' ?> pb-1 font-label-md text-label-md transition-colors" href="<?= BASE_URL ?>/about-us.php">About</a>
</nav>
<div class="flex items-center gap-6">
<button class="material-symbols-outlined text-on-surface-variant hover:text-primary transition-colors">search</button>
<div class="relative">
<a href="<?= BASE_URL ?>/shopping-cart.php" class="material-symbols-outlined text-on-surface-variant hover:text-primary transition-colors">shopping_cart</a>
<span id="cart-count-badge" class="absolute -top-2 -right-2 bg-secondary text-on-secondary text-[10px] w-4 h-4 rounded-full flex items-center justify-center font-bold"><?= htmlspecialchars($_SESSION['cart_count'] ?? 0) ?></span>
</div>
<?php if (isset($_SESSION['user_id'])): ?>
<a class="bg-primary-container text-on-primary-container px-6 py-2.5 rounded-full font-label-md text-label-md hover:bg-primary transition-all" href="<?= BASE_URL ?>/admin/dashboard.php">Dashboard</a>
<a class="text-on-surface-variant hover:text-primary font-label-md text-label-md" href="<?= BASE_URL ?>/logout.php">Logout</a>
<?php else: ?>
<a class="bg-primary text-on-primary px-6 py-2.5 rounded-full font-label-md text-label-md hover:bg-primary-container transition-all scale-95 active:scale-90 inline-block" href="<?= BASE_URL ?>/login.php">Login</a>
<?php endif; ?>
<button class="lg:hidden material-symbols-outlined" id="mobile-menu-btn">menu</button>
</div>
</div>
</header>
<main>
