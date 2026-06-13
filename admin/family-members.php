<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/feature_helpers.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$db = getDB();
$page_title = 'Family Accounts';
$active_page = 'family-members';

$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 20;
$search = trim($_GET['search'] ?? '');
$relationship = trim($_GET['relationship'] ?? '');
$has_family_table = table_exists($db, 'family_members');

$members = [];
$total = 0;
$total_pages = 1;
$total_accounts = 0;
$linked_users = 0;
$self_profiles = 0;

if ($has_family_table) {
    $where = [];
    $params = [];
    $types = '';

    if ($search !== '') {
        $where[] = '(fm.full_name LIKE ? OR u.full_name LIKE ? OR u.email LIKE ? OR u.mobile LIKE ?)';
        $term = "%$search%";
        array_push($params, $term, $term, $term, $term);
        $types .= 'ssss';
    }

    if ($relationship !== '') {
        $where[] = 'fm.relationship = ?';
        $params[] = $relationship;
        $types .= 's';
    }

    $where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $total_stmt = $db->prepare("SELECT COUNT(*) FROM family_members fm JOIN users u ON fm.user_id = u.id $where_sql");
    if ($params) {
        $total_stmt->bind_param($types, ...$params);
    }
    $total_stmt->execute();
    $total = (int)$total_stmt->get_result()->fetch_row()[0];
    $total_stmt->close();

    $total_pages = max(1, (int)ceil($total / $per_page));
    $offset = ($page - 1) * $per_page;

    $total_accounts = (int)$db->query('SELECT COUNT(*) FROM family_members')->fetch_row()[0];
    $linked_users = (int)$db->query('SELECT COUNT(DISTINCT user_id) FROM family_members')->fetch_row()[0];
    $self_profiles = (int)$db->query("SELECT COUNT(*) FROM family_members WHERE relationship = 'self'")->fetch_row()[0];

    $data_sql = "SELECT fm.*, u.full_name AS user_name, u.email, u.mobile
        FROM family_members fm
        JOIN users u ON fm.user_id = u.id
        $where_sql
        ORDER BY u.full_name ASC, FIELD(fm.relationship,'self','spouse','son','daughter','father','mother','other'), fm.created_at DESC
        LIMIT ? OFFSET ?";
    $data_stmt = $db->prepare($data_sql);
    $all_params = array_merge($params, [$per_page, $offset]);
    $all_types = $types . 'ii';
    $data_stmt->bind_param($all_types, ...$all_params);
    $data_stmt->execute();
    $members = $data_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $data_stmt->close();
}
?>
<?php require_once __DIR__ . '/includes/head.php'; ?>
<body class="bg-surface font-body-md text-on-surface flex min-h-screen">
<?php require_once __DIR__ . '/includes/sidebar.php'; ?>
<main class="flex-grow ml-64 p-margin-desktop bg-surface min-h-screen">
<header class="flex justify-between items-end mb-8">
<div>
<h2 class="text-display-lg font-display-lg text-primary">Family Accounts</h2>
<p class="text-body-lg text-on-surface-variant mt-2">Review family member profiles linked to customer accounts.</p>
</div>
</header>

<section class="grid grid-cols-1 md:grid-cols-3 gap-gutter mb-8">
<div class="bento-card bg-surface-container-lowest p-5 rounded-xl border border-outline-variant">
<span class="text-label-sm text-on-surface-variant uppercase tracking-widest">Family Profiles</span>
<p class="text-headline-md text-on-surface mt-1"><?= number_format($total_accounts) ?></p>
</div>
<div class="bento-card bg-surface-container-lowest p-5 rounded-xl border border-outline-variant">
<span class="text-label-sm text-on-surface-variant uppercase tracking-widest">Linked Customers</span>
<p class="text-headline-md text-primary mt-1"><?= number_format($linked_users) ?></p>
</div>
<div class="bento-card bg-surface-container-lowest p-5 rounded-xl border border-outline-variant">
<span class="text-label-sm text-on-surface-variant uppercase tracking-widest">Self Profiles</span>
<p class="text-headline-md text-secondary mt-1"><?= number_format($self_profiles) ?></p>
</div>
</section>

<?php if (!$has_family_table): ?>
<section class="bg-surface-container-lowest rounded-xl border border-outline-variant p-10 text-center">
<span class="material-symbols-outlined text-5xl text-outline">family_history</span>
<h3 class="mt-4 text-headline-md font-headline-md text-primary">Family members table is missing</h3>
<p class="mt-2 text-on-surface-variant">Run the database migration to create <code>family_members</code>.</p>
</section>
<?php else: ?>
<section class="bg-surface-container-lowest rounded-xl border border-outline-variant overflow-hidden">
<div class="px-6 py-4 border-b border-outline-variant flex flex-wrap justify-between items-center gap-4">
<form method="GET" class="flex gap-4 flex-wrap items-center">
<select name="relationship" class="bg-surface border border-outline-variant text-label-sm rounded-lg focus:ring-primary px-3 py-2" onchange="this.form.submit()">
<option value="">All Relationships</option>
<?php foreach (['self','spouse','son','daughter','father','mother','other'] as $rel): ?>
<option value="<?= h($rel) ?>" <?= $relationship === $rel ? 'selected' : '' ?>><?= h(ucfirst($rel)) ?></option>
<?php endforeach; ?>
</select>
<div class="relative">
<span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-sm">search</span>
<input class="bg-surface border border-outline-variant text-label-sm rounded-lg focus:ring-primary pl-9 py-2 w-64" name="search" placeholder="Search member or customer..." value="<?= h($search) ?>" type="text"/>
</div>
<button class="text-label-sm text-primary hover:underline" type="submit">Search</button>
<?php if ($search || $relationship): ?>
<a href="family-members.php" class="text-label-sm text-on-surface-variant hover:underline">Clear</a>
<?php endif; ?>
</form>
</div>

<table class="w-full text-left">
<thead>
<tr class="bg-surface-container-low text-on-surface-variant">
<th class="px-6 py-4 text-label-sm uppercase tracking-wider">Member</th>
<th class="px-6 py-4 text-label-sm uppercase tracking-wider">Customer Account</th>
<th class="px-6 py-4 text-label-sm uppercase tracking-wider">Relationship</th>
<th class="px-6 py-4 text-label-sm uppercase tracking-wider">Age</th>
<th class="px-6 py-4 text-label-sm uppercase tracking-wider">Gender</th>
<th class="px-6 py-4 text-label-sm uppercase tracking-wider">Created</th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant">
<?php foreach ($members as $member): ?>
<?php
$member_name = $member['full_name'] ?? '';
$parts = explode(' ', $member_name);
$initials = strtoupper(substr($parts[0] ?? '', 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
?>
<tr>
<td class="px-6 py-4">
<div class="flex items-center gap-3">
<div class="w-9 h-9 rounded-full bg-primary-fixed flex items-center justify-center text-primary text-sm font-bold"><?= h($initials ?: 'F') ?></div>
<p class="text-body-md font-bold text-on-surface"><?= h($member_name) ?></p>
</div>
</td>
<td class="px-6 py-4">
<p class="text-body-md font-bold text-on-surface"><?= h($member['user_name'] ?? '') ?></p>
<p class="text-label-sm text-on-surface-variant"><?= h($member['email'] ?? '') ?></p>
<p class="text-label-sm text-on-surface-variant"><?= h($member['mobile'] ?? '') ?></p>
</td>
<td class="px-6 py-4"><span class="px-3 py-1 rounded-full text-label-sm bg-primary-fixed text-on-primary-fixed"><?= h(ucfirst($member['relationship'] ?? 'other')) ?></span></td>
<td class="px-6 py-4 text-body-md"><?= !empty($member['age']) ? (int)$member['age'] : '—' ?></td>
<td class="px-6 py-4 text-body-md"><?= h(!empty($member['gender']) ? ucfirst($member['gender']) : '—') ?></td>
<td class="px-6 py-4 text-body-md text-on-surface-variant"><?= !empty($member['created_at']) ? date('M d, Y', strtotime($member['created_at'])) : '—' ?></td>
</tr>
<?php endforeach; ?>
<?php if (!$members): ?>
<tr><td colspan="6" class="px-6 py-8 text-center text-on-surface-variant">No family members found.</td></tr>
<?php endif; ?>
</tbody>
</table>
<div class="px-6 py-4 border-t border-outline-variant flex justify-between items-center text-label-sm text-on-surface-variant">
<span>Showing <?= count($members) ?> of <?= number_format($total) ?></span>
<div class="flex gap-2">
<?php if ($page > 1): ?><a class="px-3 py-1 rounded bg-surface-container-high hover:bg-surface-container-highest transition-colors" href="?page=<?= $page - 1 ?>&relationship=<?= urlencode($relationship) ?>&search=<?= urlencode($search) ?>">Previous</a><?php endif; ?>
<span class="px-3 py-1 rounded bg-primary text-on-primary"><?= $page ?></span>
<?php if ($page < $total_pages): ?><a class="px-3 py-1 rounded bg-surface-container-high hover:bg-surface-container-highest transition-colors" href="?page=<?= $page + 1 ?>&relationship=<?= urlencode($relationship) ?>&search=<?= urlencode($search) ?>">Next</a><?php endif; ?>
</div>
</div>
</section>
<?php endif; ?>
</main>
</body>
</html>
