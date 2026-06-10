<?php
require_once __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$db = getDB();
$page_title = 'Prescriptions';
$active_page = 'prescriptions';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csrf_token']) && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    if (isset($_POST['save_prescription'])) {
        $p_id = (int)($_POST['prescription_id'] ?? 0);
        $consultation_id = (int)$_POST['consultation_id'];
        $doctor_id = (int)$_POST['doctor_id'];
        $user_id = (int)$_POST['user_id'];
        $diagnosis = trim($_POST['diagnosis'] ?? '');
        $medicines = trim($_POST['medicines'] ?? '');
        $advice = trim($_POST['advice'] ?? '');
        $follow_up = !empty($_POST['follow_up_date']) ? trim($_POST['follow_up_date']) : null;

        if ($p_id > 0) {
            $stmt = $db->prepare("UPDATE prescriptions SET diagnosis = ?, medicines = ?, advice = ?, follow_up_date = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $diagnosis, $medicines, $advice, $follow_up, $p_id);
        } else {
            $stmt = $db->prepare("INSERT INTO prescriptions (consultation_id, doctor_id, user_id, diagnosis, medicines, advice, follow_up_date) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iiissss", $consultation_id, $doctor_id, $user_id, $diagnosis, $medicines, $advice, $follow_up);
        }
        $stmt->execute();
        header('Location: prescriptions.php');
        exit;
    }

    if (isset($_POST['delete_prescription'])) {
        $p_id = (int)$_POST['prescription_id'];
        $stmt = $db->prepare("DELETE FROM prescriptions WHERE id = ?");
        $stmt->bind_param("i", $p_id);
        $stmt->execute();
        header('Location: prescriptions.php');
        exit;
    }
}

$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 20;
$search = trim($_GET['search'] ?? '');
$filter_date = trim($_GET['filter_date'] ?? '');

$where = [];
$params = [];
$types = '';
if ($search) {
    $where[] = "(u.full_name LIKE ? OR d.name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= 'ss';
}
if ($filter_date) {
    $where[] = "DATE(p.created_at) = ?";
    $params[] = $filter_date;
    $types .= 's';
}
$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$count_sql = "SELECT COUNT(*) FROM prescriptions p
    JOIN users u ON p.user_id = u.id
    JOIN doctors d ON p.doctor_id = d.id
    $where_sql";
$total_stmt = $db->prepare($count_sql);
if ($params) $total_stmt->bind_param($types, ...$params);
$total_stmt->execute();
$filtered_total = $total_stmt->get_result()->fetch_row()[0];
$total_pages = max(1, ceil($filtered_total / $per_page));
$offset = ($page - 1) * $per_page;

$grand_total = $db->query("SELECT COUNT(*) FROM prescriptions")->fetch_row()[0];

$data_sql = "SELECT p.*, u.full_name AS patient_name, d.name AS doctor_name
    FROM prescriptions p
    JOIN users u ON p.user_id = u.id
    JOIN doctors d ON p.doctor_id = d.id
    $where_sql
    ORDER BY p.created_at DESC LIMIT ? OFFSET ?";
$data_stmt = $db->prepare($data_sql);
$all_params = array_merge($params, [$per_page, $offset]);
$all_types = $types . 'ii';
if ($all_types !== 'ii') {
    $data_stmt->bind_param($all_types, ...$all_params);
} else {
    $data_stmt->bind_param('ii', $per_page, $offset);
}
$data_stmt->execute();
$prescriptions = $data_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$doctors = $db->query("SELECT id, name FROM doctors ORDER BY name")->fetch_all(MYSQLI_ASSOC);

$edit_prescription = null;
if (isset($_GET['edit'])) {
    $eid = (int)$_GET['edit'];
    $stmt = $db->prepare("SELECT * FROM prescriptions WHERE id = ?");
    $stmt->bind_param("i", $eid);
    $stmt->execute();
    $edit_prescription = $stmt->get_result()->fetch_assoc();
}

$show_form = isset($_GET['add']) || $edit_prescription;
?>
<?php require_once __DIR__ . '/includes/head.php'; ?>
<body class="bg-surface font-body-md text-on-surface flex min-h-screen">
<?php require_once __DIR__ . '/includes/sidebar.php'; ?>
<main class="flex-grow ml-64 p-margin-desktop bg-surface min-h-screen">
<header class="flex justify-between items-end mb-8">
<div>
<h2 class="text-display-lg font-display-lg text-primary">Prescriptions</h2>
<p class="text-body-lg text-on-surface-variant mt-2">Manage digital prescriptions issued during consultations.</p>
</div>
<a href="?add=1" class="bg-primary text-on-primary px-6 py-3 rounded-lg font-label-md hover:opacity-90 transition-all flex items-center gap-2">
<span class="material-symbols-outlined">add</span> New Prescription
</a>
</header>

<section class="grid grid-cols-1 md:grid-cols-2 gap-gutter mb-8">
<div class="bento-card bg-surface-container-lowest p-5 rounded-xl border border-outline-variant">
<span class="text-label-sm text-on-surface-variant uppercase tracking-widest">Total Prescriptions</span>
<p class="text-headline-md text-on-surface mt-1"><?= $grand_total ?></p>
</div>
<div class="bento-card bg-surface-container-lowest p-5 rounded-xl border border-outline-variant">
<span class="text-label-sm text-on-surface-variant uppercase tracking-widest">Filtered Results</span>
<p class="text-headline-md text-primary mt-1"><?= $filtered_total ?></p>
</div>
</section>

<?php if ($show_form): ?>
<section class="bg-surface-container-lowest rounded-xl border border-outline-variant p-8 mb-8">
<h3 class="text-headline-md font-headline-md text-primary mb-6"><?= $edit_prescription ? 'Edit Prescription' : 'New Prescription' ?></h3>
<form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6">
<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
<?php if ($edit_prescription): ?>
<input type="hidden" name="prescription_id" value="<?= $edit_prescription['id'] ?>">
<input type="hidden" name="consultation_id" value="<?= $edit_prescription['consultation_id'] ?>">
<input type="hidden" name="doctor_id" value="<?= $edit_prescription['doctor_id'] ?>">
<input type="hidden" name="user_id" value="<?= $edit_prescription['user_id'] ?>">
<?php else: ?>
<div>
<label class="text-label-sm font-label-sm text-on-surface-variant block mb-1">Consultation ID</label>
<input type="number" name="consultation_id" class="w-full bg-surface border border-outline-variant rounded-lg px-4 py-2 text-body-md" required>
</div>
<div>
<label class="text-label-sm font-label-sm text-on-surface-variant block mb-1">Doctor</label>
<select name="doctor_id" class="w-full bg-surface border border-outline-variant rounded-lg px-4 py-2 text-body-md" required>
<option value="">Select Doctor...</option>
<?php foreach ($doctors as $d): ?>
<option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option>
<?php endforeach; ?>
</select>
</div>
<div>
<label class="text-label-sm font-label-sm text-on-surface-variant block mb-1">User ID</label>
<input type="number" name="user_id" class="w-full bg-surface border border-outline-variant rounded-lg px-4 py-2 text-body-md" required>
</div>
<?php endif; ?>
<div class="md:col-span-2">
<label class="text-label-sm font-label-sm text-on-surface-variant block mb-1">Diagnosis</label>
<textarea name="diagnosis" rows="3" class="w-full bg-surface border border-outline-variant rounded-lg px-4 py-2 text-body-md"><?= $edit_prescription ? htmlspecialchars($edit_prescription['diagnosis']) : '' ?></textarea>
</div>
<div class="md:col-span-2">
<label class="text-label-sm font-label-sm text-on-surface-variant block mb-1">Medicines</label>
<textarea name="medicines" rows="4" class="w-full bg-surface border border-outline-variant rounded-lg px-4 py-2 text-body-md" placeholder="Medicine name, dosage, duration..."><?= $edit_prescription ? htmlspecialchars($edit_prescription['medicines']) : '' ?></textarea>
</div>
<div class="md:col-span-2">
<label class="text-label-sm font-label-sm text-on-surface-variant block mb-1">Advice</label>
<textarea name="advice" rows="3" class="w-full bg-surface border border-outline-variant rounded-lg px-4 py-2 text-body-md" placeholder="Dietary recommendations, lifestyle advice..."><?= $edit_prescription ? htmlspecialchars($edit_prescription['advice']) : '' ?></textarea>
</div>
<div>
<label class="text-label-sm font-label-sm text-on-surface-variant block mb-1">Follow-up Date</label>
<input type="date" name="follow_up_date" class="w-full bg-surface border border-outline-variant rounded-lg px-4 py-2 text-body-md" value="<?= $edit_prescription && $edit_prescription['follow_up_date'] ? htmlspecialchars($edit_prescription['follow_up_date']) : '' ?>">
</div>
<div class="md:col-span-2 flex gap-4">
<button type="submit" name="save_prescription" class="bg-primary text-on-primary px-6 py-3 rounded-lg font-label-md hover:opacity-90 transition-all"><?= $edit_prescription ? 'Update' : 'Save' ?></button>
<a href="prescriptions.php" class="border border-outline-variant text-on-surface-variant px-6 py-3 rounded-lg font-label-md hover:bg-surface-container transition-all">Cancel</a>
</div>
</form>
</section>
<?php endif; ?>

<section class="bg-surface-container-lowest rounded-xl border border-outline-variant overflow-hidden">
<div class="px-6 py-4 border-b border-outline-variant flex flex-wrap justify-between items-center gap-4">
<form method="GET" class="flex gap-4 flex-wrap items-center">
<div class="relative">
<span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-sm">search</span>
<input class="bg-surface border border-outline-variant text-label-sm rounded-lg focus:ring-primary pl-9 py-2 w-56" name="search" placeholder="Search patient or doctor..." value="<?= htmlspecialchars($search) ?>" type="text"/>
</div>
<input type="date" name="filter_date" value="<?= htmlspecialchars($filter_date) ?>" class="bg-surface border border-outline-variant text-label-sm rounded-lg focus:ring-primary px-3 py-2">
<button class="text-label-sm text-primary hover:underline" type="submit">Filter</button>
<?php if ($search || $filter_date): ?>
<a href="prescriptions.php" class="text-label-sm text-on-surface-variant hover:underline">Clear</a>
<?php endif; ?>
</form>
</div>
<table class="w-full text-left">
<thead>
<tr class="bg-surface-container-low text-on-surface-variant">
<th class="px-6 py-4 text-label-sm uppercase tracking-wider">Patient</th>
<th class="px-6 py-4 text-label-sm uppercase tracking-wider">Doctor</th>
<th class="px-6 py-4 text-label-sm uppercase tracking-wider">Diagnosis</th>
<th class="px-6 py-4 text-label-sm uppercase tracking-wider">Date</th>
<th class="px-6 py-4 text-label-sm uppercase tracking-wider">Follow-up</th>
<th class="px-6 py-4 text-label-sm uppercase tracking-wider">Actions</th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant">
<?php foreach ($prescriptions as $p): ?>
<tr>
<td class="px-6 py-4 text-body-md font-bold text-on-surface"><?= htmlspecialchars($p['patient_name']) ?></td>
<td class="px-6 py-4 text-body-md"><?= htmlspecialchars($p['doctor_name']) ?></td>
<td class="px-6 py-4 text-body-md max-w-xs truncate"><?= htmlspecialchars($p['diagnosis'] ?: '—') ?></td>
<td class="px-6 py-4 text-body-md"><?= date('M d, Y', strtotime($p['created_at'])) ?></td>
<td class="px-6 py-4 text-body-md"><?= $p['follow_up_date'] ? date('M d, Y', strtotime($p['follow_up_date'])) : '—' ?></td>
<td class="px-6 py-4">
<div class="flex items-center gap-2">
<a href="?edit=<?= $p['id'] ?>" class="text-label-sm bg-surface-container-high text-on-surface px-3 py-1 rounded hover:bg-surface-container-highest transition-all">Edit</a>
<form method="POST" onsubmit="return confirm('Delete this prescription?')">
<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
<input type="hidden" name="prescription_id" value="<?= $p['id'] ?>">
<button type="submit" name="delete_prescription" class="text-label-sm bg-error-container text-on-error-container px-3 py-1 rounded hover:opacity-90 transition-all">Delete</button>
</form>
</div>
</td>
</tr>
<?php endforeach; ?>
<?php if (empty($prescriptions)): ?>
<tr><td colspan="6" class="px-6 py-8 text-center text-on-surface-variant">No prescriptions found.</td></tr>
<?php endif; ?>
</tbody>
</table>
<div class="px-6 py-4 border-t border-outline-variant flex justify-between items-center text-label-sm text-on-surface-variant">
<span>Showing <?= count($prescriptions) ?> of <?= $filtered_total ?></span>
<div class="flex gap-2">
<?php if ($page > 1): ?><a class="px-3 py-1 rounded bg-surface-container-high hover:bg-surface-container-highest transition-colors" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&filter_date=<?= urlencode($filter_date) ?>">Previous</a><?php endif; ?>
<span class="px-3 py-1 rounded bg-primary text-on-primary"><?= $page ?></span>
<?php if ($page < $total_pages): ?><a class="px-3 py-1 rounded bg-surface-container-high hover:bg-surface-container-highest transition-colors" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&filter_date=<?= urlencode($filter_date) ?>">Next</a><?php endif; ?>
</div>
</div>
</section>
</main>
</body>
</html>
