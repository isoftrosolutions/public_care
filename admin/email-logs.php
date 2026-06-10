<?php
require_once __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$db = getDB();
$page_title = 'Email Logs';
$active_page = 'email-logs';

$type_filter = $_GET['type'] ?? '';
$status_filter = $_GET['status'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 30;

$where = [];
$params = [];
$types = '';

if ($type_filter) {
    $where[] = "rl.reminder_type = ?";
    $params[] = $type_filter;
    $types .= 's';
}
if ($status_filter) {
    $where[] = "rl.status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$count_sql = "SELECT COUNT(*) FROM reminder_logs rl $where_sql";
$count_stmt = $db->prepare($count_sql);
if ($params) $count_stmt->bind_param($types, ...$params);
$count_stmt->execute();
$total_rows = $count_stmt->get_result()->fetch_row()[0];
$total_pages = max(1, ceil($total_rows / $per_page));
$offset = ($page - 1) * $per_page;

$data_sql = "SELECT rl.*, u.full_name, u.email FROM reminder_logs rl LEFT JOIN users u ON rl.user_id = u.id $where_sql ORDER BY rl.created_at DESC LIMIT ? OFFSET ?";
$data_stmt = $db->prepare($data_sql);
$all_params = array_merge($params, [$per_page, $offset]);
$all_types = $types . 'ii';
if ($all_types !== 'ii') {
    $data_stmt->bind_param($all_types, ...$all_params);
} else {
    $data_stmt->bind_param('ii', $per_page, $offset);
}
$data_stmt->execute();
$logs = $data_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<?php require_once __DIR__ . '/includes/head.php'; ?>
<body class="bg-surface font-body-md text-on-surface flex min-h-screen">
<?php require_once __DIR__ . '/includes/sidebar.php'; ?>
<main class="flex-grow ml-64 p-margin-desktop bg-surface min-h-screen">
<header class="flex justify-between items-end mb-8">
<div>
<h2 class="text-display-lg font-display-lg text-primary">Email Logs</h2>
<p class="text-body-lg text-on-surface-variant mt-2">Monitor automated health reminder emails sent to patients.</p>
</div>
</header>

<section class="grid grid-cols-1 md:grid-cols-4 gap-gutter mb-8">
<?php
$total_sent = $db->query("SELECT COUNT(*) FROM reminder_logs WHERE status = 'sent'")->fetch_row()[0];
$total_failed = $db->query("SELECT COUNT(*) FROM reminder_logs WHERE status = 'failed'")->fetch_row()[0];
$total_all = $db->query("SELECT COUNT(*) FROM reminder_logs")->fetch_row()[0];
$distinct_users = $db->query("SELECT COUNT(DISTINCT user_id) FROM reminder_logs")->fetch_row()[0];
?>
<div class="bento-card bg-surface-container-lowest p-5 rounded-xl border border-outline-variant">
<span class="text-label-sm text-on-surface-variant uppercase tracking-widest">Total Sent</span>
<p class="text-headline-md text-primary mt-1"><?= $total_sent ?></p>
</div>
<div class="bento-card bg-surface-container-lowest p-5 rounded-xl border border-outline-variant">
<span class="text-label-sm text-on-surface-variant uppercase tracking-widest">Failed</span>
<p class="text-headline-md text-on-error-container mt-1"><?= $total_failed ?></p>
</div>
<div class="bento-card bg-surface-container-lowest p-5 rounded-xl border border-outline-variant">
<span class="text-label-sm text-on-surface-variant uppercase tracking-widest">Total Logs</span>
<p class="text-headline-md text-on-surface mt-1"><?= $total_all ?></p>
</div>
<div class="bento-card bg-surface-container-lowest p-5 rounded-xl border border-outline-variant">
<span class="text-label-sm text-on-surface-variant uppercase tracking-widest">Active Users</span>
<p class="text-headline-md text-on-surface mt-1"><?= $distinct_users ?></p>
</div>
</section>

<section class="bg-surface-container-lowest rounded-xl border border-outline-variant overflow-hidden">
<div class="px-6 py-4 border-b border-outline-variant flex flex-wrap justify-between items-center gap-4">
<form method="GET" class="flex gap-4 flex-wrap items-center">
<select name="type" class="bg-surface border border-outline-variant text-label-sm rounded-lg focus:ring-primary px-3 py-2" onchange="this.form.submit()">
<option value="">All Types</option>
<option value="medicine" <?= $type_filter === 'medicine' ? 'selected' : '' ?>>Medicine</option>
<option value="water" <?= $type_filter === 'water' ? 'selected' : '' ?>>Water</option>
<option value="yoga" <?= $type_filter === 'yoga' ? 'selected' : '' ?>>Yoga</option>
<option value="diet" <?= $type_filter === 'diet' ? 'selected' : '' ?>>Diet</option>
</select>
<select name="status" class="bg-surface border border-outline-variant text-label-sm rounded-lg focus:ring-primary px-3 py-2" onchange="this.form.submit()">
<option value="">All Status</option>
<option value="sent" <?= $status_filter === 'sent' ? 'selected' : '' ?>>Sent</option>
<option value="failed" <?= $status_filter === 'failed' ? 'selected' : '' ?>>Failed</option>
</select>
<?php if ($type_filter || $status_filter): ?>
<a href="email-logs.php" class="text-label-sm text-on-surface-variant hover:underline">Clear</a>
<?php endif; ?>
</form>
</div>
<table class="w-full text-left">
<thead>
<tr class="bg-surface-container-low text-on-surface-variant">
<th class="px-6 py-4 text-label-sm uppercase tracking-wider">User</th>
<th class="px-6 py-4 text-label-sm uppercase tracking-wider">Type</th>
<th class="px-6 py-4 text-label-sm uppercase tracking-wider">Status</th>
<th class="px-6 py-4 text-label-sm uppercase tracking-wider">Sent At</th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant">
<?php foreach ($logs as $log): ?>
<?php
$status_class = $log['status'] === 'sent' ? 'bg-primary-fixed text-on-primary-fixed' : 'bg-error-container text-on-error-container';
$type_icons = ['medicine' => '💊', 'water' => '💧', 'yoga' => '🧘', 'diet' => '🥗'];
?>
<tr>
<td class="px-6 py-4">
<div>
<p class="text-body-md font-bold text-on-surface"><?= htmlspecialchars($log['full_name'] ?? '—') ?></p>
<p class="text-label-sm text-on-surface-variant"><?= htmlspecialchars($log['email'] ?? '') ?></p>
</div>
</td>
<td class="px-6 py-4 text-body-md"><?= ($type_icons[$log['reminder_type']] ?? '') . ' ' . htmlspecialchars(ucfirst($log['reminder_type'])) ?></td>
<td class="px-6 py-4"><span class="px-3 py-1 rounded-full text-label-sm <?= $status_class ?>"><?= htmlspecialchars(ucfirst($log['status'])) ?></span></td>
<td class="px-6 py-4 text-body-md text-on-surface-variant"><?= date('M d, Y · h:i A', strtotime($log['created_at'])) ?></td>
</tr>
<?php endforeach; ?>
<?php if (empty($logs)): ?>
<tr><td colspan="4" class="px-6 py-8 text-center text-on-surface-variant">No email logs found.</td></tr>
<?php endif; ?>
</tbody>
</table>
<div class="px-6 py-4 border-t border-outline-variant flex justify-between items-center text-label-sm text-on-surface-variant">
<span>Showing <?= count($logs) ?> of <?= $total_rows ?></span>
<div class="flex gap-2">
<?php if ($page > 1): ?><a class="px-3 py-1 rounded bg-surface-container-high hover:bg-surface-container-highest transition-colors" href="?page=<?= $page - 1 ?>&type=<?= urlencode($type_filter) ?>&status=<?= urlencode($status_filter) ?>">Previous</a><?php endif; ?>
<span class="px-3 py-1 rounded bg-primary text-on-primary"><?= $page ?></span>
<?php if ($page < $total_pages): ?><a class="px-3 py-1 rounded bg-surface-container-high hover:bg-surface-container-highest transition-colors" href="?page=<?= $page + 1 ?>&type=<?= urlencode($type_filter) ?>&status=<?= urlencode($status_filter) ?>">Next</a><?php endif; ?>
</div>
</div>
</section>
</main>
</body>
</html>
