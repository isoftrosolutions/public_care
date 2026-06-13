<!DOCTYPE html>
<html class="scroll-smooth light" lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title><?= htmlspecialchars($site_title) ?> | <?= SITE_NAME ?></title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<script>
tailwind.config = {
  darkMode: "class",
  theme: {
    extend: {
      colors: {
        primary: "#005221", "on-primary": "#ffffff",
        "primary-container": "#1e6b34", "on-primary-container": "#9be9a4",
        "primary-fixed": "#a6f5af", "primary-fixed-dim": "#8bd895",
        "on-primary-fixed": "#002109", "on-primary-fixed-variant": "#005321",
        "inverse-primary": "#8bd895",
        secondary: "#5a605a", "on-secondary": "#ffffff",
        "secondary-container": "#dfe4dc", "on-secondary-container": "#606660",
        "secondary-fixed": "#dfe4dc", "secondary-fixed-dim": "#c3c8c1",
        "on-secondary-fixed": "#181d18", "on-secondary-fixed-variant": "#434843",
        tertiary: "#5a4200", "on-tertiary": "#ffffff",
        "tertiary-container": "#785800", "on-tertiary-container": "#ffd06c",
        "tertiary-fixed": "#ffdf9f", "tertiary-fixed-dim": "#f6be35",
        "on-tertiary-fixed": "#261a00", "on-tertiary-fixed-variant": "#5c4300",
        error: "#ba1a1a", "on-error": "#ffffff",
        "error-container": "#ffdad6", "on-error-container": "#93000a",
        background: "#fbf9f8", "on-background": "#1b1c1c",
        surface: "#fbf9f8", "surface-dim": "#dbd9d9", "surface-bright": "#fbf9f8",
        "surface-container-lowest": "#ffffff", "surface-container-low": "#f5f3f3",
        "surface-container": "#efeded", "surface-container-high": "#eae8e7",
        "surface-container-highest": "#e4e2e2", "surface-variant": "#e4e2e2",
        "on-surface": "#1b1c1c", "on-surface-variant": "#40493f",
        "inverse-surface": "#303030", "inverse-on-surface": "#f2f0f0",
        outline: "#707a6e", "outline-variant": "#bfc9bc", "surface-tint": "#1f6c35"
      },
      borderRadius: { DEFAULT: "0.25rem", lg: "0.5rem", xl: "0.75rem", full: "9999px" },
      spacing: {
        base: "8px", gutter: "24px", "section-gap": "80px",
        "margin-mobile": "16px", "margin-desktop": "64px", "container-max": "1280px"
      },
      fontFamily: {
        "display-lg": ["DM Sans"], "headline-lg": ["DM Sans"],
        "headline-md": ["DM Sans"], "headline-lg-mobile": ["DM Sans"],
        "title-lg": ["Plus Jakarta Sans"], "label-lg": ["Plus Jakarta Sans"],
        "label-sm": ["Plus Jakarta Sans"], "body-lg": ["Plus Jakarta Sans"],
        "body-md": ["Plus Jakarta Sans"]
      },
      fontSize: {
        "display-lg": ["48px", { lineHeight: "56px", letterSpacing: "-0.02em", fontWeight: "700" }],
        "headline-lg": ["32px", { lineHeight: "40px", fontWeight: "700" }],
        "headline-md": ["24px", { lineHeight: "32px", fontWeight: "600" }],
        "headline-lg-mobile": ["24px", { lineHeight: "32px", fontWeight: "700" }],
        "title-lg": ["20px", { lineHeight: "28px", fontWeight: "600" }],
        "label-lg": ["14px", { lineHeight: "20px", letterSpacing: "0.01em", fontWeight: "600" }],
        "label-sm": ["12px", { lineHeight: "16px", fontWeight: "500" }],
        "body-lg": ["16px", { lineHeight: "24px", fontWeight: "400" }],
        "body-md": ["14px", { lineHeight: "20px", fontWeight: "400" }]
      }
    }
  }
};
</script>
<style>
body { background-color: #fbf9f8; color: #1b1c1c; overflow-x: hidden; -webkit-font-smoothing: antialiased; }

.material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
.hover-lift { transition: transform 0.2s ease, box-shadow 0.2s ease; }
.hover-lift:hover { transform: translateY(-4px); box-shadow: 0px 8px 30px rgba(0, 0, 0, 0.08); }
.hide-scrollbar::-webkit-scrollbar { display: none; }
.hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
.nav-link { position: relative; transition: color 0.2s ease; }
.nav-link::after { content: ''; position: absolute; bottom: 0; left: 50%; width: 0; height: 2px; background: #005221; transition: all 0.2s ease; transform: translateX(-50%); }
.nav-link:hover::after, .nav-link.active::after { width: 100%; }
.nav-link.active { color: #005221; font-weight: 600; }
</style>
</head>
<body class="bg-background text-on-surface font-body-md overflow-x-hidden">

<header class="fixed top-0 left-0 right-0 z-50 bg-surface/80 dark:bg-surface-dim/80 backdrop-blur-lg transition-all duration-300 shadow-sm">
<div class="flex justify-between items-center w-full px-base md:px-margin-desktop py-3 max-w-container-max mx-auto">
<div class="flex items-center gap-6 lg:gap-10">
<a class="font-display-lg text-headline-md font-bold text-primary tracking-tight" href="<?= BASE_URL ?>/index.php"><?= SITE_NAME ?></a>
<nav class="hidden xl:flex items-center gap-1">
<a class="nav-link px-3 py-2 text-label-lg font-label-lg text-on-surface-variant hover:text-primary transition-colors <?= $current_page === 'index.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/index.php">Home</a>
<a class="nav-link px-3 py-2 text-label-lg font-label-lg text-on-surface-variant hover:text-primary transition-colors <?= $current_page === 'order-punch.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/order-punch.php">Order Punch</a>
<a class="nav-link px-3 py-2 text-label-lg font-label-lg text-on-surface-variant hover:text-primary transition-colors <?= $current_page === 'shop.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/shop.php">Medicine</a>
<a class="nav-link px-3 py-2 text-label-lg font-label-lg text-on-surface-variant hover:text-primary transition-colors <?= $current_page === 'lab-tests.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/lab-tests.php">Lab Tests</a>
<a class="nav-link px-3 py-2 text-label-lg font-label-lg text-on-surface-variant hover:text-primary transition-colors <?= in_array($current_page, ['doctor-listing.php','doctor-profile.php','video-consult.php']) ? 'active' : '' ?>" href="<?= BASE_URL ?>/doctor-listing.php">Consult</a>
<a class="nav-link px-3 py-2 text-label-lg font-label-lg text-on-surface-variant hover:text-primary transition-colors <?= $current_page === 'ai-assistant.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/ai-assistant.php">AI Health</a>
<a class="nav-link px-3 py-2 text-label-lg font-label-lg text-on-surface-variant hover:text-primary transition-colors <?= $current_page === 'orders.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/orders.php">Orders</a>
<a class="nav-link px-3 py-2 text-label-lg font-label-lg text-on-surface-variant hover:text-primary transition-colors <?= $current_page === 'returns.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/returns.php">Returns</a>
</nav>
</div>

<div class="flex items-center gap-1 sm:gap-2 shrink-0">
<?php if (isset($_SESSION['user_id'])): ?>
<a href="<?= BASE_URL ?>/notifications.php" class="hidden md:inline-flex relative p-2 text-on-surface-variant hover:text-primary transition-colors rounded-lg hover:bg-surface-container">
<span class="material-symbols-outlined text-xl">notifications</span>
</a>
<?php endif; ?>
<a href="<?= BASE_URL ?>/shopping-cart.php" class="relative p-2 text-on-surface-variant hover:text-primary transition-colors rounded-lg hover:bg-surface-container">
<span class="material-symbols-outlined text-xl">shopping_cart</span>
<span id="cart-count-badge" class="absolute -top-0.5 -right-0.5 bg-primary text-on-primary text-[10px] min-w-[18px] h-[18px] rounded-full flex items-center justify-center font-bold px-1"><?= htmlspecialchars($_SESSION['cart_count'] ?? 0) ?></span>
</a>

<div class="relative" id="lang-switcher">
<button onclick="document.getElementById('lang-menu').classList.toggle('hidden')" class="p-2 text-on-surface-variant hover:text-primary transition-colors rounded-lg hover:bg-surface-container text-sm font-medium flex items-center gap-1">
<span><?php $langs = getAvailableLanguages(); echo $langs[$_SESSION['lang']]['flag'] ?? '🌐'; ?></span>
</button>
<div id="lang-menu" class="hidden absolute right-0 mt-2 w-44 bg-surface-container-lowest rounded-xl shadow-lg border border-outline-variant/30 py-2 z-[100]">
<?php foreach (getAvailableLanguages() as $code => $info): ?>
<a href="?lang=<?= $code ?>" class="flex items-center gap-3 px-4 py-2.5 hover:bg-surface-container transition-colors text-sm <?= ($_SESSION['lang'] ?? 'hi') === $code ? 'bg-secondary-container font-bold' : '' ?>">
<span><?= $info['flag'] ?></span>
<span><?= $info['native'] ?></span>
</a>
<?php endforeach; ?>
</div>
</div>

<?php if (isset($_SESSION['user_id'])): ?>
<?php $initial = strtoupper(($_SESSION['user_name'] ?? 'A')[0]); ?>
<div class="relative hidden xl:block">
<button onclick="document.getElementById('profile-menu').classList.toggle('hidden')" class="flex items-center gap-2 pl-2 pr-3 py-1.5 rounded-full hover:bg-surface-container transition-colors border border-transparent hover:border-outline-variant/40 group">
<div class="w-8 h-8 rounded-full bg-primary text-on-primary flex items-center justify-center text-sm font-bold font-display-lg"><?= $initial ?></div>
<span class="hidden xl:block text-label-sm font-label-sm text-on-surface max-w-[100px] truncate"><?= htmlspecialchars($_SESSION['user_name'] ?? '') ?></span>
<span class="material-symbols-outlined text-sm text-on-surface-variant transition-transform duration-200 group-[:has(+div:not(.hidden))]:rotate-180">expand_more</span>
</button>
<div id="profile-menu" class="hidden fixed md:absolute top-[calc(100%+8px)] right-0 left-4 md:left-auto md:w-72 bg-surface-container-lowest rounded-2xl shadow-2xl border border-outline-variant/30 overflow-hidden z-[100]">
<div class="bg-gradient-to-br from-primary/5 to-transparent px-5 pt-5 pb-4 border-b border-outline-variant/20">
<div class="flex items-center gap-3">
<div class="w-12 h-12 rounded-full bg-primary text-on-primary flex items-center justify-center text-xl font-bold font-display-lg shadow-sm"><?= $initial ?></div>
<div>
<p class="font-label-lg text-on-surface"><?= htmlspecialchars($_SESSION['user_name'] ?? '') ?></p>
<p class="text-label-sm text-on-surface-variant"><?= htmlspecialchars($_SESSION['role'] ?? 'customer') ?></p>
</div>
</div>
</div>
<div class="py-2">
<p class="px-5 pt-2 pb-1 text-[11px] font-bold uppercase tracking-widest text-outline">Health</p>
<a class="flex items-center gap-3 px-5 py-2.5 hover:bg-surface-container text-sm text-on-surface transition-colors" href="<?= BASE_URL ?>/health-coach.php"><span class="material-symbols-outlined text-lg text-primary-fixed-dim">notifications</span> Health Coach</a>
<a class="flex items-center gap-3 px-5 py-2.5 hover:bg-surface-container text-sm text-on-surface transition-colors" href="<?= BASE_URL ?>/my-health.php"><span class="material-symbols-outlined text-lg text-primary-fixed-dim">monitoring</span> My Health</a>
<a class="flex items-center gap-3 px-5 py-2.5 hover:bg-surface-container text-sm text-on-surface transition-colors" href="<?= BASE_URL ?>/health-records.php"><span class="material-symbols-outlined text-lg text-primary-fixed-dim">folder_shared</span> Health Records</a>
<a class="flex items-center gap-3 px-5 py-2.5 hover:bg-surface-container text-sm text-on-surface transition-colors" href="<?= BASE_URL ?>/prescriptions.php"><span class="material-symbols-outlined text-lg text-primary-fixed-dim">prescriptions</span> Prescriptions</a>
<a class="flex items-center gap-3 px-5 py-2.5 hover:bg-surface-container text-sm text-on-surface transition-colors" href="<?= BASE_URL ?>/my-family.php"><span class="material-symbols-outlined text-lg text-primary-fixed-dim">family_history</span> My Family</a>
</div>
<div class="border-t border-outline-variant/20 py-2">
<p class="px-5 pt-2 pb-1 text-[11px] font-bold uppercase tracking-widest text-outline">Account</p>
<a class="flex items-center gap-3 px-5 py-2.5 hover:bg-surface-container text-sm text-on-surface transition-colors" href="<?= BASE_URL ?>/profile.php"><span class="material-symbols-outlined text-lg text-tertiary-fixed-dim">manage_accounts</span> Profile Settings</a>
<a class="flex items-center gap-3 px-5 py-2.5 hover:bg-surface-container text-sm text-on-surface transition-colors" href="<?= BASE_URL ?>/notifications.php"><span class="material-symbols-outlined text-lg text-tertiary-fixed-dim">notifications</span> Notifications</a>
<a class="flex items-center gap-3 px-5 py-2.5 hover:bg-surface-container text-sm text-on-surface transition-colors" href="<?= BASE_URL ?>/wishlist.php"><span class="material-symbols-outlined text-lg text-tertiary-fixed-dim">favorite</span> Wishlist</a>
<a class="flex items-center gap-3 px-5 py-2.5 hover:bg-surface-container text-sm text-on-surface transition-colors" href="<?= BASE_URL ?>/subscriptions.php"><span class="material-symbols-outlined text-lg text-tertiary-fixed-dim">calendar_month</span> Subscriptions</a>
<a class="flex items-center gap-3 px-5 py-2.5 hover:bg-surface-container text-sm text-on-surface transition-colors" href="<?= BASE_URL ?>/wallet.php"><span class="material-symbols-outlined text-lg text-tertiary-fixed-dim">account_balance_wallet</span> Wallet</a>
</div>
<?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'retailer' || $_SESSION['role'] === 'distributor'): ?>
<div class="border-t border-outline-variant/20 py-2">
<p class="px-5 pt-2 pb-1 text-[11px] font-bold uppercase tracking-widest text-outline">Business</p>
<?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'customer'): ?>
<a class="flex items-center gap-3 px-5 py-2.5 hover:bg-surface-container text-sm text-on-surface transition-colors" href="<?= BASE_URL ?>/order-tracking.php"><span class="material-symbols-outlined text-lg text-secondary-fixed-dim">local_shipping</span> Order Tracking</a>
<?php endif; ?>
<?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'retailer'): ?>
<a class="flex items-center gap-3 px-5 py-2.5 hover:bg-surface-container text-sm text-on-surface transition-colors" href="<?= BASE_URL ?>/retailer-dashboard.php"><span class="material-symbols-outlined text-lg text-secondary-fixed-dim">storefront</span> Retailer Panel</a>
<?php endif; ?>
<?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'distributor'): ?>
<a class="flex items-center gap-3 px-5 py-2.5 hover:bg-surface-container text-sm text-on-surface transition-colors" href="<?= BASE_URL ?>/distributor-panel.php"><span class="material-symbols-outlined text-lg text-secondary-fixed-dim">warehouse</span> Distributor Panel</a>
<?php endif; ?>
<?php if ($_SESSION['role'] === 'admin'): ?>
<a class="flex items-center gap-3 px-5 py-2.5 hover:bg-surface-container text-sm text-on-surface transition-colors" href="<?= BASE_URL ?>/admin/dashboard.php"><span class="material-symbols-outlined text-lg text-secondary-fixed-dim">dashboard</span> Admin Dashboard</a>
<?php endif; ?>
</div>
<?php endif; ?>
<div class="border-t border-outline-variant/20 py-2">
<a class="flex items-center gap-3 px-5 py-2.5 hover:bg-surface-container text-sm text-error transition-colors" href="<?= BASE_URL ?>/logout.php"><span class="material-symbols-outlined text-lg">logout</span> Sign Out</a>
</div>
</div>
</div>
<?php else: ?>
<a class="hidden xl:inline-flex items-center gap-2 bg-primary text-on-primary px-5 py-2.5 rounded-full font-label-lg text-label-lg hover:bg-primary-container transition-all active:scale-95" href="<?= BASE_URL ?>/login.php"><span class="material-symbols-outlined text-lg">person</span> Login</a>
<?php endif; ?>
<button class="xl:hidden p-2 text-on-surface-variant hover:text-primary transition-colors rounded-lg hover:bg-surface-container" id="mobile-menu-btn" type="button" aria-controls="mobile-menu" aria-expanded="false" aria-label="Open navigation menu"><span class="material-symbols-outlined text-xl">menu</span></button>
</div>
</div>
</header>

<div id="mobile-menu" class="hidden fixed inset-0 z-[90] xl:hidden bg-surface/95 backdrop-blur-lg flex-col h-dvh w-screen max-w-full">
<div class="flex items-center justify-between px-5 py-4 border-b border-outline-variant/20 shrink-0 pt-[max(1rem,env(safe-area-inset-top))]">
<span class="font-display-lg text-headline-md font-bold text-primary"><?= SITE_NAME ?></span>
<button class="close-mobile-btn p-2 text-on-surface-variant rounded-lg hover:bg-surface-container" type="button" aria-label="Close navigation menu"><span class="material-symbols-outlined">close</span></button>
</div>
<?php if (isset($_SESSION['user_id'])): ?>
<div class="flex items-center gap-3 px-5 py-4 border-b border-outline-variant/20 bg-primary/5 shrink-0">
<div class="w-10 h-10 rounded-full bg-primary text-on-primary flex items-center justify-center text-base font-bold font-display-lg"><?php $initial = strtoupper(($_SESSION['user_name'] ?? 'A')[0]); echo $initial; ?></div>
<div>
<p class="font-label-lg text-on-surface text-sm"><?= htmlspecialchars($_SESSION['user_name'] ?? '') ?></p>
<p class="text-label-sm text-on-surface-variant text-xs"><?= htmlspecialchars($_SESSION['role'] ?? 'customer') ?></p>
</div>
</div>
<?php endif; ?>
<div class="flex-1 overflow-y-auto">
<nav class="py-2">
<a class="flex items-center gap-3 px-5 py-3 text-sm text-on-surface hover:bg-surface-container transition-colors <?= $current_page === 'index.php' ? 'bg-surface-container font-bold' : '' ?>" href="<?= BASE_URL ?>/index.php"><span class="material-symbols-outlined text-lg text-on-surface-variant">home</span> Home</a>
<a class="flex items-center gap-3 px-5 py-3 text-sm text-on-surface hover:bg-surface-container transition-colors <?= $current_page === 'order-punch.php' ? 'bg-surface-container font-bold' : '' ?>" href="<?= BASE_URL ?>/order-punch.php"><span class="material-symbols-outlined text-lg text-on-surface-variant">bolt</span> Order Punch</a>
<a class="flex items-center gap-3 px-5 py-3 text-sm text-on-surface hover:bg-surface-container transition-colors <?= $current_page === 'shop.php' ? 'bg-surface-container font-bold' : '' ?>" href="<?= BASE_URL ?>/shop.php"><span class="material-symbols-outlined text-lg text-on-surface-variant">medication</span> Medicine</a>
<a class="flex items-center gap-3 px-5 py-3 text-sm text-on-surface hover:bg-surface-container transition-colors <?= $current_page === 'lab-tests.php' ? 'bg-surface-container font-bold' : '' ?>" href="<?= BASE_URL ?>/lab-tests.php"><span class="material-symbols-outlined text-lg text-on-surface-variant">science</span> Lab Tests</a>
<a class="flex items-center gap-3 px-5 py-3 text-sm text-on-surface hover:bg-surface-container transition-colors <?= in_array($current_page, ['doctor-listing.php','doctor-profile.php','video-consult.php']) ? 'bg-surface-container font-bold' : '' ?>" href="<?= BASE_URL ?>/doctor-listing.php"><span class="material-symbols-outlined text-lg text-on-surface-variant">stethoscope</span> Consult</a>
<a class="flex items-center gap-3 px-5 py-3 text-sm text-on-surface hover:bg-surface-container transition-colors <?= $current_page === 'ai-assistant.php' ? 'bg-surface-container font-bold' : '' ?>" href="<?= BASE_URL ?>/ai-assistant.php"><span class="material-symbols-outlined text-lg text-on-surface-variant">psychology</span> AI Health</a>
<a class="flex items-center gap-3 px-5 py-3 text-sm text-on-surface hover:bg-surface-container transition-colors <?= $current_page === 'orders.php' ? 'bg-surface-container font-bold' : '' ?>" href="<?= BASE_URL ?>/orders.php"><span class="material-symbols-outlined text-lg text-on-surface-variant">shopping_bag</span> Orders</a>
<a class="flex items-center gap-3 px-5 py-3 text-sm text-on-surface hover:bg-surface-container transition-colors <?= $current_page === 'returns.php' ? 'bg-surface-container font-bold' : '' ?>" href="<?= BASE_URL ?>/returns.php"><span class="material-symbols-outlined text-lg text-on-surface-variant">assignment_return</span> Returns</a>
</nav>
<?php if (isset($_SESSION['user_id'])): ?>
<hr class="border-outline-variant/20 mx-5">
<nav class="py-2">
<p class="px-5 pt-3 pb-1 text-[10px] font-bold uppercase tracking-widest text-outline">Health</p>
<a class="flex items-center gap-3 px-5 py-2.5 text-sm text-on-surface hover:bg-surface-container transition-colors" href="<?= BASE_URL ?>/health-coach.php"><span class="material-symbols-outlined text-lg text-primary-fixed-dim">notifications</span> Health Coach</a>
<a class="flex items-center gap-3 px-5 py-2.5 text-sm text-on-surface hover:bg-surface-container transition-colors" href="<?= BASE_URL ?>/my-health.php"><span class="material-symbols-outlined text-lg text-primary-fixed-dim">monitoring</span> My Health</a>
<a class="flex items-center gap-3 px-5 py-2.5 text-sm text-on-surface hover:bg-surface-container transition-colors" href="<?= BASE_URL ?>/health-records.php"><span class="material-symbols-outlined text-lg text-primary-fixed-dim">folder_shared</span> Health Records</a>
<a class="flex items-center gap-3 px-5 py-2.5 text-sm text-on-surface hover:bg-surface-container transition-colors" href="<?= BASE_URL ?>/prescriptions.php"><span class="material-symbols-outlined text-lg text-primary-fixed-dim">prescriptions</span> Prescriptions</a>
<a class="flex items-center gap-3 px-5 py-2.5 text-sm text-on-surface hover:bg-surface-container transition-colors" href="<?= BASE_URL ?>/my-family.php"><span class="material-symbols-outlined text-lg text-primary-fixed-dim">family_history</span> My Family</a>
<div class="border-t border-outline-variant/20 my-1"></div>
<p class="px-5 pt-3 pb-1 text-[10px] font-bold uppercase tracking-widest text-outline">Account</p>
<a class="flex items-center gap-3 px-5 py-2.5 text-sm text-on-surface hover:bg-surface-container transition-colors" href="<?= BASE_URL ?>/profile.php"><span class="material-symbols-outlined text-lg text-tertiary-fixed-dim">manage_accounts</span> Profile</a>
<a class="flex items-center gap-3 px-5 py-2.5 text-sm text-on-surface hover:bg-surface-container transition-colors" href="<?= BASE_URL ?>/notifications.php"><span class="material-symbols-outlined text-lg text-tertiary-fixed-dim">notifications</span> Notifications</a>
<a class="flex items-center gap-3 px-5 py-2.5 text-sm text-on-surface hover:bg-surface-container transition-colors" href="<?= BASE_URL ?>/wishlist.php"><span class="material-symbols-outlined text-lg text-tertiary-fixed-dim">favorite</span> Wishlist</a>
<a class="flex items-center gap-3 px-5 py-2.5 text-sm text-on-surface hover:bg-surface-container transition-colors" href="<?= BASE_URL ?>/subscriptions.php"><span class="material-symbols-outlined text-lg text-tertiary-fixed-dim">calendar_month</span> Subscriptions</a>
<a class="flex items-center gap-3 px-5 py-2.5 text-sm text-on-surface hover:bg-surface-container transition-colors" href="<?= BASE_URL ?>/wallet.php"><span class="material-symbols-outlined text-lg text-tertiary-fixed-dim">account_balance_wallet</span> Wallet</a>
<div class="border-t border-outline-variant/20 my-1"></div>
<p class="px-5 pt-3 pb-1 text-[10px] font-bold uppercase tracking-widest text-outline">Business</p>
<a class="flex items-center gap-3 px-5 py-2.5 text-sm text-on-surface hover:bg-surface-container transition-colors" href="<?= BASE_URL ?>/order-tracking.php"><span class="material-symbols-outlined text-lg text-secondary-fixed-dim">local_shipping</span> Order Tracking</a>
<a class="flex items-center gap-3 px-5 py-2.5 text-sm text-on-surface hover:bg-surface-container transition-colors" href="<?= BASE_URL ?>/retailer-dashboard.php"><span class="material-symbols-outlined text-lg text-secondary-fixed-dim">storefront</span> Retailer Panel</a>
<a class="flex items-center gap-3 px-5 py-2.5 text-sm text-on-surface hover:bg-surface-container transition-colors" href="<?= BASE_URL ?>/distributor-panel.php"><span class="material-symbols-outlined text-lg text-secondary-fixed-dim">warehouse</span> Distributor Panel</a>
<a class="flex items-center gap-3 px-5 py-2.5 text-sm text-on-surface hover:bg-surface-container transition-colors" href="<?= BASE_URL ?>/admin/dashboard.php"><span class="material-symbols-outlined text-lg text-secondary-fixed-dim">dashboard</span> Admin</a>
</nav>
<?php endif; ?>
</div>
<div class="border-t border-outline-variant/20 p-5 shrink-0 pb-[max(1.25rem,env(safe-area-inset-bottom))]">
<?php if (isset($_SESSION['user_id'])): ?>
<a class="flex items-center justify-center gap-2 w-full py-3 rounded-xl border border-error/30 text-error font-label-lg hover:bg-error-container transition-colors" href="<?= BASE_URL ?>/logout.php"><span class="material-symbols-outlined text-lg">logout</span> Sign Out</a>
<?php else: ?>
<a class="flex items-center justify-center gap-2 w-full py-3 rounded-xl bg-primary text-on-primary font-label-lg hover:bg-primary-container transition-colors" href="<?= BASE_URL ?>/login.php"><span class="material-symbols-outlined text-lg">person</span> Login</a>
<?php endif; ?>
</div>
</div>

<script>
document.addEventListener('click', function(e) {
    const sw = document.getElementById('lang-switcher');
    if (sw && !sw.contains(e.target)) { const menu = document.getElementById('lang-menu'); if (menu) menu.classList.add('hidden'); }
    const pm = document.getElementById('profile-menu');
    if (pm && !pm.closest('.relative')?.contains(e.target)) { pm.classList.add('hidden'); }
});
document.getElementById('mobile-menu-btn')?.addEventListener('click', function() {
    const menu = document.getElementById('mobile-menu');
    if (!menu) return;
    const willOpen = menu.classList.contains('hidden');
    menu.classList.toggle('hidden', !willOpen);
    menu.classList.toggle('flex', willOpen);
    document.body.style.overflow = willOpen ? 'hidden' : '';
    this.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
});
document.getElementById('mobile-menu')?.addEventListener('click', function(e) {
    if (e.target === this || e.target.closest('.close-mobile-btn')) {
        this.classList.add('hidden');
        this.classList.remove('flex');
        document.body.style.overflow = '';
        document.getElementById('mobile-menu-btn')?.setAttribute('aria-expanded', 'false');
    }
});
document.addEventListener('keydown', function(e) {
    if (e.key !== 'Escape') return;
    const menu = document.getElementById('mobile-menu');
    if (!menu || menu.classList.contains('hidden')) return;
    menu.classList.add('hidden');
    menu.classList.remove('flex');
    document.body.style.overflow = '';
    document.getElementById('mobile-menu-btn')?.setAttribute('aria-expanded', 'false');
});
</script>
<main>
