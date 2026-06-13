<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/includes/admin-crud.php';

admin_require_auth();

$db = getDB();
$page_title = 'Add Category';
$active_page = 'categories';
$error = '';
$form_category = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $error = 'Security check failed. Please try again.';
    } else {
        try {
            $name = trim($_POST['name'] ?? '');
            $slug = trim($_POST['slug'] ?? '') ?: admin_slug($name);
            $image_url = admin_upload_image('image_file', 'categories');

            if ($name === '' || $slug === '') {
                throw new RuntimeException('Name and slug are required.');
            }

            $stmt = $db->prepare("INSERT INTO categories (name, slug, image_url) VALUES (?, ?, ?)");
            $stmt->bind_param('sss', $name, $slug, $image_url);
            $stmt->execute();
            header('Location: categories.php');
            exit;
        } catch (Throwable $e) {
            $error = $e->getMessage();
            $form_category = $_POST;
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
<h2 class="text-display-lg font-display-lg text-primary">Add Category</h2>
<p class="text-body-lg text-on-surface-variant mt-2">Add a new product category with a local uploaded image.</p>
</div>
<a href="categories.php" class="text-label-sm text-on-surface-variant hover:underline flex items-center gap-1"><span class="material-symbols-outlined text-sm">arrow_back</span> Categories</a>
</header>
<?php if ($error): ?><div class="mb-6 rounded-lg bg-error-container text-on-error-container px-4 py-3 text-body-md"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<section class="bg-surface-container-lowest rounded-xl border border-outline-variant p-6">
<?php $submit_label = 'Add Category'; require __DIR__ . '/includes/category-form.php'; ?>
</section>
</main>
</body>
</html>
