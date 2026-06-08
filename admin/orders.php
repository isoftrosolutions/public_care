<?php
require_once __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$db = getDB();
$page_title = 'Orders';
$active_page = 'orders';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'], $_POST['order_id'], $_POST['status'], $_POST['csrf_token']) && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    $stmt = $db->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param('si', $_POST['status'], $_POST['order_id']);
    $stmt->execute();
    header('Location: orders.php');
    exit;
}

$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 20;
$search = trim($_GET['search'] ?? '');
$status_filter = $_GET['status'] ?? '';

$where_conds = [];
$bind_params = [];
$bind_types = '';

if ($search) {
    $where_conds[] = "(o.order_number LIKE ? OR u.full_name LIKE ?)";
    $bind_params[] = "%$search%";
    $bind_params[] = "%$search%";
    $bind_types .= 'ss';
}
if ($status_filter) {
    $where_conds[] = "o.status = ?";
    $bind_params[] = $status_filter;
    $bind_types .= 's';
}
$where_sql = $where_conds ? 'WHERE ' . implode(' AND ', $where_conds) : '';

$grand_total = $db->query("SELECT COUNT(*) FROM orders")->fetch_row()[0];
$filtered_total = $db->query("SELECT COUNT(*) FROM orders o JOIN users u ON o.user_id = u.id $where_sql")->fetch_row()[0];
$total_pages = max(1, ceil($filtered_total / $per_page));
$offset = ($page - 1) * $per_page;

$shipped = $db->query("SELECT COUNT(*) FROM orders WHERE status = 'shipped'")->fetch_row()[0];
$processing = $db->query("SELECT COUNT(*) FROM orders WHERE status = 'processing'")->fetch_row()[0];
$pending_count = $db->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetch_row()[0];

$query = "SELECT o.*, u.full_name FROM orders o JOIN users u ON o.user_id = u.id $where_sql ORDER BY o.created_at DESC LIMIT ? OFFSET ?";
$stmt = $db->prepare($query);
if ($bind_params) {
    $bind_params[] = $per_page;
    $bind_params[] = $offset;
    $bind_types .= 'ii';
    $stmt->bind_param($bind_types, ...$bind_params);
} else {
    $stmt->bind_param('ii', $per_page, $offset);
}
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<?php require_once __DIR__ . '/includes/head.php'; ?>
<body class="bg-surface font-body-md text-on-surface flex min-h-screen">
<?php require_once __DIR__ . '/includes/sidebar.php'; ?>
<main class="flex-grow ml-64 p-margin-desktop bg-surface min-h-screen">
<header class="flex justify-between items-end mb-8">
<div>
<h2 class="text-display-lg font-display-lg text-primary">Orders</h2>
<p class="text-body-lg text-on-surface-variant mt-2">Track and manage all customer orders.</p>
</div>
</header>

<section class="grid grid-cols-1 md:grid-cols-4 gap-gutter mb-8">
<div class="bento-card bg-surface-container-lowest p-5 rounded-xl border border-outline-variant">
<span class="text-label-sm text-on-surface-variant uppercase tracking-widest">Total Orders</span>
<p class="text-headline-md text-on-surface mt-1"><?= number_format($grand_total) ?></p>
</div>
<div class="bento-card bg-surface-container-lowest p-5 rounded-xl border border-outline-variant">
<span class="text-label-sm text-on-surface-variant uppercase tracking-widest">Shipped</span>
<p class="text-headline-md text-primary mt-1"><?= $shipped ?></p>
</div>
<div class="bento-card bg-surface-container-lowest p-5 rounded-xl border border-outline-variant">
<span class="text-label-sm text-on-surface-variant uppercase tracking-widest">Processing</span>
<p class="text-headline-md text-secondary mt-1"><?= $processing ?></p>
</div>
<div class="bento-card bg-surface-container-lowest p-5 rounded-xl border border-outline-variant">
<span class="text-label-sm text-on-surface-variant uppercase tracking-widest">Pending</span>
<p class="text-headline-md text-on-error-container mt-1"><?= $pending_count ?></p>
</div>
</section>

<section class="bg-surface-container-lowest rounded-xl border border-outline-variant overflow-hidden">
<div class="px-6 py-4 border-b border-outline-variant flex flex-wrap justify-between items-center gap-4">
<form method="GET" class="flex gap-4 flex-wrap items-center">
<select name="status" class="bg-surface border border-outline-variant text-label-sm rounded-lg focus:ring-primary px-3 py-2" onchange="this.form.submit()">
<option value="">All Status</option>
<option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
<option value="processing" <?= $status_filter === 'processing' ? 'selected' : '' ?>>Processing</option>
<option value="shipped" <?= $status_filter === 'shipped' ? 'selected' : '' ?>>Shipped</option>
<option value="delivered" <?= $status_filter === 'delivered' ? 'selected' : '' ?>>Delivered</option>
<option value="cancelled" <?= $status_filter === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
</select>
<div class="relative">
<span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-sm">search</span>
<input class="bg-surface border border-outline-variant text-label-sm rounded-lg focus:ring-primary pl-9 py-2 w-56" name="search" placeholder="Search order ID or customer..." value="<?= htmlspecialchars($search) ?>" type="text"/>
</div>
<button class="text-label-sm text-primary hover:underline" type="submit">Search</button>
<?php if ($search || $status_filter): ?>
<a href="orders.php" class="text-label-sm text-on-surface-variant hover:underline">Clear</a>
<?php endif; ?>
</form>
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
$name = $o['full_name'] ?? '';
$parts = explode(' ', $name);
$initials = strtoupper(substr($parts[0] ?? '', 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
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
<td class="px-6 py-4"><span class="px-3 py-1 rounded-full text-label-sm <?= $status_class ?>"><?= htmlspecialchars(ucfirst($o['status'])) ?></span></td>
<td class="px-6 py-4">
<form method="POST" class="flex items-center gap-2" onsubmit="return confirm('Update status?')">
<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
<input type="hidden" name="order_id" value="<?= $o['id'] ?>">
<select name="status" class="bg-surface border border-outline-variant text-label-sm rounded px-2 py-1">
<option value="pending" <?= $o['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
<option value="processing" <?= $o['status'] === 'processing' ? 'selected' : '' ?>>Processing</option>
<option value="shipped" <?= $o['status'] === 'shipped' ? 'selected' : '' ?>>Shipped</option>
<option value="delivered" <?= $o['status'] === 'delivered' ? 'selected' : '' ?>>Delivered</option>
<option value="cancelled" <?= $o['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
</select>
<button class="text-label-sm bg-primary text-on-primary px-3 py-1 rounded hover:opacity-90" type="submit" name="update_status" value="1">Update</button>
</form>
</td>
</tr>
<?php endforeach; ?>
<?php if (empty($orders)): ?>
<tr><td colspan="6" class="px-6 py-8 text-center text-on-surface-variant">No orders found.</td></tr>
<?php endif; ?>
</tbody>
</table>
<div class="px-6 py-4 border-t border-outline-variant flex justify-between items-center text-label-sm text-on-surface-variant">
<span>Showing <?= count($orders) ?> of <?= number_format($filtered_total) ?></span>
<div class="flex gap-2">
<?php if ($page > 1): ?><a class="px-3 py-1 rounded bg-surface-container-high hover:bg-surface-container-highest transition-colors" href="?page=<?= $page - 1 ?>&status=<?= urlencode($status_filter) ?>&search=<?= urlencode($search) ?>">Previous</a><?php endif; ?>
<span class="px-3 py-1 rounded bg-primary text-on-primary"><?= $page ?></span>
<?php if ($page < $total_pages): ?><a class="px-3 py-1 rounded bg-surface-container-high hover:bg-surface-container-highest transition-colors" href="?page=<?= $page + 1 ?>&status=<?= urlencode($status_filter) ?>&search=<?= urlencode($search) ?>">Next</a><?php endif; ?>
</div>
</div>
</section>
</main>
</body>
</html>
