<?php
require_once __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$db = getDB();
$page_title = 'Appointments';
$active_page = 'appointments';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'], $_POST['appointment_id'], $_POST['status'], $_POST['csrf_token']) && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    $stmt = $db->prepare("UPDATE appointments SET status = ? WHERE id = ?");
    $stmt->bind_param('si', $_POST['status'], $_POST['appointment_id']);
    $stmt->execute();
    header('Location: appointments.php');
    exit;
}

$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 20;
$search = trim($_GET['search'] ?? '');
$status_filter = $_GET['status'] ?? '';

$where = [];
$params = [];
$types = '';
if ($search) {
    $where[] = "(u.full_name LIKE ? OR d.name LIKE ?)";
    $params[] = "%$search%"; $params[] = "%$search%";
    $types .= 'ss';
}
if ($status_filter) {
    $where[] = "a.status = ?";
    $params[] = $status_filter;
    $types .= 's';
}
$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$count_sql = "SELECT COUNT(*) FROM appointments a JOIN users u ON a.user_id = u.id LEFT JOIN doctors d ON a.doctor_id = d.id $where_sql";
$total_stmt = $db->prepare($count_sql);
if ($params) $total_stmt->bind_param($types, ...$params);
$total_stmt->execute();
$filtered_total = $total_stmt->get_result()->fetch_row()[0];
$total_pages = max(1, ceil($filtered_total / $per_page));
$offset = ($page - 1) * $per_page;

$grand_total = $db->query("SELECT COUNT(*) FROM appointments")->fetch_row()[0];
$confirmed = $db->query("SELECT COUNT(*) FROM appointments WHERE status = 'confirmed'")->fetch_row()[0];
$pending = $db->query("SELECT COUNT(*) FROM appointments WHERE status = 'pending'")->fetch_row()[0];
$cancelled = $db->query("SELECT COUNT(*) FROM appointments WHERE status = 'cancelled'")->fetch_row()[0];

$data_sql = "SELECT a.*, u.full_name, u.mobile, d.name AS doctor_name FROM appointments a JOIN users u ON a.user_id = u.id LEFT JOIN doctors d ON a.doctor_id = d.id $where_sql ORDER BY a.appointment_date DESC LIMIT ? OFFSET ?";
$data_stmt = $db->prepare($data_sql);
$all_params = array_merge($params, [$per_page, $offset]);
$all_types = $types . 'ii';
if ($all_types !== 'ii') {
    $data_stmt->bind_param($all_types, ...$all_params);
} else {
    $data_stmt->bind_param('ii', $per_page, $offset);
}
$data_stmt->execute();
$appointments = $data_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<?php require_once __DIR__ . '/includes/head.php'; ?>
<body class="bg-surface font-body-md text-on-surface flex min-h-screen">
<?php require_once __DIR__ . '/includes/sidebar.php'; ?>
<main class="flex-grow ml-64 p-margin-desktop bg-surface min-h-screen">
<header class="flex justify-between items-end mb-8">
<div>
<h2 class="text-display-lg font-display-lg text-primary">Appointments</h2>
<p class="text-body-lg text-on-surface-variant mt-2">Manage patient consultations and schedules.</p>
</div>
</header>

<section class="grid grid-cols-1 md:grid-cols-4 gap-gutter mb-8">
<div class="bento-card bg-surface-container-lowest p-5 rounded-xl border border-outline-variant">
<span class="text-label-sm text-on-surface-variant uppercase tracking-widest">Total</span>
<p class="text-headline-md text-on-surface mt-1"><?= $grand_total ?></p>
</div>
<div class="bento-card bg-surface-container-lowest p-5 rounded-xl border border-outline-variant">
<span class="text-label-sm text-on-surface-variant uppercase tracking-widest">Confirmed</span>
<p class="text-headline-md text-primary mt-1"><?= $confirmed ?></p>
</div>
<div class="bento-card bg-surface-container-lowest p-5 rounded-xl border border-outline-variant">
<span class="text-label-sm text-on-surface-variant uppercase tracking-widest">Pending</span>
<p class="text-headline-md text-secondary mt-1"><?= $pending ?></p>
</div>
<div class="bento-card bg-surface-container-lowest p-5 rounded-xl border border-outline-variant">
<span class="text-label-sm text-on-surface-variant uppercase tracking-widest">Cancelled</span>
<p class="text-headline-md text-on-error-container mt-1"><?= $cancelled ?></p>
</div>
</section>

<section class="bg-surface-container-lowest rounded-xl border border-outline-variant overflow-hidden">
<div class="px-6 py-4 border-b border-outline-variant flex flex-wrap justify-between items-center gap-4">
<form method="GET" class="flex gap-4 flex-wrap items-center">
<select name="status" class="bg-surface border border-outline-variant text-label-sm rounded-lg focus:ring-primary px-3 py-2" onchange="this.form.submit()">
<option value="">All Status</option>
<option value="confirmed" <?= $status_filter === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
<option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
<option value="completed" <?= $status_filter === 'completed' ? 'selected' : '' ?>>Completed</option>
<option value="cancelled" <?= $status_filter === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
</select>
<div class="relative">
<span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-sm">search</span>
<input class="bg-surface border border-outline-variant text-label-sm rounded-lg focus:ring-primary pl-9 py-2 w-56" name="search" placeholder="Search patient or doctor..." value="<?= htmlspecialchars($search) ?>" type="text"/>
</div>
<button class="text-label-sm text-primary hover:underline" type="submit">Search</button>
<?php if ($search || $status_filter): ?>
<a href="appointments.php" class="text-label-sm text-on-surface-variant hover:underline">Clear</a>
<?php endif; ?>
</form>
</div>
<table class="w-full text-left">
<thead>
<tr class="bg-surface-container-low text-on-surface-variant">
<th class="px-6 py-4 text-label-sm uppercase tracking-wider">Patient</th>
<th class="px-6 py-4 text-label-sm uppercase tracking-wider">Doctor</th>
<th class="px-6 py-4 text-label-sm uppercase tracking-wider">Date & Time</th>
<th class="px-6 py-4 text-label-sm uppercase tracking-wider">Status</th>
<th class="px-6 py-4 text-label-sm uppercase tracking-wider">Actions</th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant">
<?php foreach ($appointments as $a): ?>
<?php
$name = $a['full_name'] ?? '';
$parts = explode(' ', $name);
$initials = strtoupper(substr($parts[0] ?? '', 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
$status_class = match($a['status']) {
    'confirmed' => 'bg-primary-fixed text-on-primary-fixed',
    'completed' => 'bg-secondary-container text-on-secondary-container',
    'cancelled' => 'bg-error-container text-on-error-container',
    default => 'bg-surface-container-high text-on-surface'
};
?>
<tr>
<td class="px-6 py-4">
<div class="flex items-center gap-3">
<div class="w-9 h-9 rounded-full bg-primary-fixed flex items-center justify-center text-primary text-sm font-bold"><?= htmlspecialchars($initials) ?></div>
<div>
<p class="text-body-md font-bold text-on-surface"><?= htmlspecialchars($name) ?></p>
<p class="text-label-sm text-on-surface-variant"><?= htmlspecialchars($a['mobile'] ?? '') ?></p>
</div>
</div>
</td>
<td class="px-6 py-4 text-body-md"><?= htmlspecialchars($a['doctor_name'] ?? '—') ?></td>
<td class="px-6 py-4 text-body-md"><?= date('M d, Y · h:i A', strtotime($a['appointment_date'] . ' ' . ($a['appointment_time'] ?? '00:00:00'))) ?></td>
<td class="px-6 py-4"><span class="px-3 py-1 rounded-full text-label-sm <?= $status_class ?>"><?= htmlspecialchars(ucfirst($a['status'])) ?></span></td>
<td class="px-6 py-4">
<form method="POST" class="flex items-center gap-2" onsubmit="return confirm('Update status?')">
<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
<input type="hidden" name="appointment_id" value="<?= $a['id'] ?>">
<select name="status" class="bg-surface border border-outline-variant text-label-sm rounded px-2 py-1">
<option value="pending" <?= $a['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
<option value="confirmed" <?= $a['status'] === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
<option value="completed" <?= $a['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
<option value="cancelled" <?= $a['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
</select>
<button class="text-label-sm bg-primary text-on-primary px-3 py-1 rounded hover:opacity-90" type="submit" name="update_status" value="1">Update</button>
</form>
</td>
</tr>
<?php endforeach; ?>
<?php if (empty($appointments)): ?>
<tr><td colspan="5" class="px-6 py-8 text-center text-on-surface-variant">No appointments found.</td></tr>
<?php endif; ?>
</tbody>
</table>
<div class="px-6 py-4 border-t border-outline-variant flex justify-between items-center text-label-sm text-on-surface-variant">
<span>Showing <?= count($appointments) ?> of <?= $filtered_total ?></span>
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
