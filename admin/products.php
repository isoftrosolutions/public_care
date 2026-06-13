<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/includes/admin-crud.php';

admin_require_auth();

$db = getDB();
$page_title = 'Products';
$active_page = 'products';

$categories = $db->query("SELECT id, name FROM categories ORDER BY name")->fetch_all(MYSQLI_ASSOC);
$total_products = (int)$db->query("SELECT COUNT(*) FROM products")->fetch_row()[0];
$total_categories = (int)$db->query("SELECT COUNT(*) FROM categories")->fetch_row()[0];
$out_of_stock = (int)$db->query("SELECT COUNT(*) FROM products WHERE stock = 0 OR stock IS NULL")->fetch_row()[0];
$bestseller_count = (int)$db->query("SELECT COUNT(*) FROM products WHERE is_bestseller = 1")->fetch_row()[0];

$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 20;
$search = trim($_GET['search'] ?? '');
$cat_filter = $_GET['category'] ?? '';

$where_conds = [];
$bind_params = [];
$bind_types = '';

if ($search !== '') {
    $where_conds[] = 'p.name LIKE ?';
    $bind_params[] = '%' . $search . '%';
    $bind_types .= 's';
}

if ($cat_filter !== '') {
    $where_conds[] = 'p.category_id = ?';
    $bind_params[] = (int)$cat_filter;
    $bind_types .= 'i';
}

$where_sql = $where_conds ? 'WHERE ' . implode(' AND ', $where_conds) : '';
$count_stmt = $db->prepare("SELECT COUNT(*) FROM products p $where_sql");
if ($bind_params) {
    $count_stmt->bind_param($bind_types, ...$bind_params);
}
$count_stmt->execute();
$total = (int)$count_stmt->get_result()->fetch_row()[0];
$total_pages = max(1, (int)ceil($total / $per_page));
$offset = ($page - 1) * $per_page;

$stmt = $db->prepare("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id $where_sql ORDER BY p.created_at DESC LIMIT ? OFFSET ?");
$all_params = array_merge($bind_params, [$per_page, $offset]);
$all_types = $bind_types . 'ii';
$stmt->bind_param($all_types, ...$all_params);
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<?php require_once __DIR__ . '/includes/head.php'; ?>
<body class="bg-surface font-body-md text-on-surface flex min-h-screen">
<?php require_once __DIR__ . '/includes/sidebar.php'; ?>
<main class="flex-grow ml-64 p-margin-desktop bg-surface min-h-screen">

<header class="flex justify-between items-end mb-8">
<div>
<h2 class="text-display-lg font-display-lg text-primary">Products</h2>
<p class="text-body-lg text-on-surface-variant mt-2">Manage the Ayurviro product catalogue.</p>
</div>
<a href="product-create.php" class="inline-flex items-center gap-2 bg-primary text-on-primary px-5 py-2.5 rounded-lg text-label-sm hover:opacity-90 transition-opacity"><span class="material-symbols-outlined text-sm">add</span> Create Product</a>
</header>

<section class="grid grid-cols-1 md:grid-cols-4 gap-gutter mb-8">
<div class="bento-card bg-surface-container-lowest p-5 rounded-xl border border-outline-variant"><span class="text-label-sm text-on-surface-variant uppercase tracking-widest">Total Products</span><p class="text-headline-md text-on-surface mt-1"><?= number_format($total_products) ?></p></div>
<div class="bento-card bg-surface-container-lowest p-5 rounded-xl border border-outline-variant"><span class="text-label-sm text-on-surface-variant uppercase tracking-widest">Categories</span><p class="text-headline-md text-primary mt-1"><?= number_format($total_categories) ?></p></div>
<div class="bento-card bg-surface-container-lowest p-5 rounded-xl border border-outline-variant"><span class="text-label-sm text-on-surface-variant uppercase tracking-widest">Out of Stock</span><p class="text-headline-md text-on-error-container mt-1"><?= $out_of_stock ?></p></div>
<div class="bento-card bg-surface-container-lowest p-5 rounded-xl border border-outline-variant"><span class="text-label-sm text-on-surface-variant uppercase tracking-widest">Bestsellers</span><p class="text-headline-md text-secondary mt-1"><?= $bestseller_count ?></p></div>
</section>

<section class="bg-surface-container-lowest rounded-xl border border-outline-variant overflow-hidden">
<div class="px-6 py-4 border-b border-outline-variant flex flex-wrap justify-between items-center gap-4">
<form method="GET" class="flex gap-4 flex-wrap items-center">
<select name="category" class="bg-surface border border-outline-variant text-label-sm rounded-lg focus:ring-primary px-3 py-2" onchange="this.form.submit()">
<option value="">All Categories</option>
<?php foreach ($categories as $cat): ?>
<option value="<?= (int)$cat['id'] ?>" <?= $cat_filter === (string)$cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
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
$rating = (float)($p['rating'] ?? 0);
$rating_class = $rating >= 4 ? 'bg-primary-fixed text-on-primary-fixed' : ($rating >= 2 ? 'bg-secondary-container text-on-secondary-container' : 'bg-surface-container-high text-on-surface-variant');
?>
<tr>
<td class="px-6 py-4">
<div class="flex items-center gap-3">
<?php if (!empty($p['image_url'])): ?>
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
<td class="px-6 py-4 text-body-md"><?= htmlspecialchars($p['category_name'] ?? '-') ?></td>
<td class="px-6 py-4">
<p class="text-body-md font-bold text-on-surface">Rs. <?= number_format((float)$p['price'], 2) ?></p>
<?php if ($p['compare_price']): ?><p class="text-label-sm text-on-surface-variant line-through">Rs. <?= number_format((float)$p['compare_price'], 2) ?></p><?php endif; ?>
</td>
<td class="px-6 py-4"><span class="px-3 py-1 rounded-full text-label-sm <?= ((int)($p['stock'] ?? 0)) > 0 ? 'bg-primary-fixed text-on-primary-fixed' : 'bg-error-container text-on-error-container' ?>"><?= ((int)($p['stock'] ?? 0)) > 0 ? (int)$p['stock'] . ' in stock' : 'Out of stock' ?></span></td>
<td class="px-6 py-4"><span class="px-3 py-1 rounded-full text-label-sm <?= $rating_class ?>"><?= number_format($rating, 1) ?> (<?= (int)($p['reviews_count'] ?? 0) ?>)</span></td>
<td class="px-6 py-4"><?= !empty($p['is_bestseller']) ? '<span class="material-symbols-outlined text-secondary">verified</span>' : '<span class="text-on-surface-variant opacity-30">-</span>' ?></td>
<td class="px-6 py-4">
<div class="flex items-center gap-2">
<a href="product-edit.php?id=<?= (int)$p['id'] ?>" class="text-label-sm bg-surface-container-high px-3 py-1 rounded hover:bg-surface-container-highest transition-all flex items-center gap-1"><span class="material-symbols-outlined text-sm">edit</span> Edit</a>
<a href="product-delete.php?id=<?= (int)$p['id'] ?>" class="text-label-sm bg-error-container text-on-error-container px-3 py-1 rounded hover:opacity-80 transition-all flex items-center gap-1"><span class="material-symbols-outlined text-sm">delete</span> Delete</a>
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
