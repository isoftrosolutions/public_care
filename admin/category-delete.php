<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/includes/admin-crud.php';

admin_require_auth();

$db = getDB();
$page_title = 'Delete Category';
$active_page = 'categories';
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

$stmt = $db->prepare('SELECT c.*, COUNT(p.id) AS product_count FROM categories c LEFT JOIN products p ON c.id = p.category_id WHERE c.id = ? GROUP BY c.id');
$stmt->bind_param('i', $id);
$stmt->execute();
$category = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$category) {
    header('Location: categories.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $stmt = $db->prepare('DELETE FROM categories WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        admin_delete_local_upload($category['image_url'] ?? null);
    }
    header('Location: categories.php');
    exit;
}
?>
<?php require_once __DIR__ . '/includes/head.php'; ?>
<body class="bg-surface font-body-md text-on-surface flex min-h-screen">
<?php require_once __DIR__ . '/includes/sidebar.php'; ?>
<main class="flex-grow ml-64 p-margin-desktop bg-surface min-h-screen">
<header class="mb-8">
<h2 class="text-display-lg font-display-lg text-primary">Delete Category</h2>
<p class="text-body-lg text-on-surface-variant mt-2">Confirm before removing this category.</p>
</header>

<section class="bg-surface-container-lowest rounded-xl border border-outline-variant p-6 max-w-2xl">
<div class="flex items-start gap-4">
<?php if (!empty($category['image_url'])): ?>
<img src="<?= htmlspecialchars($category['image_url']) ?>" alt="" class="w-20 h-20 rounded-lg object-cover bg-surface-container-high">
<?php else: ?>
<div class="w-20 h-20 rounded-lg bg-surface-container-high flex items-center justify-center"><span class="material-symbols-outlined text-on-surface-variant">category</span></div>
<?php endif; ?>
<div>
<h3 class="text-headline-md text-on-surface"><?= htmlspecialchars($category['name']) ?></h3>
<p class="text-body-md text-on-surface-variant mt-1">Slug: <?= htmlspecialchars($category['slug']) ?></p>
<p class="text-body-md text-on-surface-variant"><?= (int)$category['product_count'] ?> product(s) in this category</p>
<p class="text-label-sm text-error mt-4">Products in this category will become uncategorized. This action cannot be undone.</p>
</div>
</div>
<form method="POST" class="flex gap-3 mt-8">
<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
<input type="hidden" name="id" value="<?= $id ?>">
<button type="submit" class="bg-error text-on-error px-6 py-2.5 rounded-lg text-label-sm hover:opacity-90 transition-opacity">Delete Category</button>
<a href="categories.php" class="border border-outline-variant text-on-surface-variant px-6 py-2.5 rounded-lg text-label-sm hover:bg-surface-container-high transition-colors">Cancel</a>
</form>
</section>
</main>
</body>
</html>
