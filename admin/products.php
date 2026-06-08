<?php
require_once __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$db = getDB();
$page_title = 'Products';
$active_page = 'products';

function generateSlug($name) {
    return preg_replace('/[^a-z0-9-]+/', '-', strtolower(trim($name)));
}

// Delete product
if (isset($_GET['delete']) && $_SERVER['REQUEST_METHOD'] === 'POST' && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    $stmt = $db->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param('i', $_GET['delete']);
    $stmt->execute();
    header('Location: products.php');
    exit;
}

// Add product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product']) && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    $name = trim($_POST['name']);
    $slug = trim($_POST['slug']) ?: generateSlug($name);
    $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $description = trim($_POST['description']);
    $price = (float)$_POST['price'];
    $compare_price = ($_POST['compare_price'] ?? '') !== '' ? (float)$_POST['compare_price'] : null;
    $stock = (int)($_POST['stock'] ?? 0);
    $image_url = trim($_POST['image_url'] ?? '');
    $is_bestseller = isset($_POST['is_bestseller']) ? 1 : 0;

    $stmt = $db->prepare("INSERT INTO products (name, slug, category_id, description, price, compare_price, stock, image_url, is_bestseller) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('ssisddiss', $name, $slug, $category_id, $description, $price, $compare_price, $stock, $image_url, $is_bestseller);
    $stmt->execute();
    header('Location: products.php');
    exit;
}

// Edit product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_product']) && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    $id = (int)$_POST['product_id'];
    $name = trim($_POST['name']);
    $slug = trim($_POST['slug']) ?: generateSlug($name);
    $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $description = trim($_POST['description']);
    $price = (float)$_POST['price'];
    $compare_price = ($_POST['compare_price'] ?? '') !== '' ? (float)$_POST['compare_price'] : null;
    $stock = (int)($_POST['stock'] ?? 0);
    $image_url = trim($_POST['image_url'] ?? '');
    $is_bestseller = isset($_POST['is_bestseller']) ? 1 : 0;

    $stmt = $db->prepare("UPDATE products SET name=?, slug=?, category_id=?, description=?, price=?, compare_price=?, stock=?, image_url=?, is_bestseller=? WHERE id=?");
    $stmt->bind_param('ssisddissi', $name, $slug, $category_id, $description, $price, $compare_price, $stock, $image_url, $is_bestseller, $id);
    $stmt->execute();
    header('Location: products.php');
    exit;
}

// Fetch categories for dropdown
$categories = $db->query("SELECT id, name FROM categories ORDER BY name")->fetch_all(MYSQLI_ASSOC);

// Fetch product for editing
$edit_product = null;
if (isset($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param('i', $_GET['edit']);
    $stmt->execute();
    $edit_product = $stmt->get_result()->fetch_assoc();
    if (!$edit_product) {
        header('Location: products.php');
        exit;
    }
}

// Stats
$total_products = $db->query("SELECT COUNT(*) FROM products")->fetch_row()[0];
$total_categories = $db->query("SELECT COUNT(*) FROM categories")->fetch_row()[0];
$out_of_stock = $db->query("SELECT COUNT(*) FROM products WHERE stock = 0 OR stock IS NULL")->fetch_row()[0];
$bestseller_count = $db->query("SELECT COUNT(*) FROM products WHERE is_bestseller = 1")->fetch_row()[0];

// List with search, filter, pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 20;
$search = trim($_GET['search'] ?? '');
$cat_filter = $_GET['category'] ?? '';

$where_conds = [];
$bind_params = [];
$bind_types = '';

if ($search !== '') {
    $where_conds[] = "p.name LIKE ?";
    $bind_params[] = "%$search%";
    $bind_types .= 's';
}
if ($cat_filter !== '') {
    $where_conds[] = "p.category_id = ?";
    $bind_params[] = (int)$cat_filter;
    $bind_types .= 'i';
}
$where_sql = $where_conds ? 'WHERE ' . implode(' AND ', $where_conds) : '';

$count_stmt = $db->prepare("SELECT COUNT(*) FROM products p $where_sql");
if ($bind_params) {
    $count_stmt->bind_param($bind_types, ...$bind_params);
}
$count_stmt->execute();
$total = $count_stmt->get_result()->fetch_row()[0];
$total_pages = max(1, ceil($total / $per_page));
$offset = ($page - 1) * $per_page;

$query = "SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id $where_sql ORDER BY p.created_at DESC LIMIT ? OFFSET ?";
$stmt = $db->prepare($query);
$all_params = array_merge($bind_params, [$per_page, $offset]);
$all_types = $bind_types . 'ii';
if ($all_types !== 'ii') {
    $stmt->bind_param($all_types, ...$all_params);
} else {
    $stmt->bind_param('ii', $per_page, $offset);
}
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$show_form = isset($_GET['add']) || isset($_GET['edit']);
$form_title = isset($_GET['edit']) ? 'Edit Product' : 'Add New Product';
$form_action = isset($_GET['edit']) ? 'edit_product' : 'add_product';
$form_product = $edit_product ?? [];
?>
<?php require_once __DIR__ . '/includes/head.php'; ?>
<body class="bg-surface font-body-md text-on-surface flex min-h-screen">
<?php require_once __DIR__ . '/includes/sidebar.php'; ?>
<main class="flex-grow ml-64 p-margin-desktop bg-surface min-h-screen">

<header class="flex justify-between items-end mb-8">
<div>
<h2 class="text-display-lg font-display-lg text-primary">Products</h2>
<p class="text-body-lg text-on-surface-variant mt-2">Manage your Ayurvedic product catalogue.</p>
</div>
<?php if (!$show_form): ?>
<a href="?add=1" class="inline-flex items-center gap-2 bg-primary text-on-primary px-5 py-2.5 rounded-lg text-label-sm hover:opacity-90 transition-opacity"><span class="material-symbols-outlined text-sm">add</span> Add New Product</a>
<?php endif; ?>
</header>

<section class="grid grid-cols-1 md:grid-cols-4 gap-gutter mb-8">
<div class="bento-card bg-surface-container-lowest p-5 rounded-xl border border-outline-variant">
<span class="text-label-sm text-on-surface-variant uppercase tracking-widest">Total Products</span>
<p class="text-headline-md text-on-surface mt-1"><?= number_format($total_products) ?></p>
</div>
<div class="bento-card bg-surface-container-lowest p-5 rounded-xl border border-outline-variant">
<span class="text-label-sm text-on-surface-variant uppercase tracking-widest">Categories</span>
<p class="text-headline-md text-primary mt-1"><?= number_format($total_categories) ?></p>
</div>
<div class="bento-card bg-surface-container-lowest p-5 rounded-xl border border-outline-variant">
<span class="text-label-sm text-on-surface-variant uppercase tracking-widest">Out of Stock</span>
<p class="text-headline-md text-on-error-container mt-1"><?= $out_of_stock ?></p>
</div>
<div class="bento-card bg-surface-container-lowest p-5 rounded-xl border border-outline-variant">
<span class="text-label-sm text-on-surface-variant uppercase tracking-widest">Bestsellers</span>
<p class="text-headline-md text-secondary mt-1"><?= $bestseller_count ?></p>
</div>
</section>

<?php if ($show_form): ?>
<section class="bg-surface-container-lowest rounded-xl border border-outline-variant p-6 mb-8">
<div class="flex items-center justify-between mb-6">
<h3 class="text-headline-md text-primary"><?= $form_title ?></h3>
<a href="products.php" class="text-label-sm text-on-surface-variant hover:underline flex items-center gap-1"><span class="material-symbols-outlined text-sm">close</span> Cancel</a>
</div>
<form method="POST" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-5">
<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
<input type="hidden" name="<?= $form_action ?>" value="1">
<?php if (isset($_GET['edit'])): ?>
<input type="hidden" name="product_id" value="<?= (int)$form_product['id'] ?>">
<?php endif; ?>

<div>
<label class="text-label-sm text-on-surface-variant uppercase tracking-widest block mb-1">Product Name <span class="text-error">*</span></label>
<input type="text" name="name" required value="<?= htmlspecialchars($form_product['name'] ?? '') ?>" oninput="document.getElementById('slug-input').value = this.value.toLowerCase().replace(/[^a-z0-9-]+/g,'-').replace(/^-+|-+$/g,'')" class="w-full bg-surface border border-outline-variant text-label-sm rounded-lg focus:ring-primary px-3 py-2.5">
</div>

<div>
<label class="text-label-sm text-on-surface-variant uppercase tracking-widest block mb-1">Slug</label>
<input type="text" name="slug" id="slug-input" value="<?= htmlspecialchars($form_product['slug'] ?? '') ?>" class="w-full bg-surface border border-outline-variant text-label-sm rounded-lg focus:ring-primary px-3 py-2.5">
</div>

<div>
<label class="text-label-sm text-on-surface-variant uppercase tracking-widest block mb-1">Category</label>
<select name="category_id" class="w-full bg-surface border border-outline-variant text-label-sm rounded-lg focus:ring-primary px-3 py-2.5">
<option value="">— No Category —</option>
<?php foreach ($categories as $cat): ?>
<option value="<?= $cat['id'] ?>" <?= ((int)($form_product['category_id'] ?? 0) === (int)$cat['id']) ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
<?php endforeach; ?>
</select>
</div>

<div class="md:col-span-2 lg:col-span-3">
<label class="text-label-sm text-on-surface-variant uppercase tracking-widest block mb-1">Description</label>
<textarea name="description" rows="4" class="w-full bg-surface border border-outline-variant text-label-sm rounded-lg focus:ring-primary px-3 py-2.5"><?= htmlspecialchars($form_product['description'] ?? '') ?></textarea>
</div>

<div>
<label class="text-label-sm text-on-surface-variant uppercase tracking-widest block mb-1">Price (₹) <span class="text-error">*</span></label>
<input type="number" name="price" step="0.01" min="0" required value="<?= htmlspecialchars($form_product['price'] ?? '') ?>" class="w-full bg-surface border border-outline-variant text-label-sm rounded-lg focus:ring-primary px-3 py-2.5">
</div>

<div>
<label class="text-label-sm text-on-surface-variant uppercase tracking-widest block mb-1">Compare Price (₹)</label>
<input type="number" name="compare_price" step="0.01" min="0" value="<?= htmlspecialchars($form_product['compare_price'] ?? '') ?>" class="w-full bg-surface border border-outline-variant text-label-sm rounded-lg focus:ring-primary px-3 py-2.5">
</div>

<div>
<label class="text-label-sm text-on-surface-variant uppercase tracking-widest block mb-1">Stock</label>
<input type="number" name="stock" min="0" value="<?= (int)($form_product['stock'] ?? 0) ?>" class="w-full bg-surface border border-outline-variant text-label-sm rounded-lg focus:ring-primary px-3 py-2.5">
</div>

<div>
<label class="text-label-sm text-on-surface-variant uppercase tracking-widest block mb-1">Image URL</label>
<input type="url" name="image_url" value="<?= htmlspecialchars($form_product['image_url'] ?? '') ?>" class="w-full bg-surface border border-outline-variant text-label-sm rounded-lg focus:ring-primary px-3 py-2.5">
</div>

<div class="flex items-center gap-3 pt-6">
<input type="hidden" name="is_bestseller" value="0">
<input type="checkbox" name="is_bestseller" value="1" id="is_bestseller" <?= ($form_product['is_bestseller'] ?? 0) ? 'checked' : '' ?> class="rounded border-outline-variant text-primary focus:ring-primary">
<label for="is_bestseller" class="text-label-sm text-on-surface-variant">Mark as Bestseller</label>
</div>

<div class="md:col-span-2 lg:col-span-3 flex gap-3 pt-2">
<button type="submit" class="bg-primary text-on-primary px-6 py-2.5 rounded-lg text-label-sm hover:opacity-90 transition-opacity"><?= isset($_GET['edit']) ? 'Update Product' : 'Add Product' ?></button>
<a href="products.php" class="border border-outline-variant text-on-surface-variant px-6 py-2.5 rounded-lg text-label-sm hover:bg-surface-container-high transition-colors">Cancel</a>
</div>
</form>
</section>
<?php endif; ?>

<section class="bg-surface-container-lowest rounded-xl border border-outline-variant overflow-hidden">
<div class="px-6 py-4 border-b border-outline-variant flex flex-wrap justify-between items-center gap-4">
<form method="GET" class="flex gap-4 flex-wrap items-center">
<select name="category" class="bg-surface border border-outline-variant text-label-sm rounded-lg focus:ring-primary px-3 py-2" onchange="this.form.submit()">
<option value="">All Categories</option>
<?php foreach ($categories as $cat): ?>
<option value="<?= $cat['id'] ?>" <?= $cat_filter === (string)$cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
<?php endforeach; ?>
</select>
<div class="relative">
<span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-sm">search</span>
<input class="bg-surface border border-outline-variant text-label-sm rounded-lg focus:ring-primary pl-9 py-2 w-56" name="search" placeholder="Search product name..." value="<?= htmlspecialchars($search) ?>" type="text">
</div>
<button class="text-label-sm text-primary hover:underline" type="submit">Search</button>
<?php if ($search || $cat_filter): ?>
<a href="products.php" class="text-label-sm text-on-surface-variant hover:underline">Clear</a>
<?php endif; ?>
</form>
</div>

<div class="overflow-x-auto">
<table class="w-full text-left">
<thead>
<tr class="bg-surface-container-low text-on-surface-variant">
<th class="px-6 py-4 text-label-sm uppercase tracking-wider">Name</th>
<th class="px-6 py-4 text-label-sm uppercase tracking-wider">Category</th>
<th class="px-6 py-4 text-label-sm uppercase tracking-wider">Price</th>
<th class="px-6 py-4 text-label-sm uppercase tracking-wider">Stock</th>
<th class="px-6 py-4 text-label-sm uppercase tracking-wider">Rating</th>
<th class="px-6 py-4 text-label-sm uppercase tracking-wider">Bestseller</th>
<th class="px-6 py-4 text-label-sm uppercase tracking-wider">Actions</th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant">
<?php foreach ($products as $p): ?>
<?php
$rating_class = match(true) {
    ($p['rating'] ?? 0) >= 4 => 'bg-primary-fixed text-on-primary-fixed',
    ($p['rating'] ?? 0) >= 2 => 'bg-secondary-container text-on-secondary-container',
    default => 'bg-surface-container-high text-on-surface-variant'
};
?>
<tr>
<td class="px-6 py-4">
<div class="flex items-center gap-3">
<?php if ($p['image_url']): ?>
<img src="<?= htmlspecialchars($p['image_url']) ?>" alt="" class="w-10 h-10 rounded-lg object-cover bg-surface-container-high flex-shrink-0">
<?php else: ?>
<div class="w-10 h-10 rounded-lg bg-surface-container-high flex items-center justify-center flex-shrink-0"><span class="material-symbols-outlined text-sm text-on-surface-variant">medication</span></div>
<?php endif; ?>
<div>
<p class="text-body-md font-bold text-on-surface"><?= htmlspecialchars($p['name']) ?></p>
<p class="text-label-sm text-on-surface-variant"><?= htmlspecialchars($p['slug']) ?></p>
</div>
</div>
</td>
<td class="px-6 py-4 text-body-md"><?= htmlspecialchars($p['category_name'] ?? '—') ?></td>
<td class="px-6 py-4">
<p class="text-body-md font-bold text-on-surface">₹<?= number_format($p['price'], 2) ?></p>
<?php if ($p['compare_price']): ?>
<p class="text-label-sm text-on-surface-variant line-through">₹<?= number_format($p['compare_price'], 2) ?></p>
<?php endif; ?>
</td>
<td class="px-6 py-4">
<span class="px-3 py-1 rounded-full text-label-sm <?= ($p['stock'] ?? 0) > 0 ? 'bg-primary-fixed text-on-primary-fixed' : 'bg-error-container text-on-error-container' ?>">
<?= ($p['stock'] ?? 0) > 0 ? htmlspecialchars($p['stock']) . ' in stock' : 'Out of stock' ?>
</span>
</td>
<td class="px-6 py-4"><span class="px-3 py-1 rounded-full text-label-sm <?= $rating_class ?>"><?= number_format($p['rating'] ?? 0, 1) ?> (<?= (int)($p['reviews_count'] ?? 0) ?>)</span></td>
<td class="px-6 py-4"><?= $p['is_bestseller'] ? '<span class="material-symbols-outlined text-secondary">verified</span>' : '<span class="text-on-surface-variant opacity-30">—</span>' ?></td>
<td class="px-6 py-4">
<div class="flex items-center gap-3">
<a href="?edit=<?= $p['id'] ?>" class="text-label-sm text-primary hover:underline">Edit</a>
<form method="POST" action="?delete=<?= $p['id'] ?>" onsubmit="return confirm('Are you sure you want to delete «<?= htmlspecialchars($p['name'], ENT_QUOTES) ?>»? This cannot be undone.')" class="inline">
<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
<button type="submit" class="text-label-sm text-error hover:underline">Delete</button>
</form>
</div>
</td>
</tr>
<?php endforeach; ?>
<?php if (empty($products)): ?>
<tr><td colspan="7" class="px-6 py-8 text-center text-on-surface-variant">No products found.</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>

<div class="px-6 py-4 border-t border-outline-variant flex justify-between items-center text-label-sm text-on-surface-variant">
<span>Showing <?= count($products) ?> of <?= number_format($total) ?></span>
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
