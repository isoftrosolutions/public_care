<?php
require_once __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$site_title = 'Orders';
require_once __DIR__ . '/../includes/header.php';

$db = getDB();
$total_orders = $db->query("SELECT COUNT(*) FROM orders")->fetch_row()[0];
$shipped = $db->query("SELECT COUNT(*) FROM orders WHERE status = 'shipped'")->fetch_row()[0];
$processing = $db->query("SELECT COUNT(*) FROM orders WHERE status = 'processing'")->fetch_row()[0];
$pending = $db->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetch_row()[0];
$orders = $db->query("SELECT o.*, u.full_name FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 50")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Orders | Admin Portal</title>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&family=Source+Serif+4:ital,opsz,wght@0,8..60,400;0,8..60,600;0,8..60,700;1,8..60,400&display=swap" rel="stylesheet"/>
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
<a href="<?= BASE_URL ?>/index.php" class="mt-4 text-xs text-primary flex items-center gap-1 hover:underline"><span class="material-symbols-outlined text-sm">arrow_back</span> Back to Public Site</a>
</div>
<nav class="flex-grow space-y-1">
<a class="text-on-surface-variant px-4 py-3 mx-2 flex items-center gap-3 hover:bg-surface-container-highest transition-all" href="dashboard.php"><span class="material-symbols-outlined">dashboard</span><span class="text-label-md">Dashboard</span></a>
<a class="text-on-surface-variant px-4 py-3 mx-2 flex items-center gap-3 hover:bg-surface-container-highest transition-all" href="appointments.php"><span class="material-symbols-outlined">calendar_month</span><span class="text-label-md">Appointments</span></a>
<a class="bg-secondary-container text-on-secondary-container rounded-lg px-4 py-3 mx-2 flex items-center gap-3 scale-95" href="orders.php"><span class="material-symbols-outlined">shopping_bag</span><span class="text-label-md">Orders</span></a>
</nav>
<div class="mt-auto border-t border-outline-variant pt-4 pb-4">
<a class="text-on-surface-variant px-4 py-3 mx-2 flex items-center gap-3 hover:bg-surface-container-highest transition-all" href="<?= BASE_URL ?>/logout.php"><span class="material-symbols-outlined">logout</span><span class="text-label-md">Logout</span></a>
</div>
</aside>
<main class="flex-grow ml-64 p-margin-desktop bg-surface min-h-screen">
<header class="flex justify-between items-end mb-8">
<div>
<h2 class="text-display-lg font-display-lg text-primary">Orders</h2>
<p class="text-body-lg text-on-surface-variant mt-2">Track and manage all customer orders.</p>
</div>
<div class="flex gap-4">
<button class="flex items-center gap-2 bg-surface-container-high text-on-surface px-6 py-3 rounded-full text-label-md font-label-md border border-outline-variant hover:bg-surface-container-highest transition-colors">
<span class="material-symbols-outlined">file_download</span> Export
</button>
</div>
</header>
<section class="grid grid-cols-1 md:grid-cols-4 gap-gutter mb-8">
<div class="bento-card bg-surface-container-lowest p-5 rounded-xl border border-outline-variant">
<span class="text-label-sm text-on-surface-variant uppercase tracking-widest">Total Orders</span>
<p class="text-headline-md text-headline-md text-on-surface mt-1"><?= number_format($total_orders) ?></p>
</div>
<div class="bento-card bg-surface-container-lowest p-5 rounded-xl border border-outline-variant">
<span class="text-label-sm text-on-surface-variant uppercase tracking-widest">Shipped</span>
<p class="text-headline-md text-headline-md text-primary mt-1"><?= $shipped ?></p>
</div>
<div class="bento-card bg-surface-container-lowest p-5 rounded-xl border border-outline-variant">
<span class="text-label-sm text-on-surface-variant uppercase tracking-widest">Processing</span>
<p class="text-headline-md text-headline-md text-secondary mt-1"><?= $processing ?></p>
</div>
<div class="bento-card bg-surface-container-lowest p-5 rounded-xl border border-outline-variant">
<span class="text-label-sm text-on-surface-variant uppercase tracking-widest">Pending</span>
<p class="text-headline-md text-headline-md text-on-error-container mt-1"><?= $pending ?></p>
</div>
</section>
<section class="bg-surface-container-lowest rounded-xl border border-outline-variant overflow-hidden">
<div class="px-6 py-4 border-b border-outline-variant flex justify-between items-center">
<div class="flex gap-4">
<select class="bg-surface border-none text-label-sm rounded-lg focus:ring-primary px-3 py-2">
<option>All Status</option>
<option>Pending</option>
<option>Processing</option>
<option>Shipped</option>
<option>Delivered</option>
<option>Cancelled</option>
</select>
<select class="bg-surface border-none text-label-sm rounded-lg focus:ring-primary px-3 py-2">
<option>Last 30 Days</option>
<option>Last 90 Days</option>
<option>This Year</option>
<option>All Time</option>
</select>
</div>
<div class="relative">
<span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-sm">search</span>
<input class="bg-surface border-none text-label-sm rounded-lg focus:ring-primary pl-9 py-2 w-56" placeholder="Search order ID..." type="text"/>
</div>
</div>
<table class="w-full text-left">
<thead>
<tr class="bg-surface-container-low text-on-surface-variant">
<th class="px-6 py-4 text-label-sm uppercase tracking-wider">Order ID</th>
<th class="px-6 py-4 text-label-sm uppercase tracking-wider">Customer</th>
<th class="px-6 py-4 text-label-sm uppercase tracking-wider">Total</th>
<th class="px-6 py-4 text-label-sm uppercase tracking-wider">Date</th>
<th class="px-6 py-4 text-label-sm uppercase tracking-wider">Status</th>
<th class="px-6 py-4 text-label-sm uppercase tracking-wider">Actions</th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant">
<?php foreach ($orders as $o): ?>
<?php
$initials = '';
$name = $o['full_name'] ?? '';
if ($name) { $parts = explode(' ', $name); $initials = strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : '')); }
$status_class = match($o['status']) {
    'shipped' => 'bg-secondary-container text-on-secondary-container',
    'delivered' => 'bg-primary-fixed text-on-primary-fixed',
    'cancelled' => 'bg-error-container text-on-error-container',
    'pending' => 'bg-surface-container-high text-on-surface',
    default => 'bg-surface-container-high text-on-surface'
};
?>
<tr>
<td class="px-6 py-4 text-label-sm font-bold text-primary"><?= htmlspecialchars($o['order_number']) ?></td>
<td class="px-6 py-4">
<div class="flex items-center gap-3">
<div class="w-9 h-9 rounded-full bg-primary-fixed flex items-center justify-center text-primary text-sm font-bold"><?= htmlspecialchars($initials) ?></div>
<div><p class="text-body-md font-bold text-on-surface"><?= htmlspecialchars($name) ?></p></div>
</div>
</td>
<td class="px-6 py-4 text-body-md font-bold">₹<?= number_format($o['total'], 2) ?></td>
<td class="px-6 py-4 text-body-md text-on-surface-variant"><?= date('M d, Y', strtotime($o['created_at'])) ?></td>
<td class="px-6 py-4"><span class="px-3 py-1 rounded-full text-label-sm <?= $status_class ?>"><?= ucfirst($o['status']) ?></span></td>
<td class="px-6 py-4"><button class="material-symbols-outlined text-on-surface-variant hover:text-primary transition-colors">more_vert</button></td>
</tr>
<?php endforeach; ?>
<?php if (empty($orders)): ?>
<tr><td colspan="6" class="px-6 py-8 text-center text-on-surface-variant">No orders found.</td></tr>
<?php endif; ?>
</tbody>
</table>
<div class="px-6 py-4 border-t border-outline-variant flex justify-between items-center text-label-sm text-on-surface-variant">
<span>Showing <?= count($orders) ?> of <?= number_format($total_orders) ?></span>
<div class="flex gap-2">
<button class="px-3 py-1 rounded bg-surface-container-high hover:bg-surface-container-highest transition-colors">Previous</button>
<button class="px-3 py-1 rounded bg-primary text-on-primary">1</button>
<button class="px-3 py-1 rounded bg-surface-container-high hover:bg-surface-container-highest transition-colors">Next</button>
</div>
</div>
</section>
</main>
</body>
</html>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
