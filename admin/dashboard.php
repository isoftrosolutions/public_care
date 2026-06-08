<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ' . (defined('BASE_URL') ? BASE_URL : '/public_care_ayurveda') . '/login.php');
    exit;
}

$db = getDB();
$total_revenue = $db->query("SELECT COALESCE(SUM(total), 0) FROM orders WHERE payment_status = 'paid'")->fetch_row()[0];
$total_orders = $db->query("SELECT COUNT(*) FROM orders")->fetch_row()[0];
$active_consults = $db->query("SELECT COUNT(*) FROM appointments WHERE status = 'confirmed'")->fetch_row()[0];
$total_users = $db->query("SELECT COUNT(*) FROM users")->fetch_row()[0];
$recent_orders = $db->query("SELECT o.*, u.full_name FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Admin Portal | Public Care Ayurveda</title>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&family=Source+Serif+4:opsz,wght@8..60,400;8..60,600;8..60,700&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script>
tailwind.config = {
  darkMode: "class",
  theme: {
    extend: {
      colors: { primary: "#012d1d", "on-primary": "#ffffff", "primary-container": "#1b4332", "on-primary-container": "#86af99", "primary-fixed": "#c1ecd4", "primary-fixed-dim": "#a5d0b9", secondary: "#735c00", "on-secondary": "#ffffff", "secondary-container": "#fed65b", "secondary-fixed": "#ffe088", "secondary-fixed-dim": "#e9c349", "tertiary-fixed-dim": "#a4d1b4", background: "#f4fafd", surface: "#f4fafd", "surface-dim": "#d4dbdd", "surface-bright": "#f4fafd", "surface-container-lowest": "#ffffff", "surface-container-low": "#eef5f7", "surface-container": "#e8eff1", "surface-container-high": "#e2e9ec", "surface-container-highest": "#dde4e6", "on-surface": "#161d1f", "on-surface-variant": "#414844", "surface-variant": "#dde4e6", outline: "#717973", "outline-variant": "#c1c8c2", error: "#ba1a1a", "on-error": "#ffffff", "error-container": "#ffdad6", "on-error-container": "#93000a", tertiary: "#002d1a", "on-tertiary": "#ffffff", "tertiary-container": "#1a432e", "on-tertiary-container": "#84b095", "tertiary-fixed": "#c0edd0", "on-secondary-container": "#745c00", "on-secondary-fixed-variant": "#574500", "on-primary-fixed": "#002114", "on-primary-fixed-variant": "#274e3d", "on-tertiary-fixed": "#002112", "on-tertiary-fixed-variant": "#264f39", "inverse-surface": "#2b3234", "inverse-on-surface": "#ebf2f4", "inverse-primary": "#a5d0b9", "surface-tint": "#3f6653", "on-background": "#161d1f" },
      borderRadius: { DEFAULT: "0.25rem", lg: "0.5rem", xl: "0.75rem", full: "9999px" },
      spacing: { base: "8px", gutter: "24px", "section-gap": "80px", "margin-mobile": "16px", "margin-desktop": "48px", "container-max": "1200px" },
      fontFamily: { "display-lg": ["Source Serif 4"], "headline-lg": ["Source Serif 4"], "headline-md": ["Source Serif 4"], "body-lg": ["Manrope"], "body-md": ["Manrope"], "label-md": ["Manrope"], "label-sm": ["Manrope"] },
      fontSize: { "display-lg": ["48px", { lineHeight: "56px", letterSpacing: "-0.02em", fontWeight: "700" }], "headline-lg": ["32px", { lineHeight: "40px", fontWeight: "600" }], "headline-md": ["24px", { lineHeight: "32px", fontWeight: "600" }], "body-lg": ["18px", { lineHeight: "28px", fontWeight: "400" }], "body-md": ["16px", { lineHeight: "24px", fontWeight: "400" }], "label-md": ["14px", { lineHeight: "20px", letterSpacing: "0.05em", fontWeight: "600" }], "label-sm": ["12px", { lineHeight: "16px", fontWeight: "500" }] }
    }
  }
};
</script>
<style>
.material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
.bento-card { transition: transform 0.2s ease, box-shadow 0.2s ease; }
.bento-card:hover { transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05); }
</style>
</head>
<body class="bg-surface font-body-md text-on-surface flex min-h-screen">

<aside class="h-screen w-64 fixed left-0 top-0 bg-surface-container-low border-r border-outline-variant flex flex-col py-base z-50">
<div class="px-6 py-8 mb-6">
<h1 class="text-headline-md font-headline-md font-bold text-primary">Admin Portal</h1>
<p class="text-label-sm text-on-surface-variant opacity-70">Operations Management</p>
<a href="../index.php" class="mt-4 text-xs text-primary flex items-center gap-1 hover:underline"><span class="material-symbols-outlined text-sm">arrow_back</span> Back to Public Site</a>
</div>
<nav class="flex-grow space-y-1">
<a class="bg-secondary-container text-on-secondary-container rounded-lg px-4 py-3 mx-2 flex items-center gap-3 scale-95" href="dashboard.php"><span class="material-symbols-outlined">dashboard</span><span class="text-label-md">Dashboard</span></a>
<a class="text-on-surface-variant px-4 py-3 mx-2 flex items-center gap-3 hover:bg-surface-container-highest transition-all" href="appointments.php"><span class="material-symbols-outlined">calendar_month</span><span class="text-label-md">Appointments</span></a>
<a class="text-on-surface-variant px-4 py-3 mx-2 flex items-center gap-3 hover:bg-surface-container-highest transition-all" href="orders.php"><span class="material-symbols-outlined">shopping_bag</span><span class="text-label-md">Orders</span></a>
</nav>
<div class="mt-auto border-t border-outline-variant pt-4 pb-4">
<a class="text-on-surface-variant px-4 py-3 mx-2 flex items-center gap-3 hover:bg-surface-container-highest transition-all" href="../logout.php"><span class="material-symbols-outlined">logout</span><span class="text-label-md">Logout</span></a>
</div>
</aside>

<main class="flex-grow ml-64 p-margin-desktop bg-surface min-h-screen">
<header class="flex justify-between items-end mb-12">
<div>
<h2 class="text-display-lg font-display-lg text-primary">Performance Dashboard</h2>
<p class="text-body-lg text-on-surface-variant mt-2">Welcome back, Admin. Here is the operational pulse for today.</p>
</div>
</header>

<section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-gutter mb-section-gap">
<div class="bento-card bg-surface-container-lowest p-6 rounded-xl border border-outline-variant">
<div class="flex justify-between items-start mb-4"><span class="material-symbols-outlined text-secondary bg-secondary-fixed p-2 rounded-lg">payments</span><span class="text-label-sm text-primary flex items-center gap-1">+12.5% <span class="material-symbols-outlined text-xs">trending_up</span></span></div>
<h3 class="text-label-sm text-on-surface-variant uppercase tracking-widest">Total Revenue</h3>
<p class="text-headline-lg text-on-surface mt-1">₹<?= number_format($total_revenue, 2) ?></p>
</div>
<div class="bento-card bg-surface-container-lowest p-6 rounded-xl border border-outline-variant">
<div class="flex justify-between items-start mb-4"><span class="material-symbols-outlined text-primary bg-primary-fixed p-2 rounded-lg">shopping_cart</span><span class="text-label-sm text-primary flex items-center gap-1">+8.2% <span class="material-symbols-outlined text-xs">trending_up</span></span></div>
<h3 class="text-label-sm text-on-surface-variant uppercase tracking-widest">Total Orders</h3>
<p class="text-headline-lg text-on-surface mt-1"><?= number_format($total_orders) ?></p>
</div>
<div class="bento-card bg-surface-container-lowest p-6 rounded-xl border border-outline-variant">
<div class="flex justify-between items-start mb-4"><span class="material-symbols-outlined text-tertiary-fixed-dim bg-tertiary-container p-2 rounded-lg">medical_information</span><span class="text-label-sm text-on-error-container flex items-center gap-1">-2.1% <span class="material-symbols-outlined text-xs">trending_down</span></span></div>
<h3 class="text-label-sm text-on-surface-variant uppercase tracking-widest">Active Consults</h3>
<p class="text-headline-lg text-on-surface mt-1"><?= $active_consults ?></p>
</div>
<div class="bento-card bg-surface-container-lowest p-6 rounded-xl border border-outline-variant">
<div class="flex justify-between items-start mb-4"><span class="material-symbols-outlined text-on-primary-container bg-primary-container p-2 rounded-lg">person_celebrate</span><span class="text-label-sm text-primary flex items-center gap-1">+24% <span class="material-symbols-outlined text-xs">trending_up</span></span></div>
<h3 class="text-label-sm text-on-surface-variant uppercase tracking-widest">Total Users</h3>
<p class="text-headline-lg text-on-surface mt-1"><?= number_format($total_users) ?></p>
</div>
</section>

<section class="bg-surface-container-lowest rounded-xl border border-outline-variant overflow-hidden">
<div class="px-8 py-6 border-b border-outline-variant flex justify-between items-center">
<h4 class="text-headline-md text-primary">Recent Orders & Activity</h4>
<a href="orders.php" class="text-label-md text-primary hover:underline">View All Records</a>
</div>
<table class="w-full text-left">
<thead>
<tr class="bg-surface-container-low text-on-surface-variant">
<th class="px-8 py-4 text-label-sm uppercase tracking-wider">Customer</th>
<th class="px-8 py-4 text-label-sm uppercase tracking-wider">Order #</th>
<th class="px-8 py-4 text-label-sm uppercase tracking-wider">Total</th>
<th class="px-8 py-4 text-label-sm uppercase tracking-wider">Date</th>
<th class="px-8 py-4 text-label-sm uppercase tracking-wider">Status</th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant">
<?php foreach ($recent_orders as $o): ?>
<tr>
<td class="px-8 py-5"><p class="text-body-md font-bold text-on-surface"><?= htmlspecialchars($o['full_name']) ?></p></td>
<td class="px-8 py-5 text-body-md"><?= htmlspecialchars($o['order_number']) ?></td>
<td class="px-8 py-5 text-body-md font-bold">₹<?= number_format($o['total'], 2) ?></td>
<td class="px-8 py-5 text-body-md text-on-surface-variant"><?= date('M d, Y', strtotime($o['created_at'])) ?></td>
<td class="px-8 py-5"><span class="px-3 py-1 rounded-full text-label-sm bg-<?= $o['status'] === 'shipped' ? 'secondary-container text-on-secondary-container' : ($o['status'] === 'delivered' ? 'primary-fixed text-on-primary-fixed' : ($o['status'] === 'cancelled' ? 'error-container text-on-error-container' : 'surface-container-high text-on-surface')) ?>"><?= ucfirst($o['status']) ?></span></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</section>
</main>
</body>
</html>
