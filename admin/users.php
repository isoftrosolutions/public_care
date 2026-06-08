<?php
require_once __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$db = getDB();
$page_title = 'Users';
$active_page = 'users';

$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 20;
$search = trim($_GET['search'] ?? '');
$role_filter = $_GET['role'] ?? '';

$where = [];
$params = [];
$types = '';

if ($search) {
    $where[] = "(full_name LIKE ? OR email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= 'ss';
}
if ($role_filter) {
    $where[] = "role = ?";
    $params[] = $role_filter;
    $types .= 's';
}
$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$total_stmt = $db->prepare("SELECT COUNT(*) FROM users $where_sql");
if ($params) $total_stmt->bind_param($types, ...$params);
$total_stmt->execute();
$total = $total_stmt->get_result()->fetch_row()[0];
$total_pages = max(1, ceil($total / $per_page));
$offset = ($page - 1) * $per_page;

$admins = $db->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetch_row()[0];
$doctors_count = $db->query("SELECT COUNT(*) FROM users WHERE role = 'doctor'")->fetch_row()[0];
$customers = $db->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetch_row()[0];

$data_stmt = $db->prepare("SELECT id, full_name, email, mobile, role, created_at FROM users $where_sql ORDER BY created_at DESC LIMIT ? OFFSET ?");
$all_params = array_merge($params, [$per_page, $offset]);
$all_types = $types . 'ii';
if ($all_types !== 'ii') {
    $data_stmt->bind_param($all_types, ...$all_params);
} else {
    $data_stmt->bind_param('ii', $per_page, $offset);
}
$data_stmt->execute();
$users = $data_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<?php require_once __DIR__ . '/includes/head.php'; ?>
<body class="bg-surface font-body-md text-on-surface flex min-h-screen">
<?php require_once __DIR__ . '/includes/sidebar.php'; ?>
<main class="flex-grow ml-64 p-margin-desktop bg-surface min-h-screen">
<header class="flex justify-between items-end mb-8">
<div>
<h2 class="text-display-lg font-display-lg text-primary">Users</h2>
<p class="text-body-lg text-on-surface-variant mt-2">Registered users and role management.</p>
</div>
</header>

<section class="grid grid-cols-1 md:grid-cols-4 gap-gutter mb-8">
<div class="bento-card bg-surface-container-lowest p-5 rounded-xl border border-outline-variant">
<span class="text-label-sm text-on-surface-variant uppercase tracking-widest">Total Users</span>
<p class="text-headline-md text-on-surface mt-1"><?= number_format($total) ?></p>
</div>
<div class="bento-card bg-surface-container-lowest p-5 rounded-xl border border-outline-variant">
<span class="text-label-sm text-on-surface-variant uppercase tracking-widest">Admins</span>
<p class="text-headline-md text-primary mt-1"><?= $admins ?></p>
</div>
<div class="bento-card bg-surface-container-lowest p-5 rounded-xl border border-outline-variant">
<span class="text-label-sm text-on-surface-variant uppercase tracking-widest">Doctors</span>
<p class="text-headline-md text-secondary mt-1"><?= $doctors_count ?></p>
</div>
<div class="bento-card bg-surface-container-lowest p-5 rounded-xl border border-outline-variant">
<span class="text-label-sm text-on-surface-variant uppercase tracking-widest">Customers</span>
<p class="text-headline-md text-on-error-container mt-1"><?= $customers ?></p>
</div>
</section>

<section class="bg-surface-container-lowest rounded-xl border border-outline-variant overflow-hidden">
<div class="px-6 py-4 border-b border-outline-variant flex flex-wrap justify-between items-center gap-4">
<form method="GET" class="flex gap-4 flex-wrap items-center">
<select name="role" class="bg-surface border border-outline-variant text-label-sm rounded-lg focus:ring-primary px-3 py-2" onchange="this.form.submit()">
<option value="">All Roles</option>
<option value="admin" <?= $role_filter === 'admin' ? 'selected' : '' ?>>Admin</option>
<option value="doctor" <?= $role_filter === 'doctor' ? 'selected' : '' ?>>Doctor</option>
<option value="customer" <?= $role_filter === 'customer' ? 'selected' : '' ?>>Customer</option>
</select>
<div class="relative">
<span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-sm">search</span>
<input class="bg-surface border border-outline-variant text-label-sm rounded-lg focus:ring-primary pl-9 py-2 w-56" name="search" placeholder="Search name or email..." value="<?= htmlspecialchars($search) ?>" type="text"/>
</div>
<button class="text-label-sm text-primary hover:underline" type="submit">Search</button>
<?php if ($search || $role_filter): ?>
<a href="users.php" class="text-label-sm text-on-surface-variant hover:underline">Clear</a>
<?php endif; ?>
</form>
</div>
<table class="w-full text-left">
<thead>
<tr class="bg-surface-container-low text-on-surface-variant">
<th class="px-6 py-4 text-label-sm uppercase tracking-wider">Name</th>
<th class="px-6 py-4 text-label-sm uppercase tracking-wider">Email</th>
<th class="px-6 py-4 text-label-sm uppercase tracking-wider">Mobile</th>
<th class="px-6 py-4 text-label-sm uppercase tracking-wider">Role</th>
<th class="px-6 py-4 text-label-sm uppercase tracking-wider">Registered Date</th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant">
<?php foreach ($users as $u): ?>
<?php
$parts = explode(' ', $u['full_name']);
$initials = strtoupper(substr($parts[0] ?? '', 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
$role_class = match($u['role']) {
    'admin' => 'bg-primary-fixed text-on-primary-fixed',
    'doctor' => 'bg-secondary-container text-on-secondary-container',
    default => 'bg-surface-container-high text-on-surface'
};
?>
<tr>
<td class="px-6 py-4">
<div class="flex items-center gap-3">
<div class="w-9 h-9 rounded-full bg-primary-fixed flex items-center justify-center text-primary text-sm font-bold"><?= htmlspecialchars($initials) ?></div>
<div><p class="text-body-md font-bold text-on-surface"><?= htmlspecialchars($u['full_name']) ?></p></div>
</div>
</td>
<td class="px-6 py-4 text-body-md text-on-surface-variant"><?= htmlspecialchars($u['email']) ?></td>
<td class="px-6 py-4 text-body-md"><?= htmlspecialchars($u['mobile'] ?? '—') ?></td>
<td class="px-6 py-4"><span class="px-3 py-1 rounded-full text-label-sm <?= $role_class ?>"><?= htmlspecialchars(ucfirst($u['role'])) ?></span></td>
<td class="px-6 py-4 text-body-md text-on-surface-variant"><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
</tr>
<?php endforeach; ?>
<?php if (empty($users)): ?>
<tr><td colspan="5" class="px-6 py-8 text-center text-on-surface-variant">No users found.</td></tr>
<?php endif; ?>
</tbody>
</table>
<div class="px-6 py-4 border-t border-outline-variant flex justify-between items-center text-label-sm text-on-surface-variant">
<span>Showing <?= count($users) ?> of <?= number_format($total) ?></span>
<div class="flex gap-2">
<?php if ($page > 1): ?><a class="px-3 py-1 rounded bg-surface-container-high hover:bg-surface-container-highest transition-colors" href="?page=<?= $page - 1 ?>&role=<?= urlencode($role_filter) ?>&search=<?= urlencode($search) ?>">Previous</a><?php endif; ?>
<span class="px-3 py-1 rounded bg-primary text-on-primary"><?= $page ?></span>
<?php if ($page < $total_pages): ?><a class="px-3 py-1 rounded bg-surface-container-high hover:bg-surface-container-highest transition-colors" href="?page=<?= $page + 1 ?>&role=<?= urlencode($role_filter) ?>&search=<?= urlencode($search) ?>">Next</a><?php endif; ?>
</div>
</div>
</section>
</main>
</body>
</html>
