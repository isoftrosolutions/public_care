<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/includes/admin-crud.php';

admin_require_auth();

$db = getDB();
$page_title = 'Edit Product';
$active_page = 'products';
$error = '';
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

$stmt = $db->prepare('SELECT * FROM products WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$form_product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$form_product) {
    header('Location: products.php');
    exit;
}

$categories = $db->query("SELECT id, name FROM categories ORDER BY name")->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $error = 'Security check failed. Please try again.';
    } else {
        try {
            $name = trim($_POST['name'] ?? '');
            $slug = trim($_POST['slug'] ?? '') ?: admin_slug($name);
            $category_id = ($_POST['category_id'] ?? '') !== '' ? (int)$_POST['category_id'] : null;
            $description = trim($_POST['description'] ?? '');
            $price = (float)($_POST['price'] ?? 0);
            $compare_price = ($_POST['compare_price'] ?? '') !== '' ? (float)$_POST['compare_price'] : null;
            $stock = (int)($_POST['stock'] ?? 0);
            $is_bestseller = isset($_POST['is_bestseller']) ? 1 : 0;
            $image_url = admin_upload_image('image_file', 'products', $form_product['image_url'] ?? null);

            if ($name === '') {
                throw new RuntimeException('Product name is required.');
            }

            $stmt = $db->prepare('UPDATE products SET name=?, slug=?, category_id=?, description=?, price=?, compare_price=?, stock=?, image_url=?, is_bestseller=? WHERE id=?');
            $stmt->bind_param('ssisddisii', $name, $slug, $category_id, $description, $price, $compare_price, $stock, $image_url, $is_bestseller, $id);
            $stmt->execute();
            header('Location: products.php');
            exit;
        } catch (Throwable $e) {
            $error = $e->getMessage();
            $form_product = array_merge($form_product, $_POST);
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
<h2 class="text-display-lg font-display-lg text-primary">Edit Product</h2>
<p class="text-body-lg text-on-surface-variant mt-2"><?= htmlspecialchars($form_product['name']) ?></p>
</div>
<a href="products.php" class="text-label-sm text-on-surface-variant hover:underline flex items-center gap-1"><span class="material-symbols-outlined text-sm">arrow_back</span> Products</a>
</header>
<?php if ($error): ?><div class="mb-6 rounded-lg bg-error-container text-on-error-container px-4 py-3 text-body-md"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<section class="bg-surface-container-lowest rounded-xl border border-outline-variant p-6">
<?php $submit_label = 'Update Product'; require __DIR__ . '/includes/product-form.php'; ?>
</section>
</main>
</body>
</html>
