<?php
require_once __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$db = getDB();
$page_title = 'Patient Metrics';
$active_page = 'patient-metrics';

$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 20;
$search = trim($_GET['search'] ?? '');
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

$where = [];
$params = [];
$types = '';
if ($search) {
    $where[] = "u.full_name LIKE ?";
    $params[] = "%$search%";
    $types .= 's';
}
if ($date_from) {
    $where[] = "pm.record_date >= ?";
    $params[] = $date_from;
    $types .= 's';
}
if ($date_to) {
    $where[] = "pm.record_date <= ?";
    $params[] = $date_to;
    $types .= 's';
}
$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$count_sql = "SELECT COUNT(*) FROM patient_metrics pm JOIN users u ON pm.user_id = u.id $where_sql";
$total_stmt = $db->prepare($count_sql);
if ($params) $total_stmt->bind_param($types, ...$params);
$total_stmt->execute();
$filtered_total = $total_stmt->get_result()->fetch_row()[0];
$total_pages = max(1, ceil($filtered_total / $per_page));
$offset = ($page - 1) * $per_page;

$grand_total = $db->query("SELECT COUNT(*) FROM patient_metrics")->fetch_row()[0];
$total_users = $db->query("SELECT COUNT(DISTINCT user_id) FROM patient_metrics")->fetch_row()[0];
$avg_weight = $db->query("SELECT ROUND(AVG(weight), 1) FROM patient_metrics WHERE weight IS NOT NULL")->fetch_row()[0];

$data_sql = "SELECT pm.*, u.full_name, u.mobile FROM patient_metrics pm JOIN users u ON pm.user_id = u.id $where_sql ORDER BY pm.record_date DESC, pm.id DESC LIMIT ? OFFSET ?";
$data_stmt = $db->prepare($data_sql);
$all_params = array_merge($params, [$per_page, $offset]);
$all_types = $types . 'ii';
if ($all_types !== 'ii') {
    $data_stmt->bind_param($all_types, ...$all_params);
} else {
    $data_stmt->bind_param('ii', $per_page, $offset);
}
$data_stmt->execute();
$metrics = $data_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<?php require_once __DIR__ . '/includes/head.php'; ?>
<body class="bg-surface font-body-md text-on-surface flex min-h-screen">
<?php require_once __DIR__ . '/includes/sidebar.php'; ?>
<main class="flex-grow ml-64 p-margin-desktop bg-surface min-h-screen">
<header class="flex justify-between items-end mb-8">
<div>
<h2 class="text-display-lg font-display-lg text-primary">Patient Metrics</h2>
<p class="text-body-lg text-on-surface-variant mt-2">View all patients' health tracking data.</p>
</div>
</header>

<section class="grid grid-cols-1 md:grid-cols-3 gap-gutter mb-8">
<div class="bento-card bg-surface-container-lowest p-5 rounded-xl border border-outline-variant">
<span class="text-label-sm text-on-surface-variant uppercase tracking-widest">Total Entries</span>
<p class="text-headline-md text-on-surface mt-1"><?= $grand_total ?></p>
</div>
<div class="bento-card bg-surface-container-lowest p-5 rounded-xl border border-outline-variant">
<span class="text-label-sm text-on-surface-variant uppercase tracking-widest">Active Patients</span>
<p class="text-headline-md text-primary mt-1"><?= $total_users ?></p>
</div>
<div class="bento-card bg-surface-container-lowest p-5 rounded-xl border border-outline-variant">
<span class="text-label-sm text-on-surface-variant uppercase tracking-widest">Avg Weight</span>
<p class="text-headline-md text-on-surface mt-1"><?= $avg_weight ?: '--' ?> kg</p>
</div>
</section>

<section class="bg-surface-container-lowest rounded-xl border border-outline-variant overflow-hidden">
<div class="px-6 py-4 border-b border-outline-variant flex flex-wrap justify-between items-center gap-4">
<form method="GET" class="flex gap-4 flex-wrap items-center">
<div class="relative">
<span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-sm">search</span>
<input class="bg-surface border border-outline-variant text-label-sm rounded-lg focus:ring-primary pl-9 py-2 w-48" name="search" placeholder="Search patient..." value="<?= htmlspecialchars($search) ?>" type="text"/>
</div>
<input class="bg-surface border border-outline-variant text-label-sm rounded-lg focus:ring-primary px-3 py-2" name="date_from" type="date" value="<?= htmlspecialchars($date_from) ?>" placeholder="From">
<input class="bg-surface border border-outline-variant text-label-sm rounded-lg focus:ring-primary px-3 py-2" name="date_to" type="date" value="<?= htmlspecialchars($date_to) ?>" placeholder="To">
<button class="text-label-sm text-primary hover:underline" type="submit">Search</button>
<?php if ($search || $date_from || $date_to): ?>
<a href="patient-metrics.php" class="text-label-sm text-on-surface-variant hover:underline">Clear</a>
<?php endif; ?>
</form>
</div>
<table class="w-full text-left">
<thead>
<tr class="bg-surface-container-low text-on-surface-variant">
<th class="px-6 py-4 text-label-sm uppercase tracking-wider">Patient</th>
<th class="px-6 py-4 text-label-sm uppercase tracking-wider">Date</th>
<th class="px-6 py-4 text-label-sm uppercase tracking-wider">Weight</th>
<th class="px-6 py-4 text-label-sm uppercase tracking-wider">Sleep</th>
<th class="px-6 py-4 text-label-sm uppercase tracking-wider">Pain</th>
<th class="px-6 py-4 text-label-sm uppercase tracking-wider">BP</th>
<th class="px-6 py-4 text-label-sm uppercase tracking-wider">Sugar</th>
<th class="px-6 py-4 text-label-sm uppercase tracking-wider">Notes</th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant">
<?php foreach ($metrics as $m): ?>
<?php
$name = $m['full_name'] ?? '';
$parts = explode(' ', $name);
$initials = strtoupper(substr($parts[0] ?? '', 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
?>
<tr>
<td class="px-6 py-4">
<div class="flex items-center gap-3">
<div class="w-9 h-9 rounded-full bg-primary-fixed flex items-center justify-center text-primary text-sm font-bold"><?= htmlspecialchars($initials) ?></div>
<div>
<p class="text-body-md font-bold text-on-surface"><?= htmlspecialchars($name) ?></p>
<p class="text-label-sm text-on-surface-variant"><?= htmlspecialchars($m['mobile'] ?? '') ?></p>
</div>
</div>
</td>
<td class="px-6 py-4 text-body-md"><?= date('M d, Y', strtotime($m['record_date'])) ?></td>
<td class="px-6 py-4 text-body-md"><?= $m['weight'] ? $m['weight'] . ' kg' : '—' ?></td>
<td class="px-6 py-4 text-body-md"><?= $m['sleep_hours'] ? $m['sleep_hours'] . ' h' : '—' ?></td>
<td class="px-6 py-4 text-body-md"><?= $m['pain_score'] ? $m['pain_score'] . '/10' : '—' ?></td>
<td class="px-6 py-4 text-body-md"><?= ($m['bp_systolic'] && $m['bp_diastolic']) ? $m['bp_systolic'] . '/' . $m['bp_diastolic'] : '—' ?></td>
<td class="px-6 py-4 text-body-md"><?= $m['blood_sugar'] ? $m['blood_sugar'] . ' mg/dL' : '—' ?></td>
<td class="px-6 py-4 text-body-md max-w-40 truncate"><?= htmlspecialchars($m['notes'] ?? '—') ?></td>
</tr>
<?php endforeach; ?>
<?php if (empty($metrics)): ?>
<tr><td colspan="8" class="px-6 py-8 text-center text-on-surface-variant">No patient metrics found.</td></tr>
<?php endif; ?>
</tbody>
</table>
<div class="px-6 py-4 border-t border-outline-variant flex justify-between items-center text-label-sm text-on-surface-variant">
<span>Showing <?= count($metrics) ?> of <?= $filtered_total ?></span>
<div class="flex gap-2">
<?php if ($page > 1): ?><a class="px-3 py-1 rounded bg-surface-container-high hover:bg-surface-container-highest transition-colors" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&date_from=<?= urlencode($date_from) ?>&date_to=<?= urlencode($date_to) ?>">Previous</a><?php endif; ?>
<span class="px-3 py-1 rounded bg-primary text-on-primary"><?= $page ?></span>
<?php if ($page < $total_pages): ?><a class="px-3 py-1 rounded bg-surface-container-high hover:bg-surface-container-highest transition-colors" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&date_from=<?= urlencode($date_from) ?>&date_to=<?= urlencode($date_to) ?>">Next</a><?php endif; ?>
</div>
</div>
</section>
</main>
</body>
</html>
