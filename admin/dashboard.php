<?php
require_once __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$db = getDB();
$page_title = 'Dashboard';
$active_page = 'dashboard';

$total_revenue = 0;
$total_orders = 0;
$active_consults = 0;
$total_users = 0;
$prev_revenue = 0;
$prev_orders = 0;
$prev_consults = 0;
$prev_users = 0;
$recent_orders = [];

try {
    $total_revenue = $db->query("SELECT COALESCE(SUM(total), 0) FROM orders WHERE payment_status = 'paid'")->fetch_row()[0];
    $total_orders = $db->query("SELECT COUNT(*) FROM orders")->fetch_row()[0];
    $active_consults = $db->query("SELECT COUNT(*) FROM appointments WHERE status = 'confirmed'")->fetch_row()[0];
    $total_users = $db->query("SELECT COUNT(*) FROM users")->fetch_row()[0];

    $prev_revenue = $db->query("SELECT COALESCE(SUM(total), 0) FROM orders WHERE payment_status = 'paid' AND created_at >= DATE_SUB(NOW(), INTERVAL 60 DAY) AND created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetch_row()[0];
    $prev_orders = $db->query("SELECT COUNT(*) FROM orders WHERE created_at >= DATE_SUB(NOW(), INTERVAL 60 DAY) AND created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetch_row()[0];
    $prev_consults = $db->query("SELECT COUNT(*) FROM appointments WHERE status = 'confirmed' AND created_at >= DATE_SUB(NOW(), INTERVAL 60 DAY) AND created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetch_row()[0];
    $prev_users = $db->query("SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 60 DAY) AND created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetch_row()[0];

    $recent_orders = $db->query("SELECT o.*, u.full_name FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    error_log('Dashboard error: ' . $e->getMessage());
}

function trend_pct($current, $previous) {
    if ($previous > 0) {
        return round((($current - $previous) / $previous) * 100, 1);
    }
    return $current > 0 ? 100 : 0;
}
$rev_trend = trend_pct($total_revenue, $prev_revenue);
$ord_trend = trend_pct($total_orders, $prev_orders);
$con_trend = trend_pct($active_consults, $prev_consults);
$usr_trend = trend_pct($total_users, $prev_users);
?>
<?php require_once __DIR__ . '/includes/head.php'; ?>
<body class="bg-surface font-body-md text-on-surface flex min-h-screen">
<?php require_once __DIR__ . '/includes/sidebar.php'; ?>
<main class="flex-grow ml-64 p-margin-desktop bg-surface min-h-screen">
<header class="flex justify-between items-end mb-12">
<div>
<h2 class="text-display-lg font-display-lg text-primary">Performance Dashboard</h2>
<p class="text-body-lg text-on-surface-variant mt-2">Welcome back, Admin. Here is the operational pulse for today.</p>
</div>
</header>

<section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-gutter mb-section-gap">
<div class="bento-card bg-surface-container-lowest p-6 rounded-xl border border-outline-variant">
<div class="flex justify-between items-start mb-4"><span class="material-symbols-outlined text-secondary bg-secondary-fixed p-2 rounded-lg">payments</span><span class="text-label-sm text-primary flex items-center gap-1"><?= $rev_trend >= 0 ? '+' : '' ?><?= $rev_trend ?>% <span class="material-symbols-outlined text-xs"><?= $rev_trend >= 0 ? 'trending_up' : 'trending_down' ?></span></span></div>
<h3 class="text-label-sm text-on-surface-variant uppercase tracking-widest">Total Revenue</h3>
<p class="text-headline-lg text-on-surface mt-1">₹<?= number_format($total_revenue, 2) ?></p>
</div>
<div class="bento-card bg-surface-container-lowest p-6 rounded-xl border border-outline-variant">
<div class="flex justify-between items-start mb-4"><span class="material-symbols-outlined text-primary bg-primary-fixed p-2 rounded-lg">shopping_cart</span><span class="text-label-sm text-primary flex items-center gap-1"><?= $ord_trend >= 0 ? '+' : '' ?><?= $ord_trend ?>% <span class="material-symbols-outlined text-xs"><?= $ord_trend >= 0 ? 'trending_up' : 'trending_down' ?></span></span></div>
<h3 class="text-label-sm text-on-surface-variant uppercase tracking-widest">Total Orders</h3>
<p class="text-headline-lg text-on-surface mt-1"><?= number_format($total_orders) ?></p>
</div>
<div class="bento-card bg-surface-container-lowest p-6 rounded-xl border border-outline-variant">
<div class="flex justify-between items-start mb-4"><span class="material-symbols-outlined text-tertiary-fixed-dim bg-tertiary-container p-2 rounded-lg">medical_information</span><span class="text-label-sm text-on-error-container flex items-center gap-1"><?= $con_trend >= 0 ? '+' : '' ?><?= $con_trend ?>% <span class="material-symbols-outlined text-xs"><?= $con_trend >= 0 ? 'trending_up' : 'trending_down' ?></span></span></div>
<h3 class="text-label-sm text-on-surface-variant uppercase tracking-widest">Active Consults</h3>
<p class="text-headline-lg text-on-surface mt-1"><?= $active_consults ?></p>
</div>
<div class="bento-card bg-surface-container-lowest p-6 rounded-xl border border-outline-variant">
<div class="flex justify-between items-start mb-4"><span class="material-symbols-outlined text-on-primary-container bg-primary-container p-2 rounded-lg">person_celebrate</span><span class="text-label-sm text-primary flex items-center gap-1"><?= $usr_trend >= 0 ? '+' : '' ?><?= $usr_trend ?>% <span class="material-symbols-outlined text-xs"><?= $usr_trend >= 0 ? 'trending_up' : 'trending_down' ?></span></span></div>
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
<td class="px-8 py-5"><span class="px-3 py-1 rounded-full text-label-sm <?= match($o['status']) { 'shipped' => 'bg-secondary-container text-on-secondary-container', 'delivered' => 'bg-primary-fixed text-on-primary-fixed', 'cancelled' => 'bg-error-container text-on-error-container', default => 'surface-container-high text-on-surface' } ?>"><?= htmlspecialchars(ucfirst($o['status'])) ?></span></td>
</tr>
<?php endforeach; ?>
<?php if (empty($recent_orders)): ?>
<tr><td colspan="5" class="px-8 py-8 text-center text-on-surface-variant">No orders yet.</td></tr>
<?php endif; ?>
</tbody>
</table>
</section>
</main>
</body>
</html>
