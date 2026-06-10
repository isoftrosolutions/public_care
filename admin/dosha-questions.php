<?php
require_once __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$db = getDB();
$page_title = 'Dosha Questions';
$active_page = 'dosha-questions';

// Toggle active/inactive
if (isset($_GET['toggle']) && $_SERVER['REQUEST_METHOD'] === 'POST' && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    $stmt = $db->prepare("UPDATE dosha_questions SET active = NOT active WHERE id = ?");
    $stmt->bind_param('i', $_GET['toggle']);
    $stmt->execute();
    header('Location: dosha-questions.php');
    exit;
}

// Delete question
if (isset($_GET['delete']) && $_SERVER['REQUEST_METHOD'] === 'POST' && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    $stmt = $db->prepare("DELETE FROM dosha_questions WHERE id = ?");
    $stmt->bind_param('i', $_GET['delete']);
    $stmt->execute();
    header('Location: dosha-questions.php');
    exit;
}

// Add question
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_question']) && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    $question_text = trim($_POST['question_text']);
    $category = $_POST['category'];
    $weight = (int)($_POST['weight'] ?? 1);
    $display_order = (int)($_POST['display_order'] ?? 0);
    $active = isset($_POST['active']) ? 1 : 0;

    $stmt = $db->prepare("INSERT INTO dosha_questions (question_text, category, weight, display_order, active) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param('ssiii', $question_text, $category, $weight, $display_order, $active);
    $stmt->execute();
    header('Location: dosha-questions.php');
    exit;
}

// Edit question
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_question']) && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    $id = (int)$_POST['question_id'];
    $question_text = trim($_POST['question_text']);
    $category = $_POST['category'];
    $weight = (int)($_POST['weight'] ?? 1);
    $display_order = (int)($_POST['display_order'] ?? 0);
    $active = isset($_POST['active']) ? 1 : 0;

    $stmt = $db->prepare("UPDATE dosha_questions SET question_text=?, category=?, weight=?, display_order=?, active=? WHERE id=?");
    $stmt->bind_param('ssiiii', $question_text, $category, $weight, $display_order, $active, $id);
    $stmt->execute();
    header('Location: dosha-questions.php');
    exit;
}

// Fetch question for editing
$edit_question = null;
if (isset($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM dosha_questions WHERE id = ?");
    $stmt->bind_param('i', $_GET['edit']);
    $stmt->execute();
    $edit_question = $stmt->get_result()->fetch_assoc();
    if (!$edit_question) {
        header('Location: dosha-questions.php');
        exit;
    }
}

// Stats
$total_questions = $db->query("SELECT COUNT(*) FROM dosha_questions")->fetch_row()[0];
$active_count = $db->query("SELECT COUNT(*) FROM dosha_questions WHERE active = TRUE")->fetch_row()[0];
$inactive_count = $db->query("SELECT COUNT(*) FROM dosha_questions WHERE active = FALSE")->fetch_row()[0];

$cat_counts = $db->query("SELECT category, COUNT(*) AS cnt FROM dosha_questions GROUP BY category")->fetch_all(MYSQLI_ASSOC);

// List with search/filter
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 20;
$search = trim($_GET['search'] ?? '');
$cat_filter = $_GET['category'] ?? '';

$where_conds = [];
$bind_params = [];
$bind_types = '';

if ($search !== '') {
    $where_conds[] = "question_text LIKE ?";
    $bind_params[] = "%$search%";
    $bind_types .= 's';
}
if ($cat_filter !== '') {
    $where_conds[] = "category = ?";
    $bind_params[] = $cat_filter;
    $bind_types .= 's';
}
$where_sql = $where_conds ? 'WHERE ' . implode(' AND ', $where_conds) : '';

$count_stmt = $db->prepare("SELECT COUNT(*) FROM dosha_questions $where_sql");
if ($bind_params) {
    $count_stmt->bind_param($bind_types, ...$bind_params);
}
$count_stmt->execute();
$total = $count_stmt->get_result()->fetch_row()[0];
$total_pages = max(1, ceil($total / $per_page));
$offset = ($page - 1) * $per_page;

$query = "SELECT * FROM dosha_questions $where_sql ORDER BY display_order ASC, created_at DESC LIMIT ? OFFSET ?";
$stmt = $db->prepare($query);
$all_params = array_merge($bind_params, [$per_page, $offset]);
$all_types = $bind_types . 'ii';
if ($all_types !== 'ii') {
    $stmt->bind_param($all_types, ...$all_params);
} else {
    $stmt->bind_param('ii', $per_page, $offset);
}
$stmt->execute();
$questions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$show_form = isset($_GET['add']) || isset($_GET['edit']);
$form_title = isset($_GET['edit']) ? 'Edit Question' : 'Add New Question';
$form_action = isset($_GET['edit']) ? 'edit_question' : 'add_question';
$form_question = $edit_question ?? [];
?>
<?php require_once __DIR__ . '/includes/head.php'; ?>
<body class="bg-surface font-body-md text-on-surface flex min-h-screen">
<?php require_once __DIR__ . '/includes/sidebar.php'; ?>
<main class="flex-grow ml-64 p-margin-desktop bg-surface min-h-screen">

<header class="flex justify-between items-end mb-8">
<div>
<h2 class="text-display-lg font-display-lg text-primary">Dosha Questions</h2>
<p class="text-body-lg text-on-surface-variant mt-2">Manage AI body analysis quiz questions (18 total: 6 per dosha).</p>
</div>
<?php if (!$show_form): ?>
<a href="?add=1" class="inline-flex items-center gap-2 bg-primary text-on-primary px-5 py-2.5 rounded-lg text-label-sm hover:opacity-90 transition-opacity"><span class="material-symbols-outlined text-sm">add</span> Add New Question</a>
<?php endif; ?>
</header>

<section class="grid grid-cols-1 md:grid-cols-4 gap-gutter mb-8">
<div class="bento-card bg-surface-container-lowest p-5 rounded-xl border border-outline-variant">
<span class="text-label-sm text-on-surface-variant uppercase tracking-widest">Total Questions</span>
<p class="text-headline-md text-on-surface mt-1"><?= number_format($total_questions) ?></p>
</div>
<div class="bento-card bg-surface-container-lowest p-5 rounded-xl border border-outline-variant">
<span class="text-label-sm text-on-surface-variant uppercase tracking-widest">Active</span>
<p class="text-headline-md text-primary mt-1"><?= number_format($active_count) ?></p>
</div>
<div class="bento-card bg-surface-container-lowest p-5 rounded-xl border border-outline-variant">
<span class="text-label-sm text-on-surface-variant uppercase tracking-widest">Inactive</span>
<p class="text-headline-md text-on-error-container mt-1"><?= $inactive_count ?></p>
</div>
<div class="bento-card bg-surface-container-lowest p-5 rounded-xl border border-outline-variant">
<span class="text-label-sm text-on-surface-variant uppercase tracking-widest">By Category</span>
<p class="text-headline-md text-secondary mt-1">
    <?php foreach ($cat_counts as $cc): ?>
    <span class="text-sm block"><?= htmlspecialchars(ucfirst($cc['category'])) ?>: <?= $cc['cnt'] ?></span>
    <?php endforeach; ?>
</p>
</div>
</section>

<?php if ($show_form): ?>
<section class="bg-surface-container-lowest rounded-xl border border-outline-variant p-6 mb-8">
<div class="flex items-center justify-between mb-6">
<h3 class="text-headline-md text-primary"><?= $form_title ?></h3>
<a href="dosha-questions.php" class="text-label-sm text-on-surface-variant hover:underline flex items-center gap-1"><span class="material-symbols-outlined text-sm">close</span> Cancel</a>
</div>
<form method="POST" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-5">
<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
<input type="hidden" name="<?= $form_action ?>" value="1">
<?php if (isset($_GET['edit'])): ?>
<input type="hidden" name="question_id" value="<?= (int)$form_question['id'] ?>">
<?php endif; ?>

<div class="md:col-span-2 lg:col-span-3">
<label class="text-label-sm text-on-surface-variant uppercase tracking-widest block mb-1">Question Text (Hindi) <span class="text-error">*</span></label>
<textarea name="question_text" rows="3" required class="w-full bg-surface border border-outline-variant text-label-sm rounded-lg focus:ring-primary px-3 py-2.5"><?= htmlspecialchars($form_question['question_text'] ?? '') ?></textarea>
</div>

<div>
<label class="text-label-sm text-on-surface-variant uppercase tracking-widest block mb-1">Category <span class="text-error">*</span></label>
<select name="category" required class="w-full bg-surface border border-outline-variant text-label-sm rounded-lg focus:ring-primary px-3 py-2.5">
<option value="vata" <?= ($form_question['category'] ?? '') === 'vata' ? 'selected' : '' ?>>Vata (वात)</option>
<option value="pitta" <?= ($form_question['category'] ?? '') === 'pitta' ? 'selected' : '' ?>>Pitta (पित्त)</option>
<option value="kapha" <?= ($form_question['category'] ?? '') === 'kapha' ? 'selected' : '' ?>>Kapha (कफ)</option>
</select>
</div>

<div>
<label class="text-label-sm text-on-surface-variant uppercase tracking-widest block mb-1">Weight</label>
<select name="weight" class="w-full bg-surface border border-outline-variant text-label-sm rounded-lg focus:ring-primary px-3 py-2.5">
<option value="1" <?= ((int)($form_question['weight'] ?? 1) === 1) ? 'selected' : '' ?>>1 (Normal)</option>
<option value="2" <?= ((int)($form_question['weight'] ?? 1) === 2) ? 'selected' : '' ?>>2 (Important)</option>
<option value="3" <?= ((int)($form_question['weight'] ?? 1) === 3) ? 'selected' : '' ?>>3 (Critical)</option>
</select>
</div>

<div>
<label class="text-label-sm text-on-surface-variant uppercase tracking-widest block mb-1">Display Order</label>
<input type="number" name="display_order" min="0" value="<?= (int)($form_question['display_order'] ?? 0) ?>" class="w-full bg-surface border border-outline-variant text-label-sm rounded-lg focus:ring-primary px-3 py-2.5">
</div>

<div class="flex items-center gap-3 pt-6">
<input type="hidden" name="active" value="0">
<input type="checkbox" name="active" value="1" id="active" <?= ($form_question['active'] ?? 1) ? 'checked' : '' ?> class="rounded border-outline-variant text-primary focus:ring-primary">
<label for="active" class="text-label-sm text-on-surface-variant">Active</label>
</div>

<div class="md:col-span-2 lg:col-span-3 flex gap-3 pt-2">
<button type="submit" class="bg-primary text-on-primary px-6 py-2.5 rounded-lg text-label-sm hover:opacity-90 transition-opacity"><?= isset($_GET['edit']) ? 'Update Question' : 'Add Question' ?></button>
<a href="dosha-questions.php" class="border border-outline-variant text-on-surface-variant px-6 py-2.5 rounded-lg text-label-sm hover:bg-surface-container-high transition-colors">Cancel</a>
</div>
</form>
</section>
<?php endif; ?>

<section class="bg-surface-container-lowest rounded-xl border border-outline-variant overflow-hidden">
<div class="px-6 py-4 border-b border-outline-variant flex flex-wrap justify-between items-center gap-4">
<form method="GET" class="flex gap-4 flex-wrap items-center">
<select name="category" class="bg-surface border border-outline-variant text-label-sm rounded-lg focus:ring-primary px-3 py-2" onchange="this.form.submit()">
<option value="">All Categories</option>
<option value="vata" <?= $cat_filter === 'vata' ? 'selected' : '' ?>>Vata (वात)</option>
<option value="pitta" <?= $cat_filter === 'pitta' ? 'selected' : '' ?>>Pitta (पित्त)</option>
<option value="kapha" <?= $cat_filter === 'kapha' ? 'selected' : '' ?>>Kapha (कफ)</option>
</select>
<div class="relative">
<span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-sm">search</span>
<input class="bg-surface border border-outline-variant text-label-sm rounded-lg focus:ring-primary pl-9 py-2 w-56" name="search" placeholder="Search question text..." value="<?= htmlspecialchars($search) ?>" type="text">
</div>
<button class="text-label-sm text-primary hover:underline" type="submit">Search</button>
<?php if ($search || $cat_filter): ?>
<a href="dosha-questions.php" class="text-label-sm text-on-surface-variant hover:underline">Clear</a>
<?php endif; ?>
</form>
</div>

<div class="overflow-x-auto">
<table class="w-full text-left">
<thead>
<tr class="bg-surface-container-low text-on-surface-variant">
<th class="px-6 py-4 text-label-sm uppercase tracking-wider">#</th>
<th class="px-6 py-4 text-label-sm uppercase tracking-wider">Question</th>
<th class="px-6 py-4 text-label-sm uppercase tracking-wider">Category</th>
<th class="px-6 py-4 text-label-sm uppercase tracking-wider">Weight</th>
<th class="px-6 py-4 text-label-sm uppercase tracking-wider">Order</th>
<th class="px-6 py-4 text-label-sm uppercase tracking-wider">Status</th>
<th class="px-6 py-4 text-label-sm uppercase tracking-wider">Actions</th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant">
<?php foreach ($questions as $q): ?>
<?php
$cat_class = match($q['category']) {
    'vata' => 'bg-blue-100 text-blue-700',
    'pitta' => 'bg-red-100 text-red-700',
    'kapha' => 'bg-green-100 text-green-700',
    default => 'bg-surface-container-high text-on-surface-variant'
};
$cat_label = match($q['category']) {
    'vata' => 'वात',
    'pitta' => 'पित्त',
    'kapha' => 'कफ',
    default => $q['category']
};
?>
<tr>
<td class="px-6 py-4 text-body-md text-on-surface-variant"><?= (int)$q['id'] ?></td>
<td class="px-6 py-4">
<p class="text-body-md font-semibold text-on-surface"><?= htmlspecialchars($q['question_text']) ?></p>
</td>
<td class="px-6 py-4"><span class="px-3 py-1 rounded-full text-label-sm <?= $cat_class ?>"><?= $cat_label ?></span></td>
<td class="px-6 py-4 text-body-md"><?= (int)$q['weight'] ?></td>
<td class="px-6 py-4 text-body-md"><?= (int)$q['display_order'] ?></td>
<td class="px-6 py-4">
<?php if ($q['active']): ?>
<span class="px-3 py-1 rounded-full text-label-sm bg-primary-fixed text-on-primary-fixed">Active</span>
<?php else: ?>
<span class="px-3 py-1 rounded-full text-label-sm bg-surface-container-high text-on-surface-variant">Inactive</span>
<?php endif; ?>
</td>
<td class="px-6 py-4">
<div class="flex items-center gap-3">
<a href="?edit=<?= $q['id'] ?>" class="text-label-sm text-primary hover:underline">Edit</a>
<form method="POST" action="?toggle=<?= $q['id'] ?>" class="inline">
<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
<button type="submit" class="text-label-sm text-secondary hover:underline"><?= $q['active'] ? 'Deactivate' : 'Activate' ?></button>
</form>
<form method="POST" action="?delete=<?= $q['id'] ?>" onsubmit="return confirm('Are you sure you want to delete question #<?= (int)$q['id'] ?>? This cannot be undone.')" class="inline">
<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
<button type="submit" class="text-label-sm text-error hover:underline">Delete</button>
</form>
</div>
</td>
</tr>
<?php endforeach; ?>
<?php if (empty($questions)): ?>
<tr><td colspan="7" class="px-6 py-8 text-center text-on-surface-variant">No questions found.</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>

<div class="px-6 py-4 border-t border-outline-variant flex justify-between items-center text-label-sm text-on-surface-variant">
<span>Showing <?= count($questions) ?> of <?= number_format($total) ?></span>
<div class="flex gap-2">
<?php if ($page > 1): ?><a class="px-3 py-1 rounded bg-surface-container-high hover:bg-surface-container-highest transition-colors" href="?page=<?= $page - 1 ?>&category=<?= urlencode($cat_filter) ?>&search=<?= urlencode($search) ?>">Previous</a><?php endif; ?>
<span class="px-3 py-1 rounded bg-primary text-on-primary"><?= $page ?></span>
<?php if ($page < $total_pages): ?><a class="px-3 py-1 rounded bg-surface-container-high hover:bg-surface-container-highest transition-colors" href="?page=<?= $page + 1 ?>&category=<?= urlencode($cat_filter) ?>&search=<?= urlencode($search) ?>">Next</a><?php endif; ?>
</div>
</div>
</section>

</main>
</body>
</html>
