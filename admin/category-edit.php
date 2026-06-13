<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/includes/admin-crud.php';

admin_require_auth();

$db = getDB();
$page_title = 'Edit Category';
$active_page = 'categories';
$error = '';
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

$stmt = $db->prepare('SELECT * FROM categories WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$form_category = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$form_category) {
    header('Location: categories.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $error = 'Security check failed. Please try again.';
    } else {
        try {
            $name = trim($_POST['name'] ?? '');
            $slug = trim($_POST['slug'] ?? '') ?: admin_slug($name);
            $image_url = admin_upload_image('image_file', 'categories', $form_category['image_url'] ?? null);

            if ($name === '' || $slug === '') {
                throw new RuntimeException('Name and slug are required.');
            }

            $stmt = $db->prepare("UPDATE categories SET name = ?, slug = ?, image_url = ? WHERE id = ?");
            $stmt->bind_param('sssi', $name, $slug, $image_url, $id);
            $stmt->execute();
            header('Location: categories.php');
            exit;
        } catch (Throwable $e) {
            $error = $e->getMessage();
            $form_category = array_merge($form_category, $_POST);
        }
    }
}
?>
<?php require_once __DIR__ . '/includes/head.php'; ?>
<body class="bg-surface font-body-md text-on-surface flex min-h-screen">
<?php require_once __DIR__ . '/includes/sidebar.php'; ?>
<main class="flex-grow ml-64 p-margin-desktop bg-surface min-h-screen">
<header class="flex justify-between items-end mb-8">
<div>
<h2 class="text-display-lg font-display-lg text-primary">Edit Category</h2>
<p class="text-body-lg text-on-surface-variant mt-2"><?= htmlspecialchars($form_category['name']) ?></p>
</div>
<a href="categories.php" class="text-label-sm text-on-surface-variant hover:underline flex items-center gap-1"><span class="material-symbols-outlined text-sm">arrow_back</span> Categories</a>
</header>
<?php if ($error): ?><div class="mb-6 rounded-lg bg-error-container text-on-error-container px-4 py-3 text-body-md"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<section class="bg-surface-container-lowest rounded-xl border border-outline-variant p-6">
<?php $submit_label = 'Update Category'; require __DIR__ . '/includes/category-form.php'; ?>
</section>
</main>
</body>
</html>
