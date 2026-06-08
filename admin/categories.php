<?php
require_once __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$db = getDB();
$page_title = 'Categories';
$active_page = 'categories';

$edit_category = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csrf_token']) && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {

    if (isset($_POST['add_category'])) {
        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $image_url = trim($_POST['image_url'] ?? '');

        if ($name !== '' && $slug !== '') {
            $stmt = $db->prepare("INSERT INTO categories (name, slug, image_url) VALUES (?, ?, ?)");
            $stmt->bind_param('sss', $name, $slug, $image_url);
            $stmt->execute();
        }
        header('Location: categories.php');
        exit;
    }

    if (isset($_POST['edit_category'], $_POST['id'])) {
        $id = (int)$_POST['id'];
        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $image_url = trim($_POST['image_url'] ?? '');

        if ($name !== '' && $slug !== '') {
            $stmt = $db->prepare("UPDATE categories SET name = ?, slug = ?, image_url = ? WHERE id = ?");
            $stmt->bind_param('sssi', $name, $slug, $image_url, $id);
            $stmt->execute();
        }
        header('Location: categories.php');
        exit;
    }

    if (isset($_POST['delete_category'], $_POST['id'])) {
        $id = (int)$_POST['id'];
        $stmt = $db->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        header('Location: categories.php');
        exit;
    }
}

if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $res = $db->prepare("SELECT * FROM categories WHERE id = ?");
    $res->bind_param('i', $edit_id);
    $res->execute();
    $edit_category = $res->get_result()->fetch_assoc();
}

$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 20;
$search = trim($_GET['search'] ?? '');

$where = '';
$params = [];
$types = '';
if ($search) {
    $where = "WHERE c.name LIKE ?";
    $params[] = "%$search%";
    $types = 's';
}

$count_sql = "SELECT COUNT(*) FROM categories c $where";
$total_stmt = $db->prepare($count_sql);
if ($params) $total_stmt->bind_param($types, ...$params);
$total_stmt->execute();
$total = $total_stmt->get_result()->fetch_row()[0];
$total_pages = max(1, ceil($total / $per_page));
$offset = ($page - 1) * $per_page;

$data_sql = "SELECT c.*, COUNT(p.id) AS product_count FROM categories c LEFT JOIN products p ON c.id = p.category_id $where GROUP BY c.id ORDER BY c.name ASC LIMIT ? OFFSET ?";
$data_stmt = $db->prepare($data_sql);
if ($params) {
    $all_params = array_merge($params, [$per_page, $offset]);
    $all_types = $types . 'ii';
    $data_stmt->bind_param($all_types, ...$all_params);
} else {
    $data_stmt->bind_param('ii', $per_page, $offset);
}
$data_stmt->execute();
$categories = $data_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<?php require_once __DIR__ . '/includes/head.php'; ?>
<body class="bg-surface font-body-md text-on-surface flex min-h-screen">
<?php require_once __DIR__ . '/includes/sidebar.php'; ?>
<main class="flex-grow ml-64 p-margin-desktop bg-surface min-h-screen">
<header class="flex justify-between items-end mb-8">
<div>
<h2 class="text-display-lg font-display-lg text-primary">Categories</h2>
<p class="text-body-lg text-on-surface-variant mt-2">Manage product categories and organize your inventory.</p>
</div>
</header>

<?php if ($edit_category): ?>
<section class="bg-surface-container-lowest rounded-xl border border-outline-variant p-6 mb-8">
<h3 class="text-headline-md font-headline-md text-primary mb-6">Edit Category</h3>
<form method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
<input type="hidden" name="id" value="<?= $edit_category['id'] ?>">
<div>
<label class="text-label-sm text-on-surface-variant block mb-1">Name</label>
<input class="w-full bg-surface border border-outline-variant rounded-lg px-4 py-2 text-body-md focus:ring-primary" name="name" value="<?= htmlspecialchars($edit_category['name']) ?>" oninput="document.getElementById('edit-slug').value = this.value.toLowerCase().replace(/[^a-z0-9-]+/g,'-').replace(/^-+|-+$/g,'')" required>
</div>
<div>
<label class="text-label-sm text-on-surface-variant block mb-1">Slug</label>
<input id="edit-slug" class="w-full bg-surface border border-outline-variant rounded-lg px-4 py-2 text-body-md focus:ring-primary" name="slug" value="<?= htmlspecialchars($edit_category['slug']) ?>" required>
</div>
<div>
<label class="text-label-sm text-on-surface-variant block mb-1">Image URL</label>
<input class="w-full bg-surface border border-outline-variant rounded-lg px-4 py-2 text-body-md focus:ring-primary" name="image_url" value="<?= htmlspecialchars($edit_category['image_url'] ?? '') ?>">
</div>
<div class="md:col-span-3 flex items-center gap-3 pt-2">
<button class="bg-primary text-on-primary px-6 py-2 rounded-lg text-label-sm hover:opacity-90 transition-opacity" type="submit" name="edit_category" value="1">Update Category</button>
<a class="text-label-sm text-on-surface-variant hover:underline" href="categories.php">Cancel</a>
</div>
</form>
</section>
<?php endif; ?>

<section class="bg-surface-container-lowest rounded-xl border border-outline-variant overflow-hidden mb-8">
<div class="px-6 py-4 border-b border-outline-variant flex flex-wrap justify-between items-center gap-4">
<h3 class="text-headline-md font-headline-md text-primary">Add New Category</h3>
</div>
<div class="p-6">
<form method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
<div>
<label class="text-label-sm text-on-surface-variant block mb-1">Name</label>
<input class="w-full bg-surface border border-outline-variant rounded-lg px-4 py-2 text-body-md focus:ring-primary" name="name" placeholder="e.g. Immunity Boosters" oninput="document.getElementById('add-slug').value = this.value.toLowerCase().replace(/[^a-z0-9-]+/g,'-').replace(/^-+|-+$/g,'')" required>
</div>
<div>
<label class="text-label-sm text-on-surface-variant block mb-1">Slug</label>
<input id="add-slug" class="w-full bg-surface border border-outline-variant rounded-lg px-4 py-2 text-body-md focus:ring-primary" name="slug" placeholder="auto-generated" required>
</div>
<div>
<label class="text-label-sm text-on-surface-variant block mb-1">Image URL</label>
<input class="w-full bg-surface border border-outline-variant rounded-lg px-4 py-2 text-body-md focus:ring-primary" name="image_url" placeholder="https://...">
</div>
<div class="md:col-span-3 flex items-center gap-3 pt-2">
<button class="bg-primary text-on-primary px-6 py-2 rounded-lg text-label-sm hover:opacity-90 transition-opacity" type="submit" name="add_category" value="1">Add Category</button>
</div>
</form>
</div>
</section>

<section class="bg-surface-container-lowest rounded-xl border border-outline-variant overflow-hidden">
<div class="px-6 py-4 border-b border-outline-variant flex flex-wrap justify-between items-center gap-4">
<form method="GET" class="flex gap-4 flex-wrap items-center">
<div class="relative">
<span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-sm">search</span>
<input class="bg-surface border border-outline-variant text-label-sm rounded-lg focus:ring-primary pl-9 py-2 w-56" name="search" placeholder="Search categories..." value="<?= htmlspecialchars($search) ?>" type="text"/>
</div>
<button class="text-label-sm text-primary hover:underline" type="submit">Search</button>
<?php if ($search): ?>
<a href="categories.php" class="text-label-sm text-on-surface-variant hover:underline">Clear</a>
<?php endif; ?>
</form>
</div>
<table class="w-full text-left">
<thead>
<tr class="bg-surface-container-low text-on-surface-variant">
<th class="px-6 py-4 text-label-sm uppercase tracking-wider">Name</th>
<th class="px-6 py-4 text-label-sm uppercase tracking-wider">Slug</th>
<th class="px-6 py-4 text-label-sm uppercase tracking-wider">Image</th>
<th class="px-6 py-4 text-label-sm uppercase tracking-wider">Products</th>
<th class="px-6 py-4 text-label-sm uppercase tracking-wider">Actions</th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant">
<?php foreach ($categories as $c): ?>
<tr>
<td class="px-6 py-4 text-body-md font-bold text-on-surface"><?= htmlspecialchars($c['name']) ?></td>
<td class="px-6 py-4 text-body-md text-on-surface-variant"><?= htmlspecialchars($c['slug']) ?></td>
<td class="px-6 py-4">
<?php if ($c['image_url']): ?>
<img class="w-12 h-12 object-cover rounded-lg border border-outline-variant" src="<?= htmlspecialchars($c['image_url']) ?>" alt="<?= htmlspecialchars($c['name']) ?>">
<?php else: ?>
<div class="w-12 h-12 rounded-lg bg-surface-container-high flex items-center justify-center text-on-surface-variant"><span class="material-symbols-outlined text-lg">image</span></div>
<?php endif; ?>
</td>
<td class="px-6 py-4"><span class="px-3 py-1 rounded-full text-label-sm bg-primary-fixed text-on-primary-fixed"><?= (int)$c['product_count'] ?></span></td>
<td class="px-6 py-4">
<div class="flex items-center gap-2">
<a class="text-label-sm bg-surface-container-high hover:bg-surface-container-highest px-3 py-1.5 rounded transition-colors flex items-center gap-1" href="?edit=<?= $c['id'] ?><?= $search ? '&search='.urlencode($search) : '' ?><?= $page > 1 ? '&page='.$page : '' ?>"><span class="material-symbols-outlined text-sm">edit</span> Edit</a>
<form method="POST" onsubmit="return confirm('Delete category &ldquo;<?= htmlspecialchars($c['name'], ENT_QUOTES) ?>&rdquo;? Products in this category will be uncategorized.')">
<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
<input type="hidden" name="id" value="<?= $c['id'] ?>">
<button class="text-label-sm bg-error-container text-on-error-container hover:opacity-80 px-3 py-1.5 rounded transition-opacity flex items-center gap-1" type="submit" name="delete_category" value="1"><span class="material-symbols-outlined text-sm">delete</span> Delete</button>
</form>
</div>
</td>
</tr>
<?php endforeach; ?>
<?php if (empty($categories)): ?>
<tr><td colspan="5" class="px-6 py-8 text-center text-on-surface-variant">No categories found.</td></tr>
<?php endif; ?>
</tbody>
</table>
<div class="px-6 py-4 border-t border-outline-variant flex justify-between items-center text-label-sm text-on-surface-variant">
<span>Showing <?= count($categories) ?> of <?= $total ?></span>
<div class="flex gap-2">
<?php if ($page > 1): ?><a class="px-3 py-1 rounded bg-surface-container-high hover:bg-surface-container-highest transition-colors" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>">Previous</a><?php endif; ?>
<span class="px-3 py-1 rounded bg-primary text-on-primary"><?= $page ?></span>
<?php if ($page < $total_pages): ?><a class="px-3 py-1 rounded bg-surface-container-high hover:bg-surface-container-highest transition-colors" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>">Next</a><?php endif; ?>
</div>
</div>
</section>
</main>
</body>
</html>
