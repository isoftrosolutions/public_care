<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/includes/admin-crud.php';

admin_require_auth();

$db = getDB();
$page_title = 'Doctors';
$active_page = 'doctors';

$total_doctors = $db->query("SELECT COUNT(*) FROM doctors")->fetch_row()[0];
$available_count = $db->query("SELECT COUNT(*) FROM doctors WHERE available = 1")->fetch_row()[0];
$unavailable_count = $db->query("SELECT COUNT(*) FROM doctors WHERE available = 0")->fetch_row()[0];

$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 20;
$search = trim($_GET['search'] ?? '');

$where_conds = [];
$bind_params = [];
$bind_types = '';

if ($search) {
    $where_conds[] = "(name LIKE ? OR specialty LIKE ?)";
    $bind_params[] = "%$search%";
    $bind_params[] = "%$search%";
    $bind_types .= 'ss';
}
$where_sql = $where_conds ? 'WHERE ' . implode(' AND ', $where_conds) : '';

$count_stmt = $db->prepare("SELECT COUNT(*) FROM doctors $where_sql");
if ($bind_params) {
    $count_stmt->bind_param($bind_types, ...$bind_params);
}
$count_stmt->execute();
$total = $count_stmt->get_result()->fetch_row()[0];
$total_pages = max(1, ceil($total / $per_page));
$offset = ($page - 1) * $per_page;

$data_sql = "SELECT * FROM doctors $where_sql ORDER BY created_at DESC LIMIT ? OFFSET ?";
$data_stmt = $db->prepare($data_sql);
if ($bind_params) {
    $bind_params[] = $per_page;
    $bind_params[] = $offset;
    $bind_types .= 'ii';
    $data_stmt->bind_param($bind_types, ...$bind_params);
} else {
    $data_stmt->bind_param('ii', $per_page, $offset);
}
$data_stmt->execute();
$doctors = $data_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<?php require_once __DIR__ . '/includes/head.php'; ?>
<body class="bg-surface font-body-md text-on-surface flex min-h-screen">
<?php require_once __DIR__ . '/includes/sidebar.php'; ?>
<main class="flex-grow ml-64 p-margin-desktop bg-surface min-h-screen">
<header class="flex justify-between items-end mb-8">
<div>
<h2 class="text-display-lg font-display-lg text-primary">Doctors</h2>
<p class="text-body-lg text-on-surface-variant mt-2">Manage Ayurvedic doctors and their profiles.</p>
</div>
<a href="doctor-create.php" class="inline-flex items-center gap-2 bg-primary text-on-primary px-5 py-2.5 rounded-lg text-label-sm hover:opacity-90 transition-opacity"><span class="material-symbols-outlined text-sm">add</span> Add Doctor</a>
</header>

<section class="grid grid-cols-1 md:grid-cols-3 gap-gutter mb-8">
<div class="bento-card bg-surface-container-lowest p-5 rounded-xl border border-outline-variant">
<span class="text-label-sm text-on-surface-variant uppercase tracking-widest">Total Doctors</span>
<p class="text-headline-md text-on-surface mt-1"><?= number_format($total_doctors) ?></p>
</div>
<div class="bento-card bg-surface-container-lowest p-5 rounded-xl border border-outline-variant">
<span class="text-label-sm text-on-surface-variant uppercase tracking-widest">Available</span>
<p class="text-headline-md text-primary mt-1"><?= $available_count ?></p>
</div>
<div class="bento-card bg-surface-container-lowest p-5 rounded-xl border border-outline-variant">
<span class="text-label-sm text-on-surface-variant uppercase tracking-widest">Unavailable</span>
<p class="text-headline-md text-on-error-container mt-1"><?= $unavailable_count ?></p>
</div>
</section>

<section class="bg-surface-container-lowest rounded-xl border border-outline-variant overflow-hidden">
<div class="px-6 py-4 border-b border-outline-variant flex flex-wrap justify-between items-center gap-4">
<form class="flex gap-4 flex-wrap items-center" method="GET">
<div class="relative">
<span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-sm">search</span>
<input class="bg-surface border border-outline-variant text-label-sm rounded-lg focus:ring-primary pl-9 py-2 w-56" name="search" placeholder="Search name or specialty..." type="text" value="<?= htmlspecialchars($search) ?>">
</div>
<button class="text-label-sm text-primary hover:underline" type="submit">Search</button>
<?php if ($search): ?>
<a class="text-label-sm text-on-surface-variant hover:underline" href="doctors.php">Clear</a>
<?php endif; ?>
</form>
</div>
<table class="w-full text-left">
<thead>
<tr class="bg-surface-container-low text-on-surface-variant">
<th class="px-6 py-4 text-label-sm uppercase tracking-wider">Name</th>
<th class="px-6 py-4 text-label-sm uppercase tracking-wider">Specialty</th>
<th class="px-6 py-4 text-label-sm uppercase tracking-wider">Experience</th>
<th class="px-6 py-4 text-label-sm uppercase tracking-wider">Fee</th>
<th class="px-6 py-4 text-label-sm uppercase tracking-wider">Rating</th>
<th class="px-6 py-4 text-label-sm uppercase tracking-wider">Available</th>
<th class="px-6 py-4 text-label-sm uppercase tracking-wider">Actions</th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant">
<?php foreach ($doctors as $d): ?>
<?php
$name_parts = explode(' ', $d['name']);
$initial = strtoupper(substr($name_parts[0] ?? '', 0, 1));
?>
<tr>
<td class="px-6 py-4">
<div class="flex items-center gap-3">
<?php if ($d['image_url']): ?>
<img alt="<?= htmlspecialchars($d['name']) ?>" class="w-9 h-9 rounded-full object-cover" src="<?= htmlspecialchars($d['image_url']) ?>">
<?php else: ?>
<div class="w-9 h-9 rounded-full bg-primary-fixed flex items-center justify-center text-primary text-sm font-bold"><?= $initial ?></div>
<?php endif; ?>
<div>
<p class="text-body-md font-bold text-on-surface"><?= htmlspecialchars($d['name']) ?></p>
<p class="text-label-sm text-on-surface-variant"><?= htmlspecialchars($d['qualifications'] ?? '') ?></p>
</div>
</div>
</td>
<td class="px-6 py-4 text-body-md"><?= htmlspecialchars($d['specialty'] ?? '—') ?></td>
<td class="px-6 py-4 text-body-md"><?= $d['experience_years'] ? $d['experience_years'] . ' yrs' : '—' ?></td>
<td class="px-6 py-4 text-body-md font-bold">₹<?= number_format($d['fee'], 2) ?></td>
<td class="px-6 py-4 text-body-md">
<?php if ($d['rating'] > 0): ?>
<?= number_format($d['rating'], 1) ?><span class="text-on-surface-variant text-label-sm"> (<?= $d['reviews_count'] ?>)</span>
<?php else: ?>
—
<?php endif; ?>
</td>
<td class="px-6 py-4"><span class="px-3 py-1 rounded-full text-label-sm <?= $d['available'] ? 'bg-primary-fixed text-on-primary-fixed' : 'bg-surface-container-high text-on-surface' ?>"><?= $d['available'] ? 'Available' : 'Unavailable' ?></span></td>
<td class="px-6 py-4">
<div class="flex items-center gap-2">
<a class="text-label-sm bg-surface-container-high px-3 py-1 rounded hover:bg-surface-container-highest transition-all flex items-center gap-1" href="doctor-edit.php?id=<?= $d['id'] ?>"><span class="material-symbols-outlined text-sm">edit</span> Edit</a>
<a class="text-label-sm bg-error-container text-on-error-container px-3 py-1 rounded hover:opacity-80 transition-all flex items-center gap-1" href="doctor-delete.php?id=<?= $d['id'] ?>"><span class="material-symbols-outlined text-sm">delete</span> Delete</a>
</div>
</td>
</tr>
<?php endforeach; ?>
<?php if (empty($doctors)): ?>
<tr><td class="px-6 py-8 text-center text-on-surface-variant" colspan="7">No doctors found.</td></tr>
<?php endif; ?>
</tbody>
</table>
<div class="px-6 py-4 border-t border-outline-variant flex justify-between items-center text-label-sm text-on-surface-variant">
<span>Showing <?= count($doctors) ?> of <?= number_format($total) ?></span>
<div class="flex gap-2">
<?php if ($page > 1): ?><a class="px-3 py-1 rounded bg-surface-container-high hover:bg-surface-container-highest transition-colors" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>">Previous</a><?php endif; ?>
<span class="px-3 py-1 rounded bg-primary text-on-primary"><?= $page ?></span>
<?php if ($page < $total_pages): ?><a class="px-3 py-1 rounded bg-surface-container-high hover:bg-surface-container-highest transition-colors" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>">Next</a><?php endif; ?>
</div>
</div>
</section>
</main>
</body>
</html>
